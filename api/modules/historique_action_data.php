<?php
// /api/modules/historique_action_data.php

// Afficher toutes les erreurs PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Retour JSON UTF-8
header('Content-Type: application/json; charset=UTF-8');

// Connexion PDO
require __DIR__ . '/connect_db_pdo.php';

// Sélection dans l’ordre attendu par DataTables : 
// id, adresse_ip, date_heure_ajout, username, nom_action, nom_table, id_concerne, ancienne_valeur, nouvelle_valeur
$sql = "
  SELECT
    h.id,
    h.adresse_ip,
    h.date_heure_ajout,
    u.username,
    h.nom_action,
    h.nom_table,
    h.id_concerne,
    h.ancienne_valeur,
    h.nouvelle_valeur
  FROM historique_action AS h
  LEFT JOIN utilisateur AS u
    ON h.id_utilisateur = u.id_utilisateur
  WHERE h.supprimer = 'NON'
  ORDER BY h.date_heure_ajout DESC
";

$stmt = $bdd->prepare($sql);
$stmt->execute();

// Fetch et encode
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['data' => $data]);
