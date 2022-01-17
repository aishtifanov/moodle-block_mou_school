<?php // $Id: textbooks_d.php,v 1.6 2010/08/23 08:48:06 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../lib_school.php');
	require_once('../authbase.inc.php');

    switch ($action)	{
    	case 'excel': $table = table_textbooks ($yid, $rid, $sid);
    				  // print_r($table);
        			  print_table_to_excel($table,1);
        			  exit();
        			  
		case 'clear': $did = optional_param('did', '0', PARAM_INT);
					  if ($did != 0) 	{
			        	// if (!set_field('monit_school_textbook', 'textbooksids', '', 'yearid', $yid , 'schoolid', $sid, 'discegeid', $did))  {
						if (!delete_records('monit_school_textbook', 'yearid', $yid , 'schoolid', $sid, 'disciplineid', $did))  {
             				notify("Could not update the school textbook record.");
        				}
					  }
					  break;
	}				  


    $currenttab = 'textbook';
    include('tabsdis.php');

	if (has_capability('block/mou_school:viewdiscipline', $context))	{
		$table = table_textbooks ($yid, $rid, $sid);
	    print_color_table($table);
	} else {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}
    

    print_footer();



function table_textbooks ($yid, $rid, $sid)
{
	global $CFG, $context;
	
	$edit_capability = has_capability('block/mou_school:editdiscipline', $context);

	$disciplines =  get_records_sql ("SELECT id, name  FROM  {$CFG->prefix}monit_school_discipline
									  WHERE schoolid=$sid ORDER BY name");
    $arr_count = array();
	if ($disciplines)	{
		foreach ($disciplines as $discipline) 	{
        	  $arr_count[$discipline->id] = 0;
		}
	}

	$table->head  = array (get_string('predmet','block_mou_school'), get_string("textbooks","block_mou_ege"), get_string("action","block_mou_ege"));
	$table->align = array ("left", "left", "center");
    $table->class = 'moutable';
   	$table->width = '90%';
    $table->size = array ('10%', '80%', '10%');

	$strlowclass = get_string('lowclass', 'block_mou_ege');

	if ($disciplines) foreach ($disciplines as $discipline) 	{

		$arr_egeids = array();
        if ($schooltextbooks =  get_record('monit_school_textbook',  'yearid', $yid , 'schoolid', $sid, 'disciplineid', $discipline->id))  {
		    $arr_egeids = explode(',', $schooltextbooks->textbooksids);
		}

		$strtextbooks = '';
		    if (!empty($schooltextbooks->textbooksids))	{
		    	$tbids = explode(',', $schooltextbooks->textbooksids);
		    	$i = 0;
		    	foreach ($tbids as $tbid)	{
		    		if ($tbid > 0)	{
		    		    if ($textbook = get_record ('monit_textbook',  'id', $tbid))	{
				    		$strtextbooks .= ++$i.'. ' .$textbook->authors .' '. $textbook->name .'. - '. $textbook->publisher . ' (' . $textbook->numclass . ' '. $strlowclass . ')<br>';
				    	}
			    	}
		    	}
		    	/*
		    	if ($textbooks != '')  {
		    		$textbooks = substr($textbooks, 0, strlen($textbooks)- 2);
		    	} */

		    }
		    if ($strtextbooks == '')  $strtextbooks = '-';


		if ($edit_capability)	{
			$title = get_string('change_textbooks_school','block_mou_ege');
			$strlinkupdate = "<a title=\"$title\" href=\"edittextbook_d.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;did={$discipline->id}\">";
			$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";

			$title = get_string('clear_textbooks_school','block_mou_ege');
			$strlinkupdate .= "<a title=\"$title\" href=\"textbooks_d.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;did={$discipline->id}&amp;action=clear\">";
			$strlinkupdate .=  "<img src=\"{$CFG->wwwroot}/blocks/mou_ege/i/goom.gif\" alt=\"$title\" /></a>&nbsp;";
		} else {
			$strlinkupdate = '-';
		}				

		$table->data[] = array ($discipline->name, $strtextbooks, $strlinkupdate);
	}
		
    return $table;
}

?>