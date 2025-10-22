<?php

session_start();
if (empty($_SESSION['id']) ||
    !in_array($_SESSION['type_compte'], [
      "Super Administrateur",
      "Medecin",
      "Laborantin",

    ])) {
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
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Clinique – Laboratoire</title>
  <?php include "inclusion_haut.php"; ?>
  <link rel="stylesheet"
        href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
</head>
<body>
  <?php include "loader.php"; ?>


  <div id="main-wrapper">
    <?php include "entete.php"; ?>
    <?php
      switch ($_SESSION['type_compte']) {
        case "Super Administrateur": include("menu.php"); break;
        case "Laborantin":           include("menu_laborantin.php"); break;
        case "Medecin":              include("menu_medecin.php"); break;
      }
    ?>

    <div class="content-body">
      <div class="row page-titles mx-0">
        <div class="col p-md-0">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Clinique</a></li>
            <li class="breadcrumb-item active"><a href="#">Laboratoire</a></li>
          </ol>
        </div>
      </div>

      <div class="container-fluid">
        <div class="card mb-3">
          <div class="card-body">
            <h4 class="card-title">Demandes d’examen & Résultats</h4>
            <div class="table-responsive">
              <table id="table_laboratoire"
                     class="table table-striped table-bordered">
                <thead>
                  <tr>
                    <th class="text-center"># Examen</th>
                    <th class="text-center">Patient</th>
                    <th class="text-center">Consultation</th>
                    <th class="text-center">Type Examen</th>
                    <th class="text-center">Motif</th>
                    <th class="text-center">Date Demande</th>
                    <th class="text-center">Date Résultat</th>
                    <th class="text-center">Ajouter</th>
                    <th class="text-center">Modifier</th>
                    <th class="text-center">Supprimer</th>
                    <th class="text-center">Imprimer</th>
                  </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                  <tr>
                    <th class="text-center"># Examen</th>
                    <th class="text-center">Patient</th>
                    <th class="text-center">Consultation</th>
                    <th class="text-center">Type Examen</th>
                    <th class="text-center">Motif</th>
                    <th class="text-center">Date Demande</th>
                    <th class="text-center">Date Résultat</th>
                    <th class="text-center">Ajouter</th>
                    <th class="text-center">Modifier</th>
                    <th class="text-center">Supprimer</th>
                    <th class="text-center">Imprimer</th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div><!-- /.content-body -->

    <?php include "footer.php"; ?>
  </div><!-- /#main-wrapper -->

  <!-- Mods al groupés -->
  <?php include "modals/modal_laboratoire.php"; ?>

  <?php include "inclusion_bas.php"; ?>
  <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
  <script src="DataTables/data_table_laboratoire.js?version=1.5"></script>
</body>
</html>
