<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

/*-------------- includes --------------*/

require_once BASE . 'include/page.php';


/*-------------- database functions --------------*/

function get_topic ($tid, $field = '')
{
  if (empty($tid))
    error(_('Topic not specified'));
  if (!empty($field))
    $q = db_query("SELECT $field FROM topic WHERE id='$tid'");
  else
    $q = db_query("SELECT * FROM topic WHERE id='$tid'");
  if (!db_rows($q))
    error(_('Topic not found'));
  if (!empty($field))
    return db_result($q, 0, $field);
  else
    return db_fetch_array($q);
}


/*-------------- convenience functions --------------*/

function topic_finish_user ($uid)
{
  // move maintained topics to administrator
  db_command("UPDATE topic SET maintainer_id='1' WHERE maintainer_id='$uid'");
}

?>
