<?php
// modals/modal_laboratoire.php
// Pas de require : on injecte tout via JS
?>
<!-- Modal Ajouter Résultat -->
<div class="modal fade" id="modal_ajouter_resultat" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="../api/modules/ajouter_laboratoire.php"
          method="post"
          enctype="multipart/form-data">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Ajouter un résultat</h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="aj_id_examen">Examen</label>
            <select name="id_examen" id="aj_id_examen" class="form-control">
              <!-- options injectées par JS -->
            </select>
          </div>
          <div class="form-group">
            <label for="aj_contenu">Contenu texte</label>
            <textarea id="aj_contenu" name="contenu_texte"
                      class="form-control" rows="4"></textarea>
          </div>
          <div class="form-group">
            <label for="aj_fichier">Fichier (optionnel)</label>
            <input type="file" id="aj_fichier" name="fichier"
                   class="form-control-file">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Enregistrer</button>
          <button type="button" class="btn btn-secondary"
                  data-dismiss="modal">Annuler</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Modifier Résultat (inchangé côté examen) -->
<div class="modal fade" id="modal_modifier_resultat" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="../api/modules/modifier_laboratoire.php"
          method="post" enctype="multipart/form-data">
      <input type="hidden" name="id_resultat" id="mod_id_resultat">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Modifier le résultat</h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="mod_examen_info">Examen</label>
            <input type="text" id="mod_examen_info"
                   class="form-control" readonly>
          </div>
          <div class="form-group">
            <label for="mod_contenu">Contenu texte</label>
            <textarea id="mod_contenu" name="contenu_texte"
                      class="form-control" rows="4"></textarea>
          </div>
          <div class="form-group">
            <label>Fichier actuel</label>
            <div id="mod_fichier_actuel">–</div>
          </div>
          <div class="form-group">
            <label for="mod_fichier">Remplacer le fichier</label>
            <input type="file" id="mod_fichier" name="fichier"
                   class="form-control-file">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-warning">Mettre à jour</button>
          <button type="button" class="btn btn-secondary"
                  data-dismiss="modal">Annuler</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Supprimer Résultat (inchangé) -->
<div class="modal fade" id="modal_supprimer_resultat" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="../api/modules/supprimer_laboratoire.php" method="post">
      <input type="hidden" name="id_resultat" id="del_id_resultat">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Supprimer le résultat</h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>Voulez-vous vraiment supprimer ce résultat ?</p>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger">Supprimer</button>
          <button type="button" class="btn btn-secondary"
                  data-dismiss="modal">Annuler</button>
        </div>
      </div>
    </form>
  </div>
</div>
