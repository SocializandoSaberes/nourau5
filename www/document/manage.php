<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

require_once '../include/start.php';
require_once BASE . 'include/control.php';
require_once BASE . 'include/format.php';
require_once BASE . 'include/page_d.php';

check_maintainer_rights();

page_begin('m');

echo html_h(_('Manage documents'));

// show documents waiting for verification
$pending = false;
if (is_administrator()) {
  $q = db_query("SELECT D.title,D.code,D.created,T.id,T.name FROM nr_document D,topic T WHERE (D.status='v' or D.status='u')AND D.topic_id=T.id ORDER BY D.created ASC");
  if (db_rows($q)) {
    list_documents(_('To verify'), $q);
    $pending = true;
  }
}

// show documents waiting for approval
if (is_administrator())
  $q = db_query("SELECT D.title,D.code,D.created,T.id,T.name FROM nr_document D,topic T WHERE (D.status='w' or D.status='p') AND D.topic_id=T.id ORDER BY D.created ASC");
else
  $q = db_query("SELECT D.title,D.code,D.created,T.id,T.name FROM nr_document D,topic T WHERE (D.status='w' or D.status='p') AND D.topic_id=T.id AND T.maintainer_id='{$_SESSION['uid']}' ORDER BY D.created ASC");
  
if (db_rows($q)) {
  list_documents(_('To approve'), $q);
  $pending = true;
}

if (!$pending)
  echo html_p(html_b(_('There are no pending documents')));

if (is_administrator()) {
  $updated = db_simple_query("SELECT updated FROM nr_htdig_status");
  $upd = db_simple_query("SELECT COUNT(DISTINCT document_id) FROM nr_document_queue WHERE op='u'");
  $del = db_simple_query("SELECT COUNT(DISTINCT document_id) FROM nr_document_queue WHERE op='d'");

  format_line(_('Search index last updated'), db_locale_date($updated));
  format_line(_('Documents to update'), $upd);
  format_line(_('Documents to remove'), $del);

  if ($upd + $del)
    echo html_p(format_action(_('Update search index'),
                              "{$cfg_site}document/htdig.php?op=u"));
  echo html_p(format_action(_('Rebuild search index'),
                            "{$cfg_site}document/htdig.php?op=b"));
  echo html_p(format_action(_('Show search index statistics'),
                            "{$cfg_site}document/htdig.php?op=s"));
}

page_end();


/*-------------- functions --------------*/

function list_documents ($title, $query)
{
  global $cfg_site;

  html_table_begin(false, 'right', true);
  html_table_item(html_b($title), 'title');
  while ($a = db_fetch_array($query)) {
    html_table_row_begin();
    html_table_row_item(db_locale_date($a['created']), '', '20%');
    html_table_row_item(html_a(convert_line($a['title'], 65), "{$cfg_site}document/?code=" . rawurlencode($a['code'])), 'left', '50%');
    html_table_row_item(_('in') . '&nbsp;' . html_a(convert_line($a['name'], 25), "{$cfg_site}document/list.php?tid={$a['id']}"), 'left', '30%');
    html_table_row_end();
  }
  html_table_end();
  echo "<p>\n";
}

?>
