<?php
// api/modules/facturation_data.php

// Affichage des erreurs en dev (désactiver en prod)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// ➊ Contrôle d’accès : Super Administrateur et Comptable
$allowedRoles = ['Super Administrateur', 'Comptable'];
if (empty($_SESSION['id'])
    || ! in_array($_SESSION['type_compte'], $allowedRoles, true)
) {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

// ➋ Sous-requête DataTables : factures + patient
$table = <<<SQL
(
  SELECT
    ROW_NUMBER() OVER (ORDER BY f.id_facture DESC) AS num_row,
    f.id_facture                                    AS id_facture,
    CONCAT(p.numero_dossier,' – ',p.nom,' ',p.prenom) AS patient,
    f.type_prestation                               AS prestation,
    f.montant                                       AS montant,
    f.taux_couverture                                AS taux,
    f.part_assurance                                AS assurance,
    f.reste_a_charge                                AS reste,
    f.montant_total                                 AS total,
    f.moyen_paiement                                AS paiement,
    COALESCE(f.reference_paiement,'–')               AS reference,
    DATE_FORMAT(f.date_paiement,'%d/%m/%Y %H:%i')    AS date_paiement,
    f.paiement_differe                              AS differe,
    f.id_patient                                    AS id_patient
  FROM facturation f
  JOIN patient p ON f.id_patient = p.id
  WHERE f.supprimer = 'NON'
) AS tmp
SQL;

$primaryKey = 'id_facture';

$columns = [
  ['db' => 'num_row',       'dt' => 0],
  ['db' => 'patient',       'dt' => 1],
  ['db' => 'prestation',    'dt' => 2],
  ['db' => 'montant',       'dt' => 3],
  ['db' => 'taux',          'dt' => 4],
  ['db' => 'assurance',     'dt' => 5],
  ['db' => 'reste',         'dt' => 6],
  ['db' => 'total',         'dt' => 7],
  ['db' => 'paiement',      'dt' => 8],
  ['db' => 'reference',     'dt' => 9],
  ['db' => 'date_paiement','dt' => 10],
  ['db' => 'differe',       'dt' => 11],
  ['db' => 'id_facture',    'dt' => 12],  // caché
  ['db' => 'id_patient',    'dt' => 13]   // caché
];

require __DIR__ . '/connect_db_data.php';
require __DIR__ . '/DataTables/examples/server_side/scripts/ssp.class.php';

// ➌ Envoi de la réponse JSON à DataTables
echo json_encode(
    SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns)
);
