<?php
// pages/dossier_patient.php
// Affiche le dossier médical complet d'un patient + export PDF

session_start();

// ➊ Contrôle d’accès : Super Administrateur, Secretaire et Medecin
if (empty($_SESSION['id'])
    || ! in_array($_SESSION['type_compte'], [
         "Super Administrateur",
         "Secretaire",
         "Medecin"
       ], true)
) {
    header('Location: ../index.php?erreur=3');
    exit();
}

// … le reste de votre page …


if (!isset($_GET['id_patient']) || !is_numeric($_GET['id_patient'])) {
    header('Location: patient.php');
    exit();
}

$id_patient = (int) $_GET['id_patient'];
require __DIR__ . '/../api/modules/connect_db_pdo.php';

// 1) Informations personnelles
$stmtP = $bdd->prepare("
    SELECT
      p.*,
      DATE_FORMAT(p.date_naissance,     '%d/%m/%Y')         AS date_naiss,
      DATE_FORMAT(p.date_enregistrement, '%d/%m/%Y %H:%i')  AS date_enreg
    FROM patient p
    WHERE p.id = ? AND p.supprimer = 'NON'
");
$stmtP->execute([$id_patient]);
$patient = $stmtP->fetch(PDO::FETCH_ASSOC);
if (!$patient) {
    die('Patient introuvable.');
}

// 2) Consultations
$stmtC = $bdd->prepare("
    SELECT
      c.id_consultation,
      DATE_FORMAT(c.date_consultation, '%d/%m/%Y %H:%i') AS date_consult,
      CONCAT(u.nom,' ',u.prenom) AS medecin,
      c.motif, c.symptomes, c.diagnostic, c.observations
    FROM consultation c
    JOIN utilisateur u ON c.id_medecin = u.id_utilisateur
    WHERE c.id_patient = ? AND c.supprimer = 'NON'
    ORDER BY c.date_consultation DESC
");
$stmtC->execute([$id_patient]);
$consultations = $stmtC->fetchAll(PDO::FETCH_ASSOC);

// 3) Examens de laboratoire (jointure consultation → id_patient)
$stmtE = $bdd->prepare("
    SELECT
      e.id_examen,
      DATE_FORMAT(e.date_demande, '%d/%m/%Y %H:%i') AS date_demande,
      e.type_examen,
      e.motif,
      e.est_urgent
    FROM examen_labo e
    JOIN consultation c ON e.id_consultation = c.id_consultation
    WHERE c.id_patient = ? AND e.supprimer = 'NON'
    ORDER BY e.date_demande DESC
");
$stmtE->execute([$id_patient]);
$examens = $stmtE->fetchAll(PDO::FETCH_ASSOC);

// Résultats par examen
$rStmt = $bdd->prepare("
    SELECT
      DATE_FORMAT(date_resultat, '%d/%m/%Y %H:%i') AS date_res,
      contenu_texte,
      fichier
    FROM resultat_examen
    WHERE id_examen = ? AND supprimer = 'NON'
    ORDER BY date_resultat DESC
");
$resultats_by_ex = [];
foreach ($examens as $ex) {
    $rStmt->execute([$ex['id_examen']]);
    $resultats_by_ex[$ex['id_examen']] = $rStmt->fetchAll(PDO::FETCH_ASSOC);
}

// 4) Ordonnances + médicaments
$stmtO = $bdd->prepare("
    SELECT
      o.id_ordonnance,
      DATE_FORMAT(o.date_creation, '%d/%m/%Y') AS date_ord,
      o.type_ordonnance,
      o.instructions
    FROM ordonnance o
    WHERE o.id_patient = ? AND o.supprimer = 'NON'
    ORDER BY o.date_creation DESC
");
$stmtO->execute([$id_patient]);
$ordonnances = $stmtO->fetchAll(PDO::FETCH_ASSOC);

$mStmt = $bdd->prepare("
    SELECT medicament, posologie, duree
    FROM ordonnance_medicament
    WHERE id_ordonnance = ? AND supprimer = 'NON'
");
$med_by_ord = [];
foreach ($ordonnances as $o) {
    $mStmt->execute([$o['id_ordonnance']]);
    $med_by_ord[$o['id_ordonnance']] = $mStmt->fetchAll(PDO::FETCH_ASSOC);
}

// 5) Documents justificatifs
$docs = [];
if ($patient['assurance_statut'] === 'Assuré' && !empty($patient['justificatif_assurance'])) {
    $docs[] = [
      'titre' => 'Justificatif assurance',
      'url'   => '../uploads/assurances/' . $patient['justificatif_assurance']
    ];
}
if (!empty($patient['photo'])) {
    $docs[] = [
      'titre' => 'Photo patient',
      'url'   => '../uploads/patients/' . $patient['photo']
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Dossier Médical — <?= htmlspecialchars($patient['nom'].' '.$patient['prenom']) ?></title>
  <style>
    /* Police moderne et lisible */
    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');
    
    body { 
      margin:0; 
      padding:0; 
      font-family:'Roboto', Arial, sans-serif; 
      background:#f8f9fa; 
    }
    
    .page { 
      width:210mm; 
      min-height:297mm; 
      margin:20px auto; 
      background:#fff;
      padding:25mm; 
      box-shadow:0 0 15px rgba(0,0,0,.1); 
      position:relative;
      border:1px solid #e0e0e0;
    }
    
    .header { 
      display:flex; 
      justify-content:space-between; 
      align-items:center; 
      margin-bottom:25px;
      padding-bottom:15px;
      border-bottom:2px solid #e0e0e0;
    }
    
    .header img { 
      height:70px; 
      filter: drop-shadow(1px 1px 3px rgba(0,0,0,0.2));
    }
    
    .header .clinic { 
      text-align:right; 
      font-size:14px; 
      color:#555;
      line-height:1.4;
    }
    
    .header .clinic div:first-child {
      font-weight:bold;
      color:#2c3e50;
      font-size:16px;
    }
    
    h1 { 
      text-align:center; 
      margin:20px 0 30px; 
      color:#3498db; 
      font-size:28px;
      font-weight:600;
      text-transform:uppercase;
      letter-spacing:1px;
    }
    
    .section { 
      margin-bottom:30px;
      page-break-inside: avoid;
    }
    
    .section h2 { 
      font-size:20px; 
      color:#2c3e50; 
      border-bottom:2px solid #3498db; 
      padding-bottom:8px;
      margin-bottom:15px;
      font-weight:500;
    }
    
    .info-list { 
      margin-top:15px;
      display:grid;
      grid-template-columns:repeat(auto-fill, minmax(300px, 1fr));
      gap:8px 20px;
    }
    
    .info-list .item { 
      margin:5px 0; 
      font-size:14px;
      line-height:1.5;
    }
    
    .info-list .item strong { 
      color:#555;
      font-weight:500;
      display:inline-block;
      min-width:150px;
    }
    
    table { 
      width:100%; 
      border-collapse:collapse; 
      margin-top:15px; 
      font-size:13px;
      box-shadow:0 1px 3px rgba(0,0,0,0.1);
    }
    
    th, td { 
      border:1px solid #e0e0e0; 
      padding:8px 12px; 
      text-align:left; 
      vertical-align:top; 
    }
    
    th { 
      background:#3498db;
      color:white;
      font-weight:500;
      text-transform:uppercase;
      font-size:12px;
      letter-spacing:0.5px;
    }
    
    tr:nth-child(even) {
      background-color:#f8f9fa;
    }
    
    tr:hover {
      background-color:#f1f8fe;
    }
    
    .urgent { 
      color:#e74c3c; 
      font-weight:bold;
    }
    
    .footer { 
      position:absolute; 
      bottom:20mm; 
      right:25mm; 
      font-size:11px; 
      color:#95a5a6;
      border-top:1px solid #eee;
      padding-top:5px;
      width:calc(100% - 50mm);
      text-align:center;
    }
    
    #exportPDF { 
      text-align:center; 
      margin:25px 0; 
    }
    
    #exportPDF button { 
      padding:12px 25px; 
      background:#3498db; 
      color:#fff;
      border:none; 
      font-size:15px; 
      cursor:pointer; 
      border-radius:4px;
      transition:all 0.3s ease;
      box-shadow:0 2px 5px rgba(0,0,0,0.1);
      font-weight:500;
    }
    
    #exportPDF button:hover { 
      background:#2980b9; 
      transform:translateY(-2px);
      box-shadow:0 4px 8px rgba(0,0,0,0.15);
    }
    
    hr { 
      border:none; 
      border-top:1px dashed #ddd; 
      margin:25px 0;
    }
    
    .empty { 
      font-style:italic; 
      color:#95a5a6; 
      margin-top:15px;
      text-align:center;
      padding:10px;
      background:#f8f9fa;
      border-radius:4px;
    }
    
    /* Améliorations pour le PDF */
    @media print {
      body { background:none; }
      .page { box-shadow:none; margin:0; padding:20mm; }
      #exportPDF { display:none; }
    }
    
    /* Badges pour les statuts */
    .badge {
      display:inline-block;
      padding:3px 8px;
      border-radius:3px;
      font-size:12px;
      font-weight:500;
    }
    
    .badge-primary {
      background:#e1f0fa;
      color:#3498db;
    }
    
    .badge-danger {
      background:#fde8e8;
      color:#e74c3c;
    }
    
    /* Mise en forme des liens */
    a {
      color:#3498db;
      text-decoration:none;
      transition:color 0.2s;
    }
    
    a:hover {
      color:#2980b9;
      text-decoration:underline;
    }
  </style>
</head>
<body>

  <div class="page" id="dossier_patient">
    <div class="header">
      <img src="../pages/images/login.png" alt="Logo Clinique +">
      <div class="clinic">
        <div>CLINIQUE + OUAGADOUGOU</div>
        <div>Quartier Tanghin – Tél : +226 07 17 36 39</div>
        <div>Email : contact@clinicplus.bf – Site : www.clinicplus.bf</div>
      </div>
    </div>

    <h1>DOSSIER MÉDICAL DU PATIENT</h1>

    <!-- 1. Informations personnelles -->
    <div class="section">
      <h2>INFORMATIONS PERSONNELLES</h2>
      <div class="info-list">
        <div class="item"><strong>Dossier n° :</strong> <span class="badge badge-primary"><?= htmlspecialchars($patient['numero_dossier']) ?></span></div>
        <div class="item"><strong>Nom & Prénom :</strong> <strong style="color:#2c3e50;"><?= htmlspecialchars($patient['nom'].' '.$patient['prenom']) ?></strong></div>
        <div class="item"><strong>Sexe :</strong> <?= htmlspecialchars($patient['sexe']) ?></div>
        <div class="item"><strong>Naissance :</strong> <?= htmlspecialchars($patient['date_naiss']) ?></div>
        <div class="item"><strong>Lieu :</strong> <?= htmlspecialchars($patient['lieu_naissance']) ?></div>
        <div class="item"><strong>Nationalité :</strong> <?= htmlspecialchars($patient['nationalite']) ?></div>
        <div class="item"><strong>État civil :</strong> <?= htmlspecialchars($patient['etat_civil']) ?></div>
        <div class="item"><strong>Profession :</strong> <?= htmlspecialchars($patient['profession']) ?></div>
        <div class="item"><strong>Adresse :</strong> <?= htmlspecialchars($patient['adresse']) ?></div>
        <div class="item"><strong>Ville/Quartier :</strong> <?= htmlspecialchars($patient['ville'].' / '.$patient['quartier']) ?></div>
        <div class="item"><strong>Téléphone :</strong> <?= htmlspecialchars($patient['telephone']) ?></div>
        <div class="item"><strong>Secondaire :</strong> <?= htmlspecialchars($patient['telephone_secondaire']) ?></div>
        <div class="item"><strong>Email :</strong> <?= htmlspecialchars($patient['email']) ?></div>
        <div class="item"><strong>Groupe sanguin :</strong> <?= htmlspecialchars($patient['groupe_sanguin']) ?></div>
        <div class="item"><strong>Handicap :</strong> <?= $patient['situation_handicap'] ? '<span class="badge badge-danger">Oui</span>' : 'Non' ?></div>
        <div class="item"><strong>Allergies :</strong> <?= nl2br(htmlspecialchars($patient['allergie'])) ?: '–' ?></div>
        <div class="item"><strong>Antécédents :</strong> <?= nl2br(htmlspecialchars($patient['antecedents_medicaux'])) ?: '–' ?></div>
        <div class="item"><strong>Enregistré le :</strong> <?= htmlspecialchars($patient['date_enreg']) ?></div>
      </div>
    </div>

    <!-- 2. Consultations -->
    <div class="section">
      <h2>CONSULTATIONS</h2>
      <?php if ($consultations): ?>
      <table>
        <thead>
          <tr>
            <th>Date/Heure</th>
            <th>Médecin</th>
            <th>Motif</th>
            <th>Diagnostic</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($consultations as $c): ?>
          <tr>
            <td><?= $c['date_consult'] ?></td>
            <td><?= htmlspecialchars($c['medecin']) ?></td>
            <td><?= nl2br(htmlspecialchars($c['motif'])) ?></td>
            <td>
              <strong>Symptômes:</strong> <?= nl2br(htmlspecialchars($c['symptomes'])) ?><br><br>
              <strong>Diagnostic:</strong> <?= nl2br(htmlspecialchars($c['diagnostic'])) ?><br><br>
              <strong>Observations:</strong> <?= nl2br(htmlspecialchars($c['observations'])) ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
      <p class="empty">Aucune consultation enregistrée pour ce patient.</p>
      <?php endif; ?>
    </div>

    <!-- 3. Examens de laboratoire et résultats -->
    <div class="section">
      <h2>EXAMENS DE LABORATOIRE</h2>
      <?php if ($examens): ?>
      <table>
        <thead>
          <tr>
            <th>Date demande</th>
            <th>Type</th>
            <th>Statut</th>
            <th>Résultats</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($examens as $e): ?>
          <tr>
            <td><?= $e['date_demande'] ?></td>
            <td><?= htmlspecialchars($e['type_examen']) ?></td>
            <td>
              <?= $e['est_urgent'] ? '<span class="badge badge-danger">URGENT</span>' : '<span class="badge badge-primary">Standard</span>' ?>
            </td>
            <td>
              <strong>Motif:</strong> <?= nl2br(htmlspecialchars($e['motif'])) ?><br><br>
              
              <?php if (!empty($resultats_by_ex[$e['id_examen']])): ?>
                <?php foreach ($resultats_by_ex[$e['id_examen']] as $r): ?>
                  <div style="margin-bottom:15px;">
                    <strong style="color:#3498db;"><?= $r['date_res'] ?> :</strong><br>
                    <?= nl2br(htmlspecialchars($r['contenu_texte'])) ?>
                    <?php if ($r['fichier']): ?>
                      <div style="margin-top:5px;">
                        <a href="../uploads/resultats/<?= htmlspecialchars($r['fichier']) ?>" target="_blank" class="badge badge-primary">
                          📄 Télécharger le rapport complet
                        </a>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <span class="empty">En attente de résultats</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
      <p class="empty">Aucun examen de laboratoire demandé pour ce patient.</p>
      <?php endif; ?>
    </div>

    <!-- 4. Ordonnances -->
    <div class="section">
      <h2>ORDONNANCES</h2>
      <?php if ($ordonnances): ?>
        <?php foreach ($ordonnances as $o): ?>
        <div style="background:#f8f9fa; padding:15px; border-radius:5px; margin-bottom:20px;">
          <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
            <div><strong>Date :</strong> <?= $o['date_ord'] ?></div>
            <div><strong>Type :</strong> <span class="badge badge-primary"><?= htmlspecialchars($o['type_ordonnance']) ?></span></div>
          </div>
          
          <?php if (!empty($o['instructions'])): ?>
            <div style="margin-bottom:15px; padding:10px; background:#fff; border-radius:3px;">
              <strong>Instructions :</strong><br>
              <?= nl2br(htmlspecialchars($o['instructions'])) ?>
            </div>
          <?php endif; ?>
          
          <?php if (!empty($med_by_ord[$o['id_ordonnance']])): ?>
          <table>
            <thead>
              <tr>
                <th width="35%">Médicament</th>
                <th width="35%">Posologie</th>
                <th width="30%">Durée</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($med_by_ord[$o['id_ordonnance']] as $m): ?>
              <tr>
                <td><?= htmlspecialchars($m['medicament']) ?></td>
                <td><?= htmlspecialchars($m['posologie']) ?></td>
                <td><?= htmlspecialchars($m['duree']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
      <p class="empty">Aucune ordonnance disponible pour ce patient.</p>
      <?php endif; ?>
    </div>

    <!-- 5. Documents justificatifs -->
    <div class="section">
      <h2>DOCUMENTS JUSTIFICATIFS</h2>
      <?php if ($docs): ?>
      <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(250px, 1fr)); gap:15px;">
        <?php foreach ($docs as $doc): ?>
        <div style="border:1px solid #e0e0e0; padding:15px; border-radius:5px; background:#f8f9fa;">
          <div style="font-weight:500; margin-bottom:10px;"><?= htmlspecialchars($doc['titre']) ?></div>
          <a href="<?= htmlspecialchars($doc['url']) ?>" target="_blank" style="display:inline-block; padding:8px 12px; background:#3498db; color:white; border-radius:4px; font-size:13px;">
            📂 Télécharger le document
          </a>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <p class="empty">Aucun document justificatif disponible.</p>
      <?php endif; ?>
    </div>

    <div class="footer">
      <em>Document généré le <?= date('d/m/Y à H:i') ?> – Ce dossier médical est strictement confidentiel.</em>
    </div>
  </div>

  <div id="exportPDF">
    <button onclick="downloadPDF()">
      <span style="font-size:18px; margin-right:8px;">🖨️</span> 
      Télécharger le dossier PDF
    </button>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
  <script>
    function downloadPDF() {
      const element = document.getElementById('dossier_patient');
      const opt = {
        margin:       10,
        filename:     'Dossier_Medical_<?= htmlspecialchars($patient['numero_dossier']) ?>_<?= htmlspecialchars($patient['nom']) ?>.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { 
          scale: 2,
          letterRendering: true,
          useCORS: true
        },
        jsPDF:        { 
          unit: 'mm', 
          format: 'a4', 
          orientation: 'portrait',
          hotfixes: ["px_scaling"] 
        },
        pagebreak:    { mode: ['avoid-all', 'css', 'legacy'] }
      };
      
      // Afficher un message pendant la génération
      const btn = document.querySelector('#exportPDF button');
      const originalText = btn.innerHTML;
      btn.innerHTML = '⏳ Génération du PDF en cours...';
      btn.disabled = true;
      
      html2pdf().set(opt).from(element).toPdf().get('pdf').then(function(pdf) {
        // Ajouter un pied de page numéroté
        const totalPages = pdf.internal.getNumberOfPages();
        for (let i = 1; i <= totalPages; i++) {
          pdf.setPage(i);
          pdf.setFontSize(10);
          pdf.setTextColor(150);
          pdf.text('Page ' + i + ' sur ' + totalPages, pdf.internal.pageSize.getWidth() - 30, pdf.internal.pageSize.getHeight() - 10);
        }
      }).save().finally(function() {
        btn.innerHTML = originalText;
        btn.disabled = false;
      });
    }
  </script>
</body>
</html>