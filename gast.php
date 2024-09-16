<?php

   // Include-Datei laden
   include ("util.inc.php");
   
   // Datenbankverbindung herstellen
   // $conn = new_db_connect ("gaestebuch", "rbuser", "R3153n");
   $conn = new_db_connect ("reisebuero", "root", "");
   
   // Aktuellen Modus ermitteln: 
   // Lesen (r), Eintragen (e) oder speichern (s)?
   // Standard ist Lesen
   $mode = cgi_param ("mode", "r");
   
   
?>

<html>
<head>
<title>Reiseb&uuml;ro - G&auml;stebuch</title>
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
    G&auml;stebuch
    |
    <a href="forum.php">Forum</a>
    ]
    <!-- Ende der Navigationsleiste -->
<?php

   if ($mode == "r") {
      // Lesemodus: Einträge anzeigen
      
      echo ("<h2>G&auml;stebuch lesen</h2>");
      
      // Link für Neueintrag
      echo ("<a href=\"gast.php?mode=e\">Ins G&auml;stebuch eintragen</a><br /><br />");
      
      // Abfrage für alle Einträge - neuester zuerst
      $querytext = "SELECT DATE_FORMAT(e_datum, '%d.%m.%Y, %H:%i'),
                    e_nick, e_mail, e_titel, e_inhalt
                    FROM gb_eintraege
                    ORDER BY e_datum DESC";
      // $query = mysql_query ($querytext); deprecated
	  $query = $conn->query ($querytext);
      
      // Anzahl der Einträge ermitteln
      // $anzahl = mysql_num_rows ($query); deprecated
	  $anzahl =$query->num_rows;
      
      if ($anzahl == 0) {
         // Meldung, falls keine Einträge vorhanden sind
         echo ("Noch keine Eintr&auml;ge vorhanden.<br />");
      } else {
         // Alle Einträge anzeigen
         // while (list ($datum, $nick, $mail, $titel, $text) = mysql_fetch_row ($query)) {
			while (list ($datum, $nick, $mail, $titel, $text) = $query->fetch_row ()){
            // Nickname leer? -> "Anonymous"
            if ($nick == "") {
               $nick = "Anonymous";
            }
            // Titel leer? -> "[Ohne Titel]";
            
            if ($titel == "") {
               $titel = "[Ohne Titel]";
            }
            
            // Titel ausgeben
            echo ("<h3>$titel</h3>");
            
            // Nickname ausgeben
            if ($mail != "") {
               // E-Mail-Adresse vorhanden? -> Nickname als Link
               echo ("von <a href=\"mailto:$mail\">$nick</a>");
            } else {
               // nur Nickname
               echo ("von $nick");
            }
            
            // Datum ausgeben
            echo (", $datum<br /><br />");
            
            // Text und Trennlinie ausgeben
            echo ("$text<hr />");
         }
      }
   } elseif ($mode == "e") {
      // Schreibmodus: Eintragsformular anzeigen

?>
<h2>Neuer G&auml;stebucheintrag</h2>
<a href="gast.php?mode=r">Eintr&auml;ge lesen</a>
<form action="gast.php" method="post">
<input type="hidden" name="mode" value="s" />
<table border="0" cellpadding="4">
  <tr>
    <td>Nickname:</td>
    <td><input type="text" name="nick" size="50" /></td>
  </tr>
  <tr>
    <td>E-Mail (optional):</td>
    <td><input type="text" name="mail" size="50" /></td>
  </tr>
  <tr>
    <td>Titel:</td>
    <td><input type="text" name="titel" size="50" /></td>
  </tr>
  <tr>
    <td valign="top">Ihr Eintrag:</td>
    <td><textarea name="eintr" cols="40" rows="7" wrap="virtual"></textarea></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><input type="submit" value="Eintragen" />
  </tr>
</table>
<?php

   } elseif ($mode == "s") {
      // Speichermodus: Neuen Eintrag speichern
      
      // Formularfelder auslesen
      $nick = cgi_param ("nick", "");
      $mail = cgi_param ("mail", "");
      $titel = cgi_param ("titel", "");
      $eintr = cgi_param ("eintr", "[kein Text]");
      
      // SQL-String für die Einfügeabfrage
      $querytext = "INSERT INTO gb_eintraege 
                    (e_datum, e_nick, e_mail, e_titel, e_inhalt)
                    VALUES (NOW(), \"$nick\", \"$mail\", \"$titel\", \"$eintr\")";

      // Abfrage senden
      // mysql_query ($querytext);
	  $conn->query ($querytext);
	  
      
      // Hat es geklappt?
      // if (mysql_affected_rows() == 1) {  deprecated
	  if ($conn->affected_rows  == 1) {
         echo ("<br /><br />Ihr Eintrag wurde erfolgreich hinzugef&uuml;gt.<br /><br />");
      } else {
         echo ("Aufgrund eines Fehlers konnte Ihr Eintrag leider nicht hinzugef&uuml;gt werden.<br /><br />");
      }
      
      echo ("<a href=\"gast.php?mode=r\">Eintr&auml;ge lesen</a>");
   } else {

?>
<h2>Fehler</h2>
Ung&uuml;ltiger Zugriff auf das G&auml;stebuch.<br />
<a href="gast.php?mode=r">Zur&uuml;ck</a>
<?php

   }
   
?>
   <br />
    &nbsp;
    </td>
    </tr>
    </table>
</body>
</html>