<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

/*-------------- includes --------------*/

require_once BASE . 'include/page.php';


/*-------------- database functions --------------*/

function get_user ($uid, $field = '')
{
  if (empty($uid))
    error(_('User not specified'));
  if (!empty($field))
    $q = db_query("SELECT $field FROM users WHERE id='$uid'");
  else
    $q = db_query("SELECT * FROM users WHERE id='$uid'");
  if (!db_rows($q))
    error(_('User not found'));
  if (!empty($field))
    return db_result($q, 0, $field);
  else
    return db_fetch_array($q);
}


/*-------------- convenience functions --------------*/

function user_pending ()
{
  static $pending = -1;

  if ($pending == -1) {
    $pending = 0;
    if (is_administrator() && db_simple_query("SELECT COUNT(email) FROM user_registration WHERE status='w'"))
      $pending = 1;
  }
  return $pending;
}


/*-------------- validation functions --------------*/

function valid_username ($username)
{
  return eregi('^[0-9a-z]([-_.]?[0-9a-z])+$', $username);
}

?>
