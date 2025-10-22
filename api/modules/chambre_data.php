<?php
// api/modules/chambre_data.php

// Affichage des erreurs en dev (désactiver en prod)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// ➊ Contrôle d’accès : Super Administrateur, Secretaire et Medecin
$allowedRoles = ['Super Administrateur', 'Secretaire', 'Medecin'];
if (empty($_SESSION['id'])
    || ! in_array($_SESSION['type_compte'], $allowedRoles, true)
) {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

require __DIR__ . '/connect_db_pdo.php';
header('Content-Type: application/json');

// ➋ Récupération d’une chambre pour le modal (edit)
if (isset($_GET['id_chambre'])) {
    $id = (int) $_GET['id_chambre'];
    $stmt = $bdd->prepare("
        SELECT *
        FROM chambre
        WHERE id_chambre = ? 
          AND supprimer = 'NON'
    ");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['data' => $row ? [$row] : []], JSON_UNESCAPED_UNICODE);
    exit();
}

// ➌ Paramètres DataTables
$draw   = intval($_POST['draw']   ?? 0);
$start  = intval($_POST['start']  ?? 0);
$length = intval($_POST['length'] ?? 10);
$search = trim($_POST['search']['value'] ?? '');

// ➍ Comptage total
$totalStmt = $bdd->query("SELECT COUNT(*) FROM chambre WHERE supprimer = 'NON'");
$recordsTotal = $totalStmt->fetchColumn();

// ➎ Filtre recherche
$where  = " WHERE supprimer = 'NON'";
$params = [];
if ($search !== '') {
    $where .= " AND (
        numero_chambre    LIKE :search OR
        type_chambre      LIKE :search OR
        disponibilite     LIKE :search OR
        description       LIKE :search
    )";
    $params[':search'] = "%$search%";
}

// ➏ Comptage après filtre
$filteredStmt = $bdd->prepare("SELECT COUNT(*) FROM chambre $where");
$filteredStmt->execute($params);
$recordsFiltered = $filteredStmt->fetchColumn();

// ➐ Tri et pagination
$columns = [
    '', 'numero_chambre', 'type_chambre', 'capacite',
    'disponibilite', 'tarif_journalier', 'etage', 'description'
];
$orderIdx  = intval($_POST['order'][0]['column'] ?? 1);
$orderDir  = (($_POST['order'][0]['dir'] ?? 'asc') === 'desc') ? 'DESC' : 'ASC';
$orderCol  = $columns[$orderIdx] ?? 'numero_chambre';

$orderClause = " ORDER BY $orderCol $orderDir";
$limitClause = " LIMIT $start, $length";

// ➑ Récupération des données
$data = [];
$stmt = $bdd->prepare("
    SELECT 
      id_chambre, numero_chambre, type_chambre, capacite,
      disponibilite, tarif_journalier, etage, description
    FROM chambre
    $where
    $orderClause
    $limitClause
");
$stmt->execute($params);

$index = $start + 1;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $row['DT_RowIndex'] = $index++;
    $data[] = $row;
}

// ➒ Retour JSON
echo json_encode([
    'draw'            => $draw,
    'recordsTotal'    => intval($recordsTotal),
    'recordsFiltered' => intval($recordsFiltered),
    'data'            => $data
], JSON_UNESCAPED_UNICODE);
