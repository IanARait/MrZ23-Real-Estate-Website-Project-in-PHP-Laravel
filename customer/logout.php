<?php
session_start();
session_destroy();
$base = 'http://' . $_SERVER['HTTP_HOST'] . '/E_COMMERCE/real-estate';
header('Location: ' . $base . '/customer/index.php');
exit;
