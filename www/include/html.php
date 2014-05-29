<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

/*-------------- local variables --------------*/

$html_table_align;
$html_table_border;
$html_table_row_align;


/*-------------- basic functions --------------*/

function html_header ($title = '', $head = '', $body = '')
{
  global $cfg_site;
  static $sent = false;

  if (!$sent) {
    echo "<html>\n<head>\n<title>$title</title>\n";
    echo "<link href=\"{$cfg_site}config.css\" rel=\"StyleSheet\" type=\"text/css\">\n";
    echo "$head</head>\n<body $body>\n\n";
    $sent = true;
    return true;
  }
  else
    return false;
}

function html_footer ()
{
  echo "\n</body>\n</html>\n";
}

function html_a ($content, $url, $target = '')
{
  if (empty($target))
    return "<a href=\"$url\">$content</a>";
  else
    return "<a href=\"$url\" target=\"$target\">$content</a>";
}

function html_b ($content)
{
  return "<b>$content</b>";
}

function html_big ($content)
{
  return "<big>$content</big>";
}

function html_error ($content)
{
  return "<font class=\"emphasis\"><b>$content</b></font>";
}

function html_h ($content)
{
  return "<h2>$content</h2>\n\n";
}

function html_p ($content, $align = 'center')
{
  return "<p align=\"$align\">$content</p>\n";
}

function html_small ($content)
{
  return "<small>$content</small>";
}


/*-------------- table functions --------------*/

function html_table_begin ($border = false, $align = 'center', $auto = false)
{
  global $html_table_align, $html_table_auto, $html_table_border;

  $html_table_align = $align;
  $html_table_auto = ($auto) ? 1 : 0;
  if ($html_table_border = $border) {
    echo "<table width=\"100%\" border=\"0\" cellpadding=\"1\" cellspacing=\"0\">\n";
    echo "<tr class=\"border\"><td>";
  }
  echo "<table width=\"100%\" border=\"0\" cellpadding=\"3\" cellspacing=\"0\">\n";
}

function html_table_item ($content, $class = '', $align = '')
{
  global $html_table_align, $html_table_auto;

  if (empty($class)) {
    if ($html_table_auto)
      $class = ($html_table_auto & 1) ? 'odd' : 'even';
    else
      $class = 'base';
  }
  if (empty($align))
    $align = $html_table_align;
  echo "<tr class=\"$class\"><td align=\"$align\">$content</td></tr>\n";
}

function html_table_row_begin ($class = '', $align = '')
{
  global $html_table_align, $html_table_auto, $html_table_row_align;

  if (empty($class)) {
    if ($html_table_auto)
      $class = ($html_table_auto++ & 1) ? 'odd' : 'even';
    else
      $class = 'base';
  }
  if (empty($align))
    $html_table_row_align = $html_table_align;
  echo "<tr class=\"$class\">\n";
}

function html_table_row_item ($content, $align = '', $width = '')
{
  global $html_table_row_align;

  if (empty($align))
    $align = $html_table_row_align;
  if (!empty($width))
    echo "<td align=\"$align\" width=\"$width\">$content</td>\n";
  else
    echo "<td align=\"$align\">$content</td>\n";
}

function html_table_row_end ()
{
  echo "</tr>\n";
}

function html_table_end ()
{
  global $html_table_border;

  echo "</table>\n";
  if ($html_table_border)
    echo "</td></tr></table>\n";
}


/*-------------- form functions --------------*/

function html_form_begin ($action, $post = true, $enctype = '', $target = '')
{
//print("Post: $action \n");
  echo '<form method="' . (($post) ? 'post' : 'get') . "\" action=\"$action\"";
  if (!empty($enctype))
    echo " enctype=\"$enctype\"";
  if (!empty($target))
    echo " target=\"$target\"";
  echo ">\n";
}

function html_form_area ($title, $name, $rows, $content, $max = 0)
{
  $content = htmlentities(stripslashes($content));
  if (!empty($title)) {
    if ($max)
      echo "<b>$title (" . _M('maximum size of @1 characters', $max) . "):</b><br>\n";
    else
      echo "<b>$title:</b><br>\n";
  }
  echo "<textarea class=\"text\" name=\"$name\" rows=\"$rows\" cols=\"80\" wrap=\"soft\">$content</textarea>\n";
}

function html_form_check ($title, $name, $options, $array = '', $break = true)
{
  if (!empty($title)) {
    echo "<b>$title:</b>\n";
    if ($break)
      echo '<br>';
  }
  foreach (array_keys($options) as $key) {
    if (empty($array[$key]))
      echo '<input ';
    else
      echo '<input checked ';
    echo "type=\"checkbox\" name=\"$name" . "[$key]\" value=\"$key\"> {$options[$key]}\n";
  }
}

function html_form_file ($title, $name, $break = true)
{
  if (!empty($title)) {
    echo "<b>$title:</b>\n";
    if ($break)
      echo '<br>';
  }
  echo "<input type=\"file\" name=\"$name\">\n";
}

function html_form_hidden ($name, $content)
{
  $content = htmlentities(stripslashes($content));
  echo "<input type=\"hidden\" name=\"$name\" value=\"$content\">\n";
}

function html_form_password ($title, $name, $size, $content, $max,
                             $break = true)
{
  $content = htmlentities(stripslashes($content));
  if (!empty($title)) {
    echo "<b>$title:</b>\n";
    if ($break)
      echo '<br>';
  }
  echo "<input type=\"password\" name=\"$name\" value=\"$content\" maxlength=\"$max\" size=\"$size\">\n";
}

function html_form_radio ($title, $name, $options, $default = '',
                          $break = true)
{
  if (!empty($title)) {
    echo "<b>$title:</b>\n";
    if ($break)
      echo '<br>';
  }
  foreach (array_keys($options) as $key) {
    if ($key == $default)
      echo '<input checked ';
    else
      echo '<input ';
    echo "type=\"radio\" name=\"$name\" value=\"$key\"> {$options[$key]}\n";
  }
}

function html_form_select ($title, $name, $options, $default = '',
                           $break = true)
{
  if (!empty($title)) {
    echo "<b>$title:</b>\n";
    if ($break)
      echo '<br>';
  }
  echo "<select name=\"$name\" size=\"1\">\n";
  foreach (array_keys($options) as $key) {
    if ($key == $default)
      echo '<option selected ';
    else
      echo '<option ';
    echo "value=\"$key\">{$options[$key]}</option>\n";
  }
  echo "</select>\n";
}

function html_form_submit ($label, $name = '')
{
  echo '<input type="submit"';
  if (!empty($name))
    echo " name=\"$name\"";
  echo " value=\"$label\">\n";
}

function html_form_text ($title, $name, $size, $content, $max, $break = true)
{
  $content = htmlentities(stripslashes($content));
  if (!empty($title)) {
    echo "<b>$title:</b>\n";
    if ($break)
      echo '<br>';
  }
  echo "<input type=\"text\" name=\"$name\" value=\"$content\" maxlength=\"$max\" size=\"$size\">\n";
}

function html_form_end ()
{
  echo "</form>\n";
}

?>
