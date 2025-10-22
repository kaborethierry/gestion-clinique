// DataTables/data_table_consultation.js

$(document).ready(function () {
  // 1) Initialisation de DataTables
  var table = $('#datatable').DataTable({
    processing:  true,
    serverSide:  true,
    responsive:  true,
    ajax: {
      url:      '../api/modules/consultation_data.php',
      type:     'POST',
      dataType: 'json',
      error: function(xhr, status, error) {
        console.error('🚨 consultation_data AJAX error:', status, error);
        console.error('Response:', xhr.responseText);
      }
    },
    columns: [
      { data: 'DT_RowIndex',            className: 'text-center', orderable: false },
      { data: 'patient',                className: 'text-center' },
      { data: 'medecin',                className: 'text-center' },
      { data: 'date_consultation',      className: 'text-center' },
      { data: 'motif',                  className: 'text-center' },
      { data: 'symptomes',              className: 'text-center' },
      { data: 'diagnostic',             className: 'text-center' },
      { data: 'observations',           className: 'text-center' },
      { data: 'tension_arterielle',     className: 'text-center' },
      { data: 'temperature',            className: 'text-center' },
      { data: 'poids',                  className: 'text-center' },
      { data: 'taille',                 className: 'text-center' },
      { data: 'frequence_cardiaque',    className: 'text-center' },
      { data: 'imc',                    className: 'text-center' },
      { data: 'glycemie',               className: 'text-center' },
      { data: 'frequence_respiratoire', className: 'text-center' },
      { data: 'saturation_oxygene',     className: 'text-center' },
      {
        data: null, className: 'text-center', orderable: false,
        defaultContent:
          '<button class="open-Details_Consultation btn btn-secondary"><i class="fa fa-info"></i></button>'
      },
      {
        data: null, className: 'text-center', orderable: false,
        defaultContent:
          '<button class="open-Modifier_Consultation btn btn-warning"><i class="fa fa-pencil"></i></button>'
      },
      {
        data: null, className: 'text-center', orderable: false,
        defaultContent:
          '<a class="btn btn-info print-fiche" target="_blank"><i class="fa fa-file-pdf-o"></i></a>'
      },
      {
        data: null, className: 'text-center', orderable: false,
        defaultContent:
          '<a class="btn btn-success print-ordon" target="_blank"><i class="fa fa-file-text-o"></i></a>'
      },
      {
        data: null, className: 'text-center', orderable: false,
        defaultContent:
          '<button class="open-Resultats_Consultation btn btn-primary"><i class="fa fa-flask"></i></button>'
      },
      {
        data: null, className: 'text-center', orderable: false,
        defaultContent:
          '<button class="supprimer-consultation btn btn-danger"><i class="fa fa-trash"></i></button>'
      },
      { data: 'id_consultation', visible: false, searchable: false }
    ],
    dom:    'Blfrtip',
    buttons: [
      { 
        extend: 'pdfHtml5', 
        orientation: 'landscape', 
        pageSize: 'LEGAL',
        text: 'PDF',
        titleAttr: 'Exporter en PDF',
        className: 'btn btn-secondary'
      },
      { 
        extend: 'copyHtml5',
        text: 'Copier',
        titleAttr: 'Copier dans le presse-papier',
        className: 'btn btn-secondary'
      },
      { 
        extend: 'excelHtml5',
        text: 'Excel',
        titleAttr: 'Exporter vers Excel',
        className: 'btn btn-secondary'
      },
      { 
        extend: 'csvHtml5',
        text: 'CSV',
        titleAttr: 'Exporter en CSV',
        className: 'btn btn-secondary'
      }
    ],
    order:      [[0,'desc']],
    pageLength: 10,
    lengthMenu: [[5,10,25,50,100,-1],[5,10,25,50,100,'Tous']],
    language: {
      "sProcessing":     "Traitement en cours...",
      "sSearch":         "Rechercher&nbsp;:",
      "sLengthMenu":     "Afficher _MENU_ &eacute;l&eacute;ments",
      "sInfo":           "Affichage de l'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
      "sInfoEmpty":      "Affichage de l'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment",
      "sInfoFiltered":   "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
      "sInfoPostFix":    "",
      "sLoadingRecords": "Chargement en cours...",
      "sZeroRecords":    "Aucun &eacute;l&eacute;ment &agrave; afficher",
      "sEmptyTable":     "Aucune donn&eacute;e disponible dans le tableau",
      "oPaginate": {
        "sFirst":      "Premier",
        "sPrevious":   "Pr&eacute;c&eacute;dent",
        "sNext":       "Suivant",
        "sLast":       "Dernier"
      },
      "oAria": {
        "sSortAscending":  ": activer pour trier la colonne par ordre croissant",
        "sSortDescending": ": activer pour trier la colonne par ordre d&eacute;croissant"
      },
      "select": {
        "rows": {
          "_": "%d lignes s&eacute;lectionn&eacute;es",
          "0": "Aucune ligne s&eacute;lectionn&eacute;e",
          "1": "1 ligne s&eacute;lectionn&eacute;e"
        }
      },
      "buttons": {
        "copyTitle": "Copié dans le presse-papier",
        "copySuccess": {
          "_": "%d lignes copiées",
          "1": "1 ligne copiée"
        }
      }
    }
  });

  // 1.1) Imprimer Consultation
  $('#datatable tbody').on('click', 'a.print-fiche', function(e) {
    e.preventDefault();
    var rowData = table.row($(this).closest('tr')).data();
    var id      = rowData.id_consultation;
    window.open(
      '../api/modules/fiche_consultation.php?id_consultation=' + id,
      '_blank'
    );
  });

  // 1.2) Imprimer Ordonnance
  $('#datatable tbody').on('click', 'a.print-ordon', function(e) {
    e.preventDefault();
    var rowData = table.row($(this).closest('tr')).data();
    var id      = rowData.id_consultation;
    window.open(
      '../api/modules/fiche_ordonnance.php?id_consultation=' + id,
      '_blank'
    );
  });

  // 2) Ouvrir le modal Modifier Consultation
  $('#datatable tbody').on('click', '.open-Modifier_Consultation', function () {
    var rowData   = table.row($(this).closest('tr')).data();
    var consultId = rowData.id_consultation;

    $.ajax({
      url:      '../api/modules/consultation_data.php',
      type:     'GET',
      data:     { id_consultation: consultId },
      dataType: 'json',
      success: function (res) {
        if (!res.data || !res.data.length) return;
        var d       = res.data[0];
        var exams   = res.examens     || [];
        var meds    = res.medicaments || [];
        var results = res.resultats   || [];

        // Champs principaux
        $('#id_consultation_mod').val(d.id_consultation);
        $('#id_patient_mod'     ).val(d.id_patient);
        $('#id_medecin_mod'     ).val(d.id_medecin);
        $('#date_consultation_mod').val(d.date_consultation);

        // Constantes vitales
        $('#tension_arterielle_mod'    ).val(d.tension_arterielle);
        $('#temperature_mod'           ).val(d.temperature);
        $('#poids_mod'                 ).val(d.poids);
        $('#taille_mod'                ).val(d.taille);
        $('#frequence_cardiaque_mod'   ).val(d.frequence_cardiaque);
        $('#imc_mod'                   ).val(d.imc);
        $('#glycemie_mod'              ).val(d.glycemie);
        $('#frequence_respiratoire_mod').val(d.frequence_respiratoire);
        $('#saturation_oxygene_mod'    ).val(d.saturation_oxygene);

        // Texte libre
        $('#motif_mod'       ).val(d.motif);
        $('#symptomes_mod'   ).val(d.symptomes);
        $('#diagnostic_mod'  ).val(d.diagnostic);
        $('#observations_mod').val(d.observations);

        // Ordonnance
        $('#type_ordonnance_mod').val(d.type_ordonnance);
        $('#instructions_mod'   ).val(d.instructions);

        // Mettre à jour les liens d'impression du modal
        $('.print-fiche').attr(
          'href',
          '../api/modules/fiche_consultation.php?id_consultation=' + consultId
        );
        $('.print-ordon').attr(
          'href',
          '../api/modules/fiche_ordonnance.php?id_consultation=' + consultId
        );

        // Vider et recharger les tableaux dynamiques
        $('#table_medicaments_mod tbody, #table_examens_mod tbody, #table_resultats_mod tbody').empty();

        // Médicaments existants
        meds.forEach(function(m) {
          $('#table_medicaments_mod tbody').append(
            `<tr>
               <td><input name="medicament[]" class="form-control" value="${m.medicament}" required></td>
               <td><input name="posologie[]"  class="form-control" value="${m.posologie}"  required></td>
               <td><input name="duree[]"      class="form-control" value="${m.duree}"      required></td>
               <td><button type="button" class="btn btn-sm btn-danger remove-medicament"><i class="fa fa-times"></i></button></td>
             </tr>`
          );
        });

        // Examens existants
        exams.forEach(function(e) {
          $('#table_examens_mod tbody').append(
            `<tr>
               <td>
                 <input type="hidden" name="id_examen[]" value="${e.id_examen}">
                 <input name="examen_type[]" class="form-control" value="${e.type_examen}" required>
               </td>
               <td><input name="examen_motif[]" class="form-control" value="${e.motif}"></td>
               <td>
                 <select name="examen_urgent[]" class="form-control">
                   <option value="0"${e.est_urgent==0?' selected':''}>Non</option>
                   <option value="1"${e.est_urgent==1?' selected':''}>Oui</option>
                 </select>
               </td>
               <td><button type="button" class="btn btn-sm btn-danger remove-examen"><i class="fa fa-times"></i></button></td>
             </tr>`
          );
        });

        // Résultats existants
        results.forEach(function(r) {
          var sel = '<select name="resultat_examen_id_examen[]" class="form-control" required>' +
                    '<option value="">Sélectionner…</option>';
          exams.forEach(function(ex) {
            sel += `<option value="${ex.id_examen}"${ex.id_examen===r.id_examen?' selected':''}>${ex.type_examen}</option>`;
          });
          sel += '</select>';
          var fileLink = r.fichier
            ? `<a href="../../uploads/resultats/${r.fichier}" target="_blank">Télécharger</a><br>`
            : '';
          $('#table_resultats_mod tbody').append(
            `<tr>
               <td>${sel}</td>
               <td><textarea name="resultat_texte[]" class="form-control" rows="1">${r.contenu_texte||''}</textarea></td>
               <td>${fileLink}<input type="file" name="resultat_fichier[]" class="form-control-file" accept=".pdf,image/*"></td>
               <td><button type="button" class="btn btn-sm btn-danger remove-resultat"><i class="fa fa-times"></i></button></td>
             </tr>`
          );
        });

        // Ajouter dynamiquement des résultats
        $('#add_resultat_mod').off('click').on('click', function () {
          var sel = '<select name="resultat_examen_id_examen[]" class="form-control" required>' +
                    '<option value="">Sélectionner…</option>';
          exams.forEach(function(ex) {
            sel += `<option value="${ex.id_examen}">${ex.type_examen}</option>`;
          });
          sel += '</select>';
          $('#table_resultats_mod tbody').append(
            `<tr>
               <td>${sel}</td>
               <td><textarea name="resultat_texte[]" class="form-control" rows="1"></textarea></td>
               <td><input type="file" name="resultat_fichier[]" class="form-control-file" accept=".pdf,image/*"></td>
               <td><button type="button" class="btn btn-sm btn-danger remove-resultat"><i class="fa fa-times"></i></button></td>
             </tr>`
          );
        });

        // Supprimer lignes dynamiques
        $('#table_medicaments_mod, #table_examens_mod, #table_resultats_mod')
          .off('click', '.remove-medicament, .remove-examen, .remove-resultat')
          .on('click', '.remove-medicament, .remove-examen, .remove-resultat', function() {
            $(this).closest('tr').remove();
          });

        // Afficher le modal Modification
        $('#modifier_consultation').modal('show');
      },
      error: function(xhr, status, error) {
        console.error('🚨 Erreur GET consultation_data:', status, error);
        console.error('Response:', xhr.responseText);
      }
    });
  });

  // 3) Ouvrir le modal Détails Consultation (lecture seule)
  $('#datatable tbody').on('click', '.open-Details_Consultation', function () {
    var rowData   = table.row($(this).closest('tr')).data();
    var consultId = rowData.id_consultation;
    $.ajax({
      url:      '../api/modules/consultation_data.php',
      type:     'GET',
      data:     { id_consultation: consultId },
      dataType: 'json',
      success: function (res) {
        if (!res.data || !res.data.length) return;
        var d       = res.data[0];
        var exams   = res.examens     || [];
        var meds    = res.medicaments || [];
        var results = res.resultats   || [];
        var html = `<p><strong>Patient :</strong> ${d.patient}</p>`;
        html += `<p><strong>Médecin :</strong> ${d.medecin}</p>`;
        html += `<p><strong>Date :</strong> ${d.date_consultation}</p>`;
        html += `<hr><p><strong>Motif :</strong> ${d.motif}</p>`;
        html += `<p><strong>Symptômes :</strong> ${d.symptomes}</p>`;
        html += `<p><strong>Diagnostic :</strong> ${d.diagnostic}</p>`;
        html += `<p><strong>Observations :</strong> ${d.observations}</p>`;
        html += `<hr><h5>Constantes Vitales</h5><ul>`;
        html += `<li>Température : ${d.temperature}</li>`;
        html += `<li>Poids : ${d.poids}</li>`;
        html += `<li>Taille : ${d.taille}</li>`;
        html += `<li>IMC : ${d.imc}</li>`;
        html += `<li>TA : ${d.tension_arterielle}</li>`;
        html += `<li>FC : ${d.frequence_cardiaque}</li>`;
        html += `<li>FR : ${d.frequence_respiratoire}</li>`;
        html += `<li>SaO₂ : ${d.saturation_oxygene}</li>`;
        html += `<li>Glycémie : ${d.glycemie}</li></ul>`;
        if (meds.length) {
          html += `<hr><h5>Ordonnance</h5><ul>`;
          meds.forEach(m => {
            html += `<li>${m.medicament} — ${m.posologie} — ${m.duree}</li>`;
          });
          html += `</ul>`;
          html += `<p><strong>Type :</strong> ${d.type_ordonnance || '—'}</p>`;
          html += `<p><strong>Instructions :</strong> ${d.instructions || '—'}</p>`;
        }
        if (exams.length) {
          html += `<hr><h5>Examens Prescrits</h5><ul>`;
          exams.forEach(e => {
            html += `<li>${e.type_examen} — ${e.motif || ''} (${e.est_urgent ? 'Urgent' : 'Normal'})</li>`;
          });
          html += `</ul>`;
        }
        if (results.length) {
          html += `<hr><h5>Résultats</h5><ul>`;
          results.forEach(r => {
            html += `<li>${r.contenu_texte || ''}`;
            if (r.fichier) {
              html += ` (<a href="../../uploads/resultats/${r.fichier}" target="_blank">Fichier</a>)`;
            }
            html += `</li>`;
          });
          html += `</ul>`;
        }
        $('#details_body').html(html);
        $('#details_consultation').modal('show');
      },
      error: function(xhr, status, error) {
        console.error('🚨 Erreur GET détails consultation:', status, error);
      }
    });
  });

  // 4) Ouvrir la fiche PDF Résultats Examen
  $('#datatable tbody').on('click', '.open-Resultats_Consultation', function () {
    var rowData = table.row($(this).closest('tr')).data();
    var id      = rowData.id_consultation;
    window.open(
      '../api/modules/fiche_resultats.php?id_consultation=' + id,
      '_blank'
    );
  });

  // 5) Supprimer consultation
  $('#datatable tbody').on('click', '.supprimer-consultation', function () {
    var rowData = table.row($(this).closest('tr')).data();
    Swal.fire({
      title: 'Êtes-vous sûr ?',
      text:  'Cette action est irréversible.',
      icon:  'warning',
      showCancelButton: true,
      confirmButtonText: 'Oui, supprimer',
      cancelButtonText:  'Annuler',
      customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-secondary' },
      buttonsStyling: false
    }).then(function(res) {
      if (res.isConfirmed) {
        window.location.href =
          '../api/modules/supprimer_consultation.php?id_consultation=' +
          rowData.id_consultation;
      }
    });
  });

  // 6) Fonctions d'ajout/suppression dynamiques de lignes
  function bindDynamic(tableId, addBtn, removeClass, rowHtml) {
    $(addBtn).on('click', function () {
      $(tableId + ' tbody').append(rowHtml);
    });
    $(tableId).on('click', removeClass, function () {
      $(this).closest('tr').remove();
    });
  }

  // 6.1 Ajouter Médicaments (modal modif)
  bindDynamic(
    '#table_medicaments_mod', '#add_medicament_mod', '.remove-medicament',
    `<tr>
       <td><input name="medicament[]" class="form-control" required></td>
       <td><input name="posologie[]"  class="form-control" required></td>
       <td><input name="duree[]"      class="form-control" required></td>
       <td><button type="button" class="btn btn-sm btn-danger remove-medicament"><i class="fa fa-times"></i></button></td>
     </tr>`
  );

  // 6.2 Ajouter Examens (modal modif)
  bindDynamic(
    '#table_examens_mod', '#add_examen_mod', '.remove-examen',
    `<tr>
       <td>
         <input type="hidden" name="id_examen[]" value="">
         <input name="examen_type[]" class="form-control" required>
       </td>
       <td><input name="examen_motif[]" class="form-control"></td>
       <td><select name="examen_urgent[]" class="form-control"><option value="0">Non</option><option value="1">Oui</option></select></td>
       <td><button type="button" class="btn btn-sm btn-danger remove-examen"><i class="fa fa-times"></i></button></td>
     </tr>`
  );

  // 6.3 Ajouter Médicaments (formulaire ajout)
  bindDynamic(
    '#table_medicaments', '#add_medicament', '.remove-medicament',
    `<tr>
       <td><input name="medicament[]" class="form-control" required></td>
       <td><input name="posologie[]"  class="form-control" required></td>
       <td><input name="duree[]"      class="form-control" required></td>
       <td><button type="button" class="btn btn-sm btn-danger remove-medicament"><i class="fa fa-times"></i></button></td>
     </tr>`
  );

  // 6.4 Ajouter Examens (formulaire ajout)
  bindDynamic(
    '#table_examens', '#add_examen', '.remove-examen',
    `<tr>
       <td><input name="examen_type[]" class="form-control" required></td>
       <td><input name="examen_motif[]" class="form-control"></td>
       <td><select name="examen_urgent[]" class="form-control"><option value="0">Non</option><option value="1">Oui</option></select></td>
       <td><button type="button" class="btn btn-sm btn-danger remove-examen"><i class="fa fa-times"></i></button></td>
     </tr>`
  );
});