<?php
session_start();

// ➊ Contrôle d’accès : Super Administrateur, Secrétaire et Médecin
if (empty($_SESSION['id'])
    || ! in_array($_SESSION['type_compte'], [
         "Super Administrateur",
         "Secretaire",
         "Medecin"
       ], true)
) {
    session_unset();
    session_destroy();
    header('Location: ../index.php?erreur=3');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Clinique – Gestion des Patients</title>
  <?php include "inclusion_haut.php"; ?>
</head>
<body>
  <?php include "loader.php"; ?>
  <?php include "alert_patient.php"; ?>

  <?php if (!empty($_SESSION['message_erreur'])): ?>
    <script>
      console.error(<?= json_encode($_SESSION['message_erreur']) ?>);
    </script>
    <?php unset($_SESSION['message_erreur']); ?>
  <?php endif; ?>

  <div id="main-wrapper">
    <?php include "entete.php"; ?>

    <!-- ➋ Menu selon rôle -->
    <?php
      switch ($_SESSION['type_compte']) {
        case "Super Administrateur":
          include("menu.php");
          break;
        case "Secretaire":
          include("menu_secretaire.php");
          break;
        case "Medecin":
          include("menu_medecin.php");
          break;
      }
    ?>

    <div class="content-body">
      <div class="container-fluid">
        <div class="card">
          <div class="card-body">

            <div class="d-flex justify-content-between mb-3">
              <h4 class="card-title">Liste des Patients</h4>
              <button
                id="btn_add_patient"
                type="button"
                class="btn btn-primary">
                <i class="fa fa-plus"></i> Ajouter un patient
              </button>
            </div>

            <div class="table-responsive">
              <table
                id="datatable"
                class="table table-striped table-bordered zero-configuration">
                <thead>
                  <tr>
                    <th class="text-center">N°</th>
                    <th class="text-center">N° Dossier</th>
                    <th class="text-center">Nom</th>
                    <th class="text-center">Prénom</th>
                    <th class="text-center">Sexe</th>
                    <th class="text-center">Date Naissance</th>
                    <th class="text-center">Téléphone</th>
                    <th class="text-center">Tél. Secondaire</th>
                    <th class="text-center">Email</th>
                    <th class="text-center">Ville</th>
                    <th class="text-center">Quartier</th>
                    <th class="text-center">Groupe Sanguin</th>
                    <th class="text-center">Poids (kg)</th>
                    <th class="text-center">Tension</th>
                    <th class="text-center">Date Enregistrement</th>
                    <th class="text-center">Statut</th>
                    <th class="text-center">Modifier</th>
                    <th class="text-center">Dossier</th>
                    <th class="text-center">Supprimer</th>
                    <!-- ➌ TH caché pour l'ID interne -->
                    <th hidden></th>
                  </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                  <tr>
                    <th class="text-center">N°</th>
                    <th class="text-center">N° Dossier</th>
                    <th class="text-center">Nom</th>
                    <th class="text-center">Prénom</th>
                    <th class="text-center">Sexe</th>
                    <th class="text-center">Date Naissance</th>
                    <th class="text-center">Téléphone</th>
                    <th class="text-center">Tél. Secondaire</th>
                    <th class="text-center">Email</th>
                    <th class="text-center">Ville</th>
                    <th class="text-center">Quartier</th>
                    <th class="text-center">Groupe Sanguin</th>
                    <th class="text-center">Poids (kg)</th>
                    <th class="text-center">Tension</th>
                    <th class="text-center">Date Enregistrement</th>
                    <th class="text-center">Statut</th>
                    <th class="text-center">Modifier</th>
                    <th class="text-center">Dossier</th>
                    <th class="text-center">Supprimer</th>
                    <th hidden></th>
                  </tr>
                </tfoot>
              </table>
            </div>

          </div>
        </div>
      </div>
    </div>
    <?php include "footer.php"; ?>
  </div>

  <?php include "inclusion_bas.php"; ?>

  <!-- Exposer le rôle côté JS pour DataTables -->
  <script>
    var currentUserRole = '<?= addslashes($_SESSION['type_compte']) ?>';
  </script>

  <script src="DataTables/data_table_patient.js?version=1.5"></script>
  <?php include "modals/modal_patient.php"; ?>

  <script>
    $(function() {
      // 1) Ouvre directement le modal d'ajout de patient
      $('#btn_add_patient').on('click', function() {
        // Réinitialiser le formulaire
        $('#ajouter_patient form')[0].reset();
        // Masquer la section assurance par défaut
        $('#section_assurance').hide();
        // Afficher le modal
        $('#ajouter_patient').modal('show');
      });

      // 2) Toggle section assurance sur changement manuel
      function toggleAssuranceSection() {
        var statut = $('input[name="assurance_statut"]:checked').val();
        if (statut === 'Assuré') {
          $('#section_assurance').slideDown();
        } else {
          $('#section_assurance').slideUp();
        }
      }
      
      $('input[name="assurance_statut"]').on('change', toggleAssuranceSection);
      
      // 3) Gestion des boutons de modification et suppression dans DataTable
      // (sera géré dans data_table_patient.js)
    });
  </script>
</body>
</html>