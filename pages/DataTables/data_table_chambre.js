// DataTables/data_table_chambre.js

$(function () {
  var table = $('#datatable').DataTable({
    processing: true,
    serverSide: true,
    displayStart: 0, // Commencer à partir du premier enregistrement
    ajax: {
      url: '../api/modules/chambre_data.php',
      type: 'POST'
    },
    columns: [
      { data: 'DT_RowIndex', orderable: false },
      { data: 'numero_chambre' },
      { data: 'type_chambre' },
      { data: 'capacite' },
      { data: 'disponibilite' },
      { data: 'tarif_journalier' },
      { data: 'etage' },
      { data: 'description' },
      {
        data: null,
        orderable: false,
        className: 'text-center',
        render: function (row) {
          return `
            <button class="btn btn-sm btn-warning btn-edit" data-id="${row.id_chambre}">
              <i class="fa fa-pencil"></i>
            </button>
            <button class="btn btn-sm btn-danger btn-delete ml-1" data-id="${row.id_chambre}">
              <i class="fa fa-trash"></i>
            </button>
          `;
        }
      }
    ],
    order: [[1, 'asc']],
    language: {
      processing:     "Traitement en cours...",
      search:         "Rechercher :",
      lengthMenu:     "Afficher _MENU_ éléments",
      info:           "Affichage de l’élément _START_ à _END_ sur _TOTAL_ éléments",
      infoEmpty:      "Affichage de l’élément 0 à 0 sur 0 élément",
      infoFiltered:   "(filtré de _MAX_ éléments au total)",
      loadingRecords: "Chargement en cours...",
      zeroRecords:    "Aucun élément à afficher",
      emptyTable:     "Aucune donnée disponible dans le tableau",
      paginate: {
        first:    "Premier",
        previous: "Précédent",
        next:     "Suivant",
        last:     "Dernier"
      },
      aria: {
        sortAscending:  ": activer pour trier la colonne par ordre croissant",
        sortDescending: ": activer pour trier la colonne par ordre décroissant"
      }
    }
  });

  // Édition d’une chambre
  $('#datatable').on('click', '.btn-edit', function () {
    var id = $(this).data('id');
    $.ajax({
      url: '../api/modules/chambre_data.php',
      type: 'GET',
      data: { id_chambre: id },
      dataType: 'json',
      success: function (resp) {
        if (resp.data && resp.data.length) {
          var c = resp.data[0];
          $('#id_chambre_modif').val(c.id_chambre);
          $('#numero_chambre_modif').val(c.numero_chambre);
          $('#type_chambre_modif').val(c.type_chambre);
          $('#capacite_modif').val(c.capacite);
          $('#disponibilite_modif').val(c.disponibilite);
          $('#tarif_journalier_modif').val(c.tarif_journalier);
          $('#etage_modif').val(c.etage);
          $('#description_modif').val(c.description);
          $('#modifier_chambre').modal('show');
        } else {
          Swal.fire('Erreur', 'Impossible de charger les données de la chambre.', 'error');
        }
      },
      error: function () {
        Swal.fire('Erreur', 'Une erreur s’est produite lors de la récupération des données.', 'error');
      }
    });
  });

  // Suppression d’une chambre
  $('#datatable').on('click', '.btn-delete', function () {
    var id = $(this).data('id');
    Swal.fire({
      title: 'Supprimer cette chambre ?',
      text:  "Cette action est irréversible.",
      icon:  'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Oui, supprimer',
      cancelButtonText: 'Annuler'
    }).then(function (res) {
      if (res.isConfirmed) {
        window.location.href = 
          '../api/modules/supprimer_chambre.php?id_chambre=' + id;
      }
    });
  });
});
