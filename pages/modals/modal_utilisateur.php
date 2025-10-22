<!-- Modal Ajouter Utilisateur -->
<div class="modal fade" id="ajouter_utilisateur" tabindex="-1" role="dialog" aria-labelledby="ajouterUtilisateurLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ajouterUtilisateurLabel">Ajout d'un nouvel utilisateur</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="./../api/modules/ajouter_utilisateur.php" method="POST">
        <div class="modal-body">
          <!-- 1. Nom & Prénom -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="nom">Nom (*)</label>
              <input type="text" id="nom" name="nom" class="form-control" placeholder="Entrer le nom" required>
            </div>
            <div class="form-group col-md-6">
              <label for="prenom">Prénom(s) (*)</label>
              <input type="text" id="prenom" name="prenom" class="form-control" placeholder="Entrer le prénom" required>
            </div>
          </div>

          <!-- 2. Email & Téléphone -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="email">Email (*)</label>
              <input type="email" id="email" name="email" class="form-control" placeholder="Entrer l'email" required>
            </div>
            <div class="form-group col-md-6">
              <label for="telephone">Téléphone</label>
              <input type="text" id="telephone" name="telephone" class="form-control" placeholder="Entrer le téléphone">
            </div>
          </div>

          <!-- 3. Adresse & Poste -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="adresse">Adresse</label>
              <input type="text" id="adresse" name="adresse" class="form-control" placeholder="Entrer l'adresse">
            </div>
            <div class="form-group col-md-6">
              <label for="poste">Poste</label>
              <input type="text" id="poste" name="poste" class="form-control" placeholder="Entrer le poste">
            </div>
          </div>

          <!-- 4. Nom d'utilisateur & Type de compte -->
          <div class="form-row">
            
            <div class="form-group col-md-6">
              <label for="type_compte">Type de compte (*)</label>
              <select id="type_compte" name="type_compte" class="form-control" required>
                <option value="">Sélectionner...</option>
                <option value="Utilisateur">Utilisateur</option>
                <option value="Medecin">Medecin</option>
                <option value="Secretaire">Secretaire</option>
                <option value="Comptable">Comptable</option>
                <option value="Laborantin">Laborantin</option>
                <option value="Super Administrateur">Super Administrateur</option>
              </select>
            </div>

            <div class="form-group col-md-6">
              <label for="statut">Statut (*)</label>
              <select id="statut" name="statut" class="form-control" required>
                <option value="">Sélectionner...</option>
                <option value="Actif" selected>Actif</option>
                <option value="Inactif">Inactif</option>
              </select>
            </div>
          </div>

          <!-- 5. Statut & Mot de passe -->
          <div class="form-row">
            
            <div class="form-group col-md-6">
              <label for="username">Nom d'utilisateur (*)</label>
              <input type="text" id="username" name="username" class="form-control" placeholder="Entrer le nom d'utilisateur" required>
            </div>
            <div class="form-group col-md-6">
              <label for="passworde">Mot de passe (*)</label>
              <input type="password" id="passworde" name="passworde" class="form-control" placeholder="Entrer le mot de passe" required>
            </div>
          </div>

          <p><small>NB : Les champs marqués (*) sont obligatoires.</small></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-warning">Ajouter</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Modifier Utilisateur -->
<div class="modal fade" id="modifier_utilisateur" tabindex="-1" role="dialog" aria-labelledby="modifierUtilisateurLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modifierUtilisateurLabel">Modification d'un utilisateur</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="../api/modules/modifier_utilisateur.php" method="post">
        <div class="modal-body">
          <input type="hidden" id="id_utilisateur" name="id_utilisateur">

          <!-- 1. Nom & Prénom -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="nom_modif">Nom (*)</label>
              <input type="text" id="nom_modif" name="nom" class="form-control" placeholder="Entrer le nom" required>
            </div>
            <div class="form-group col-md-6">
              <label for="prenom_modif">Prénom(s) (*)</label>
              <input type="text" id="prenom_modif" name="prenom" class="form-control" placeholder="Entrer le prénom" required>
            </div>
          </div>

          <!-- 2. Email & Téléphone -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="email_modif">Email (*)</label>
              <input type="email" id="email_modif" name="email" class="form-control" placeholder="Entrer l'email" required>
            </div>
            <div class="form-group col-md-6">
              <label for="telephone_modif">Téléphone</label>
              <input type="text" id="telephone_modif" name="telephone" class="form-control" placeholder="Entrer le téléphone">
            </div>
          </div>

          <!-- 3. Adresse & Poste -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="adresse_modif">Adresse</label>
              <input type="text" id="adresse_modif" name="adresse" class="form-control" placeholder="Entrer l'adresse">
            </div>
            <div class="form-group col-md-6">
              <label for="poste_modif">Poste</label>
              <input type="text" id="poste_modif" name="poste" class="form-control" placeholder="Entrer le poste">
            </div>
          </div>

          <!-- 4. Nom d'utilisateur & Type de compte -->
          <div class="form-row">
            
            <div class="form-group col-md-6">
              <label for="type_compte_modif">Type de compte (*)</label>
              <select id="type_compte_modif" name="type_compte" class="form-control" required>
                <option value="">Sélectionner...</option>
                <option value="Utilisateur">Utilisateur</option>
                <option value="Medecin">Medecin</option>
                <option value="Secretaire">Secretaire</option>
                <option value="Comptable">Comptable</option>
                <option value="Laborantin">Laborantin</option>
                <option value="Super Administrateur">Super Administrateur</option>
              </select>
            </div>

            <div class="form-group col-md-6">
              <label for="statut_modif">Statut (*)</label>
              <select id="statut_modif" name="statut" class="form-control" required>
                <option value="">Sélectionner...</option>
                <option value="Actif" selected>Actif</option>
                <option value="Inactif">Inactif</option>
              </select>
            </div>
          </div>

          <!-- 5. Statut & Mot de passe -->
          <div class="form-row">
            
            <div class="form-group col-md-6">
              <label for="username_modif">Nom d'utilisateur (*)</label>
              <input type="text" id="username_modif" name="username" class="form-control" placeholder="Entrer le nom d'utilisateur" required>
            </div>
          </div>

          <p><small>NB : Les champs marqués (*) sont obligatoires.</small></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-warning">Modifier</button>
        </div>
      </form>
    </div>
  </div>
</div>
```