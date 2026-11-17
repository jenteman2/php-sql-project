<?php
$titre = 'Modifier médecin';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
demander_role('admin');
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT m.*, u.nom, u.prenom, u.email, u.telephone FROM medecins m JOIN utilisateurs u ON u.id=m.id_utilisateur WHERE m.id=?");
$stmt->execute([$id]);
$m = $stmt->fetch();
if (!$m) { echo '<div class="card"><p>Médecin introuvable.</p></div>'; require_once __DIR__ . '/../../includes/footer.php'; exit; }
$specialites = $pdo->query('SELECT * FROM specialites ORDER BY libelle')->fetchAll();
$erreurs = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $id_specialite = (int)($_POST['id_specialite'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $duree_rdv = (int)($_POST['duree_rdv'] ?? 30);
    if ($nom===''||$prenom===''||$email===''||$telephone===''||$id_specialite<=0) $erreurs[] = 'Tous les champs obligatoires doivent être remplis.';
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = 'E-mail invalide.';
    if (!$erreurs) {
        $stmt = $pdo->prepare('SELECT id FROM utilisateurs WHERE email=? AND id <> ?');
        $stmt->execute([$email, $m['id_utilisateur']]);
        if ($stmt->fetch()) $erreurs[] = 'Cet e-mail est déjà utilisé.';
    }
    if (!$erreurs) {
        $pdo->beginTransaction();
        try {
            $pdo->prepare('UPDATE utilisateurs SET nom=?, prenom=?, email=?, telephone=? WHERE id=?')->execute([$nom,$prenom,$email,$telephone,$m['id_utilisateur']]);
            $pdo->prepare('UPDATE medecins SET id_specialite=?, description=?, duree_rdv=? WHERE id=?')->execute([$id_specialite,$description,$duree_rdv,$id]);
            $pdo->commit();
            redirect('/cabinet_medical/admin/medecins/liste.php');
        } catch (Exception $e) {
            $pdo->rollBack();
            $erreurs[] = 'Erreur lors de la modification.';
        }
    }
} else {
    $_POST = $m;
}
?>
<div class="card">
    <h1>Modifier un médecin</h1>
    <?php foreach ($erreurs as $e): ?><div class="alert alert-error"><?php echo e($e); ?></div><?php endforeach; ?>
    <form method="post" class="form-grid">
        <div><label>Nom</label><input name="nom" value="<?php echo e($_POST['nom']); ?>" required></div>
        <div><label>Prénom</label><input name="prenom" value="<?php echo e($_POST['prenom']); ?>" required></div>
        <div><label>Spécialité</label><select name="id_specialite" required><?php foreach($specialites as $s): ?><option value="<?php echo $s['id']; ?>" <?php if($_POST['id_specialite']==$s['id']) echo 'selected'; ?>><?php echo e($s['libelle']); ?></option><?php endforeach; ?></select></div>
        <div><label>Téléphone</label><input name="telephone" value="<?php echo e($_POST['telephone']); ?>" required></div>
        <div><label>E-mail</label><input type="email" name="email" value="<?php echo e($_POST['email']); ?>" required></div>
        <div><label>Durée RDV (minutes)</label><input type="number" name="duree_rdv" value="<?php echo e($_POST['duree_rdv']); ?>" min="10" step="5"></div>
        <div style="grid-column:1/-1"><label>Description</label><textarea name="description"><?php echo e($_POST['description']); ?></textarea></div>
        <div><button type="submit">Enregistrer</button> <a class="btn btn-secondary" href="liste.php">Retour</a></div>
    </form>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
