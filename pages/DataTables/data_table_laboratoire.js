// pages/Datatables/data_table_laboratoire.js
$(document).ready(function() {
  // 1) SweetAlert depuis l’URL
  function handleUrlAlerts() {
    const p = new URLSearchParams(window.location.search);
    if (p.has('success')) {
      const msgs = {
        ajout: 'Résultat enregistré avec succès !',
        modif: 'Résultat mis à jour avec succès !',
        supp:  'Résultat supprimé avec succès !'
      };
      Swal.fire({
        icon: 'success',
        title: msgs[p.get('success')] || 'Opération réussie !',
        confirmButtonText: 'OK',
        confirmButtonColor: '#28a745'
      });
      return;
    }
    if (p.has('error')) {
      Swal.fire({
        icon: 'error',
        title: 'Erreur',
        text: p.get('error'),
        confirmButtonText: 'OK',
        confirmButtonColor: '#dc3545'
      });
    }
  }
  handleUrlAlerts();

  // Helpers pour parser les dates MySQL
  function parseDate(d) {
    if (!d) return '-';
    const iso = d.replace(' ', 'T');
    const dt  = new Date(iso);
    if (isNaN(dt)) {
      const [datePart] = d.split(' ');
      const [y, m, j] = datePart.split('-');
      return `${j}/${m}/${y}`;
    }
    return dt.toLocaleDateString('fr-FR');
  }

  function parseDateTime(d) {
    if (!d) return '-';
    const iso = d.replace(' ', 'T');
    const dt  = new Date(iso);
    if (isNaN(dt)) {
      const [datePart, timePart] = d.split(' ');
      const [y, m, j] = datePart.split('-');
      const hhmm = timePart ? timePart.substr(0,5) : '';
      return `${j}/${m}/${y} ${hhmm}`;
    }
    return dt.toLocaleString('fr-FR', {
      day:    '2-digit',
      month:  'long',
      year:   'numeric',
      hour:   '2-digit',
      minute: '2-digit'
    });
  }

  // 2) DataTable
  const table = $('#table_laboratoire').DataTable({
    ajax: {
      url: '../api/modules/laboratoire_data.php',
      dataSrc: 'data',
      error(xhr, status, err) {
        console.error('Ajax Error', status, err);
        Swal.fire({
          icon: 'error',
          title: 'Chargement impossible',
          text: 'Erreur lors du chargement des données.',
          confirmButtonText: 'OK',
          confirmButtonColor: '#dc3545'
        });
      }
    },
    columns: [
      { data: 'id_examen',       className: 'text-center' },
      { data: null,              className: 'text-center',
        render: r => `${r.nom} ${r.prenom}` },
      { data: 'id_consultation', className: 'text-center' },
      { data: 'type_examen',     className: 'text-center' },
      { data: 'motif',           className: 'text-center' },
      { data: 'date_demande',    className: 'text-center', render: d => parseDate(d) },
      { data: 'date_resultat',   className: 'text-center', render: d => parseDateTime(d) },
      {
        data: 'id_examen',
        className: 'text-center',
        orderable: false,
        render: id => `
          <button class="btn btn-sm btn-success add-btn" data-id="${id}" title="Ajouter">
            <i class="fa fa-plus"></i>
          </button>`
      },
      {
        data: 'id_resultat',
        className: 'text-center',
        orderable: false,
        render: id => id
          ? `<button class="btn btn-sm btn-warning edit-btn" data-id="${id}" title="Modifier">
               <i class="fa fa-pencil"></i>
             </button>`
          : '-'
      },
      {
        data: 'id_resultat',
        className: 'text-center',
        orderable: false,
        render: id => id
          ? `<button class="btn btn-sm btn-danger delete-btn" data-id="${id}" title="Supprimer">
               <i class="fa fa-trash"></i>
             </button>`
          : '-'
      },
      {
        data: null,
        className: 'text-center',
        orderable: false,
        render: r => r.id_resultat
          ? `<a class="btn btn-sm btn-primary"
                 href="../api/modules/fiche_resultats.php?id_consultation=${r.id_consultation}"
                 target="_blank" title="Imprimer">
                 <i class="fa fa-print"></i>
               </a>`
          : '-'
      }
    ],
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

  // 3) Ajouter : remplir le <select> avec tous les examens disponibles
  $('#table_laboratoire').on('click', '.add-btn', function() {
    const row   = table.row($(this).closest('tr')).data();
    const sel   = $('#aj_id_examen').empty();
    const exams = row.examens_disponibles || [];

    exams.forEach(ex => {
      sel.append(`<option value="${ex.id_examen}">
        ${ex.type_examen} — ${ex.motif}
      </option>`);
    });

    $('#aj_contenu').val('');
    $('#aj_fichier').val('');
    $('#modal_ajouter_resultat').modal('show');
  });

  // 4) Modifier
  $('#table_laboratoire').on('click', '.edit-btn', function() {
    const idRes = $(this).data('id');
    $.getJSON('../api/modules/laboratoire_data.php', { id_resultat: idRes })
      .done(json => {
        const r = json.data[0];
        $('#mod_id_resultat').val(r.id_resultat);
        $('#mod_examen_info').val(`${r.nom} ${r.prenom} — ${r.type_examen}`);
        $('#mod_contenu').val(r.contenu_texte || '');
        $('#mod_fichier_actuel').html(
          r.fichier
            ? `<a href="../../uploads/resultats/${r.fichier}" target="_blank">${r.fichier}</a>`
            : '-'
        );
        $('#modal_modifier_resultat').modal('show');
      })
      .fail(() => {
        Swal.fire({
          icon: 'error',
          title: 'Erreur',
          text: 'Impossible de charger le résultat à modifier.',
          confirmButtonText: 'OK',
          confirmButtonColor: '#dc3545'
        });
      });
  });

  // 5) Supprimer
  $('#table_laboratoire').on('click', '.delete-btn', function() {
    const idRes = $(this).data('id');
    Swal.fire({
      icon: 'warning',
      title: 'Confirmer la suppression',
      text: 'Voulez-vous vraiment supprimer ce résultat ?',
      showCancelButton: true,
      confirmButtonText: 'Oui, supprimer',
      cancelButtonText: 'Annuler',
      confirmButtonColor: '#dc3545'
    }).then(res => {
      if (res.isConfirmed) {
        $('<form>', {
          method: 'POST',
          action: '../api/modules/supprimer_laboratoire.php'
        })
        .append($('<input>', {
          type: 'hidden',
          name: 'id_resultat',
          value: idRes
        }))
        .appendTo('body')
        .submit();
      }
    });
  });
});
