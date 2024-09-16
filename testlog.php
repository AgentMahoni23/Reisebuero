<?php

   include ("util.inc.php");
   
   $conn = new_db_connect ("reisebuero", "root", "");
   
   session_start();
   
   // Formulardaten einlesen
   // Buchungsanforderung
   $buchnr = cgi_param ("b", 0);
   $buchtxt = cgi_param ("bt", "");
   // E-Mail-Adresse
   $mail = $_POST[mail];

   // E-Mail-Adresse aus der Datenbank heraussuchen
   $querytext = "SELECT kk_nr FROM rb_kundenkontakte WHERE kk_mail=\"$mail\"";
   $query = $conn->query ($querytext);
   
   // Existiert die Adresse?
   if ($query->num_rows > 0) {
      // Nummer auslesen und in der Session speichern
      list ($user_nr) = $query->fetch_row ();
      $_SESSION["user"] = $user_nr;
      
      // Anmeldebestätigung ausgeben

?>
<html>
  <head>
    <title>Reiseb&uuml;ro: Anmeldung</title>
	<link rel="stylesheet" type="text/css" href="main.css" />
  </head>
  <body>
  <table border="0" width="750" align="center">
    <tr>
    <td>
    
    &nbsp;<br /><div align="center"><img src="logo.gif" width="480" height="80" alt="EuroCityTravel" /></div><br />
    <h1>Reiseb&uuml;ro</h1>
    <!-- Einfache Navigationsleiste -->
    [
    <a href="index.php">Home</a>
    |
    <a href="auskunft.php">Reisesuche</a>
    |
    <a href="info.php">Touristeninfo</a>
    |
    <a href="login.php">Anmeldung</a>
    |
    <a href="gast.php">G&auml;stebuch</a>
    |
    <a href="forum.php">Forum</a>
    ]
    <!-- Ende der Navigationsleiste> -->
    <h2>Anmeldung</h2>
    Sie sind jetzt angemeldet.
<?php

      // Falls Buchung, zurück zur Buchungsbestätigung
      if ($buchnr) {
   
?>
    <form action="buchabschl.php" method="post">
      <input type="hidden" name="buchnr" value="<?php echo ($buchnr); ?>" />
      <input type="hidden" name="buchtxt" value="<?php echo ($buchtxt); ?>" />
      <input type="submit" name="bestaet" value="Buchung abschlie&szlig;en" />
    </form>
    
<?php

      }
   
?>
  </body>
</html>
<?php

   } else {
      // Zurück zur Anmeldeseite mit Fehlermeldung
      $fehlermode = 1;
      $fehler = "Die angegebene E-Mail-Adresse existiert nicht.";
      header ("Location: login.php?f=$fehler&fm=$fehlermode&b=$buchnr&bt=$buchtxt");
   }
   
?>