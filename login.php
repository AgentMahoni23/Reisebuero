<?php

   include ("util.inc.php");
   
   $conn = new_db_connect ("reisebuero", "root", "");
   
   
   // Funktion zur Wahl eines Datums
   function datumswahl ($feldname) {
      // Tageswähler
      echo ("<select name=\"{$feldname}_tag\" size=\"1\">\n");
      echo ("<option value=\"0\">Tag</option>\n");
      for ($i = 1; $i <= 31; $i++) {
         echo ("<option value=\"$i\">$i</option>\n");
      }
      echo ("</select>.\n");
      // Monatswähler
      echo ("<select name=\"{$feldname}_monat\" size=\"1\">\n");
      echo ("<option value=\"0\">Monat</option>\n");
      for ($i = 1; $i <= 12; $i++) {
         echo ("<option value=\"$i\">$i</option>\n");
      }
      echo ("</select>.\n");
      // Jahr (Textfeld)
      echo ("<input type=\"text\" name=\"{$feldname}_jahr\" value=\"19\" size=\"10\" maxlength=\"4\" />\n");
   }
   
   // Bereits eingeloggt?
   session_start();
   $user_nummer = session_param ("user", 0);

   // Formulardaten auslesen
   // Wurde eine Buchungsanforderung mitgeschickt?
   $buchnr = cgi_param ("b", 0);
   $buchtxt = cgi_param ("bt", "");
   // Erzwungene Neuanmeldung wegen ungültiger Benutzer-ID?
   $user = cgi_param ("user", 0);
   $forcelogin = cgi_param ("forcelogin", 0);
   // Fehler bei vorherigem Anmeldeversuch?
   $fehlermode = cgi_param ("fm", 0);
   $fehler = cgi_param ("f", "");
   
?>
<html>
  <head>
    <title>Reiseb&uuml;ro - Benutzeranmeldung</title>
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
    Anmeldung
    |
    <a href="gast.php">G&auml;stebuch</a>
    |
    <a href="forum.php">Forum</a>
    ]
    <!-- Ende der Navigationsleiste> -->
    <h2>Kundenanmeldung</h2>
<?php

  // Bereits eingeloggt und KEINE forcelogin?
  // if ($user && !$forcelogin)
  if (!$forcelogin && $user) {
     echo ("Sie sind bereits angemeldet. <a href=\"javascript: history.back();\">Zur&uuml;ck</a>");
  } else {
  
?>
    Bitte geben Sie hier Ihre E-Mail-Adresse ein, falls Sie bereits registriert sind:
    <br />
    <form action="testlog.php" method="post">
      <input type="hidden" name="b" value="<?php echo ($buchnr); ?>" />
      <input type="hidden" name="bt" value="<?php echo ($buchtxt); ?>" />
      <input type="text" name="mail" size="50" maxlength="50" />
      <input type="submit" value="Anmelden" />
      <br />
<?php
   
   if ($fehlermode == 1) {
      echo ("<span style=\"color: #FF0000\">Fehler: $fehler</span><br />");
   }
   
?>
      <span style="font-size: 10px">Anmerkung: Hier fehlt zur Praxistauglichkeit eine Passwort&uuml;berpr&uuml;fung.</span>
    </form>
    <h2>Neuer Kunde</h2>
    Bitte geben Sie hier Ihre Daten ein, falls Sie ein neuer Kunde sind (die mit * markierten Felder sind Pflichtangaben):
<?php
   
   if ($fehlermode == 2) {
      echo ("<br /><span style=\"color: #FF0000\">Fehler: $fehler</span><br />");
   }
   
?>
    <form action="newuser.php" method="post">
      <input type="hidden" name="b" value="<?php echo ($buchnr); ?>" />
      <input type="hidden" name="bt" value="<?php echo ($buchtxt); ?>" />
      <table border="0" cellpadding="4">
        <tr>
          <td>*</td>
          <td>E-Mail:</td>
          <td valign="top"><input type="text" name="mail" size="50" /></td>
        </tr>
        <tr>
          <td colspan="3"><span style="font-size: 10px">Dies wird Ihre Anmelde-ID.</span></td>
        </tr>
        <tr>
          <td>*</td>
          <td>Name:</td>
          <td><input type="text" name="fname" size="50" /></td>	
        </tr>
        <tr>
          <td>*</td>
          <td>Vorname:</td>
          <td><input type="text" name="vname" size="50" /></td>
        </tr>
        <tr>
          <td>*</td>
          <td>Geburtsdatum:</td>
          <td><?php datumswahl ("geb"); ?></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>Stra&szlig;e:</td>
          <td><input type="text" name="str" size="50" /></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>Hausnummer:</td>
          <td><input type="text" name="hausnr" size="10" /></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>Postleitzahl:</td>
          <td><input type="text" name="plz" size="10" /></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>Wohnort:</td>
          <td><input type="text" name="ort" size="50" /></td>
        </tr>
        <tr>
          <td>*</td>
          <td>Land:</td>
          <td>
            <select name="land" size="1">
              <option value="0">[Bitte w&auml;hlen]</option>
<?php

   // Länder aus der Datenbank lesen und als Auswahlmenü ausgeben
   $querytext = "SELECT la_nr, la_name FROM rb_laender ORDER BY la_name ASC";
   $query = $conn->query ($querytext);
   while (list ($la_nr, $la_name) = $query->fetch_row ()) {
      echo ("<option value=\"$la_nr\">$la_name</option>\n");
   }
   
?>
            </select>
          </td>
        <tr>
          <td>&nbsp;</td>
          <td>Telefon:</td>
          <td><input type="text" name="tel" size="30" /></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td colspan="2">
            <input type="submit" value="Eintragen" />
            <input type="reset" value="Zur&uuml;cksetzen" />
          </td>
        </tr>
      </table>
    </form>
<?php

  // Schließende else-Klammer
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