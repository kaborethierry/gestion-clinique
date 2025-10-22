<?php
session_start();

// Contrôle d’accès : Super Administrateur, Secretaire et Medecin
if (empty($_SESSION['id'])
    || ! in_array($_SESSION['type_compte'], [
         'Super Administrateur',
         'Secretaire',
         'Medecin'
       ], true)
) {
    header('Location: ../index.php?erreur=3');
    exit();
}

// … le reste de pages/hospitalisation.php …

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Clinique – Hospitalisations</title>
  <?php include "inclusion_haut.php"; ?>
</head>
<body>
  <?php include "loader.php"; ?>
  <?php include "alert_hospitalisation.php"; ?>

  <div id="main-wrapper">
    <?php include "entete.php"; ?>
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
            <li class="breadcrumb-item"><a href="#">Clinique</a></li>
            <li class="breadcrumb-item active"><a href="#">Hospitalisations</a></li>
          </ol>
        </div>
      </div>

      <div class="container-fluid">
        <div class="card">
          <div class="card-body">

            <div class="d-flex justify-content-between mb-3">
              <h4 class="card-title m-0">Liste des hospitalisations</h4>
              <button class="btn btn-primary" data-toggle="modal"
                      href="#ajouter_hosp" data-backdrop="false">
                <i class="fa fa-bed"></i> Admettre un patient
              </button>
            </div>

            <div class="table-responsive">
              <table id="datatable"
                     class="table table-striped table-bordered zero-configuration">
                <thead>
                  <tr>
                    <th>N°</th>
                    <th>Patient</th>
                    <th>Médecin</th>
                    <th>Chambre/Lit</th>
                    <th>Date entrée</th>
                    <th>Date sortie</th>
                    <th>Statut</th>
                    <th>Modifier</th>
                    <th>Libérer</th>
                    <th>Supprimer</th>
                  </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                  <tr>
                    <th>N°</th>
                    <th>Patient</th>
                    <th>Médecin</th>
                    <th>Chambre/Lit</th>
                    <th>Date entrée</th>
                    <th>Date sortie</th>
                    <th>Statut</th>
                    <th>Modifier</th>
                    <th>Libérer</th>
                    <th>Supprimer</th>
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
  <script src="Datatables/data_table_hospitalisation.js?version=1.5"></script>
  <?php include "modals/modal_hospitalisation.php"; ?>
</body>
</html>
```