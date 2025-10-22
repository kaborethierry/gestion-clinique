<?php
// api/modules/ajouter_chambre.php

// Affichage des erreurs en dev (désactiver en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// ➊ Contrôle d’accès : Super Administrateur, Secretaire et Medecin
$allowedRoles = ['Super Administrateur', 'Secretaire', 'Medecin'];
if (empty($_SESSION['id'])
    || ! in_array($_SESSION['type_compte'], $allowedRoles, true)
) {
    header('Location: ../../index.php?erreur=3');
    exit();
}

if (!empty($_GET['id_chambre'])) {
    require __DIR__ . '/connect_db_pdo.php';

    try {
        // 1) Charger l'ancienne valeur
        $stmtOld = $bdd->prepare("SELECT * FROM chambre WHERE id_chambre = ?");
        $stmtOld->execute([(int)$_GET['id_chambre']]);
        $old = $stmtOld->fetch(PDO::FETCH_ASSOC);

        if (!$old) {
            throw new Exception("Chambre introuvable.");
        }

        // 2) Suppression
        $stmt = $bdd->prepare("DELETE FROM chambre WHERE id_chambre = ?");
        $stmt->execute([(int)$_GET['id_chambre']]);

        // 3) Historique
        $h = $bdd->prepare("INSERT INTO historique_action
            (id_utilisateur, nom_action, nom_table, id_concerne,
             ancienne_valeur, nouvelle_valeur, adresse_ip, supprimer)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $h->execute([
            $_SESSION['id'],
            'Suppression chambre',
            'chambre',
            (int)$_GET['id_chambre'],
            json_encode($old, JSON_UNESCAPED_UNICODE),
            null,
            $_SERVER['REMOTE_ADDR'],
            'NON'
        ]);

        $_SESSION['suppr_ch'] = 1;

    } catch (Exception $e) {
        $_SESSION['imp_ch']     = 1;
        $_SESSION['message_ch'] = $e->getMessage();
    }
}

header('Location:../../pages/chambre.php');
exit();
