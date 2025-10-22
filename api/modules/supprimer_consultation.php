<?php
// /api/modules/supprimer_consultation.php

// Affichage des erreurs en développement (désactiver en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// 1) Authentification
if (empty($_SESSION['id'])) {
    header('Location: ../../index.php?erreur=3');
    exit();
}

// 2) Vérifier la présence de l'ID en GET
if (!isset($_GET['id_consultation'])) {
    header('Location: ../../pages/consultation.php');
    exit();
}

$id = (int) $_GET['id_consultation'];

require __DIR__ . '/connect_db_pdo.php';

try {
    // 3) Charger l'ancienne ligne pour l'historique
    $oldStmt = $bdd->prepare("SELECT * FROM consultation WHERE id_consultation = ?");
    $oldStmt->execute([$id]);
    $old = $oldStmt->fetch(PDO::FETCH_ASSOC);

    if ($old) {
        // 4) Soft delete : passer la consultation à 'OUI'
        $upd = $bdd->prepare("
            UPDATE consultation
               SET supprimer = 'OUI'
             WHERE id_consultation = ?
        ");
        $upd->execute([$id]);

        // 5) Enregistrer l'action dans l'historique
        $hist = $bdd->prepare("
            INSERT INTO historique_action
              (id_utilisateur, nom_action, nom_table, id_concerne,
               ancienne_valeur, nouvelle_valeur, adresse_ip, supprimer)
            VALUES
              (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $hist->execute([
            $_SESSION['id'],
            'Suppression consultation',
            'consultation',
            $id,
            json_encode($old, JSON_UNESCAPED_UNICODE),
            json_encode(['supprimer' => 'OUI'], JSON_UNESCAPED_UNICODE),
            $_SERVER['REMOTE_ADDR'] ?? null,
            'NON'
        ]);

        $_SESSION['suppr_co'] = 1;
    }

} catch (PDOException $e) {
    $_SESSION['imp_co']     = 1;
    $_SESSION['message_co'] = 'Erreur SQL : ' . $e->getMessage();
}

// 6) Redirection vers la liste
header('Location: ../../pages/consultation.php');
exit();
