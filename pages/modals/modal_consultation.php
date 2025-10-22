<?php 
// pages/modals/modal_consultation.php

// Connexion PDO
include __DIR__ . '/../../api/modules/connect_db_pdo.php';

// 1. Charger la liste des patients
$reqPatients = $bdd->prepare("
    SELECT id, CONCAT(nom, ' ', prenom) AS nom_complet
      FROM patient
     ORDER BY nom, prenom
");
$reqPatients->execute();
$patients = $reqPatients->fetchAll(PDO::FETCH_ASSOC);

// 2. Charger la liste des médecins
$reqMedecins = $bdd->prepare("
    SELECT id_utilisateur AS id,
           CONCAT(nom, ' ', prenom) AS nom_complet,
           poste
      FROM utilisateur
     WHERE type_compte = 'Medecin'
     ORDER BY nom, prenom
");
$reqMedecins->execute();
$medecins = $reqMedecins->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Modal Ajouter Consultation -->
<div class="modal fade" id="ajouter_consultation" tabindex="-1" role="dialog" aria-labelledby="ajouterConsultationLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      
      <!-- En-tête -->
      <div class="modal-header">
        <h5 class="modal-title" id="ajouterConsultationLabel">Nouvelle consultation</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      
      <!-- Formulaire -->
      <form action="../api/modules/ajouter_consultation.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          
          <!-- 1. Patient & Médecin -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="id_patient_add">Patient <span class="text-danger">*</span></label>
              <select id="id_patient_add" name="id_patient" class="form-control" required>
                <option value="">Sélectionner un patient…</option>
                <?php foreach ($patients as $p): ?>
                  <option value="<?= $p['id'] ?>">
                    <?= htmlspecialchars($p['nom_complet']) ?> (ID <?= $p['id'] ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="id_medecin_add">Médecin <span class="text-danger">*</span></label>
              <select id="id_medecin_add" name="id_medecin" class="form-control" required>
                <option value="">Sélectionner un médecin…</option>
                <?php foreach ($medecins as $m): ?>
                  <option value="<?= $m['id'] ?>">
                    <?= htmlspecialchars($m['nom_complet']) ?> — <?= htmlspecialchars($m['poste']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          
          <!-- 2. Date -->
          <div class="form-group">
            <label for="date_consultation_add">Date de consultation <span class="text-danger">*</span></label>
            <input type="datetime-local" id="date_consultation_add" name="date_consultation"
                   class="form-control" required>
          </div>
          
          <!-- 3. Constantes vitales -->
          <h5 class="mt-4">Constantes vitales</h5>
          <div class="form-row">
            <div class="form-group col-md-3">
              <label for="tension_arterielle_add">Tension artérielle</label>
              <input type="text" id="tension_arterielle_add" name="tension_arterielle"
                     class="form-control" placeholder="Ex : 120/80">
            </div>
            <div class="form-group col-md-3">
              <label for="temperature_add">Température (°C)</label>
              <input type="number" id="temperature_add" name="temperature" step="0.1"
                     class="form-control" placeholder="Ex : 37.2">
            </div>
            <div class="form-group col-md-3">
              <label for="poids_add">Poids (kg)</label>
              <input type="number" id="poids_add" name="poids" step="0.1"
                     class="form-control" placeholder="Ex : 70.5">
            </div>
            <div class="form-group col-md-3">
              <label for="taille_add">Taille (cm)</label>
              <input type="number" id="taille_add" name="taille" step="0.1"
                     class="form-control" placeholder="Ex : 175">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="frequence_cardiaque_add">Fréquence cardiaque (bpm)</label>
              <input type="number" id="frequence_cardiaque_add" name="frequence_cardiaque"
                     class="form-control" placeholder="Ex : 75">
            </div>
            <div class="form-group col-md-6">
              <label for="frequence_respiratoire_add">Fréquence respiratoire (cpm)</label>
              <input type="number" id="frequence_respiratoire_add" name="frequence_respiratoire"
                     class="form-control" placeholder="Ex : 16">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="imc_add">IMC (kg/m²)</label>
              <input type="number" id="imc_add" name="imc" step="0.1"
                     class="form-control" placeholder="Ex : 22.9">
            </div>
            <div class="form-group col-md-6">
              <label for="glycemie_add">Glycémie (g/L)</label>
              <input type="number" id="glycemie_add" name="glycemie" step="0.01"
                     class="form-control" placeholder="Ex : 0.95">
            </div>
          </div>
          <div class="form-group">
            <label for="saturation_oxygene_add">Saturation O₂ (%)</label>
            <input type="number" id="saturation_oxygene_add" name="saturation_oxygene" step="0.1"
                   class="form-control" placeholder="Ex : 98">
          </div>
          
          <!-- 4. Motif, Symptômes, Diagnostic -->
          <div class="form-group">
            <label for="motif_add">Motif <span class="text-danger">*</span></label>
            <textarea id="motif_add" name="motif" class="form-control" rows="2" required></textarea>
          </div>
          <div class="form-group">
            <label for="symptomes_add">Symptômes</label>
            <textarea id="symptomes_add" name="symptomes" class="form-control" rows="2"></textarea>
          </div>
          <div class="form-group">
            <label for="diagnostic_add">Diagnostic <span class="text-danger">*</span></label>
            <textarea id="diagnostic_add" name="diagnostic" class="form-control" rows="2" required></textarea>
          </div>
          
          <!-- 5. Observations & Traitement -->
          <div class="form-group">
            <label for="observations_add">Observations</label>
            <textarea id="observations_add" name="observations" class="form-control" rows="3"></textarea>
          </div>

          
          <!-- 6. Ordonnance & Médicaments -->
          <h5 class="mt-4">Ordonnance</h5>
          <div class="form-row">
            <div class="form-group col-md-4">
              <label for="type_ordonnance_add">Type d'ordonnance</label>
              <select id="type_ordonnance_add" name="type_ordonnance" class="form-control">
                <option value="Standard">Standard</option>
                <option value="Urgence">Urgence</option>
              </select>
            </div>
            <div class="form-group col-md-8">
              <label for="instructions_add">Instructions</label>
              <textarea id="instructions_add" name="instructions" class="form-control" rows="2"></textarea>
            </div>
          </div>
          <div class="form-group">
            <label>Médicaments</label>
            <table class="table table-sm" id="table_medicaments">
              <thead>
                <tr>
                  <th>Nom</th><th>Posologie</th><th>Durée</th><th></th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
            <button type="button" id="add_medicament" class="btn btn-secondary btn-sm">
              <i class="fa fa-plus"></i> Ajouter un médicament
            </button>
          </div>
          
          <!-- 7. Examens de laboratoire -->
          <h5 class="mt-4">Examens de laboratoire</h5>
          <table class="table table-sm" id="table_examens">
            <thead>
              <tr>
                <th>Type</th><th>Motif</th><th>Urgent</th><th></th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
          <button type="button" id="add_examen" class="btn btn-secondary btn-sm">
            <i class="fa fa-plus"></i> Ajouter un examen
          </button>
          
          <!-- 8. Résultats de laboratoire -->


          
        </div>
        
        <!-- Pied de modal -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Ajouter</button>
        </div>
      </form>
      
    </div>
  </div>
</div>


<?php 
// pages/modals/modal_consultation.php

// Connexion PDO
include __DIR__ . '/../../api/modules/connect_db_pdo.php';

// 1. Charger la liste des patients
$reqPatients = $bdd->prepare("
    SELECT id, CONCAT(nom,' ',prenom) AS nom_complet
      FROM patient
     ORDER BY nom, prenom
");
$reqPatients->execute();
$patients = $reqPatients->fetchAll(PDO::FETCH_ASSOC);

// 2. Charger la liste des médecins
$reqMedecins = $bdd->prepare("
    SELECT id_utilisateur AS id,
           CONCAT(nom,' ',prenom) AS nom_complet,
           poste
      FROM utilisateur
     WHERE type_compte = 'Medecin'
     ORDER BY nom, prenom
");
$reqMedecins->execute();
$medecins = $reqMedecins->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Modal Ajouter Consultation -->
<div class="modal fade" id="ajouter_consultation" tabindex="-1" role="dialog" aria-labelledby="ajouterConsultationLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="ajouterConsultationLabel">Nouvelle consultation</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form action="../api/modules/ajouter_consultation.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <!-- Patient & Médecin -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="id_patient_add">Patient *</label>
              <select id="id_patient_add" name="id_patient" class="form-control" required>
                <option value="">Sélectionner un patient…</option>
                <?php foreach ($patients as $p): ?>
                  <option value="<?= $p['id'] ?>">
                    <?= htmlspecialchars($p['nom_complet']) ?> (ID <?= $p['id'] ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="id_medecin_add">Médecin *</label>
              <select id="id_medecin_add" name="id_medecin" class="form-control" required>
                <option value="">Sélectionner un médecin…</option>
                <?php foreach ($medecins as $m): ?>
                  <option value="<?= $m['id'] ?>">
                    <?= htmlspecialchars($m['nom_complet']) ?> — <?= htmlspecialchars($m['poste']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Date -->
          <div class="form-group">
            <label for="date_consultation_add">Date de consultation *</label>
            <input type="datetime-local" id="date_consultation_add" name="date_consultation"
                   class="form-control" required>
          </div>

          <!-- Constantes vitales -->
          <h5 class="mt-4">Constantes vitales</h5>
          <div class="form-row">
            <div class="form-group col-md-3">
              <label for="tension_arterielle_add">Tension artérielle</label>
              <input type="text" id="tension_arterielle_add" name="tension_arterielle" class="form-control" placeholder="120/80">
            </div>
            <div class="form-group col-md-3">
              <label for="temperature_add">Température (°C)</label>
              <input type="number" id="temperature_add" name="temperature" step="0.1" class="form-control" placeholder="37.2">
            </div>
            <div class="form-group col-md-3">
              <label for="poids_add">Poids (kg)</label>
              <input type="number" id="poids_add" name="poids" step="0.1" class="form-control" placeholder="70.5">
            </div>
            <div class="form-group col-md-3">
              <label for="taille_add">Taille (cm)</label>
              <input type="number" id="taille_add" name="taille" step="0.1" class="form-control" placeholder="175">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="frequence_cardiaque_add">Fréquence cardiaque (bpm)</label>
              <input type="number" id="frequence_cardiaque_add" name="frequence_cardiaque" class="form-control" placeholder="75">
            </div>
            <div class="form-group col-md-6">
              <label for="frequence_respiratoire_add">Fréquence respiratoire (cpm)</label>
              <input type="number" id="frequence_respiratoire_add" name="frequence_respiratoire" class="form-control" placeholder="16">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="imc_add">IMC (kg/m²)</label>
              <input type="number" id="imc_add" name="imc" step="0.1" class="form-control" placeholder="22.9">
            </div>
            <div class="form-group col-md-6">
              <label for="glycemie_add">Glycémie (g/L)</label>
              <input type="number" id="glycemie_add" name="glycemie" step="0.01" class="form-control" placeholder="0.95">
            </div>
          </div>
          <div class="form-group">
            <label for="saturation_oxygene_add">Saturation O₂ (%)</label>
            <input type="number" id="saturation_oxygene_add" name="saturation_oxygene" step="0.1" class="form-control" placeholder="98">
          </div>

          <!-- Motif, Symptômes, Diagnostic -->
          <div class="form-group">
            <label for="motif_add">Motif *</label>
            <textarea id="motif_add" name="motif" class="form-control" rows="2" required></textarea>
          </div>
          <div class="form-group">
            <label for="symptomes_add">Symptômes</label>
            <textarea id="symptomes_add" name="symptomes" class="form-control" rows="2"></textarea>
          </div>
          <div class="form-group">
            <label for="diagnostic_add">Diagnostic *</label>
            <textarea id="diagnostic_add" name="diagnostic" class="form-control" rows="2" required></textarea>
          </div>

          <!-- Observations & Traitement -->
          <div class="form-group">
            <label for="observations_add">Observations</label>
            <textarea id="observations_add" name="observations" class="form-control" rows="3"></textarea>
          </div>


          <!-- Ordonnance & Médicaments -->
          <h5 class="mt-4">Ordonnance</h5>
          <div class="form-row">
            <div class="form-group col-md-4">
              <label for="type_ordonnance_add">Type</label>
              <select id="type_ordonnance_add" name="type_ordonnance" class="form-control">
                <option value="Standard">Standard</option>
                <option value="Urgence">Urgence</option>
              </select>
            </div>
            <div class="form-group col-md-8">
              <label for="instructions_add">Instructions</label>
              <textarea id="instructions_add" name="instructions" class="form-control" rows="2"></textarea>
            </div>
          </div>
          <div class="form-group">
            <label>Médicaments</label>
            <table class="table table-sm" id="table_medicaments">
              <thead>
                <tr><th>Nom</th><th>Posologie</th><th>Durée</th><th></th></tr>
              </thead>
              <tbody></tbody>
            </table>
            <button type="button" id="add_medicament" class="btn btn-secondary btn-sm">
              <i class="fa fa-plus"></i> Ajouter un médicament
            </button>
          </div>

          <!-- Examens de laboratoire -->
          <h5 class="mt-4">Examens de laboratoire</h5>
          <table class="table table-sm" id="table_examens">
            <thead>
              <tr><th>Type</th><th>Motif</th><th>Urgent</th><th></th></tr>
            </thead>
            <tbody></tbody>
          </table>
          <button type="button" id="add_examen" class="btn btn-secondary btn-sm">
            <i class="fa fa-plus"></i> Ajouter un examen
          </button>

          <!-- Résultats de laboratoire -->

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Ajouter</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
// pages/modals/modal_consultation.php

// Connexion PDO
include __DIR__ . '/../../api/modules/connect_db_pdo.php';

// Récupérer patients
$reqPatients = $bdd->prepare("
    SELECT id, CONCAT(nom,' ',prenom) AS nom_complet
      FROM patient
     ORDER BY nom, prenom
");
$reqPatients->execute();
$patients = $reqPatients->fetchAll(PDO::FETCH_ASSOC);

// Récupérer médecins
$reqMedecins = $bdd->prepare("
    SELECT id_utilisateur AS id,
           CONCAT(nom,' ',prenom) AS nom_complet,
           poste
      FROM utilisateur
     WHERE type_compte = 'Medecin'
     ORDER BY nom, prenom
");
$reqMedecins->execute();
$medecins = $reqMedecins->fetchAll(PDO::FETCH_ASSOC);
?>

<?php
// pages/modals/modal_modifier_consultation.php

// Connexion PDO
include __DIR__ . '/../../api/modules/connect_db_pdo.php';

// 1. Charger la liste des patients
$reqPatients = $bdd->prepare("
    SELECT id, CONCAT(nom,' ',prenom) AS nom_complet
      FROM patient
     ORDER BY nom, prenom
");
$reqPatients->execute();
$patients = $reqPatients->fetchAll(PDO::FETCH_ASSOC);

// 2. Charger la liste des médecins
$reqMedecins = $bdd->prepare("
    SELECT id_utilisateur AS id,
           CONCAT(nom,' ',prenom) AS nom_complet,
           poste
      FROM utilisateur
     WHERE type_compte = 'Medecin'
     ORDER BY nom, prenom
");
$reqMedecins->execute();
$medecins = $reqMedecins->fetchAll(PDO::FETCH_ASSOC);
?>

<?php
// pages/modals/modal_modifier_consultation.php

// Connexion PDO
require __DIR__ . '/../../api/modules/connect_db_pdo.php';

// Charger patients
$reqP = $bdd->query("
  SELECT id, CONCAT(nom,' ',prenom) AS nom_complet
    FROM patient
   ORDER BY nom, prenom
");
$patients = $reqP->fetchAll(PDO::FETCH_ASSOC);

// Charger médecins
$reqM = $bdd->query("
  SELECT id_utilisateur AS id,
         CONCAT(nom,' ',prenom) AS nom_complet,
         poste
    FROM utilisateur
   WHERE type_compte = 'Medecin'
   ORDER BY nom, prenom
");
$medecins = $reqM->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Modal Modifier Consultation -->
<div class="modal fade" id="modifier_consultation" tabindex="-1" role="dialog"
     aria-labelledby="modifierConsultationLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="modifierConsultationLabel">Modifier consultation</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <form action="../api/modules/modifier_consultation.php"
            method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id_consultation" id="id_consultation_mod">

        <div class="modal-body">

          <!-- 1. Patient & Médecin -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="id_patient_mod">Patient *</label>
              <select id="id_patient_mod" name="id_patient" class="form-control" required>
                <option value="">Sélectionner un patient…</option>
                <?php foreach ($patients as $p): ?>
                  <option value="<?= $p['id'] ?>">
                    <?= htmlspecialchars($p['nom_complet']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="id_medecin_mod">Médecin *</label>
              <select id="id_medecin_mod" name="id_medecin" class="form-control" required>
                <option value="">Sélectionner un médecin…</option>
                <?php foreach ($medecins as $m): ?>
                  <option value="<?= $m['id'] ?>">
                    <?= htmlspecialchars($m['nom_complet']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- 2. Date de consultation -->
          <div class="form-group">
            <label for="date_consultation_mod">Date de consultation *</label>
            <input type="datetime-local" id="date_consultation_mod"
                   name="date_consultation" class="form-control" required>
          </div>

          <!-- 3. Constantes vitales -->
          <h5 class="mt-4">Constantes vitales</h5>
          <div class="form-row">
            <div class="form-group col-md-3">
              <label for="tension_arterielle_mod">Tension artérielle</label>
              <input type="text" id="tension_arterielle_mod"
                     name="tension_arterielle" class="form-control">
            </div>
            <div class="form-group col-md-3">
              <label for="temperature_mod">Température (°C)</label>
              <input type="number" id="temperature_mod"
                     name="temperature" step="0.1" class="form-control">
            </div>
            <div class="form-group col-md-3">
              <label for="poids_mod">Poids (kg)</label>
              <input type="number" id="poids_mod"
                     name="poids" step="0.1" class="form-control">
            </div>
            <div class="form-group col-md-3">
              <label for="taille_mod">Taille (cm)</label>
              <input type="number" id="taille_mod"
                     name="taille" step="0.1" class="form-control">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="frequence_cardiaque_mod">Fréquence cardiaque (bpm)</label>
              <input type="number" id="frequence_cardiaque_mod"
                     name="frequence_cardiaque" class="form-control">
            </div>
            <div class="form-group col-md-6">
              <label for="frequence_respiratoire_mod">Fréquence respiratoire (cpm)</label>
              <input type="number" id="frequence_respiratoire_mod"
                     name="frequence_respiratoire" class="form-control">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="imc_mod">IMC (kg/m²)</label>
              <input type="number" id="imc_mod" name="imc"
                     step="0.1" class="form-control">
            </div>
            <div class="form-group col-md-6">
              <label for="glycemie_mod">Glycémie (g/L)</label>
              <input type="number" id="glycemie_mod"
                     name="glycemie" step="0.01" class="form-control">
            </div>
          </div>
          <div class="form-group">
            <label for="saturation_oxygene_mod">Saturation O₂ (%)</label>
            <input type="number" id="saturation_oxygene_mod"
                   name="saturation_oxygene" step="0.1" class="form-control">
          </div>

          <!-- 4. Motif, Symptômes, Diagnostic -->
          <div class="form-group">
            <label for="motif_mod">Motif *</label>
            <textarea id="motif_mod" name="motif" class="form-control" rows="2" required></textarea>
          </div>
          <div class="form-group">
            <label for="symptomes_mod">Symptômes</label>
            <textarea id="symptomes_mod" name="symptomes" class="form-control" rows="2"></textarea>
          </div>
          <div class="form-group">
            <label for="diagnostic_mod">Diagnostic *</label>
            <textarea id="diagnostic_mod" name="diagnostic" class="form-control" rows="2" required></textarea>
          </div>

          <!-- 5. Observations -->
          <div class="form-group">
            <label for="observations_mod">Observations</label>
            <textarea id="observations_mod" name="observations" class="form-control" rows="3"></textarea>
          </div>

          <!-- 6. Ordonnance -->
          <h5 class="mt-4">Ordonnance</h5>
          <div class="form-row">
            <div class="form-group col-md-4">
              <label for="type_ordonnance_mod">Type</label>
              <select id="type_ordonnance_mod" name="type_ordonnance" class="form-control">
                <option value="Standard">Standard</option>
                <option value="Urgence">Urgence</option>
              </select>
            </div>
            <div class="form-group col-md-8">
              <label for="instructions_mod">Instructions</label>
              <textarea id="instructions_mod" name="instructions" class="form-control" rows="2"></textarea>
            </div>
          </div>

          <!-- 7. Médicaments -->
          <div class="form-group">
            <label>Médicaments</label>
            <table class="table table-sm" id="table_medicaments_mod">
              <thead>
                <tr><th>Nom</th><th>Posologie</th><th>Durée</th><th></th></tr>
              </thead>
              <tbody></tbody>
            </table>
            <button type="button" id="add_medicament_mod" class="btn btn-secondary btn-sm">
              <i class="fa fa-plus"></i> Ajouter un médicament
            </button>
          </div>

          <!-- 8. Examens de laboratoire -->
          <h5 class="mt-4">Examens de laboratoire</h5>
          <table class="table table-sm" id="table_examens_mod">
            <thead>
              <tr><th>Type</th><th>Motif</th><th>Urgent</th><th></th></tr>
            </thead>
            <tbody></tbody>
          </table>
          <button type="button" id="add_examen_mod" class="btn btn-secondary btn-sm">
            <i class="fa fa-plus"></i> Ajouter un examen
          </button>

          <!-- 9. Résultats de laboratoire -->
          <h5 class="mt-4">Résultats de laboratoire</h5>
          <table class="table table-sm" id="table_resultats_mod">
            <thead>
              <tr><th>Examen</th><th>Texte</th><th>Fichier</th><th></th></tr>
            </thead>
            <tbody></tbody>
          </table>
          <button type="button" id="add_resultat_mod" class="btn btn-secondary btn-sm">
            <i class="fa fa-plus"></i> Ajouter un résultat
          </button>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-success">Modifier</button>
        </div>
      </form>
    </div>
  </div>
</div>
