<?php

$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'echo-db';

//connect to database
$conn = new mysqli($servername, $username, $password, $dbname);


?>

<!doctype html>
<html lang="en" data-theme="dark" >
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark" />
    <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.pink.min.css"
    >
    <title>Hello world!</title>
  </head>
  <body class="container">

    <header>
        <?php include "./pages/header.php"?>
    </header>


    <main>
        <h1>hello world</h1>

    </main>

    <footer>...</footer>

  </body>
</html>