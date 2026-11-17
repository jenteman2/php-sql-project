<?php
$titre = 'Ajouter spécialité';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
demander_role('admin');
$erreurs = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $libelle = trim($_POST['libelle'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if ($libelle === '') $erreurs[] = 'Le libellé est obligatoire.';
    if (!$erreurs) {
        $stmt = $pdo->prepare('INSERT INTO specialites (libelle, description) VALUES (?, ?)');
        $stmt->execute([$libelle, $description]);
        redirect('/cabinet_medical/admin/specialites/liste.php');
    }
}
?>
<div class="card">
    <h1>Ajouter une spécialité</h1>
    <?php foreach ($erreurs as $e): ?><div class="alert alert-error"><?php echo e($e); ?></div><?php endforeach; ?>
    <form method="post">
        <p><label>Libellé</label><input type="text" name="libelle" required></p>
        <p><label>Description</label><textarea name="description"></textarea></p>
        <button type="submit">Enregistrer</button> <a class="btn btn-secondary" href="liste.php">Retour</a>
    </form>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
