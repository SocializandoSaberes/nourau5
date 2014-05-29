<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

require_once '../include/start.php';
require_once BASE . 'include/control.php';
require_once BASE . 'include/format.php';
require_once BASE . 'include/page.php';
require_once BASE . 'include/util_t.php';

if (isset($_POST['nid'])) {
	$nid = $_POST['nid'];
}

if (isset($_GET['nid'])) {
	$nid = $_GET['nid'];
}

if (isset($_POST['sent'])) {
	$sent = $_POST['sent'];
}

if (isset($_POST['subject'])) {
	$subject = $_POST['subject'];
}

if (isset($_POST['notice'])) {
	$notice = $_POST['notice'];
}

// validate input
if (!empty($nid) && !valid_opt_int($nid))
  error(_('Invalid parameter'));

if (!empty($nid)) {
  // handle edit mode
  if (empty($sent)) {
    // first time; load from base
    load();
    form();
  }
  else if ($sent == _('Cancel')) {
    // abort editing
    redirect("{$cfg_site}notice/publish.php");
  }
}

check_maintainer_rights();

// filter input
if (isset($subject)) {
	$subject = trim($subject);
	$notice = trim($notice);
}
// validate input
if (empty($sent))
  form();
if (empty($subject))
  form(_('Subject not specified'));
if (empty($notice))
  form(_('Notice not specified'));

if (empty($nid)) {
    // insert new notice
    db_command("INSERT INTO Notice (subject,notice,user_id) VALUES ('$subject','$notice',{$_SESSION['uid']})");
    $nid = db_simple_query("SELECT CURRVAL('notice_seq')");
    add_log('c', 'nc', "nid=$nid");
    message(_("Notice posted"), "{$cfg_site}notice/publish.php");
}
else {
    // update notice
    db_command("UPDATE Notice SET subject='$subject',notice='$notice' WHERE id=$nid");
    add_log('c', 'nu', "nid=$nid");
    message(_("Notice updated"), "{$cfg_site}notice/publish.php");
}

/*-------------- functions --------------*/

function form ($msg = "")
{
  global $cfg_site;
  global $nid, $subject, $notice, $user_id;
$PHP_SELF = $_SERVER['PHP_SELF'];
  page_begin_aux();
  page_menu_begin('n');
  page_menu_end();

  echo html_h(_("Publish Notice"));

  format_warning($msg);

  html_form_begin($PHP_SELF);

  if (!empty($nid))
    html_form_hidden('nid', $nid);

  html_form_text(_("Subject"), 'subject', 64, $subject, 64);
  echo "<p>\n";
  html_form_area(_("Notice"), 'notice', 4, $notice);
  echo "<p>\n";

  if (empty($nid))
    html_form_submit(_('Send'), 'sent');
  else {
    html_form_submit(_('Save'), 'sent');
    html_form_submit(_('Cancel'), 'sent');
  }
  html_form_end();
  echo "<p>\n";

  page_end("{$cfg_site}notice/publish.php");
  exit();
}

function load ()
{
  global $nid, $subject, $notice, $user_id;

  $q = db_query("SELECT subject,notice,user_id FROM Notice WHERE id=$nid");
  if (!db_rows($q))
      error(_("Notice not found"));
  $a = db_fetch_array($q);
  $subject = $a['subject'];
  $notice = $a['notice'];
  // ensure proper access
  if ($a['user_id'] != $_SESSION['uid'] && !is_administrator())
      error(_("Access denied"));
}

?>
