// pages/Datatables/data_table_hospitalisation.js
$(document).ready(function () {
  var table = $('#datatable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
          url: '../api/modules/hospitalisation_data.php',
          type: 'POST'
      },
      columns: [
          { data: 0 }, // N°
          { data: 1 }, // Patient
          { data: 2 }, // Médecin
          { data: 3 }, // Chambre/Lit
          { data: 4 }, // Date entrée
          { data: 5 }, // Date sortie
          { data: 6 }, // Statut

          // Modifier complet
          {
              data: null,
              defaultContent:
                  '<button class="open-Modifier_Hosp btn btn-info btn-sm" data-toggle="modal" data-target="#modifier_hosp">' +
                  '<i class="fa fa-pencil"></i></button>',
              orderable: false,
              searchable: false
          },

          // Libérer
          {
              data: null,
              defaultContent:
                  '<button class="open-Liberer_Hosp btn btn-warning btn-sm" data-toggle="modal" data-target="#modifier_hosp">' +
                  '<i class="fa fa-bed"></i></button>',
              orderable: false,
              searchable: false
          },

          // Supprimer
          {
              data: null,
              defaultContent:
                  '<button class="btn btn-danger btn-sm supprimer-hosp">' +
                  '<i class="fa fa-trash"></i></button>',
              orderable: false,
              searchable: false
          },

          // colonnes cachées
          { data: 9, visible: false, searchable: false },  // id_hosp
          { data: 7, visible: false, searchable: false },  // motif
          { data: 8, visible: false, searchable: false },  // observations
          { data: 10, visible: false, searchable: false }, // id_patient
          { data: 11, visible: false, searchable: false }, // id_medecin
          { data: 12, visible: false, searchable: false }  // id_lit
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
          [5, 10, 25, 50, -1],
          [5, 10, 25, 50, "Tout"]
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

  // Préremplissage du modal Modifier complet
  $('#datatable tbody').on('click', '.open-Modifier_Hosp', function () {
      var d = table.row($(this).closest('tr')).data();
      $('#id_hosp_mod').val(d[9]);
      $('#id_patient_mod').val(d[10]);
      $('#id_medecin_mod').val(d[11]);
      $('#id_lit_mod').val(d[12]);
      $('#date_entree_mod').val(d[4]);
      $('#motif_mod').val(d[7]);
      $('#observations_mod').val(d[8]);
      $('#date_sortie_mod').val(d[5]);
      $('#statut_mod').val(d[6]);
  });

  // Préremplissage du modal Libérer
  $('#datatable tbody').on('click', '.open-Liberer_Hosp', function () {
      var d = table.row($(this).closest('tr')).data();
      $('#id_hosp_mod').val(d[9]);
      $('#id_patient_mod').val(d[10]);
      $('#id_medecin_mod').val(d[11]);
      $('#id_lit_mod').val(d[12]);
      $('#date_entree_mod').val(d[4]);
      $('#motif_mod').val(d[7]);
      $('#observations_mod').val(d[8]);
      $('#date_sortie_mod').val('');
      $('#statut_mod').val('');
  });

  // Suppression via SweetAlert
  $('#datatable tbody').on('click', '.supprimer-hosp', function () {
      var d = table.row($(this).closest('tr')).data();
      Swal.fire({
          title: 'Confirmer la suppression ?',
          text: "Cette action est irréversible.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Oui, supprimer',
          cancelButtonText: 'Annuler',
          customClass: {
              confirmButton: 'btn btn-danger mx-2',
              cancelButton: 'btn btn-secondary mx-2'
          },
          buttonsStyling: false
      }).then((res) => {
          if (res.isConfirmed) {
              window.location.href =
                  '../api/modules/supprimer_hospitalisation.php?id_hosp=' + d[9];
          }
      });
  });
});
