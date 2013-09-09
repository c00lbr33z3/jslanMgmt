<?php
/**
 * Login Page JSLAN Mgmt Page
 * @package jsmgmt
 * @since 0.1
 * @version 0.1
 * @link http://www.js-forum.ch/jslan/mgmt
 */

//include config files
include_once('../config.php');
include_once('../session.php');
include_once('../template.php');

$title = "JSLAN v6|Mgmt - Login";

if(!isset($_GET['action'])){
	//If User has no session show login
	if($_SESSION['login'] == 0){
		$index = show("login/index", array("" => ""));	
	}else {
	//show user functions
		$index = show("login/loggedin", array("" => ""));
		$title = "JSLAN v6|Mgmt - User Control Panel";
		if($_SESSION['admin'] == 1){
			//if user is admin show admin functions
			$index .= show("login/admin", array("" => ""));
		}
	}
}else {
//if $_GEt['action'] is set -> do whatever was called
	switch ($_GET['action']) {
		case 'login':
			//do login process
			//create db connection
			$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
			//print error if something went wrong
			if ($mysqli->connect_errno) {
    			echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}
			//filter user input
			$user = $mysqli->real_escape_string($_POST['username']);
			$pass = $mysqli->real_escape_string($_POST['password']);

			$user=filtervar($user);
			$pass=filtervar($pass);

			//fetch user from db
			$stmt = $mysqli->prepare("SELECT `ID`, `admin`, `password` FROM `users` WHERE `username` = ?");
			$stmt->bind_param("s", $user);
			$stmt->execute();
			$stmt->store_result();

			//make sure there is only 1 result
			if($stmt->num_rows() == 1){
				$stmt->bind_result($id, $level, $password);
				$stmt->fetch();
			}else {
				//user does not exist
				$index = show("error", array("error" => "Deine Eingaben waren falsch!",
										 "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));
				$password=""; //set dummy password for pw check
			}
			$stmt->close();
			$mysqli->close();

			//check login if correct set session variables
			if(sha1($pass) == $password){
				//set session variables
				$_SESSION['login'] = 1; //logged in
				$_SESSION['admin'] = $level; //userlevel 0=user, 1=admin
				$_SESSION['uid'] = $id; //save userid for registration
				$_SESSION['sesstime'] = time(); //set login time
				$_SESSION['ip'] = $_SERVER['REMOTE_ADDR']; //save IP in Session for security checks.
				
				$index = show("login/success", array('redirect' => '<meta http-equiv="refresh" content="0; URL=./index.php">'));
			}else {
			//password was wrong
				$index = show("error", array("error" => "Deine Eingaben waren falsch!",
										 "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));
			}
			break;
		case 'logout':
			if($_SESSION['login'] == 0){
				$title = "JSLAN v6|Mgmt - Oops";
				$index = show("error", array("error" => "Oops! Etwas ging schief!",
										 "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));
				page($index, $title); die();
			}
			//do logout process
			session_destroy();
			$index = show("login/logoutok", array("message" => "Du hast dich erfolgreich <br /><br />ausgeloggt!",
												  "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));
			break;
		case 'lanreg':
			if($_SESSION['login'] == 0){
				$title = "JSLAN v6|Mgmt - Oops";
				$index = show("error", array("error" => "Oops! Etwas ging schief!",
										 "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));
				page($index, $title); die();
			}
			$_SESSION['sesstime'] = time(); //update session time
			//create db connection
			$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
			//print error if something went wrong
			if ($mysqli->connect_errno) {
    			echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}
			//check if user is already registerd
			//fetch registration status
			$stmt = $mysqli->prepare("SELECT `registered` FROM `users` WHERE `ID` = ?");
			$stmt->bind_param("i", $_SESSION['uid']);
			$stmt->execute();
			$stmt->store_result();

			//make sure there is only 1 result
			if($stmt->num_rows() == 1){
				$stmt->bind_result($regstatus);
				$stmt->fetch();
			}else {
				//user does not exist
				$index = show("error", array("error" => "Oops! Etwas ging schief!",
										 "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));
			}

			if($regstatus==1){
				//user is already registered
				$index = show("login/alreadyregisted", array("" => ""));
			}else {
				//register for lan party
				$stmt = $mysqli->prepare("UPDATE `users` SET `registered` = '1' WHERE `ID` = ?");
				$stmt->bind_param("i", $_SESSION['uid']);
				$stmt->execute();

				$index = show("login/registrationok", array("" => ""));
			}
			$stmt->close();
			$mysqli->close();
			break;
		case 'info':
			if($_SESSION['login'] == 0){
				$title = "JSLAN v6|Mgmt - Oops";
				$index = show("error", array("error" => "Oops! Etwas ging schief!",
										 "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));
				page($index, $title); die();
			}
			$_SESSION['sesstime'] = time(); //update session time
			//show user info
			//create db connection
			$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
			//print error if something went wrong
			if ($mysqli->connect_errno) {
    			echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}

			//fetch user from db
			$stmt = $mysqli->prepare("SELECT * FROM `users` WHERE `ID` = ?");
			$stmt->bind_param("i", $_SESSION['uid']);
			$stmt->execute();
			$stmt->store_result();

			//make sure there is only 1 result
			if($stmt->num_rows() == 1){
				$stmt->bind_result($id, $username, $password, $admin, $forename, $surname, $mail, $registered, $paid);
				$stmt->fetch();
			}else {
				//user does not exist
				$index = show("error", array("error" => "Oops! Etwas ging schief!",
										 "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));
			}
			$stmt->close();
			$mysqli->close();

			//make numbers readable
			if($registered==1){
				$registered = "Ja";
			}else {
				$registered = "Nein";
			}

			if($paid==1){
				$paid = "Ja";
			}else {
				$paid = "Nein";
			}

			//show the stuff ;) 
			$index = show("login/info", array("forename" => $forename,
											 "surname" => $surname,
 											 "username" => $username,
 											 "mail" => $mail,
 											 "registered" => $registered,
 											 "paid" => $paid));
			//show who's attending the LAN
			$index .= show("login/laninfo", array("" => ""));
			//create db connection
			$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
			//print error if something went wrong
			if ($mysqli->connect_errno) {
		    	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}
			$registrationstate = 1;
			//show registered users
			$stmt = $mysqli->prepare("SELECT `username`, `forename`, `surname` FROM `users` WHERE `registered` = ? ORDER BY `username` DESC");
			$stmt->bind_param("i", $registrationstate);
			$stmt->execute();
			$stmt->store_result();
			$stmt->bind_result($username, $forename, $surname);
			while ($stmt->fetch()) {
				$user = '<li>'.$username.' - '.$forename.' '.$surname.'</li>';
				$index .= show("login/laninfoelement", array("user" => $user));
			}
			$stmt->close();
			$mysqli->close();
			$index .= show("login/laninfofooter", array("" => ""));
			break;
		case 'profile':
			if($_SESSION['login'] == 0){
				$title = "JSLAN v6|Mgmt - Oops";
				$index = show("error", array("error" => "Oops! Etwas ging schief!",
										 "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));
				page($index, $title); die();
			}
			$_SESSION['sesstime'] = time(); //update session time
			//edit your profile
			//create db connection
			$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
			//print error if something went wrong
			if ($mysqli->connect_errno) {
    			echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}

			//fetch user from db
			$stmt = $mysqli->prepare("SELECT * FROM `users` WHERE `ID` = ?");
			$stmt->bind_param("i", $_SESSION['uid']);
			$stmt->execute();
			$stmt->store_result();

			//make sure there is only 1 result
			if($stmt->num_rows() == 1){
				$stmt->bind_result($id, $username, $password, $admin, $forename, $surname, $mail, $registered, $paid);
				$stmt->fetch();
			}else {
				//user does not exist
				$index = show("error", array("error" => "Oops! Etwas ging schief!",
										 "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));
			}
			$stmt->close();
			$mysqli->close();

			$index = show("login/profile", array("username" => $username,  
								  				  "forename" => $forename, 
								  				  "surname" => $surname, 
								  				  "mail1" => $mail, 
								  				  "mail2" => $mail));
			break;
		case 'profile_edit':
			if($_SESSION['login'] == 0){
				$title = "JSLAN v6|Mgmt - Oops";
				$index = show("error", array("error" => "Oops! Etwas ging schief!",
										 "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));
				page($index, $title); die();
			}
			$_SESSION['sesstime'] = time(); //update session time
			//create db connection
			$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
			//print error if something went wrong
			if ($mysqli->connect_errno) {
    			echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}
			//check if all fields have data
			if(!isset($_POST['username']) OR $_POST['username'] == ""){
				printfielderror();
			}
			if(!isset($_POST['password']) OR $_POST['password'] == ""){
				printfielderror();
			}
			if(!isset($_POST['password2']) OR $_POST['password2'] == ""){
				printfielderror();
			}
			if(!isset($_POST['forename']) OR $_POST['forename'] == ""){
				printfielderror();
			}
			if(!isset($_POST['surname']) OR $_POST['surname'] == ""){
				printfielderror();
			}
			if(!isset($_POST['mail1']) OR $_POST['mail1'] == ""){
				printfielderror();
			}
			if(!isset($_POST['mail2']) OR $_POST['mail2'] == ""){
				printfielderror();
			}
			//filter user input
			$username = $mysqli->real_escape_string($_POST['username']);
			$password = $mysqli->real_escape_string($_POST['password']);
			$password2 = $mysqli->real_escape_string($_POST['password2']);
			$forename = $mysqli->real_escape_string($_POST['forename']);
			$surname = $mysqli->real_escape_string($_POST['surname']);
			$mail1 = $mysqli->real_escape_string($_POST['mail1']);
			$mail2 = $mysqli->real_escape_string($_POST['mail2']);

			$username = filtervar($username);
			$password = filtervar($password);
			$password2 = filtervar($password2);
			$forename = filtervar($forename);
			$surname = filtervar($surname);
			$mail1 = filtervar($mail1);
			$mail2 = filtervar($mail2);
			
			//check if password ist correct
			if($password!=$password2){
				$mysqli->close();
				$index = show("error", array("error" => "Passwörter stimmen nicht<br /><br />  überein!",
										 "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));	
				page($index, $title); die();
			}
			//check if password policy is ok
			if(pwdPolicy($password) == false){
				$mysqli->close();
				$title = "JSLAN v6|Mgmt - Register";
				$index = show("error", array("error" => "Das Passwort entspricht<br /><br />  nicht den Kriterien!",
										 "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));	
				page($index, $title); die();
			}
			//check if mail is correct
			if($mail1!=$mail2){
				$mysqli->close();
				$index = show("error", array("error" => "Mailadressen stimmen nicht<br /><br /> überein!",
										 "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));
				page($index, $title); die();
			}
			//check if password has been altered
			if($password=="supersecretpassword"){
				//update without password
				$stmt = $mysqli->prepare("UPDATE `users` SET `username` = ?, `forename` = ?, `surname` = ?, `mail` = ? WHERE `ID` = ?");
				$stmt->bind_param("ssssi", $username, $forename, $surname, $mail1, $_SESSION['uid']);
				$stmt->execute();
				$index = show("login/profileedit", array("message" => "",
														"redirect" => '<meta http-equiv="refresh" content="0; URL=./index.php?action=info">'));
			}else {
				//hash password for db
				$password=sha1($password);
				//also update password
				$stmt = $mysqli->prepare("UPDATE `users` SET `username` = ?, `password` = ?, `forename` = ?, `surname` = ?, `mail` = ? WHERE `ID` = ?");
				$stmt->bind_param("sssssi", $username, $password, $forename, $surname, $mail1, $_SESSION['uid']);
				$stmt->execute();
				session_destroy();
				$index = show("login/profileedit", array("message" => "Weil du dein Passwort geändert hast, musst du dich nochmals einloggen.",
														"redirect" => '<meta http-equiv="refresh" content="5; URL=../login/index.php">'));
			}
			$stmt->close();
			$mysqli->close();
			break;
		default:
			//show error message because of wrong action.
			$index = show("error", array("error" => "Oops! Etwas ging schief!",
										 "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));
			break;
	}
}
page($index, $title);
?>