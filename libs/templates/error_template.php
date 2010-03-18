<!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
 <head>
  <meta http-equiv="content-type" value="text/html; charset=iso-8859-1"/>
  <title><?=($title_for_page != '') ? $title_for_page . " - " : "" ?>SimpleMVC</title>
  <style type="text/css">
  <!--
  body {
  	font-family: Tahoma, Arial, helvetica, sans-serif;
  	font-size: 12pt;
  }
  h1, h2, h3 {
  	color: #666666;
  	border-bottom: 1px dashed #666666;
  	font-weight: normal;
  }
  div.error {
  	color: #FFFFFF;
  	background-color: #990000;
  	font-size: 14pt;
  	padding: 10px;
  }
  div.fix {
  	color: #FFFFFF;
  	background-color: #009900;
  	font-size: 14pt;
  	padding: 10px;
  }
  -->
  </style>
 </head>
 <body>
  <h1>SimpleMVC Error</h1>
  <p>An error occured when processing this action.</p>
  <div class="error">
   <?=$error_description;?>
  </div>
  <?if(isset($details)):?>
  <h2>Error details</h2>
  <div class="fix">
   <?=$details;?>
  </div>
  <?endif?>
  <?if(isset($error_fix)):?>
  <h2>How to fix this error?</h2>
  <div class="fix">
   <?=$error_fix;?>
  </div>
  <?endif;?>
  <h2>Backtrace</h2>
  <div class="fix">
   <pre><?debug_print_backtrace()?></pre>
  </div>
 </body>
</html>