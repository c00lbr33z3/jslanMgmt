<?php
/**
 * NOC for theJSLAN Mgmt Page
 * @package jsmgmt
 * @since 0.1
 * @version 0.1
 * @link http://www.js-forum.ch/jslan/mgmt
 */

//include config files
include_once('../config.php');
include_once('../session.php');
include_once('../template.php');

$title = "JSLAN v6|Mgmt - NOC";

$index = show("noc/index", array("" => ""));

page($index, $title);
?>