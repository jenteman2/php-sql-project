<?php
$titre = 'Mon planning';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
demander_role('medecin');

$med = medecin_par_utilisateur($pdo, $_SESSION['user']['id']);
if (!$med) { echo '<div class="card"><p>Profil médecin introuvable.</p></div>'; require_once __DIR__ . '/../includes/footer.php'; exit; }
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['statut'], $_POST['id'])) {
    $id = (int)$_POST['id'];
    $statut = $_POST['statut'];
    $stmt = $pdo->prepare('SELECT * FROM rendez_vous WHERE id=? AND id_medecin=?');
    $stmt->execute([$id, $med['id']]);
    $rdv = $stmt->fetch();
    $autorisés = ['Confirmée','Terminée','Absent'];
    if ($rdv && in_array($statut, $autorisés, true)) {
        if ($statut === 'Confirmée' && !creneau_disponible($pdo, $med['id'], $rdv['date_rdv'], $rdv['heure_rdv'], $id)) {
            $message = 'Créneau indisponible.';
        } else {
            $pdo->prepare('UPDATE rendez_vous SET statut=? WHERE id=?')->execute([$statut,$id]);
            $message = 'Statut mis à jour.';
        }
    }
}
$date_debut = $_GET['date_debut'] ?? date('Y-m-d');
$date_fin = $_GET['date_fin'] ?? date('Y-m-d');
$stmt = $pdo->prepare("SELECT r.*, u.nom AS pnom, u.prenom AS pprenom, u.telephone
    FROM rendez_vous r JOIN utilisateurs u ON u.id=r.id_patient
    WHERE r.id_medecin=? AND r.date_rdv BETWEEN ? AND ?
    ORDER BY r.date_rdv, r.heure_rdv");
$stmt->execute([$med['id'], $date_debut, $date_fin]);
$rdvs = $stmt->fetchAll();
?>
<div class="card">
    <h1>Planning du Dr <?php echo e($med['prenom'].' '.$med['nom']); ?></h1>
    <?php if ($message): ?><div class="alert alert-success"><?php echo e($message); ?></div><?php endif; ?>
    <form method="get" class="form-grid">
        <div><label>Du</label><input type="date" name="date_debut" value="<?php echo e($date_debut); ?>"></div>
        <div><label>Au</label><input type="date" name="date_fin" value="<?php echo e($date_fin); ?>"></div>
        <div><label>&nbsp;</label><button type="submit">Afficher</button></div>
    </form>
</div>
<div class="card">
    <h2>Rendez-vous</h2>
    <?php if (!$rdvs): ?><p>Aucun rendez-vous sur cette période.</p><?php else: ?>
    <table><tr><th>Date</th><th>Heure</th><th>Patient</th><th>Téléphone</th><th>Motif</th><th>Statut</th><th>Action</th></tr>
    <?php foreach ($rdvs as $r): ?><tr>
        <td><?php echo e($r['date_rdv']); ?></td>
        <td><?php echo e(substr($r['heure_rdv'],0,5)); ?></td>
        <td><?php echo e($r['pprenom'].' '.$r['pnom']); ?></td>
        <td><?php echo e($r['telephone']); ?></td>
        <td><?php echo e($r['motif']); ?></td>
        <td><?php echo e($r['statut']); ?></td>
        <td>
            <form method="post" class="inline-form">
                <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                <select name="statut">
                    <?php foreach (['Confirmée','Terminée','Absent'] as $s): ?><option value="<?php echo $s; ?>" <?php if($r['statut']==$s) echo 'selected'; ?>><?php echo $s; ?></option><?php endforeach; ?>
                </select>
                <button type="submit">Mettre à jour</button>
            </form>
        </td>
    </tr><?php endforeach; ?></table>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
