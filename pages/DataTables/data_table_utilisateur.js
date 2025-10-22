$(document).ready(function () {
    var tab = $('#datatable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: '../api/modules/utilisateur_data.php',
        type: 'POST'
      },
      columns: [
        { data: 0 },    // Numéro
        { data: 1 },    // Nom
        { data: 2 },    // Prénom(s)
        { data: 3 },    // Nom d'utilisateur
        { data: 4 },    // Email
        { data: 5 },    // Téléphone
        { data: 6 },    // Adresse
        { data: 7 },    // Poste
        { data: 8 },    // Type de compte
        { data: 9 },    // Date d'inscription
        { data: 10 },   // Statut
        {
          data: null,
          defaultContent: '<button class="open-Modifier_Utilisateur btn btn-warning" data-toggle="modal" data-target="#modifier_utilisateur"><i class="fa fa-pencil"></i></button>',
          orderable: false
        },
        {
          data: null,
          defaultContent: '<button class="btn btn-danger" id="supprimer"><i class="fa fa-trash"></i></button>',
          orderable: false
        },
        {
          data: 13, // ID utilisateur
          visible: false,
          searchable: false
        }
      ],
      dom: 'Blfrtip',
      buttons: [
        { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'LEGAL' },
        'copyHtml5', 'excelHtml5', 'csvHtml5'
      ],
      columnDefs: [
        {
          targets: '_all',
          createdCell: function (td) {
            $(td).css('text-align', 'center');
          }
        }
      ],
      order: [[0, 'desc']],
      pageLength: 5,
      lengthMenu: [
        [1, 2, 3, 4, 5, 10, 25, 50, 100, -1],
        [1, 2, 3, 4, 5, 10, 25, 50, 100, "Tout"]
      ],
      language: {
        sProcessing:     "Traitement en cours...",
        sSearch:         "Rechercher:",
        sLengthMenu:     "Afficher _MENU_ éléments",
        sInfo:           "Affichage de l'élément _START_ à _END_ sur _TOTAL_ éléments",
        sInfoEmpty:      "Affichage de l'élément 0 à 0 sur 0 éléments",
        sInfoFiltered:   "(filtré de _MAX_ éléments au total)",
        sLoadingRecords: "Chargement en cours...",
        sZeroRecords:    "Aucun élément à afficher",
        sEmptyTable:     "Aucune donnée disponible dans le tableau",
        oPaginate: {
          sFirst:    "Premier",
          sPrevious: "Précédent",
          sNext:     "Suivant",
          sLast:     "Dernier"
        },
        oAria: {
          sSortAscending:  ": activer pour trier la colonne par ordre croissant",
          sSortDescending: ": activer pour trier la colonne par ordre décroissant"
        }
      }
    });
  
    // Remplissage du modal Modifier avec l’ordre exact des nouveaux champs
    $('#datatable tbody').on('click', '.open-Modifier_Utilisateur', function () {
      var data = tab.row($(this).parents('tr')).data();
      $('#id_utilisateur').val(data[13]);       // ID utilisateur
      $('#nom_modif').val(data[1]);             // Nom
      $('#prenom_modif').val(data[2]);          // Prénom
      $('#email_modif').val(data[4]);           // Email
      $('#telephone_modif').val(data[5]);       // Téléphone
      $('#adresse_modif').val(data[6]);         // Adresse
      $('#poste_modif').val(data[7]);           // Poste
      $('#username_modif').val(data[3]);        // Nom d'utilisateur
      $('#type_compte_modif').val(data[8]);     // Type de compte
      $('#statut_modif').val(data[10]);         // Statut
      $('#password_modif').val('');             // Mot de passe (vidé à chaque fois)
    });
  
    // Gestion du bouton Supprimer via SweetAlert
    $('#datatable tbody').on('click', '#supprimer', function () {
      var data = tab.row($(this).parents('tr')).data();
      var id_utilisateur = data[13];
  
      Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: "Voulez-vous vraiment supprimer cet utilisateur ?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6b6c6c',
        confirmButtonText: "Oui, supprimer",
        cancelButtonText: "Annuler"
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = './../api/modules/supprimer_utilisateur.php?id_utilisateur=' + id_utilisateur;
        }
      });
    });
  });
  