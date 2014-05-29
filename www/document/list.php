<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

require_once '../include/start.php';
require_once BASE . 'include/defs_d.php';
require_once BASE . 'include/format.php';
require_once BASE . 'include/format_d.php';
require_once BASE . 'include/format_t.php';
require_once BASE . 'include/page_d.php';
require_once BASE . 'include/util_d.php';

if (isset($_GET['tid'])) {
	$tid = $_GET['tid'];
}

if (isset($_GET['page'])) {
        $page = $_GET['page'];
}

if (isset($_GET['desc'])) {
        $desc = $_GET['desc'];
}

if (isset($_GET['sort'])) {
        $sort = $_GET['sort'];
}


$desc='y';
// validate input
if (!valid_opt_int($page) || !valid_opt_int($tid))
  error(_('Invalid parameter'));
if ($page < 1)
  $page = 1;

// start page
page_begin('b');
if (empty($tid))
  $topic = _('all main topics');
else {
  $a = get_topic($tid);
  $topic = htmlspecialchars($a['name']);
}
if (is_user())
  $msg = _('Browse/Archive:') . ' ' . $topic;
else
  $msg = _('Browse:') . ' ' . $topic;
echo html_h($msg);

// list only main topics
if (empty($tid)) {
  // $q = db_query("SELECT T.id,T.name,T.description,COUNT(D.id) AS documents FROM topic T LEFT OUTER JOIN nr_document D ON (T.id=D.topic_id AND D.status='a') WHERE T.parent_id='0' GROUP BY T.id,T.name,T.description ORDER BY T.name"); FIXME: only for POSTGRESQL >= 7.1
  $q = db_query("SELECT T.id,T.name,T.description,COUNT(D.id) AS documents FROM topic T,nr_document D WHERE T.id=D.topic_id AND D.status='a' AND T.parent_id='0' GROUP BY T.position,T.id,T.name,T.description UNION ALL SELECT id,name,description,0 AS documents FROM topic WHERE parent_id='0' AND id NOT IN (SELECT topic_id FROM nr_document WHERE status='a') ORDER BY name");
  if (db_rows($q))
    list_topics(_('Topics'), $q);
  else
    echo html_p(html_b(_('There are no topics available')));

  // permit topic creation
  if (is_administrator())
    echo html_p(format_action(_('Create a new main topic'),
                              "{$cfg_site}topic/edit.php?pid=0"));

  // finish
  page_end();
  exit();
}

// show topic path
format_path($tid, "{$cfg_site}document/list.php", false);

// show topic info
format_topic($a['description'], $a['created'], $a['maintainer_id']);
$pid = $a['parent_id'];
if (is_administrator()) {
  echo html_p(format_action(_('Edit topic'),
                            "{$cfg_site}topic/edit.php?tid=$tid"));
  echo html_p(format_action(_('Remove topic'),
                            "{$cfg_site}topic/action.php?op=d&tid=$tid"));
}
else
  echo "<p>\n";

// list subtopics, if any
//$q = db_query("SELECT T.id,T.name,T.description,COUNT(D.id) AS documents FROM topic T LEFT OUTER JOIN nr_document D ON (T.id=D.topic_id AND D.status='a') WHERE T.parent_id='$tid' GROUP BY T.id,T.name,T.description ORDER BY T.name"); FIXME: only for POSTGRESQL >= 7.1
$q = db_query("SELECT T.id,T.name,T.description,COUNT(D.id) AS documents FROM topic T,nr_document D WHERE T.id=D.topic_id AND D.status='a' AND T.parent_id='$tid' GROUP BY T.id,T.name,T.description UNION ALL SELECT id,name,description,0 AS documents FROM topic WHERE parent_id='$tid' AND id NOT IN (SELECT topic_id FROM nr_document WHERE status='a') ORDER BY name");
if (db_rows($q))
  list_topics(_('Subtopics'), $q);
if (is_administrator())
  echo html_p(format_action(_('Create a new subtopic'),
                            "{$cfg_site}topic/edit.php?pid=$tid"));

// set sort column
if (empty($sort))
  $sort = 't';
switch ($sort) {
 case 't':
   $ord = 'title';
   break;
 case 's':
   $ord = 'size';
   break;
 case 'c':
   $ord = 'category';
   break;
 default:
   $ord = 'updated';
};
$ord .= ($desc == 'y') ? ' DESC' : ' ASC';

$lim = 25;
$off = ($page - 1) * $lim;

$q = db_query("SELECT D.title,D.code,D.updated,D.size,D.remote,C.name AS category,F.name AS format,F.icon FROM nr_document D,nr_category C,nr_format F WHERE D.status='a' AND D.topic_id='$tid' AND C.id=D.category_id AND F.id=D.format_id ORDER BY $ord", $lim, $off);
$n = db_simple_query("SELECT COUNT(id) FROM nr_document WHERE status='a' AND topic_id='$tid'");

if ($n) {
  if ($n == 1) {
    $left = _('There is <b>1</b> document available');
    $first = $last = 1;
  }
  else {
    $first = min($n, $off + 1);
    $last = min($n, $off + $lim);
    $left = _M('There are <b>@1</b> documents available', $n);
    $right = _M('Showing the documents <b>@1</b> - <b>@2</b>', $first, $last);
  }
  format_bar($left, $right);
  echo "<p>\n";
  $PHP_SELF = $_SERVER['PHP_SELF'];
  if ($page == 1)
    $url = "$PHP_SELF?tid=$tid";
  else
    $url = "$PHP_SELF?tid=$tid&page=$page";
  echo '<table width="100%" border="0" cellpadding="0" cellspacing="1">';
  echo '<tr><td></td>';
  format_header(_('Title'),    $url,             $sort == 't', $desc == 'y');
  format_header(_('Size'),     $url . '&sort=s', $sort == 's', $desc == 'y');
  format_header(_('Category'), $url . '&sort=c', $sort == 'c', $desc == 'y');
  format_header(_('Updated'),  $url . '&sort=u', $sort == 'u', $desc == 'y');
  echo '</tr>';
  while ($a = db_fetch_array($q))
    format_item($a['title'], $a['code'], $a['updated'], $a['size'],
                $a['remote'], _($a['category']), _($a['format']), $a['icon'], $tid);
  echo '</table><p>';

  format_page_list(ceil($n/$lim), $page);
}
else
  echo html_p(html_b(_('No documents archived')));

if (is_user() && db_simple_query("SELECT COUNT(category_id) FROM nr_topic_category WHERE topic_id='$tid'"))
  echo html_p(format_action(_('Archive a new document in this topic'),
                            "{$cfg_site}document/put.php?tid=$tid"));

echo html_p(html_small(_('All material on this system is the property and responsibility of its authors.')));
$PHP_SELF = $_SERVER['PHP_SELF'];
page_end($PHP_SELF . (($pid) ? "?tid=$pid" : ''));


/*-------------- functions --------------*/

function list_topics ($title, $query)
{
  $PHP_SELF = $_SERVER['PHP_SELF'];

  html_table_begin(false, 'right', true);
  html_table_item(html_b($title), 'title');
  while ($a = db_fetch_array($query)) {
    html_table_row_begin();
    html_table_row_item(html_a(convert_line($a['name']), "$PHP_SELF?tid={$a['id']}"), '', '30%');
    html_table_row_item(convert_text($a['description']), 'left', '60%');
    html_table_row_item(($a['documents']) ? _('doc:') . $a['documents'] : '&nbsp;', '', '10%');
    html_table_row_end();
  }
  html_table_end();
  echo "<p>\n";
}

function format_header ($title, $url, $active, $descending)
{
  global $cfg_site;

  if ($active && !$descending)
    $url .= '&desc=y';
  echo '<td><b>' . html_a($title, $url) . '</b>';
  if ($active) {
    if ($descending)
      echo " <img src=\"{$cfg_site}images/desc.gif\"></td>";
    else
      echo " <img src=\"{$cfg_site}images/asc.gif\"></td>";
  }
  else
    echo '</td>';
}

function format_item ($title, $code, $updated, $size, $remote, $category,
                      $format, $icon,$tid)
{
  global $cfg_site;

  $title = convert_line($title, 65);
  $updated = db_locale_date($updated);
  $size = ($remote == 'n') ? int_to_size($size) : '&nbsp;';
  $category = htmlspecialchars($category);
  $format = htmlspecialchars($format);

  $code = rawurlencode($code);
  $details  = "{$cfg_site}document/?code=$code&tid=$tid";
  $view     = "{$cfg_site}document/?view=$code&tid=$tid";
  $download = "{$cfg_site}document/?down=$code&tid=$tid";

  $msg1 = _('View');
  $msg2 = _('Download');

echo <<<HTML
<tr>
<td width="1%"><a href="$details"><img alt="$format" border="0" src="{$cfg_site}images/icon_$icon.gif" title="$format"></a></td>
<td class="odd" width="50%">&nbsp;<a href="$details">$title</a></td>
<td class="odd" align="right">$size&nbsp;</td>
<td class="odd">&nbsp;$category</td>
<td class="odd">&nbsp;$updated</td>
<td width="1%"><a href="$view"><img alt="$msg1" border="0" src="{$cfg_site}images/view.gif" title="$msg1"></a></td>
<td width="1%"><a href="$download"><img alt="$msg2" border="0" src="{$cfg_site}images/download.gif" title="$msg2"></a></td>
</tr>
HTML;
}

?>
