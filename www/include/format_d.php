<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

/*-------------- includes --------------*/

require_once BASE . 'include/html.php';


/*-------------- formating functions --------------*/

function format_bar ($left, $right = '')
{
  echo "<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">\n";
  echo "<tr class=\"footer\"><td align=\"left\"><font class=\"inverse\"><small>&nbsp;$left</small></font></td>\n";
  if (!empty($right))
    echo "<td align=\"right\"><font class=\"inverse\"><small>$right&nbsp;</small></font></td>\n";
  echo "</tr></table>\n";
}

function format_page_list ($pages, $current)
{
  global $cfg_site;
  global $QUERY_STRING;
  $PHP_SELF = $_SERVER['PHP_SELF'];
  $previous = _('previous');
  $next = _('next');

  if ($pages == 1)
    return;
  echo "<p align=\"center\">\n";
  $url = "$PHP_SELF?" . preg_replace('/&page=\d+/', '', $QUERY_STRING);
  if ($current == 1)
    echo $previous . '&nbsp;&nbsp;';
  else {
    $i = $current - 1;
    echo html_a($previous, ($i == 1) ? $url : "$url&page=$i") . '&nbsp;&nbsp;';
  }
  for ($i = 1; $i <= $pages; $i++) {
    if ($i == $current)
      echo "<b>$i</b>&nbsp;&nbsp;";
    else {
      echo html_a($i, ($i == 1) ? $url : "$url&page=$i") . '&nbsp;&nbsp;';
    }
  }
  if ($current == $pages)
    echo $next;
  else {
    $i = $current + 1;
    echo html_a($next, "$url&page=$i");
  }
  echo "</p>\n";
}

function format_search_box ($center = false)
{
  global $cfg_htdig_conf, $cfg_site;
  global $adv, $matchesperpage, $method, $sort, $words;

  html_form_begin("{$cfg_site}document/results.php", false);
  if ($center)
    echo "<table align=\"center\" border=\"0\"><tr><td>\n";
  if ($adv == 'y') {
    $method_op = array('and'     => _('all words'),
                       'or'      => _('any words'),
                       'boolean' => _('logical expression'));
    $sort_op = array('score'    => _('score'),
                     'title'    => _('title'),
                     'time'     => _('time'),
                     'revscore' => _('reverse score'),
                     'revtitle' => _('reverse title'),
                     'revtime'  => _('reverse time'));
    $matches_op = array('10'  => '10',
                        '25'  => '25',
                        '50'  => '50',
                        '999' => _('all'));
    html_form_select(_('Selection'), 'method', $method_op, $method, false);
    html_form_select(_('Ordering'), 'sort', $sort_op, $sort, false);
    html_form_select(_('Matches per page'), 'matchesperpage', $matches_op,
                     $matchesperpage, false);
    if ($center)
      echo "</td></tr><tr><td>\n";
    else
      echo "<p>\n";
  }
  else {
    if (!empty($method))
      html_form_hidden('method', $method);
    if (!empty($sort))
      html_form_hidden('sort', $sort);
    if (!empty($matchesperpage))
      html_form_hidden('matchesperpage', $matchesperpage);
  }
  if (!empty($adv))
    html_form_hidden('adv', $adv);
  html_form_text(_('Search for'), 'words', 30, $words, 100, false);
  html_form_submit(_('Search'));
  echo "&nbsp;&nbsp;";
  if ($adv == 'y')
    echo html_a(html_small(_('Normal search')),
                "{$cfg_site}document/search.php?words=" .
                rawurlencode(stripslashes($words)));
  else
    echo html_a(html_small(_('Advanced search')),
                "{$cfg_site}document/search.php?adv=y&words=" .
                rawurlencode(stripslashes($words)));
  if ($center)
    echo "</td></tr></table>\n";
  html_form_end();
}

function format_search_box_menu ()
{
  global $cfg_site;

  html_form_begin("{$cfg_site}document/results.php", false);
  html_table_begin(true);
  html_table_item(html_b(_('Search for') . ':'), 'title');
  html_table_item('<input type="text" name="words" value="" maxlength="30" size="10">', 'odd');
  html_table_item('<input type="submit" value="' . _('Search') . '">', 'odd');
  html_table_item(html_a(html_small(_('Advanced search')),
                         "{$cfg_site}document/search.php?adv=y"), 'odd');
  html_table_end();
  html_form_end();
}

?>
