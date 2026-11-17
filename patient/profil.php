<?php
$titre = 'Mon espace patient';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
demander_role('patient');
?>
<div class="card">
    <h1>Espace patient</h1>
    <p>Bienvenue <?php echo e($_SESSION['user']['prenom'].' '.$_SESSION['user']['nom']); ?>.</p>
    <p>Depuis cet espace, vous pouvez consulter les médecins, demander un rendez-vous et suivre vos demandes.</p>
    <p>
        <a class="btn" href="prendre_rdv.php">Demander un rendez-vous</a>
        <a class="btn btn-secondary" href="mes_rendez_vous.php">Voir mes rendez-vous</a>
    </p>
</div>
<div class="card">
    <h2>Médecins disponibles</h2>
    <?php
    $medecins = $pdo->query("SELECT u.nom,u.prenom,u.email,u.telephone,s.libelle AS specialite,m.description
        FROM medecins m JOIN utilisateurs u ON u.id=m.id_utilisateur
        JOIN specialites s ON s.id=m.id_specialite
        WHERE m.actif=1 ORDER BY s.libelle,u.nom")->fetchAll();
    ?>
    <table><tr><th>Médecin</th><th>Spécialité</th><th>Contact</th><th>Description</th></tr>
    <?php foreach ($medecins as $m): ?><tr>
        <td><?php echo e($m['prenom'].' '.$m['nom']); ?></td>
        <td><?php echo e($m['specialite']); ?></td>
        <td><?php echo e($m['email']); ?><br><?php echo e($m['telephone']); ?></td>
        <td><?php echo e($m['description']); ?></td>
    </tr><?php endforeach; ?></table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
