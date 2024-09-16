<?php

   include ("util.inc.php");
   
   // Datenbankverbindung herstellen
   $conn = new_db_connect ("reisebuero", "root", "");
   
   session_start();
   
   // User bereits eingeloggt? Aus Session-Daten lesen
   $user_nr = session_param ("user", 0);
   
   // Formulardaten auslesen
   $buchnr = cgi_param ("buchnr", 0);
   $buchtxt = cgi_param ("buchtxt", "");
   $bestaet = cgi_param ("bestaet", "");
   $abbruch = cgi_param ("abbruch", "");

   if (!is_numeric ($buchnr) || $buchnr <= 0) {
      // Buchung 0 oder ungültig
      header ("Location: auskunft.php?f=Ungültige Buchung");
   } else {
      // Existiert die Buchung?
      $querystr = "SELECT * FROM rb_buchungen WHERE bu_nr=$buchnr";
      $query = $conn->query ($querystr);
      if ($query->num_rows == 0) {
         header ("Location: auskunft.php?f=Buchung existiert nicht");
      } else {
         // Bestätigung oder Storno eintragen
         $querystr = "UPDATE rb_buchungen SET bu_status=";
         if ($bestaet) {
            $querystr .= "\"aktiv\"";
         } else {
            $querystr .= "\"storniert\"";
         }
         $querystr .= " WHERE bu_nr=$buchnr";
         $query = $conn->query ($querystr);
   
?>

<html>
  <head>
    <title>Reiseb&uuml;ro: Auswahl</title>
    <link rel="stylesheet" type="text/css" href="main.css" />
  </head>
  <body>
    <table border="0" width="750" align="center">
    <tr>
    <td>
    &nbsp;<br /><div align="center"><img src="logo.gif" width="480" height="80" alt="EuroCityTravel" /></div><br />
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
<br /><br />
Ihre Reisebuchung<br />
<?php echo ("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; $buchtxt<br />"); ?>
wurde soeben
<?php

         if ($bestaet) {
            echo ("best&auml;tigt.<br />");
         } else {
            echo ("storniert.<br />");
         }
         
         if ($user_nr) {
            // Daten des Users auslesen
            $querystr = "SELECT kd_vorname, kd_name FROM rb_kunden WHERE kd_nr=$user_nr";
            $query = $conn->query ($querystr);
            // Existiert dieser User?
            if ($query->num_rows > 0) {
               // Daten holen
               list ($vorname, $name) = $query->fetch_row();
               // Kundennummer in die Buchung eintragen
               $querystr = "UPDATE rb_buchungen SET bu_kunde=$user_nr WHERE bu_nr=$buchnr";
               $query = $conn->query ($querystr);
               // Hat es geklappt?
               if ($conn->affected_rows == 1) {
                  echo ("Die Buchung wurde f&uuml;r <b>$vorname $name</b> eingetragen.<br />");
               } else {
                  echo ("Leider konnte die Buchung nicht f&uuml;r <b>$vorname $name</b> eingetragen werden.<br />");
               }
            } else {
               // User ist nicht vorhanden - Neuanmeldung mit Buchungsvermerk
               echo ("Ihre Benutzerdaten sind ung&uuml;ltig. Bitte melden Sie sich <a href=\"login.php?forcelogin=1&b=$buchnr\">neu an</a>.");
            }
      
         } else {
            // Login mit Buchungsvermerk (bei Storno nicht erforderlich)
            if ($bestaet) {
               echo ("Bitte <a href=\"login.php?b=$buchnr&bt=$buchtxt\">melden Sie sich an</a>, um die Buchung endg&uuml;tig einzutragen.");
            }
         }
   
?>
	<br />
    <br />
    &nbsp;
    </td>
    </tr>
    </table>
</body>
</html>
<?php

      // Schließende else-Klammern
      }
   }
   
?>
