<?php
$titre = 'Spécialités';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
demander_role('admin');

$message = '';
$erreur = '';
if (isset($_GET['supprimer'])) {
    $id = (int)$_GET['supprimer'];
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM medecins WHERE id_specialite = ?');
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        $erreur = "Impossible de supprimer : cette spécialité est utilisée par un médecin.";
    } else {
        $stmt = $pdo->prepare('DELETE FROM specialites WHERE id = ?');
        $stmt->execute([$id]);
        $message = 'Spécialité supprimée.';
    }
}
$specialites = $pdo->query('SELECT s.*, (SELECT COUNT(*) FROM medecins m WHERE m.id_specialite=s.id) AS nb_medecins FROM specialites s ORDER BY libelle')->fetchAll();
?>
<div class="card">
    <h1>Gestion des spécialités</h1>
    <?php if ($message): ?><div class="alert alert-success"><?php echo e($message); ?></div><?php endif; ?>
    <?php if ($erreur): ?><div class="alert alert-error"><?php echo e($erreur); ?></div><?php endif; ?>
    <p><a class="btn" href="ajouter.php">Ajouter une spécialité</a></p>
    <table><tr><th>Libellé</th><th>Description</th><th>Médecins</th><th>Actions</th></tr>
    <?php foreach ($specialites as $s): ?><tr>
        <td><?php echo e($s['libelle']); ?></td>
        <td><?php echo e($s['description']); ?></td>
        <td><?php echo e($s['nb_medecins']); ?></td>
        <td class="actions">
            <a class="btn btn-secondary" href="modifier.php?id=<?php echo $s['id']; ?>">Modifier</a>
            <a class="btn btn-danger" href="liste.php?supprimer=<?php echo $s['id']; ?>" onclick="return confirm('Supprimer cette spécialité ?');">Supprimer</a>
        </td>
    </tr><?php endforeach; ?></table>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
