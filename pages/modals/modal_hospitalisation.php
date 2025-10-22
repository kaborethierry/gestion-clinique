<?php
// pages/modals/modal_hospitalisation.php
require __DIR__ . '/../../api/modules/connect_db_pdo.php';

// Récupération des listes
$patients = $bdd->query("
  SELECT id, numero_dossier, nom, prenom
    FROM patient
   WHERE supprimer = 'NON'
")->fetchAll(PDO::FETCH_ASSOC);

$medecins = $bdd->query("
  SELECT id_utilisateur, nom, prenom
    FROM utilisateur
   WHERE type_compte = 'Medecin'
     AND statut = 'Actif'
")->fetchAll(PDO::FETCH_ASSOC);

$lits = $bdd->query("
  SELECT l.id_lit, c.numero_chambre, l.numero_lit
    FROM lit l
    JOIN chambre c ON l.id_chambre = c.id_chambre
   WHERE l.supprimer = 'NON'
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Modal Ajouter Hospitalisation -->
<div class="modal fade" id="ajouter_hosp" tabindex="-1" role="dialog" aria-labelledby="ajouterHospLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form action="../api/modules/ajouter_hospitalisation.php" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="ajouterHospLabel">Admettre un patient</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="id_patient">Patient (*)</label>
              <select id="id_patient" name="id_patient" class="form-control" required>
                <option value="">Sélectionner...</option>
                <?php foreach ($patients as $p): ?>
                  <option value="<?= $p['id'] ?>">
                    <?= htmlspecialchars($p['numero_dossier'].' – '.$p['nom'].' '.$p['prenom']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="id_medecin">Médecin (*)</label>
              <select id="id_medecin" name="id_medecin" class="form-control" required>
                <option value="">Sélectionner...</option>
                <?php foreach ($medecins as $m): ?>
                  <option value="<?= $m['id_utilisateur'] ?>">
                    <?= htmlspecialchars($m['nom'].' '.$m['prenom']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="id_lit">Lit (chambre/lit) (*)</label>
              <select id="id_lit" name="id_lit" class="form-control" required>
                <option value="">Sélectionner...</option>
                <?php foreach ($lits as $l): ?>
                  <option value="<?= $l['id_lit'] ?>">
                    <?= htmlspecialchars($l['numero_chambre'].' / '.$l['numero_lit']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="date_entree">Date et heure d’entrée (*)</label>
              <input type="datetime-local" id="date_entree" name="date_entree"
                     class="form-control" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="motif">Motif (*)</label>
              <textarea id="motif" name="motif" class="form-control" rows="2" required></textarea>
            </div>
            <div class="form-group col-md-6">
              <label for="observations">Observations</label>
              <textarea id="observations" name="observations" class="form-control" rows="2"></textarea>
            </div>
          </div>

          <p><small>NB : Les champs marqués (*) sont obligatoires.</small></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Admettre</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Modifier Hospitalisation -->
<div class="modal fade" id="modifier_hosp" tabindex="-1" role="dialog" aria-labelledby="modifierHospLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form action="../api/modules/modifier_hospitalisation.php" method="POST">
      <input type="hidden" id="id_hosp_mod" name="id_hosp">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modifierHospLabel">Modifier / Clôturer hospitalisation</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="id_patient_mod">Patient (*)</label>
              <select id="id_patient_mod" name="id_patient_mod" class="form-control" required>
                <option value="">Sélectionner...</option>
                <?php foreach ($patients as $p): ?>
                  <option value="<?= $p['id'] ?>">
                    <?= htmlspecialchars($p['numero_dossier'].' – '.$p['nom'].' '.$p['prenom']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="id_medecin_mod">Médecin (*)</label>
              <select id="id_medecin_mod" name="id_medecin_mod" class="form-control" required>
                <option value="">Sélectionner...</option>
                <?php foreach ($medecins as $m): ?>
                  <option value="<?= $m['id_utilisateur'] ?>">
                    <?= htmlspecialchars($m['nom'].' '.$m['prenom']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="id_lit_mod">Lit (chambre/lit) (*)</label>
              <select id="id_lit_mod" name="id_lit_mod" class="form-control" required>
                <option value="">Sélectionner...</option>
                <?php foreach ($lits as $l): ?>
                  <option value="<?= $l['id_lit'] ?>">
                    <?= htmlspecialchars($l['numero_chambre'].' / '.$l['numero_lit']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="date_entree_mod">Date et heure d’entrée (*)</label>
              <input type="datetime-local" id="date_entree_mod" name="date_entree_mod"
                     class="form-control" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="motif_mod">Motif (*)</label>
              <textarea id="motif_mod" name="motif_mod" class="form-control" rows="2" required></textarea>
            </div>
            <div class="form-group col-md-6">
              <label for="observations_mod">Observations</label>
              <textarea id="observations_mod" name="observations_mod" class="form-control" rows="2"></textarea>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="date_sortie_mod">Date et heure de sortie</label>
              <input type="datetime-local" id="date_sortie_mod" name="date_sortie_mod"
                     class="form-control">
            </div>
            <div class="form-group col-md-6">
              <label for="statut_mod">Statut (*)</label>
              <select id="statut_mod" name="statut_mod" class="form-control" required>
                <option value="En cours">En cours</option>
                <option value="Terminé">Terminé</option>
              </select>
            </div>
          </div>

          <p><small>NB : Les champs marqués (*) sont obligatoires.</small></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-warning">Enregistrer</button>
        </div>
      </div>
    </form>
  </div>
</div>
```