<?php
// api/modules/modifier_password.php

// Debug – afficher les erreurs (désactiver en prod)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (empty($_SESSION['id'])) {
    header('Location:../../index.php?erreur=3');
    exit();
}

// Vérifie la présence des champs
if (!isset($_POST['ancien'], $_POST['nouveau'], $_POST['confirmation'])) {
    header('Location:../../pages/profil.php');
    exit();
}

require __DIR__ . '/connect_db_pdo.php';

$id       = (int) $_SESSION['id'];
$ancien   = trim($_POST['ancien']);
$nouveau  = trim($_POST['nouveau']);
$confirm  = trim($_POST['confirmation']);

// 1) Vérifier que le nouveau mot de passe et la confirmation correspondent
if ($nouveau !== $confirm) {
    $_SESSION['anc_password'] = 1; // on réutilise anc_password pour indiquer l’erreur
    header('Location:../../pages/profil.php');
    exit();
}

// 2) Vérifier l’ancien mot de passe
$stmt = $bdd->prepare("SELECT passworde FROM utilisateur WHERE id_utilisateur = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($ancien, $user['passworde'])) {
    $_SESSION['anc_password'] = 1;
    header('Location:../../pages/profil.php');
    exit();
}

// 3) Hasher et mettre à jour le nouveau mot de passe
$newHash = password_hash($nouveau, PASSWORD_DEFAULT);
$updStmt = $bdd->prepare("UPDATE utilisateur SET passworde = ? WHERE id_utilisateur = ?");
$updStmt->execute([$newHash, $id]);

// 4) Historique de l’action
$stmtHist = $bdd->prepare("
    INSERT INTO historique_action
      (id_utilisateur, nom_action, nom_table, id_concerne, ancienne_valeur, nouvelle_valeur, adresse_ip)
    VALUES (?, 'Changement de mot de passe', 'utilisateur', ?, ?, ?, ?)
");
$stmtHist->execute([
    $id,
    $id,
    null,
    json_encode(['password' => 'modifié'], JSON_UNESCAPED_UNICODE),
    $_SERVER['REMOTE_ADDR']
]);

// 5) Préparer l’alerte SweetAlert pour succès
$_SESSION['modif_password'] = 1;

// 6) Redirection vers la page profil
header('Location:../../pages/profil.php');
exit();
