<?php
/**
 * Admin Functions JSLAN Mgmt Page
 * @package jsmgmt
 * @since 0.1
 * @version 0.1
 * @link http://www.js-forum.ch/jslan/mgmt
 */

//include config files
include_once('../config.php');
include_once('../session.php');
include_once('../template.php');

$title = "JSLAN v6|Mgmt - Admin";

//check user rights
if($_SESSION['login'] == 0){
	$title = "JSLAN v6|Mgmt - Oops";
	$index = show("error", array("error" => "Oops! Etwas ging schief!",
								 "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));
	page($index, $title); die();
}
if($_SESSION['admin'] == 0){
	$title = "JSLAN v6|Mgmt - Oops";
	$index = show("error", array("error" => "Oops! Etwas ging schief!",
								 "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));
	page($index, $title); die();
}

if(!isset($_GET['action'])){

} else {
	switch ($_GET['action']) {
		case 'show_userlist':
			//show who's registered
			$_SESSION['sesstime'] = time();
			$index = show("admin/userheader", array("message" => "Edit an account!", 
													"subtitle" => ""));
			//create db connection
			$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
			//print error if something went wrong
			if ($mysqli->connect_errno) {
		    	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}
			//show registered users
			$stmt = $mysqli->prepare("SELECT `ID`, `username`, `forename`, `surname` FROM `users` ORDER BY `username` DESC");
			$stmt->execute();
			$stmt->store_result();
			$stmt->bind_result($id, $username, $forename, $surname);
			while ($stmt->fetch()) {
				$user = '
				<li>
				<a href="./index.php?action=edit_userlist&id='.$id.'">'.$username.' - '.$forename.' '.$surname.'</a> 
				</li>';
				$index .= show("login/laninfoelement", array("user" => $user));
			}
			$stmt->close();
			$mysqli->close();
			$index .= show("login/laninfofooter", array("" => ""));
			break;
		case 'show_payments':
			//show who's has payed or not
			$_SESSION['sesstime'] = time();
			$index = show("admin/userheader", array("message" => "Payment Status", 
													"subtitle" => ""));
			//create db connection
			$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
			//print error if something went wrong
			if ($mysqli->connect_errno) {
		    	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}
			//show registered users
			$registrationstate = 1;
			//show registered users
			$stmt = $mysqli->prepare("SELECT `ID`, `username`, `forename`, `surname`, `paid` FROM `users` WHERE `registered` = ? ORDER BY `username` DESC");
			$stmt->bind_param("i", $registrationstate);
			$stmt->execute();
			$stmt->store_result();
			$stmt->bind_result($id, $username, $forename, $surname, $paid);
			while ($stmt->fetch()) {
				if($paid==0){
					//show red payment button
					$user = '
					<li>
					<span>'.$username.' - '.$forename.' '.$surname.'</span> 
					&nbsp;&nbsp;&nbsp;<a href="./index.php?action=pay&id='.$id.'"><img src="../template/images/button_cancel.png" alt="Pay"/></a>
					</li>';
					$index .= show("login/laninfoelement", array("user" => $user));
				} else {
					//show green payment button
					$user = '
					<li>
					<span>'.$username.' - '.$forename.' '.$surname.'</span> 
					&nbsp;&nbsp;&nbsp;<img src="../template/images/button_ok.png" alt="Pay"/>
					</li>';
					$index .= show("login/laninfoelement", array("user" => $user));
				}
			}
			$stmt->close();
			$mysqli->close();

			//show next area
			$index .= show("login/laninfofooter", array("" => ""));
			$index .= show("admin/userheader", array("message" => "Expenses and Ticket price",
													 "subtitle" => '<a href="index.php?action=add_expenses">Add expenses</a>'));
			//show expenses and calulate lan party price
			//fetch attendence list
			//create db connection
			$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
			//print error if something went wrong
			if ($mysqli->connect_errno) {
		    	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}
			$registrationstate = 1;
			//show registered users
			$stmt = $mysqli->prepare("SELECT `ID` FROM `users` WHERE `registered` = ?");
			$stmt->bind_param("i", $registrationstate);
			$stmt->execute();
			$stmt->store_result();
			$userlist=$stmt->num_rows();
			$stmt->close();
			$mysqli->close();

			//create db connection
			$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
			//print error if something went wrong
			if ($mysqli->connect_errno) {
		    	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}
			//fetch expenses from database
			$stmt = $mysqli->prepare("SELECT * FROM `expenses` ORDER BY `ID` DESC");
			$stmt->execute();
			$stmt->store_result();
			$stmt->bind_result($id, $description, $price, $customer);

			//init counter
			$totalprice = 0;
			while ($stmt->fetch()) {
				//list expenses
				$user = '
				<li>
				<span>'.$description.' | '.$customer.' | '.$price.' Chf</span> 
				</li>';
				$totalprice = $totalprice + $price;
				$index .= show("login/laninfoelement", array("user" => $user));
			}
			$stmt->close();
			$mysqli->close();
			$ticketprice = $totalprice / $userlist;
			$ticketprice = round($ticketprice, 1, PHP_ROUND_HALF_UP);
			$totalprice = round($totalprice, 1, PHP_ROUND_HALF_UP);

			$index .= show("admin/price", array("totalprice" => $totalprice,
												"ticketprice" => $ticketprice));

			$index .= show("login/laninfofooter", array("" => ""));
			
			break;
		case 'show_mail':
			$_SESSION['sesstime'] = time();
			//show form for newsletter
			$index = show("admin/showmail", array("" => ""));
			break;
		case 'send_mail':
			//fetch attendence list
			//create db connection
			$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
			//print error if something went wrong
			if ($mysqli->connect_errno) {
		    	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}
			//init vars
			$registrationstate = 1;
			$mail1 = "";
			//show registered users
			$stmt = $mysqli->prepare("SELECT `mail` FROM `users` WHERE `registered` = ?");
			$stmt->bind_param("i", $registrationstate);
			$stmt->execute();
			$stmt->bind_result($mail);
			while ($stmt->fetch()) {
				//get mail list
				$mail1 = $mail1 . "," . $mail;	
			}
			$stmt->close();
			$mysqli->close();
			//set mail header
			$headers = 'From: '.MAIL_FROM.'' . "\r\n" .
                       'Reply-To: '.MAIL_FROM.'' . "\r\n" .
         	           'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
          	           'X-Mailer: PHP/' . phpversion();

           	//set message
          	$message = $_POST['message'];
            $mail1 = substr($mail1, 1);
            //send mail
           	mail($mail1, "JSLAN Newsletter", $message, $headers);

           	//show stuff
			$index = show("admin/mailsent", array("redirect" => '<meta http-equiv="refresh" content="5; URL=../login/index.php">'));
			break;
		case 'edit_userlist':
			if(!isset($_GET['id'])){
				$title = "JSLAN v6|Mgmt - Oops";
				$index = show("error", array("error" => "Oops! Etwas ging schief!",
								 "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));
				page($index, $title); die();
			}
			if(!is_numeric($_GET['id'])){
				$title = "JSLAN v6|Mgmt - Oops";
				$index = show("error", array("error" => "Oops! Etwas ging schief!",
								 "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));
				page($index, $title); die();
			} else {
				$uid = $_GET['id'];
			}

			//create db connection
			$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
			//print error if something went wrong
			if ($mysqli->connect_errno) {
    			echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}

			//fetch user from db
			$stmt = $mysqli->prepare("SELECT * FROM `users` WHERE `ID` = ?");
			$stmt->bind_param("i", $uid);
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

			$index = show("admin/edituser", array("id" => $uid, 
												  "username" => $username,  
								  				  "forename" => $forename, 
								  				  "surname" => $surname, 
								  				  "mail1" => $mail, 
								  				  "mail2" => $mail,
								  				  "admin" => $admin,
								  				  "registered" => $registered,
								  				  "paid" => $paid));
			break;
		case 'profile_edit':
			$_SESSION['sesstime'] = time(); //update session time
			if(!isset($_GET['id'])){
				$title = "JSLAN v6|Mgmt - Oops";
				$index = show("error", array("error" => "Oops! Etwas ging schief!",
								 "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));
				page($index, $title); die();
			}
			if(!is_numeric($_GET['id'])){
				$title = "JSLAN v6|Mgmt - Oops";
				$index = show("error", array("error" => "Oops! Etwas ging schief!",
								 "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));
				page($index, $title); die();
			} else {
				$uid = $_GET['id'];
			}
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
			if(!isset($_POST['admin']) OR $_POST['admin'] == ""){
				printfielderror();
			}
			if(!isset($_POST['registered']) OR $_POST['registered'] == ""){
				printfielderror();
			}
			if(!isset($_POST['paid']) OR $_POST['paid'] == ""){
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
			$admin = $mysqli->real_escape_string($_POST['admin']);
			$registered = $mysqli->real_escape_string($_POST['registered']);
			$paid = $mysqli->real_escape_string($_POST['paid']);

			$username = filtervar($username);
			$password = filtervar($password);
			$password2 = filtervar($password2);
			$forename = filtervar($forename);
			$surname = filtervar($surname);
			$mail1 = filtervar($mail1);
			$mail2 = filtervar($mail2);
			$admin = filtervar($admin);
			$registered = filtervar($registered);
			$paid = filtervar($paid);
			
			//check if password ist correct
			if($password!=$password2){
				$mysqli->close();
				$index = show("error", array("error" => "Passwörter stimmen nicht<br /><br />  überein!",
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
				$stmt = $mysqli->prepare("UPDATE `users` SET `username` = ?, `forename` = ?, `surname` = ?, `mail` = ? , `admin` = ? , `registered` = ? , `paid` = ? WHERE `ID` = ?");
				$stmt->bind_param("ssssiiii", $username, $forename, $surname, $mail1, $admin, $registered, $paid, $uid);
				$stmt->execute();
				$index = show("login/profileedit", array("message" => "",
														"redirect" => '<meta http-equiv="refresh" content="3; URL=../login/index.php">'));
			}else {
				//check if password policy is ok
				if(pwdPolicy($password) == false){
					$mysqli->close();
					$title = "JSLAN v6|Mgmt - Register";
					$index = show("error", array("error" => "Das Passwort entspricht<br /><br />  nicht den Kriterien!",
											 "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));	
					page($index, $title); die();
				}
				//hash password for db
				$password=sha1($password);
				//also update password
				$stmt = $mysqli->prepare("UPDATE `users` SET `username` = ?, `password` = ?, `forename` = ?, `surname` = ?, `mail` = ? , `admin` = ? , `registered` = ? , `paid` = ? WHERE `ID` = ?");
				$stmt->bind_param("ssssiiii", $username, $password, $forename, $surname, $mail1, $admin, $registered, $paid, $uid);
				$stmt->execute();
				$index = show("login/profileedit", array("message" => "",
														"redirect" => '<meta http-equiv="refresh" content="3; URL=../login/index.php">'));
			}
			$stmt->close();
			$mysqli->close();
			break;
		case 'add_expenses':
			$_SESSION['sesstime'] = time();
			//show form for expenses
			$index = show("admin/addexpenses", array("" => ""));
			break;
		case 'add_expensestodb':
			$_SESSION['sesstime'] = time();
			//create db connection
			$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
			//print error if something went wrong
			if ($mysqli->connect_errno) {
    			echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}

			//check if all fields have data
			if(!isset($_POST['description']) OR $_POST['description'] == ""){
				printfielderror();
			}
			if(!isset($_POST['price']) OR $_POST['price'] == ""){
				printfielderror();
			}
			if(!isset($_POST['customer']) OR $_POST['customer'] == ""){
				printfielderror();
			}
			
			//filter user input
			$description = $mysqli->real_escape_string($_POST['description']);
			$price = $mysqli->real_escape_string($_POST['price']);
			$customer = $mysqli->real_escape_string($_POST['customer']);

			$stmt = $mysqli->prepare("INSERT INTO `expenses` (
															`description` ,
															`price` ,
															`customer`
															)
														VALUES (?, ?, ?)");	

				$stmt->bind_param("sds", $description, $price, $customer);
				$stmt->execute();
				$stmt->close();
				$mysqli->close();

				$index = show("admin/expensesuccess", array("message" => "",
														"redirect" => '<meta http-equiv="refresh" content="3; URL=../login/index.php">'));
			break;
		case 'pay':
			if(!isset($_GET['id'])){
				$title = "JSLAN v6|Mgmt - Oops";
				$index = show("error", array("error" => "Oops! Etwas ging schief!",
								 "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));
				page($index, $title); die();
			}
			if(!is_numeric($_GET['id'])){
				$title = "JSLAN v6|Mgmt - Oops";
				$index = show("error", array("error" => "Oops! Etwas ging schief!",
								 "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));
				page($index, $title); die();
			} else {
				$uid = $_GET['id'];
			}
			//create db connection
			$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
			//print error if something went wrong
			if ($mysqli->connect_errno) {
    			echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}
			$paid=1;
			//update without password
			$stmt = $mysqli->prepare("UPDATE `users` SET `paid` = ? WHERE `ID` = ?");
			$stmt->bind_param("ii", $paid, $uid);
			$stmt->execute();
			$stmt->close();
			$mysqli->close();
			$index = show("login/profileedit", array("message" => "",
													 "redirect" => '<meta http-equiv="refresh" content="0; URL=../admin/index.php?action=show_payments">'));

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