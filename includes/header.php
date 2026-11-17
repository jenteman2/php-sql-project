<?php
require_once __DIR__ . '/fonctions.php';
$user = utilisateur();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($titre ?? 'Cabinet Médical'); ?></title>
    <link rel="stylesheet" href="/cabinet_medical/assets/css/style.css">
</head>
<body>
<header class="topbar">
    <div class="container nav">
        <a class="brand" href="/cabinet_medical/index.php">Cabinet Médical</a>
        <nav>
            <a href="/cabinet_medical/index.php">Accueil</a>
            <?php if (!est_connecte()): ?>
                <a href="/cabinet_medical/inscription.php">Inscription</a>
                <a href="/cabinet_medical/login.php">Connexion</a>
            <?php elseif (a_role('admin')): ?>
                <a href="/cabinet_medical/admin/dashboard.php">Tableau de bord</a>
                <a href="/cabinet_medical/admin/medecins/liste.php">Médecins</a>
                <a href="/cabinet_medical/admin/specialites/liste.php">Spécialités</a>
                <a href="/cabinet_medical/admin/rendez_vous/liste.php">Rendez-vous</a>
            <?php elseif (a_role('medecin')): ?>
                <a href="/cabinet_medical/medecin/planning.php">Mon planning</a>
            <?php elseif (a_role('patient')): ?>
                <a href="/cabinet_medical/patient/profil.php">Mon espace</a>
                <a href="/cabinet_medical/patient/prendre_rdv.php">Prendre RDV</a>
                <a href="/cabinet_medical/patient/mes_rendez_vous.php">Mes rendez-vous</a>
            <?php endif; ?>
            <?php if (est_connecte()): ?>
                <span class="user">Bonjour, <?php echo e($user['prenom'] . ' ' . $user['nom']); ?></span>
                <a href="/cabinet_medical/logout.php">Déconnexion</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">
