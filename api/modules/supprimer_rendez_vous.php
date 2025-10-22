<?php
// api/modules/supprimer_rendez_vous.php

// Afficher les erreurs pendant le dev (désactiver en prod)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1) Démarrage de session si nécessaire
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2) Contrôle des rôles autorisés
if (empty($_SESSION['id'])
    || ! in_array($_SESSION['type_compte'], ['Super Administrateur','Secretaire','Medecin'], true)) {
    session_unset();
    session_destroy();
    header('Location: ../index.php?erreur=3');
    exit();
}

// 3) Vérification de l'ID en GET
if (!isset($_GET['id_rdv']) || !is_numeric($_GET['id_rdv'])) {
    header('Location: ../../pages/rendez_vous.php');
    exit();
}
$id_rdv = (int) $_GET['id_rdv'];

// 4) Connexion PDO
require __DIR__ . '/connect_db_pdo.php';

try {
    // 5) Charger l'ancien rendez-vous
    $stmtOld = $bdd->prepare("SELECT * FROM rendez_vous WHERE id = ?");
    $stmtOld->execute([$id_rdv]);
    $old = $stmtOld->fetch(PDO::FETCH_ASSOC);

    if ($old) {
        // 🔹 5.a) Récupérer l'email et nom du patient avant suppression
        $qPatient = $bdd->prepare("
            SELECT email, nom, prenom
              FROM patient
             WHERE id = ?
               AND supprimer = 'NON'
             LIMIT 1
        ");
        $qPatient->execute([$old['id_patient']]);
        $patientData = $qPatient->fetch(PDO::FETCH_ASSOC);

        // 6) Supprimer le rendez-vous (hard delete)
        $stmtDel = $bdd->prepare("DELETE FROM rendez_vous WHERE id = ?");
        $stmtDel->execute([$id_rdv]);

        // 7) Journaliser l’action
        $hist = $bdd->prepare("
            INSERT INTO historique_action
              (id_utilisateur, nom_action, nom_table, id_concerne,
               ancienne_valeur, nouvelle_valeur, adresse_ip)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $hist->execute([
            $_SESSION['id'],
            'Suppression rendez-vous',
            'rendez_vous',
            $id_rdv,
            json_encode($old, JSON_UNESCAPED_UNICODE),
            null,
            $_SERVER['REMOTE_ADDR']
        ]);

        // 🔹 8) Envoi d'email au patient pour l'informer de l'annulation
        if ($patientData && !empty($patientData['email']) && filter_var($patientData['email'], FILTER_VALIDATE_EMAIL)) {
            $to       = $patientData['email'];
            $subject  = "Annulation de votre rendez-vous - BI Health";
            $message  = "
                <html>
                <body>
                    <p>Bonjour <strong>{$patientData['prenom']} {$patientData['nom']}</strong>,</p>
                    <p>Votre rendez-vous prévu initialement a été <strong>annulé</strong> :</p>
                    <ul>
                        <li><strong>Date :</strong> {$old['date_rdv']}</li>
                        <li><strong>Heure :</strong> {$old['heure_rdv']}</li>
                        <li><strong>Motif :</strong> {$old['motif']}</li>
                    </ul>
                    <p>Si vous souhaitez le reprogrammer, merci de nous contacter.<br>BI Health</p>
                </body>
                </html>
            ";
            $headers  = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8\r\n";
            $headers .= "From: BI Health <no-reply@bihealth.com>\r\n";

            @mail($to, $subject, $message, $headers);
        }

        $_SESSION['supr'] = 1;
    }

    $bdd = null;
    header('Location: ../../pages/rendez_vous.php');
    exit();

} catch (Exception $e) {
    $bdd = null;
    $_SESSION['imp'] = 1;
    header('Location: ../../pages/rendez_vous.php');
    exit();
}
