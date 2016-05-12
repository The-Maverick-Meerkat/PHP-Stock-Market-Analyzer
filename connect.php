
<?php

$connect = mysql_connect('localhost','USER','*****'); //put your own user and password. 
//If using XAMPP for first time, it will probably be USER='root', with no password

if(!$connect) { 
	die('could not connect');
} else {echo "connected! <br>";
}
mysql_select_db("youtube", $connect)

?>