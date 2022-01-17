<?php // $Id: studyperiod.php,v 1.17 2010/08/23 08:48:07 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_school.php');    
	require_once('../authbase.inc.php');


	$currenttab = 'studyperiod';
    include('tabsup.php');

	if (has_capability('block/mou_school:viewtypestudyperiod', $context))	{
	   	$table = table_studyperiod ($yid, $rid, $sid);
	
	   	if (isset($table->data))	{
	 		print_color_table($table);
		} else {
			notice(get_string('notfoundtypestudyperiod', 'block_mou_school'), "typestudyperiod.php?rid=$rid&amp;sid=$sid&amp;yid=$yid");
		}
	}	else {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}

    print_footer();

	// print_heading(get_string('studyperiod', 'block_mou_school'), 'center');
	

function table_studyperiod ($yid, $rid, $sid)
{
	global $CFG, $context;

	$edit_capability = has_capability('block/mou_school:edittypestudyperiod', $context);
	
	$table->head  = array (	get_string('name', 'block_mou_school'), get_string('timestart', 'block_mou_school'),
							get_string('timeend', 'block_mou_school'), get_string('action', 'block_mou_school'));
    $table->align = array ("left", "center", "center", "center");
    $table->class = 'moutable';
    $table->size = array('15%', '10%', '10%', '5%');
   	$table->width = '60%';

	$terms = get_records_sql ("SELECT * FROM {$CFG->prefix}monit_school_term
								  WHERE schoolid=$sid AND yearid=$yid
								  ORDER BY termtypeid");

	if ($terms)	{
		foreach ($terms as $term) {

			if ($edit_capability)	{
				$title = get_string('editperiod','block_mou_school');
				$strlinkupdate = "<a title=\"$title\" href=\"addperiod.php?mode=edit&amp;yid=$yid&amp;sid=$sid&amp;rid=$rid&amp;tid={$term->id}\">";
				$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";
				/*
				$title = get_string('deleteperiod','block_mou_school');
			    $strlinkupdate = $strlinkupdate . "<a title=\"$title\" href=\"delcurriculum.php?sid=$sid&amp;cid={$curr->id}\">";
				$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/t/delete.gif\" alt=\"$title\" /></a>&nbsp;";
				*/
				$title = get_string('disciplines','block_mou_school');
				$strdiscipline = $term->name;
			}
			else	{
				$strlinkupdate = '-';
				$strdiscipline = $term->name;
			}

			$table->data[] = array ($strdiscipline, convert_date($term->datestart, 'en', 'ru'),
									convert_date($term->dateend, 'en', 'ru'), $strlinkupdate);
		}
	}
    return $table;
}


?>

