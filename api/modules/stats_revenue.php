<?php
// api/modules/stats_revenue.php

session_start();
if (empty($_SESSION['id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit();
}

require __DIR__ . '/connect_db_pdo.php';

$sql = "
  SELECT
    DATE_FORMAT(date_paiement, '%M %Y') AS label,
    ROUND(SUM(montant_total), 2) AS value
  FROM facturation
  WHERE supprimer = 'NON'
  GROUP BY YEAR(date_paiement), MONTH(date_paiement)
  ORDER BY MIN(date_paiement)
";

$stmt = $bdd->query($sql);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = array_column($data, 'label');
$values = array_column($data, 'value');

header('Content-Type: application/json');
echo json_encode(['labels' => $labels, 'values' => $values]);
