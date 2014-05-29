<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

define('OFFLINE', true);

require_once '../include/start.php';
require_once BASE . 'include/defs_d.php';
require_once BASE . 'include/htdig.php';

// update search index
rundig();

?>
