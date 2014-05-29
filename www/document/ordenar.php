<?php

// NOU-RAU - Copyright (C) 2002 Instituto Vale do Futuro
// This program is free software; see COPYING for details.

require_once '../include/start.php';
require_once BASE . 'include/defs_d.php';
require_once BASE . 'include/format.php';
require_once BASE . 'include/format_d.php';
require_once BASE . 'include/format_t.php';
require_once BASE . 'include/page_d.php';
require_once BASE . 'include/util_d.php';

// validate input
if (!valid_opt_int($page) || !valid_opt_int($tid))
  error(_('Invalid parameter'));
if ($page < 1)
  $page = 1;

if(isset($btnConfirmar)){
	$tmp=array();
	$error=false;
	
	while(list($key,$value)=each($positions)){
		$x=explode("|",$value);
		if(!in_array($x[1],$tmp))
			$tmp[$x[0]]=$x[1];
		else{
			$error=true;
			
			//error('Indices duplicados',"ordenar.php?tid=$tid");
		}
	}
	if(!$error){
		while(list($key,$value)=each($tmp)){
			$x=db_simple_query("update topic set position={$tmp[$key]} where id=$key");
		}
			//exibir mensagem
			redirect("{$cfg_site}document/list.php");
			
	}
	
	
}else if(isset($btnCancel))
	redirect("{$cfg_site}document/list.php");



// start page
page_begin('b');
if (empty($tid))
  $topic = _('all main topics');
else {
  $a = get_topic($tid);
  $topic = htmlspecialchars($a['name']);
}

if (is_user())
  $msg = 'Ordenar topicos';
echo html_h($msg);
	
if($error)
	format_warning('Indices duplicados');

// show topic path
format_path($tid, "{$cfg_site}document/list.php", false);

  echo "<p>\n";

order($tid,$tmp);
	

echo html_p(html_small(_('All material on this system is the property and responsibility of its authors.')));
$PHP_SELF = $_SERVER['PHP_SELF'];
page_end($PHP_SELF . (($pid) ? "?tid=$pid" : ''));


/*-------------- functions --------------*/

function order($parent,$indices=''){
	global $tid;
	$PHP_SELF = $_SERVER['PHP_SELF'];
	if(empty($parent))
		$parent=0;
	$a=db_query("select id,name,description,position from topic where parent_id=$parent order by position");
	$qtde=pg_num_rows($a);
	
	if($qtde>1){
		html_form_begin("$PHP_SELF",false);
  		html_table_begin(false, 'right', true);
  		html_table_item(html_b('Topicos'), 'title');
  		if(!isset($indices))
	  		while (list($id,$name,$description,$position) = pg_fetch_array($a)) {
	    		html_table_row_begin();
		    	html_table_row_item($name,'', '30%');
		    	html_table_row_item($description,'left', '55%');
		    	html_table_row_item(create_select($id,$qtde,"$id|$position"),'','15%');
		    	html_table_row_end();
		  	}
		else
			while (list($id,$name,$description,$position) = pg_fetch_array($a)) {
	    		html_table_row_begin();
		    	html_table_row_item($name,'', '30%');
		    	html_table_row_item($description,'left', '55%');
		    	html_table_row_item(create_select($id,$qtde,"$id|{$indices[$id]}"),'','15%');
		    	html_table_row_end();
		  	}
	  	html_table_end();
	  	html_form_hidden ('tid', $tid);
	  	echo "<br><div align=\"right\">";
	  	html_form_submit ('Ok', $name = 'btnConfirmar');
	  	html_form_submit ('Cancelar', $name = 'btnCancelar');
	  	echo "</div>";
	  	
	  	
	  	html_form_end();
		echo "<p>\n";	
	}else{
		echo html_h('N?o foram encontrados topicos');		
	}
}

function create_options($id,$qtde){
	$a=array();
	for($i=1;$i<=$qtde;$i++)
		array_push($a,"$id|$i");
	return $a;

}

function create_select($id,$qtde,$selected){
	$x="<div align=\"right\">\n <select name=\"positions[]\">\n";
	$a=create_options($id,$qtde);
	while(list($key,$value)=each($a)){
		$key++;
		if($value==$selected)
			$x.="<option value=\"$value\" selected>$key</option>\n";
		else	 
			$x.="<option value=\"$value\">$key</option>\n";
	}
	$x.="</select>\n</div>";	
	return $x;
}

?>
