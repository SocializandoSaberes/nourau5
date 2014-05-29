<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

/*-------------- includes --------------*/

require_once BASE . 'include/format.php';
require_once BASE . 'include/util.php';


/*-------------- formating functions --------------*/

function format_user ($name, $email, $info, $level, $accessed)
{
  $email = convert_email($email);
  format_line(_('Name'), htmlspecialchars($name) . ' &lt;' .
              html_a($email, "mailto:$email") . '&gt;', false);
  format_line(_('Level'), $level);
  format_line(_('Last access'), db_locale_date($accessed));
  format_block(_('Personal information'), $info);
  echo "<p>\n";
}

?>
