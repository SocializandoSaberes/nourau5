<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

require_once '../include/start.php';
require_once BASE . 'include/page.php';
require_once BASE . 'include/util.php';

// ensure that client is logged
if (!is_user())
  error(_('Access denied'));
$PHP_SELF = $_SERVER['PHP_SELF'];
// finish session
if (isset($_POST['conf'])) {
	$conf = $_POST['conf'];
}
if (empty($conf))
  confirm(_('Do you want to leave the system?'), $PHP_SELF);
if ($conf == _('Yes'))
  session_destroy();
redirect($cfg_site);

?>
