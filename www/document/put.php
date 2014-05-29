<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

require_once '../include/start.php';
require_once BASE . 'include/defs_d.php';
require_once BASE . 'include/page_d.php';
require_once BASE . 'include/format.php';
require_once BASE . 'include/util_d.php';

if (isset($_GET['tid'])) {

	$tid=$_GET['tid'];
}

if (isset($_POST['tid'])) {

        $tid=$_POST['tid'];
}

if (isset($_POST['cid'])) {

        $cid=$_POST['cid'];
}

if (isset($_FILES['file']['name'])) {
	$file_name=$_FILES['file']['name'];
	$file_size=$_FILES['file']['size'];
	$file_type=$_FILES['file']['type'];
        $file=$_FILES['file']['tmp_name'];

}

if (isset($_POST['sent'])) {
	$sent=$_POST['sent'];
}
//var_export($_POST);
//var_export($_FILES);
// validate input
if (!valid_opt_int($cid) || !valid_int($tid))
  error(_('Invalid parameter'));

// check access rights
check_user_rights();

// filter input
$file = trim($file);
$file_type = trim($file_type);

// validate input
$topic = htmlspecialchars(get_topic($tid, 'name'));
if (empty($sent)){
 	if(!empty($did)){
  		form('',$did);
  	}else{
  		form();
  	}
}

if (empty($cid)){
	if(!empty($did)){
  		form(_('Please choose a category'),$did);
	}else{
		form(_('Please choose a category'));
	}
}


if (empty($file) || $file == 'none' || !file_exists($file)){
	if(!empty($did)){
  		form(_('File not found'),$did);
	}else{
		form(_('File not found'));
	}
}
// check file size
$max = get_category($cid, 'max_size');
if ($max && $file_size > $max)
  form(_('The file size (@1 bytes) exceeded the maximum permitted', $file_size));

// find matching format
if (empty($file_type)) {
  // empty, use 'file' utility
  $a = split("[ \t,;]", exec("$cfg_tool_file -bi -m $cfg_dir_share/mime.magic $file"));
  $file_type = $a[0];
  $fid = find_format($cid, $file_type);
}
else {
  $fid = find_format($cid, $file_type);
  if($cid==7 && empty($fid)) $fid=7; //modify to accepty any document
  
  if (!$fid) {
    // try again using 'file'
    $a = split("[ \t,;]", exec("$cfg_tool_file -bi -m $cfg_dir_share/mime.magic $file"));
    $fid = find_format($cid, $a[0]);
  }
}
if (!$fid)
  form(_M("File type '@1' is not accepted in this category", $file_type));

// move file into incoming directory
chmod($file, 0644);
$file_name = random_string(4) . '-' . basename($file_name);
copy($file, "$cfg_dir_incoming/$file_name");

if(!isset($did)){
  // insert document
  db_command("INSERT INTO nr_document (topic_id,owner_id,category_id,filename,size,format_id) VALUES ('$tid','{$_SESSION['uid']}','$cid','$file_name','$file_size','$fid')");
  $did = db_simple_query("SELECT CURRVAL('nr_document_seq')");
  add_log('n', 'di', "did=$did&from=$REMOTE_ADDR $HTTP_USER_AGENT");
  redirect("{$cfg_site}document/edit.php?did=$did");
}else{
//verify if one document was submit, but not approved. Is yes, delete, the new document will replace it.
verifyDoc($cfg_dir_incoming,$did);

//alert about new document
  if (get_format($fid, 'verify') == 'y' && $cfg_need_verify) {
  	
    send_mail(get_user(1, 'email'), _('Document to be verified'), _M("The document '@1' in the directory '@2' needs verification.", $file_name, $cfg_dir_incoming));
  db_command("update nr_document set status='u', new_filename='$file_name', new_size='$file_size', new_format_id='$fid' where id='$did'");

  }
  // check if it needs to be approved
  else if ($cfg_need_approval && $mid!=$_SESSION['uid'] && $_SESSION['uid']!=1) {
  	
  	$a = get_document($did); 
    $q2 = db_query("SELECT U.email,T.name FROM users U,topic T WHERE U.id=T.maintainer_id AND T.id='{$a['topic_id']}'");
    $a2 = db_fetch_array($q2);
    $topic = $a2['name'];
    send_mail($a2['email'], _('Document received'), _M("The document with title '@1' was received on the topic '@2'.", $title, $topic));
    db_command("update nr_document set status='p',new_filename='$file_name', new_size='$file_size', new_format_id='$fid' where id='$did'");
  }
  // archive immediately
  else {
	
	deleteDoc($cfg_dir_archive,$did);
      // move file to archive, renaming it and compressing if necessary
      $q2 = db_query("SELECT extension,compress FROM nr_format WHERE id='$fid'");
      $a2 = db_fetch_array($q2);
      $old = "$cfg_dir_incoming/$file_name";
      $new = "$cfg_dir_archive/$did.{$a2['extension']}";
      if (!@rename($old, $new)){
          error(_('Rename failed')); // FIXME: notify admin?
      }
      if ($a2['compress'] == 'y') {
          // compress it
          exec("gzip -9 $new");
      }
	$filename = substr($file_name, 5); // remove random prefix
	db_command("UPDATE nr_document set status='a',filename='$filename',size='$file_size', category_id=$cid,format_id='$fid',updated='now', new_filename='',new_size=0 where id=$did");
	add_log('n', 'di', "did=$did&from=$REMOTE_ADDR $HTTP_USER_AGENT");
                                                                                                    
 }


if ($cfg_need_approval) {
    add_log('n', 'dc', "did=$did");
    message(_('Document received'), "{$cfg_site}document/list.php?tid=$tid");
}
else {
  add_log('n', 'du', "did=$did");
  message(_('Document updated'), "{$cfg_site}document/?code=$did&tid=$tid");
}

}

/*-------------- functions --------------*/

function find_format ($cid, $file_type)
{
  $type = explode('/', $file_type);
  $match = 0;
  $q = db_query("SELECT C.id,C.type,C.subtype FROM nr_format C,nr_category_format CC WHERE CC.category_id='$cid' AND CC.format_id=C.id");
  while ($a = db_fetch_array($q)) { 
    if ($a['type'] == 'any') { 
      // match against all types
      $q2 = db_query("SELECT id,type,subtype FROM nr_format WHERE subtype<>'any'");
      while ($a2 = db_fetch_array($q2))
        if (!strcasecmp($a2['type'], $type[0]) &&
            !strcasecmp($a2['subtype'], $type[1])) {
          $match = $a2['id'];
          break;
        }
    }
    else if ($a['subtype'] == 'any') {
      // match against all subtypes with the given type
      $q2 = db_query("SELECT id,type,subtype FROM nr_format WHERE type='{$a['type']}' AND subtype<>'any'");
      while ($a2 = db_fetch_array($q2))
        if (!strcasecmp($a2['type'], $type[0]) &&
            !strcasecmp($a2['subtype'], $type[1])) {
          $match = $a2['id'];
          break;
        }
    }
    else {
      // match against specified type and subtype
      if (!strcasecmp($a['type'], $type[0]) &&
          !strcasecmp($a['subtype'], $type[1]))
        $match = $a['id'];
    }
    if ($match)
      break;
  }
  return $match;
}

function form ($msg = "",$docid="")
{
  global $cfg_site;
  global $cid, $tid, $topic;
  $PHP_SELF = $_SERVER['PHP_SELF'];
  page_begin('b');

  echo html_h(_('Archive document into:') . ' ' . $topic);
  format_warning($msg);

  $q = db_query("SELECT C.id,C.name,C.description,C.max_size FROM nr_category C,nr_topic_category TC WHERE TC.topic_id='$tid' AND TC.category_id=C.id ORDER BY name");
  if (db_rows($q)) {
    echo _('Select one of the available categories and specify the file to be sent.');
    echo "<p>\n";

    if(empty($docid)){
    	html_form_begin("$PHP_SELF?tid=$tid", true, 'multipart/form-data');
    }else{
    	html_form_begin("$PHP_SELF?tid=$tid&did=$docid&edit=1", true, 'multipart/form-data');
    }

    if(!empty($docid)){
      echo "Documento atual: <b>".nameDoc($docid)."</b><br><br>\n";
    }

    $opt = array();
    $opt['0'] = _('-- choose one of these categories --');
    while ($a = db_fetch_array($q)) {
      $tmp = _($a['name']) . ' - ' . _($a['description']);
      if ($a['max_size'])
        $tmp .= ' (' . _('maximum') . ' ' . int_to_size($a['max_size']) . ')';
      $opt[$a['id']] = $tmp;
    }
    html_form_select(_('Category'), 'cid', $opt, $cid);
    echo "<p>\n";

    html_form_file(_('Document'), 'file');
    echo "<p>\n";

    echo _('By submitting this file you agree that it will be made publically available for downloads.');
    echo "<p>\n";

    html_form_submit(_('Send'), 'sent');
    html_form_end();
    echo "<p>\n";
  }
  else
    echo html_p(html_b(_('This topic does not accept document submissions')));

  page_end("{$cfg_site}document/list.php?tid=$tid");
  exit();
}

function nameDoc($docid){
	$q=db_query("SELECT filename FROM nr_document WHERE id=$docid");
	$a=db_fetch_array($q);
	return $a['filename'];

}


function verifyDoc($caminho,$docID){
	global $cfg_site;	
	$q=db_query("select new_filename from nr_document where id='$docID'");
	$a=pg_fetch_array($q);
	if(!empty($a['new_filename'])){
		unlink("$caminho/".$a['new_filename']);
	}
}

function deleteDoc($cfg_dir_archive,$did){
	@unlink("$cfg_dir_archive/$did.*");
}



?>

