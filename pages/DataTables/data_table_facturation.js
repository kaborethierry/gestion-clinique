// pages/Datatables/data_table_facturation.js
$(document).ready(function () {
  var table = $('#datatable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
          url: '../api/modules/facturation_data.php',
          type: 'POST'
      },
      columns: [
          { data: 0 },  // N°
          { data: 1 },  // Patient
          { data: 2 },  // Prestation
          { data: 3 },  // Montant
          { data: 4 },  // Taux
          { data: 5 },  // Part assurance
          { data: 6 },  // Reste
          { data: 7 },  // Total
          { data: 8 },  // Moyen
          { data: 9 },  // Référence
          { data: 10 }, // Date paiement
          { data: 11 }, // Différé

          // Modifier
          {
              data: null,
              defaultContent:
                  '<button class="open-Modifier_Fact btn btn-info btn-sm" data-toggle="modal" data-target="#modifier_fact">' +
                  '<i class="fa fa-pencil"></i></button>',
              orderable: false
          },

          // Imprimer
          {
              data: null,
              defaultContent:
                  '<button class="print-fact btn btn-secondary btn-sm"><i class="fa fa-print"></i></button>',
              orderable: false
          },

          // Supprimer
          {
              data: null,
              defaultContent:
                  '<button class="btn btn-danger btn-sm supprimer-fact"><i class="fa fa-trash"></i></button>',
              orderable: false
          },

          // Colonnes cachées
          { data: 12, visible: false }, // id_facture
          { data: 13, visible: false }  // id_patient
      ],
      dom: 'Blfrtip',
      buttons: [
          { extend: 'copyHtml5', text: 'Copier' },
          { extend: 'excelHtml5', text: 'Excel' },
          { extend: 'csvHtml5', text: 'CSV' },
          { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'LEGAL', text: 'PDF' }
      ],
      columnDefs: [{
          targets: '_all',
          createdCell: function (td) {
              $(td).css('text-align', 'center');
          }
      }],
      order: [[0, 'desc']],
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

  // Pré-remplir Modal Modifier
  $('#datatable tbody').on('click', '.open-Modifier_Fact', function () {
      var d = table.row($(this).closest('tr')).data();
      $('#id_fact_mod').val(d[12]);
      $('#prestation_mod').val(d[2]);
      $('#montant_mod').val(d[3]);
      $('#mp_mod').val(d[8]);
      $('#ref_mod').val(d[9]);
      // Transformer "DD/MM/YYYY HH:mm" en "YYYY-MM-DDTHH:mm"
      let dt = d[10].split(' ')[0].split('/').reverse().join('-') +
               'T' + d[10].split(' ')[1];
      $('#dpaie_mod').val(dt);
      $('#pd_mod').val(d[11]);
  });

  // Imprimer
  $('#datatable tbody').on('click', '.print-fact', function () {
      var d = table.row($(this).closest('tr')).data();
      window.open('../api/modules/print_facture.php?id_facture=' + d[12], '_blank');
  });

  // Supprimer
  $('#datatable tbody').on('click', '.supprimer-fact', function () {
      var d = table.row($(this).closest('tr')).data();
      Swal.fire({
          title: 'Supprimer cette facture ?',
          text: "Action irréversible.",
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
                  '../api/modules/supprimer_facturation.php?id_facture=' + d[12];
          }
      });
  });
});
