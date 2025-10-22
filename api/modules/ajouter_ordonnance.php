<?php
// /api/modules/ajouter_ordonnance.php

// Affichage des erreurs en développement (à désactiver en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// 1) Authentification
if (empty($_SESSION['id'])) {
    header('Location: ../../index.php?erreur=3');
    exit();
}

// 2) Méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../pages/ordonnance.php');
    exit();
}

// 3) Validation des champs obligatoires
foreach (['type_ordonnance', 'id_patient', 'id_consultation'] as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['err_ordon']     = 1;
        $_SESSION['message_ordon'] = "Le champ « {$field} » est requis";
        header('Location: ../../pages/ordonnance.php');
        exit();
    }
}

require __DIR__ . '/connect_db_pdo.php';

try {
    // 4) Démarrer la transaction
    $bdd->beginTransaction();

    // 5) Insertion de l'en-tête de l'ordonnance
    $insertOrd = $bdd->prepare("
        INSERT INTO ordonnance
          (type_ordonnance, id_patient, id_consultation, instructions)
        VALUES (?, ?, ?, ?)
    ");
    $insertOrd->execute([
        trim($_POST['type_ordonnance']),
        (int) $_POST['id_patient'],
        (int) $_POST['id_consultation'],
        !empty($_POST['instructions']) ? trim($_POST['instructions']) : null
    ]);
    $idOrdonnance = $bdd->lastInsertId();

    // 6) Insertion des lignes de médicaments
    $medNames  = $_POST['med_nom']  ?? [];
    $medPos    = $_POST['med_pos']  ?? [];
    $medDurees = $_POST['med_dur']  ?? [];

    if (!empty($medNames)) {
        $insertMed = $bdd->prepare("
            INSERT INTO ordonnance_medicament
              (id_ordonnance, medicament, posologie, duree, supprimer)
            VALUES (?, ?, ?, ?, 'NON')
        ");
        foreach ($medNames as $i => $nom) {
            $nom   = trim($nom);
            $poso  = trim($medPos[$i]   ?? '');
            $duree = trim($medDurees[$i] ?? '');
            if ($nom !== '') {
                $insertMed->execute([
                    $idOrdonnance,
                    $nom,
                    $poso,
                    $duree
                ]);
            }
        }
    }

    // 7) Valider la transaction
    $bdd->commit();

    // 8) Flag de succès pour SweetAlert
    $_SESSION['ajout_ordon']   = 1;
    $_SESSION['message_ordon'] = 'Ordonnance ajoutée avec succès';

} catch (PDOException $e) {
    // 9) Annuler la transaction et stocker l'erreur
    $bdd->rollBack();
    $_SESSION['err_ordon']     = 1;
    $_SESSION['message_ordon'] = 'Erreur SQL : ' . $e->getMessage();
}

// 10) Redirection vers la liste pour afficher la SweetAlert
header('Location: ../../pages/ordonnance.php');
exit();
