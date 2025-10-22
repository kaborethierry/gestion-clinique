<?php
// api/modules/stats_patients_age.php

session_start();
if (empty($_SESSION['id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit();
}

require __DIR__ . '/connect_db_pdo.php';

$sql = "
  SELECT
    CASE
      WHEN TIMESTAMPDIFF(YEAR, date_naissance, CURDATE()) <= 18 THEN '0-18'
      WHEN TIMESTAMPDIFF(YEAR, date_naissance, CURDATE()) <= 35 THEN '19-35'
      WHEN TIMESTAMPDIFF(YEAR, date_naissance, CURDATE()) <= 60 THEN '36-60'
      ELSE '60+'
    END AS label,
    COUNT(*) AS value
  FROM patient
  WHERE supprimer = 'NON'
  GROUP BY label
  ORDER BY FIELD(label, '0-18','19-35','36-60','60+')
";

$stmt = $bdd->query($sql);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = array_column($data, 'label');
$values = array_column($data, 'value');

header('Content-Type: application/json');
echo json_encode(['labels' => $labels, 'values' => $values]);
