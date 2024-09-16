<?php

  function ap_auswahl ($feldname) {
     $ausgabe = "<select name=\"{$feldname}_airport\">\n";
     // Flughäfen auslesen
     $querystr = "SELECT ap_nr, ap_kuerzel, ap_name FROM rb_airports ORDER BY ap_kuerzel ASC";
     $query = mysql_query ($querystr);
     while (list ($ap_nr, $ap_kuerzel, $ap_name) = mysql_fetch_row ($query)) {
        $ausgabe .= "<option value=\"$ap_nr\">$ap_kuerzel ($ap_name)</option>\n";
     }
     $ausgabe .= "</select>\n";
     return $ausgabe;
  }
  
  function wt_auswahl () {
     $ausgabe = "";
     $wt = array ("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");
     for ($i = 0; $i < sizeof ($wt); $i++) {
        $tag = $wt[$i];
        $ausgabe .= "<input type=\"checkbox\" name=\"wt$i\" value=\"1\" />$tag<br />\n";
     }
     return $ausgabe;
  }

  include ("util.inc.php");
  
  $connID = old_db_connect ("reisebuero", "rbuser", "R3153n");
  
  // evtl. Fehler auslesen
  $fehler = cgi_param ("f", "");

?>
<html>
<head>
<title>Flug-Generator</title>
</head>
<body>
<h1>Reiseb&uuml;ro</h1>
<h2>Flug-Generator</h2>
<?php

   if ($fehler) {
      echo ("<font color=\"#FF0000\">Ein Fehler ist aufgetreten: $fehler</font><br /><br />\n");
   }
   
?>
<form action="sv_fluege.php" method="post">
<?php

  echo ("Von:");
  echo (ap_auswahl ("von"));
  echo ("<br />");
  echo ("Nach:");
  echo (ap_auswahl ("nach"));
  echo ("<br /><br />\n");
  echo ("Wochentage:<br />\n");
  echo (wt_auswahl ());

?>
<br /><br />
Fluggesellschaft:
<select name="airline" size="1">
<?php

   $querystr = "SELECT ai_nr, ai_kuerzel, ai_name FROM rb_airlines ORDER BY ai_kuerzel ASC";
   $query = mysql_query ($querystr);
   while (list ($ai_nr, $ai_kuerzel, $ai_name) = mysql_fetch_row ($query)) {
      echo ("<option value=\"$ai_nr\">$ai_kuerzel ($ai_name)</option>\n");
   }

?>
</select>
<br /><br />
Original-Flugnummer:
<input type="text" name="fnummer" size="20" />
<br /><br />
Uhrzeit:
<input type="text" name="zeit" size="20" />
<br /><br />
Flugdauer:
<input type="text" name="dauer" size="20" /> Minuten
<br /><br />
Grundpreis:
<input type="text" name="gpreis" size="20" /> &euro;
<br /><br />
<input type="submit" value="Flug eintragen" />
</form>
</body>
</html>