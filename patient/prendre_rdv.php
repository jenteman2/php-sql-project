<?php
$titre = 'Prendre un rendez-vous';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
demander_role('patient');

$medecins = $pdo->query("SELECT m.id, m.duree_rdv, u.nom, u.prenom, s.libelle AS specialite
    FROM medecins m JOIN utilisateurs u ON u.id=m.id_utilisateur
    JOIN specialites s ON s.id=m.id_specialite
    WHERE m.actif=1 ORDER BY s.libelle, u.nom")->fetchAll();

$id_medecin = (int)($_REQUEST['id_medecin'] ?? 0);
$date_rdv = $_REQUEST['date_rdv'] ?? '';
$creneaux = [];
$erreurs = [];
$succes = '';

if ($id_medecin && $date_rdv) {
    $jour = (int)date('N', strtotime($date_rdv)); // 1-7
    $stmt = $pdo->prepare('SELECT * FROM disponibilites WHERE id_medecin=? AND jour_semaine=? ORDER BY heure_debut');
    $stmt->execute([$id_medecin, $jour]);
    $dispos = $stmt->fetchAll();
    $stmt = $pdo->prepare('SELECT duree_rdv FROM medecins WHERE id=?');
    $stmt->execute([$id_medecin]);
    $duree = (int)$stmt->fetchColumn() ?: 30;
    $stmt = $pdo->prepare("SELECT heure_rdv FROM rendez_vous WHERE id_medecin=? AND date_rdv=? AND statut IN ('En attente','Confirmée')");
    $stmt->execute([$id_medecin, $date_rdv]);
    $pris = array_map(function($r){ return substr($r['heure_rdv'],0,5); }, $stmt->fetchAll());
    foreach ($dispos as $d) {
        $debut = strtotime($d['heure_debut']);
        $fin = strtotime($d['heure_fin']);
        for ($t = $debut; $t + $duree*60 <= $fin; $t += $duree*60) {
            $h = date('H:i', $t);
            // ne pas proposer les créneaux passés si date du jour
            if ($date_rdv === date('Y-m-d') && $h <= date('H:i')) continue;
            if (!in_array($h, $pris, true)) $creneaux[] = $h;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmer'])) {
    $heure_rdv = $_POST['heure_rdv'] ?? '';
    $motif = trim($_POST['motif'] ?? '');
    if ($id_medecin <= 0 || !$date_rdv || !$heure_rdv || $motif === '') {
        $erreurs[] = 'Veuillez choisir un médecin, une date, un créneau et préciser le motif.';
    } elseif ($date_rdv < date('Y-m-d')) {
        $erreurs[] = 'La date doit être aujourd\'hui ou plus tard.';
    } elseif (!creneau_disponible($pdo, $id_medecin, $date_rdv, $heure_rdv)) {
        $erreurs[] = 'Ce créneau n\'est plus disponible.';
    } else {
        $stmt = $pdo->prepare('SELECT actif FROM medecins WHERE id=?');
        $stmt->execute([$id_medecin]);
        if (!$stmt->fetchColumn()) {
            $erreurs[] = 'Ce médecin n\'est pas disponible.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO rendez_vous (id_patient,id_medecin,date_rdv,heure_rdv,motif,statut,date_creation) VALUES (?,?,?,?,?,'En attente',NOW())");
            $stmt->execute([$_SESSION['user']['id'], $id_medecin, $date_rdv, $heure_rdv, $motif]);
            $succes = 'Votre demande de rendez-vous a bien été enregistrée (statut : En attente).';
            $creneaux = array_values(array_diff($creneaux, [$heure_rdv]));
        }
    }
}
?>
<div class="card">
    <h1>Demander un rendez-vous</h1>
    <?php foreach ($erreurs as $e): ?><div class="alert alert-error"><?php echo e($e); ?></div><?php endforeach; ?>
    <?php if ($succes): ?><div class="alert alert-success"><?php echo e($succes); ?></div><?php endif; ?>
    <form method="get" class="form-grid">
        <div>
            <label>Médecin</label>
            <select name="id_medecin" required>
                <option value="">-- Choisir --</option>
                <?php foreach ($medecins as $m): ?>
                    <option value="<?php echo $m['id']; ?>" <?php if($id_medecin==$m['id']) echo 'selected'; ?>><?php echo e($m['prenom'].' '.$m['nom'].' - '.$m['specialite']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div><label>Date</label><input type="date" name="date_rdv" value="<?php echo e($date_rdv); ?>" min="<?php echo date('Y-m-d'); ?>" required></div>
        <div><label>&nbsp;</label><button type="submit">Voir les créneaux</button></div>
    </form>
</div>

<?php if ($id_medecin && $date_rdv): ?>
<div class="card">
    <h2>Créneaux disponibles</h2>
    <?php if (!$creneaux): ?>
        <p>Aucun créneau disponible pour cette date. Essayez une autre date.</p>
    <?php else: ?>
        <form method="post">
            <input type="hidden" name="id_medecin" value="<?php echo $id_medecin; ?>">
            <input type="hidden" name="date_rdv" value="<?php echo e($date_rdv); ?>">
            <p><label>Créneau</label>
            <select name="heure_rdv" required>
                <option value="">-- Choisir --</option>
                <?php foreach ($creneaux as $c): ?><option value="<?php echo e($c); ?>"><?php echo e($c); ?></option><?php endforeach; ?>
            </select></p>
            <p><label>Motif du rendez-vous</label><textarea name="motif" required><?php echo e($_POST['motif'] ?? ''); ?></textarea></p>
            <button type="submit" name="confirmer" value="1">Envoyer la demande</button>
        </form>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
