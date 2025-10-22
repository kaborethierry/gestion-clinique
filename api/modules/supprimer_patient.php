<?php
// api/modules/supprimer_patient.php
// Supprime un patient et enregistre l’historique

// Affichage des erreurs en dev (retirer en prod)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Session sécurisée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ➊ Contrôle d’accès : Super Administrateur, Secrétaire et Médecin
$allowedRoles = ['Super Administrateur', 'Secretaire', 'Medecin'];
if (empty($_SESSION['id'])
    || ! in_array($_SESSION['type_compte'], $allowedRoles, true)
) {
    session_unset();
    session_destroy();
    header('Location: ../index.php?erreur=3');
    exit();
}

// ➋ Vérification de l’ID patient
if (!isset($_GET['id_patient']) || !is_numeric($_GET['id_patient'])) {
    header('Location: ../../pages/patient.php');
    exit();
}
$id_patient = (int) $_GET['id_patient'];

// ➌ Connexion PDO
require __DIR__ . '/connect_db_pdo.php';

try {
    // ➍ Récupérer le patient avant suppression
    $stmtSelect = $bdd->prepare("SELECT * FROM patient WHERE id = ?");
    $stmtSelect->execute([$id_patient]);
    $old_patient = $stmtSelect->fetch(PDO::FETCH_ASSOC);

    if (!$old_patient) {
        $_SESSION['imp'] = 1;
        $_SESSION['message_erreur'] = "Patient introuvable.";
        header('Location: ../../pages/patient.php');
        exit();
    }

    // ➎ Supprimer la photo si existante
    if (!empty($old_patient['photo'])) {
        $photo_path = __DIR__ . '/../../uploads/patients/' . $old_patient['photo'];
        if (file_exists($photo_path)) {
            unlink($photo_path);
        }
    }

    // ➏ Suppression du patient en base
    $stmtDelete = $bdd->prepare("DELETE FROM patient WHERE id = ?");
    $stmtDelete->execute([$id_patient]);

    // ➐ Enregistrement dans historique_action
    $stmtHist = $bdd->prepare("
        INSERT INTO historique_action (
            id_utilisateur, nom_action, nom_table, id_concerne,
            ancienne_valeur, nouvelle_valeur, adresse_ip, supprimer
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmtHist->execute([
        $_SESSION['id'],
        "Suppression patient",
        "patient",
        $id_patient,
        json_encode($old_patient, JSON_UNESCAPED_UNICODE),
        null,
        $_SERVER['REMOTE_ADDR'],
        'OUI'
    ]);

    $_SESSION['suppr'] = 1;
    header('Location: ../../pages/patient.php');
    exit();

} catch (PDOException $e) {
    // Gestion des erreurs d’intégrité (FK)
    $msg = $e->getMessage();
    if (strpos($msg, 'Integrity constraint violation') !== false) {
        $_SESSION['imp'] = 1;
        $_SESSION['message_erreur'] = "Impossible de supprimer : contraintes liées.";
    } else {
        $_SESSION['imp'] = 1;
        $_SESSION['message_erreur'] = "Erreur PDO : " . $msg;
    }
    header('Location: ../../pages/patient.php');
    exit();
} finally {
    // Fermeture de la connexion
    $bdd = null;
}
