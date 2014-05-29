<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

require_once '../include/start.php';
require_once BASE . 'include/format.php';
require_once BASE . 'include/page.php';
require_once BASE . 'include/util_d.php';
require_once BASE . 'include/util_t.php';



if (!is_administrator())
  error(_('Access denied'));

if ($op == 'a') { // ---------------- approve registration
  // validate input
  if (empty($email))
    error(_('E-mail not specified'));
  $q = db_query("SELECT code,motive FROM user_registration WHERE email='$email'");
  if (!db_rows($q))
    error(_('Registration not found'));

  // ask confirmation
  if (empty($conf)) {
    $motive = db_result($q, 0, 'motive');
    $PHP_SELF = $_SERVER['PHP_SELF'];
    approve(_M("Do you want to approve the user with e-mail '@1'?", $email), "$PHP_SELF?op=$op&email=$email", $motive);
  }

  if ($conf == _('Yes')) {
    // approve registration
    db_command("UPDATE user_registration SET status='a' WHERE email='$email'");
    add_log('c', 'ua', "email=$email");

    // send e-mail confirmation
    $code = db_result($q, 0, 'code');
    $url = "{$cfg_site}user/edit.php?code=$code&email=$email";
    send_mail($email, _('Registration confirmation'), _("Your registration was approved.\nConfirm your registration by following the link below:") . "\n\n$url\n");

    // finish
    message(_('User approved'), "{$cfg_site}user/list.php");
  }
  else if ($conf == _('No'))
    redirect("{$cfg_site}user/action.php?op=r&email=$email");
  else
    redirect("{$cfg_site}user/list.php");
}
else if ($op == 'd') { // ---------------- remove user
  // validate input
  if (!valid_int($uid))
    error(_('Invalid parameter'));
  $a = get_user($uid);

  // ask confirmation
  if (empty($conf)) {
    $user = $a['name'] . ' (' . $a['username'] . ')';
    $PHP_SELF = $_SERVER['PHP_SELF'];
    remove(_M("Do you want to remove the user '@1'?", $user),
           "$PHP_SELF?op=$op&uid=$uid", true);
  }

  if ($conf == _('Yes')) {
    // update topics
    topic_finish_user($uid);

    // update documents
    document_finish_user($uid);

    // remove user
    db_command("DELETE FROM users WHERE id='$uid'");
    add_log('c', 'ud', "uid=$uid");

    if ($notify == 'y') {
      // send e-mail notification
      $msg = _('Your registration was removed by the administrator.') . "\n";
      if (!empty($reason))
        $msg .= _('The reason given was:') . "\n\n$reason\n";
      send_mail($a['email'], _('Registration removed'), $msg);
    }

    // finish
    message(_('User removed'), "{$cfg_site}user/list.php");
  }
  else
    redirect("{$cfg_site}user/list.php");
}
else if ($op == 'r') { // ---------------- reject registration
  // validate input
  if (empty($email))
    error(_('E-mail not specified'));
  if (!db_simple_query("SELECT COUNT(email) FROM user_registration WHERE email='$email'"))
    error(_('Registration not found'));
  $PHP_SELF = $_SERVER['PHP_SELF'];
  // ask confirmation
  if (empty($conf))
    remove(_M("Do you want to reject the user with e-mail '@1'?", $email),
           "$PHP_SELF?op=$op&email=$email", false);

  if ($conf == _('Yes')) {
    // reject registration
    db_command("DELETE FROM user_registration WHERE email='$email'");
    add_log('c', 'ur', "email=$email");

    // send e-mail notification
    $msg = _('Your registration was rejected by the administrator.') . "\n";
    if (!empty($reason))
      $msg .= _('The reason given was:') . "\n\n$reason\n";
    send_mail($email, _('Registration rejected'), $msg);

    // finish
    message(_('User rejected'), "{$cfg_site}user/list.php");
  }
  else
    redirect("{$cfg_site}user/list.php");
}
else
  error(_('Invalid operation'));


/*-------------- functions --------------*/

function approve ($msg, $url, $content, $back = '')
{
  page_begin_aux($msg);

  $yes = _('Yes');
  $no = _('No');
  $cancel = _('Cancel');

echo <<<HTML
<form method="post" action="$url">
<table align="center" border="0" cellpadding="8" cellspacing="0">
<tr><td align="left" colspan="3">
HTML;

  format_block(_('Motive for the registration'), $content);

echo <<<HTML
</td></tr>
<tr><td align="center" colspan="3"><b>$msg</b></td></tr>
<tr><td align="center"><input type="submit" name="conf" value="$yes"></td>
<td align="center"><input type="submit" name="conf" value="$no"></td>
<td align="center"><input type="submit" name="conf" value="$cancel"></td></tr>
</table>
</form>
HTML;

  page_end($back);
  exit();
}

?>
