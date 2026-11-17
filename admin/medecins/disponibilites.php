<?php
$titre = 'Disponibilités';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
demander_role('admin');
$id_medecin = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT m.*, u.nom, u.prenom FROM medecins m JOIN utilisateurs u ON u.id=m.id_utilisateur WHERE m.id=?");
$stmt->execute([$id_medecin]);
$med = $stmt->fetch();
if (!$med) { echo '<div class="card"><p>Médecin introuvable.</p></div>'; require_once __DIR__ . '/../../includes/footer.php'; exit; }
$message = ''; $erreurs = [];
if (isset($_GET['supprimer'])) {
    $id = (int)$_GET['supprimer'];
    $pdo->prepare('DELETE FROM disponibilites WHERE id=? AND id_medecin=?')->execute([$id,$id_medecin]);
    $message = 'Disponibilité supprimée.';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jour = (int)($_POST['jour_semaine'] ?? 0);
    $debut = $_POST['heure_debut'] ?? '';
    $fin = $_POST['heure_fin'] ?? '';
    $duree = (int)($_POST['duree_rdv'] ?? 30);
    if ($jour < 1 || $jour > 7 || !$debut || !$fin) $erreurs[] = 'Veuillez remplir tous les champs.';
    if ($debut >= $fin) $erreurs[] = "L'heure de début doit être avant l'heure de fin.";
    if (!$erreurs) {
        $pdo->prepare('INSERT INTO disponibilites (id_medecin, jour_semaine, heure_debut, heure_fin) VALUES (?,?,?,?)')->execute([$id_medecin,$jour,$debut,$fin]);
        $pdo->prepare('UPDATE medecins SET duree_rdv=? WHERE id=?')->execute([$duree,$id_medecin]);
        $med['duree_rdv'] = $duree;
        $message = 'Disponibilité ajoutée.';
    }
}
$dispos = $pdo->prepare('SELECT * FROM disponibilites WHERE id_medecin=? ORDER BY jour_semaine, heure_debut');
$dispos->execute([$id_medecin]);
$dispos = $dispos->fetchAll();
?>
<div class="card">
    <h1>Disponibilités - <?php echo e($med['prenom'].' '.$med['nom']); ?></h1>
    <?php if ($message): ?><div class="alert alert-success"><?php echo e($message); ?></div><?php endif; ?>
    <?php foreach ($erreurs as $e): ?><div class="alert alert-error"><?php echo e($e); ?></div><?php endforeach; ?>
    <form method="post" class="form-grid">
        <div><label>Jour</label><select name="jour_semaine" required>
            <option value="">-- Choisir --</option>
            <?php for($i=1;$i<=7;$i++): ?><option value="<?php echo $i; ?>"><?php echo e(jour_nom($i)); ?></option><?php endfor; ?>
        </select></div>
        <div><label>Heure début</label><input type="time" name="heure_debut" required></div>
        <div><label>Heure fin</label><input type="time" name="heure_fin" required></div>
        <div><label>Durée RDV (minutes)</label><input type="number" name="duree_rdv" value="<?php echo e($med['duree_rdv']); ?>" min="10" step="5"></div>
        <div><label>&nbsp;</label><button type="submit">Ajouter</button></div>
    </form>
    <h3>Disponibilités enregistrées</h3>
    <table><tr><th>Jour</th><th>Début</th><th>Fin</th><th>Action</th></tr>
    <?php foreach ($dispos as $d): ?><tr>
        <td><?php echo e(jour_nom($d['jour_semaine'])); ?></td>
        <td><?php echo e(substr($d['heure_debut'],0,5)); ?></td>
        <td><?php echo e(substr($d['heure_fin'],0,5)); ?></td>
        <td><a class="btn btn-danger" href="disponibilites.php?id=<?php echo $id_medecin; ?>&supprimer=<?php echo $d['id']; ?>" onclick="return confirm('Supprimer ?');">Supprimer</a></td>
    </tr><?php endforeach; ?></table>
    <p><a class="btn btn-secondary" href="liste.php">Retour</a></p>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
