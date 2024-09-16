<?php

  include ("util.inc.php");
  
  $connID = old_db_connect ("reisebuero", "rbuser", "R3153n");
  
  // Formulardaten auslesen
  $von_airport = cgi_param ("von_airport", 0);
  $nach_airport = cgi_param ("nach_airport", 0);
  $wt = array();
  for ($i = 0; $i < 7; $i++) {
     $wt[$i] = cgi_param ("wt$i", 0);
  }
  $airline = cgi_param ("airline", 0);
  $fnummer = cgi_param ("fnummer", "");
  $zeit = cgi_param ("zeit", "");
  $dauer = cgi_param ("dauer", 0);
  $gpreis = cgi_param ("gpreis", 0);
  
  // Parameter prüfen
  $fehler = "";
  if (!$von_airport || !$nach_airport || !$airline || !$fnummer || !$zeit || !$dauer || !$gpreis) {
     // Parameter vergessen
     $fehler = "Fehlender Parameter!";
  } elseif (!$wt[0] && !$wt[1] && !$wt[2] && !$wt[3] && !$wt[4] && !$wt[5] && !$wt[6]) {
     // KEIN EINZIGER Wochentag gewählt!
     $fehler = "Kein Wochentag ausgew&auml;hlt!";
  } elseif ($von_airport == $nach_airport) {
     // Identischer Start- und Zielflughafen
     $fehler = "Start- und Zielflughafen identisch!";
  } else {
     // Prüfen, ob die Orig.-Flugnummer bereits existiert
     $querystr = "SELECT * FROM rb_flugstrecken WHERE fs_onr=\"$fnummer\"";
     $query = mysql_query ($querystr);
     if (mysql_num_rows() > 0) {
        $fehler = "Flugstrecke existiert bereits!";
     }
  }
  if ($fehler) {
     header ("Location: mk_fluege.php?f=$fehler");
  } else {
  
?>
<html>
<head>
<title>Flug eintragen</title>
</head>
<body>
<h1>Reiseb&uuml;ro</h1>
<h2>Flug eintragen</h2>
<?php

   // Zuerst Flugstrecke eintragen
   $querystr = "INSERT INTO rb_flugstrecken (fs_airline, fs_onr, fs_start, fs_ziel, fs_dauer) values ($airline, \"$fnummer\", $von_airport, $nach_airport, $dauer)";
   $query = mysql_query ($querystr);
   if (mysql_affected_rows() == 1) {
      echo ("Die Flugstrecke wurde erfolgreich eingetragen.<br />");
   } else {
      echo ("Die Flugstrecke konnte nicht eingetragen werden.<br />");
   }
   
   // ID der neuen Flugstrecke anhand der Orig.-Flugnummer ermitteln
   $querystr = "SELECT fs_nr FROM rb_flugstrecken WHERE fs_onr=\"$fnummer\"";
   $query = mysql_query ($querystr);
   list ($fs_nr) = mysql_fetch_row ($query);
   
   // Wochentagnamen für die Ausgabe
   $wt_namen = array ("So", "Mo", "Di", "Mi", "Do", "Fr", "Sa", "So");

   // Zeitraum festlegen
   $start = strtotime ("2005-01-01");
   $ende = strtotime ("2006-12-31");

   $ein_tag = 86400;  // Sekunden pro Tag

   // Beginn des Abfrage-Strings
   $querystr = "INSERT INTO rb_fluege (fl_strecke, fl_preis, fl_datum, fl_zeit) VALUES ";

   // Schleife über 2005/2006, um alle entsprechenden Flüge einzutragen
   for ($tag = $start; $tag <= $ende; $tag += $ein_tag) {
      // Numerischen Wochentag ermitteln
      $wtnr = date ("w", $tag);
      // Soll der Flug an diesem Tag stattfinden?
      if ($wt[$wtnr]) {
         // Preis je nach Monat berechnen (Ferienaufschläge):
         // Apr., Okt., Dez. + 40%
         // Jul./Aug. + 80%
         $preis = $gpreis;
         $monat = date ("n", $tag);
         if ($monat == 4 || $monat == 10 || $monat == 12) {
            $preis *= 1.4;
         } elseif ($monat == 7 || $monat == 8) {
            $preis *= 1.8;
         }
         
         // Den Flug für den aktuellen Tag eintragen
         $datum = date ("Y-m-d", $tag);
         // Flug zum Abfrage-String hinzufügen
         $querystr .= "($fs_nr, $preis, \"$datum\", \"$zeit\"),";
      }
   }
   // Letztes Komma am Ende entfernen
   $querystr = rtrim ($querystr, ",");
   // Abfrage durchführen
   $query = mysql_query ($querystr);
   $eintr = mysql_affected_rows();
   echo ("<br /><br />$eintr einzelne Flugeintr&auml;ge vorgenommen.");

?>
<br />
<br />
<a href="mk_fluege.php">Einen weiteren Flug eintragen</a>
</body>
</html>
<?php

  // Die schließende else-Klammer
  }
  
?>