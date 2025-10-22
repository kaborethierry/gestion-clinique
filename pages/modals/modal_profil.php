<!-- Modal Modifier le profil -->
<div class="modal fade" id="modifier_profil" tabindex="-1" role="dialog" aria-labelledby="modifierProfilLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md" role="document">
    <div class="modal-content">
      <form action="../api/modules/modifier_profil.php" method="post">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="modifierProfilLabel" style="color: white;">Modifier mes informations </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_utilisateur" value="<?= $_SESSION['id']; ?>">

          <div class="form-group">
            <label for="nom">Nom :</label>
            <input type="text" class="form-control" name="nom" value="<?= $_SESSION['nom']; ?>" required>
          </div>
          <div class="form-group">
            <label for="prenom">Prénom :</label>
            <input type="text" class="form-control" name="prenom" value="<?= $_SESSION['prenom']; ?>" required>
          </div>
          <div class="form-group">
            <label for="telephone">Téléphone :</label>
            <input type="text" class="form-control" name="telephone" value="<?= $_SESSION['telephone']; ?>">
          </div>
          <div class="form-group">
            <label for="email">Adresse email :</label>
            <input type="email" class="form-control" name="email" value="<?= $_SESSION['email']; ?>" required>
          </div>
          <div class="form-group">
            <label for="username">Nom d’utilisateur :</label>
            <input type="text" class="form-control" name="username" value="<?= $_SESSION['username']; ?>" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Modifier le mot de passe -->
<div class="modal fade" id="modifier_password" tabindex="-1" role="dialog" aria-labelledby="modifierPasswordLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
    <div class="modal-content">
      <form action="../api/modules/modifier_password.php" method="post">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title" id="modifierPasswordLabel">Modifier mon mot de passe</h5>
          <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_utilisateur" value="<?= $_SESSION['id']; ?>">

          <div class="form-group">
            <label for="ancien">Ancien mot de passe :</label>
            <input type="password" class="form-control" name="ancien" required>
          </div>
          <div class="form-group">
            <label for="nouveau">Nouveau mot de passe :</label>
            <input type="password" class="form-control" name="nouveau" required>
          </div>
          <div class="form-group">
            <label for="confirmation">Confirmation :</label>
            <input type="password" class="form-control" name="confirmation" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-warning">Changer</button>
        </div>
      </form>
    </div>
  </div>
</div>
