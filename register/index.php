<?php
/**
 * Registration Page for theJSLAN Mgmt Page
 * @package jsmgmt
 * @since 0.1
 * @version 0.1
 * @link http://www.js-forum.ch/jslan/mgmt
 */

//include config files
include_once('../config.php');
include_once('../session.php');
include_once('../template.php');

$title = "JSLAN v6|Mgmt - Register";
if(!isset($_GET['action'])){
	//show form
	$index = show("register/index", array("" => ""));
}else {
	//switch values
	switch ($_GET['action']) {
		case 'doregistration':
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
				$title = "JSLAN v6|Mgmt - Register";
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
				$title = "JSLAN v6|Mgmt - Register";
				$index = show("error", array("error" => "Mailadressen stimmen nicht<br /><br /> überein!",
										 "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));
				page($index, $title); die();
			}

			//hash password
			$password = sha1($password);

			//check if user already exists
			$stmt = $mysqli->prepare("SELECT `ID` FROM `users` WHERE `username` = ?");
			$stmt->bind_param("s", $username);
			$stmt->execute();
			$stmt->store_result();

			//make sure there is no result
			if($stmt->num_rows() == 1){
				//this user already exists
				$stmt->close();
				$title = "JSLAN v6|Mgmt - Opps";
				$index = show("error", array("error" => "Dieser User existiert bereits!",
											 "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));
				page($index, $title); die();
			}else {
				//user does not exist
				//create new user
				$stmt->close();
				$stmt = $mysqli->prepare("INSERT INTO `users` (
															`username` ,
															`password` ,
															`forename` ,
															`surname` ,
															`mail`
															)
														VALUES (?, ?, ?, ?, ?)");	

				$stmt->bind_param("sssss", $username, $password, $forename, $surname, $mail1);
				$stmt->execute();
				$stmt->close();
				$mysqli->close();

				//set mail header
				$headers = 'From: '.MAIL_FROM.'' . "\r\n" .
          	             'Reply-To: '.MAIL_FROM.'' . "\r\n" .
           	            'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
           	            'X-Mailer: PHP/' . phpversion();

           		 //set message
          		$message = '
            		<p>Hallo '.$forename.'!<br />
            		Du hast dich erfolgreich auf dem JSLAN Portal registriert!<br />
            		Du kannst dich nun mit deinem Usernamen: '.$username.' am Portal anmelden, um dich für die LAN anzumelden.<br /><br />
            		Viel Spass an der LAN ;)
            		</p>
            	';
            	//send mail
           		mail($mail1, "JSLAN Registrierung", $message, $headers);

           		//show stuff
				$index = show("register/success", array("forename" => $forename,
															"message" => "Logge dich ein, um dich für die LAN anzumelden.",
															"redirect" => '<meta http-equiv="refresh" content="5; URL=../login/index.php">'));
			}
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