<?php
// api/modules/lit_data.php

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

// ➋ Sous-requête SQL pour DataTables (lits + numéro de chambre)
$table = <<<EOT
(
    SELECT
        ROW_NUMBER() OVER (ORDER BY l.id_lit DESC)           AS num_row,
        l.id_lit                                            AS id,
        c.numero_chambre                                    AS numero_chambre,
        l.numero_lit                                        AS numero_lit,
        l.statut                                            AS statut,
        DATE_FORMAT(l.date_creation, '%d/%m/%Y %H:%i')      AS date_creation
    FROM lit l
    JOIN chambre c ON c.id_chambre = l.id_chambre
    WHERE l.supprimer = 'NON'
) tmp
EOT;

$primaryKey = 'id';

// Colonnes mappées pour DataTables (indexes JS)
$columns = [
    ['db' => 'num_row',        'dt' => 0],
    ['db' => 'numero_chambre', 'dt' => 1],
    ['db' => 'numero_lit',     'dt' => 2],
    ['db' => 'statut',         'dt' => 3],
    ['db' => 'date_creation',  'dt' => 4],
    // Colonnes actions et ID caché
    ['db' => 'id',             'dt' => 7]
];

require __DIR__ . '/connect_db_data.php';
require __DIR__ . '/DataTables/examples/server_side/scripts/ssp.class.php';

// Envoi de la réponse JSON
echo json_encode(
    SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns)
);
