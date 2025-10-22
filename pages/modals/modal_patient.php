<?php
// modals/modal_patient.php
// Contient :
// 1) modal d'ajout (avec scroll & section Assurance conditionnelle)
// 2) modal de modification (idem ajout, scrollable)
// 3) modal de suppression

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!-- 1) MODAL : Ajouter un patient -->
<div class="modal fade" id="ajouter_patient" tabindex="-1" aria-labelledby="ajouterPatientLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ajouterPatientLabel">Ajout d'un nouveau patient</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="../api/modules/ajouter_patient.php"
            method="POST"
            enctype="multipart/form-data">
        <div class="modal-body" style="max-height:calc(100vh - 200px);overflow-y:auto;">

          <!-- 1. Nom & Prénom -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="nom">Nom *</label>
              <input type="text" id="nom" name="nom" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
              <label for="prenom">Prénom(s) *</label>
              <input type="text" id="prenom" name="prenom" class="form-control" required>
            </div>
          </div>

          <!-- 2. Sexe & Date de naissance -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="sexe">Sexe *</label>
              <select id="sexe" name="sexe" class="form-control" required>
                <option value="">Sélectionner...</option>
                <option value="Masculin">Masculin</option>
                <option value="Féminin">Féminin</option>
                <option value="Autre">Autre</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="date_naissance">Date de naissance</label>
              <input type="date" id="date_naissance" name="date_naissance" class="form-control">
            </div>
          </div>

          <!-- 3. Lieu de naissance & Nationalité -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="lieu_naissance">Lieu de naissance</label>
              <input type="text" id="lieu_naissance" name="lieu_naissance" class="form-control">
            </div>
            <div class="form-group col-md-6">
              <label for="nationalite">Nationalité</label>
              <input type="text" id="nationalite" name="nationalite" class="form-control">
            </div>
          </div>

          <!-- 4. État civil & Profession -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="etat_civil">État civil</label>
              <select id="etat_civil" name="etat_civil" class="form-control">
                <option value="">Sélectionner...</option>
                <option value="Célibataire">Célibataire</option>
                <option value="Marié(e)">Marié(e)</option>
                <option value="Divorcé(e)">Divorcé(e)</option>
                <option value="Veuf(ve)">Veuf(ve)</option>
                <option value="Union libre">Union libre</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="profession">Profession</label>
              <input type="text" id="profession" name="profession" class="form-control">
            </div>
          </div>

          <!-- 5. Adresse & Ville -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="adresse">Adresse</label>
              <input type="text" id="adresse" name="adresse" class="form-control">
            </div>
            <div class="form-group col-md-6">
              <label for="ville">Ville</label>
              <input type="text" id="ville" name="ville" class="form-control">
            </div>
          </div>

          <!-- 6. Quartier & Téléphone -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="quartier">Quartier</label>
              <input type="text" id="quartier" name="quartier" class="form-control">
            </div>
            <div class="form-group col-md-6">
              <label for="telephone">Téléphone *</label>
              <input type="text" id="telephone" name="telephone" class="form-control" required>
            </div>
          </div>

          <!-- 7. Téléphone secondaire & Email -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="telephone_secondaire">Téléphone secondaire</label>
              <input type="text" id="telephone_secondaire" name="telephone_secondaire" class="form-control">
            </div>
            <div class="form-group col-md-6">
              <label for="email">Email</label>
              <input type="email" id="email" name="email" class="form-control">
            </div>
          </div>

          <!-- 8. Groupe sanguin & Handicap -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="groupe_sanguin">Groupe sanguin</label>
              <select id="groupe_sanguin" name="groupe_sanguin" class="form-control">
                <option value="">Sélectionner...</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="situation_handicap">Situation de handicap</label>
              <select id="situation_handicap" name="situation_handicap" class="form-control">
                <option value="0">Non</option>
                <option value="1">Oui</option>
              </select>
            </div>
          </div>

          <!-- 9. Poids & Tension -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="poids">Poids (kg)</label>
              <input type="number" id="poids" name="poids" class="form-control" step="0.1">
            </div>
            <div class="form-group col-md-6">
              <label for="tension">Tension artérielle</label>
              <input type="text" id="tension" name="tension" class="form-control">
            </div>
          </div>

          <!-- 10. Allergies & Antécédents -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="allergie">Allergies</label>
              <textarea id="allergie" name="allergie" class="form-control" rows="2"></textarea>
            </div>
            <div class="form-group col-md-6">
              <label for="antecedents_medicaux">Antécédents médicaux</label>
              <textarea id="antecedents_medicaux" name="antecedents_medicaux" class="form-control" rows="2"></textarea>
            </div>
          </div>

          <!-- 11. Contact urgence -->
          <div class="form-row">
            <div class="form-group col-md-4">
              <label for="personne_contact_nom">Contact (Nom)</label>
              <input type="text" id="personne_contact_nom" name="personne_contact_nom" class="form-control">
            </div>
            <div class="form-group col-md-4">
              <label for="personne_contact_lien">Lien</label>
              <input type="text" id="personne_contact_lien" name="personne_contact_lien" class="form-control">
            </div>
            <div class="form-group col-md-4">
              <label for="personne_contact_tel">Téléphone</label>
              <input type="text" id="personne_contact_tel" name="personne_contact_tel" class="form-control">
            </div>
          </div>

          <!-- 12. Photo -->
          <div class="form-row">
            <div class="form-group col-md-12">
              <label for="photo">Photo</label>
              <input type="file" id="photo" name="photo" class="form-control-file">
            </div>
          </div>

          <!-- 13. Assurance du patient -->
          <h5 class="mt-3">Assurance du patient</h5>
          <div class="form-row mb-2">
            <div class="form-group col-md-12">
              <label>Statut assurance *</label><br>
              <label class="mr-3">
                <input type="radio" name="assurance_statut" value="Assuré" required>
                Assuré
              </label>
              <label>
                <input type="radio" name="assurance_statut" value="Non assuré" checked>
                Non assuré
              </label>
            </div>
          </div>
          <div id="section_assurance" style="display:none;">
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="assurance_compagnie">Compagnie d'assurance</label>
                <select id="assurance_compagnie" name="assurance_compagnie" class="form-control">
                  <option value="">Sélectionner...</option>
                  <option value="NSIA">NSIA</option>
                  <option value="SAGO">SAGO</option>
                  <option value="Allianz">Allianz</option>
                </select>
              </div>
              <div class="form-group col-md-6">
                <label for="numero_police">Numéro de police</label>
                <input type="text" id="numero_police" name="numero_police" class="form-control">
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-md-4">
                <label for="taux_couverture">Taux de couverture (%)</label>
                <input type="number" id="taux_couverture" name="taux_couverture"
                       class="form-control" min="0" max="100" step="0.01">
              </div>
              <div class="form-group col-md-4">
                <label for="type_couverture">Couverture</label>
                <select id="type_couverture" name="type_couverture" class="form-control">
                  <option value="">Sélectionner...</option>
                  <option value="Consultation">Consultation</option>
                  <option value="Examen">Examen</option>
                  <option value="Tous">Tous</option>
                </select>
              </div>
              <div class="form-group col-md-4">
                <label for="justificatif_assurance">Pièce justificative</label>
                <input type="file" id="justificatif_assurance" name="justificatif_assurance" class="form-control-file">
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="date_debut_couverture">Date début de couverture</label>
                <input type="date" id="date_debut_couverture" name="date_debut_couverture" class="form-control">
              </div>
              <div class="form-group col-md-6">
                <label for="date_fin_couverture">Date fin de couverture</label>
                <input type="date" id="date_fin_couverture" name="date_fin_couverture" class="form-control">
              </div>
            </div>
          </div>

        </div><!-- /.modal-body -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-success">Enregistrer</button>
        </div>
      </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- 2) MODAL : Modifier un patient -->
<div class="modal fade" id="modifier_patient" tabindex="-1" aria-labelledby="modifierPatientLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modifierPatientLabel">Modification d'un patient</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="../api/modules/modifier_patient.php" method="post" enctype="multipart/form-data">
        <div class="modal-body" style="max-height:calc(100vh - 200px); overflow-y:auto;">
          <input type="hidden" id="id_patient_modif" name="id_patient">

          <!-- 1. Nom & Prénom -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="nom_modif">Nom *</label>
              <input type="text" id="nom_modif" name="nom" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
              <label for="prenom_modif">Prénom(s) *</label>
              <input type="text" id="prenom_modif" name="prenom" class="form-control" required>
            </div>
          </div>

          <!-- 2. Sexe & Date de naissance -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="sexe_modif">Sexe *</label>
              <select id="sexe_modif" name="sexe" class="form-control" required>
                <option value="">Sélectionner…</option>
                <option value="Masculin">Masculin</option>
                <option value="Féminin">Féminin</option>
                <option value="Autre">Autre</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="date_naissance_modif">Date de naissance</label>
              <input type="date" id="date_naissance_modif" name="date_naissance" class="form-control">
            </div>
          </div>

          <!-- 3. Lieu de naissance & Nationalité -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="lieu_naissance_modif">Lieu de naissance</label>
              <input type="text" id="lieu_naissance_modif" name="lieu_naissance" class="form-control">
            </div>
            <div class="form-group col-md-6">
              <label for="nationalite_modif">Nationalité</label>
              <input type="text" id="nationalite_modif" name="nationalite" class="form-control">
            </div>
          </div>

          <!-- 4. État civil & Profession -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="etat_civil_modif">État civil</label>
              <select id="etat_civil_modif" name="etat_civil" class="form-control">
                <option value="">Sélectionner…</option>
                <option value="Célibataire">Célibataire</option>
                <option value="Marié(e)">Marié(e)</option>
                <option value="Divorcé(e)">Divorcé(e)</option>
                <option value="Veuf(ve)">Veuf(ve)</option>
                <option value="Union libre">Union libre</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="profession_modif">Profession</label>
              <input type="text" id="profession_modif" name="profession" class="form-control">
            </div>
          </div>

          <!-- 5. Adresse & Ville -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="adresse_modif">Adresse</label>
              <input type="text" id="adresse_modif" name="adresse" class="form-control">
            </div>
            <div class="form-group col-md-6">
              <label for="ville_modif">Ville</label>
              <input type="text" id="ville_modif" name="ville" class="form-control">
            </div>
          </div>

          <!-- 6. Quartier & Téléphone -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="quartier_modif">Quartier</label>
              <input type="text" id="quartier_modif" name="quartier" class="form-control">
            </div>
            <div class="form-group col-md-6">
              <label for="telephone_modif">Téléphone *</label>
              <input type="text" id="telephone_modif" name="telephone" class="form-control" required>
            </div>
          </div>

          <!-- 7. Téléphone secondaire & Email -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="telephone_secondaire_modif">Téléphone secondaire</label>
              <input type="text" id="telephone_secondaire_modif" name="telephone_secondaire" class="form-control">
            </div>
            <div class="form-group col-md-6">
              <label for="email_modif">Email</label>
              <input type="email" id="email_modif" name="email" class="form-control">
            </div>
          </div>

          <!-- 8. Groupe sanguin & Handicap -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="groupe_sanguin_modif">Groupe sanguin</label>
              <select id="groupe_sanguin_modif" name="groupe_sanguin" class="form-control">
                <option value="">Sélectionner…</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="situation_handicap_modif">Situation de handicap</label>
              <select id="situation_handicap_modif" name="situation_handicap" class="form-control">
                <option value="0">Non</option>
                <option value="1">Oui</option>
              </select>
            </div>
          </div>

          <!-- 9. Poids & Tension -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="poids_modif">Poids (kg)</label>
              <input type="number" id="poids_modif" name="poids" class="form-control" step="0.1">
            </div>
            <div class="form-group col-md-6">
              <label for="tension_modif">Tension artérielle</label>
              <input type="text" id="tension_modif" name="tension" class="form-control">
            </div>
          </div>

          <!-- 10. Allergies & Antécédents médicaux -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="allergie_modif">Allergies</label>
              <textarea id="allergie_modif" name="allergie" class="form-control" rows="2"></textarea>
            </div>
            <div class="form-group col-md-6">
              <label for="antecedents_medicaux_modif">Antécédents médicaux</label>
              <textarea id="antecedents_medicaux_modif" name="antecedents_medicaux" class="form-control" rows="2"></textarea>
            </div>
          </div>

          <!-- 11. Personne de contact -->
          <div class="form-row">
            <div class="form-group col-md-4">
              <label for="personne_contact_nom_modif">Nom du contact</label>
              <input type="text" id="personne_contact_nom_modif" name="personne_contact_nom" class="form-control">
            </div>
            <div class="form-group col-md-4">
              <label for="personne_contact_lien_modif">Lien</label>
              <input type="text" id="personne_contact_lien_modif" name="personne_contact_lien" class="form-control">
            </div>
            <div class="form-group col-md-4">
              <label for="personne_contact_tel_modif">Téléphone</label>
              <input type="text" id="personne_contact_tel_modif" name="personne_contact_tel" class="form-control">
            </div>
          </div>

          <!-- 12. Photo -->
          <div class="form-row">
            <div class="form-group col-md-12">
              <label for="photo_modif">Photo</label>
              <input type="file" id="photo_modif" name="photo" class="form-control-file">
              <small class="form-text text-muted">Laissez vide pour conserver la photo actuelle</small>
            </div>
          </div>

          <!-- 13. Assurance du patient -->
          <h5 class="mt-3">Assurance du patient</h5>
          <div class="form-row mb-2">
            <div class="form-group col-12">
              <label>Statut assurance *</label><br>
              <label class="mr-3">
                <input type="radio" name="assurance_statut" value="Assuré" required> Assuré
              </label>
              <label>
                <input type="radio" name="assurance_statut" value="Non assuré"> Non assuré
              </label>
            </div>
          </div>
          <div id="section_assurance_modif" style="display:none;">
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="assurance_compagnie_modif">Compagnie d'assurance</label>
                <select id="assurance_compagnie_modif" name="assurance_compagnie" class="form-control">
                  <option value="">Sélectionner…</option>
                  <option value="NSIA">NSIA</option>
                  <option value="SAGO">SAGO</option>
                  <option value="Allianz">Allianz</option>
                </select>
              </div>
              <div class="form-group col-md-6">
                <label for="numero_police_modif">Numéro de police</label>
                <input type="text" id="numero_police_modif" name="numero_police" class="form-control">
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-md-4">
                <label for="taux_couverture_modif">Taux de couverture (%)</label>
                <input type="number" id="taux_couverture_modif" name="taux_couverture" class="form-control" min="0" max="100" step="0.01">
              </div>
              <div class="form-group col-md-4">
                <label for="type_couverture_modif">Couverture</label>
                <select id="type_couverture_modif" name="type_couverture" class="form-control">
                  <option value="">Sélectionner…</option>
                  <option value="Consultation">Consultation</option>
                  <option value="Examen">Examen</option>
                  <option value="Tous">Tous</option>
                </select>
              </div>
              <div class="form-group col-md-4">
                <label for="justificatif_assurance_modif">Justificatif</label>
                <input type="file" id="justificatif_assurance_modif" name="justificatif_assurance" class="form-control-file">
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="date_debut_couverture_modif">Début de couverture</label>
                <input type="date" id="date_debut_couverture_modif" name="date_debut_couverture" class="form-control">
              </div>
              <div class="form-group col-md-6">
                <label for="date_fin_couverture_modif">Fin de couverture</label>
                <input type="date" id="date_fin_couverture_modif" name="date_fin_couverture" class="form-control">
              </div>
            </div>
          </div>

        </div><!-- /.modal-body -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-warning">Modifier</button>
        </div>
      </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- 3) MODAL : Supprimer un patient -->
<div class="modal fade" id="supprimer_patient" tabindex="-1" aria-labelledby="supprimerPatientLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="../api/modules/supprimer_patient.php" method="post">
      <input type="hidden" name="id_patient" id="id_patient_suppr">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="supprimerPatientLabel">Supprimer un patient</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          Voulez-vous vraiment supprimer ce patient ?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-danger">Supprimer</button>
        </div>
      </div>
    </form>
  </div>
</div>