<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

require_once '../include/start.php';
require_once BASE . 'include/page_d.php';

if (isset($_GET['type'])) {
	$type = $_GET['type'];
}

if (isset($_GET['N'])) {
        $N = $_GET['type'];
}


// set default values if needed
if (empty($type))
  $type = 'a';
if (empty($n))
  $n = '25';

page_begin('t');

echo html_h(_('Show statistics'));

$type_op = array('a' => _('Most accessed documents'),
                 'v' => _('Most visited documents'),
                 'r' => _('Most recent documents'));
$n_op = array('25'  => '25',
              '50'  => '50',
              '100' => '100',
              '999' => _('all'));
$PHP_SELF = $_SERVER['PHP_SELF'];
html_form_begin($PHP_SELF, false);
html_form_select(_('Type'), 'type', $type_op, $type, false);
html_form_select(_('Number of items'), 'n', $n_op, $n, false);
html_form_submit(_('Show'));
html_form_end();
echo "<p>\n";

// show listing
if ($type == 'a') {
  echo html_h(_('Most accessed documents'));
  $q = db_query("SELECT title,code,downloads AS item FROM nr_document WHERE downloads<>'0' AND status='a' ORDER BY downloads DESC", $n);
}
else if ($type == 'v') {
  echo html_h(_('Most visited documents'));
  $q = db_query("SELECT title,code,visits AS item FROM nr_document WHERE visits<>'0' AND status='a' ORDER BY visits DESC", $n);
}
else {
  echo html_h(_('Most recent documents'));
  $q = db_query("SELECT title,code,updated AS item FROM nr_document WHERE status='a' ORDER BY updated DESC", $n);
}
if (db_rows($q))
  list_documents($type, $q);

page_end();


/*-------------- functions --------------*/

function list_documents ($type, $query)
{
  $PHP_SELF = $_SERVER['PHP_SELF'];

  if ($type == 'a')
    $title = _('Downloads');
  else if ($type == 'v')
    $title = _('Visits');
  else
    $title = _('Last update');

  html_table_begin(false, 'right', true);
  html_table_item(html_b($title), 'title');
  while ($a = db_fetch_array($query)) {
    $item = ($type == 'r') ? db_locale_date($a['item']) : $a['item'];
    html_table_row_begin();
    html_table_row_item($item, '', '20%');
    html_table_row_item(html_a(convert_line($a['title'], 100), "{$cfg_site}../document/?code=" . rawurlencode($a['code'])), 'left', '80%');
    html_table_row_end();
  }
  html_table_end();
}

?>
