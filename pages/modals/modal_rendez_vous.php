<?php
// modals/modal_rendez_vous.php

// Connexion PDO
include __DIR__ . '/../../api/modules/connect_db_pdo.php';

// Liste des patients
$reqPatients = $bdd->prepare("
  SELECT id, CONCAT(nom, ' ', prenom) AS nom_complet 
  FROM patient 
  ORDER BY nom, prenom
");
$reqPatients->execute();
$patients = $reqPatients->fetchAll(PDO::FETCH_ASSOC);

// Liste des médecins
$reqMedecins = $bdd->prepare("
  SELECT id_utilisateur AS id, CONCAT(nom, ' ', prenom) AS nom_complet 
  FROM utilisateur 
  WHERE type_compte = 'Medecin'
  ORDER BY nom, prenom
");
$reqMedecins->execute();
$medecins = $reqMedecins->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Modal Ajouter Rendez-vous -->
<div class="modal fade" id="ajouter_rendez_vous" tabindex="-1" role="dialog" aria-labelledby="ajouterRendezVousLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="ajouterRendezVousLabel">Nouveau rendez-vous</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form action="../api/modules/ajouter_rendez_vous.php" method="POST">
        <div class="modal-body">
          <!-- Patient & Médecin -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="id_patient">Patient (*)</label>
              <select id="id_patient" name="id_patient" class="form-control" required>
                <option value="">Sélectionner un patient...</option>
                <?php foreach ($patients as $p): ?>
                  <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nom_complet']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group col-md-6">
              <label for="id_medecin">Médecin (*)</label>
              <select id="id_medecin" name="id_medecin" class="form-control" required>
                <option value="">Sélectionner un médecin...</option>
                <?php foreach ($medecins as $m): ?>
                  <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nom_complet']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Date & Heure -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="date_rdv">Date du rendez-vous (*)</label>
              <input type="date" id="date_rdv" name="date_rdv" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
              <label for="heure_rdv">Heure du rendez-vous (*)</label>
              <input type="time" id="heure_rdv" name="heure_rdv" class="form-control" required>
            </div>
          </div>

          <!-- Motif & Statut -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="motif">Motif du rendez-vous (*)</label>
              <input type="text" id="motif" name="motif" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
              <label for="statut">Statut (*)</label>
              <select id="statut" name="statut" class="form-control" required>
                <option value="">Sélectionner le statut...</option>
                <option value="En attente">En attente</option>
                <option value="Confirmé">Confirmé</option>
                <option value="Annulé">Annulé</option>
                <option value="Terminé">Terminé</option>
              </select>
            </div>
          </div>

          <!-- Note -->
          <div class="form-group">
            <label for="note">Note complémentaire</label>
            <textarea id="note" name="note" class="form-control" rows="3"></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Ajouter</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Modifier Rendez-vous -->
<div class="modal fade" id="modifier_rendez_vous" tabindex="-1" role="dialog" aria-labelledby="modifierRendezVousLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="modifierRendezVousLabel">Modifier le rendez-vous</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form action="../api/modules/modifier_rendez_vous.php" method="POST">
        <div class="modal-body">
          <input type="hidden" name="id_rdv" id="id_rdv_modif">

          <!-- Patient & Médecin -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="id_patient_modif">Patient (*)</label>
              <select id="id_patient_modif" name="id_patient" class="form-control" required>
                <option value="">Sélectionner un patient...</option>
                <?php foreach ($patients as $p): ?>
                  <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nom_complet']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group col-md-6">
              <label for="id_medecin_modif">Médecin (*)</label>
              <select id="id_medecin_modif" name="id_medecin" class="form-control" required>
                <option value="">Sélectionner un médecin...</option>
                <?php foreach ($medecins as $m): ?>
                  <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nom_complet']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Date & Heure -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="date_rdv_modif">Date du rendez-vous (*)</label>
              <input type="date" id="date_rdv_modif" name="date_rdv" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
              <label for="heure_rdv_modif">Heure du rendez-vous (*)</label>
              <input type="time" id="heure_rdv_modif" name="heure_rdv" class="form-control" required>
            </div>
          </div>

          <!-- Motif & Statut -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="motif_modif">Motif du rendez-vous (*)</label>
              <input type="text" id="motif_modif" name="motif" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
              <label for="statut_modif">Statut (*)</label>
              <select id="statut_modif" name="statut" class="form-control" required>
                <option value="">Sélectionner le statut...</option>
                <option value="En attente">En attente</option>
                <option value="Confirmé">Confirmé</option>
                <option value="Annulé">Annulé</option>
                <option value="Terminé">Terminé</option>
              </select>
            </div>
          </div>

          <!-- Note -->
          <div class="form-group">
            <label for="note_modif">Note complémentaire</label>
            <textarea id="note_modif" name="note" class="form-control" rows="3"></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-success">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>
