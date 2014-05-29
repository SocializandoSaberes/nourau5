<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

/*-------------- includes --------------*/

require_once BASE . 'include/page.php';


/*-------------- page functions --------------*/

function page_begin ($action = '', $title = '')
{
  global $cfg_site;
  global $print;

  page_begin_aux($title);
  if ($print == 'y')
    return;
  page_menu_begin('d');

  // action colors
  $bro = ($action == 'b') ? 'even' : 'odd';
  $sea = ($action == 's') ? 'even' : 'odd';
  $sta = ($action == 't') ? 'even' : 'odd';
  $man = ($action == 'm') ? 'even' : 'odd';

  // highlight if necessary
  if (document_pending())
    $man = 'hilite';

  // action box
  html_table_begin(true);
  html_table_item(html_b(_('Actions')), 'title');
  if (!is_user())
    html_table_item(html_a(_('Browse'), "{$cfg_site}document/list.php"), $bro);
  else
    html_table_item(html_a(_('Browse/Archive'),
                           "{$cfg_site}document/list.php"), $bro);
  html_table_item(html_a(_('Search'), "{$cfg_site}document/search.php"), $sea);
  html_table_item(html_a(_('Show statistics'),
                         "{$cfg_site}document/stats.php"), $sta);
  //if (is_user()) {
  //  html_table_item(html_a('Atualizar', 'FIXME'));
  //}
  
  /* ALTERAÇÃO - RAFAEL: Removi a opção de Gerenciamento. */
  if (is_maintainer())
    html_table_item(html_a(_('Manage'),
                           "{$cfg_site}document/manage.php"), $man);
  html_table_end();
  echo "<p>\n";

  page_menu_end();
}

?>
