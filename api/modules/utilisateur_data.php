<?php
session_start();
if (empty($_SESSION['id']) || ($_SESSION['type_compte'] != "Super Administrateur")) {
    session_unset();
    session_destroy();
    header('Location:./../index.php?erreur=3');
    exit();
} else {
    $table = <<<EOT
    (
        SELECT 
            ROW_NUMBER() OVER(ORDER BY id_utilisateur) AS num_row,
            id_utilisateur AS id,
            nom,
            prenom,
            username,
            email,
            telephone,
            adresse,
            poste,
            type_compte,
            date_inscription,
            statut
        FROM utilisateur
    ) tem
EOT;

    $primaryKey = 'id';

    $columns = array(
        array('db' => 'num_row',        'dt' => 0),
        array('db' => 'nom',            'dt' => 1),
        array('db' => 'prenom',         'dt' => 2),
        array('db' => 'username',       'dt' => 3),
        array('db' => 'email',          'dt' => 4),
        array('db' => 'telephone',      'dt' => 5),
        array('db' => 'adresse',        'dt' => 6),
        array('db' => 'poste',          'dt' => 7),
        array('db' => 'type_compte',    'dt' => 8),
        array('db' => 'date_inscription','dt' => 9),
        array('db' => 'statut',         'dt' => 10),
        // Nous n'envoyons pas les colonnes Modifier/Supprimer depuis le serveur ; 
        // la colonne cachée pour l'ID se trouve à l'index 13.
        array('db' => 'id',             'dt' => 13)
    );

    include('connect_db_data.php');
    require('DataTables/examples/server_side/scripts/ssp.class.php');
    echo json_encode(
        SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns)
    );
}
?>
