<?php
// api/modules/stats_revenue_month.php

session_start();
if (empty($_SESSION['id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit();
}

// Affichage des erreurs en dev (supprimer en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/connect_db_pdo.php';

$sql = "
  SELECT
    'CA mensuel'             AS label,
    COALESCE(SUM(montant_total), 0) AS value
  FROM facturation
  WHERE supprimer = 'NON'
    AND MONTH(date_paiement) = MONTH(CURDATE())
    AND YEAR(date_paiement)  = YEAR(CURDATE())
";

$stmt = $bdd->query($sql);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = array_column($data, 'label');   // ["CA mensuel"]
$values = array_column($data, 'value');   // [50033.00]

header('Content-Type: application/json; charset=utf-8');
echo json_encode(
    ['labels' => $labels, 'values' => $values],
    JSON_UNESCAPED_UNICODE
);
