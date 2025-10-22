<!-- Modal de déconnexion -->
<div class="modal fade" id="modal_deconnexion" tabindex="-1" role="dialog" aria-labelledby="deconnexionLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deconnexionLabel">Confirmer la déconnexion</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Êtes-vous sûr de vouloir vous déconnecter ?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-dismiss="modal">Annuler</button>
        <a href="../api/modules/deconnexion.php" class="btn btn-danger">Se déconnecter</a>
      </div>
    </div>
  </div>
</div>
