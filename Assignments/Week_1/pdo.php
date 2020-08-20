<?php
$pdo = new PDO('mysql:host=localhost;port=3306;dbname=misc', 'admin', 'password');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);