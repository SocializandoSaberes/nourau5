<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

/*-------------- includes --------------*/

require_once BASE . 'include/page.php';
require_once BASE . 'include/util.php';
require_once BASE . 'include/util_t.php';


/*-------------- access control functions --------------*/
session_start();
if (!isset($_SESSION['level'])) {
	$_SESSION['level']=0;
}
function is_administrator ()
{
  
  return $_SESSION['level'] == ADM_LEVEL;
}

function is_maintainer ()
{

  return $_SESSION['level'] >= MNT_LEVEL;
}

function is_user ()
{

  return $_SESSION['level'] >= USR_LEVEL;
}

function check_administrator_rights ()
{
  global $cfg_site;
  
  if ($_SESSION['level'] == ADM_LEVEL)
    return;
  if ($_SESSION['level'] == MNT_LEVEL || $_SESSION['level'] == USR_LEVEL)
    error(_('Access denied'));
  redirect("{$cfg_site}user/login.php?url=" . rawurlencode($_SERVER['REQUEST_URI']));
}

function check_maintainer_rights ($tid = '')
{
  global $cfg_site;
  
  if ($_SESSION['level'] == ADM_LEVEL)
    return;
  if ($_SESSION['level'] == MNT_LEVEL) {
    if (empty($tid) || get_topic($tid, 'maintainer_id') == $_SESSION['uid'])
      return;
    else
      error(_('Access denied'));
  }
  if ($_SESSION['level'] == USR_LEVEL)
    error(_('Access denied'));
  redirect("{$cfg_site}user/login.php?url=" . rawurlencode($_SERVER['REQUEST_URI']));
}

function check_user_rights ()
{
  global $cfg_site;
  
  if ($_SESSION['level'] >= USR_LEVEL)
    return;
  redirect("{$cfg_site}user/login.php?url=" . rawurlencode($_SERVER['REQUEST_URI']));
}

?>
