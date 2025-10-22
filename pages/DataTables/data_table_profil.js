// pages/Datatables/data_table_profil.js
// Pré-remplit le modal "Modifier mon profil" lors de son ouverture

document.addEventListener('DOMContentLoaded', function() {
    $('#modifier_profil').on('show.bs.modal', function () {
      // Cet endpoint est relatif à pages/profil.php
      const apiUrl = '../api/modules/profil_data.php';
  
      fetch(apiUrl, { credentials: 'same-origin' })
        .then(response => {
          if (!response.ok) throw new Error('Statut HTTP ' + response.status);
          return response.json();
        })
        .then(data => {
          if (data.error) {
            console.error('API profil error:', data.error);
            return;
          }
          // Remplissage des champs
          const modal = document.getElementById('modifier_profil');
          modal.querySelector('input[name="nom"]').value       = data.nom      || '';
          modal.querySelector('input[name="prenom"]').value    = data.prenom   || '';
          modal.querySelector('input[name="telephone"]').value = data.telephone|| '';
          modal.querySelector('input[name="email"]').value     = data.email    || '';
          modal.querySelector('input[name="username"]').value  = data.username || '';
        })
        .catch(err => {
          console.error('Impossible de charger le profil :', err);
        });
    });
  });
  