<?php
// modals/modal_chambre.php
?>
<!-- Modal : Ajouter Chambre -->
<div class="modal fade" id="ajouter_chambre" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title">Nouvelle chambre</h5>
      <button class="close" data-dismiss="modal">&times;</button>
    </div>
    <form action="../api/modules/ajouter_chambre.php" method="POST">
      <div class="modal-body">
        <div class="form-group">
          <label>Numéro (*)</label>
          <input type="text" name="numero_chambre" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Type (*)</label>
          <select name="type_chambre" class="form-control" required>
            <option>Standard</option>
            <option>Double</option>
            <option>VIP</option>
            <option>Isolation</option>
          </select>
        </div>
        <div class="form-row">
          <div class="form-group col">
            <label>Capacité (*)</label>
            <input type="number" name="capacite" class="form-control" min="1" value="1" required>
          </div>
          <div class="form-group col">
            <label>Disponibilité (*)</label>
            <select name="disponibilite" class="form-control" required>
              <option>Disponible</option>
              <option>Occupée</option>
              <option>Maintenance</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label>Tarif journalier</label>
          <input type="number" step="0.01" name="tarif_journalier" class="form-control">
        </div>
        <div class="form-group">
          <label>Étage</label>
          <input type="number" name="etage" class="form-control">
        </div>
        <div class="form-group">
          <label>Description</label>
          <textarea name="description" class="form-control" rows="3"></textarea>
        </div>
        <small class="text-muted">* Champs obligatoires</small>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-primary">Ajouter</button>
      </div>
    </form>
  </div></div>
</div>

<!-- Modal : Modifier Chambre -->
<div class="modal fade" id="modifier_chambre" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title">Modifier chambre</h5>
      <button class="close" data-dismiss="modal">&times;</button>
    </div>
    <form action="../api/modules/modifier_chambre.php" method="POST">
      <input type="hidden" name="id_chambre" id="id_chambre_modif">
      <div class="modal-body">
        <div class="form-group">
          <label>Numéro (*)</label>
          <input type="text" name="numero_chambre" id="numero_chambre_modif" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Type (*)</label>
          <select name="type_chambre" id="type_chambre_modif" class="form-control" required>
            <option>Standard</option>
            <option>Double</option>
            <option>VIP</option>
            <option>Isolation</option>
          </select>
        </div>
        <div class="form-row">
          <div class="form-group col">
            <label>Capacité (*)</label>
            <input type="number" name="capacite" id="capacite_modif" class="form-control" min="1" required>
          </div>
          <div class="form-group col">
            <label>Disponibilité (*)</label>
            <select name="disponibilite" id="disponibilite_modif" class="form-control" required>
              <option>Disponible</option>
              <option>Occupée</option>
              <option>Maintenance</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label>Tarif journalier</label>
          <input type="number" step="0.01" name="tarif_journalier" id="tarif_journalier_modif" class="form-control">
        </div>
        <div class="form-group">
          <label>Étage</label>
          <input type="number" name="etage" id="etage_modif" class="form-control">
        </div>
        <div class="form-group">
          <label>Description</label>
          <textarea name="description" id="description_modif" class="form-control" rows="3"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-success">Enregistrer</button>
      </div>
    </form>
  </div></div>
</div>
