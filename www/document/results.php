<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

require_once '../include/start.php';
require_once BASE . 'include/defs_d.php';
require_once BASE . 'include/format_d.php';
require_once BASE . 'include/page_d.php';

if (isset($_GET['words'])) {

	$words = $_GET['words'];

}
//$QUERY_STRING = $words;
//$query="";
// validate input
if (empty($words))
  redirect("{$cfg_site}document/search.php");
else {
	$query.="words=" . $words;
}
// set default values if needed
if (empty($method)) {
  $method = 'and';
  $query .= "&method=$method";
}
if (empty($sort)) {
  $sort = 'score';
  $query .= "&sort=$sort";
}
if (empty($matchesperpage)) {
  $matchesperpage = '10';
  $query .= "&matchesperpage=$matchesperpage";
}
$htsearch_config =  $cfg_dir_base . "/" . $cfg_htdig_conf . ".conf";
//print("Query: $query <BR>");
// run search engine
putenv("QUERY_STRING=$query");
//putenv("REQUEST_METHOD=$REQUEST_METHOD");
exec("$cfg_tool_htsearch -c $htsearch_config $query", $result, $retval);
//print("Retval: $retval <br>");
//var_export($result);
if ($retval) {
   //var_export($result);
   error(_('Cannot complete search'));
}
//echo "$QUERY_STRING<br>$REQUEST_METHOD<p>";
//foreach ($result as $line) echo "$line<br>";
//page_end();
//exit();

// check results
array_shift($result); // drop content type
array_shift($result); // drop newline
if ($result[0] != 'OK' && $result[0] != 'NOMATCH' &&
    $result[0] != 'SYNTAXERROR') {
   var_export($result);
   //print("Resultado: $result[0] <br>");
   error(_('Cannot complete search'));

}

page_begin('s');

echo html_h(_('Search results: all topics'));

$search = '<b>' . htmlspecialchars(stripslashes($words)) . '</b>';
if ($result[0] == 'NOMATCH') {
  // no matches found
  echo _M('No documents found containing @1', $search);
  echo "<p>\n";
  format_search_box();
  page_end();
  exit();
}
else if ($result[0] == 'SYNTAXERROR') {
  // syntax error in boolean expression
  echo _M('The logical expression @1 has a syntax error', $search);
  echo "<p>\n";
  format_search_box();
  page_end();
  exit();
}

array_shift($result); // drop 'OK'
$matches = array_shift($result); // number of matches
$first = array_shift($result); // first match shown
$last = array_shift($result); // last match shown
$pages = array_shift($result); // number of pages
$page = array_shift($result); // current page

if ($matches == 1)
  $left = _M('Found <b>1</b> document containing @1', $search);
else {
  $left = _M('Found <b>@1</b> documents containing @2', $matches, $search);
  $right = _M('Showing the results <b>@1</b> - <b>@2</b>', $first, $last);
}
format_bar($left, $right);
echo "<p>\n";

for ($i = $first; $i <= $last; $i++) {
  $url = array_shift($result); // document URL
  $title = array_shift($result); // document title
  $score = array_shift($result); // search score
  $modified = db_locale_date(array_shift($result)); // document date FIXME
  $excerpt = array_shift($result); // document excerpt

  $a = explode('.', basename($url));
  format_result($a[0], $a[1], $a[2], $title, $score, $modified, $excerpt);
}

format_page_list($pages, $page);
format_search_box('center');

page_end("{$cfg_site}document/search.php");


/*-------------- functions --------------*/

function format_result ($tid, $did, $icon, $title, $score, $modified, $excerpt)
{
  global $cfg_site;

  $title = htmlspecialchars($title);
  $topic = get_topic($tid, 'name'); // FIXME needs a db query; too costly?

echo <<<HTML
<a href="{$cfg_site}document/?did=$did"><img border="0" src="{$cfg_site}images/icon_$icon.gif"></a>
<a href="{$cfg_site}document/?did=$did">$title</a><br>
<small>$excerpt<br>
<font class="emphasis">$score%</font> &raquo;
<font class="emphasis">$modified</font> &raquo;
<a href="{$cfg_site}document/list.php?tid=$tid">$topic</a></small><p>
HTML;
}

?>
