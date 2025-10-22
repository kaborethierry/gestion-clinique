<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// pages/dossier_patient.php
// Affiche le dossier médical complet d’un patient + export PDF

session_start();
if (empty($_SESSION['id']) || $_SESSION['type_compte'] !== "Super Administrateur") {
    header('Location: ../index.php?erreur=3');
    exit();
}

if (!isset($_GET['id_patient']) || !is_numeric($_GET['id_patient'])) {
    header('Location: patient.php');
    exit();
}

$id_patient = (int) $_GET['id_patient'];
require __DIR__ . '/connect_db_pdo.php';

// 1) Informations personnelles
$stmtP = $bdd->prepare("
    SELECT
      p.*,
      DATE_FORMAT(p.date_naissance,     '%d/%m/%Y')         AS date_naiss,
      DATE_FORMAT(p.date_enregistrement, '%d/%m/%Y %H:%i') AS date_enreg
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

// 3) Examens de laboratoire + résultats
$stmtE = $bdd->prepare("
    SELECT
      e.id_examen,
      DATE_FORMAT(e.date_demande, '%d/%m/%Y %H:%i') AS date_demande,
      e.type_examen,
      e.motif,
      e.est_urgent
    FROM examen_labo e
    WHERE e.id_patient = ? AND e.supprimer = 'NON'
    ORDER BY e.date_demande DESC
");
$stmtE->execute([$id_patient]);
$examens = $stmtE->fetchAll(PDO::FETCH_ASSOC);

// Pour chaque examen, charger ses résultats
$resultats_by_ex = [];
$rStmt = $bdd->prepare("
    SELECT contenu_texte, fichier,
           DATE_FORMAT(date_resultat, '%d/%m/%Y %H:%i') AS date_res
    FROM resultat_examen
    WHERE id_examen = ? AND supprimer = 'NON'
    ORDER BY date_resultat DESC
");
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

// Médicaments par ordonnance
$med_by_ord = [];
$mStmt = $bdd->prepare("
    SELECT medicament, posologie, duree
    FROM ordonnance_medicament
    WHERE id_ordonnance = ? AND supprimer = 'NON'
");
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
    body { margin:0; padding:0; font-family:Arial,sans-serif; background:#f5f6fa; }
    .page { width:210mm; min-height:297mm; margin:20px auto; background:#fff;
            padding:20mm; box-shadow:0 0 5px rgba(0,0,0,.1); position:relative; }
    .header { display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; }
    .header img { height:60px; }
    .header .clinic { text-align:right; font-size:13px; color:#555; }
    h1 { text-align:center; margin:10px 0 25px; color:#007bff; font-size:24px; }
    .section { margin-bottom:25px; }
    .section h2 { font-size:18px; color:#333; border-bottom:2px solid #007bff; padding-bottom:5px; }
    .info-list { margin-top:10px; }
    .info-list .item { margin:4px 0; font-size:14px; }
    .info-list .item strong { width:180px; display:inline-block; }
    table { width:100%; border-collapse:collapse; margin-top:10px; font-size:13px; }
    th, td { border:1px solid #ccc; padding:6px 8px; text-align:left; vertical-align:top; }
    th { background:#e9f2ff; }
    .urgent { color:#d9534f; font-weight:bold; }
    .footer { position:absolute; bottom:20mm; right:20mm; font-size:11px; color:#888; }
    #exportPDF { text-align:center; margin:20px 0; }
    #exportPDF button { padding:8px 18px; background:#007bff; color:#fff;
                        border:none; font-size:14px; cursor:pointer; border-radius:4px; }
    #exportPDF button:hover { background:#0056b3; }
    hr { border:none; border-top:1px solid #ddd; margin:20px 0; }
  </style>
</head>
<body>

  <div class="page" id="dossier_patient">
    <div class="header">
      <img src="../pages/images/login.png" alt="Logo Clinique +">
      <div class="clinic">
        <div>Clinique + Ouagadougou</div>
        <div>Quartier Tanghin – Tél : +226 07 17 36 39</div>
        <div>www.clinic+.bf</div>
      </div>
    </div>

    <h1>Dossier Médical du Patient</h1>

    <!-- 1. Informations personnelles -->
    <div class="section">
      <h2>Informations Personnelles</h2>
      <div class="info-list">
        <div class="item"><strong>Dossier n° :</strong> <?= htmlspecialchars($patient['numero_dossier']) ?></div>
        <div class="item"><strong>Nom & Prénom :</strong> <?= htmlspecialchars($patient['nom'].' '.$patient['prenom']) ?></div>
        <div class="item"><strong>Sexe :</strong> <?= htmlspecialchars($patient['sexe']) ?></div>
        <div class="item"><strong>Naissance :</strong> <?= htmlspecialchars($patient['date_naiss']) ?></div>
        <div class="item"><strong>Lieu de naissance :</strong> <?= htmlspecialchars($patient['lieu_naissance']) ?></div>
        <div class="item"><strong>Nationalité :</strong> <?= htmlspecialchars($patient['nationalite']) ?></div>
        <div class="item"><strong>État civil :</strong> <?= htmlspecialchars($patient['etat_civil']) ?></div>
        <div class="item"><strong>Profession :</strong> <?= htmlspecialchars($patient['profession']) ?></div>
        <div class="item"><strong>Adresse :</strong> <?= htmlspecialchars($patient['adresse']) ?></div>
        <div class="item"><strong>Ville / Quartier :</strong> <?= htmlspecialchars($patient['ville'].' / '.$patient['quartier']) ?></div>
        <div class="item"><strong>Téléphone :</strong> <?= htmlspecialchars($patient['telephone']) ?></div>
        <div class="item"><strong>Secondaire :</strong> <?= htmlspecialchars($patient['telephone_secondaire']) ?></div>
        <div class="item"><strong>Email :</strong> <?= htmlspecialchars($patient['email']) ?></div>
        <div class="item"><strong>Groupe sanguin :</strong> <?= htmlspecialchars($patient['groupe_sanguin']) ?></div>
        <div class="item"><strong>Handicap :</strong> <?= $patient['situation_handicap'] ? 'Oui' : 'Non' ?></div>
        <div class="item"><strong>Allergies :</strong> <?= nl2br(htmlspecialchars($patient['allergie'])) ?: '–' ?></div>
        <div class="item"><strong>Antécédents :</strong> <?= nl2br(htmlspecialchars($patient['antecedents_medicaux'])) ?: '–' ?></div>
        <div class="item"><strong>Enregistré le :</strong> <?= htmlspecialchars($patient['date_enreg']) ?></div>
      </div>
    </div>

    <!-- 2. Consultations -->
    <?php if ($consultations): ?>
    <div class="section">
      <h2>Consultations</h2>
      <table>
        <thead>
          <tr><th>Date / Heure</th><th>Médecin</th><th>Motif</th><th>Symptômes</th><th>Diagnostic</th><th>Observations</th></tr>
        </thead>
        <tbody>
          <?php foreach ($consultations as $c): ?>
          <tr>
            <td><?= $c['date_consult'] ?></td>
            <td><?= htmlspecialchars($c['medecin']) ?></td>
            <td><?= nl2br(htmlspecialchars($c['motif'])) ?></td>
            <td><?= nl2br(htmlspecialchars($c['symptomes'])) ?></td>
            <td><?= nl2br(htmlspecialchars($c['diagnostic'])) ?></td>
            <td><?= nl2br(htmlspecialchars($c['observations'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

    <!-- 3. Examens et Résultats -->
    <?php if ($examens): ?>
    <div class="section">
      <h2>Examens de laboratoire et résultats</h2>
      <table>
        <thead>
          <tr><th>Date demande</th><th>Type</th><th>Urgent</th><th>Motif</th><th>Résultats</th></tr>
        </thead>
        <tbody>
          <?php foreach ($examens as $e): ?>
          <tr>
            <td><?= $e['date_demande'] ?></td>
            <td><?= htmlspecialchars($e['type_examen']) ?></td>
            <td><?= $e['est_urgent'] ? '<span class="urgent">Oui</span>' : 'Non' ?></td>
            <td><?= nl2br(htmlspecialchars($e['motif'])) ?></td>
            <td>
              <?php if (!empty($resultats_by_ex[$e['id_examen']])): ?>
                <?php foreach ($resultats_by_ex[$e['id_examen']] as $r): ?>
                  <div><strong><?= $r['date_res'] ?> :</strong>
                    <?= nl2br(htmlspecialchars($r['contenu_texte'])) ?>
                    <?php if ($r['fichier']): ?>
                      <br><a href="../uploads/resultats/<?= htmlspecialchars($r['fichier']) ?>" target="_blank">Télécharger</a>
                    <?php endif; ?>
                  </div>
                  <hr>
                <?php endforeach; ?>
              <?php else: ?>
                —
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

    <!-- 4. Ordonnances -->
    <?php if ($ordonnances): ?>
    <div class="section">
      <h2>Ordonnances</h2>
      <?php foreach ($ordonnances as $o): ?>
        <div class="info-list">
          <div class="item"><strong>Date :</strong> <?= $o['date_ord'] ?> — <strong>Type :</strong> <?= htmlspecialchars($o['type_ordonnance']) ?></div>
          <div class="item"><strong>Instructions :</strong> <?= nl2br(htmlspecialchars($o['instructions'])) ?></div>
        </div>
        <?php if (!empty($med_by_ord[$o['id_ordonnance']])): ?>
        <table>
          <thead><tr><th>Médicament</th><th>Posologie</th><th>Durée</th></tr></thead>
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
        <hr>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- 5. Documents justificatifs -->
    <?php if ($docs): ?>
    <div class="section">
      <h2>Documents justificatifs</h2>
      <div class="info-list">
        <?php foreach ($docs as $doc): ?>
        <div class="item">
          <strong><?= htmlspecialchars($doc['titre']) ?> :</strong>
          <a href="<?= htmlspecialchars($doc['url']) ?>" target="_blank">Télécharger</a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="footer">
      <em>Document généré automatiquement – usage interne uniquement.</em>
    </div>
  </div>

  <div id="exportPDF">
    <button onclick="downloadPDF()">🖨️ Télécharger le dossier PDF</button>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
  <script>
    function downloadPDF() {
      const element = document.getElementById('dossier_patient');
      html2pdf()
        .set({
          margin:       0.5,
          filename:     'dossier_patient_<?= htmlspecialchars($patient['numero_dossier']) ?>.pdf',
          image:        { type: 'jpeg', quality: 0.98 },
          html2canvas:  { scale: 2 },
          jsPDF:        { unit: 'cm', format: 'a4', orientation: 'portrait' }
        })
        .from(element)
        .save();
    }
  </script>
</body>
</html>
