<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

require_once '../include/start.php';
require_once BASE . 'include/defs_d.php';
require_once BASE . 'include/util_d.php';

$PHP_SELF = $_SERVER['PHP_SELF'];
if (isset($_GET['did'])) {
	$did = $_GET['did'];
}

if (isset($_GET['op'])) {
        $op = $_GET['op'];
}

if (isset($_GET['tid'])) {
        $tid = $_GET['tid'];
}

if (isset($_POST['conf'])) {
        $conf = $_POST['conf'];
}

if (isset($_POST['notify'])) {
        $notify = $_POST[''];
}

// validate input
if (!valid_int($did))
  error(_('Invalid parameter'));
$a = get_document($did);

if ($op == 'v') { // ---------------- accept document after verification
  // validate access
  check_administrator_rights();
  if ($a['status'] != 'v')
    error(_('Access denied'));
  $mid = get_topic($a['topic_id'],'maintainer_id');
  if ($cfg_need_approval && $mid!=$a['owner_id'] && $a['owner_id']!=1) {
      // update document
      db_command("UPDATE nr_document SET status='w' WHERE id='$did'");
      add_log('n', 'dv', "did=$did");
      
      // notify maintainer
      $title = $a['title'];
      $topic = get_topic($a['topic_id'], 'name');
      $email = get_user($a['owner_id'], 'email');
      send_mail($email, _('Document received'), _M("The document with title '@1' was received on the topic '@2'.", $title, $topic));
  }
  else {
      // move file to archive, renaming it and compressing if necessary
      $q2 = db_query("SELECT extension,compress FROM nr_format WHERE id='{$a['format_id']}'");
      $a2 = db_fetch_array($q2);
      $old = "$cfg_dir_incoming/{$a['filename']}";
      $new = "$cfg_dir_archive/$did.{$a2['extension']}";
      $filename = substr($a['filename'], 5); // remove random prefix
      if (!@rename($old, $new))
	  error(_('Rename failed')); // FIXME: notify admin?
      if ($a2['compress'] == 'y') {
	  // compress it
	  exec("gzip -9 $new");
      }
      
      // update document
      db_command("UPDATE nr_document SET status='a',filename='$filename',visits='0',downloads='0' WHERE id='$did'");
      add_log('n', 'dv', "did=$did");
      
      // add document to search index
      db_command("INSERT INTO nr_document_queue (op,document_id) VALUES ('u','$did')");
      
      // notify owner
      $title = $a['title'];
      $topic = get_topic($a['topic_id'], 'name');
      $email = get_user($a['owner_id'], 'email');
      send_mail($email, _('Document accepted'), _M("The document with title '@1' has been accepted in the topic '@2'.", $title, $topic));
  }  
  // finish
  message(_('Document accepted'), "{$cfg_site}document/manage.php");
}
else if ($op == 'a') { // ---------------- approve document
  // validate access
  check_maintainer_rights($tid);
  if ($a['status'] != 'w')
    error(_('Access denied'));

  // move file to archive, renaming it and compressing if necessary
  $q2 = db_query("SELECT extension,compress FROM nr_format WHERE id='{$a['format_id']}'");
  $a2 = db_fetch_array($q2);
  $old = "$cfg_dir_incoming/{$a['filename']}";
  $new = "$cfg_dir_archive/$did.{$a2['extension']}";
  $filename = substr($a['filename'], 5); // remove random prefix
  if (!@rename($old, $new))
    error(_('Rename failed')); // FIXME: notify admin?
  if ($a2['compress'] == 'y') {
    // compress it
    exec("gzip -9 $new");
  }

  // update document
  db_command("UPDATE nr_document SET status='a',filename='$filename',visits='0',downloads='0' WHERE id='$did'");
  add_log('n', 'da', "did=$did");

  // add document to search index
  db_command("INSERT INTO nr_document_queue (op,document_id) VALUES ('u','$did')");

  // notify owner
  $title = $a['title'];
  $topic = get_topic($a['topic_id'], 'name');
  $email = get_user($a['owner_id'], 'email');
  send_mail($email, _('Document accepted'), _M("The document with title '@1' has been accepted in the topic '@2'.", $title, $topic));

  // finish
  message(_('Document approved'), "{$cfg_site}document/manage.php");

}else if ($op == 'u') { // ---------------- approve new document
// validate access
  check_maintainer_rights($tid);
  if (empty($a['new_filename']))
    error(_('Access denied'));

  // move file to archive, renaming it and compressing if necessary
  $q2 = db_query("SELECT extension,compress FROM nr_format WHERE id='{$a['format_id']}'");
  $a2 = db_fetch_array($q2);
  //get the data about new document
  $q3 = db_query("SELECT extension,compress FROM nr_format WHERE id='{$a['new_format_id']}'");
  $a3 = db_fetch_array($q3);
  //delete old document
  deleteOldDoc("$cfg_dir_archive/$did.{$a2['extension']}");  
  $old = "$cfg_dir_incoming/{$a['new_filename']}";
  $new = "$cfg_dir_archive/$did.{$a3['extension']}";
  $filename = substr($a['new_filename'], 5); // remove random prefix
  if (!@rename($old, $new))
    error(_('Rename failed')); // FIXME: notify admin?
  if ($a2['compress'] == 'y') {
    // compress it
    exec("gzip -9 $new");
  }

  // update document
  db_command("UPDATE nr_document SET status='a',filename='$filename',visits='0',downloads='0', new_filename='', size=new_size, format_id=new_format_id, updated='now' WHERE id='$did'");
  add_log('n', 'da', "did=$did");

  // add document to search index
  db_command("INSERT INTO nr_document_queue (op,document_id) VALUES ('u','$did')");

  // notify owner
  $title = $a['title'];
  $topic = get_topic($a['topic_id'], 'name');
  $email = get_user($a['owner_id'], 'email');
  send_mail($email, _('Document accepted'), _M("The document with title '@1' has been accepted in the topic '@2'.", $title, $topic));

  // finish
  message(_('Document approved'), "{$cfg_site}document/manage.php");
}else if ($op == 'f') { // ---------------- reject new document
  // validate access
  check_maintainer_rights($tid);
  if (empty($a['new_filename']))
    error(_('Access denied'));
    
  // ask confirmation 
  if (empty($conf)) {
    $title = $a['title'];
    remove(_M("Do you want to reject the document with title '@1'?", $title), "$PHP_SELF?did=$did&op=$op", false);
  }

  if ($conf == _('Yes')) {
    // remove file and document entry
    unlink("$cfg_dir_incoming/{$a['new_filename']}");
    db_command("update nr_document set  new_filename='' WHERE id='$did'");
    add_log('n', 'dr', "did=$did");

    // notify owner
    $title = $a['title'];
    $topic = get_topic($a['topic_id'], 'name');
    $msg = _M("The document sent to the topic '@1' with title '@2' was rejected by the maintainer.", $topic, $title) . "\n";
    if (!empty($reason))
      $msg .= _('The reason given was:') . "\n\n$reason\n";
    $email = get_user($a['owner_id'], 'email');
    send_mail($email, _('Document rejected'), $msg);

    // finish
    message(_('Document rejected'), "{$cfg_site}document/manage.php");
  }
  else
    redirect("{$cfg_site}document/manage.php");

}
else if ($op == 'r') { // ---------------- reject document
  // validate access
  check_maintainer_rights($tid);
  if ($a['status'] != 'v' && $a['status'] != 'w')
    error(_('Access denied'));

  // ask confirmation
  if (empty($conf)) {
    $title = $a['title'];
    $PHP_SELF = $_SERVER['PHP_SELF'];
    remove(_M("Do you want to reject the document with title '@1'?", $title), "$PHP_SELF?did=$did&op=$op", false);
  }

  if ($conf == _('Yes')) {
    // remove file and document entry
    @unlink("$cfg_dir_incoming/{$a['filename']}");
    db_command("DELETE FROM nr_document WHERE id='$did'");
    add_log('n', 'dr', "did=$did");

    // notify owner
    $title = $a['title'];
    $topic = get_topic($a['topic_id'], 'name');
    $msg = _M("The document sent to the topic '@1' with title '@2' was rejected by the maintainer.", $topic, $title) . "\n";
    if (!empty($reason))
      $msg .= _('The reason given was:') . "\n\n$reason\n";
    $email = get_user($a['owner_id'], 'email');
    send_mail($email, _('Document rejected'), $msg);

    // finish
    message(_('Document rejected'), "{$cfg_site}document/manage.php");
  }
  else
    redirect("{$cfg_site}document/manage.php");
}
else if ($op == 'd') { // ---------------- remove document
  // validate access
  if ($a['status'] != 'a' || !can_edit_document($did))
    error(_('Access denied'));

  // ask confirmation
  if (empty($conf)) {
    $title = $a['title'];
    $PHP_SELF = $_SERVER['PHP_SELF'];
    remove(_M("Do you want to remove the document with title '@1'?", $title), "$PHP_SELF?did=$did&op=$op", true);
  }

  if ($conf == _('Yes')) {
    // remove file and document entry
    $q2 = db_query("SELECT extension,compress FROM nr_format WHERE id='{$a['format_id']}'");
    $a2 = db_fetch_array($q2);
    $file = "$cfg_dir_archive/$did.{$a2['extension']}";
    if ($a2['compress'] == 'y')
      $file .= '.gz';
    @unlink($file);
    db_command("UPDATE nr_document SET status='d' WHERE id='$did'");
    add_log('n', 'dd', "did=$did");

    // remove document from search index
    db_command("INSERT INTO nr_document_queue (op,document_id) VALUES ('d','$did')");

    if ($notify == 'y') {
      // send e-mail notification
      $title = $a['title'];
      $topic = get_topic($a['topic_id'], 'name');
      $msg = _M("The document with title '@1' was removed from the topic '@2'.", $title, $topic) . "\n";
      if (!empty($reason))
        $msg .= _('The reason given was:') . "\n\n$reason\n";
      $email = get_user($a['owner_id'], 'email');
      send_mail($email, _('Document removed'), $msg);
    }

    // finish
    message(_('Document removed'), "{$cfg_site}document/list.php?tid={$a['topic_id']}");
  }
  else
    redirect("{$cfg_site}document/?code=" . rawurlencode($a['code']));
}
else {
  print("Op: " . $op . "<BR>");
  error(_('Invalid operation'));
}

/*-------------- functions --------------*/
function deleteOldDoc($caminhoDoc){
	global $cfg_site;	
	if(!empty($caminhoDoc)){
		unlink("$caminhoDoc");
	}
}

?>
