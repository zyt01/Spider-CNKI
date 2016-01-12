<?php
$conn = mysql_pconnect("127.0.0.1", "root", "") or die("Database connect error" . mysql_error());
mysql_select_db("cnki_db", $conn) or die("Database access error" . mysql_error());
mysql_query("set names utf8");
?>