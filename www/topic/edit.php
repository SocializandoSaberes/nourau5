<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

require_once '../include/start.php';
require_once BASE . 'include/control.php';
require_once BASE . 'include/format.php';
require_once BASE . 'include/page_d.php';
require_once BASE . 'include/util_t.php';

if (isset($_GET['pid'])) {
	$pid = $_GET['pid'];
}

if (isset($_POST['pid'])) {
        $pid = $_POST['pid'];
}


if (isset($_GET['tid'])) {
	$tid = $_GET['tid'];
}

if (isset($_POST['tid'])) {
        $tid = $_POST['tid'];
}


if (isset($_POST['sent'])) {
	$sent = $_POST['sent'];
}

if (isset($_POST['name'])) {
	$name = $_POST['name'];
}

if (isset($_POST['description'])) {
	$description = $_POST['description'];
}

if (isset($_POST['maintainer'])) {
	$maintainer = $_POST['maintainer'];
}

if (isset($_POST['category'])) {
        $category = $_POST['category'];
}


// validate input
if (!valid_opt_int($pid) || !valid_opt_int($tid))
  error(_('Invalid parameter'));

check_administrator_rights();

if (!empty($tid)) {
  // handle edit mode
  if (empty($sent)) {
    // first time; load from base
    load();
    form();
  }
  else if ($sent == _('Cancel')) {
    // abort editing
    redirect("{$cfg_site}document/list.php?tid=$tid");
  }
}

// filter input
if (isset($name)) {
	$name = trim($name);
	$description = trim($description);
	$maintainer = trim($maintainer);
}
// validate input
if (empty($sent))
  form();
if (empty($name))
  form(_('Please specify the name'));
if (empty($description))
  form(_('Please specify the description'));
if (empty($maintainer))
  form(_('Please specify the maintainer'));
$mid = db_simple_query("SELECT id FROM users WHERE username='$maintainer'");
if (empty($mid)) {
  unset($maintainer);
  form(_('Maintainer not found'));
}

if (empty($tid)) {
  // insert new topic
  if (empty($pid))
    $pid = 0;
  db_command("INSERT INTO topic (name,description,parent_id,maintainer_id) VALUES ('$name','$description','$pid','$mid')");
  $tid = db_simple_query("SELECT CURRVAL('topic_seq')");
  add_log('c', 'tc', "tid=$tid");
//printf("OLA MUNDO!!<BR>");
  // insert categories
  if (count($category)) {
    //print("INSERT INTO nr_topic_category (topic_id,category_id) VALUES ('$tid','$cid')");
    foreach ($category as $cid)
      db_command("INSERT INTO nr_topic_category (topic_id,category_id) VALUES ('$tid','$cid')");
  }
  // promote new maintainer
  db_command("UPDATE users SET level='" . MNT_LEVEL . "' WHERE id='$mid' AND level='" . USR_LEVEL . "'");

  // finish
  message(_('Topic created'),
          "{$cfg_site}document/list.php" . (($pid) ? "?tid=$pid" : ''));
}
else {
  // get old maintainer
  $old = get_topic($tid, 'maintainer_id');

  // update topic info
  db_command("UPDATE topic SET name='$name',description='$description',maintainer_id='$mid' WHERE id='$tid'");
  add_log('c', 'tu', "tid=$tid");

  // update categories
  db_command("DELETE FROM nr_topic_category WHERE topic_id='$tid'");
  if (count($category))
    foreach ($category as $cid)
      db_command("INSERT INTO nr_topic_category (topic_id,category_id) VALUES ('$tid','$cid')");

  // change maintainers if needed
  if ($old != $mid) {
    // demote old maintainer
    db_command("UPDATE users SET level='" . USR_LEVEL . "' WHERE id='$old' AND level='" . MNT_LEVEL . "' AND id NOT IN (SELECT maintainer_id FROM topic)");

    // promote new maintainer
    db_command("UPDATE users SET level='" . MNT_LEVEL . "' WHERE id='$mid' AND level='" . USR_LEVEL . "'");
  }

  // finish
  message(_('Topic updated'), "{$cfg_site}document/list.php?tid=$tid");
}


/*-------------- functions --------------*/

function form ($msg = "")
{
  global $cfg_site;
  global $tid, $pid, $name, $description, $maintainer, $category;
  $PHP_SELF = $_SERVER['PHP_SELF'];
  page_begin('b');

  if (empty($tid))
    echo html_h(_('Create a new topic'));
  else
    echo html_h(_('Edit topic:') . ' '. $name);
  format_warning($msg);

  html_form_begin($PHP_SELF);

  if (empty($tid))
    html_form_hidden('pid', $pid);
  else
    html_form_hidden('tid', $tid);
  html_form_text(_('Name'), 'name', 80, $name, 100);
  echo "<p>\n";
  html_form_text(_('Description'), 'description', 80, $description, 150);
  echo "<p>\n";
  html_form_text(_('Maintainer'), 'maintainer', 10, $maintainer, 10);
  echo "<p>\n";

  // show categories for this topic
  if (empty($category))
    $category = array();
  $q = db_query("SELECT id,name,description FROM nr_category ORDER BY name");
  echo html_b(_('Categories') . ':') . "<br>\n";
  echo "<select name=\"category[]\" multiple size=\"4\">\n";
  while ($a = db_fetch_array($q)) {
    if (in_array($a['id'], $category))
      echo '<option selected ';
    else
      echo '<option ';
    echo "value=\"{$a['id']}\">" . _($a['name']) . ' - ' . _($a['description']) . "</option>\n";
  }
  echo "</select><p>\n";

  if (empty($tid))
    html_form_submit(_('Send'), 'sent');
  else {
    html_form_submit(_('Save'), 'sent');
    html_form_submit(_('Cancel'), 'sent');
  }
  html_form_end();
  echo "<p>\n";

  if (empty($tid))
    page_end("{$cfg_site}document/list.php" . (($pid) ? "?tid=$pid" : ''));
  else
    page_end("{$cfg_site}document/list.php?tid=$tid");
  exit();
}

function load ()
{
  global $tid, $name, $description, $maintainer, $category;

  $a = get_topic($tid);

  $name = $a['name'];
  $description = $a['description'];
  $maintainer = get_user($a['maintainer_id'], 'username');

  $category = array();
  $q = db_query("SELECT category_id FROM nr_topic_category WHERE topic_id='$tid'");
  while ($a = db_fetch_array($q))
    array_push($category, $a['category_id']);
}

?>
