<?php
// api/modules/modifier_rendez_vous.php

// 1) Afficher les erreurs pour le développement (désactiver en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2) Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3) Contrôle des rôles autorisés : Super Administrateur, Secretaire, Medecin
if (empty($_SESSION['id'])
    || ! in_array($_SESSION['type_compte'], ['Super Administrateur', 'Secretaire', 'Medecin'], true)) {
    session_unset();
    session_destroy();
    header('Location:../index.php?erreur=3');
    exit();
}

// 4) Vérifier les champs POST obligatoires
$required = ['id_rdv','id_patient','id_medecin','date_rdv','heure_rdv','motif','statut'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['erreur_champ_manquant'] = 1;
        header('Location:../../pages/rendez_vous.php');
        exit();
    }
}

// 5) Connexion MySQLi
include __DIR__ . '/connect_db.php';

// 6) Échapper et nettoyer les entrées
$id_rdv     = mysqli_real_escape_string($db, $_POST['id_rdv']);
$id_patient = mysqli_real_escape_string($db, $_POST['id_patient']);
$id_medecin = mysqli_real_escape_string($db, $_POST['id_medecin']);
$date_rdv   = mysqli_real_escape_string($db, $_POST['date_rdv']);
$heure_rdv  = mysqli_real_escape_string($db, $_POST['heure_rdv']);
$motif      = mysqli_real_escape_string($db, trim($_POST['motif']));
$statut     = mysqli_real_escape_string($db, $_POST['statut']);
$note       = isset($_POST['note'])
              ? mysqli_real_escape_string($db, trim($_POST['note']))
              : '';

// 7) Nettoyer les retours à la ligne
$motif = str_replace(["\r\n","\n","\r"], ' ', $motif);
$note  = str_replace(["\r\n","\n","\r"], ' ', $note);

// 8) Vérifier que le rendez-vous existe
$res0 = mysqli_query($db, "
    SELECT COUNT(*) AS cnt
      FROM rendez_vous
     WHERE id = '$id_rdv'
");
$row0 = mysqli_fetch_assoc($res0);
if (empty($row0['cnt']) || $row0['cnt'] == 0) {
    $_SESSION['imp'] = 1;
    header('Location:../../pages/rendez_vous.php');
    exit();
}

// 9) Vérifier que le patient existe
$resP = mysqli_query($db, "
    SELECT COUNT(*) AS cnt
      FROM patient
     WHERE id = '$id_patient'
");
$rowP = mysqli_fetch_assoc($resP);
if (empty($rowP['cnt']) || $rowP['cnt'] == 0) {
    $_SESSION['erreur_patient_introuvable'] = 1;
    header('Location:../../pages/rendez_vous.php');
    exit();
}

// 10) Vérifier que le médecin existe et est de type 'Medecin'
$resM = mysqli_query($db, "
    SELECT COUNT(*) AS cnt
      FROM utilisateur
     WHERE id_utilisateur = '$id_medecin'
       AND type_compte     = 'Medecin'
");
$rowM = mysqli_fetch_assoc($resM);
if (empty($rowM['cnt']) || $rowM['cnt'] == 0) {
    $_SESSION['erreur_medecin_introuvable'] = 1;
    header('Location:../../pages/rendez_vous.php');
    exit();
}

// 11) Passage en PDO pour UPDATE + historique
include __DIR__ . '/connect_db_pdo.php';

try {
    // 11.a) Récupérer l’ancien état
    $stmtOld = $bdd->prepare("SELECT * FROM rendez_vous WHERE id = ?");
    $stmtOld->execute([$id_rdv]);
    $ancienne = $stmtOld->fetch(PDO::FETCH_ASSOC);

    // 11.b) Mettre à jour le rendez-vous
    $sql = "
        UPDATE rendez_vous SET
            id_patient = ?,
            id_medecin = ?,
            date_rdv   = ?,
            heure_rdv  = ?,
            motif      = ?,
            statut     = ?,
            note       = ?
        WHERE id = ?
    ";
    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        $id_patient,
        $id_medecin,
        $date_rdv,
        $heure_rdv,
        $motif,
        $statut,
        $note,
        $id_rdv
    ]);

    // 11.c) Récupérer le nouvel état
    $stmtNew = $bdd->prepare("SELECT * FROM rendez_vous WHERE id = ?");
    $stmtNew->execute([$id_rdv]);
    $nouvelle = $stmtNew->fetch(PDO::FETCH_ASSOC);

    // 11.d) Historiser
    $hist = $bdd->prepare("
        INSERT INTO historique_action
            (id_utilisateur, nom_action, nom_table, id_concerne,
             ancienne_valeur, nouvelle_valeur, adresse_ip)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $hist->execute([
        $_SESSION['id'],
        'Modification rendez-vous',
        'rendez_vous',
        $id_rdv,
        json_encode($ancienne, JSON_UNESCAPED_UNICODE),
        json_encode($nouvelle, JSON_UNESCAPED_UNICODE),
        $_SERVER['REMOTE_ADDR']
    ]);

    // 11.e) Envoi d'email au patient pour l'informer de la modification
    $qPatient = $bdd->prepare("
        SELECT email, nom, prenom
          FROM patient
         WHERE id = ?
           AND supprimer = 'NON'
         LIMIT 1
    ");
    $qPatient->execute([$id_patient]);
    $patientData = $qPatient->fetch(PDO::FETCH_ASSOC);

    if ($patientData && !empty($patientData['email']) && filter_var($patientData['email'], FILTER_VALIDATE_EMAIL)) {
        $to       = $patientData['email'];
        $subject  = "Mise à jour de votre rendez-vous - BI Health";
        $message  = "
            <html>
            <body>
                <p>Bonjour <strong>{$patientData['prenom']} {$patientData['nom']}</strong>,</p>
                <p>Les détails de votre rendez-vous ont été mis à jour :</p>
                <ul>
                    <li><strong>Date :</strong> {$date_rdv}</li>
                    <li><strong>Heure :</strong> {$heure_rdv}</li>
                    <li><strong>Motif :</strong> {$motif}</li>
                    <li><strong>Statut :</strong> {$statut}</li>
                </ul>
                <p>Merci de votre confiance.<br>BI Health</p>
            </body>
            </html>
        ";
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: BI Health <no-reply@bihealth.com>\r\n";

        @mail($to, $subject, $message, $headers);
    }

    // 12) Nettoyage et redirection succès
    mysqli_close($db);
    $bdd = null;
    $_SESSION['mod'] = 1;
    header('Location:../../pages/rendez_vous.php');
    exit();

} catch (Exception $e) {
    // En cas d’erreur PDO
    mysqli_close($db);
    $bdd = null;
    $_SESSION['imp'] = 1;
    header('Location:../../pages/rendez_vous.php');
    exit();
}
