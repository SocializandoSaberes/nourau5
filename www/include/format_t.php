<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

/*-------------- includes --------------*/

require_once BASE . 'include/util_u.php';


/*-------------- formating functions --------------*/

function format_path ($tid, $home, $link_self)
{
  $name_array = array();
  $id_array = array();
  $id = $tid;
  while ($id) {
    $q = db_query("SELECT name,parent_id FROM topic WHERE id='$id'");
    array_push($id_array, $id);
    array_push($name_array, htmlspecialchars(db_result($q, 0, 'name')));
    $id = db_result($q, 0, 'parent_id');
  }

  echo '<small>' . html_a(_('Home'), $home);
  echo ' <b>&gt;</b> ';
  while ($id = array_pop($id_array)) {
    $name = array_pop($name_array);
    if ($id == $tid && !$link_self)
      echo $name;
    else
      echo html_a($name, "$home?tid=$id");
    if ($id != $tid)
      echo ' <b>&gt;</b> ';
  }
  echo " </small>\n";
}

function format_topic ($description, $created, $uid, $username = '')
{
  global $cfg_site;

  $description = convert_text($description);
  $created = db_locale_date($created);
  if (empty($username))
    $username = get_user($uid, 'username');
  $maintainer = _('Maintainer');
  $created_msg = _('Created');

echo <<<HTML
<blockquote><table width="90%" border="0" cellpadding="3" cellspacing="0">
<tr class="odd"><td align="left">$description</td></tr>
</table></blockquote><p>
<b>$maintainer:</b>
<b><a href="{$cfg_site}user/?uid=$uid">$username</a></b><br>
HTML;
  // <b>$created_msg:</b> $created<p>
}

?>
