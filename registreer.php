<?php
require_once ("./util/error.php");

weZijnMisschienOffline(); // Controleer of de database online is
geefFoutmeldingWeer(); // Geef een eventuele foutmelding weer

if (isset($_SESSION["gebruiker"])) {
  header("location:/");
}

// Deze functie wordt gebruikt om de gebruiker te registreren via de POST data
function registreerGebruiker()
{
  // Check via de request method en POST data of de gebruiker probeert te registeren
  if ($_SERVER['REQUEST_METHOD'] != "POST" || !isset($_POST["gebruikersnaam"]) || !isset($_POST["wachtwoord"]) || !isset($_POST["wachtwoordOpnieuw"])) {
    return; // De gebruiker probeert niet te registreren, stop.
  }

  $gebruikersnaam = $_POST["gebruikersnaam"]; // De gebruikersnaam die de gebruiker heeft ingevoegd
  $gebruikersnaamVeilig = htmlspecialchars(trim($gebruikersnaam)); // De gebruikersnaam beveiligd tegen XSS
  $wachtwoord = $_POST["wachtwoord"]; // Het wachtwoord die de gebruiker heeft ingevoerd
  $wachtwoordOpnieuw = $_POST["wachtwoordOpnieuw"]; // Het wachtwoord die de gebruiker een tweede keer heeft ingevoerd

  // Check of de twee wachtwoorden hetzelfde zijn
  if ($wachtwoord != $wachtwoordOpnieuw) {
    foutmelding(Foutmeldingen::WachtwoordenMismatch, "/registreer.php"); // Wachtwoorden niet hetzelfde, geef een foutmelding weer.

    return;
  }

  // Hash het wachtwoord met de builtin hashing-functie password_hash()
  $wachtwoordHash = password_hash($wachtwoord, PASSWORD_DEFAULT);

  // Maak verbinding met de database
  $connectie = verbindMysqli();

  try { // Probeer...
    // De vraag aan de database: Maak een rij aan in de gebruikers tabel met gebruikersnaam ? en wachtwoord-hash ?
    $query = "INSERT INTO gebruikers(naam,wachtwoord) values (?,?)";

    $statement = $connectie->prepare($query); // Bereid de vraag voor
    $statement->bind_param("ss", $gebruikersnaamVeilig, $wachtwoordHash); // Vervang de vraagtekens met hun respectieve waarden

    if (!($statement->execute()))
      throw new Exception(); // Voer de vraag uit

  } catch (Exception $e) { // Anders...
    error_log($e->getMessage());
    foutmelding(Foutmeldingen::GebruikerBestaatAl, "/registreer.php"); // Geef een foutmelding weer als het niet is gelukt om de gebruiker aan te maken

    return;
  } finally { // Ten slotte...
    sluitMysqli($connectie, $statement); // Probeer de connectie en statement te sluiten
  }

  $_SESSION["toast"] = 1;
  header("location: /login.php"); // Stuur de gebruiker naar de inlog pagina
}

registreerGebruiker(); // Probeer de gebruieker te registeren
?>

<!DOCTYPE html>
<html lang="nl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registreren - Twitter</title>
  <link rel="stylesheet" href="/css/loginpage.css">
  <link rel="shortcut icon" href="/images/logo.png" type="image/png">
  <link rel="manifest" href="/manifest.webmanifest">
</head>

<body>
  <!-- Main: een gecentreerde div met daarin de content van de pagina -->
  <main>
    <img src="/images/logo.png" alt="">
    <!-- De header van het registereer-formulier -->
    <h1>Registreren</h1>

    <!-- Het registreer-formulier, wordt terug gestuurd naar dezelfde pagina met POST data om te gebruiken voor het registreer-proces -->
    <form action="" method="POST">
      <!-- Het gebruikersnaam veld: Komt in de POST data als "gebruikersnaam" en is een verplicht veld. -->
      <input type="text" name="gebruikersnaam" placeholder="Gebruikersnaam" required>
      <!-- Het wachtwoord veld: Komt in de POST data als "wachtwoord" en is een verplicht veld. -->
      <input type="password" name="wachtwoord" placeholder="Wachtwoord" required>
      <!-- Het wachtwoord-opnieuw veld: Komt in de POST data als "wachtwoordOpnieuw" en is een verplicht veld. -->
      <input type="password" name="wachtwoordOpnieuw" placeholder="Wachtwoord nogmaals" required>

      <!-- De knop om door te gaan naar het registreer-proces -->
      <input type="submit" value="Registreren">
    </form>

    <!-- Een handy-dandy link naar de login-pagina -->
    <a href="/login.php">Heb je al een account?</a>
  </main>
</body>

</html>