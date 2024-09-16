<?php

   include ("util.inc.php");
   
   // Datenbankverbindung herstellen
   $conn = new_db_connect ("reisebuero", "root", "");
   
   // Formulardaten auslesen
   $hfl_buchnr = cgi_param ("hinflug", 0);
   $rfl_buchnr = cgi_param ("rueckflug", 0);
   $fl_pers = cgi_param ("fl_pers", 0);
   $ht_buchnr = cgi_param ("hotel", 0);
   $ht_ez = cgi_param ("ht_ez", 0);
   $ht_dz = cgi_param ("ht_dz", 0);
   
   // Zurück zur Auskunftsseite, falls nicht mindestens
   // ein Hinflug ausgewählt wurde
   if (!is_numeric ($hfl_buchnr) || $hfl_buchnr <= 0 || !is_numeric ($fl_pers) || $fl_pers <= 0) {
      $fehler = "Eine Buchung muss mindestens einen Hinflug für mindestens eine Person enthalten.";
      header ("Location: auskunft.php?f=$fehler");
   }
   
   // Hinflugdatum und -preis ermitteln
   $querystr = "SELECT fl_datum, fl_preis FROM rb_fluege WHERE fl_nr=$hfl_buchnr";
   $query = $conn->query ($querystr);
   list ($hfl_datum, $hfl_preis) = $query->fetch_row ();
   
   // Evtl. Rückflugdatum und -preis ermitteln
   if (is_numeric ($rfl_buchnr) && $rfl_buchnr > 0) {
      $querystr = "SELECT fl_datum, fl_preis FROM rb_fluege WHERE fl_nr=$rfl_buchnr";
      $query = $conn->query ($querystr);
      list ($rfl_datum, $rfl_preis) = $query->fetch_row ();
   } else {
      // Für gültigen Buchungsdatensatz andernfalls Rückflug- auf Hinflugdatum und Rückflug auf 0 setzen
      $rfl_datum = $hfl_datum;
      $rfl_buchnr = 0;
   }
   
   // Evtl. Hotelpreis ermitteln und Aufenthaltsdauer berechnen
   if (is_numeric ($ht_buchnr) && $ht_buchnr > 0 && ($ht_ez > 0 || $ht_dz > 0)) {
      $querystr = "SELECT ht_ezpreis, ht_dzpreis FROM rb_hotels WHERE ht_nr=$ht_buchnr";
      $query = $conn->query ($querystr);
      list ($ht_ezpreis, $ht_dzpreis) = $query->fetch_row();
      
      // MySQL um die Berechnung der Aufenthaltsdauer bitten, da es in PHP komplizierter wäre
      $querystr = "SELECT (UNIX_TIMESTAMP(\"$rfl_datum\") - UNIX_TIMESTAMP(\"$hfl_datum\")) / 86400";
      $query = $conn->query ($querystr);
      list ($dauer) = $query->fetch_row ();
      
      // Übrigens: Wenn kein Rückflug stattfindet (Dauer 0!), gibt es auch keinen Hotelaufenthalt
      if ($dauer == 0) {
         $ht_buchnr = 0;
      }
   } else {
      // Hotelbuchung sauber auf 0 setzen
      $ht_buchnr = 0;
   }
      
   // Buchung eintragen
   $querystr = "INSERT INTO rb_buchungen (bu_datum, bu_perszahl, bu_startdat, bu_enddat, bu_hinflug, bu_rueckflug, bu_hotel, bu_ezanz, bu_dzanz, bu_status) VALUES (NOW(), $fl_pers, \"$hfl_datum\", \"$rfl_datum\", $hfl_buchnr, $rfl_buchnr, $ht_buchnr, $ht_ez, $ht_dz, \"in_arbeit\")";
   $query = $conn->query ($querystr);
   
?>
<html>
<head>
<title>Reiseb&uuml;ro: Buchungsbest&auml;tigung</title>
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
<h2>Buchungsbest&auml;tigung</h2>
<?php

   if ($conn->affected_rows == 1) {
   
      // Buchungsnummer auswählen - die zuletzt eingefügte (=höchste) ID
      // WARNUNG: Bei sehr frequentierten Sites ist dieses Verfahren möglicherweise unsicher!
      $querystr = "SELECT MAX(bu_nr) FROM rb_buchungen";
      $query = $conn->query ($querystr);
      list ($buchnr) = $query->fetch_row ();
      
      // Buchung anzeigen und bestätigen lassen, falls es funktioniert hat
      echo ("Bitte best&auml;tigen Sie die folgende Buchung:<br /><br /><br />");
      
      // Erst einmal alle Gesamtpreise auf 0 setzen
      $hfl_gpreis = 0;
      $rfl_gpreis = 0;
      $ht_gpreis = 0;
      
      // Hinflug
      
      // Weitere Hinflugdetails ermitteln
      // Flugstrecke
      $querystr = "SELECT fl_strecke FROM rb_fluege WHERE fl_nr=$hfl_buchnr";
      $query = $conn->query ($querystr);
      list ($hfl_fs) = $query->fetch_row ();
      // Originalflugnummer, Airlinenummer, Startortnummer, Zielortnummer
      $querystr = "SELECT fs_onr, fs_airline, fs_start, fs_ziel FROM rb_flugstrecken WHERE fs_nr=$hfl_fs";
      $query = $conn->query ($querystr);
      list ($hfl_flugnr, $hfl_ai_nr, $hfl_st_nr, $hfl_zi_nr) = $query->fetch_row ();
      // Airline
      $querystr = "SELECT ai_name FROM rb_airlines WHERE ai_nr=$hfl_ai_nr";
      $query = $conn->query ($querystr);
      list ($hfl_airline) = $query->fetch_row ();
      // Startflughafen
      $querystr = "SELECT ap_name, ap_kuerzel FROM rb_airports WHERE ap_nr=$hfl_st_nr";
      $query = $conn->query ($querystr);
      list ($hfl_start, $hfl_start_k) = $query->fetch_row ();
      // Zielflughafen
      $querystr = "SELECT ap_name, ap_kuerzel FROM rb_airports WHERE ap_nr=$hfl_zi_nr";
      $query = $conn->query ($querystr);
      list ($hfl_ziel, $hfl_ziel_k) = $query->fetch_row ();
      // Gesamtpreis berechnen
      $hfl_gpreis = $fl_pers * $hfl_preis;
      // Ausgabe
      echo ("<dl><dt><b>H i n f l u g</b></dt><dd>");
      echo ("<br />$hfl_datum<br /><b>$hfl_airline</b> Flug Nr. <b>$hfl_flugnr</b><br />");
      echo ("von <b>$hfl_start</b> ($hfl_start_k) nach <b>$hfl_ziel</b> ($hfl_ziel_k)<br />");
      echo ("<b>$fl_pers</b>");
      if ($fl_pers == 1) {
         echo (" Person<br />");
      } else {
         echo (" Personen<br />");
      }
      echo ("Einzelpreis: <b>$hfl_preis,00 EUR</b>; Gesamtpreis: <b>$hfl_gpreis,00 EUR</b><br />&nbsp;</dd>");
      
      // Rückflug
      if ($rfl_buchnr > 0) {
         // Weitere Hinflugdetails ermitteln
         // Flugstrecke
         $querystr = "SELECT fl_strecke FROM rb_fluege WHERE fl_nr=$rfl_buchnr";
         $query = $conn->query ($querystr);
         list ($rfl_fs) = $query->fetch_row ();
         // Originalflugnummer, Airlinenummer, Startortnummer, Zielortnummer
         $querystr = "SELECT fs_onr, fs_airline, fs_start, fs_ziel FROM rb_flugstrecken WHERE fs_nr=$rfl_fs";
         $query = $conn->query ($querystr);
         list ($rfl_flugnr, $rfl_ai_nr, $rfl_st_nr, $rfl_zi_nr) = $query->fetch_row ();
         // Airline
         $querystr = "SELECT ai_name FROM rb_airlines WHERE ai_nr=$rfl_ai_nr";
         $query = $conn->query ($querystr);
         list ($rfl_airline) = $query->fetch_row ();
         // Startflughafen
         $querystr = "SELECT ap_name, ap_kuerzel FROM rb_airports WHERE ap_nr=$rfl_st_nr";
         $query = $conn->query ($querystr);
         list ($rfl_start, $rfl_start_k) = $query->fetch_row ();
         // Zielflughafen
         $querystr = "SELECT ap_name, ap_kuerzel FROM rb_airports WHERE ap_nr=$rfl_zi_nr";
         $query = $conn->query ($querystr);
         list ($rfl_ziel, $rfl_ziel_k) = $query->fetch_row ();
         // Gesamtpreis berechnen
         $rfl_gpreis = $fl_pers * $rfl_preis;
         // Ausgabe
         echo ("<dt><b>R &uuml; c k f l u g</b></dt><dd>");
         echo ("<br />$rfl_datum<br /><b>$rfl_airline</b> Flug Nr. <b>$rfl_flugnr</b><br />");
         echo ("von <b>$rfl_start</b> ($rfl_start_k) nach <b>$rfl_ziel</b> ($rfl_ziel_k)<br />");
         echo ("<b>$fl_pers</b>");
         if ($fl_pers == 1) {
            echo (" Person<br />");
         } else {
            echo (" Personen<br />");
         }
         echo ("Einzelpreis: <b>$rfl_preis,00 EUR</b>; Gesamtpreis: <b>$rfl_gpreis,00 EUR</b><br />&nbsp;</dd>");
      }
      
      // Hotelaufenthalt
      if ($ht_buchnr > 0) {
         // Weitere Hoteldetails ermitteln
         // Name und Stadtnummer des Hotels
         $querystr = "SELECT ht_name, ht_stadt FROM rb_hotels WHERE ht_nr=$ht_buchnr";
         $query = $conn->query ($querystr);
         list ($ht_name, $ht_st_nr) = $query->fetch_row ();
         // Stadt, Landnummer
         $querystr = "SELECT st_name, st_eigen, st_land FROM rb_staedte WHERE st_nr=$ht_st_nr";
         $query = $conn->query ($querystr);
         list ($ht_stadt, $ht_city, $ht_landnr) = $query->fetch_row ();
         // Land
         $querystr = "SELECT la_name FROM rb_laender WHERE la_nr=$ht_landnr";
         $query = $conn->query ($querystr);
         list ($ht_land) = $query->fetch_row ();
         // Gesamtpreis berechnen
         $ht_gpreis = $ht_ez * $ht_ezpreis * $dauer + $ht_dz * $ht_dzpreis * $dauer;
         // Ausgabe
         echo ("<dt><b>H o t e l a u f e n t h a l t</b></dt><dd><br />");
         echo ("<b>$ht_name</b> in <b>$ht_stadt</b>");
         if ($ht_stadt != $ht_city) {
            echo ("($ht_city)");
         }
         echo (", <b>$ht_land</b><br />");
         if ($ht_ez > 0) {
            echo ("<b>$ht_ez</b> Einzelzimmer &agrave; <b>$ht_ezpreis,00 EUR</b><br />");
         }
         if ($ht_dz > 0) {
            echo ("<b>$ht_dz</b> Doppelzimmer &agrave; <b>$ht_dzpreis,00 EUR</b><br />");
         }
         echo ("f&uuml;r <b>$dauer</b> Tage<br />");
         echo ("Gesamtpreis: <b>$ht_gpreis,00 EUR</b><br />&nbsp;</dd>");
      }
      // Gesamtbetrag anzeigen
      $gpreis = $hfl_gpreis + $rfl_gpreis + $ht_gpreis;
      echo ("<dt><b>G e s a m t b e t r a g</b></dt><dd><br />");
      echo ("<b>$gpreis,00 EUR</b> incl. 16% Mehrwertsteuer</dd>");
      echo ("</dl>");
      
?>
<form action="buchabschl.php" method="post">
<input type="hidden" name="buchnr" value="<?php echo ($buchnr); ?>" />
<input type="hidden" name="buchtxt" value="<?php echo ("$hfl_datum bis $rfl_datum, von $hfl_start nach $hfl_ziel f&uuml;r $gpreis,00 EUR"); ?>" />
<input type="submit" name="bestaet" value="Buchung best&auml;tigen" />
<input type="submit" name="abbruch" value="Buchung abbrechen" />
</form>
<?php
      
  } else {
      // Ansonsten Fehlermeldung
      echo ("Leider ist bei der Buchung ein Fehler aufgetreten.<br />Bitte w&auml;hlen Sie Ihre Reisedaten <a href=\"auskunft.php\">erneut</a>.");
  }
   
?>
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