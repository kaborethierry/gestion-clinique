<?php
session_start();

// ➊ Contrôle d’accès : Super Administrateur, Secretaire et Medecin
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
 else {
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Clinique - Gestion des Chambres</title>
  <?php include("inclusion_haut.php"); ?>
</head>
<body>
  <?php include("loader.php"); ?>
  <?php include("alert_chambre.php"); ?>

  <div id="main-wrapper">
    <?php include("entete.php"); ?>
    <?php
      switch ($_SESSION['type_compte']) {
        case "Super Administrateur": include("menu.php"); break;
        case "Secretaire":           include("menu_secretaire.php"); break;
        case "Medecin":              include("menu_medecin.php"); break;
      }
    ?>


    <div class="content-body">
      <div class="row page-titles mx-0">
        <div class="col p-md-0">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0)">Clinique</a></li>
            <li class="breadcrumb-item active"><a href="javascript:void(0)">Chambres</a></li>
          </ol>
        </div>
      </div>

      <!-- Conteneur du tableau -->
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <!-- Titre et bouton d'ajout -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h4 class="card-title m-0">Liste des Chambres</h4>
                  <button data-toggle="modal" data-backdrop="false" class="open-Ajouter_Chambre btn btn-primary" href="#ajouter_chambre">
                    <i class="fa fa-plus"></i> Ajouter une chambre
                  </button>
                </div>

                <!-- Tableau DataTables -->
                <div class="table-responsive">
                  <table id="datatable" class="table table-striped table-bordered zero-configuration">
                    <thead>
                      <tr>
                        <th class="text-center">N°</th>
                        <th class="text-center">Numéro</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Capacité</th>
                        <th class="text-center">Disponibilité</th>
                        <th class="text-center">Tarif</th>
                        <th class="text-center">Étage</th>
                        <th class="text-center">Description</th>
                        <th class="text-center">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <!-- Enregistrements chargés via AJAX -->
                    </tbody>
                    <tfoot>
                      <tr>
                        <th class="text-center">N°</th>
                        <th class="text-center">Numéro</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Capacité</th>
                        <th class="text-center">Disponibilité</th>
                        <th class="text-center">Tarif</th>
                        <th class="text-center">Étage</th>
                        <th class="text-center">Description</th>
                        <th class="text-center">Actions</th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
                <!-- Fin table-responsive -->

              </div>
              <!-- Fin card-body -->
            </div>
            <!-- Fin card -->
          </div>
          <!-- Fin col-12 -->
        </div>
        <!-- Fin row -->
      </div>
      <!-- Fin container-fluid -->
    </div>
    <!-- Fin content-body -->

    <?php include("footer.php"); ?>
  </div>
  <!-- Fin main-wrapper -->

  <?php include("inclusion_bas.php"); ?>
  <!-- Chargement du script DataTables spécifique aux chambres -->
  <script type="text/javascript" src="DataTables/data_table_chambre.js?Version=1.0"></script>
  <!-- Inclusion du modal pour les chambres -->
  <?php include("modals/modal_chambre.php"); ?>
</body>
</html>
<?php } ?>
