<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

define('OFFLINE', true);

require_once '../include/start.php';
require_once BASE . 'include/defs_d.php';
require_once BASE . 'include/util.php';

// remove incoming documents older than 2 hours
$q = db_query("SELECT id,filename FROM nr_document WHERE status='i' AND AGE('now',created)>'2 hours'");
if (db_rows($q)) {
  db_command("DELETE FROM nr_document WHERE status='i' AND AGE('now',created)>'2 hours'");
  while ($a = db_fetch_array($q)) {
    @unlink("$cfg_dir_incoming/{$a['filename']}");
    add_log('n', 'de', "did={$a['id']}");
  }
}

?>
