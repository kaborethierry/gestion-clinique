<?php
session_start();

// Contrôle d’accès : Super Administrateur et Comptable
if (empty($_SESSION['id'])
    || ! in_array($_SESSION['type_compte'], [
         'Super Administrateur',
         'Comptable'
       ], true)
) {
    session_unset();
    session_destroy();
    header('Location: ../index.php?erreur=3');
    exit();
}

// … le reste de votre traitement d’ajout de facturation …


// Vérifier que l'ID de la facture est fourni
if (!isset($_GET['id_facture']) || !is_numeric($_GET['id_facture'])) {
    header('Location: ../../pages/facturation.php');
    exit();
}

$idFacture = (int) $_GET['id_facture'];

require __DIR__ . '/connect_db_pdo.php';

// Récupérer l'enregistrement avant suppression pour l'historique
$stmtOld = $bdd->prepare("SELECT * FROM facturation WHERE id_facture = ?");
$stmtOld->execute([$idFacture]);
$oldData = $stmtOld->fetch(PDO::FETCH_ASSOC);

if ($oldData) {
    // Soft-delete de la facture
    $stmtDel = $bdd->prepare("
        UPDATE facturation
           SET supprimer = 'OUI',
               date_modification = NOW()
         WHERE id_facture = ?
    ");
    $stmtDel->execute([$idFacture]);

    // Enregistrer l'action dans l'historique
    $stmtHist = $bdd->prepare("
        INSERT INTO historique_action
            (id_utilisateur, nom_action, nom_table, id_concerne,
             ancienne_valeur, nouvelle_valeur, adresse_ip)
        VALUES (?, 'Suppression facture', 'facturation', ?, ?, ?, ?)
    ");
    $stmtHist->execute([
        $_SESSION['id'],
        $idFacture,
        json_encode($oldData),
        null,
        $_SERVER['REMOTE_ADDR']
    ]);
}

$_SESSION['supr_fact'] = 1;
header('Location: ../../pages/facturation.php');
exit();
