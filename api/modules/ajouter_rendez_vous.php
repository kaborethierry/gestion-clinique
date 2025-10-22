<?php
// api/modules/ajouter_rendez_vous.php

// Afficher les erreurs pendant le dev (désactiver en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1) Démarrage de session si nécessaire
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2) Contrôle des rôles autorisés
if (empty($_SESSION['id'])
    || ! in_array($_SESSION['type_compte'], ["Super Administrateur", "Secretaire", "Medecin"], true)) {
    session_unset();
    session_destroy();
    header('Location:../index.php?erreur=3');
    exit();
}

// 3) Vérification des champs POST obligatoires
$required = ['id_patient','id_medecin','date_rdv','heure_rdv','motif','statut'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['erreur_champ_manquant'] = 1;
        header('Location:../../pages/rendez_vous.php');
        exit();
    }
}

// 4) Connexion MySQLi (pour l’échappement et les vérifications)
include __DIR__ . '/connect_db.php';

// 5) Échappement & assignation
$id_patient = mysqli_real_escape_string($db, $_POST['id_patient']);
$id_medecin = mysqli_real_escape_string($db, $_POST['id_medecin']);
$date_rdv   = mysqli_real_escape_string($db, $_POST['date_rdv']);
$heure_rdv  = mysqli_real_escape_string($db, $_POST['heure_rdv']);
$motif      = mysqli_real_escape_string($db, trim($_POST['motif']));
$statut     = mysqli_real_escape_string($db, $_POST['statut']);
$note       = isset($_POST['note'])
              ? mysqli_real_escape_string($db, trim($_POST['note']))
              : '';

// 6) Nettoyage des sauts de ligne
$motif = str_replace(["\r\n","\n","\r"], ' ', $motif);
$note  = str_replace(["\r\n","\n","\r"], ' ', $note);

// 7) Vérifier que le patient existe
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

// 8) Vérifier que le médecin existe
$resM = mysqli_query($db, "
    SELECT COUNT(*) AS cnt
      FROM utilisateur
     WHERE id_utilisateur = '$id_medecin'
       AND type_compte = 'Medecin'
");
$rowM = mysqli_fetch_assoc($resM);
if (empty($rowM['cnt']) || $rowM['cnt'] == 0) {
    $_SESSION['erreur_medecin_introuvable'] = 1;
    header('Location:../../pages/rendez_vous.php');
    exit();
}

// 9) Insérer en PDO et historiser
include __DIR__ . '/connect_db_pdo.php';

try {
    // 9.a) INSERT
    $sql  = "INSERT INTO rendez_vous
               (id_patient, id_medecin, date_rdv, heure_rdv, motif, statut, note)
             VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        $id_patient,
        $id_medecin,
        $date_rdv,
        $heure_rdv,
        $motif,
        $statut,
        $note
    ]);
    $newId = $bdd->lastInsertId();

    // 9.b) Historique
    $nouvelle = json_encode([
        'id_patient' => $id_patient,
        'id_medecin' => $id_medecin,
        'date_rdv'   => $date_rdv,
        'heure_rdv'  => $heure_rdv,
        'motif'      => $motif,
        'statut'     => $statut,
        'note'       => $note
    ], JSON_UNESCAPED_UNICODE);

    $hist = $bdd->prepare("
        INSERT INTO historique_action
          (id_utilisateur, nom_action, nom_table, id_concerne,
           ancienne_valeur, nouvelle_valeur, adresse_ip)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $hist->execute([
        $_SESSION['id'],
        'Ajout rendez-vous',
        'rendez_vous',
        $newId,
        null,
        $nouvelle,
        $_SERVER['REMOTE_ADDR']
    ]);

    // 9.c) Envoi d'email au patient
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
        $subject  = "Confirmation de votre rendez-vous - BI Health";
        $message  = "
            <html>
            <body>
                <p>Bonjour <strong>{$patientData['prenom']} {$patientData['nom']}</strong>,</p>
                <p>Votre rendez-vous a été enregistré avec succès :</p>
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

    // 10) Cleanup
    mysqli_close($db);
    $bdd = null;

    $_SESSION['ajout'] = 1;
    header('Location:../../pages/rendez_vous.php');
    exit();

} catch (Exception $e) {
    mysqli_close($db);
    $bdd = null;
    $_SESSION['imp'] = 1;
    header('Location:../../pages/rendez_vous.php');
    exit();
}
