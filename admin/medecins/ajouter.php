<?php
$titre = 'Ajouter médecin';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
demander_role('admin');
$specialites = $pdo->query('SELECT * FROM specialites ORDER BY libelle')->fetchAll();
$erreurs = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $id_specialite = (int)($_POST['id_specialite'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    if ($nom===''||$prenom===''||$email===''||$telephone===''||$id_specialite<=0||$mot_de_passe==='') $erreurs[] = 'Tous les champs obligatoires doivent être remplis.';
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = 'E-mail invalide.';
    if (!$erreurs) {
        $stmt = $pdo->prepare('SELECT id FROM utilisateurs WHERE email=?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) $erreurs[] = 'Cet e-mail est déjà utilisé.';
    }
    if (!$erreurs) {
        $pdo->beginTransaction();
        try {
            $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO utilisateurs (nom,prenom,email,telephone,mot_de_passe,role) VALUES (?,?,?,?,?, "medecin")');
            $stmt->execute([$nom,$prenom,$email,$telephone,$hash]);
            $id_utilisateur = $pdo->lastInsertId();
            $stmt = $pdo->prepare('INSERT INTO medecins (id_utilisateur,id_specialite,description,actif) VALUES (?,?,?,1)');
            $stmt->execute([$id_utilisateur,$id_specialite,$description]);
            $pdo->commit();
            redirect('/cabinet_medical/admin/medecins/liste.php');
        } catch (Exception $e) {
            $pdo->rollBack();
            $erreurs[] = 'Erreur lors de l\'ajout.';
        }
    }
}
?>
<div class="card">
    <h1>Ajouter un médecin</h1>
    <?php foreach ($erreurs as $e): ?><div class="alert alert-error"><?php echo e($e); ?></div><?php endforeach; ?>
    <form method="post" class="form-grid">
        <div><label>Nom</label><input name="nom" value="<?php echo e($_POST['nom'] ?? ''); ?>" required></div>
        <div><label>Prénom</label><input name="prenom" value="<?php echo e($_POST['prenom'] ?? ''); ?>" required></div>
        <div><label>Spécialité</label><select name="id_specialite" required><option value="">-- Choisir --</option><?php foreach($specialites as $s): ?><option value="<?php echo $s['id']; ?>" <?php if(($_POST['id_specialite'] ?? '')==$s['id']) echo 'selected'; ?>><?php echo e($s['libelle']); ?></option><?php endforeach; ?></select></div>
        <div><label>Téléphone</label><input name="telephone" value="<?php echo e($_POST['telephone'] ?? ''); ?>" required></div>
        <div><label>E-mail</label><input type="email" name="email" value="<?php echo e($_POST['email'] ?? ''); ?>" required></div>
        <div><label>Mot de passe (compte médecin)</label><input type="password" name="mot_de_passe" required></div>
        <div style="grid-column:1/-1"><label>Description</label><textarea name="description"><?php echo e($_POST['description'] ?? ''); ?></textarea></div>
        <div><button type="submit">Ajouter</button> <a class="btn btn-secondary" href="liste.php">Retour</a></div>
    </form>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
