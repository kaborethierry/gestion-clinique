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
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Clinique - Rendez-vous</title>
  <?php include("inclusion_haut.php"); ?>
</head>
<body>
  <?php include("loader.php"); ?>
  <?php include("alert_rendez_vous.php"); ?>

  <div id="main-wrapper">
    <?php include("entete.php"); ?>

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

            <div class="d-flex justify-content-between align-items-center mb-3">
              <h4 class="card-title">Liste des rendez-vous</h4>

              <!-- ➌ Nouveau RDV visible pour tous les rôles -->
              <button class="btn btn-primary"
                      data-toggle="modal"
                      data-target="#ajouter_rendez_vous">
                <i class="fa fa-plus"></i> Nouveau rendez-vous
              </button>
            </div>

            <div class="table-responsive">
              <table id="datatable"
                     class="table table-striped table-bordered zero-configuration">
                <thead>
                  <tr>
                    <th class="text-center">N°</th>
                    <th class="text-center">Patient</th>
                    <th class="text-center">Médecin</th>
                    <th class="text-center">Date</th>
                    <th class="text-center">Heure</th>
                    <th class="text-center">Motif</th>
                    <th class="text-center">Statut</th>
                    <th class="text-center">Note</th>
                    <th class="text-center">Modifier</th>
                    <th class="text-center">Supprimer</th>
                    <!-- 3 colonnes cachées pour les IDs -->
                    <th style="display:none;"></th>
                    <th style="display:none;"></th>
                    <th style="display:none;"></th>
                  </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                  <tr>
                    <th class="text-center">N°</th>
                    <th class="text-center">Patient</th>
                    <th class="text-center">Médecin</th>
                    <th class="text-center">Date</th>
                    <th class="text-center">Heure</th>
                    <th class="text-center">Motif</th>
                    <th class="text-center">Statut</th>
                    <th class="text-center">Note</th>
                    <th class="text-center">Modifier</th>
                    <th class="text-center">Supprimer</th>
                    <!-- mêmes TH cachés -->
                    <th style="display:none;"></th>
                    <th style="display:none;"></th>
                    <th style="display:none;"></th>
                  </tr>
                </tfoot>
              </table>
            </div>

          </div>
        </div>
      </div>
    </div>

    <?php include("footer.php"); ?>
  </div>

  <?php include("inclusion_bas.php"); ?>

  <!-- expose le rôle JS-side -->
  <script>
    var currentUserRole = '<?= addslashes($_SESSION['type_compte']) ?>';
  </script>
  <script src="DataTables/data_table_rendez_vous.js?version=1.5"></script>
  <?php include("modals/modal_rendez_vous.php"); ?>
</body>
</html>
```