<?php
$titre = 'Connexion';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

$erreurs = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    if ($email === '' || $mot_de_passe === '') {
        $erreurs[] = 'Veuillez saisir votre e-mail et votre mot de passe.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'nom' => $user['nom'],
                'prenom' => $user['prenom'],
                'email' => $user['email'],
                'role' => $user['role']
            ];
            if ($user['role'] === 'admin') {
                redirect('/cabinet_medical/admin/dashboard.php');
            } elseif ($user['role'] === 'medecin') {
                redirect('/cabinet_medical/medecin/planning.php');
            } else {
                redirect('/cabinet_medical/patient/profil.php');
            }
        } else {
            $erreurs[] = 'E-mail ou mot de passe incorrect.';
        }
    }
}
?>
<div class="card">
    <h1>Connexion</h1>
    <?php foreach ($erreurs as $e): ?><div class="alert alert-error"><?php echo e($e); ?></div><?php endforeach; ?>
    <form method="post" class="form-grid">
        <div><label>E-mail</label><input type="email" name="email" value="<?php echo e($_POST['email'] ?? ''); ?>" required></div>
        <div><label>Mot de passe</label><input type="password" name="mot_de_passe" required></div>
        <div><button type="submit">Se connecter</button></div>
    </form>
    <p class="small">Comptes de démonstration : admin@cabinet.test / password, medecin1@cabinet.test / password, patient1@cabinet.test / password</p>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>