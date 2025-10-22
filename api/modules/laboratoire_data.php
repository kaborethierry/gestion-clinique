<?php
// api/modules/laboratoire_data.php

session_start();

// ➊ Contrôle d’accès : Super Administrateur, Medecin et Laborantin
$allowedRoles = ['Super Administrateur', 'Medecin', 'Laborantin'];
if (empty($_SESSION['id']) 
    || ! in_array($_SESSION['type_compte'], $allowedRoles, true)
) {
    header('Content-Type: application/json; charset=UTF-8', true, 403);
    echo json_encode(['error' => 'Accès refusé']);
    exit();
}

header('Content-Type: application/json; charset=UTF-8');

// Debug (à retirer en prod)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/connect_db_pdo.php';

$idRes = isset($_GET['id_resultat']) ? (int) $_GET['id_resultat'] : 0;

// 1. Requête principale
$sql = "
  SELECT
    el.id_examen,
    el.id_consultation,
    el.type_examen,
    el.motif,
    el.date_demande,
    r.id_resultat,
    r.contenu_texte,
    r.fichier,
    r.date_resultat,
    p.nom,
    p.prenom
  FROM examen_labo AS el
  JOIN consultation AS c 
    ON el.id_consultation = c.id_consultation
   AND c.supprimer = 'NON'
  JOIN patient AS p 
    ON c.id_patient = p.id
  LEFT JOIN resultat_examen AS r
    ON el.id_examen = r.id_examen
   AND r.supprimer = 'NON'
  WHERE el.supprimer = 'NON'
";

if ($idRes > 0) {
    $sql .= " AND r.id_resultat = :idr";
}

$sql .= " ORDER BY el.date_demande DESC";
$stmt = $bdd->prepare($sql);

if ($idRes > 0) {
    $stmt->bindValue(':idr', $idRes, PDO::PARAM_INT);
}

$stmt->execute();
$raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Charger examents disponibles par consultation
$cache  = [];
$result = [];

foreach ($raw as $row) {
    $idConsult = $row['id_consultation'];

    if (!isset($cache[$idConsult])) {
        $q = $bdd->prepare("
          SELECT id_examen, type_examen, motif
          FROM examen_labo
          WHERE id_consultation = ?
            AND supprimer       = 'NON'
          ORDER BY date_demande ASC
        ");
        $q->execute([$idConsult]);
        $cache[$idConsult] = $q->fetchAll(PDO::FETCH_ASSOC);
    }

    $row['examens_disponibles'] = $cache[$idConsult];
    $result[] = $row;
}

// 3. Retour JSON
echo json_encode(['data' => $result], JSON_UNESCAPED_UNICODE);
exit();
