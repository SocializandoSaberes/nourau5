<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

require_once '../include/start.php';
require_once BASE . 'include/page_u.php';
require_once BASE . 'include/format.php';
require_once BASE . 'include/util.php';

//RAFAEL - INICIO
if (isset($_POST['sent'])) {
	$username = $_POST['username'];
	$password = $_POST['password'];
	$sent = $_POST['sent'];
	$username = strtolower(trim($username));

}

//RAFAEL - FIM

// filter input
// validate input
if (empty($sent))
  form();
if (empty($username))
  form(_('Please specify an identification'));
if (empty($password))
  form(_('Please specify a password'));
// check username and password
$q = db_query("SELECT id,password,level FROM users WHERE username='$username'");
if (!db_rows($q))
  form(_('Invalid identification or password'));
$a = db_fetch_array($q);
if (rot13($password) != $a['password'])
  form(_('Invalid identification or password'));
// initialize session
session_name($cfg_session_name);
//session_register('session');
$_SESSION['uid'] = $a['id'];
$_SESSION['username'] = $username;
$_SESSION['level'] = $a['level'];

// update last access
db_command("UPDATE users SET accessed='now' WHERE id='{$_SESSION['uid']}'");
add_log('c', 'ul');

// finish
if (empty($url))
  redirect($cfg_site);
else
  redirect($url);


/*-------------- functions --------------*/

function form ($msg = "")
{
  global $cfg_site;
  global $username, $url;
  $PHP_SELF = $_SERVER['PHP_SELF'];
  page_begin();

  echo html_h(_('Login'));
  format_warning($msg);

  echo _('Enter below your identification and password to log into the system. If you have forgotten your password, please follow the link on the bottom of this page.');
  echo "<p>\n";
  html_form_begin($PHP_SELF);
  html_form_text(_('Identification'), 'username', 10, $username, 10);
  echo "<p>\n";
  html_form_password(_('Password'), 'password', 10, '', 10);
  echo "<p>\n";
  html_form_hidden('url', $url);
  html_form_submit(_('Send'), 'sent');
  html_form_end();
  echo "<p>\n";

  echo html_p(format_action(_('Remind forgotten identification or password'),
                            "{$cfg_site}user/remind.php"));

  if (empty($url))
    page_end($cfg_site);
  else
    page_end($url);
  exit();
}

?>
