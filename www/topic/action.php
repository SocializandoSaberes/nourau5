<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

require_once '../include/start.php';
require_once BASE . 'include/control.php';
require_once BASE . 'include/util.php';
require_once BASE . 'include/util_t.php';

if (!is_administrator())
  error(_('Access denied'));

if (isset($_GET['op'])) {
        $op = $_GET['op'];
}

if (isset($_GET['tid'])) {
        $tid = $_GET['tid'];
}

if (isset($_POST['conf'])) {
        $conf = $_POST['conf'];
}

if ($op == 'd') { // ---------------- remove topic
  // validate input
  if (!valid_int($tid))
    error(_('Invalid parameter'));

  // check if topic is empty
  $doc = db_simple_query("SELECT COUNT(ID) FROM nr_document WHERE topic_id='$tid'");
  $top = db_simple_query("SELECT COUNT(ID) FROM topic WHERE parent_id='$tid'");
  $back = "{$cfg_site}document/list.php?tid=$tid";
  if ($doc + $top)
    message(_('A non-empty topic cannot be removed'), $back);

  // ask confirmation
  if (empty($conf)) {
    $topic = get_topic($tid, 'name');
    $PHP_SELF = $_SERVER['PHP_SELF'];
    confirm(_M("Do you want to remove the topic '@1'?", $topic),
            "$PHP_SELF?op=$op&tid=$tid");
  }

  if ($conf == _('Yes')) {
    // remove topic
    $pid = get_topic($tid, 'parent_id');
    db_command("DELETE FROM topic WHERE id='$tid'");
    db_command("DELETE FROM nr_topic_category WHERE topic_id='$tid'");
    add_log('c', 'td', "tid=$tid");
    message(_('Topic removed'),
            "{$cfg_site}document/list.php" . (($pid) ? "?tid=$pid" : ''));
  }
  else
    redirect($back);
}
else
  error(_('Invalid operation'));

?>
