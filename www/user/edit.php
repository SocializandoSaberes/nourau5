<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

require_once '../include/start.php';
require_once BASE . 'include/format.php';
require_once BASE . 'include/page_u.php';
require_once BASE . 'include/util_u.php';

if (isset($_POST['code'])) {
	$code = $_POST['code'];
}

if (isset($_POST['uid'])) {
	$uid = $_POST['uid'];
}

if (isset($_GET['uid'])) {
	$uid = $_GET['uid'];
}

if (isset($_POST['sent'])) {
	$sent = $_POST['sent'];
}

if (isset($_POST['username'])) {
	$username = $_POST['username'];
}

if (isset($_POST['password'])) {
	$password = $_POST['password'];
}

if (isset($_POST['password2'])) {
	$password2 = $_POST['password2'];
}

if (isset($_POST['name'])) {
	$name = $_POST['name'];
}

if (isset($_POST['email'])) {
	$email = $_POST['email'];
}

if (isset($_POST['info'])) {
	$info = $_POST['info'];
}

if (isset($code)) {
// validate input
	if (!valid_opt_int($code) || !valid_opt_int($uid))
	  error(_('Invalid parameter'));

}
if (!empty($code)) {
  // handle new user mode
  if ($code != db_simple_query("SELECT code FROM user_registration WHERE email='$email'"))
    error(_('Invalid code'));
}
else if (!empty($uid)) {
  // handle edit mode
  check_user_rights();
  if ($_SESSION['uid'] != $uid && !is_administrator())
    error(_('Access denied'));
  if (empty($sent)) {
    // first time; load from base
    load();
    form();
  }
  else if ($sent == _('Cancel')) {
    // abort editing
    redirect("{$cfg_site}user/?uid=$uid");
  }
}
else if (!is_administrator())
  error(_('Access denied'));

// filter input
if (isset($username)) {
	$username = strtolower(trim($username));
	$name = trim($name);
	$email = trim($email);
	$info = trim($info);
	
}
// validate input
if (empty($sent))
  form();
if (empty($uid)) {
  if (empty($username))
    form(_('Please specify an identification'));
  if (!valid_username($username))
    form(_('Invalid identification: only use the indicated characters'));
  if (db_simple_query("SELECT COUNT(id) FROM users WHERE username='$username'"))
    form(_('There is already a collaborator with the same identification'));
  if (empty($password) || empty($password2))
    form(_('Please specify a password and its confirmation'));
  if ($password != $password2) {
    unset($password);
    unset($password2);
    form(_('Confirmation password does not match'));
  }
}
if (empty($name))
  form(_('Please specify the full name'));
if (empty($email))
  form(_('Please specify the e-mail address'));
if (!valid_email($email))
  form(_('Invalid e-mail: please check'));
$n = strlen($info) - $cfg_max_user_info;
if ($n > 0)
  form(_M('The size of the information exceeded the maximum allowed in @1 characters', $n));

if (empty($uid)) {
  // insert new user
  db_command("INSERT INTO users (username,password,name,email,info) VALUES ('$username','" . rot13($password) . "','$name','$email','$info')");
  $uid = db_simple_query("SELECT CURRVAL('users_seq')");
  add_log('c', 'uc', "uid=$uid&from=$REMOTE_ADDR $HTTP_USER_AGENT");

  if (is_administrator())
    message(_('User created'), "{$cfg_site}user/list.php");
  else {
    // remove registration request
    db_command("DELETE FROM user_registration WHERE email='$email'");

    // FIXME: notify admin?

    // finish
    message(_('You have been successfully registered'),
            "{$cfg_site}user/login.php?username=$username&password=$password&sent=y");
  }
}
else {
  // update user info
  db_command("UPDATE users SET name='$name',email='$email',info='$info' WHERE id='$uid'");
  add_log('c', 'uu');

  // finish
  message(_('User profile updated'), "{$cfg_site}user/?uid=$uid");
}


/*-------------- functions --------------*/

function form ($msg = "")
{
  global $cfg_max_user_info, $cfg_site;
  global $code, $uid, $username, $password, $password2, $name,
    $email, $info;
  $PHP_SELF = $_SERVER['PHP_SELF'];
  page_begin();

  if (empty($uid))
    echo html_h(_('New collaborator registration'));
  else
    echo html_h(_('Edit user:') . ' '. $username);
  format_warning($msg);

  html_form_begin($PHP_SELF);

  if (empty($uid)) {
    html_form_hidden('code', $code);
    html_form_text(_("Identification (use only letters and numbers, using '-', '_' or '.' as separation marks if needed)"), 'username', 10, $username, 10);
    echo "<p>\n";
    html_form_password(_('Password'), 'password', 10, $password, 10);
    echo "<p>\n";
    html_form_password(_('Confirm the password'), 'password2', 10, $password2,
                       10);
    echo "<p>\n";
  }
  else {
    html_form_hidden('uid', $uid);
    html_form_hidden('username', $username);
  }
  html_form_text(_('Full name'), 'name', 80, $name, 100);
  echo "<p>\n";
  html_form_text(_('E-mail'), 'email', 50, $email, 50);
  echo "<p>\n";
  html_form_area(_('Personal information (optional)'), 'info', 4, $info,
                 $cfg_max_user_info);
  echo "<p>\n";

  if (empty($uid))
    html_form_submit(_('Send'), 'sent');
  else {
    html_form_submit(_('Save'), 'sent');
    html_form_submit(_('Cancel'), 'sent');
  }
  html_form_end();
  echo "<p>\n";

  if (empty($uid)) {
    if (is_administrator())
      page_end("{$cfg_site}user/list.php");
    else
      page_end($cfg_site);
  }
  else
    page_end("{$cfg_site}user/?uid=$uid");
  exit();
}

function load ()
{
  global $uid, $username, $name, $email, $info;

  $a = get_user($uid);

  $username = $a['username'];
  $name = $a['name'];
  $email = $a['email'];
  $info = $a['info'];
}

?>
