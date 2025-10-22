<!-- alert_lit.php -->
<?php
if (empty($_SESSION['id'])) {
    header('Location: ./../index.php?erreur=3');
    exit();
}
?>

<?php
// Ajout
if (isset($_SESSION['ajout_lit']) && $_SESSION['ajout_lit'] == 1) {
?>
  <script>
    Swal.fire(
      'Lit ajouté !',
      'Cliquez sur OK pour continuer',
      'success'
    );
  </script>
<?php
    $_SESSION['ajout_lit'] = 0;
}
?>

<?php
// Modification
if (isset($_SESSION['mod_lit']) && $_SESSION['mod_lit'] == 1) {
?>
  <script>
    Swal.fire(
      'Lit modifié !',
      'Cliquez sur OK pour continuer',
      'success'
    );
  </script>
<?php
    $_SESSION['mod_lit'] = 0;
}
?>

<?php
// Suppression
if (isset($_SESSION['supr_lit']) && $_SESSION['supr_lit'] == 1) {
?>
  <script>
    Swal.fire(
      'Lit supprimé !',
      'Cliquez sur OK pour continuer',
      'success'
    );
  </script>
<?php
    $_SESSION['supr_lit'] = 0;
}
?>

<?php
// Erreur d'intégrité ou doublon
if (isset($_SESSION['imp_lit']) && $_SESSION['imp_lit'] == 1) {
?>
  <script>
    Swal.fire(
      'Erreur !',
      'Impossible d\'effectuer l\'opération. Vérifiez les données.',
      'error'
    );
  </script>
<?php
    $_SESSION['imp_lit'] = 0;
}
?>
