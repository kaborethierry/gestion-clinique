<?php
// api/modules/modifier_profil.php

// Affichage des erreurs (à désactiver en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (empty($_SESSION['id'])) {
    header('Location:../../index.php?erreur=3');
    exit();
}

// On vérifie que l’ID posté correspond bien à l’utilisateur connecté
$idSession = (int) $_SESSION['id'];
$idPost    = isset($_POST['id_utilisateur']) ? (int) $_POST['id_utilisateur'] : 0;
if ($idPost !== $idSession) {
    header('Location:../../pages/profil.php');
    exit();
}

require __DIR__ . '/connect_db_pdo.php';

// 1) Charger les anciennes données pour l’historique
$stmtOld = $bdd->prepare("
    SELECT nom, prenom, telephone, email, username
      FROM utilisateur
     WHERE id_utilisateur = ?
");
$stmtOld->execute([$idSession]);
$oldData = $stmtOld->fetch(PDO::FETCH_ASSOC) ?: [];

// 2) Fonction de nettoyage
function clean($value) {
    return htmlspecialchars(trim($value), ENT_QUOTES);
}

// 3) Récupérer et nettoyer le POST
$nom       = clean($_POST['nom'] ?? '');
$prenom    = clean($_POST['prenom'] ?? '');
$telephone = clean($_POST['telephone'] ?? '');
$email     = clean($_POST['email'] ?? '');
$username  = clean($_POST['username'] ?? '');

// 4) Mettre à jour l’utilisateur
$stmt = $bdd->prepare("
    UPDATE utilisateur
       SET nom       = ?,
           prenom    = ?,
           telephone = ?,
           email     = ?,
           username  = ?
     WHERE id_utilisateur = ?
");
$stmt->execute([$nom, $prenom, $telephone, $email, $username, $idSession]);

// 5) Enregistrer l’action dans l’historique
$ancienne  = json_encode($oldData, JSON_UNESCAPED_UNICODE);
$nouvelle  = json_encode([
    'nom'       => $nom,
    'prenom'    => $prenom,
    'telephone' => $telephone,
    'email'     => $email,
    'username'  => $username
], JSON_UNESCAPED_UNICODE);

$stmtHist = $bdd->prepare("
    INSERT INTO historique_action
      (id_utilisateur, nom_action, nom_table, id_concerne,
       ancienne_valeur, nouvelle_valeur, adresse_ip)
    VALUES (?, 'Modification de profil', 'utilisateur', ?, ?, ?, ?)
");
$stmtHist->execute([
    $idSession,
    $idSession,
    $ancienne,
    $nouvelle,
    $_SERVER['REMOTE_ADDR']
]);

// 6) Préparer la SweetAlert dans alert_profil.php
$_SESSION['mod_profil'] = 1;

// 7) Retour à la page Profil
header('Location:../../pages/profil.php');
exit();
