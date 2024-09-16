<?php

   // Include-Datei laden
   include ("util.inc.php");
   
   // Datenbankverbindung herstellen
   //$connID = old_db_connect ("forum", "rbuser", "R3153n");
   $conn = new_db_connect ("reisebuero", "root", "");
   
   // Aktuellen Modus ermitteln: 
   // Liste (l), Lesen (r), Eintragen (e) oder speichern (s)?
   // Standard ist Liste
   $mode = cgi_param ("mode", "l");


   // Die Funktion zeige_thread() zeigt alle auf den 
   // angegebenen Beitrag folgenden Einträge rekursiv an
   function zeige_thread ($pid, $stufe) {
	       
      // Daten aller Postings mit der Parent-ID $id auslesen
      // $query = mysql_query ("SELECT f_id, f_titel, f_nick FROM fo_eintraege WHERE f_pid=$pid ORDER BY f_id ASC");
	  $querytext = "SELECT f_id, f_titel, f_nick FROM fo_eintraege WHERE f_pid=$pid ORDER BY f_id ASC";
	  $query = $conn->query ($querytext);
     // while (list ($id, $titel, $nick) = mysql_fetch_row ($query)) {
	  while (list ($id, $titel, $nick) = $query->fetch_row () ){
         // Einrückung
         for ($i = 0; $i < $stufe; $i++) {
            echo ("&nbsp;&nbsp;");
         }
         
         // Nickname leer? -> "Anonymous"
         if ($nick == "") {
            $nick = "Anonymous";
         }

         // Titel leer? -> "[Ohne Titel]";
         if ($titel == "") {
            $titel = "[Ohne Titel]";
         }
         
         echo ("<a href=\"forum.php?mode=r&id=$id\">$titel</a> von $nick<br />");
         
         // Rekursiver Aufruf für die Nachfolger von $id
         zeige_thread ($id, $stufe + 1);
      }
   }
   
?>

<html>
<head>
<title>Reiseb&uuml;ro - Diskussionsforum</title>
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
    Forum
    ]
    <!-- Ende der Navigationsleiste> -->
<?php

   if ($mode == "l") {
      // Listenmodus: Eintragsliste anzeigen
      echo ("<h2>Diskussionsforum</h2>");
      
      // Threads auf- oder zugeklappt?
      $offen = cgi_param ("o", 1);
      
      // Links zum Schreiben-, Auf- und Zuklappen
      echo ("<a href=\"forum.php?mode=e&p=0\">Neuer Beitrag</a> | ");
      if ($offen) {
         echo ("Aufklappen | <a href=\"forum.php?mode=l&o=0\">Zuklappen</a>");
      } else {
         echo ("<a href=\"forum.php?mode=l&o=1\">Aufklappen</a> | Zuklappen");
      }
      echo ("<br /><br />");
      
      // Thread-Startbeiträge (mit Parent-ID 0) auswählen
      $querytext = "SELECT f_id, f_titel, f_nick FROM fo_eintraege WHERE f_pid=0 ORDER BY f_id DESC";
      
      // Abfrage senden
      // $query = mysql_query ($querytext);
	  $query = $conn->query ($querytext);
      
      // Sind Beiträge vorhanden?
      // $anzahl = mysql_num_rows ($query);
	  $anzahl = $query->num_rows;
	 
      if ($anzahl == 0) {
         // Noch kein Beitrag da
         echo ("Bisher keine Beitr&auml;ge.");
      } else {
         // Liste anzeigen
         
         //while (list ($id, $titel, $nick) = mysql_fetch_row ($query)) {
		 while (list ($id, $titel, $nick) = $query->fetch_row () ){
            // Nickname leer? -> "Anonymous"
            if ($nick == "") {
               $nick = "Anonymous";
            }
            
            // Titel leer? -> "[Ohne Titel]";
            if ($titel == "") {
               $titel = "[Ohne Titel]";
            }
            
            // Beitrag anzeigen
            echo ("<a href=\"forum.php?mode=r&id=$id\">$titel</a> von $nick");
            
            // Thread anzeigen, falls aufgeklappt
            if ($offen) {
               echo ("<br /><br />");
               zeige_thread ($id, 1);
            }
            
            // Trennlinie
            echo ("<hr />");
         }
      }
         
   } elseif ($mode == "r") {
      // Lesemodus: Eintrag anzeigen
      
      echo ("<h2>Diskussionsforum</h2>");
      
      // Links für Liste Neueintrag
      echo ("<a href=\"forum.php?mode=e&p=0\">Neuer Beitrag</a> | <a href=\"forum.php?mode=l&o=1\">Zur &Uuml;bersicht</a><br /><br />");
      
      // ID lesen - Standard: -1 für nicht vorhanden
      $id = cgi_param ("id", -1);
      // Abfrage für den Beitrag
      $querytext = "SELECT DATE_FORMAT(f_datum, '%d.%m.%Y, %H:%i'),
                    f_pid, f_nick, f_mail, f_titel, f_inhalt
                    FROM fo_eintraege
                    WHERE f_id=$id";
                    
      //$query = mysql_query ($querytext);
	  $query= $conn->query ($querytext);
      
      // Beitrag vorhanden?
      // $anzahl = mysql_num_rows ($query);
	  $anzahl = $query->num_rows;
	  
      if ($anzahl == 0) {
         // Nicht vorhanden
         echo ("Dieser Beitrag ist leider nicht vorhanden.<br />");
      } else {
         // Den Beitrag anzeigen
         // list ($datum, $pid, $nick, $mail, $titel, $text) = mysql_fetch_row ($query);
		 list ($datum, $pid, $nick, $mail, $titel, $text) =  $query->fetch_row ();
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
            
         // Text ausgeben
         echo ("$text");
         
         // Trennlinie
         echo ("<hr />");
         
         // Link zum Antworten
         echo ("<a href=\"forum.php?mode=e&p=$id\">Beitrag beantworten</a>");
         
         // Folgepostings ausgeben, falls vorhanden
         echo ("<h3>Bisherige Antworten</h3>");
         zeige_thread ($id, 0);
      }
   } elseif ($mode == "e") {
      // Schreibmodus: Beitragsformular anzeigen
      
      // Parent-ID lesen - Standard 0 (neuer Beitrag)
      $pid = cgi_param ("p", 0);

?>
<h2>Forumsbeitrag verfassen</h2>
<a href="forum.php?mode=r">Eintr&auml;ge lesen</a>
<form action="forum.php" method="post">
<input type="hidden" name="mode" value="s" />
<input type="hidden" name="p" value="<?php echo ($pid); ?>" />
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
      // Speichermodus: Neuen Beitrag speichern
      
      // Formularfelder auslesen
      $pid = cgi_param ("p", "");
      $nick = cgi_param ("nick", "");
      $mail = cgi_param ("mail", "");
      $titel = cgi_param ("titel", "");
      $eintr = cgi_param ("eintr", "[kein Text]");
      
      // SQL-String für die Einfügeabfrage
      $querytext = "INSERT INTO fo_eintraege 
                    (f_pid, f_datum, f_nick, f_mail, f_titel, f_inhalt)
                    VALUES (\"$pid\", NOW(), \"$nick\", \"$mail\", \"$titel\", \"$eintr\")";

      // Abfrage senden
      // mysql_query ($querytext);
	  $query= $conn->query ($querytext);
      
      // Hat es geklappt?
      //if (mysql_affected_rows() == 1) {
	  if ($conn->affected_rows == 1) {
         echo ("<br /><br />Ihr Beitrag wurde erfolgreich hinzugef&uuml;gt.<br /><br />");
      } else {
         echo ("<br /><br />Aufgrund eines Fehlers konnte Ihr Beitrag leider nicht hinzugef&uuml;gt werden.<br /><br />");
      }
      
      echo ("<a href=\"forum.php?mode=l&o=1\">Zur &Uuml;bersicht</a>");
   } else {

?>
<h2>Fehler</h2>
Ung&uuml;ltiger Zugriff auf das Forum.<br />
<a href="forum.php?mode=l&o=1">Zur&uuml;ck</a>
<?php

   }
   
?>
    <br />
    <br />
    <br />
    <br />
    <br />
    &nbsp;
</td>
</tr>
</table>
</body>
</html>