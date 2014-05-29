<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

require_once '../include/start.php';
require_once BASE . 'include/control.php';
require_once BASE . 'include/defs_d.php';
require_once BASE . 'include/format.php';
require_once BASE . 'include/page_d.php';
require_once BASE . 'include/util.php';
require_once BASE . 'include/util_d.php';
require_once BASE . 'include/util_t.php';

if (isset($_GET['did'])) {
	$did = $_GET['did'];
}

if (isset($_POST['did'])) {
        $did = $_POST['did'];
}

if (isset($_POST['title'])) {
        $title = $_POST['title'];
}

if (isset($_POST['author'])) {
        $author = $_POST['author'];
}

if (isset($_POST['email'])) {
        $email = $_POST['email'];
}

if (isset($_POST['keywords'])) {
        $keywords = $_POST['keywords'];
}

if (isset($_POST['description'])) {
        $description = $_POST['description'];
}

if (isset($_POST['code'])) {
        $code = $_POST['code'];
}

if (isset($_POST['info'])) {
        $info = $_POST['info'];
	//$info2 = mb_convert_encoding($info,"UTF-8","ISO-8859-1");
	//$info  = $info2;
	//echo mb_detect_encoding($info);
	//print("<BR>");
}

if (isset($_POST['sent'])) {
        $sent = $_POST['sent'];
}

// validate input
if (!valid_int($did))
  error(_('Invalid parameter'));

// check access rights
if (!can_edit_document($did))
  error(_('Access denied'));

$a = get_document($did);
if ($a['status'] != 'i') {
  // handle edit mode
  if (empty($sent)) {
    // first time; load from base
    load();
    form();
  }
  else if ($sent == _('Cancel')) {
    // abort editing
    redirect("{$cfg_site}document/?code=" . rawurlencode($a['code']));
  }
}

if (isset($_POST['title'])) {
        $title = $_POST['title'];
}

if (isset($_POST['author'])) {
        $author = $_POST['author'];
}

if (isset($_POST['email'])) {
        $email = $_POST['email'];
}

if (isset($_POST['keywords'])) {
        $keywords = $_POST['keywords'];
}

if (isset($_POST['description'])) {
        $description = $_POST['description'];
}

if (isset($_POST['code'])) {
        $code = $_POST['code'];
}

if (isset($_POST['info'])) {
        $info = $_POST['info'];
}

if (isset($_POST['filename'])) {
        $filename = $_POST['filename'];
}

if (isset($_POST['sent'])) {
        $sent = $_POST['sent'];
}

// filter input
$title = trim($title);
$author = trim($author);
$email = trim($email);
$keywords = trim($keywords);
$description = trim($description);
$code = trim($code);
$info = trim($info);
$filename = trim($filename);

// validate input
if (empty($sent))
  form();
if (empty($title))
  form(_('Please specify the title'));
if (empty($author))
  form(_('Please specify the name of the author(s)'));
if (!empty($email) && !valid_email_list($email))
  form(_('Invalid e-mail(s): please check'));
if (empty($keywords))
  form(_('Please specify the keywords'));
$n = strlen($keywords) - $cfg_max_document_keywords;
if ($n > 0)
  form(_M('The size of the keywords exceeded the maximum allowed in @1 characters', $n));
$n = strlen($description) - $cfg_max_document_description;
if ($n > 0)
  form(_M('The size of the description exceeded the maximum allowed in @1 characters', $n));
if (empty($code))
  form(_('The document code is needed; please check the default value given'));
$oid = db_simple_query("SELECT id FROM nr_document WHERE code='$code'");
if (!empty($oid) && $oid != $did)
  form(_('There is already another document with the same code'));
$n = strlen($info) - $cfg_max_document_info;
if ($n > 0)
  form(_M('The size of the additional information exceeded the maximum allowed in @1 characters', $n));
if (empty($filename)) {
  if ($a['remote'] == 'n')
    form(_('The file name is needed; please check the default value given'));
  else
    form(_('The address is needed; please check the default value given'));
}

// check if document is being created
$status = $a['status'];
$mid = get_topic($a['topic_id'],'maintainer_id');
if ($status == 'i') {
  // check if it needs to be verified
  if (get_format($a['format_id'], 'verify') == 'y' && $cfg_need_verify) {
    $status = 'v';
    send_mail(get_user(1, 'email'), _('Document to be verified'), _M("The document '@1' in the directory '@2' needs verification.", $filename, $cfg_dir_incoming));
  }
  // check if it needs to be approved
  else if ($cfg_need_approval && $mid!=$a['owner_id'] && $a['owner_id']!=1) {
    $status = 'w';
    $q2 = db_query("SELECT U.email,T.name FROM users U,topic T WHERE U.id=T.maintainer_id AND T.id='{$a['topic_id']}'");
    $a2 = db_fetch_array($q2);
    $topic = $a2['name'];
    send_mail($a2['email'], _('Document received'), _M("The document with title '@1' was received on the topic '@2'.", $title, $topic));
  }
  // archive immediately
  else {
      $status = 'a';
      // move file to archive, renaming it and compressing if necessary
      $q2 = db_query("SELECT extension,compress FROM nr_format WHERE id='{$a['format_id']}'");
      $a2 = db_fetch_array($q2);
      $old = "$cfg_dir_incoming/{$filename}";
      $new = "$cfg_dir_archive/$did.{$a2['extension']}";
      $filename = substr($filename, 5); // remove random prefix
      if (!@rename($old, $new))
	  error(_('Rename failed')); // FIXME: notify admin?
      if ($a2['compress'] == 'y') {
	  // compress it
	  exec("gzip -9 $new");
      }

  }
}

// update document
if ($a['status']=='i' && $status == 'a')
    db_command("UPDATE nr_document SET title='$title',author='$author',email='$email',keywords='$keywords',description='$description',code='$code',info='$info',status='$status',filename='$filename',updated='now', visits='0', downloads='0' WHERE id='$did'");
else
    db_command("UPDATE nr_document SET title='$title',author='$author',email='$email',keywords='$keywords',description='$description',code='$code',info='$info',status='$status',filename='$filename',updated='now' WHERE id='$did'");    

// update document in search index
if ($status == 'a')
  db_command("INSERT INTO nr_document_queue (op,document_id) VALUES ('u','$did')");

// finish
if ($a['status'] == 'i') {
    add_log('n', 'dc', "did=$did");
    message(_('Document received'), "{$cfg_site}document/list.php?tid={$a['topic_id']}");
}
else {
  add_log('n', 'du', "did=$did");
  message(_('Document updated'), "{$cfg_site}document/?code=" . rawurlencode($code));
}


/*-------------- functions --------------*/

function form ($msg = "")
{
  global $cfg_max_document_keywords, $cfg_max_document_description,
    $cfg_max_document_info, $cfg_site;
  global $a, $did, $title, $author, $email, $keywords, $description,
    $code, $info, $filename;

  page_begin('b');

  $topic = get_topic($a['topic_id'], 'name');
  if ($a['status'] == 'i') {
    echo html_h(_('Archive document into:') . ' ' . $topic);
    format_warning($msg);
    $format = '<b>' . get_format($a['format_id'], 'name') . '</b>';
    echo _M("Document accepted with format '@1'.", $format) . "<br>\n";
    echo _('Please fill below all information regarding to the submitted file.');
    echo "<p>\n";
  }
  else {
    echo html_h(_('Edit document of:') . ' ' . $topic);
    format_warning($msg);
  }

  if (empty($code))
    $code = $did;
  if (empty($filename))
    $filename = $a['filename'];
  $PHP_SELF = $_SERVER['PHP_SELF'];
  html_form_begin($PHP_SELF);
  html_form_hidden('did', $did);
  html_form_text(_('Title'), 'title', 80, $title, 250);
  echo "<p>\n";
  html_form_text(_('Author (or authors, separated by comma)'), 'author', 80,
                 $author, 250);
  echo "<p>\n";
  html_form_text(_('E-mail (or e-mails, separated by comma) (optional)'),
                 'email', 80, $email, 150);
  echo "<p>\n";
  html_form_area(_('Keywords (separated by comma)'), 'keywords', 2, $keywords,
                 $cfg_max_document_keywords);
  echo "<p>\n";
  html_form_area(_('Description (optional)'), 'description', 6, $description,
                 $cfg_max_document_description);
  echo "<p>\n";
  html_form_text(_('Code'), 'code', 20, $code, 50);
  echo "<p>\n";
  html_form_area(_('Additional information (optional)'), 'info', 6, $info,
                 $cfg_max_document_info);
  echo "<p>\n";
  if ($a['status'] == 'a') {
    html_form_text(($a['remote'] == 'n') ? _('File name') : _('Address'),
                   'filename', 80, $filename, 150);
    echo "<p>\n";
  }
  else
    html_form_hidden('filename', $filename);

  if ($a['status'] == 'i') {
    echo _('Observe that the archival of your document depends on the approval of this topic maintainer.');
    echo "<p>\n";
    html_form_submit(_('Send'), 'sent');
  }
  else {
    html_form_submit(_('Save'), 'sent');
    html_form_submit(_('Cancel'), 'sent');
  }
  html_form_end();
  echo "<p>\n";

  if ($a['status'] == 'i')
    page_end();
  else
    page_end("{$cfg_site}document/?code=" . rawurlencode($a['code']));
  exit();
}

function load ()
{
  global $a, $title, $author, $email, $keywords, $description, $code, $info,
    $filename;

  $title = $a['title'];
  $author = $a['author'];
  $email = $a['email'];
  $keywords = $a['keywords'];
  $description = $a['description'];
  $code = $a['code'];
  $info = $a['info'];
  $filename = $a['filename'];
}

?>
