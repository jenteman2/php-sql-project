<?php
$titre = 'Tableau de bord';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
demander_role('admin');

$nb_patients = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role='patient'")->fetchColumn();
$nb_medecins = $pdo->query("SELECT COUNT(*) FROM medecins WHERE actif=1")->fetchColumn();
$nb_attente = $pdo->query("SELECT COUNT(*) FROM rendez_vous WHERE statut='En attente'")->fetchColumn();
$nb_confirmes = $pdo->query("SELECT COUNT(*) FROM rendez_vous WHERE statut='Confirmée'")->fetchColumn();
$rdv_jour = $pdo->query("SELECT r.*, up.nom AS pnom, up.prenom AS pprenom, um.nom AS mnom, um.prenom AS mprenom
    FROM rendez_vous r
    JOIN utilisateurs up ON up.id = r.id_patient
    JOIN medecins m ON m.id = r.id_medecin
    JOIN utilisateurs um ON um.id = m.id_utilisateur
    WHERE r.date_rdv = CURDATE() ORDER BY r.heure_rdv")->fetchAll();
$dernieres = $pdo->query("SELECT r.*, up.nom AS pnom, up.prenom AS pprenom, um.nom AS mnom, um.prenom AS mprenom
    FROM rendez_vous r
    JOIN utilisateurs up ON up.id = r.id_patient
    JOIN medecins m ON m.id = r.id_medecin
    JOIN utilisateurs um ON um.id = m.id_utilisateur
    ORDER BY r.date_creation DESC LIMIT 5")->fetchAll();
?>
<div class="card">
    <h1>Tableau de bord administrateur</h1>
    <div class="stats">
        <div class="stat"><strong><?php echo e($nb_patients); ?></strong><br>Patients</div>
        <div class="stat"><strong><?php echo e($nb_medecins); ?></strong><br>Médecins actifs</div>
        <div class="stat"><strong><?php echo e($nb_attente); ?></strong><br>Rendez-vous en attente</div>
        <div class="stat"><strong><?php echo e($nb_confirmes); ?></strong><br>Rendez-vous confirmés</div>
    </div>
</div>
<div class="card">
    <h2>Rendez-vous du jour</h2>
    <?php if (!$rdv_jour): ?><p>Aucun rendez-vous aujourd'hui.</p><?php else: ?>
    <table><tr><th>Heure</th><th>Patient</th><th>Médecin</th><th>Motif</th><th>Statut</th></tr>
    <?php foreach ($rdv_jour as $r): ?><tr>
        <td><?php echo e(substr($r['heure_rdv'],0,5)); ?></td>
        <td><?php echo e($r['pprenom'].' '.$r['pnom']); ?></td>
        <td><?php echo e($r['mprenom'].' '.$r['mnom']); ?></td>
        <td><?php echo e($r['motif']); ?></td>
        <td><?php echo e($r['statut']); ?></td>
    </tr><?php endforeach; ?></table>
    <?php endif; ?>
</div>
<div class="card">
    <h2>Dernières demandes reçues</h2>
    <?php if (!$dernieres): ?><p>Aucune demande.</p><?php else: ?>
    <table><tr><th>Date demande</th><th>Patient</th><th>Médecin</th><th>RDV</th><th>Statut</th></tr>
    <?php foreach ($dernieres as $r): ?><tr>
        <td><?php echo e($r['date_creation']); ?></td>
        <td><?php echo e($r['pprenom'].' '.$r['pnom']); ?></td>
        <td><?php echo e($r['mprenom'].' '.$r['mnom']); ?></td>
        <td><?php echo e($r['date_rdv'].' '.substr($r['heure_rdv'],0,5)); ?></td>
        <td><?php echo e($r['statut']); ?></td>
    </tr><?php endforeach; ?></table>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
