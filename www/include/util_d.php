<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

/*-------------- includes --------------*/

require_once BASE . 'include/control.php';
require_once BASE . 'include/page.php';
require_once BASE . 'include/util.php';


/*-------------- database functions --------------*/

function get_category ($cid, $field = '')
{
  if (empty($cid))
    error(_('Category not specified'));
  if (!empty($field))
    $q = db_query("SELECT $field FROM nr_category WHERE id='$cid'");
  else
    $q = db_query("SELECT * FROM nr_category WHERE id='$cid'");
  if (!db_rows($q))
    error(_('Category not found'));
  if (!empty($field))
    return db_result($q, 0, $field);
  else
    return db_fetch_array($q);
}

function get_document ($did, $field = '')
{
  if (empty($did))
    error(_('Document not specified'));
  if (!empty($field))
    $q = db_query("SELECT $field FROM nr_document WHERE id='$did'");
  else
    $q = db_query("SELECT * FROM nr_document WHERE id='$did'");
  if (!db_rows($q))
    error(_('Document not found'));
  if (!empty($field))
    return db_result($q, 0, $field);
  else
    return db_fetch_array($q);
}

function get_format ($fid, $field = '')
{
  if (empty($fid))
    error(_('Format not specified'));
  if (!empty($field))
    $q = db_query("SELECT $field FROM nr_format WHERE id='$fid'");
  else
    $q = db_query("SELECT * FROM nr_format WHERE id='$fid'");
  if (!db_rows($q))
    error(_('Format not found'));
  if (!empty($field))
    return db_result($q, 0, $field);
  else
    return db_fetch_array($q);
}

/*-------------- convenience functions --------------*/

function document_pending ()
{
  
  static $pending = -1;

  if ($pending == -1) {
    $pending = 0;

    // check documents waiting for approval
    if (is_maintainer()) {
      if (is_administrator() && db_simple_query("SELECT COUNT(id) FROM nr_document WHERE status='w'"))
        $pending = 1;
      else if (db_simple_query("SELECT COUNT(id) FROM nr_document WHERE status='w' AND topic_id IN (SELECT id FROM topic WHERE maintainer_id='{$_SESSION['uid']}')"))
        $pending = 1;
    }

    // check documents waiting for verification
    if (is_administrator() && db_simple_query("SELECT COUNT(id) FROM nr_document WHERE status='v'"))
      $pending = 1;
  }
  return $pending;
}

function can_edit_document ($did)
{
  
  // check current user rights to edit/remove given document
  if (!is_user())
    return false;
  return (is_administrator() || $_SESSION['uid'] == get_document($did, 'owner_id') || $_SESSION['uid'] == db_simple_query("SELECT T.maintainer_id FROM nr_document D,topic T WHERE D.id='$did' AND D.topic_id=T.id"));
}

function document_finish_user ($uid)
{
  // move ownership of documents to respective topic maintainer
  $q = db_query("SELECT D.id,T.maintainer_id FROM nr_document D,topic T WHERE D.owner_id='$uid' AND D.topic_id=T.id");
  while ($a = db_fetch_array($q))
    db_command("UPDATE nr_document SET owner_id='{$a['maintainer_id']}' WHERE id='{$a['id']}'");
}


/*-------------- validation functions --------------*/

function valid_email_list ($email)
{
  $list = split('[ ,]+', $email);
  foreach ($list as $item)
    if (!valid_email($item))
      return false;
  return true;
}

function valid_size ($size)
{
  return preg_match('/^\d+([.,]\d+)?\s*([mk]b)?$/i', $size);
}


/*-------------- miscellaneous functions --------------*/

function int_to_size ($value, $show_mb = false)
{
  if ($show_mb && $value > 1024*1024)
    return round($value / (1024*1024)) . ' Mb';
  if ($value > 1024)
    return round($value / 1024) . ' Kb';
  return $value;
}

function size_to_int ($size)
{
  $size = strtr($size, ',MKB', '.mkb'); // normalize
  if (!preg_match('/^(\d+(.\d+)?)\s*([mk]b)?$/', $size, $matches))
    return 0;
  $value = $matches[1];
  switch ($matches[3]) {
  case 'mb':
    $value *= 1024;
  case 'kb':
    $value *= 1024;
    break;
  }
  if ($value > 1<<30)
    return 0; // too big
  return ceil($value);
}

?>
