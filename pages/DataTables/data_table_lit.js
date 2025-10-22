// pages/Datatables/data_table_lit.js
$(document).ready(function () {
  var tab = $('#datatable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
          url: '../api/modules/lit_data.php',
          type: 'POST'
      },
      columns: [
          { data: 0 }, // Numéro
          { data: 1 }, // Chambre n°
          { data: 2 }, // Numéro du lit
          { data: 3 }, // Statut
          { data: 4 }, // Date de création
          {
              data: null,
              defaultContent:
                  '<button class="open-Modifier_Lit btn btn-warning" ' +
                  'data-toggle="modal" data-target="#modifier_lit">' +
                  '<i class="fa fa-pencil"></i></button>',
              orderable: false,
              searchable: false
          },
          {
              data: null,
              defaultContent:
                  '<button class="btn btn-danger" id="supprimer-lit">' +
                  '<i class="fa fa-trash"></i></button>',
              orderable: false,
              searchable: false
          },
          {
              data: 7, // ID lit
              visible: false,
              searchable: false
          }
      ],
      dom: 'Blfrtip',
      buttons: [
          { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'LEGAL', text: 'PDF' },
          { extend: 'copyHtml5', text: 'Copier' },
          { extend: 'excelHtml5', text: 'Excel' },
          { extend: 'csvHtml5', text: 'CSV' }
      ],
      columnDefs: [{
          targets: '_all',
          createdCell: function (td) {
              $(td).css('text-align', 'center');
          }
      }],
      order: [[0, 'desc']],
      pageLength: 5,
      lengthMenu: [
          [5, 10, 25, 50, 100, -1],
          [5, 10, 25, 50, 100, "Tout"]
      ],
      language: {
          processing:     "Traitement en cours...",
          search:         "Rechercher&nbsp;:",
          lengthMenu:     "Afficher _MENU_ éléments",
          info:           "Affichage de l'élément _START_ à _END_ sur _TOTAL_ éléments",
          infoEmpty:      "Affichage de l'élément 0 à 0 sur 0 élément",
          infoFiltered:   "(filtré à partir de _MAX_ éléments au total)",
          loadingRecords: "Chargement en cours...",
          zeroRecords:    "Aucun élément à afficher",
          emptyTable:     "Aucune donnée disponible dans le tableau",
          paginate: {
              first:      "Premier",
              previous:   "Précédent",
              next:       "Suivant",
              last:       "Dernier"
          },
          aria: {
              sortAscending:  ": activer pour trier la colonne par ordre croissant",
              sortDescending: ": activer pour trier la colonne par ordre décroissant"
          },
          buttons: {
              copy: "Copier",
              excel: "Exporter Excel",
              csv: "Exporter CSV",
              pdf: "Exporter PDF",
              print: "Imprimer"
          }
      }
  });

  // Préremplissage du modal Modifier Lit
  $('#datatable tbody').on('click', '.open-Modifier_Lit', function () {
      var data = tab.row($(this).closest('tr')).data();
      $('#id_lit_modif').val(data[7]);      // ID lit
      $('#id_chambre_modif').val(data[1]);  // Chambre n°
      $('#numero_lit_modif').val(data[2]);  // Numéro lit
      $('#statut_modif').val(data[3]);      // Statut
  });

  // Gestion du bouton Supprimer via SweetAlert
  $('#datatable tbody').on('click', '#supprimer-lit', function () {
      var data = tab.row($(this).closest('tr')).data();
      var id_lit = data[7];

      Swal.fire({
          title: 'Êtes-vous sûr ?',
          text: "Cette action est irréversible.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#6b6c6c',
          confirmButtonText: "Oui, supprimer",
          cancelButtonText: "Annuler",
          customClass: {
              confirmButton: 'btn btn-danger mx-2',
              cancelButton: 'btn btn-secondary mx-2'
          },
          buttonsStyling: false
      }).then((result) => {
          if (result.isConfirmed) {
              window.location.href =
                  '../api/modules/supprimer_lit.php?id_lit=' + id_lit;
          }
      });
  });
});
