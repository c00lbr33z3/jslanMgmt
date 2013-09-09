<?php
/**
 * Session Mgmt for the JSLAN Mgmt Page
 * @package jsmgmt
 * @since 0.1
 * @version 0.1
 * @link http://www.js-forum.ch/jslan/mgmt
 */

//start session 
session_start();

function pwdPolicy($pwd){
     // Load password policy class library
    // Use require to halt execution if loading fails
    require_once('../password-policy.php');
    
    // Array defining rules
    $rules['min_length'] = 8;
    $rules['max_length'] = 60;
    
    // Create password policy object
    // Pass rule array in constructor
    $policy = new PasswordPolicy($rules);
    
    // Rules defined on object
    $policy->min_lowercase_chars = 1;
    $policy->min_numeric_chars = 1;
    
    // Validate submitted password

    if( $policy->validate($pwd) ){
    	return true;
    }else {
    	return false;
    }

}

function filtervar($var){
	if (!preg_match('/^[a-zA-Z0-9!@.*_]{1,60}$/', $var)){
		$title = "JSLAN v6|Mgmt - Opps";
		$index = show("error", array("error" => "Oops! Etwas ging schief!",
									 "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));
		page($index, $title); die();	
		}
	return $var;
}
function printfielderror(){
	$title = "JSLAN v6|Mgmt - Opps";
	$index = show("error", array("error" => "Du musst alle Felder ausfüllen!",
								 "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));
	page($index, $title); die();
}
//some basic functions
//check entitlement
function checkEntitlement($admin){
//if $admin = 1 -> admin area $admin = 0 -> normal area
//return 0 = access granted; return 1 = access denied
	if(!isset($_SESSION['admin'])){
		return 1;  //access denied due to no login
	}
	if($admin==1){
	//check admin area
		if($_SESSION['admin']==1){
			return 0; //access granted
		} else {
			return 1; //access denied
		}
	}else {
	//check normal area
		if($_SESSION['admin']==0){
			return 0; //access granted
		} else if ($_SESSION['admin']==1){
			return 0; //also admin has access to user area
		}
		else {
			return 1; //access denied
		}	
	}
}

//set initial session variable (only if not allready logged in)
if(!isset($_SESSION['login'])){
	$_SESSION['login'] = 0;
}
if(isset($_SESSION['sesstime'])){
	//check if user is logged in for too long. 
	if(time() >= ($_SESSION['sesstime']+MAX_TIME)){
	//do logout process
	session_destroy();
	include_once('../template.php');
	$title = "JSLAN v6|Mgmt - Session timed out";
	$index = show("login/logoutok", array("message" => "Deine Session ist <br /><br />abgelaufen!",
										  "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));
	page($index, $title);
	}
}
if(isset($_SESSION['ip'])){
	//check if user still has the same ip. 
	if($_SESSION['ip'] != $_SERVER['REMOTE_ADDR']){
	//do logout process
	session_destroy();
	include_once('../template.php');
	$title = "JSLAN v6|Mgmt - Session timed out";
	$index = show("login/logoutok", array("message" => "Session Hijacking<br /><br />attempt!",
										  "redirect" => "Du wirst in 3 Sekunden automatisch weitergeleitet."));
	page($index, $title);
	}
}
?>