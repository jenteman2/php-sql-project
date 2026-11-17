<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function medecin_par_utilisateur($pdo, $id_utilisateur) {
    $stmt = $pdo->prepare('SELECT m.*, u.nom, u.prenom, u.email, u.telephone FROM medecins m JOIN utilisateurs u ON u.id = m.id_utilisateur WHERE m.id_utilisateur = ?');
    $stmt->execute([$id_utilisateur]);
    return $stmt->fetch();
}

function creneau_disponible($pdo, $id_medecin, $date_rdv, $heure_rdv, $id_rdv_exclu = null) {
    $sql = "SELECT COUNT(*) FROM rendez_vous WHERE id_medecin = ? AND date_rdv = ? AND heure_rdv = ? AND statut IN ('En attente','Confirmée')";
    $params = [$id_medecin, $date_rdv, $heure_rdv];
    if ($id_rdv_exclu) {
        $sql .= ' AND id <> ?';
        $params[] = $id_rdv_exclu;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn() == 0;
}

function statuts_rdv() {
    return ['En attente', 'Confirmée', 'Refusée', 'Annulée', 'Terminée', 'Absent'];
}

function jour_nom($n) {
    $jours = [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'];
    return $jours[(int)$n] ?? '';
}

function est_connecte() {
    return isset($_SESSION['user']);
}

function utilisateur() {
    return $_SESSION['user'] ?? null;
}

function a_role($role) {
    return est_connecte() && isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === $role;
}

function demander_connexion() {
    if (!est_connecte()) {
        header('Location: /cabinet_medical/login.php');
        exit;
    }
}

function demander_role($role) {
    demander_connexion();
    if (!a_role($role)) {
        header('Location: /cabinet_medical/index.php');
        exit;
    }
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}
?>
