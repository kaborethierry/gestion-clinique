<?php
// pages/modals/modal_ordonnance.php

include __DIR__ . '/../../api/modules/connect_db_pdo.php';

// Récupérer la liste des patients
$ps = $bdd
    ->query("
        SELECT id, CONCAT(nom,' ',prenom) AS nom_complet
          FROM patient
         ORDER BY nom, prenom
    ")
    ->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la liste des consultations
$cs = $bdd
    ->query("
        SELECT c.id_consultation,
               CONCAT(p.nom,' ',p.prenom,' – ',
                      DATE_FORMAT(c.date_consultation,'%d/%m/%Y %H:%i')
               ) AS libelle
          FROM consultation AS c
          JOIN patient AS p ON c.id_patient = p.id
         WHERE c.supprimer = 'NON'
         ORDER BY c.date_consultation DESC
    ")
    ->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Modal Ajouter / Modifier Ordonnance -->
<div class="modal fade" id="modal_ordonnance" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Ordonnance</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="form_ordonnance" method="POST">
        <input type="hidden" name="id_ordonnance" id="id_ordonnance">

        <div class="modal-body">

          <div class="form-row">
            <div class="form-group col-md-4">
              <label for="type_ordonnance">Type (*)</label>
              <select name="type_ordonnance" id="type_ordonnance" class="form-control" required>
                <option value="">Sélectionner…</option>
                <option value="Standard">Standard</option>
                <option value="Urgence">Urgence</option>
              </select>
            </div>

            <div class="form-group col-md-4">
              <label for="id_patient">Patient (*)</label>
              <select name="id_patient" id="id_patient" class="form-control" required>
                <option value="">Sélectionner…</option>
                <?php foreach ($ps as $p): ?>
                  <option value="<?= $p['id'] ?>">
                    <?= htmlspecialchars($p['nom_complet']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group col-md-4">
              <label for="id_consultation">Consultation (*)</label>
              <select name="id_consultation" id="id_consultation" class="form-control" required>
                <option value="">Sélectionner…</option>
                <?php foreach ($cs as $c): ?>
                  <option value="<?= $c['id_consultation'] ?>">
                    <?= htmlspecialchars($c['libelle']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label>Médicaments</label>
            <table class="table table-sm" id="table_meds">
              <thead>
                <tr>
                  <th>Nom</th>
                  <th>Posologie</th>
                  <th>Durée</th>
                  <th class="text-center">–</th>
                </tr>
              </thead>
              <tbody>
                <!-- Les lignes seront ajoutées dynamiquement via JS -->
              </tbody>
            </table>
            <button type="button" id="add_med" class="btn btn-secondary btn-sm">
              <i class="fa fa-plus"></i> Ajouter un médicament
            </button>
          </div>

          <div class="form-group">
            <label for="instructions">Instructions supplémentaires</label>
            <textarea name="instructions" id="instructions" class="form-control" rows="3"></textarea>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            Annuler
          </button>
          <button type="button" id="save_ordonnance" class="btn btn-success">
            Enregistrer
          </button>
        </div>
      </form>

    </div>
  </div>
</div>
