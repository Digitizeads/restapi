<?php
// Local Database connection
$host = 'localhost';
$dbname = 'verifyads';
$username = 'root';
$password = '';

//Production Database connection

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database $dbname :" . $e->getMessage());
}
?>