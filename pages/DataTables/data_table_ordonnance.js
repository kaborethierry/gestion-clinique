// DataTables/data_table_ordonnance.js

$(function() {
  // 1) Réinitialiser uniquement lors de l'ajout
  $('#modal_ordonnance').on('show.bs.modal', function(e) {
    if (e.relatedTarget) {
      // Bouton "Nouvelle ordonnance" déclenche reset
      $(this).find('form')[0].reset();
      $('#table_meds tbody').empty();
      $('#id_ordonnance').val('');
    }
  });

  // 2) Initialisation de la DataTable
  var table = $('#datatable_ordonnance').DataTable({
    processing: true,
    serverSide: true,
    responsive: true,
    ajax: {
      url: '../api/modules/ordonnance_data.php',
      type: 'POST'
    },
    columns: [
      { data: 'DT_RowIndex',     className: 'text-center', orderable: false },
      { data: 'type_ordonnance', className: 'text-center' },
      { data: 'patient',         className: 'text-center' },
      { data: 'consult',         className: 'text-center' },
      { data: 'date_creation',   className: 'text-center' },
      { // Modifier
        data: null, orderable: false, searchable: false, className: 'text-center',
        render: function(_,__,r) {
          return `
            <button class="btn btn-warning btn-sm edit-ord" data-id="${r.id_ordonnance}">
              <i class="fa fa-pencil"></i>
            </button>`;
        }
      },
      { // Imprimer
        data: null, orderable: false, searchable: false, className: 'text-center',
        render: function(_,__,r) {
          return `
            <a href="../api/modules/fiche_ordonnance.php?id_ordonnance=${r.id_ordonnance}"
               target="_blank" class="btn btn-info btn-sm">
               <i class="fa fa-file-pdf-o"></i>
            </a>`;
        }
      },
      { // Supprimer
        data: null, orderable: false, searchable: false, className: 'text-center',
        render: function(_,__,r) {
          return `
            <button class="btn btn-danger btn-sm del-ord" data-id="${r.id_ordonnance}">
              <i class="fa fa-trash"></i>
            </button>`;
        }
      },
      { data: 'id_ordonnance', visible: false, searchable: false }
    ],
    order: [[0, 'desc']],
    lengthMenu: [[5, 10, 25], [5, 10, 25]],
    language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json' }
  });

  // 3) Ouvrir modal pour modification
  $('#datatable_ordonnance tbody').on('click', '.edit-ord', function() {
    var id = $(this).data('id');
    $.getJSON('../api/modules/ordonnance_data.php', { id_ordonnance: id })
      .done(function(resp) {
        var o = resp.data[0] || {};

        // Pré-remplir le formulaire
        $('#id_ordonnance').val(o.id_ordonnance);
        $('#type_ordonnance').val(o.type_ordonnance);
        $('#id_patient').val(o.id_patient);
        $('#id_consultation').val(o.id_consultation);
        $('#instructions').val(o.instructions || '');

        // Remplir les lignes de médicaments
        var tbody = $('#table_meds tbody').empty();
        (o.lignes || []).forEach(function(m) {
          tbody.append(`
            <tr>
              <td>
                <input type="text"
                       name="med_nom[]"
                       class="form-control med-nom"
                       value="${m.medicament}"
                       required>
              </td>
              <td>
                <input type="text"
                       name="med_pos[]"
                       class="form-control med-pos"
                       value="${m.posologie}"
                       required>
              </td>
              <td>
                <input type="text"
                       name="med_dur[]"
                       class="form-control med-dur"
                       value="${m.duree}"
                       required>
              </td>
              <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger remove-med">
                  <i class="fa fa-times"></i>
                </button>
              </td>
            </tr>`);
        });

        $('#modal_ordonnance').modal('show');
      });
  });

  // 4) Supprimer une ordonnance
  $('#datatable_ordonnance tbody').on('click', '.del-ord', function() {
    var id = $(this).data('id');
    Swal.fire({
      title: 'Supprimer cette ordonnance ?',
      text: 'Cette action est irréversible.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Oui, supprimer',
      cancelButtonText: 'Annuler'
    }).then(function(r) {
      if (r.isConfirmed) {
        window.location.href =
          '../api/modules/supprimer_ordonnance.php?id_ordonnance=' + id;
      }
    });
  });

  // 5) Soumettre le formulaire pour ajout ou modification
  $('#save_ordonnance').click(function() {
    var form = $('#form_ordonnance');
    form.attr('action',
      $('#id_ordonnance').val()
        ? '../api/modules/modifier_ordonnance.php'
        : '../api/modules/ajouter_ordonnance.php'
    );
    form.submit();
  });

  // 6) Ajouter une ligne de médicament
  $('#add_med').click(function() {
    $('#table_meds tbody').append(`
      <tr>
        <td>
          <input type="text"
                 name="med_nom[]"
                 class="form-control med-nom"
                 required>
        </td>
        <td>
          <input type="text"
                 name="med_pos[]"
                 class="form-control med-pos"
                 required>
        </td>
        <td>
          <input type="text"
                 name="med_dur[]"
                 class="form-control med-dur"
                 required>
        </td>
        <td class="text-center">
          <button type="button" class="btn btn-sm btn-danger remove-med">
            <i class="fa fa-times"></i>
          </button>
        </td>
      </tr>`);
  });

  // 7) Supprimer une ligne de médicament
  $('#table_meds').on('click', '.remove-med', function() {
    $(this).closest('tr').remove();
  });
});
