<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "smart_expense_tracker";


$conn = mysqli_connect(
    $host,
    $username,
    $password,
    $database
);


if(!$conn)
{
    die("Database Connection Failed: " . mysqli_connect_error());
}


?>