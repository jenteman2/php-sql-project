<?php
$titre = 'Accueil - Cabinet Médical';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

$specialites = $pdo->query("SELECT s.id, s.libelle, s.description, COUNT(m.id) AS nb_medecins
    FROM specialites s LEFT JOIN medecins m ON m.id_specialite = s.id AND m.actif = 1
    GROUP BY s.id ORDER BY s.libelle")->fetchAll();
$medecins = $pdo->query("SELECT u.nom, u.prenom, s.libelle AS specialite
    FROM medecins m
    JOIN utilisateurs u ON u.id = m.id_utilisateur
    JOIN specialites s ON s.id = m.id_specialite
    WHERE m.actif = 1
    ORDER BY s.libelle, u.nom, u.prenom")->fetchAll();
?>
<div class="card">
    <h1>Bienvenue au cabinet médical</h1>
    <p>Cette application permet aux patients de consulter les médecins et de demander un rendez-vous en ligne.
       Le personnel du cabinet gère les médecins, les spécialités, les disponibilités et les demandes.</p>
    <?php if (!est_connecte()): ?>
        <p><a class="btn" href="/cabinet_medical/inscription.php">Créer un compte patient</a>
           <a class="btn btn-secondary" href="/cabinet_medical/login.php">Se connecter</a></p>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Nos spécialités</h2>
    <?php if (!$specialites): ?>
        <p>Aucune spécialité enregistrée pour le moment.</p>
    <?php else: ?>
        <table>
            <tr><th>Spécialité</th><th>Description</th><th>Médecins disponibles</th></tr>
            <?php foreach ($specialites as $s): ?>
                <tr>
                    <td><?php echo e($s['libelle']); ?></td>
                    <td><?php echo e($s['description']); ?></td>
                    <td><?php echo e($s['nb_medecins']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Nos médecins</h2>
    <?php if (!$medecins): ?>
        <p>Aucun médecin disponible pour le moment.</p>
    <?php else: ?>
        <table>
            <tr><th>Médecin</th><th>Spécialité</th></tr>
            <?php foreach ($medecins as $m): ?>
                <tr>
                    <td><?php echo e($m['prenom'] . ' ' . $m['nom']); ?></td>
                    <td><?php echo e($m['specialite']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
