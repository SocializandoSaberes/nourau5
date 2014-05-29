<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

require_once '../include/start.php';
require_once BASE . 'include/format.php';
require_once BASE . 'include/page_u.php';
require_once BASE . 'include/util.php';

if ($cfg_reg_mode == 'closed') {
  // user registration is disabled
  redirect($cfg_site);
}

if ($cfg_reg_mode == 'open' && !$cfg_reg_verify_email) {
  // send directly to user creation
  redirect("{$cfg_site}user/edit.php");
}

if (isset($_POST['sent'])) {
	$sent = $_POST['sent'];
}

if (isset($_POST['email'])) {
	$email = $_POST['email'];
}

if (isset($_POST['motive'])) {
	$motive = $_POST['motive'];
}

// filter input
$email = trim($email);
$motive = trim($motive);

// validate input
if (empty($sent))
  form();
if (empty($email))
  form(_('Please specify the e-mail address'));
if (!valid_email($email))
  form(_('Invalid e-mail: please check'));
if ($cfg_reg_mode == 'moderated' && $cfg_reg_ask_motive) {
  if (empty($motive))
    form(_('Please specify the motive for the registration'));
  $n = strlen($motive) - $cfg_max_reg_motive;
  if ($n > 0)
    form(_M('The size of the motive exceeded the maximum allowed in @1 characters', $n));
}
else
  $motive = '';

// check whether already registered
if (db_simple_query("SELECT COUNT(email) FROM user_registration WHERE email='$email'"))
  form(_('Registration request already made for this e-mail'));
if (db_simple_query("SELECT COUNT(id) FROM users WHERE email='$email'"))
  error(_('This e-mail is already in use by a collaborator'));

mt_srand((double)microtime()*1000000);
$code = mt_rand(0, 999999);
if ($cfg_reg_mode == 'moderated') {
  // insert registration request
  db_command("INSERT INTO user_registration (email,code,motive) VALUES ('$email','$code','$motive')");

  // notify administrator
  send_mail(get_user(1, 'email'), _('Registration request'), _M("The user with e-mail '@1' requested to be registered.", $email) . "\n");

  // finish
  message(_('Your request will be evaluated; please wait for an e-mail with the results'), $cfg_site);
}
else {
  // insert registration request
  db_command("INSERT INTO user_registration (email,code,status) VALUES ('$email','$code','a')");

  // send e-mail confirmation
  $url = "{$cfg_site}user/edit.php?code=$code&email=$email";
  send_mail($email, _('Registration confirmation'), _('Confirm your registration by following the link below:') . "\n\n$url\n");

  // finish
  message(_('Wait for an e-mail with instructions on how to proceed with your registration'), $cfg_site);
}


/*-------------- functions --------------*/

function form ($msg = "")
{
  global $cfg_site, $cfg_reg_mode, $cfg_reg_verify_email, $cfg_reg_ask_motive,
    $cfg_max_reg_motive;
  global $email, $motive;
  $PHP_SELF = $_SERVER['PHP_SELF'];
  page_begin();

  echo html_h(_('Registration'));
  format_warning($msg);

  echo _("You only need to register as a user if you plan to upload documents to the system. You can already browse and search documents, just go to the 'Documents' section in the index box on the left.");
  echo "<p>\n";

  html_form_begin($PHP_SELF);
  if ($cfg_reg_verify_email) {
    html_form_text(_('E-mail'), 'email', 50, $email, 50);
    echo "<p>\n";
  }
  if ($cfg_reg_mode == 'moderated' && $cfg_reg_ask_motive) {
    html_form_area(_('Motive for the registration'), 'motive', 3, $motive,
                   $cfg_max_reg_motive);
    echo "<p>\n";
  }
  html_form_submit(_('Send'), 'sent');
  html_form_end();
  echo "<p>\n";

  page_end($cfg_site);
  exit();
}
