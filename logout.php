<?php
require __DIR__ . '/vendor/autoload.php';
$db = new PDO('mysql:host=localhost;dbname=echo-db;charset=utf8', 'root', '');
$auth = new \Delight\Auth\Auth($db);

$auth->logout();
header('Location: login.php');
?>