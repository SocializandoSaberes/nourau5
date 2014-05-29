<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

// This database abstraction layer was inspired by SourceForge code.
// It uses PostgreSQL, but should be easy to port to other DBs.

/*-------------- database variables --------------*/

// database connection handle
$db_conn = array();

// current row for each result set
$db_row_pointer = array();


/*-------------- database functions --------------*/

function db_connect ()
{
  global $db_conn, $cfg_base, $cfg_user, $cfg_pass;
  //print("Usuario $cfg_user \nSenha: $cfg_pass \n Base: $cfg_base\n<BR>");
  $settings = "host=localhost dbname=$cfg_base user=$cfg_user";
  //print($settings . "<BR>");
  //if ($cfg_pass)
  //  $settings .= " password=$cfg_pass";
  if (!($db_conn = @pg_pconnect($settings)))
    if (!($db_conn = @pg_connect($settings)))
      fatal('<b>' . _('Database connection failed:') . "</b> $cfg_base");
}

function db_query ($sql, $limit = 0, $offset = 0)
{
  global $db_conn;

  if ($limit > 0) {
    if ($offset < 0)
      $offset = 0;
    $sql .= " LIMIT $limit OFFSET $offset";
  }
  if (!($q = @pg_exec($db_conn, $sql)))
    fatal('<b>' . _('Query failed:') . "</b> $sql<br><b>" .
          _('Error message:') . '</b> ' . @pg_errormessage($db_conn));
  return $q;
}

function db_simple_query ($sql)
{
  global $db_conn;

  if (!($q = @pg_exec($db_conn, $sql)))
    fatal('<b>' . _('Query failed:') . "</b> $sql<br><b>" .
          _('Error message:') . '</b> ' . @pg_errormessage($db_conn));
  return @pg_result($q, 0, 0);
}

function db_command ($sql)
{
  global $db_conn;

  if (!($q = @pg_exec($db_conn, $sql)))
    fatal('<b>' . _('Query failed:') . "</b> $sql<br><b>" .
          _('Error message:') . '</b> ' . @pg_errormessage($db_conn));
  return @pg_cmdtuples($q);
}

function db_rows ($q)
{
  return @pg_numrows($q);
}

function db_result ($q, $row, $field)
{
  return @pg_result($q, $row, $field);
}

function db_reset ($q, $row = 0)
{
  global $db_row_pointer;

  return $db_row_pointer[$q] = $row;
}

function db_fetch_array ($q)
{
  global $db_row_pointer;

  $db_row_pointer[$q]++;
  //print("Entrou...<BR>");
  //if (@pg_fetch_array($q, $db_row_pointer[$q] -1)==false) print("Falso <br>");
  return @pg_fetch_array($q, $db_row_pointer[$q] -1);
}

function db_error ()
{
  global $db_conn;

  return @pg_errormessage($db_conn);
}

function db_unix_date ($date)
{
  $a = explode(' ', $date);
  $d = explode('-', $a[0]);
  $t = explode(':', $a[1]);
  return mktime($t[0], $t[1], 0, $d[1], $d[2], $d[0]);
}

function db_locale_date ($date)
{
  return strftime('%x %H:%M', db_unix_date($date));
}

?>
