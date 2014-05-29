<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

require_once '../include/start.php';
require_once BASE . 'include/defs_d.php';
require_once BASE . 'include/format_d.php';
require_once BASE . 'include/page_d.php';

page_begin('s');

echo html_h(_('Search: all topics'));

if (empty($adv))
  echo _('To find documents containing one or more words, fill the following field:');
else
  echo _('To find documents containing one or more words, fill the following fields:');
echo "<p>\n";

format_search_box();
echo "<p>\n";

echo _('To access a document given its code, just indicate it below:');
echo "<p>\n";

html_form_begin("{$cfg_site}document/", false);
html_form_text(_('Code'), 'code', 20, '', 50, false);
html_form_submit(_('Access'));
html_form_end();

page_end();

?>
