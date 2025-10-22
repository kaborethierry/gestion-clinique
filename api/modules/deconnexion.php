<?php
// api/modules/deconnexion.php

// Démarrer la session
session_start();

// ➊ Vérification : tout utilisateur connecté peut se déconnecter
if (empty($_SESSION['id'])) {
    header('Location: ../../index.php?erreur=3');
    exit();
}

// ➋ Connexion à la base de données
require __DIR__ . '/connect_db_pdo.php';

// ➌ Enregistrement de l’action dans l’historique
$action = 'Déconnexion';
$stmt   = $bdd->prepare(
    'INSERT INTO historique_action (id_utilisateur, nom_action) VALUES (?, ?)'
);
$stmt->execute([$_SESSION['id'], $action]);

// ➍ Destruction complète de la session
session_unset();
session_destroy();

// ➎ Retour à la page de connexion
header('Location: ../../index.php');
exit();
?>
