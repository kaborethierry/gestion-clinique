<?php
// api/modules/supprimer_lit.php

session_start();

// ➊ Contrôle d’accès : Super Administrateur, Secretaire et Medecin
if (empty($_SESSION['id'])
    || ! in_array($_SESSION['type_compte'], [
         "Super Administrateur",
         "Secretaire",
         "Medecin"
       ], true)
) {
    session_unset();
    session_destroy();
    header('Location: ../index.php?erreur=3');
    exit();
}

// … le reste de votre traitement d’ajout de lit …


// Vérifier que l'ID du lit à supprimer est fourni
if (!isset($_GET['id_lit']) || !is_numeric($_GET['id_lit'])) {
    header('Location: ../../pages/lit.php');
    exit();
}

$id_lit = (int) $_GET['id_lit'];

require __DIR__ . '/connect_db_pdo.php';

// Récupération du lit avant suppression (soft‐delete) pour l’historique
$stmtSelect = $bdd->prepare("SELECT id_chambre, numero_lit, statut, date_creation FROM lit WHERE id_lit = ?");
$stmtSelect->execute([$id_lit]);
$old_lit = $stmtSelect->fetch(PDO::FETCH_ASSOC);

if ($old_lit) {
    // Soft‐delete du lit
    $stmtDelete = $bdd->prepare("
        UPDATE lit
           SET supprimer = 'OUI',
               date_modification = NOW()
         WHERE id_lit = ?
    ");
    $stmtDelete->execute([$id_lit]);

    // Préparer l'historique
    $ancienne_valeur = json_encode($old_lit);
    $nouvelle_valeur = json_encode(['supprimer' => 'OUI']);
    $nom_action      = "Suppression lit";
    $nom_table       = "lit";
    $id_concerne     = $id_lit;
    $adresse_ip      = $_SERVER['REMOTE_ADDR'];

    // Enregistrement dans historique_action
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

$bdd = null;
$_SESSION['supr_lit'] = 1;

header('Location: ../../pages/lit.php');
exit();
