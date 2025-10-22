<?php
// /api/modules/modifier_consultation.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// 1) Authentification
if (empty($_SESSION['id'])) {
    header('Location: ../../index.php?erreur=3');
    exit();
}

// 2) Vérifier POST + ID
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id_consultation'])) {
    header('Location: ../../pages/consultation.php');
    exit();
}

$id_consult = (int) $_POST['id_consultation'];

// 3) Conversion datetime-local → SQL DATETIME
$dt_raw = trim($_POST['date_consultation'] ?? '');
if ($dt_raw !== '') {
    $dt = DateTime::createFromFormat('Y-m-d\TH:i', $dt_raw);
    if (!$dt) {
        $_SESSION['imp_co']     = 1;
        $_SESSION['message_co'] = 'Format de date invalide.';
        header('Location: ../../pages/consultation.php');
        exit();
    }
    $date_mysql = $dt->format('Y-m-d H:i:s');
} else {
    $date_mysql = null;
}

require __DIR__ . '/connect_db_pdo.php';

try {
    $bdd->beginTransaction();

    // 4) Mettre à jour la consultation
    $updC = $bdd->prepare("
      UPDATE consultation SET
        id_patient        = ?,
        id_medecin        = ?,
        date_consultation = ?,
        motif             = ?,
        symptomes         = ?,
        diagnostic        = ?,
        observations      = ?
      WHERE id_consultation = ?
    ");
    $updC->execute([
        (int) $_POST['id_patient'],
        (int) $_POST['id_medecin'],
        $date_mysql,
        trim($_POST['motif']),
        trim($_POST['symptomes']   ?? ''),
        trim($_POST['diagnostic']),
        trim($_POST['observations'] ?? ''),
        $id_consult
    ]);

    // 5) Constantes vitales : supprimer + réinsérer
    $delConst = $bdd->prepare("DELETE FROM constante_consultation WHERE id_consultation = ?");
    $delConst->execute([$id_consult]);

    $constantes = [
        'Tension artérielle'     => $_POST['tension_arterielle']    ?? '',
        'Température'            => $_POST['temperature']           ?? '',
        'Poids'                  => $_POST['poids']                 ?? '',
        'Taille'                 => $_POST['taille']                ?? '',
        'Fréquence cardiaque'    => $_POST['frequence_cardiaque']   ?? '',
        'IMC'                    => $_POST['imc']                   ?? '',
        'Glycémie'               => $_POST['glycemie']              ?? '',
        'Fréquence respiratoire' => $_POST['frequence_respiratoire']?? '',
        'Saturation oxygène'     => $_POST['saturation_oxygene']    ?? ''
    ];
    $unités = [
        'Tension artérielle'     => 'mmHg',
        'Température'            => '°C',
        'Poids'                  => 'kg',
        'Taille'                 => 'cm',
        'Fréquence cardiaque'    => 'bpm',
        'IMC'                    => 'kg/m²',
        'Glycémie'               => 'g/L',
        'Fréquence respiratoire' => 'cpm',
        'Saturation oxygène'     => '%'
    ];
    $insConst = $bdd->prepare("
      INSERT INTO constante_consultation
        (id_consultation, nom, valeur, unite)
      VALUES (:cid, :nom, :val, :unit)
    ");
    foreach ($constantes as $nom => $val) {
        $v = trim($val);
        if ($v !== '') {
            $insConst->execute([
                ':cid'  => $id_consult,
                ':nom'  => $nom,
                ':val'  => $v,
                ':unit' => $unités[$nom]
            ]);
        }
    }

    // 6) Ordonnance : update ou insert
    $ordStmt = $bdd->prepare("
      SELECT id_ordonnance 
        FROM ordonnance
       WHERE id_consultation = ?
         AND supprimer       = 'NON'
       LIMIT 1
    ");
    $ordStmt->execute([$id_consult]);
    $ordId = $ordStmt->fetchColumn();

    if ($ordId) {
        $updO = $bdd->prepare("
          UPDATE ordonnance
             SET type_ordonnance = ?, instructions = ?
           WHERE id_ordonnance = ?
        ");
        $updO->execute([
            $_POST['type_ordonnance'] ?? 'Standard',
            trim($_POST['instructions'] ?? ''),
            $ordId
        ]);
    } else {
        $insO = $bdd->prepare("
          INSERT INTO ordonnance
            (type_ordonnance, id_patient, id_consultation, instructions)
          VALUES (?, ?, ?, ?)
        ");
        $insO->execute([
            $_POST['type_ordonnance'] ?? 'Standard',
            (int) $_POST['id_patient'],
            $id_consult,
            trim($_POST['instructions'] ?? '')
        ]);
        $ordId = $bdd->lastInsertId();
    }

    // 7) Médicaments : supprimer + réinsérer
    $delMed = $bdd->prepare("DELETE FROM ordonnance_medicament WHERE id_ordonnance = ?");
    $delMed->execute([$ordId]);

    if (!empty($_POST['medicament'])) {
        $insMed = $bdd->prepare("
          INSERT INTO ordonnance_medicament
            (id_ordonnance, medicament, posologie, duree)
          VALUES (?, ?, ?, ?)
        ");
        foreach ($_POST['medicament'] as $i => $med) {
            $m = trim($med);
            $p = trim($_POST['posologie'][$i] ?? '');
            $d = trim($_POST['duree'][$i]      ?? '');
            if ($m !== '') {
                $insMed->execute([$ordId, $m, $p, $d]);
            }
        }
    }

    // 8) Examens de labo : upsert & suppression des absents
    // Récupérer les IDs existants
    $stmtExist = $bdd->prepare("
      SELECT id_examen
        FROM examen_labo
       WHERE id_consultation = ?
         AND supprimer       = 'NON'
    ");
    $stmtExist->execute([$id_consult]);
    $existing = $stmtExist->fetchAll(PDO::FETCH_COLUMN);

    $updE = $bdd->prepare("
      UPDATE examen_labo
         SET type_examen = ?, motif = ?, est_urgent = ?
       WHERE id_examen = ?
    ");
    $insE = $bdd->prepare("
      INSERT INTO examen_labo
        (id_consultation, type_examen, motif, est_urgent)
      VALUES (?, ?, ?, ?)
    ");

    $postedIds = [];
    if (!empty($_POST['examen_type'])) {
        foreach ($_POST['examen_type'] as $i => $rawType) {
            $typeEx = trim($rawType);
            if ($typeEx === '') continue;

            $motif   = trim($_POST['examen_motif'][$i]   ?? '');
            $urgent  = isset($_POST['examen_urgent'][$i]) 
                     ? (int) $_POST['examen_urgent'][$i] 
                     : 0;
            $eid     = isset($_POST['id_examen'][$i]) 
                     ? (int) $_POST['id_examen'][$i] 
                     : 0;

            if ($eid > 0) {
                // mise à jour
                $updE->execute([$typeEx, $motif, $urgent, $eid]);
                $postedIds[] = $eid;
            } else {
                // nouvelle insertion
                $insE->execute([$id_consult, $typeEx, $motif, $urgent]);
                $newId = $bdd->lastInsertId();
                $postedIds[] = $newId;
            }
        }
    }

    // supprime les examens retirés
    $toDelete = array_diff($existing, $postedIds);
    if (!empty($toDelete)) {
        $delE = $bdd->prepare("
          UPDATE examen_labo
             SET supprimer = 'OUI'
           WHERE id_examen = ?
        ");
        foreach ($toDelete as $delId) {
            $delE->execute([(int) $delId]);
        }
    }

    // 9) Résultats de labo : supprimer + réinsérer pour chaque examen publié
    if (!empty($_POST['resultat_examen_id_examen'])) {
        $uploadDir = __DIR__ . '/../../uploads/resultats/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $delR = $bdd->prepare("DELETE FROM resultat_examen WHERE id_examen = ?");
        $insR = $bdd->prepare("
          INSERT INTO resultat_examen
            (id_examen, contenu_texte, fichier, date_resultat, supprimer)
          VALUES (?, ?, ?, NOW(), 'NON')
        ");

        foreach ($_POST['resultat_examen_id_examen'] as $i => $examId) {
            $idEx     = (int) $examId;
            $texte    = trim($_POST['resultat_texte'][$i] ?? '');
            $fileName = null;

            if (!empty($_FILES['resultat_fichier']['tmp_name'][$i])) {
                $tmp  = $_FILES['resultat_fichier']['tmp_name'][$i];
                $orig = basename($_FILES['resultat_fichier']['name'][$i]);
                $fileName = uniqid('res_') . '_' 
                          . preg_replace('/[^a-zA-Z0-9._-]/', '_', $orig);
                move_uploaded_file($tmp, $uploadDir . $fileName);
            }

            $delR->execute([$idEx]);
            $insR->execute([$idEx, $texte, $fileName]);
        }
    }

    // 10) Commit
    $bdd->commit();

    $_SESSION['mod_co']     = 1;
    $_SESSION['message_co'] = 'Consultation modifiée avec succès';
} catch (PDOException $e) {
    $bdd->rollBack();
    $_SESSION['imp_co']     = 1;
    $_SESSION['message_co'] = 'Erreur SQL : ' . $e->getMessage();
}

header('Location: ../../pages/consultation.php');
exit();
