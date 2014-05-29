<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

/*-------------- includes --------------*/

require_once BASE . 'include/page.php';


/*-------------- page functions --------------*/

function page_begin ($title = '')
{
  global $print;

  page_begin_aux($title);
  if ($print == 'y')
    return;
  page_menu_begin('u');
  page_menu_end();
}

?>
