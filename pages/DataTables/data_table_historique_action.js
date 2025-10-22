// DataTables/data_table_historique_action.js

$(document).ready(function() {
  $('#datatable').DataTable({
    ajax: '../api/modules/historique_action_data.php',
    columns: [
      { data: 'id',            className: 'text-center' },
      { data: 'adresse_ip',    className: 'text-center' },
      {
        data: 'date_heure_ajout',
        className: 'text-center',
        render: function(data) {
          if (!data) return '-';
          var parts     = data.split(' ');
          var dateParts = parts[0].split('-');
          var timeParts = parts[1].split(':');
          var dt = new Date(
            parseInt(dateParts[0],10),
            parseInt(dateParts[1],10)-1,
            parseInt(dateParts[2],10),
            parseInt(timeParts[0],10),
            parseInt(timeParts[1],10),
            parseInt(timeParts[2],10)
          );
          return dt.toLocaleString('fr-FR', {
            day:    '2-digit',
            month:  'long',
            year:   'numeric',
            hour:   '2-digit',
            minute: '2-digit',
            second: '2-digit'
          });
        }
      },
      { data: 'username',      className: 'text-center' },
      { data: 'nom_action',    className: 'text-center' },
      { data: 'nom_table',     className: 'text-center' },
      { data: 'id_concerne',   className: 'text-center' },
      {
        data: 'ancienne_valeur',
        render: function(txt) {
          return txt
            ? '<pre style="white-space: pre-wrap;">'+ txt +'</pre>'
            : '-';
        }
      },
      {
        data: 'nouvelle_valeur',
        render: function(txt) {
          return txt
            ? '<pre style="white-space: pre-wrap;">'+ txt +'</pre>'
            : '-';
        }
      }
    ],
    order: [[2, 'desc']],  // tri par date_heure_ajout
    language: {
      processing:     "Traitement en cours...",
      search:         "Rechercher :",
      lengthMenu:     "Afficher _MENU_ entrées",
      info:           "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
      infoEmpty:      "Affichage de 0 à 0 sur 0 entrées",
      infoFiltered:   "(filtré de _MAX_ au total)",
      loadingRecords: "Chargement en cours...",
      zeroRecords:    "Aucun enregistrement trouvé",
      emptyTable:     "Aucune donnée disponible",
      paginate: {
        first:    "Premier",
        previous: "Précédent",
        next:     "Suivant",
        last:     "Dernier"
      },
      aria: {
        sortAscending:  ": activer pour trier par ordre croissant",
        sortDescending: ": activer pour trier par ordre décroissant"
      }
    }
  });
});
