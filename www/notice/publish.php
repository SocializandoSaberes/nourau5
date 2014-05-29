<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

require_once '../include/start.php';
require_once BASE . 'include/format.php';
require_once BASE . 'include/format_n.php';
require_once BASE . 'include/page.php';
require_once BASE . 'include/util.php';

page_begin_aux();
page_menu_begin('n');
page_menu_end();

if (isset($_POST['lim'])) {
	$lim = $_POST['lim'];
}

if (isset($_POST['off'])) {
	$off = $_POST['off'];
}

if (!is_maintainer() && !is_administrator()) {
  // show all notices
  echo html_h(_("Notices"));
  $q = db_query("SELECT subject,notice,posted,user_id FROM Notice ORDER BY posted DESC");
  while ($a = db_fetch_array($q)) {
    format_notice($a['subject'], $a['notice'], $a['user_id'], $a['posted']);
    echo "<br>\n";
  }
}
else {
  // show all notices
  echo html_h(_("Notices"));
  if (empty($lim))
    $lim = 8;
  if (empty($off))
    $off = 0;
  $q = db_query("SELECT id,subject,notice,posted,user_id FROM Notice ORDER BY posted DESC",$lim+1,$off);
  if (db_rows($q)) list_notices($q, $lim, $off);
  echo html_p(format_action(_("Publish notice"), 'post-notice.php'));
}

/*-------------- functions --------------*/

function list_notices ($query, $limit, $offset)
{
  $PHP_SELF = $_SERVER['PHP_SELF'];
  $n = min($limit, db_rows($query));
  while ($n--) {
    $a = db_fetch_array($query);
    echo <<<HTML
<table width="100%" cellpadding="4" cellspacing="0">
<tr>
<td align="left" width="90%" valign="top">
HTML;
    echo format_notice($a['subject'], $a['notice'], $a['user_id'], $a['posted']);
    echo "</td>\n<td align=\"center\" width=\"10%\" valign=\"middle\">\n";
    // if notice can be edited
    if (is_administrator() || $_SESSION['uid'] == $a['user_id'])
	echo '<center>' .
	    format_action(_("Edit"),"post-notice.php?nid={$a['id']}")
	    . '</center>';
    echo <<<HTML
</td>
</tr>
</table>
HTML;
  }
  html_table_begin(false, 'right');
  html_table_row_begin();
  if ($offset > 0)
    $prev = format_action(_("previous"), "$PHP_SELF?lim=$limit&off=" .
                          max(0, $offset-$limit));
  else
    $prev = _("previous");
  if (db_rows($query) > $limit)
    $next = format_action(_("next"), "$PHP_SELF?lim=$limit&off=" .
                          ($offset+$limit));
  else
    $next = _("next");
  html_table_row_item($prev, 'left', '25%');
  html_table_row_item('&nbsp;', 'left', '5%');
  html_table_row_item($next, 'right');
  html_table_row_end();
  html_table_end();
  echo '<p>';
}


?>
