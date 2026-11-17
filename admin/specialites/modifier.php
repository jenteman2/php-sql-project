<?php
$titre = 'Modifier spécialité';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
demander_role('admin');
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM specialites WHERE id = ?');
$stmt->execute([$id]);
$s = $stmt->fetch();
if (!$s) { echo '<div class="card"><p>Spécialité introuvable.</p></div>'; require_once __DIR__ . '/../../includes/footer.php'; exit; }
$erreurs = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $libelle = trim($_POST['libelle'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if ($libelle === '') $erreurs[] = 'Le libellé est obligatoire.';
    if (!$erreurs) {
        $stmt = $pdo->prepare('UPDATE specialites SET libelle=?, description=? WHERE id=?');
        $stmt->execute([$libelle, $description, $id]);
        redirect('/cabinet_medical/admin/specialites/liste.php');
    }
} else {
    $_POST = $s;
}
?>
<div class="card">
    <h1>Modifier une spécialité</h1>
    <?php foreach ($erreurs as $e): ?><div class="alert alert-error"><?php echo e($e); ?></div><?php endforeach; ?>
    <form method="post">
        <p><label>Libellé</label><input type="text" name="libelle" value="<?php echo e($_POST['libelle']); ?>" required></p>
        <p><label>Description</label><textarea name="description"><?php echo e($_POST['description']); ?></textarea></p>
        <button type="submit">Enregistrer</button> <a class="btn btn-secondary" href="liste.php">Retour</a>
    </form>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
