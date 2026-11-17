<?php
$titre = 'Mes rendez-vous';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
demander_role('patient');
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['annuler'])) {
    $id = (int)$_POST['id'];
    $stmt = $pdo->prepare("UPDATE rendez_vous SET statut='Annulée' WHERE id=? AND id_patient=? AND statut IN ('En attente','Confirmée')");
    $stmt->execute([$id, $_SESSION['user']['id']]);
    if ($stmt->rowCount()) $message = 'Rendez-vous annulé. Le créneau est de nouveau disponible.';
}
$stmt = $pdo->prepare("SELECT r.*, u.nom AS mnom, u.prenom AS mprenom, s.libelle AS specialite
    FROM rendez_vous r
    JOIN medecins m ON m.id=r.id_medecin
    JOIN utilisateurs u ON u.id=m.id_utilisateur
    JOIN specialites s ON s.id=m.id_specialite
    WHERE r.id_patient=?
    ORDER BY r.date_rdv DESC, r.heure_rdv DESC");
$stmt->execute([$_SESSION['user']['id']]);
$rdvs = $stmt->fetchAll();
?>
<div class="card">
    <h1>Mes rendez-vous</h1>
    <?php if ($message): ?><div class="alert alert-success"><?php echo e($message); ?></div><?php endif; ?>
    <?php if (!$rdvs): ?><p>Vous n'avez pas encore de rendez-vous.</p><?php else: ?>
    <table><tr><th>Date</th><th>Heure</th><th>Médecin</th><th>Spécialité</th><th>Motif</th><th>Statut</th><th>Action</th></tr>
    <?php foreach ($rdvs as $r): ?><tr>
        <td><?php echo e($r['date_rdv']); ?></td>
        <td><?php echo e(substr($r['heure_rdv'],0,5)); ?></td>
        <td><?php echo e($r['mprenom'].' '.$r['mnom']); ?></td>
        <td><?php echo e($r['specialite']); ?></td>
        <td><?php echo e($r['motif']); ?></td>
        <td><?php echo e($r['statut']); ?></td>
        <td>
            <?php if (in_array($r['statut'], ['En attente','Confirmée'], true)): ?>
            <form method="post" class="inline-form" onsubmit="return confirm('Annuler ce rendez-vous ?');">
                <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                <button class="btn-danger" type="submit" name="annuler" value="1">Annuler</button>
            </form>
            <?php else: ?>-<?php endif; ?>
        </td>
    </tr><?php endforeach; ?></table>
    <?php endif; ?>
    <p><a class="btn" href="prendre_rdv.php">Demander un nouveau rendez-vous</a></p>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
