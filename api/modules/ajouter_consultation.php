<?php
// /api/modules/ajouter_consultation.php

// Affichage des erreurs (dev only)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// 1) Vérifier l'authentification
if (empty($_SESSION['id'])) {
    header('Location: ../../index.php?erreur=3');
    exit();
}

// 2) Vérifier la méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../pages/consultation.php');
    exit();
}

// 3) Champs obligatoires
$required = [
    'id_patient',
    'id_medecin',
    'date_consultation',
    'motif',
    'diagnostic'
];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['imp_co']     = 1;
        $_SESSION['message_co'] = "Le champ « {$field} » est obligatoire.";
        header('Location: ../../pages/consultation.php');
        exit();
    }
}

// 4) Convertir datetime-local -> SQL DATETIME
$dateObj = DateTime::createFromFormat('Y-m-d\TH:i', trim($_POST['date_consultation']));
if (!$dateObj) {
    $_SESSION['imp_co']     = 1;
    $_SESSION['message_co'] = "Format de date invalide.";
    header('Location: ../../pages/consultation.php');
    exit();
}
$date_mysql = $dateObj->format('Y-m-d H:i:s');

// 5) Connexion PDO
require __DIR__ . '/connect_db_pdo.php';

try {
    // Démarrer la transaction
    $bdd->beginTransaction();

    // 6) Insérer la consultation
    $sqlConsult = "
      INSERT INTO consultation (
        id_patient,
        id_medecin,
        date_consultation,
        motif,
        symptomes,
        diagnostic,
        observations
      ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ";
    $stmtConsult = $bdd->prepare($sqlConsult);
    $stmtConsult->execute([
        (int) $_POST['id_patient'],
        (int) $_POST['id_medecin'],
        $date_mysql,
        trim($_POST['motif']),
        trim($_POST['symptomes']   ?? ''),
        trim($_POST['diagnostic']),
        trim($_POST['observations'] ?? '')
    ]);
    $consultId = (int) $bdd->lastInsertId();

    // 7) Enregistrer les constantes vitales
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
    $sqlConst = "
      INSERT INTO constante_consultation
        (id_consultation, nom, valeur, unite)
      VALUES
        (:cid, :nom, :val, :unit)
    ";
    $stmtConst = $bdd->prepare($sqlConst);
    foreach ($constantes as $nom => $valeur) {
        $v = trim($valeur);
        if ($v !== '') {
            $stmtConst->execute([
                ':cid'  => $consultId,
                ':nom'  => $nom,
                ':val'  => $v,
                ':unit' => $unités[$nom]
            ]);
        }
    }

    // 8) Insérer l'ordonnance
    $sqlOrd = "
      INSERT INTO ordonnance
        (type_ordonnance, id_patient, id_consultation, instructions)
      VALUES (?, ?, ?, ?)
    ";
    $stmtOrd = $bdd->prepare($sqlOrd);
    $stmtOrd->execute([
      $_POST['type_ordonnance'] ?? 'Standard',
      (int) $_POST['id_patient'],
      $consultId,
      trim($_POST['instructions'] ?? '')
    ]);
    $ordId = (int) $bdd->lastInsertId();

    // 9) Insérer les lignes de médicaments
    if (!empty($_POST['medicament'])) {
        $sqlMed = "
          INSERT INTO ordonnance_medicament
            (id_ordonnance, medicament, posologie, duree)
          VALUES (?, ?, ?, ?)
        ";
        $stmtMed = $bdd->prepare($sqlMed);
        foreach ($_POST['medicament'] as $i => $med) {
            $m = trim($med);
            if ($m === '') continue;
            $stmtMed->execute([
                $ordId,
                $m,
                trim($_POST['posologie'][$i] ?? ''),
                trim($_POST['duree'][$i]      ?? '')
            ]);
        }
    }

    // 10) Insérer les examens de laboratoire
    if (!empty($_POST['examen_type'])) {
        $sqlExam = "
          INSERT INTO examen_labo
            (id_consultation, type_examen, motif, est_urgent)
          VALUES (?, ?, ?, ?)
        ";
        $stmtExam = $bdd->prepare($sqlExam);
        foreach ($_POST['examen_type'] as $i => $typeEx) {
            $te = trim($typeEx);
            if ($te === '') continue;
            $stmtExam->execute([
                $consultId,
                $te,
                trim($_POST['examen_motif'][$i]   ?? ''),
                isset($_POST['examen_urgent'][$i]) 
                  ? (int) $_POST['examen_urgent'][$i]
                  : 0
            ]);
        }
    }

    // 11) Succès
    $bdd->commit();
    $_SESSION['ajout_co']   = 1;
    $_SESSION['message_co'] = "Consultation ajoutée avec succès.";

} catch (PDOException $e) {
    $bdd->rollBack();
    $_SESSION['imp_co']     = 1;
    $_SESSION['message_co'] = "Erreur SQL : " . $e->getMessage();
}

// 12) Redirection finale
header('Location: ../../pages/consultation.php');
exit();
