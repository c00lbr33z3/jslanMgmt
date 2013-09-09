<?php
/**
 * Config File JSLAN Mgmt Page
 * @package jsmgmt
 * @since 0.1
 * @version 0.1
 * @link http://www.js-forum.ch/jslan/mgmt
 */
/* MySQL Config */
define("DB_SERVER", "localhost"); //Servername required for database access.
define("DB_USER", "root"); //Username required for database access.
define("DB_PASSWORD", ""); //Password required for database access. 
define("DB_DATABASE", "jslanMgmt"); //Database name required for database access. 

/* Mail Config */
define("MAIL_FROM", "test@test.de"); //used as from and reply to address

/* Session Config */
define("MAX_TIME", 600); //max Login Time - Default 10 Min
?>