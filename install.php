<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

require_once 'www/include/db.php';
//print($_ENV['PATH']);
if (empty($_ENV['BASE']))
  fatal('environment variable BASE must be set');
$cfg_base = $_ENV['BASE'];
$cfg_user = $_ENV['USER'];
//print($_ENV['USER']);
if (empty($_ENV['USER']))
  fatal('environment variable USER must be set');
$new_user = $_ENV['USER'];

if (empty($_ENV['LANG']))
  fatal('environment variable LANG must be set');
$cfg_language = $_ENV['LANG'];
print("Antes do db_connect \n");
db_connect();
print("Depois do db_connect \n");

if (!db_simple_query("SELECT COUNT(*) FROM pg_tables WHERE tablename='version'")) {
  if (empty($_ENV['PSQL']))
    $PSQL = 'psql';
  else
    $PSQL = $_ENV['PSQL'];
  // create common schema
  exec("$PSQL -q -U $new_user -f schema.sql -d ".$_ENV['BASE']." 2>&1", $output, $result);
  if ($result)
    fatal("can't run '$PSQL'");
  exec("$PSQL -q -U $new_user -f reset.sql -d ".$_ENV['BASE']." 2>&1", $output, $result);
  if ($result)
    fatal("can't run '$PSQL'");

  // create Nou-Rau schema
  exec("$PSQL -q -U $new_user -f schema_nr.sql -d ".$_ENV['BASE']." 2>&1", $output, $result);
  if ($result)
    fatal("can't run '$PSQL'");
  exec("$PSQL -q -U $new_user -f reset_nr.sql -d ".$_ENV['BASE']." 2>&1", $output, $result);
  if ($result)
    fatal("can't run '$PSQL'");

  // grant access to new user
  $seq = array('notice_seq',
               'topic_seq',
               'users_seq',
               'nr_category_seq',
               'nr_format_seq',
               'nr_document_seq',
	       'position_seq'
               );
  foreach ($seq as $s)
    db_command("GRANT ALL PRIVILEGES ON $s TO $new_user");
  $tab = array('log',
               'notice',
               'topic',
               'topic_path',
               'users',
               'user_registration',
               'version',
               'nr_category',
               'nr_category_format',
               'nr_document',
               'nr_document_queue',
               'nr_format',
               'nr_htdig_status',
               'nr_topic_category',
               'nr_version',
               );
  foreach ($tab as $t)
    db_command("GRANT ALL PRIVILEGES ON $t TO $new_user");

  echo "database installed\n";
  return;
}

if (db_simple_query("SELECT schema FROM nr_version") == '1.0.0') {
  // upgrade to schema FIXME

  db_command("CREATE SEQUENCE notice_seq MINVALUE 0");
  $max = db_simple_query("SELECT MAX(id) FROM Notice");
  $max = (empty($max)) ? 0 : $max;
  db_command("SELECT SETVAL('notice_seq', $max)");
  db_command("ALTER TABLE Notice ALTER COLUMN id SET DEFAULT NEXTVAL('notice_seq')");

  db_command("CREATE SEQUENCE question_seq MINVALUE 0");
  $max = db_simple_query("SELECT MAX(id) FROM Question");
  $max = (empty($max)) ? 0 : $max;
  db_command("SELECT SETVAL('question_seq', $max)");
  db_command("ALTER TABLE Question ALTER COLUMN id SET DEFAULT NEXTVAL('question_seq')");

  db_command("CREATE SEQUENCE topic_seq MINVALUE 0");
  $max = db_simple_query("SELECT MAX(id) FROM Topic");
  $max = (empty($max)) ? 0 : $max;
  db_command("SELECT SETVAL('topic_seq', $max)");
  db_command("ALTER TABLE Topic ALTER COLUMN id SET DEFAULT NEXTVAL('topic_seq')");

  db_command("CREATE SEQUENCE position_seq MINVALUE 0");
  $max = db_simple_query("SELECT MAX(position) FROM Topic");
  $max = (empty($max)) ? 0 : $max;
  db_command("SELECT SETVAL('position_seq', $max)");
  db_command("ALTER TABLE Topic ALTER COLUMN position SET DEFAULT NEXTVAL('position_seq')");
	  
  db_command("CREATE SEQUENCE users_seq MINVALUE 0");
  $max = db_simple_query("SELECT MAX(id) FROM Users");
  $max = (empty($max)) ? 0 : $max;
  db_command("SELECT SETVAL('users_seq', $max)");
  db_command("ALTER TABLE Users ALTER COLUMN id SET DEFAULT NEXTVAL('users_seq')");

  db_command("CREATE TABLE Version (schema varchar(8))");
  db_command("INSERT INTO Version (schema) VALUES ('1.0.3')");

  echo "database upgraded to schema 1.0.3\n";
}
else {
  echo "database is up to date\n";
}


/*-------------- functions --------------*/

function fatal ($msg)
{
  echo "fatal: $msg\n";
  exit(1);
}

?>
