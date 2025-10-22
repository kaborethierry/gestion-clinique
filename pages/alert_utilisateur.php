<!-- alert_utilisateur.php -->
<?php
if (empty($_SESSION['id'])) {
    header('Location:./../index.php?erreur=3');
    exit();
}
?>

<?php
if (isset($_SESSION['mod']) && $_SESSION['mod'] == 1) { 
?>
  <script>
    Swal.fire(
      'Utilisateur modifié!',
      'Cliquez sur OK pour continuer',
      'success'
    );
  </script>
<?php 
    $_SESSION['mod'] = 0;
}
?>

<?php
if (isset($_SESSION['supr']) && $_SESSION['supr'] == 1) {
?>
  <script>
    Swal.fire(
      'Utilisateur supprimé!',
      'Cliquez sur OK pour continuer',
      'success'
    );
  </script>
<?php 
    $_SESSION['supr'] = 0;
}
?>

<?php
if (isset($_SESSION['ajout']) && $_SESSION['ajout'] == 1) { 
?>
  <script>
    Swal.fire(
      'Utilisateur ajouté!',
      'Cliquez sur OK pour continuer',
      'success'
    );
  </script>
<?php 
    $_SESSION['ajout'] = 0;
}
?>

<?php
if (isset($_SESSION['imp']) && $_SESSION['imp'] == 1) { 
?>
  <script>
    Swal.fire(
      'Erreur d\'ajout!',
      'Un utilisateur possède déjà ce code ou une donnée similaire !',
      'error'
    );
  </script>
<?php 
    $_SESSION['imp'] = 0;
}
?>
