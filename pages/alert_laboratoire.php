<?php if (isset($_GET['success'])): ?>
  <?php
    $msgs = [
      'ajout' => 'Résultat ajouté avec succès',
      'modif' => 'Résultat mis à jour',
      'supp'  => 'Résultat supprimé'
    ];
    $msg  = $msgs[$_GET['success']] ?? '';
  ?>
  <?php if ($msg): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= $msg ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
  <?php endif; ?>
<?php elseif (isset($_GET['error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($_GET['error']) ?>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
  </div>
<?php endif; ?>
