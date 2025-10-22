<?php 
//CONNEXION A LA BASE DE DONNEES
    $db_username = 'root';
    $db_password = '';
    $db_name     = 'clinique_bd';
    $db_host     = 'localhost';
    $db = mysqli_connect($db_host, $db_username, $db_password,$db_name)
    or die('N\'arrive pas à se connecter à la BD !');
?>