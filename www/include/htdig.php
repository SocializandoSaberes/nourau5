<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

/*-------------- includes --------------*/

require_once BASE . 'include/defs_d.php';
require_once BASE . 'include/html.php';
require_once BASE . 'include/page.php';


/*-------------- ht://Dig functions --------------*/

function rundig ($rebuild = false)
{
  global $cfg_dir_archive, $cfg_dir_base, $cfg_dir_database, $cfg_dir_share,
    $cfg_dir_temporary, $cfg_htdig_conf;
  global $cfg_tool_cat, $cfg_tool_chmod, $cfg_tool_doc, $cfg_tool_dvi,
    $cfg_tool_gzip, $cfg_tool_htdig, $cfg_tool_htfuzzy, $cfg_tool_htpurge,
    $cfg_tool_pdf, $cfg_tool_ppt, $cfg_tool_ps, $cfg_tool_rm, $cfg_tool_tex,
    $cfg_tool_touch, $cfg_tool_xls;

  // obtain lock
  if (!db_command("UPDATE nr_htdig_status SET running='y' WHERE running='n'")) {
    if (!defined('OFFLINE'))
      error(_('ht://Dig already running'));
    else
      return;
  }

  if ($rebuild) {
    // insert all documents into queue
    $q = db_query("SELECT id FROM nr_document WHERE status='a'");
    while ($a = db_fetch_array($q))
      db_command("INSERT INTO nr_document_queue (op,document_id) VALUES ('u','{$a['id']}')");

    // clean up work database
    if (!defined('OFFLINE')) {
      echo html_b(_('cleaning...')) . "<br>\n";
      flush();
    }
    @unlink("$cfg_dir_database/db.docdb.work");
    @unlink("$cfg_dir_database/db.excerpts.work");
    @unlink("$cfg_dir_database/db.docs.index.work");
    @unlink("$cfg_dir_database/db.words.db.work");
    @unlink("$cfg_dir_database/db.words.db.work_weakcmpr");
  }

  // process documents recently updated
  db_command("UPDATE nr_document_queue SET op='-' WHERE op='u'");
  $q = db_query("SELECT DISTINCT D.id,D.title,D.author,D.email,D.keywords,D.description,D.code,D.info,D.topic_id,D.filename,to_char(D.updated,'YYYY-MM-DD HH24:MI') AS updated,D.remote,F.type,F.subtype,F.extension,F.icon,F.compress FROM nr_document_queue Q,nr_document D,nr_format F WHERE Q.op='-' AND Q.document_id=D.id AND D.status='a' AND D.format_id=F.id");
  $upd = db_rows($q);
  if ($upd) {
    // convert each document into a temporary HTML file
    if (!defined('OFFLINE')) {
      echo html_b(_('converting...')) . "<br>\n";
      flush();
    }
    $urls = '';
    putenv("XCAT=$cfg_tool_cat");
    putenv("XGZIP=$cfg_tool_gzip");
    putenv("XTOUCH=$cfg_tool_touch");
    putenv("XDOC=$cfg_tool_doc");
    putenv("XDVI=$cfg_tool_dvi");
    putenv("XPDF=$cfg_tool_pdf");
    putenv("XPPT=$cfg_tool_ppt");
    putenv("XPS=$cfg_tool_ps");
    putenv("XTEX=$cfg_tool_tex");
    putenv("XXLS=$cfg_tool_xls");
    while ($a = db_fetch_array($q)) {
      $title = htmlspecialchars($a['title']);
      $keywords = htmlspecialchars(strtr($a['keywords'], ',', ' '));
      $description = htmlspecialchars($a['author'] . "\n" .
                                      $a['email'] . "\n" .
                                      $a['description'] . "\n" .
                                      $a['code'] . "\n" .
                                      $a['info'] . "\n" .
                                      $a['filename']);
      $updated = $a['updated'];

      putenv("TITLE=$title");
      putenv("KEYWORDS=$keywords");
      putenv("DESCRIPTION=$description");
      putenv("MODIFIED=$updated");
      if ($a['remote'] == 'n') {
        // local file
        $format = $a['type'] . '/' . $a['subtype'];
        $file = "$cfg_dir_archive/{$a['id']}.{$a['extension']}";
        if ($a['compress'] == 'y')
          $file .= '.gz';
      }
      else {
        // remote file
        $file = $format = 'none';
      }

      // add temporary file to an URL list to be passed to ht://Dig
      $tmp = "$cfg_dir_temporary/{$a['topic_id']}.{$a['id']}.{$a['icon']}.html";
      $urls .= "file://$tmp\n";
      if (!defined('OFFLINE')) {
        echo '&nbsp;' . htmlspecialchars($a['code']) . "&nbsp;\n";
        flush();
      }

      // call converter script
      $result = array();
      exec("$cfg_dir_share/convert.pl $format $file $tmp 2>&1", $result);
      if (count($result)) {
        // some error occurred during conversion
        $result = substr(implode(' ', $result), 0, 100);
        if (!defined('OFFLINE')) {
          echo html_error($result) . "<br>\n";
          flush();
        }
        $result = addslashes($result);
        add_log('n', 'ie', "did={$a['id']}&result=$result", 'e');
      }
      else
        if (!defined('OFFLINE')) {
          echo "<b>ok</b><br>\n";
          flush();
        }
    }

    // update search database
    if (!defined('OFFLINE')) {
      echo html_b(_('indexing...')) . "<br>\n";
      flush();
    }
    $list = "$cfg_dir_temporary/htdig.url";
    $out = fopen($list, 'w');
    fwrite($out, $urls);
    fclose($out);
    $continue2=false;
    if (!defined('OFFLINE')) {
      $pipe = popen("$cfg_tool_htdig -a -c $cfg_dir_base/$cfg_htdig_conf.conf -m $list -v", 'r');
      $n = 0;
      while (!feof($pipe)) {
        fgets($pipe, 1024);
        $n++;
        echo ".\n";
        flush();
      }
      echo "<br>\n";
      flush();
      pclose($pipe);
    }
    else
      exec("$cfg_tool_htdig -a -c $cfg_dir_base/$cfg_htdig_conf.conf -m $list");

    // dequeue processed documents
    db_command("DELETE FROM nr_document_queue WHERE op='-'");

    // continue2 database update
    $continue2 = true;
  }

  // process documents recently removed
  db_command("UPDATE nr_document_queue SET op='-' WHERE op='d'");
  $q = db_query("SELECT DISTINCT D.id,D.code,D.topic_id,D.remote,F.icon FROM nr_document_queue Q,nr_document D,nr_format F WHERE Q.op='-' AND Q.document_id=D.id AND D.status='d' AND D.format_id=F.id");
  $rem = db_rows($q);
  if ($rem || $continue2) {
    if (!defined('OFFLINE')) {
      echo html_b(_('removing...')) . "<br>\n";
      flush();
    }
    $urls = '';
    while ($a = db_fetch_array($q)) {
      // really remove document from table
      db_command("DELETE FROM nr_document WHERE id='{$a['id']}'");

      // add temporary file to a URL list to be passed to htpurge
      $tmp = "$cfg_dir_temporary/{$a['topic_id']}.{$a['id']}.{$a['icon']}.html";
      $urls .= "file://$tmp\n";
      if (!defined('OFFLINE')) {
        echo '&nbsp;' . htmlspecialchars($a['code']) . "&nbsp;<b>ok</b><br>\n";
        flush();
      }
    }

    // purge old documents from search database
    $list = "$cfg_dir_temporary/htpurge.url";
    $out = fopen($list, 'w');
    fwrite($out, $urls);
    fclose($out);
    exec("$cfg_tool_htpurge - -a -c $cfg_dir_base/$cfg_htdig_conf.conf < $list");

    // install updated databases
    if (!defined('OFFLINE')) {
      echo html_b(_('installing...')) . "<br>\n";
      flush();
    }
    copy("$cfg_dir_database/db.docdb.work",
         "$cfg_dir_database/db.docdb");
    copy("$cfg_dir_database/db.excerpts.work",
         "$cfg_dir_database/db.excerpts");
    copy("$cfg_dir_database/db.docs.index.work",
         "$cfg_dir_database/db.docs.index");
    copy("$cfg_dir_database/db.words.db.work",
         "$cfg_dir_database/db.words.db");
    copy("$cfg_dir_database/db.words.db.work_weakcmpr",
         "$cfg_dir_database/db.words.db_weakcmpr");

    // update accents dir_database
    if (!defined('OFFLINE')) {
      echo html_b(_('updating...')) . "<br>\n";
      flush();
    }
    exec("$cfg_tool_htfuzzy -c $cfg_dir_base/$cfg_htdig_conf.conf accents");

    // set files read/writable
    exec("$cfg_tool_chmod 666 $cfg_dir_database/*");
    // dequeue processed documents
    db_command("DELETE FROM nr_document_queue WHERE op='-'");

    // remove temporary files
    //exec("$cfg_tool_rm -f $cfg_dir_temporary/*");

    // update timestamp
    db_command("UPDATE nr_htdig_status SET updated='now'");

    // add log entry
    if ($rebuild)
      add_log('n', 'ir', "n=$upd+$rem");
    else
      add_log('n', 'iu', "n=$upd+$rem");
  }

  // finish
  if (!defined('OFFLINE')) {
    echo html_b(_('finished!'));
    if (($upd + $rem) == 1)
      echo html_p(html_b(_('1 document processed')));
    else
      echo html_p(html_b(_M('@1 documents processed', $upd + $rem)));
  }

  // release lock
  db_command("UPDATE nr_htdig_status SET running='n'");
}

function show_stats ()
{
  global $cfg_dir_base, $cfg_htdig_conf, $cfg_tool_htstat;

  if (empty($cfg_tool_htstat))
    error(_("\$cfg_tool_htstat must be defined in the 'config_d.php' file"));

  echo html_b(_('collecting...')) . "<br>\n";
  flush();
  $pipe = popen("$cfg_tool_htstat -c $cfg_dir_base/$cfg_htdig_conf.conf", 'r');
  while (!feof($pipe)) {
    $line = fgets($pipe, 1024);
    if (!empty($line))
      echo substr($line, 8) . "<br>\n";
    flush();
  }
  pclose($pipe);
  add_log('n', 'is');
  echo html_b(_('finished!'));
}

?>
