<?php
session_start();
unset($_SESSION['personID']);
header('location:login.php');
?>