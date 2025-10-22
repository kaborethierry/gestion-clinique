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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/connect_db_pdo.php';

    try {
        // 1) Création de la chambre
        $sql = "INSERT INTO chambre
            (numero_chambre, type_chambre, capacite, disponibilite,
             tarif_journalier, etage, description)
         VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $bdd->prepare($sql);
        $stmt->execute([
            $_POST['numero_chambre'],
            $_POST['type_chambre'],
            $_POST['capacite'],
            $_POST['disponibilite'],
            $_POST['tarif_journalier'] ?: null,
            $_POST['etage']               ?: null,
            $_POST['description']         ?: null
        ]);

        // 2) Historique
        $id_new = $bdd->lastInsertId();
        $nouvelle = json_encode([
            'numero_chambre'   => $_POST['numero_chambre'],
            'type_chambre'     => $_POST['type_chambre'],
            'capacite'         => $_POST['capacite'],
            'disponibilite'    => $_POST['disponibilite'],
            'tarif_journalier' => $_POST['tarif_journalier'] ?: null,
            'etage'            => $_POST['etage']               ?: null,
            'description'      => $_POST['description']         ?: null
        ], JSON_UNESCAPED_UNICODE);

        $h = $bdd->prepare("INSERT INTO historique_action
            (id_utilisateur, nom_action, nom_table, id_concerne,
             ancienne_valeur, nouvelle_valeur, adresse_ip, supprimer)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $h->execute([
            $_SESSION['id'],
            'Ajout chambre',
            'chambre',
            $id_new,
            null,
            $nouvelle,
            $_SERVER['REMOTE_ADDR'],
            'NON'
        ]);

        $_SESSION['ajout_ch'] = 1;

    } catch (Exception $e) {
        $_SESSION['imp_ch']     = 1;
        $_SESSION['message_ch'] = $e->getMessage();
    }
}

header('Location:../../pages/chambre.php');
exit();
