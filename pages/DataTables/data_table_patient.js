// DataTables/data_table_patient.js

$(document).ready(function () {
  // Récupération du rôle injecté dans patient.php
  var role = window.currentUserRole || '';
  var canEdit = ['Super Administrateur', 'Secretaire', 'Medecin'].includes(role);

  // Initialisation du DataTable
  var table = $('#datatable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '../api/modules/patient_data.php',
      type: 'POST'
    },
    columns: [
      { data: 0 },    // N° de ligne
      { data: 1 },    // Numéro de dossier
      { data: 2 },    // Nom
      { data: 3 },    // Prénom
      { data: 4 },    // Sexe
      { data: 5 },    // Date de naissance
      { data: 6 },    // Téléphone
      { data: 7 },    // Tél. secondaire
      { data: 8 },    // Email
      { data: 9 },    // Ville
      { data: 10 },   // Quartier
      { data: 11 },   // Groupe sanguin
      { data: 12 },   // Poids (kg)
      { data: 13 },   // Tension artérielle
      { data: 14 },   // Date d'enregistrement
      { data: 15 },   // Statut

      // Modifier
      {
        data: null,
        visible: canEdit,
        orderable: false,
        searchable: false,
        defaultContent:
          '<button class="btn btn-warning btn-sm open-Modifier_Patient" ' +
          'data-toggle="modal" data-target="#modifier_patient">' +
          '<i class="fa fa-pencil"></i></button>'
      },

      // Dossier patient
      {
        data: null,
        visible: canEdit,
        orderable: false,
        searchable: false,
        defaultContent:
          '<button class="btn btn-info btn-sm open-Dossier_Patient">' +
          '<i class="fa fa-folder-open"></i></button>'
      },

      // Supprimer
      {
        data: null,
        visible: canEdit,
        orderable: false,
        searchable: false,
        defaultContent:
          '<button class="btn btn-danger btn-sm supprimer-patient">' +
          '<i class="fa fa-trash"></i></button>'
      },

      // ID patient caché
      { data: 16, visible: false, searchable: false }
    ],
    dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
         "<'row'<'col-sm-12'tr>>" +
         "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
    buttons: [
      { 
        extend: 'pdfHtml5', 
        orientation: 'landscape', 
        pageSize: 'LEGAL',
        text: 'PDF',
        title: 'Liste des patients',
        exportOptions: {
          columns: ':visible'
        }
      },
      { 
        extend: 'excelHtml5',
        text: 'Excel',
        title: 'Liste des patients',
        exportOptions: {
          columns: ':visible'
        }
      },
      { 
        extend: 'csvHtml5',
        text: 'CSV',
        title: 'Liste des patients',
        exportOptions: {
          columns: ':visible'
        }
      },
      { 
        extend: 'copyHtml5',
        text: 'Copier',
        exportOptions: {
          columns: ':visible'
        }
      }
    ],
    order: [[0, 'desc']],
    pageLength: 10,
    lengthMenu: [
      [5, 10, 25, 50, 100, -1],
      [5, 10, 25, 50, 100, "Tous"]
    ],
    columnDefs: [{
      targets: '_all',
      createdCell: function (td) {
        $(td).css('text-align', 'center');
      }
    }],
    language: {
      "processing":     "Traitement en cours...",
      "search":         "Rechercher:",
      "lengthMenu":     "Afficher _MENU_ éléments",
      "info":           "Affichage de _START_ à _END_ sur _TOTAL_ éléments",
      "infoEmpty":      "Affichage de 0 à 0 sur 0 éléments",
      "infoFiltered":   "(filtrés sur _MAX_ éléments au total)",
      "infoPostFix":    "",
      "loadingRecords": "Chargement en cours...",
      "zeroRecords":    "Aucun élément à afficher",
      "emptyTable":     "Aucune donnée disponible dans le tableau",
      "paginate": {
        "first":      "Premier",
        "previous":   "Précédent",
        "next":       "Suivant",
        "last":       "Dernier"
      },
      "aria": {
        "sortAscending":  ": activer pour trier la colonne par ordre croissant",
        "sortDescending": ": activer pour trier la colonne par ordre décroissant"
      },
      "buttons": {
        "copyTitle": "Copié dans le presse-papier",
        "copySuccess": {
          "_": "%d lignes copiées",
          "1": "1 ligne copiée"
        }
      }
    },
    responsive: true
  });

  // Préremplissage du modal Modifier Patient
  $('#datatable tbody').on('click', '.open-Modifier_Patient', function () {
    var data = table.row($(this).closest('tr')).data();
    var id = data[16];

    // Infos de base
    $('#id_patient_modif').val(id);
    $('#numero_dossier_modif').val(data[1]);
    $('#nom_modif').val(data[2]);
    $('#prenom_modif').val(data[3]);
    $('#sexe_modif').val(data[4]).trigger('change');
    $('#date_naissance_modif').val(convertDate(data[5]));
    $('#lieu_naissance_modif').val(data[24]);
    $('#nationalite_modif').val(data[25]);

    // État civil et profession
    $('#etat_civil_modif').val(data[26]).trigger('change');
    $('#profession_modif').val(data[27]);

    // Adresse
    $('#adresse_modif').val(data[28]);
    $('#ville_modif').val(data[9]);
    $('#quartier_modif').val(data[10]);

    // Contacts
    $('#telephone_modif').val(data[6]);
    $('#telephone_secondaire_modif').val(data[7]);
    $('#email_modif').val(data[8]);

    // Santé
    $('#groupe_sanguin_modif').val(data[11]).trigger('change');
    $('#poids_modif').val(data[12]);
    $('#tension_modif').val(data[13]);
    $('#situation_handicap_modif').val(data[29]).trigger('change');
    $('#allergie_modif').val(data[30]);
    $('#antecedents_medicaux_modif').val(data[31]);

    // Contact urgence
    $('#personne_contact_nom_modif').val(data[32]);
    $('#personne_contact_lien_modif').val(data[33]).trigger('change');
    $('#personne_contact_tel_modif').val(data[34]);

    // Statut assurance
    var assuranceStatut = data[17];
    // Décoche tous les radios
    $('input[name="assurance_statut"]').prop('checked', false);
    // Coche la bonne option
    $('input[name="assurance_statut"][value="' + assuranceStatut + '"]')
      .prop('checked', true);

    // Affiche ou masque la section détails assurance
    if (assuranceStatut === 'Assuré') {
      $('#section_assurance_modif').slideDown();
      $('#assurance_compagnie_modif').val(data[18]).trigger('change');
      $('#numero_police_modif').val(data[19]);
      $('#taux_couverture_modif').val(data[20]);
      $('#type_couverture_modif').val(data[21]).trigger('change');
      $('#date_debut_couverture_modif').val(data[22]);
      $('#date_fin_couverture_modif').val(data[23]);
    } else {
      $('#section_assurance_modif').slideUp();
    }
  });

  // Ouverture du dossier patient
  $('#datatable tbody').on('click', '.open-Dossier_Patient', function () {
    var data = table.row($(this).closest('tr')).data();
    window.location.href =
      '../pages/dossier_patient.php?id_patient=' + data[16];
  });

  // Suppression via SweetAlert
  $('#datatable tbody').on('click', '.supprimer-patient', function () {
    var data = table.row($(this).closest('tr')).data();
    Swal.fire({
      title: 'Êtes-vous sûr ?',
      text:  "Cette action est irréversible.",
      icon:  'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: "Oui, supprimer",
      cancelButtonText:  "Annuler",
      customClass: {
        confirmButton: 'btn btn-danger mx-2',
        cancelButton:  'btn btn-secondary mx-2'
      },
      buttonsStyling: false
    }).then((res) => {
      if (res.isConfirmed) {
        window.location.href =
          '../api/modules/supprimer_patient.php?id_patient=' + data[16];
      }
    });
  });

  // Conversion jj/mm/aaaa → aaaa-mm-jj pour input[type=date]
  function convertDate(str) {
    if (!str || str.indexOf('/') === -1) return str;
    var parts = str.split('/');
    return parts[2] + '-' + parts[1] + '-' + parts[0];
  }

  // Toggle section assurance dans le modal de modification
  $('input[name="assurance_statut"]').on('change', function() {
    if ($(this).val() === 'Assuré') {
      $('#section_assurance_modif').slideDown();
    } else {
      $('#section_assurance_modif').slideUp();
    }
  });
});