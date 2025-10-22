<?php
// pages/modals/modal_lit.php
require __DIR__ . '/../../api/modules/connect_db_pdo.php';
// Récupère la liste des chambres disponibles
$chambres = $bdd->query("
  SELECT id_chambre, numero_chambre
    FROM chambre
   WHERE supprimer = 'NON'
     AND disponibilite = 'Disponible'
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!-- Modal Ajouter Lit -->
<div class="modal fade" id="ajouter_lit" tabindex="-1" role="dialog" aria-labelledby="ajouterLitLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form action="../api/modules/ajouter_lit.php" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="ajouterLitLabel">Ajouter un lit</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <!-- Choix de la chambre -->
          <div class="form-group">
            <label for="id_chambre">Chambre (*)</label>
            <select name="id_chambre" id="id_chambre" class="form-control" required>
              <option value="">Sélectionner la chambre</option>
              <?php foreach ($chambres as $c): ?>
                <option value="<?= $c['id_chambre'] ?>">
                  <?= htmlspecialchars($c['numero_chambre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <!-- Numéro du lit -->
          <div class="form-group">
            <label for="numero_lit">Numéro du lit (*)</label>
            <input type="text"
                   id="numero_lit"
                   name="numero_lit"
                   class="form-control"
                   placeholder="Ex : A1, 1, B…"
                   required>
          </div>
          <!-- Statut -->
          <div class="form-group">
            <label for="statut">Statut (*)</label>
            <select name="statut" id="statut" class="form-control" required>
              <option value="Libre">Libre</option>
              <option value="Occupé">Occupé</option>
              <option value="Maintenance">Maintenance</option>
            </select>
          </div>
          <p><small>Les champs marqués (*) sont obligatoires.</small></p>
        </div>
        <div class="modal-footer">
          <button type="button"
                  class="btn btn-secondary"
                  data-dismiss="modal">Annuler</button>
          <button type="submit"
                  class="btn btn-primary">Ajouter</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Modifier Lit -->
<div class="modal fade" id="modifier_lit" tabindex="-1" role="dialog" aria-labelledby="modifierLitLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form action="../api/modules/modifier_lit.php" method="POST">
      <input type="hidden" name="id_lit" id="id_lit_modif">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modifierLitLabel">Modifier un lit</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <!-- Chambre -->
          <div class="form-group">
            <label for="id_chambre_modif">Chambre (*)</label>
            <select name="id_chambre_modif" id="id_chambre_modif" class="form-control" required>
              <option value="">Sélectionner la chambre</option>
              <?php foreach ($chambres as $c): ?>
                <option value="<?= $c['id_chambre'] ?>">
                  <?= htmlspecialchars($c['numero_chambre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <!-- Numéro du lit -->
          <div class="form-group">
            <label for="numero_lit_modif">Numéro du lit (*)</label>
            <input type="text"
                   id="numero_lit_modif"
                   name="numero_lit_modif"
                   class="form-control"
                   placeholder="Ex : A1, 1, B…"
                   required>
          </div>
          <!-- Statut -->
          <div class="form-group">
            <label for="statut_modif">Statut (*)</label>
            <select name="statut_modif" id="statut_modif" class="form-control" required>
              <option value="Libre">Libre</option>
              <option value="Occupé">Occupé</option>
              <option value="Maintenance">Maintenance</option>
            </select>
          </div>
          <p><small>Les champs marqués (*) sont obligatoires.</small></p>
        </div>
        <div class="modal-footer">
          <button type="button"
                  class="btn btn-secondary"
                  data-dismiss="modal">Annuler</button>
          <button type="submit"
                  class="btn btn-warning">Enregistrer</button>
        </div>
      </div>
    </form>
  </div>
</div>
