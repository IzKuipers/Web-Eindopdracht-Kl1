<?php

require ("./util/session.php");
require ("./util/error.php");

session_start();
verifieer_ingelogd();


if (!isset($_POST["bericht"])) {
  header("location:/");
}


$gebruiker = gebruiker_uit_sessie();

var_dump($gebruiker);

$bericht = $_POST["bericht"];
$likes = 0;

$connectie = verbind_mysqli();

try {
  global $statement;

  $query = "INSERT INTO posts(body,likes,auteur) VALUES (?,?,?)";
  $statement = $connectie->prepare($query);
  $statement->bind_param("sii", $bericht, $likes, $gebruiker["id"]);
  $statement->execute();
} catch (Exception $e) {
  foutmelding(7, "/index.php", $e->getMessage());
} finally {
  sluit_mysqli($connectie, $statement);
}

header("location:/");