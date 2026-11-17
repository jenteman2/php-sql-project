<?php
$titre = 'Gestion des rendez-vous';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
demander_role('admin');
$message = ''; $erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'];
    $stmt = $pdo->prepare('SELECT * FROM rendez_vous WHERE id=?');
    $stmt->execute([$id]);
    $rdv = $stmt->fetch();
    if ($rdv) {
        if ($action === 'statut') {
            $nouveau = $_POST['statut'] ?? '';
            if (in_array($nouveau, statuts_rdv(), true)) {
                if (in_array($nouveau, ['Confirmée','En attente'], true) && !creneau_disponible($pdo, $rdv['id_medecin'], $rdv['date_rdv'], $rdv['heure_rdv'], $id)) {
                    $erreur = "Créneau indisponible pour confirmer ce rendez-vous.";
                } else {
                    $pdo->prepare('UPDATE rendez_vous SET statut=? WHERE id=?')->execute([$nouveau, $id]);
                    $message = 'Statut mis à jour.';
                }
            }
        } elseif ($action === 'modifier') {
            $date = $_POST['date_rdv'] ?? '';
            $heure = $_POST['heure_rdv'] ?? '';
            if (!$date || !$heure) {
                $erreur = 'Date et heure obligatoires.';
            } elseif (!creneau_disponible($pdo, $rdv['id_medecin'], $date, $heure, $id)) {
                $erreur = "Créneau indisponible.";
            } else {
                $pdo->prepare('UPDATE rendez_vous SET date_rdv=?, heure_rdv=? WHERE id=?')->execute([$date,$heure,$id]);
                $message = 'Rendez-vous modifié.';
            }
        }
    }
}

$medecins = $pdo->query("SELECT m.id, u.nom, u.prenom FROM medecins m JOIN utilisateurs u ON u.id=m.id_utilisateur ORDER BY u.nom")->fetchAll();
$patients = $pdo->query("SELECT id, nom, prenom FROM utilisateurs WHERE role='patient' ORDER BY nom")->fetchAll();
$f_medecin = $_GET['medecin'] ?? '';
$f_patient = $_GET['patient'] ?? '';
$f_date = $_GET['date'] ?? '';
$f_statut = $_GET['statut'] ?? '';

$sql = "SELECT r.*, up.nom AS pnom, up.prenom AS pprenom, um.nom AS mnom, um.prenom AS mprenom
    FROM rendez_vous r
    JOIN utilisateurs up ON up.id=r.id_patient
    JOIN medecins m ON m.id=r.id_medecin
    JOIN utilisateurs um ON um.id=m.id_utilisateur
    WHERE 1=1";
$params = [];
if ($f_medecin !== '') { $sql .= ' AND r.id_medecin=?'; $params[] = $f_medecin; }
if ($f_patient !== '') { $sql .= ' AND r.id_patient=?'; $params[] = $f_patient; }
if ($f_date !== '') { $sql .= ' AND r.date_rdv=?'; $params[] = $f_date; }
if ($f_statut !== '') { $sql .= ' AND r.statut=?'; $params[] = $f_statut; }
$sql .= ' ORDER BY r.date_rdv DESC, r.heure_rdv DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rdvs = $stmt->fetchAll();
?>
<div class="card">
    <h1>Traitement des rendez-vous</h1>
    <?php if ($message): ?><div class="alert alert-success"><?php echo e($message); ?></div><?php endif; ?>
    <?php if ($erreur): ?><div class="alert alert-error"><?php echo e($erreur); ?></div><?php endif; ?>
    <form method="get" class="form-grid" style="margin-bottom:12px;">
        <div><label>Médecin</label><select name="medecin"><option value="">Tous</option><?php foreach($medecins as $m): ?><option value="<?php echo $m['id']; ?>" <?php if($f_medecin==$m['id']) echo 'selected'; ?>><?php echo e($m['prenom'].' '.$m['nom']); ?></option><?php endforeach; ?></select></div>
        <div><label>Patient</label><select name="patient"><option value="">Tous</option><?php foreach($patients as $p): ?><option value="<?php echo $p['id']; ?>" <?php if($f_patient==$p['id']) echo 'selected'; ?>><?php echo e($p['prenom'].' '.$p['nom']); ?></option><?php endforeach; ?></select></div>
        <div><label>Date</label><input type="date" name="date" value="<?php echo e($f_date); ?>"></div>
        <div><label>Statut</label><select name="statut"><option value="">Tous</option><?php foreach(statuts_rdv() as $s): ?><option value="<?php echo e($s); ?>" <?php if($f_statut==$s) echo 'selected'; ?>><?php echo e($s); ?></option><?php endforeach; ?></select></div>
        <div><label>&nbsp;</label><button type="submit">Filtrer</button></div>
    </form>
    <table>
        <tr><th>Date/Heure</th><th>Patient</th><th>Médecin</th><th>Motif</th><th>Statut</th><th>Actions</th></tr>
        <?php foreach ($rdvs as $r): ?>
        <tr>
            <td><?php echo e($r['date_rdv'].' '.substr($r['heure_rdv'],0,5)); ?></td>
            <td><?php echo e($r['pprenom'].' '.$r['pnom']); ?></td>
            <td><?php echo e($r['mprenom'].' '.$r['mnom']); ?></td>
            <td><?php echo e($r['motif']); ?></td>
            <td><?php echo e($r['statut']); ?></td>
            <td>
                <form method="post" class="inline-form">
                    <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                    <input type="hidden" name="action" value="statut">
                    <select name="statut" onchange="this.form.submit()">
                        <?php foreach (statuts_rdv() as $s): ?><option value="<?php echo e($s); ?>" <?php if($r['statut']==$s) echo 'selected'; ?>><?php echo e($s); ?></option><?php endforeach; ?>
                    </select>
                </form>
                <form method="post" style="margin-top:6px;">
                    <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                    <input type="hidden" name="action" value="modifier">
                    <input type="date" name="date_rdv" value="<?php echo e($r['date_rdv']); ?>" required>
                    <input type="time" name="heure_rdv" value="<?php echo e(substr($r['heure_rdv'],0,5)); ?>" required>
                    <button type="submit">Modifier date/heure</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
