<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

require_once '../include/start.php';
require_once BASE . 'include/htdig.php';
require_once BASE . 'include/html.php';
require_once BASE . 'include/page.php';

if (!is_administrator())
  error(_('Access denied'));

if (isset($_GET['op'])) {

	$op = $_GET['op'];
}

if (isset($_POST['conf'])) {

        $conf = $_POST['conf'];
}

if ($op == 's') { // ---------------- show search index statistics
  html_header(_('Search index statistics'));
  show_stats();
  echo html_p(html_a(_('Click here to continue'),
                     "{$cfg_site}document/manage.php"));
  html_footer();
}
else if ($op == 'b') { // ---------------- rebuild search index
  // ask confirmation
  if (empty($conf)) {
    $PHP_SELF = $_SERVER['PHP_SELF']; 
    confirm(_('Do you really want to rebuild the search index? This may take a long time...'), "$PHP_SELF?op=$op");
  }
  if ($conf == _('Yes')) {
    html_header(_('Updating search index'));
    rundig(true);
    echo html_p(html_a(_('Click here to continue'),
                       "{$cfg_site}document/manage.php"));
    html_footer();
  }
  else
    redirect("{$cfg_site}document/manage.php");
}
else if ($op == 'u') { // ---------------- update search index
  html_header(_('Updating search index'));
  rundig();
  echo html_p(html_a(_('Click here to continue'),
                     "{$cfg_site}document/manage.php"));
  html_footer();
}
else
  error(_('Invalid operation'));

?>
