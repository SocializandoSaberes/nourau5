<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

/*-------------- includes --------------*/

if (defined('TOPLEVEL')) {
      define('BASE', dirname(dirname(__FILE__)) . '/');
//	define('BASE', dirname(dirname($_SERVER['PHP_SELF'])) . '/');
//	print("TOPLEVEL:\n");
}

else {
	define('BASE', dirname(dirname(__FILE__)) . '/');
//        define('BASE', dirname($_SERVER['PHP_SELF']) . '/');
//	print("Nao TOPLEVEL:\n");

}

define('BASE2', dirname(dirname(__FILE__)) . '/');


require_once BASE . 'config.php';
//require_once BASE2 . 'config.php';
require_once BASE . 'include/db.php';
require_once BASE . 'include/defs.php';

/*-------------- startup --------------*/

// set language
putenv("LANG=$cfg_language");
putenv("LC_ALL=$cfg_language");
setlocale(LC_ALL, $cfg_language);
bindtextdomain('nou-rau', $cfg_locale_dir);
textdomain('nou-rau');

// halt system
if (defined('HALT'))
  fatal(_("We are offline for maintenance, we'll be back soon"));

// start database connection
db_connect();

if (!defined('OFFLINE')) {
  // retrieve session (if available)
  if (!empty($_COOKIE[$cfg_session_name])) {
    session_name($cfg_session_name);
    session_start();
  }

  // connection parameters
  ignore_user_abort(true);
  set_time_limit(60);
}
//session_start();
//$session = $_SESSION;

/*-------------- support functions --------------*/

function _M ($msg, $p1 = '', $p2 = '', $p3 = '')
{
  $msg = _($msg);
  if (!empty($p1))
    $msg = str_replace('@1', $p1, $msg);
  if (!empty($p2))
    $msg = str_replace('@2', $p2, $msg);
  if (!empty($p3))
    $msg = str_replace('@3', $p3, $msg);
  return $msg;
}

function fatal ($msg)
{
  echo "$msg\n";
  exit();
}

?>
