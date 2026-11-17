<?php
$titre = 'Médecins';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
demander_role('admin');
$message = '';
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $pdo->prepare('UPDATE medecins SET actif = 1 - actif WHERE id = ?')->execute([$id]);
    $message = 'Statut du médecin mis à jour.';
}
$q = trim($_GET['q'] ?? '');
$sql = "SELECT m.*, u.nom, u.prenom, u.email, u.telephone, s.libelle AS specialite
    FROM medecins m
    JOIN utilisateurs u ON u.id = m.id_utilisateur
    JOIN specialites s ON s.id = m.id_specialite";
$params = [];
if ($q !== '') {
    $sql .= " WHERE u.nom LIKE ? OR u.prenom LIKE ? OR s.libelle LIKE ?";
    $like = "%$q%";
    $params = [$like, $like, $like];
}
$sql .= " ORDER BY u.nom, u.prenom";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$medecins = $stmt->fetchAll();
?>
<div class="card">
    <h1>Gestion des médecins</h1>
    <?php if ($message): ?><div class="alert alert-success"><?php echo e($message); ?></div><?php endif; ?>
    <p><a class="btn" href="ajouter.php">Ajouter un médecin</a></p>
    <form method="get" class="form-grid" style="margin-bottom:12px;">
        <div><input type="text" name="q" placeholder="Rechercher par nom ou spécialité" value="<?php echo e($q); ?>"></div>
        <div><button type="submit">Rechercher</button></div>
    </form>
    <table><tr><th>Nom</th><th>Spécialité</th><th>Contact</th><th>Actif</th><th>Actions</th></tr>
    <?php foreach ($medecins as $m): ?><tr>
        <td><?php echo e($m['prenom'].' '.$m['nom']); ?></td>
        <td><?php echo e($m['specialite']); ?></td>
        <td><?php echo e($m['email']); ?><br><?php echo e($m['telephone']); ?></td>
        <td><?php echo $m['actif'] ? 'Oui' : 'Non'; ?></td>
        <td class="actions">
            <a class="btn btn-secondary" href="modifier.php?id=<?php echo $m['id']; ?>">Modifier</a>
            <a class="btn btn-secondary" href="disponibilites.php?id=<?php echo $m['id']; ?>">Disponibilités</a>
            <a class="btn <?php echo $m['actif'] ? 'btn-danger' : 'btn-success'; ?>" href="liste.php?toggle=<?php echo $m['id']; ?>"><?php echo $m['actif'] ? 'Désactiver' : 'Activer'; ?></a>
        </td>
    </tr><?php endforeach; ?></table>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
