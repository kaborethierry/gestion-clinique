// pages/Datatables/data_table_rendez_vous.js

$(document).ready(function () {
  // ➊ Initialisation du DataTable
  var tab = $('#datatable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '../api/modules/rendez_vous_data.php',
      type: 'POST'
    },
    columns: [
      { data: 0 },  // N°
      { data: 1 },  // Patient
      { data: 2 },  // Médecin
      { data: 3 },  // Date
      { data: 4 },  // Heure
      { data: 5 },  // Motif
      { data: 6 },  // Statut
      { data: 7 },  // Note

      // ➌ Bouton Modifier (toujours visible)
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function (_d, _t, row) {
          return `
            <button
              class="open-Modifier_RendezVous btn btn-warning"
              data-toggle="modal"
              data-target="#modifier_rendez_vous"
              data-id="${row[8]}"
              data-id-patient="${row[9]}"
              data-id-medecin="${row[10]}"
              data-date="${row[3]}"
              data-heure="${row[4]}"
              data-motif="${row[5]}"
              data-statut="${row[6]}"
              data-note="${row[7]}">
              <i class="fa fa-pencil"></i>
            </button>`;
        }
      },

      // ➍ Bouton Supprimer (toujours visible)
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function (_d, _t, row) {
          return `
            <button
              class="open-Supprimer_RendezVous btn btn-danger"
              data-id="${row[8]}">
              <i class="fa fa-trash"></i>
            </button>`;
        }
      },

      // ➎ Colonnes cachées pour les IDs
      { data: 8,  visible: false, searchable: false },  // id_rdv
      { data: 9,  visible: false, searchable: false },  // id_patient
      { data: 10, visible: false, searchable: false }   // id_medecin
    ],

    dom: 'Blfrtip',
    buttons: [
      { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'LEGAL' },
      'copyHtml5','excelHtml5','csvHtml5'
    ],

    columnDefs: [{
      targets: '_all',
      createdCell: function (td) {
        $(td).css('text-align', 'center');
      }
    }],

    order: [[0,'desc']],
    pageLength: 5,
    lengthMenu: [
      [5,10,25,50,100,-1],
      [5,10,25,50,100,"Tout"]
    ],

    language: {
      sProcessing:     "Traitement en cours...",
      sSearch:         "Rechercher:",
      sLengthMenu:     "Afficher _MENU_ éléments",
      sInfo:           "Affichage de l'élément _START_ à _END_ sur _TOTAL_ éléments",
      sInfoEmpty:      "Affichage de l'élément 0 à 0 sur 0 éléments",
      sInfoFiltered:   "(filtré de _MAX_ éléments au total)",
      sZeroRecords:    "Aucun élément à afficher",
      sEmptyTable:     "Aucune donnée disponible dans le tableau",
      oPaginate: {
        sFirst:    "Premier",
        sPrevious: "Précédent",
        sNext:     "Suivant",
        sLast:     "Dernier"
      },
      oAria: {
        sSortAscending:  ": trier la colonne par ordre croissant",
        sSortDescending: ": trier la colonne par ordre décroissant"
      }
    }
  });

  // ➏ Remplissage du modal Modifier
  $('#datatable').on('click', '.open-Modifier_RendezVous', function () {
    var btn = $(this);
    $('#id_rdv_modif').val(btn.data('id'));
    $('#id_patient_modif').val(btn.data('id-patient'));
    $('#id_medecin_modif').val(btn.data('id-medecin'));
    $('#date_rdv_modif').val(btn.data('date'));
    $('#heure_rdv_modif').val(btn.data('heure'));
    $('#motif_modif').val(btn.data('motif'));
    $('#statut_modif').val(btn.data('statut'));
    $('#note_modif').val(btn.data('note'));
  });

  // ➐ Suppression via SweetAlert
  $('#datatable').on('click', '.open-Supprimer_RendezVous', function () {
    var id = $(this).data('id');
    Swal.fire({
      title: 'Êtes-vous sûr ?',
      text:  "Voulez-vous vraiment supprimer ce rendez-vous ?",
      icon:  'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6b6c6c',
      confirmButtonText:  "Oui, supprimer",
      cancelButtonText:   "Annuler"
    }).then((res) => {
      if (res.isConfirmed) {
        window.location.href =
          '../api/modules/supprimer_rendez_vous.php?id_rdv=' + id;
      }
    });
  });
});
