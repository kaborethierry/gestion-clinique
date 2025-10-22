<?php if (!empty($_SESSION['ajout_ordon'])): ?>
  <script>
    Swal.fire('Succès','Ordonnance ajoutée','success');
  </script>
  <?php unset($_SESSION['ajout_ordon']); unset($_SESSION['message_ordon']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['mod_ordon'])): ?>
  <script>
    Swal.fire('Succès','Ordonnance modifiée','success');
  </script>
  <?php unset($_SESSION['mod_ordon']); unset($_SESSION['message_ordon']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['sup_ordon'])): ?>
  <script>
    Swal.fire('Succès','Ordonnance supprimée','success');
  </script>
  <?php unset($_SESSION['sup_ordon']); unset($_SESSION['message_ordon']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['err_ordon'])): ?>
  <script>
    Swal.fire('Erreur','<?= $_SESSION['message_ordon'] ?>','error');
  </script>
  <?php unset($_SESSION['err_ordon']); unset($_SESSION['message_ordon']); ?>
<?php endif; ?>
