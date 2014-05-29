<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

require_once '../include/start.php';
require_once BASE . 'include/page_u.php';
require_once BASE . 'include/format.php';

if (isset($_POST['uid'])) {
	$uid = $_POST['uid'];
}

if (isset($_GET['uid'])) {
	$uid = $_GET['uid'];
}

if (isset($_POST['sent'])) {
	$sent = $_POST['sent'];
}

if (isset($_POST['old'])) {
	$old = $_POST['old'];
}

if (isset($_POST['new'])) {
	$new = $_POST['new'];
}

if (isset($_POST['new2'])) {
	$new2 = $_POST['new2'];
}

// validate input
if (!valid_int($uid))
  error(_('Invalid parameter'));

// validate access
if ($_SESSION['uid'] != $uid && !is_administrator())
  error(_('Access denied'));

// validate input
if (empty($sent))
  form();
if ($_SESSION['uid'] == $uid && empty($old))
  form(_('Please specify your current password'));
if (empty($new) || empty($new2))
  form(_('Please specify a new password and its confirmation'));
if ($new != $new2) {
  unset($new);
  unset($new2);
  form(_('Confirmation password does not match'));
}

// check old password
if ($_SESSION['uid'] == $uid && rot13($old) != get_user($uid, 'password'))
  form(_('Invalid password'));

// change password
db_command("UPDATE users SET password='" . rot13($new) . "' WHERE id='$uid'");
add_log('c', 'up', ($_SESSION['uid'] == $uid) ? '' : "uid=$uid");

// finish
message(_('Password changed'), "{$cfg_site}user/?uid=$uid");


/*-------------- functions --------------*/

function form ($msg = "")
{
  global $cfg_site;
  global $uid, $old, $new, $new2;
  $PHP_SELF = $_SERVER['PHP_SELF'];
  page_begin();

  echo html_h(_('Change password'));
  format_warning($msg);

  html_form_begin($PHP_SELF);
  html_form_hidden('uid', $uid);
  html_form_password(_('Current password'), 'old', 10, $old, 10);
  echo "<p>\n";
  html_form_password(_('New password'), 'new', 10, $new, 10);
  echo "<p>\n";
  html_form_password(_('Confirm the new password'), 'new2', 10, $new2, 10);
  echo "<p>\n";
  html_form_submit(_('Send'), 'sent');
  html_form_end();
  echo "<p>\n";

  page_end("{$cfg_site}user/?uid=$uid");
  exit();
}

?>
