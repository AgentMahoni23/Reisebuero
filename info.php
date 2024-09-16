<?php

   include ("util.inc.php");
   
   $conn = new_db_connect ("reisebuero", "root", "");
   
   // Info über eine Stadt gewünscht?
   $infostadt = cgi_param ("stadt", 0);
   // Dann Daten dieser Stadt einlesen
   $querytext = "SELECT st_name, st_eigen, st_text_url, st_bild_url, la_name FROM rb_staedte, rb_laender WHERE st_land=la_nr AND st_nr=$infostadt";
   $query = $conn->query ($querytext);
   // Daten erhalten?
   if ($query->num_rows > 0) {
      list ($name, $eigenname, $text_url, $bild_url, $land) = $query->fetch_row ();
      // Flughafeninfo ermitteln
      $querytext = "SELECT ap_name, ap_zusatz, ap_kuerzel, ap_url FROM rb_airports WHERE ap_stadt=$infostadt";
      $query = $conn->query ($querytext);
      list ($ap_name, $ap_zusatz, $ap_kuerzel, $ap_url) = $query->fetch_row ();
   } else {
      // Stadtnummer explizit auf 0 setzen
      $infostadt = 0;
   }
   
?>
<html>
  <head>
    <title>Reiseb&uuml;ro: Touristeninfo</title>
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
    Touristeninfo
    |
    <a href="login.php">Anmeldung</a>
    |
    <a href="gast.php">G&auml;stebuch</a>
    |
    <a href="forum.php">Forum</a>
    ]
    <!-- Ende der Navigationsleiste> -->
    <h2>Touristeninfo</h2>
      <form action="info.php" method="get">
        Info &uuml;ber Reiseziel:
        <select name="stadt" size="1">
        <option value="0">[Bitte w&auml;hlen]</option>
<?php

   // Liste aller Städte mit Touristeninfo auslesen
   $querytext = "SELECT st_nr, st_name FROM rb_staedte WHERE st_text_url != '' ORDER BY st_name ASC";
   $query = $conn->query ($querytext);
   while (list ($nr, $stadt) = $query->fetch_row ()) {
      echo ("<option value=\"$nr\">$stadt</option>\n");
   }
   
?>
        </select>
        <input type="submit" value="OK" />
      </form>
<?php

   // Stadtinfo ausgeben, falls angefordert
   if ($infostadt) {
      echo ("<h3>Info &uuml;ber $name</h3>");
      echo ("<b>Stadt:</b> $name");
      // Den Eigennamen ausgeben, falls unterschiedlich
      if ($name != $eigenname) {
         echo (" ($eigenname)");
      }
      echo (", $land <br />");
      echo ("<b>Flughafen:</b> <a href=\"$ap_url\" target=\"_blank\">$ap_name</a>");
      if ($ap_zusatz) {
         echo (", $ap_zusatz");
      }
      echo (" ($ap_kuerzel)<br /><br />\n");
      echo ("<img src=\"stadtinfo/$bild_url\" /><br />");
      include ("stadtinfo/$text_url");
      
      // Sehenswürdigkeiten?
      $querytext = "SELECT sw_name, sw_eigen, sw_bild_url, sw_anschrift, sw_beschr FROM rb_sehensw WHERE sw_stadt=$infostadt";
      $query = $conn->query ($querytext);
      // Gefunden?
      if ($query->num_rows) {
         echo ("<h3>Sehensw&uuml;rdigkeiten</h3>");
         while (list ($sw_name, $sw_eigenname, $sw_bild_url, $sw_anschrift, $sw_beschr) = $query->fetch_row ()) {
            echo ("<b>$sw_name</b>");
            if ($sw_name != $sw_eigenname) {
               echo (" ($sw_eigenname)");
            }
            echo ("<br />\n");
            echo ("$sw_anschrift<br />\n");
            echo ("<img src=\"stadtinfo/$sw_bild_url\" /><br />\n");
            echo ("$sw_beschr<br /><br />\n");
         }
      }
      echo ("<br /><br />");
      echo ("(Text- und Bildquelle: <a href=\"http://de.wikipedia.org\">Wikipedia</a>)");
   }
   
?>
 <br />
    &nbsp;
    </td>
    </tr>
    </table>
  </body>
</html>