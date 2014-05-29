<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

/*-------------- includes --------------*/

require_once BASE . 'include/page.php';


/*-------------- database functions --------------*/

function get_notice ($nid, $field = '')
{
  if (empty($nid))
    error(_('Notice not specified'));
  if (!empty($field))
    $q = db_query("SELECT $field FROM notice WHERE id='$nid'");
  else
    $q = db_query("SELECT * FROM notice WHERE id='$nid'");
  if (!db_rows($q))
    error(_('Notice not found'));
  if (!empty($field))
    return db_result($q, 0, $field);
  else
    return db_fetch_array($q);
}

?>
