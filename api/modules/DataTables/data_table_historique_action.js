$(document).ready(function() {
    $('#tab').DataTable({
        "ajax": "api/modules/historique_action_data.php",
        "columns": [
            { "data": "id" },
            { "data": "username" },
            { "data": "nom_action" },
            { "data": "nom_table" },
            { "data": "id_concerne" },
            { "data": "ancienne_valeur" },
            { "data": "nouvelle_valeur" },
            { "data": "adresse_ip" },
            { "data": "date_heure_ajout" }
        ],
        "order": [[8, "desc"]],
        "language": {
            "url": "DataTables/French.json"  // Optionnel, pour la traduction si vous disposez d'un fichier French.json
        }
    });
});
