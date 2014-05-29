<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

/*-------------- directories --------------*/

// base directory for Nou-Rau files
$cfg_dir_base = '/opt/nourau';

// archive directory
$cfg_dir_archive = "$cfg_dir_base/archive";

// incoming directory
$cfg_dir_incoming = "$cfg_dir_base/incoming";

// search database directory
$cfg_dir_database = "$cfg_dir_base/htdig";

// templates and scripts directory
$cfg_dir_share = "$cfg_dir_base/share";

// temporary directory for indexing
$cfg_dir_temporary = "$cfg_dir_base/temp";


/*-------------- htdig configuration --------------*/

// name of ht://Dig configuration file to use, without '.conf'
$cfg_htdig_conf = 'nourau';

/*------------- document management ------------*/

// document needs verification (from Administrator)
$cfg_need_verify = true;

// document needs approval (from Maintainer)
$cfg_need_approval = true;

/*-------------- tools --------------*/

// location of standard UNIX utilities
$cfg_tool_cat   = '/bin/cat';
$cfg_tool_chmod = '/bin/chmod';
$cfg_tool_file  = '/usr/bin/file';
$cfg_tool_gzip  = '/bin/gzip';
$cfg_tool_rm    = '/bin/rm';
$cfg_tool_touch = '/bin/touch';

// location of indexing and search programs
$cfg_tool_htdig    = '/usr/bin/htdig';
$cfg_tool_htfuzzy  = '/usr/bin/htfuzzy';
$cfg_tool_htpurge  = '/usr/bin/htpurge';
$cfg_tool_htsearch = '/usr/lib/cgi-bin/htsearch';
$cfg_tool_htstat   = '/usr/bin/htstat';

// location of conversion tools
$cfg_tool_doc = '/usr/bin/antiword';
$cfg_tool_dvi = '/usr/bin/dvi2tty';
$cfg_tool_pdf = '/usr/bin/pdftotext';
$cfg_tool_ppt = '/usr/bin/ppthtml';
$cfg_tool_ps  = '/usr/bin/pstotext';
$cfg_tool_tex = '/usr/bin/recode';
$cfg_tool_xls = '/usr/bin/xlhtml';

?>
