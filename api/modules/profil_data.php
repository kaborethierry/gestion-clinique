<?php
// api/modules/profil_data.php

// Affichage des erreurs en dev (désactiver en prod)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json; charset=utf-8');

// 1) Vérification de session : tous les comptes authentifiés sont autorisés
if (empty($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require __DIR__ . '/connect_db_pdo.php';

// 2) Récupération des données du profil
$stmt = $bdd->prepare("
    SELECT 
        id_utilisateur,
        nom,
        prenom,
        telephone,
        email,
        username
    FROM utilisateur
    WHERE id_utilisateur = ?
      AND supprimer = 'NON'
    LIMIT 1
");
$stmt->execute([$_SESSION['id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit();
}

// 3) Retour JSON
echo json_encode($user, JSON_UNESCAPED_UNICODE);
exit();
