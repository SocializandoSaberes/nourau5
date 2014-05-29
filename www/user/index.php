<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

require_once '../include/start.php';
require_once BASE . 'include/format_u.php';
require_once BASE . 'include/page_u.php';

if (isset($_SESSION['uid'])) {
	$uid = $_SESSION['uid'];
}

if (isset($_GET['uid'])) {
	$uid = $_GET['uid'];
}

if (empty($uid))
  redirect("{$cfg_site}user/search.php");

// validate input
if (!valid_int($uid))
  error(_('Invalid parameter'));
$a = get_user($uid);

page_begin();

$user = $a['username'];
$name = $a['name'];
echo html_h(_M('Profile of @1', $user));

if ($a['level'] == ADM_LEVEL) {
  $level = _('Administrator');
  $name = _($name); // translate admin name
}
else if ($a['level'] == MNT_LEVEL)
  $level = _('Maintainer');
else
  $level = _('Collaborator');
format_user($name, $a['email'], $a['info'], $level, $a['accessed']);

if ($_SESSION['uid'] == $uid || is_administrator())
  echo html_p(format_action(_('Edit profile'),
                            "{$cfg_site}user/edit.php?uid=$uid"));
if ($_SESSION['uid'] == $uid)
  echo html_p(format_action(_('Change password'),
                            "{$cfg_site}user/change.php?uid=$uid"));

page_end();

?>
