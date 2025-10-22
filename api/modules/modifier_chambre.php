<?php
// api/modules/modifier_chambre.php

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id_chambre'])) {
    require __DIR__ . '/connect_db_pdo.php';

    try {
        // 1) Charger l'ancienne valeur
        $stmtOld = $bdd->prepare("SELECT * FROM chambre WHERE id_chambre = ?");
        $stmtOld->execute([$_POST['id_chambre']]);
        $old = $stmtOld->fetch(PDO::FETCH_ASSOC);

        if (!$old) {
            throw new Exception("Chambre introuvable.");
        }

        // 2) Mise à jour
        $sql = "UPDATE chambre SET
            numero_chambre   = ?,
            type_chambre     = ?,
            capacite         = ?,
            disponibilite    = ?,
            tarif_journalier = ?,
            etage            = ?,
            description      = ?
          WHERE id_chambre = ?";
        $stmt = $bdd->prepare($sql);
        $stmt->execute([
            $_POST['numero_chambre'],
            $_POST['type_chambre'],
            $_POST['capacite'],
            $_POST['disponibilite'],
            $_POST['tarif_journalier'] ?: null,
            $_POST['etage']               ?: null,
            $_POST['description']         ?: null,
            $_POST['id_chambre']
        ]);

        // 3) Historique
        $nouveau = json_encode([
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
            'Modification chambre',
            'chambre',
            $_POST['id_chambre'],
            json_encode($old, JSON_UNESCAPED_UNICODE),
            $nouveau,
            $_SERVER['REMOTE_ADDR'],
            'NON'
        ]);

        $_SESSION['mod_ch'] = 1;

    } catch (Exception $e) {
        $_SESSION['imp_ch']     = 1;
        $_SESSION['message_ch'] = $e->getMessage();
    }
}

header('Location:../../pages/chambre.php');
exit();
