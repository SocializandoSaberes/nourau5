<?php
// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

require_once '../include/start.php';
require_once BASE . 'include/defs_d.php';
require_once BASE . 'include/format.php';
require_once BASE . 'include/format_d.php';
require_once BASE . 'include/format_t.php';
require_once BASE . 'include/page_d.php';
require_once BASE . 'include/util.php';
require_once BASE . 'include/util_d.php';


if (isset($_GET['view'])) {
	$view = $_GET['view'];
}

if (isset($_GET['down'])) {
        $down = $_GET['down'];
}

if (isset($_GET['tid'])) {
        $tid = $_GET['tid'];
}

if (isset($_GET['print'])) {
        $print = $_GET['print'];
}


if (isset($_GET['code'])) {
        $code = $_GET['code'];
}

if (isset($_GET['did'])) {
        $did = $_GET['did'];
}


// view document
if (!empty($view))
  view_document($view, false);

// download document
if (!empty($down))
  view_document($down, true);

// translate from document id to code and redirect
if (!empty($did)) {
  if (!valid_int($did))
    error(_('Invalid parameter'));
  $code = get_document($did, 'code');
  redirect("{$cfg_site}document/?code=" . rawurlencode($code));
}

// if empty go to search page
if (empty($code))
  redirect("{$cfg_site}document/search.php");

// get document
$q = db_query("SELECT * FROM nr_document WHERE code='$code'");
if (!db_rows($q))
  error(_('Document not found'));
$a = db_fetch_array($q);
$did = $a['id'];

// get display mode
$topic = htmlspecialchars(get_topic($a['topic_id'], 'name'));
if ($a['status'] == 'v') {
  check_administrator_rights();
  $msg = _('Verify:') . ' ' . $topic;
  $filename = substr($a['filename'], 5); // remove random prefix
}
else if ($a['status'] == 'w' || $a['status'] == 'p') {
  check_maintainer_rights();
  $msg = _('Approve:') . ' ' . $topic;
  $filename = substr($a['filename'], 5); // remove random prefix
}
else if ($a['status'] == 'a') {
  $msg = _('Browse:') . ' ' . $topic;
  $filename = $a['filename'];
}
else
  error(_('Access denied'));

page_begin('b');

echo html_h($msg);

// show topic path
if ($a['status'] == 'a')
  format_path($a['topic_id'], "{$cfg_site}document/list.php", true);
echo "<p>\n";

format_document($a['title'], $a['author'], $a['email'], $a['keywords'],
                $a['description'], $code, $a['info'], $a['created'],
                $a['updated'], $a['owner_id'], $a['category_id'],
                $filename, $a['size'], $a['format_id'], $a['visits'],
                $a['downloads'], $a['remote'],$a['new_filename']);

// view/download actions
$code = rawurlencode($code);
echo html_p(format_action(_('View'), "{$cfg_site}document/?view=$code") .
            '&nbsp;&nbsp;' .
            format_action(_('Download'), "{$cfg_site}document/?down=$code"));

if ($a['status'] == 'v') {
  // verify actions
  echo html_p(format_action(_('Accept this document'),
                            "{$cfg_site}document/action.php?did=$did&op=v"));
  echo html_p(format_action(_('Reject this document'),
                            "{$cfg_site}document/action.php?did=$did&op=r"));
}
else if ($a['status'] == 'w') {
  // approval actions
  echo html_p(format_action(_('Approve this document'),
                            "{$cfg_site}document/action.php?did=$did&op=a"));
  echo html_p(format_action(_('Reject this document'),
                            "{$cfg_site}document/action.php?did=$did&op=r"));
}
///////////////////////////////////EDITAR DOCUMENTO //////////////////////////////////////////
if (can_edit_document($did)) {
  // editing/removal actions
  echo html_p(format_action(_('Edit document'),
                            "{$cfg_site}document/edit.php?did=$did&code=$code&tid=$tid"));
  if ($a['status'] == 'a'){
    echo html_p(format_action(_('Remove document'),
                              "{$cfg_site}document/action.php?did=$did&op=d"));
	
	echo html_p(format_action(_('Change document'),
                              "{$cfg_site}document/put.php?did=$did&tid=$tid"));                              
  }
}

if(!empty($a['new_filename'])){ 
	echo html_p(format_action(_('Accept new document'), 
                            "{$cfg_site}document/action.php?did=$did&op=u"));
}

if(!empty($a['new_filename'])){ 
	echo html_p(format_action(_('Reject new document'),
                            "{$cfg_site}document/action.php?did=$did&op=f"));
}

echo html_p(html_small(_('All material on this system is the property and responsibility of its authors.')));

// increase visit counter
db_command("UPDATE nr_document SET visits=visits+'1' WHERE id='$did'");

// finish
if ($a['status'] == 'a')
  page_end("{$cfg_site}document/list.php?tid={$a['topic_id']}");
else
  page_end("{$cfg_site}document/manage.php");


/*-------------- functions --------------*/

function format_document ($title, $author, $email, $keywords, $description,
                          $code, $info, $created, $updated, $owner_id,
                          $category_id, $filename, $size, $format_id,
                          $visits, $downloads, $remote,$new_filename)
{
  global $cfg_site;

  format_line(_('Title'), $title);
  format_line(_('Author(s)'), $author);
  if (!empty($email)) {
    $email = convert_email($email);
    format_line(_('E-mail(s)'), html_a($email, "mailto:$email"), false);
  }
  format_block(_('Keywords'), $keywords);
  format_block(_('Description'), $description);
  if(!empty($new_filename)){
  format_block(_('New document'), 
  html_a(substr($new_filename, 5),"{$cfg_dir_incoming}$new_filename"), false );
  }
  format_line(_('Code'), $code);
  format_block(_('Additional information'), $info);
  echo "<p>\n";

  format_line(_('Owner'), html_a(get_user($owner_id, 'username'),
                                 "{$cfg_site}user/?uid=$owner_id"), false);
  format_line(_('Category'), _(get_category($category_id, 'name')));
  format_line(_('Format'), _(get_format($format_id, 'name')));
  if ($remote == 'n') {
    format_line(_('File name'), $filename);
    format_line(_('Size'), int_to_size($size) . " ($size bytes)");
    format_line(_('Time estimated for download'), ceil(int_to_size($size)/(56*60)) . _('minute(s) (56 kb/s connection speed)'));
  }
  else
    format_line(_('Address'), html_a($filename, $filename), false);
  format_line(_('Created'), db_locale_date($created));
  format_line(_('Updated'), db_locale_date($updated));
  format_line(_('Visits'), $visits);
  format_line(_('Downloads'), $downloads);
}

function view_document ($code, $force_download)
{
  global $cfg_site, $cfg_dir_archive, $cfg_dir_incoming;

  // find document
  $q = db_query("SELECT D.id,D.topic_id,D.status,D.filename,D.size,D.remote,F.type,F.subtype,F.extension,F.compress FROM nr_document D,nr_format F WHERE D.code='$code' AND D.format_id=F.id");
  if (!db_rows($q))
    error(_('Document not found'));
  $a = db_fetch_array($q);

  // increase download counter
  db_command("UPDATE nr_document SET downloads=downloads+'1' WHERE id='{$a['id']}'");

  // handle remote documents
  if ($a['remote'] == 'y')
    redirect($a['filename']);

  // check document status
  $filename = $a['filename'];
  if ($a['status'] == 'v') {
    // document needs verification
    check_administrator_rights();
    $file = "$cfg_dir_incoming/$filename";
    $compress = 'n'; // output as is
  }
  else if ($a['status'] == 'w') {
    // document waiting for approval
    check_maintainer_rights($a['topic_id']);
    $file = "$cfg_dir_incoming/$filename";
    $compress = 'n'; // output as is
  }
  else if ($a['status'] == 'a') {
    // document archived
    $file = "$cfg_dir_archive/{$a['id']}.{$a['extension']}";
    if ($a['compress'] == 'y')
      $file .= '.gz';
    $compress = $a['compress'];
  }
  else
    error(_('Access denied'));

	
	//trata caracteres estranhos
	$filename=htmlspecialchars($filename);
	
	//trata espacos
	if(ereg(" ",$filename))
		$filename=str_replace(" ","%20",$filename);
		
  // output document
  if (!$force_download) {
    header("Content-Type: {$a['type']}/{$a['subtype']}");
    header("Content-Disposition: inline; filename=$filename");
  }
  else {
    header("Content-type: application/octet-stream");
    header("Content-Disposition: attachment; filename=$filename");
  }
  if ($compress == 'y') {
    // decompress file on the fly
    header("Content-Length: {$a['size']}");
    @passthru("gzip -cd $file");
  }
  else {
    // simply output file
    header("Content-Length: {$a['size']}");
    @readfile($file);
  }

  // finish explicitly
  exit();
}

?>
