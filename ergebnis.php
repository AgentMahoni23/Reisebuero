<?php

   include ("util.inc.php");
   
   $conn = new_db_connect ("reisebuero", "root", "");

   // Optimistische Annahme: kein Fehler
   $fehler = "";
   
   // Formulardaten entgegennehmen
   
   // Abreiseort
   $start = cgi_param ("start", -1);
   // Zielort
   $ziel = cgi_param ("ziel", -1);
   // Abreisedatum
   $starttag = cgi_param ("start_tag", 0);
   $startmonat = cgi_param ("start_monat", 0);
   $startjahr = cgi_param ("start_jahr", 0);
   // Rückreisedatum
   $endtag = cgi_param ("ende_tag", 0);
   $endmonat = cgi_param ("ende_monat", 0);
   $endjahr = cgi_param ("ende_jahr", 0);
   // Hotelinformationen gewünscht?
   $hotelinfo = cgi_param ("hotel", 0);
   
   // Plausibilität der Eingaben testen
   
   // Abreise- oder Zielort vergessen?
   if ($start == -1 || $ziel == -1) {
      $fehler = "Ortsangabe vergessen";
   }
   // Abreise- und Zielort gleich?
   if ($start == $ziel) {
      $fehler = "Abreise- und Zielort identisch";
   }
   // Abreise vor dem aktuellen Datum?
   $heute = date ("Ymd", time());
   $start_vergl = $startjahr.($startmonat < 10 ? "0" : "").$startmonat.($starttag < 10 ? "0" : "").$starttag;
   $end_vergl = $endjahr.($endmonat < 10 ? "0" : "").$endmonat.($endtag < 10 ? "0" : "").$endtag;
   if ($start_vergl < $heute) {
      $fehler = "Abreise frühestens heute möglich";
   }
   // Rückreise vor Abreise?
   if ($end_vergl <= $start_vergl) {
      $fehler = "Abreise muss vor Rückreise stattfinden";
   }
   
   // Ist ein Fehler aufgetreten?
   if ($fehler) {
      // Mit Fehlermeldung zurück zur Eingabe
      header ("Location:auskunft.php?f=$fehler");
   } else {
      // Der Rest der Anwendung findet nur statt,
      // wenn kein Fehler auftrat
   
?>
<html>
<head>
<title>Reiseb&uuml;ro: Reiseangebot</title>
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
<h2>Gefundene Angebote</h2>
<form action="buchung.php" method="post">
<?php

   // Flüge verfügbar?
  // $query = $conn->query ($querytext);
  // while (list ($nr, $stadt, $land) = $query->fetch_row()) {
   // Startflughafen heraussuchen
   $querytext = "SELECT ap_nr, ap_name, ap_kuerzel FROM rb_airports WHERE ap_stadt=$start";
   $query = $conn->query ($querytext);
   list ($start_ap_nr, $start_ap_name, $start_ap_kuerzel) = $query->fetch_row ();

   // Zielflughafen heraussuchen
   $querytext = "SELECT ap_nr, ap_name, ap_kuerzel FROM rb_airports WHERE ap_stadt=$ziel";
   $query = $conn->query ($querytext);
   list ($ziel_ap_nr, $ziel_ap_name, $ziel_ap_kuerzel) = $query->fetch_row ();
   
   echo ("<h3>Hinfl&uuml;ge</h3>\n");
   
   // Hinflüge heraussuchen
   $querytext = "SELECT fs_nr, fs_onr FROM rb_flugstrecken WHERE fs_start=$start_ap_nr AND fs_ziel=$ziel_ap_nr";
   $query = $conn->query ($querytext);
   // Steht diese Flugverbindung zur Verfügung?
   if ($query->num_rows > 0) {
      // Alle Flugangebote durchgehen
      $gefunden = 0;
      while (list ($fs_nr, $flugnummer) = $query->fetch_row ()) {
         // Ist dieser Flug am gewünschten Tag verfügbar?
         $flugdatum = $startjahr.($startmonat < 10 ? "-0" : "-").$startmonat.($starttag < 10 ? "-0" : "-").$starttag;
         $querytext = "SELECT fl_nr, fl_preis, fl_zeit FROM rb_fluege WHERE fl_strecke=$fs_nr AND fl_datum=\"$flugdatum\"";
         $query2 = $conn->query($querytext);
         if ($query2->num_rows > 0) {
            list ($hfl_buchnr, $flugpreis, $flugzeit) = $query2->fetch_row ();
            // Das Flugangebot anzeigen
            echo ("<input type=\"radio\" name=\"hinflug\" value=\"$hfl_buchnr\" />");
            echo ("<b>$flugnummer</b> $start_ap_name ($start_ap_kuerzel) - $ziel_ap_name ($ziel_ap_kuerzel): $flugdatum, $flugzeit - $flugpreis &euro;<br />");
            // Zähler erhöhen
            $gefunden++;
         }
      }
      if ($gefunden == 0) {
         // Keine passenden Angebote
         echo ("Leider nicht verf&uuml;gbar. Bitte w&auml;hlen Sie ein anderes Hinflugdatum.");
      }
   } else {
	  $gefunden = 0;
      echo ("Diese Flugverbindung steht nicht zur Verf&uuml;gung. Bitte w&auml;hlen Sie eine andere.");
   }
   
   // Anzahl der Hinflüge merken, da eine Buchung nur mit Hinflug nützlich ist
   $hinfl = $gefunden;
   
   echo ("<h3>R&uuml;ckfl&uuml;ge</h3>\n");
   
   // Auswahl: ohne Rückflug
   if ($hinfl) {
      echo ("<input type=\"radio\" name=\"rueckflug\" value=\"0\" checked=\"checked\" /><i>ohne R&uuml;ckflug</i><br />");
   }
   
   // Rückflüge heraussuchen
   $querytext = "SELECT fs_nr, fs_onr FROM rb_flugstrecken WHERE fs_start=$ziel_ap_nr AND fs_ziel=$start_ap_nr";
   $query = $conn->query ($querytext);
   // Steht diese Flugverbindung zur Verfügung?
   if ($query->num_rows > 0) {
      // Alle Flugangebote durchgehen
      $gefunden = 0;
      while (list ($fs_nr, $flugnummer) = $query->fetch_row ()) {
         // Ist dieser Flug am gewünschten Tag verfügbar?
         $flugdatum = $endjahr.($endmonat < 10 ? "-0" : "-").$endmonat.($endtag < 10 ? "-0" : "-").$endtag;
         $querytext = "SELECT fl_nr, fl_preis, fl_zeit FROM rb_fluege WHERE fl_strecke=$fs_nr AND fl_datum=\"$flugdatum\"";
         $query2 = $conn->query($querytext);
         if ($query2->num_rows > 0) {
            list ($rfl_buchnr, $flugpreis, $flugzeit) = $query2->fetch_row ();
            // Das Flugangebot anzeigen
            if ($hinfl) {
               echo ("<input type=\"radio\" name=\"rueckflug\" value=\"$rfl_buchnr\" />");
            }
            echo ("<b>$flugnummer</b> $ziel_ap_name ($ziel_ap_kuerzel) - $start_ap_name ($start_ap_kuerzel): $flugdatum, $flugzeit - $flugpreis &euro;<br />");
            // Zähler erhöhen
            $gefunden++;
         }
      }
      if ($gefunden == 0) {
         // Keine passenden Angebote
         echo ("Leider nicht verf&uuml;gbar. Bitte w&auml;hlen Sie ein anderes R&uuml;ckflugdatum.");
      }
   } else {
      echo ("Diese Flugverbindung steht nicht zur Verf&uuml;gung. Bitte w&auml;hlen Sie eine andere.");
   }
   // Anzahl der Personen für Flugbuchung
   if ($hinfl) {
?>

      <br />
      <br />
      <b>Flugbuchung</b> f&uuml;r
      <select name="fl_pers" size="1">
      <option value="1" selected="selected">1</option>
      <option value="2">2</option>
      <option value="3">3</option>
      <option value="4">4</option>
      <option value="5">5</option>
      <option value="6">6</option>
      </select>
      Person(en)<br />
      <br />
      <br />
      
<?php

   }
   
   // Hotelinformationen gewünscht?
   if ($hotelinfo) {
      echo ("<h3>Hotelangebote</h3>\n");

      // Datenbankabfrage für Hotels am Zielort
      $querytext = "SELECT ht_nr, ht_name, ht_ezpreis, ht_dzpreis, ht_bad, ht_mahlzeit FROM rb_hotels WHERE ht_stadt=$ziel";
      $query = $conn->query ($querytext);
      // Hotels verfügbar?
      if ($query->num_rows > 0) {
      
?>
  
  <table border="2" cellpadding="4">
    <tr>
      <?php if ($hinfl) { ?><th>Buchen</th><?php } ?>
      <th>Hotelname</th>
      <th>Badtyp</th>
      <th>Verpflegung</th>
      <th>Einzelzimmer/Nacht</th>
      <th>Doppelzimmer/Nacht</th>
    </tr>
    <tr>
      <?php if ($hinfl) { ?><td align="center"><input type="radio" name="hotel" value="0" checked="checked" /></td>
      <td colspan="5"><i>kein Hotel buchen</i></td><?php } ?>
    
<?php

         while (list ($ht_buchnr, $name, $ezpreis, $dzpreis, $bad, $mahlzeit) = $query->fetch_row ()) {
            echo ("<tr>\n");
            if ($hinfl) {
               echo ("<td align=\"center\"><input type=\"radio\" name=\"hotel\" value=\"$ht_buchnr\" /></td>\n");
            }
            echo ("<td>$name</td>\n");
            echo ("<td>$bad</td>\n");
            echo ("<td>$mahlzeit</td>\n");
            echo ("<td align=\"right\">$ezpreis &euro;</td>\n");
            echo ("<td align=\"right\">$dzpreis &euro;</td>\n");
         }
         echo ("</table>\n");
      } else {
         echo ("F&uuml;r diese Stadt sind keine Hotelangebote verf&uuml;gbar.");
         // Hotel-Info explizit auf 0 setzen
         $hotelinfo = 0;
      }
   }
   
?>
<br />

<?php if ($hinfl && $hotelinfo) { ?>

<b>Buchen:</b>
<select name="ht_ez" size="1">
<option value="0">0</option>
<option value="1" selected="selected">1</option>
<option value="2">2</option>
<option value="3">3</option>
</select>
Einzelzimmer und

<select name="ht_dz" size="1">
<option value="0" selected="selected">0</option>
<option value="1">1</option>
<option value="2">2</option>
<option value="3">3</option>
</select>
Doppelzimmer

<?php } ?>
<br />
<br />
<a href="auskunft.php">Reisedaten &auml;ndern</a><br />
<br />
<?php if ($hinfl) { ?><input type="submit" value="Jetzt buchen" /><?php } ?>
</form> 
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
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

<?php

   // Die schließende else-Klammer
   
   }
   
?> 