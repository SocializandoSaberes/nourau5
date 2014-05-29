<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

/*-------------- includes --------------*/

require_once BASE . 'include/control.php';
require_once BASE . 'include/format_d.php';
require_once BASE . 'include/html.php';
require_once BASE . 'include/util.php';
require_once BASE . 'include/util_d.php';
require_once BASE . 'include/util_u.php';


/*-------------- local variables --------------*/

$page_has_menu = false;


/*-------------- page functions --------------*/

function page_begin_aux ($title = '', $head = '')
{
  global $cfg_site, $cfg_site_title, $cfg_reg_mode, $cfg_version,
    $cfg_banner, $cfg_banner_url, $cfg_banner_background, $cfg_banner_color;
  global $print;

  if (empty($title))
    $title = $cfg_site_title;
  if ($print == 'y') {
    html_header($title, $head);
    return;
  }
  if (!html_header($title, $head, 'marginwidth="0" marginheight="0" leftmargin="0" rightmargin="0" topmargin="0" bottommargin="0"'))
    return;

  $system = _('Document Archival and Indexing System');
  $menu = array();
  if (is_user()) {
    $username = $_SESSION['username'];
    array_push($menu, _M('logged as <b>@1</b>', $username));
    array_push($menu, html_a(_('profile'),
                             "{$cfg_site}user/?uid={$_SESSION['uid']}"));
    array_push($menu, html_a(_('logout'),
                             "{$cfg_site}user/logout.php"));
  }
  else {
    array_push($menu, html_a(_('login'), "{$cfg_site}user/login.php"));
    if ($cfg_reg_mode != 'closed')
      array_push($menu, html_a(_('register'), "{$cfg_site}user/register.php"));
  }
  array_push($menu, html_a(_('about'),
                           "http://www.rau-tu.unicamp.br/nou-rau/")); // FIXME
  array_push($menu, _('help'));
  array_push($menu, '<font class="emphasis">' . _('version') .
             ' ' . $cfg_version . '</font>'); // FIXME
  $menu = implode(' | ', $menu);

echo <<<HTML
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr background="$cfg_banner_background" bgcolor="$cfg_banner_color">
<td align="left"><a href="$cfg_banner_url"><img src="$cfg_banner" border="0"></a></td>
</tr></table>
<table border="0" cellpadding="1" cellspacing="0" width="100%">
<tr class="odd" >
<td align="left">&nbsp;&nbsp;<b>$system</b></td>
<td align="right" valign="center"><small>$menu &nbsp;</small></td>
</tr></table>
<table border="0" cellpadding="0" cellspacing="0" width="100%" height="1">
<tr class="border" height="1">
<td><img src="{$cfg_site}images/empty.gif"></td>
</tr></table>
HTML;

  echo "\n\n";
}

function page_menu_begin ($module = '')
{
  global $cfg_site;
  global $page_has_menu;

  $page_has_menu = true;

echo <<<HTML
<table width="100%" border="0" cellpadding="0" cellspacing="8"><tr>
<td align="left" valign="top" width="15%">
HTML;

  echo "\n\n";

  // index colors
  $mai = ($module == 'm') ? 'even' : 'odd';
  $doc = ($module == 'd') ? 'even' : 'odd';
  //$not = ($module == 'n') ? 'even' : 'odd'; FIXME
  $usr = ($module == 'u') ? 'even' : 'odd';

  // highlight if necessary
  if (document_pending())
    $doc = 'hilite';
  if (user_pending())
    $usr = 'hilite';

  // index box
  html_table_begin(true);
  html_table_item(html_b(_('Index')), 'title');
  html_table_item(html_a(_('Main page'), $cfg_site), $mai);
  html_table_item(html_a(_('Documents'), "{$cfg_site}document/list.php"),
                  $doc);
  html_table_item(html_a(_('Notices'), "{$cfg_site}notice/publish.php"), $not);
  html_table_item(html_a(_('Users'), "{$cfg_site}user/list.php"), $usr);
  html_table_end();
  echo "<p>\n";
}

function page_menu_end ()
{
  // search box
  format_search_box_menu();
  echo "<p>\n";

  echo "\n";
  echo '</td><td align="left" valign="top" width="85%">';
  echo "\n\n";
}

function page_end ($url = '')
{
  global $REQUEST_URI, $print;

  $back = _('Go back');

  if ($print == 'y') {
    $url = preg_replace('/&print=y/', '', $REQUEST_URI);
    echo html_a($back, $url);
    html_footer();
    return;
  }

  if (!empty($url)) {
    global $cfg_site, $cfg_webmaster;

    $home = _('Main page');
    $mail = _('Contact us');
    $print = _('Print format');

    echo "\n";

echo <<<HTML
<table width="100%" border="0" cellpadding="2" cellspacing="0">
<tr class="footer">
<td align="left">
  <a href="$url"><img alt="$back" border="0" src="{$cfg_site}images/back.gif" title="$back"></a>
  <a href="$cfg_site"><img alt="$home" border="0" src="{$cfg_site}images/home.gif" title="$home"></a>
</td>
<td align="right">
  <a href="mailto:$cfg_webmaster"><img alt="$mail" border="0" src="{$cfg_site}images/mail.gif" title="$mail"></a>
  <a href="$REQUEST_URI&print=y"><img alt="$print" border="0" src="{$cfg_site}images/print.gif" title="$print"></a>
</td>
</tr></table>
HTML;

    echo "\n";
  }

  global $page_has_menu;

  if ($page_has_menu)
    echo "\n</td></tr></table>\n\n";

  html_footer();
}


/*-------------- feedback functions --------------*/

function message ($msg, $url = '', $back = '')
{
  if (!empty($url))
    //$meta = "<meta http-equiv=\"refresh\" content=\"5; URL=$url\">";
    $meta = "<meta http-equiv=\"refresh\" content=\"5; \">";
  page_begin_aux($msg, $meta);
  echo html_p(html_big(html_b($msg)));
  if (!empty($url))
    echo html_p(html_a(_('Click here to continue'), $url));
  page_end($back);
  exit();
}

function error ($msg, $url = '')
{
  global $cfg_site;

  $error = _('Error');

  if (empty($url))
    $url = $cfg_site;
  page_begin_aux($msg,
                 //"<meta http-equiv=\"refresh\" content=\"5; URL=$url\">");
                 "<meta http-equiv=\"refresh\" content=\"5; \">");
  echo html_p(html_error(html_big("$error: $msg")));
  if (!empty($url))
    echo html_p(html_a(_('Click here to continue'), $url));
  page_end();
  exit();
}

function confirm ($msg, $url, $back = '')
{
  page_begin_aux($msg);

  $yes = _('Yes');
  $no = _('No');
echo <<<HTML
<form method="post" action="$url">
<table align="center" border="0" cellpadding="8" cellspacing="0">
<tr><td align="center" colspan="2"><b>$msg</b></td></tr>
<tr><td align="center"><input type="submit" name="conf" value="$yes"></td>
<td align="center"><input type="submit" name="conf" value="$no"></td></tr>
</table>
</form>
HTML;

  page_end($back);
  exit();
}

function remove ($msg, $url, $notify, $back = '')
{
  page_begin_aux($msg);

  $reason = _('Specify the reason:');
  $yes = _('Yes');
  $no = _('No');

echo <<<HTML
<form method="post" action="$url">
<table align="center" border="0" cellpadding="8" cellspacing="0">
<tr><td align="left" colspan="2"><b>$reason</b><br>
<textarea name="reason" rows="4" cols="80" wrap="soft"></textarea>
HTML;

  if ($notify) {
    echo '<br><input type="checkbox" name="notify" value="y" checked>';
    echo ' <b>' . _('send notification') . '</b>';
    echo "\n";
  }

echo <<<HTML
</td></tr>
<tr><td align="center" colspan="2"><b>$msg</b></td></tr>
<tr><td align="center"><input type="submit" name="conf" value="$yes"></td>
<td align="center"><input type="submit" name="conf" value="$no"></td></tr>
</table>
</form>
HTML;

  page_end($back);
  exit();
}

?>
