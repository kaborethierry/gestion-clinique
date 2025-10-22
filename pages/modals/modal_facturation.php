<?php
require __DIR__ . '/../../api/modules/connect_db_pdo.php';

// Liste des patients
$patients = $bdd->query("
  SELECT id, numero_dossier, nom, prenom
    FROM patient
   WHERE supprimer = 'NON'
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!-- Modal Ajouter Facture -->
<div class="modal fade" id="ajouter_fact" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form action="../api/modules/ajouter_facturation.php" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h5>Nouvelle facture</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Patient *</label>
              <select name="id_patient" class="form-control" required>
                <option value="">Sélectionner…</option>
                <?php foreach ($patients as $p): ?>
                  <option value="<?= $p['id'] ?>">
                    <?= htmlspecialchars($p['numero_dossier'].' – '.$p['nom'].' '.$p['prenom']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label>Prestation *</label>
              <input type="text" name="type_prestation" class="form-control" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-4">
              <label>Montant *</label>
              <input type="number" step="0.01" name="montant" class="form-control" required>
            </div>
            <div class="form-group col-md-4">
              <label>Moyen paiement</label>
              <select name="moyen_paiement" class="form-control">
                <option>Especes</option>
                <option>Mobile Money</option>
                <option>Carte</option>
                <option>Autre</option>
              </select>
            </div>
            <div class="form-group col-md-4">
              <label>Réf. paiement</label>
              <textarea name="reference_paiement" class="form-control" rows="2" placeholder="Référence de paiement..."></textarea>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Date paiement</label>
              <input type="datetime-local" name="date_paiement" class="form-control"
                     value="<?= date('Y-m-d\TH:i') ?>" required>
            </div>
            <div class="form-group col-md-6">
              <label>Paiement différé</label>
              <select name="paiement_differe" class="form-control">
                <option value="NON">NON</option>
                <option value="OUI">OUI</option>
              </select>
            </div>
          </div>

          <small>* Champs obligatoires</small>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary"
                  data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Modifier Facture -->
<div class="modal fade" id="modifier_fact" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form action="../api/modules/modifier_facturation.php" method="POST">
      <input type="hidden" name="id_facture" id="id_fact_mod">
      <div class="modal-content">
        <div class="modal-header">
          <h5>Modifier facture</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Prestation *</label>
              <input type="text" id="prestation_mod" name="type_prestation_mod"
                     class="form-control" required>
            </div>
            <div class="form-group col-md-6">
              <label>Montant *</label>
              <input type="number" step="0.01" id="montant_mod" name="montant_mod"
                     class="form-control" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-4">
              <label>Moyen paiement</label>
              <select id="mp_mod" name="moyen_paiement_mod" class="form-control">
                <option>Especes</option>
                <option>Mobile Money</option>
                <option>Carte</option>
                <option>Autre</option>
              </select>
            </div>
            <div class="form-group col-md-4">
              <label>Réf. paiement</label>
              <textarea id="ref_mod" name="reference_paiement_mod" class="form-control" rows="2" placeholder="Référence de paiement..."></textarea>
            </div>
            <div class="form-group col-md-4">
              <label>Date paiement</label>
              <input type="datetime-local" id="dpaie_mod" name="date_paiement_mod"
                     class="form-control" required>
            </div>
          </div>

          <div class="form-group">
            <label>Paiement différé</label>
            <select id="pd_mod" name="paiement_differe_mod" class="form-control">
              <option value="NON">NON</option>
              <option value="OUI">OUI</option>
            </select>
          </div>

          <small>* Champs obligatoires</small>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary"
                  data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-warning">Modifier</button>
        </div>
      </div>
    </form>
  </div>
</div>
