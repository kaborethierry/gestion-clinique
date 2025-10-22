<?php
// api/modules/ajouter_hospitalisation.php

session_start();

// ➊ Contrôle d’accès : Super Administrateur, Secretaire et Medecin
$allowedRoles = ['Super Administrateur', 'Secretaire', 'Medecin'];
if (empty($_SESSION['id'])
    || ! in_array($_SESSION['type_compte'], $allowedRoles, true)
) {
    session_unset();
    session_destroy();
    header('Location: ../index.php?erreur=3');
    exit();
}

// … le reste de votre traitement d’ajout d’hospitalisation …


// Vérifier que l'ID de l'hospitalisation est fourni
if (!isset($_GET['id_hosp']) || !is_numeric($_GET['id_hosp'])) {
    header('Location: ../../pages/hospitalisation.php');
    exit();
}

$id_hosp = (int) $_GET['id_hosp'];

require __DIR__ . '/connect_db_pdo.php';

// Récupérer l'enregistrement avant suppression
$stmtSelect = $bdd->prepare("SELECT * FROM hospitalisation WHERE id_hosp = ?");
$stmtSelect->execute([$id_hosp]);
$old = $stmtSelect->fetch(PDO::FETCH_ASSOC);

if ($old) {
    // 1) Soft-delete de l'hospitalisation
    $stmtDelete = $bdd->prepare("
        UPDATE hospitalisation
           SET supprimer = 'OUI',
               date_modification = NOW()
         WHERE id_hosp = ?
    ");
    $stmtDelete->execute([$id_hosp]);

    // 2) Libérer le lit si l'hospitalisation était en cours
    if ($old['statut'] === 'En cours') {
        $stmtFree = $bdd->prepare("
            UPDATE lit
               SET statut = 'Libre',
                   date_modification = NOW()
             WHERE id_lit = ?
        ");
        $stmtFree->execute([$old['id_lit']]);
    }

    // 3) Enregistrer l'action dans l'historique
    $nom_action      = "Suppression hospitalisation";
    $nom_table       = "hospitalisation";
    $id_concerne     = $id_hosp;
    $ancienne_valeur = json_encode($old);
    $nouvelle_valeur = null;
    $adresse_ip      = $_SERVER['REMOTE_ADDR'];

    $stmtHist = $bdd->prepare("
        INSERT INTO historique_action
            (id_utilisateur, nom_action, nom_table, id_concerne,
             ancienne_valeur, nouvelle_valeur, adresse_ip)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmtHist->execute([
        $_SESSION['id'],
        $nom_action,
        $nom_table,
        $id_concerne,
        $ancienne_valeur,
        $nouvelle_valeur,
        $adresse_ip
    ]);
}

// Fermer la connexion et rediriger
$bdd = null;
$_SESSION['supr_hosp'] = 1;
header('Location: ../../pages/hospitalisation.php');
exit();
