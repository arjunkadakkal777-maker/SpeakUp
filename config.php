<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


$conn = new mysqli("localhost","root","","cgms");

if($conn->connect_error){
    die("database connection failed");
}
?>