<?php
$titre = 'Inscription';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

$erreurs = [];
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';

    if ($nom === '' || $prenom === '' || $email === '' || $telephone === '' || $mot_de_passe === '') {
        $erreurs[] = 'Tous les champs sont obligatoires.';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "L'adresse e-mail n'est pas valide.";
    }
    if (strlen($mot_de_passe) < 6) {
        $erreurs[] = 'Le mot de passe doit contenir au moins 6 caractères.';
    }
    if ($mot_de_passe !== $confirmation) {
        $erreurs[] = 'Les mots de passe ne correspondent pas.';
    }

    if (!$erreurs) {
        $stmt = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $erreurs[] = 'Cette adresse e-mail est déjà utilisée.';
        } else {
            $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO utilisateurs (nom, prenom, email, telephone, mot_de_passe, role) VALUES (?, ?, ?, ?, ?, "patient")');
            $stmt->execute([$nom, $prenom, $email, $telephone, $hash]);
            $succes = 'Compte créé avec succès. Vous pouvez maintenant vous connecter.';
        }
    }
}
?>
<div class="card">
    <h1>Créer un compte patient</h1>
    <?php foreach ($erreurs as $e): ?><div class="alert alert-error"><?php echo e($e); ?></div><?php endforeach; ?>
    <?php if ($succes): ?><div class="alert alert-success"><?php echo e($succes); ?></div><?php endif; ?>
    <form method="post" class="form-grid">
        <div><label>Nom</label><input type="text" name="nom" value="<?php echo e($_POST['nom'] ?? ''); ?>" required></div>
        <div><label>Prénom</label><input type="text" name="prenom" value="<?php echo e($_POST['prenom'] ?? ''); ?>" required></div>
        <div><label>E-mail</label><input type="email" name="email" value="<?php echo e($_POST['email'] ?? ''); ?>" required></div>
        <div><label>Téléphone</label><input type="text" name="telephone" value="<?php echo e($_POST['telephone'] ?? ''); ?>" required></div>
        <div><label>Mot de passe</label><input type="password" name="mot_de_passe" required></div>
        <div><label>Confirmation</label><input type="password" name="confirmation" required></div>
        <div><button type="submit">S'inscrire</button></div>
    </form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>