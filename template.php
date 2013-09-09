<?php
/**
 * Template System for the Bball HP
 * @package bball
 * @since 0.1
 * @version 0.1
 * @link http://www.opf-basket.ch
 */

//Main Template Function - Searches for [tags] in template files and replaces with code
function show($tpl, $array)
{
    //Get filepath
    $template = "../template/".$tpl;
  
    //open file and read it 
    if($fp = @fopen($template.".".html, "r"))
      $tpl = @fread($fp, filesize($template.".".html));
    
    //replace tags
    foreach($array as $value => $code)
    {
      $tpl = str_replace('['.$value.']', $code, $tpl);
    }

  //return definitive file
  return $tpl;
}

//function for printing out the site. Based on the template function
function page($index,$title)
{
if($_SESSION['login'] == 1){
  $login='<li><a href="../login/index.php">Control Panel</a></li>';
  $logout='<li><a href="../login/index.php?action=logout">Logout</a></li>';
  echo show("index", array("title" => $title,
                   "login" => $login,
                   "logout" => $logout,
                   "index" => $index));    
}else {
  $login='<li><a href="../login/index.php">Login</a></li>';
  echo show("index", array("title" => $title,
                   "login" => $login,
                   "logout" => "",
                   "index" => $index));  
  }
}
?>