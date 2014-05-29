<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

require_once '../include/start.php';
require_once BASE . 'include/format.php';
require_once BASE . 'include/page_u.php';

if (isset($_POST['sent'])) {
	$sent = $_POST['sent'];
}


if (isset($_POST['username'])) {
	$username = $_POST['username'];
}


if (isset($_POST['email'])) {
	$email = $_POST['email'];
}

// filter input
$username = trim($username);
$email = trim($email);

// validate input
if (empty($sent))
  form();
if (empty($username) && empty($email))
  form(_('Please specify either an identification or an e-mail'));
if (!empty($email) && !valid_email($email))
  form(_('Invalid e-mail: please check'));

// send username and password through e-mail
if (!empty($username)) {
  $q = db_query("SELECT id,username,password,email FROM Users WHERE username='$username'");
  if (!db_rows($q))
    form(_('Identification not found'));
}
else {
  $q = db_query("SELECT id,username,password,email FROM Users WHERE email='$email'");
  if (!db_rows($q))
    form(_('E-mail not found'));
}
$a = db_fetch_array($q);
$uid = $a['id'];
$username = $a['username'];
$password = rot13($a['password']);
send_mail($a['email'], _('Registration data'), _M("You are registered as '@1' with password '@2'.", $username, $password) . "\n");
add_log('c', 'um', "uid=$uid");

// finish
message(_('Password reminder sent'), "{$cfg_site}user/login.php");


/*-------------- functions --------------*/

function form ($msg = "")
{
  global $cfg_site;
  global $username, $email;
  $PHP_SELF = $_SERVER['PHP_SELF'];
  page_begin();

  echo html_h(_('Remind password'));
  format_warning($msg);

  echo _('You only have to fill one of the fields below. After sending this request you will receive an e-mail with your identification and password.');
  echo "<p>\n";

  html_form_begin($PHP_SELF);
  html_form_text(_('Identification'), 'username', 10, $username, 10);
  echo "<p>\n";
  html_form_text(_('E-mail'), 'email', 50, $email, 50);
  echo "<p>\n";

  html_form_submit(_('Send'), 'sent');
  html_form_end();
  echo "<p>\n";

  page_end("{$cfg_site}user/login.php");
  exit();
}

?>
