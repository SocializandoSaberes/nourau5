<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

/*-------------- includes --------------*/

require_once BASE . 'include/html.php';
require_once BASE . 'include/util.php';


/*-------------- formating functions --------------*/

function format_notice ($subject, $notice, $uid, $posted, $username = '')
{
  global $cfg_site;

  $subject = htmlspecialchars($subject);
  $notice = convert_text($notice);
  $posted = db_locale_date($posted);
  if (empty($username))
    $username = get_user($uid, 'username');
  $user = html_a(html_b($username), "{$cfg_site}user/?uid=$uid");

  html_table_begin(true, 'left');
  html_table_item(html_b($subject) . '<br>' .
                  html_small(_M('published by @1 at @2', $user, $posted)),
                  'odd');
  html_table_item($notice);
  html_table_end();
}

?>
