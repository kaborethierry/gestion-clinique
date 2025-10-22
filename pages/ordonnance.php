<?php
session_start();
if (empty($_SESSION['id'])) {
    session_unset();
    session_destroy();
    header('Location: ./../index.php?erreur=3');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Clinique – Gestion des Ordonnances</title>
  <?php include("inclusion_haut.php"); ?>
</head>
<body>
  <?php include("loader.php"); ?>
  <?php include("alert_ordonnance.php"); ?>
  <div id="main-wrapper">
    <?php include("entete.php"); ?>
    <?php include("menu.php"); ?>

    <div class="content-body">
      <div class="row page-titles mx-0">
        <div class="col p-md-0">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Clinique</a></li>
            <li class="breadcrumb-item active"><a href="#">Ordonnances</a></li>
          </ol>
        </div>
      </div>

      <div class="container-fluid">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
              <h4 class="card-title">Liste des Ordonnances</h4>
              <button class="btn btn-primary" data-toggle="modal" data-target="#modal_ordonnance">
                <i class="fa fa-plus"></i> Nouvelle ordonnance
              </button>
            </div>
            <div class="table-responsive">
              <table id="datatable_ordonnance" class="table table-striped table-bordered">
                <thead>
                  <tr>
                    <th>N°</th>
                    <th>Type</th>
                    <th>Patient</th>
                    <th>Consultation</th>
                    <th>Date</th>
                    <th>Modifier</th>
                    <th>Imprimer</th>
                    <th>Supprimer</th>
                    <th hidden>ID</th>
                  </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                  <tr>
                    <th>N°</th>
                    <th>Type</th>
                    <th>Patient</th>
                    <th>Consultation</th>
                    <th>Date</th>
                    <th>Modifier</th>
                    <th>Imprimer</th>
                    <th>Supprimer</th>
                    <th hidden>ID</th>
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
  <script src="DataTables/data_table_ordonnance.js"></script>
  <?php include("modals/modal_ordonnance.php"); ?>
</body>
</html>
```