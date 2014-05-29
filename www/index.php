
<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

define('TOPLEVEL', true);
require_once 'include/start.php';
require_once BASE . 'include/format.php';
require_once BASE . 'include/format_n.php';
require_once BASE . 'include/page.php';
require_once BASE . 'include/util.php';
page_begin_aux();
page_menu_begin('m');
page_menu_end();

echo <<<HTML
<table width="100%" border="0" cellpadding="4" cellspacing="0">
<tr><td colspan="2" valign="top">
HTML;

// show introduction
insert_text_file('intro.html');

echo <<<HTML
</td></tr>
<tr><td valign="top" width="25%">
HTML;

// show totals
echo html_h(_('Totals'));
html_table_begin(true);
$n = db_simple_query("SELECT COUNT(id) FROM nr_document WHERE status='a'");
html_table_item(html_b(_('Documents:')) . " $n", 'odd', 'left');
$n = db_simple_query("SELECT SUM(size) FROM nr_document WHERE status='a'");
html_table_item(html_b(_('Total size:')) . ' ' . int_to_size($n, true), 'odd', 'left');
html_table_end();

// show latest notices
echo html_h(_('Notices'));
$q = db_query("SELECT subject,notice,user_id,posted FROM notice ORDER BY posted DESC", 3);
while ($a = db_fetch_array($q)) {
  format_notice($a['subject'], $a['notice'], $a['user_id'], $a['posted']);
  echo "<p>\n";
}
$archive = html_a(_('archive'), "{$cfg_site}notice/publish.php");
echo html_small(_M('Read the notice @1', $archive));

echo <<<HTML
</td>
<td valign="top" width="75%">
HTML;

// show main topics
echo html_h(_('Main topics'));
$q = db_query("SELECT id,name,description FROM topic WHERE parent_id='0' ORDER BY name");
if (db_rows($q))
  list_topics(_('Topics'), $q);

// show most accessed documents
echo html_h(_('Most accessed documents'));
$q = db_query("SELECT title,code,downloads FROM nr_document WHERE downloads<>'0' AND status='a' ORDER BY downloads DESC", 10);
if (db_rows($q))
  list_documents(_('Downloads'), $q);

echo <<<HTML
</td></tr></table>
HTML;

page_end();


/*-------------- functions --------------*/

function list_documents ($title, $query)
{
  global $cfg_site;

  html_table_begin(false, 'right', true);
  html_table_item(html_b($title), 'title');
  while ($a = db_fetch_array($query)) {
    html_table_row_begin();
    html_table_row_item($a['downloads'], '', '30%');
    html_table_row_item(html_a(convert_line($a['title'], 65), "{$cfg_site}document/?code=" . rawurlencode($a['code'])), 'left', '70%');
    html_table_row_end();
  }
  html_table_item(html_a(html_small(_('see more')),
                         "{$cfg_site}document/stats.php"), 'base');
  html_table_end();
}

function list_topics ($title, $query)
{
  global $cfg_site;

  html_table_begin(false, 'right', true);
  html_table_item(html_b($title), 'title');
  while ($a = db_fetch_array($query)) {
    html_table_row_begin();
    html_table_row_item(html_a(convert_line($a['name']), "{$cfg_site}document/list.php?tid={$a['id']}"), '', '30%');
    html_table_row_item(convert_line($a['description']), 'left', '70%');
    html_table_row_end();
  }
  html_table_end();
}

?>
