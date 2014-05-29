<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

/*-------------- database --------------*/

// name of PostgreSQL database
$cfg_base = 'nourau';

// name of PostgreSQL user (and password, if needed)
$cfg_user = 'rafael';
$cfg_pass = 'rafael';


/*-------------- site --------------*/

// site base address
//$cfg_site = 'http://localhost:8888/~rafael/nourau/www/';
$cfg_site = 'http://beijing.dnsalias.org:8880/nourau/www/';


// site title
$cfg_site_title = 'Sistema Novo Nou-Rau';

// user registration mode:
//   [open]       anyone can create a new user
//   [moderated]  new users must be approved by the administrator
//   [closed]     only the administrator can add new users
//
$cfg_reg_mode = 'closed';

// should verify user e-mail when registering? (only for open mode)
$cfg_reg_verify_email = true;

// should users specify the motive for registering? (only for moderated mode)
$cfg_reg_ask_motive = true;


/*-------------- e-mail --------------*/

// webmaster contact e-mail
$cfg_webmaster = 'rafaelperazzo@gmail.com';

// subject tag for outgoing e-mail
$cfg_subject_tag = 'nou-rau';

// should redirect all outgoing e-mail to webmaster?
//$cfg_redirect_emails = false;
$cfg_redirect_emails = true;

// should obfuscate all e-mail addresses shown to prevent SPAM?
$cfg_obfuscate_emails = true;


/*-------------- language --------------*/

// language of choice
//$cfg_language = 'en_US';
$cfg_language = 'pt_BR';

// uncomment this if PHP does not have gettext support
//require_once BASE . 'include/gettext.php';


/*-------------- layout --------------*/

// banner settings
$cfg_banner            = "{$cfg_site}images/banner_nr.gif";
$cfg_banner_background = '';
$cfg_banner_color      = '#00b844';
$cfg_banner_url        = $cfg_site;


/*-------------- miscellaneous --------------*/

// cookie name where session id is stored
$cfg_session_name = $cfg_base;

// uncomment this to go offline
//define('HALT', true);

//need approval for documents?
$cfg_need_approval=false;



?>
