<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

require_once '../include/start.php';
require_once BASE . 'include/format.php';
require_once BASE . 'include/page_u.php';
require_once BASE . 'include/util.php';

page_begin();

echo html_h(_('Browse: all users'));

if (is_administrator()) {
  // list candidates
  $q = db_query("SELECT email,requested FROM user_registration WHERE status='w' ORDER BY requested");
  if (db_rows($q))
    list_candidates(_('Candidates'), $q);
}

// list users
$q = db_query("SELECT id,username,name,accessed FROM users ORDER BY accessed DESC");
if (db_rows($q))
  list_users(_('Users'), $q);

if (is_administrator())
  echo html_p(format_action(_('Create a new user'),
                            "{$cfg_site}user/edit.php"));

page_end();


/*-------------- functions --------------*/

function list_candidates ($title, $query)
{
  global $cfg_site;

  html_table_begin(false, 'right', true);
  html_table_item(html_b($title), 'title');
  while ($a = db_fetch_array($query)) {
    $email = $a['email'];
    $requested = db_locale_date($a['requested']);

    html_table_row_begin();
    html_table_row_item($requested, '', '20%');
    html_table_row_item($email, 'left', '70%');
    html_table_row_item(format_action(_('approve/reject'), "{$cfg_site}user/action.php?op=a&email=$email"), '', '10%');
    html_table_row_end();
  }
  html_table_end();
  echo "<p>\n";
}

function list_users ($title, $query)
{
  global $cfg_site;

  html_table_begin(false, 'right', true);
  html_table_item(html_b($title), 'title');
  while ($a = db_fetch_array($query)) {
    $uid = $a['id'];
    $username = $a['username'];
    $name = htmlspecialchars(($uid == '1') ? _($a['name']) : $a['name']);
    $accessed = db_locale_date($a['accessed']);

    html_table_row_begin();
    html_table_row_item($accessed, '', '20%');
    html_table_row_item(html_a(html_b($username), "{$cfg_site}user/?uid=$uid"),
                        '', '5%');
    html_table_row_item($name, 'left', '65%');
    if (is_administrator())
      html_table_row_item(($uid != '1') ? format_action(_('remove'), "{$cfg_site}user/action.php?op=d&uid=$uid") : '&nbsp;', '', '10%');
    html_table_row_end();
  }
  html_table_end();
  echo "<p>\n";
}
