<?php

   include ("util.inc.php");
   
   $conn = new_db_connect ("reisebuero", "root", "");
   
   session_start();
   
   // Formulardaten auslesen
   // Buchung?
   $buchnr = cgi_param ("b", 0);
   $buchtxt = cgi_param ("bt", "");
   // Benutzerdaten
   $mail = cgi_param ("mail", "");
   $fname = cgi_param ("fname", "");
   $vname = cgi_param ("vname", "");
   $geb_tag = cgi_param ("geb_tag", 0);
   $geb_monat = cgi_param ("geb_monat", 0);
   $geb_jahr = cgi_param ("geb_jahr", 0);
   $str = cgi_param ("str", "");
   $hausnr = cgi_param ("hausnr", "");
   $plz = cgi_param ("plz", "");
   $ort = cgi_param ("ort", "");
   $land = cgi_param ("land", 0);
   $tel = cgi_param ("tel", "");
   $user = cgi_param ("user", 0);
   // Überprüfung
   if (!$mail || !$fname || !$vname || !$geb_tag || !$geb_monat || !$geb_jahr || !$land) {
      // Es fehlt ein Pflichtparameter
      $fehlermode = 2;
      $fehler = "Eine erforderliche Angabe wurde vergessen.";
      header ("Location: login.php?b=$buchnr&bt=$buchtxt&fm=$fehlermode&f=$fehler&fname=$fname");
   } elseif (!is_numeric ($geb_tag) || !is_numeric ($geb_monat) || !is_numeric ($geb_jahr)) {
      // Falsche Angabe im Geburtsdatum
      $fehlermode = 2;
      $fehler = "Das Geburtsdatum enth&auml;lt eine ung&uuml;ltige Angabe.";
      header ("Location: login.php?b=$buchnr&bt=$buchtxt&fm=$fehlermode&f=$fehler");
   } else {
   
      // Existiert der User bereits?
      $querytext = "SELECT kk_nr FROM rb_kundenkontakte WHERE kk_mail=\"$mail\"";
      $query = $conn->query ($querytext);
      if ($query->num_rows > 0) {
         // Kundennummer ermitteln
         list ($user_nr) = $query->fetch_row ();
         // In die Session schreiben
         $_SESSION["user"] = $user_nr;
         $meldung = "Sie sind bereits registriert.";
      } else {
         // Daten eintragen
         $gebformat = "$geb_jahr-".($geb_monat < 10 ? "0" : "")."$geb_monat-".($geb_tag < 10 ? "0" : "").$geb_tag;
         $querytext = "INSERT INTO rb_kunden (kd_name, kd_vorname, kd_gdatum) VALUES (\"$fname\", \"$vname\", \"$gebformat\")";
         $query = $conn->query ($querytext);
         // Hat es funktioniert?
         if ($conn->affected_rows == 1) {
            // Benutzernummer ermitteln
            // (die höchste Nr., denn es KÖNNTE ja - unwahrscheinlicherweise - mehrere
            //  User mit denselben Daten geben)
            $querytext = "SELECT kd_nr FROM rb_kunden WHERE kd_name=\"$fname\" AND kd_vorname=\"$vname\" AND kd_gdatum=\"$gebformat\" ORDER BY kd_nr DESC";
            $query = $conn->query ($querytext);
            list ($user_nr) = $query->fetch_row ();
            // Kundenkontaktdaten eintragen
            $querytext = "INSERT INTO rb_kundenkontakte (kk_nr, kk_strasse, kk_hausnr, kk_plz, kk_ort, kk_land, kk_tel, kk_mail) VALUES ($user_nr, \"$str\", \"$hausnr\", \"$plz\", \"$ort\", $land, \"$tel\", \"$mail\")";
            $query = $conn->query ($querytext);
            if ($conn->affected_rows == 1) {
               // In die Session schreiben
               $_SESSION["user"] = $user_nr;
               $meldung = "Alle Daten wurden ordnungsgem&auml;&szlig; eingetragen.";
            } else {
               $meldung = "Fehler beim Eintragen der Kontaktdaten.";
            }
         } else {
            $meldung = "Fehler beim Eintragen der Grunddaten.";
         }
      }
      
?>
<html>
  <head>
    <title>Reiseb&uuml;ro: Neuanmeldung</title>
  </head>
  <body>
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
    <h2>Neuanmeldung</h2>
<?php

      echo ($meldung);
      echo ("<br /><br />");

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

   }
   
?>