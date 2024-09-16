<?php

   function cgi_param ($feld, $default) {
      $var = $default;
      $rmeth = $_SERVER['REQUEST_METHOD'];
      if ($rmeth == "GET") {
         if (isset ($_GET[$feld]) && $_GET[$feld] != "") {
            $var = $_GET[$feld];
         }
      } elseif ($rmeth == "POST") {
         if (isset ($_POST[$feld]) && $_POST[$feld] != "") {
            $var = $_POST[$feld];
         }
      }
      return $var;
   }
   
   function session_param ($feld, $default) {
      $var = $default;
      if (isset ($_SESSION[$feld]) && $_SESSION[$feld] != "") {
         $var = $_SESSION[$feld];
      }
      return $var;
   }
   
   function old_db_connect ($database, $user, $pass) {
      $host = "127.0.0.1";
      $connID = mysql_connect ($host, $user, $pass);
      mysql_select_db ($database);
      return $connID;
   }
   
   function new_db_connect ($database, $user, $pass) {
      $host = "127.0.0.1";
      $connID = new mysqli($host, $user, $pass, $database);
      /*mysql_select_db ($database);*/
      return $connID;
   }
?>