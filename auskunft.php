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
      // Aktuelles Jahr ermitteln
      $jahr = date("Y", time());
      // Wähler für aktuelles und nachfolgendes Jahr
      echo ("<select name=\"{$feldname}_jahr\" size=\"1\">\n");
      echo ("<option value=\"$jahr\" selected=\"selected\">$jahr</option>\n");
      $jahr++;
      echo ("<option value=\"$jahr\">$jahr</option>\n</select>\n");
   }
   
   // Liste aller Städte auslesen
   $stnr = array();
   $staedte = array();
   $querytext = "SELECT st_nr, st_name, la_name FROM rb_staedte INNER JOIN rb_laender ON st_land=la_nr ORDER BY la_name ASC, st_name ASC";
   $query = $conn->query ($querytext);
   while (list ($nr, $stadt, $land) = $query->fetch_row()) {
      array_push ($stnr, $nr);
      array_push ($staedte, "$stadt, $land");
   }
   
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
    <h1>Reiseb&uuml;ro</h1>
    
    <!-- Einfache Navigationsleiste -->
    [
    <a href="index.php">Home</a>
    |
    Reisesuche
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
    
    <h2>Reiseauswahl</h2>
<?php

   // Fehler bei der vorherigen Auswahl?
   $fehler = cgi_param ("f", "");
   if ($fehler) {
      echo ("<i>Ein Fehler ist aufgetreten: $fehler</i><br />");
   }
   
?>
    <form action="ergebnis.php" method="post">
      <table border="0" cellpadding="4">
      <tr>
        <td valign="top">Abreiseort:</td>
        <td>
          <select name="start" size="1">
            <option value="-1">[Bitte w&auml;hlen]</option>
<?php

   for ($i = 0; $i < sizeof ($staedte); $i++) {
      $nr = $stnr [$i];
      $stadt = $staedte [$i];
      echo ("<option value=\"$nr\">$stadt</option>\n");
   }
   
?>
          </select><br />
          <span style="font-size: 10px">Die meisten Flugangebote gehen von/nach K&ouml;ln/Bonn, einige auch von/nach Frankfurt oder D&uuml;sseldorf.</span>
        </td>
      </tr>
      <tr>
        <td>Zielort:</td>
        <td>
          <select name="ziel" size="1">
            <option value="-1">[Bitte w&auml;hlen]</option>
<?php

   for ($i = 0; $i < sizeof ($staedte); $i++) {
      $nr = $stnr [$i];
      $stadt = $staedte [$i];
      echo ("<option value=\"$nr\">$stadt</option>\n");
   }
   
?>
          </select>
        </td>
      </tr>
      <tr>
        <td>Abreisedatum:</td>
        <td>Datum: <?php datumswahl ("start"); ?></td>
      </tr>
      <tr>
        <td>R&uuml;ckreisedatum:</td>
        <td>Datum: <?php datumswahl ("ende"); ?></td>
      </tr>
      <tr>
        <td>Mit Hotelangebot?</td>
        <td>
          <input type="radio" name="hotel" value="1" checked="checked" />ja
          <input type="radio" name="hotel" value="0" />nein
        </td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td><input type="submit" value="Anfrage senden" />
      </tr>
      </table>
    </form>
  </body>
</html>