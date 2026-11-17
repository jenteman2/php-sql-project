<?php
require_once __DIR__ . '/includes/fonctions.php';
session_unset();
session_destroy();
header('Location: /cabinet_medical/login.php');
exit;
?>
