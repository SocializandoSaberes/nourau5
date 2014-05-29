<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

/*-------------- mail functions --------------*/

function send_mail ($to, $subject, $message, $reply = '')
{
  global $cfg_webmaster, $cfg_subject_tag, $cfg_redirect_emails;

  $headers = "From: $cfg_webmaster";
  if (!empty($reply))
    $headers .= "\nReply-To: $reply";
  if (!$cfg_redirect_emails)
    mail($to, "[$cfg_subject_tag] $subject", $message, $headers);
  else
    mail($cfg_webmaster, "[$to] $subject", $message, $headers);
}


/*-------------- logging functions --------------*/

function add_log ($scope, $op, $info = '', $level = '')
{
  

  if (empty($level))
    $level = 'i';
  $uid = ($_SESSION['uid']) ? $_SESSION['uid'] : '0';
  db_command("INSERT INTO log (scope,op,user_id,level,info) VALUES ('$scope','$op','$uid','$level','$info')");
}


/*-------------- validation functions --------------*/

function valid_email ($email)
{
  return eregi('^[0-9a-z]([-_.]?[0-9a-z])*@[0-9a-z]([-.]?[0-9a-z])*\.[a-z]{2,3}$', $email);
}

function valid_int ($int)
{
  return preg_match('/^\d+$/', $int);
}

function valid_opt_int ($int)
{
  return preg_match('/^\d*$/', $int);
}


/*-------------- conversion functions --------------*/

function convert_email ($email)
{
  global $cfg_obfuscate_emails;
  

  if ($cfg_obfuscate_emails && !$_SESSION['level'])
    $email = preg_replace('/@/', ' ' . _('at') . ' ', $email);
  return $email;
}

function convert_line ($str, $size = '')
{
  if (!empty($size) && strlen($str) > $size)
    $str = substr($str, 0, $size) . '...';
  return htmlspecialchars($str);
}

function convert_text ($str)
{
  // convert special HTML characters
  $str = htmlspecialchars($str);

  // insert links for valid URLs (based on code by Tom Christiansen)
  $sch = '(http|ftp)';               // URL schemes
  $any = '\w\-.!~*\'();/?:@&=+$,%#'; // valid characters (RFC 2396)
  $pun = '.!);?,';                   // punctuation that can be at URL end
  $ent = '&(gt|lt|quot);';           // HTML entities to ignore at URL end
  $str = preg_replace("¬\\b($sch://[$any]+?)(?=[$pun]*([^$any]|$ent|$))¬i",
                      '<a href="\1" target="_blank">\1</a>', $str);

  // insert linebreaks
  return nl2br($str);
}


/*-------------- miscellaneous functions --------------*/

function insert_text_file ($file)
{
  global $cfg_language, $cfg_locale_dir;

  if (is_readable("$cfg_locale_dir/$cfg_language/$file"))
    readfile("$cfg_locale_dir/$cfg_language/$file");
  else if (is_readable("$cfg_locale_dir/pt_BR/$file"))
    readfile("$cfg_locale_dir/pt_BR/$file"); // fallback
  echo "\n";
}

function random_string ($size)
{
  $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
  mt_srand((double)microtime()*1000000);
  while ($size--)
    $str .= substr($chars, mt_rand(0, 61), 1);
  return $str;
}

function redirect ($url)
{
  header("Location: $url");
  exit();
}

function rot13 ($str)
{
  return strtr($str,
               'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
               'NOPQRSTUVWXYZABCDEFGHIJKLMnopqrstuvwxyzabcdefghijklm');
}

?>
