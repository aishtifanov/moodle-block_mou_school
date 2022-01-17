<?php // $Id: lessonplans.php,v 1.3 2012/02/13 10:32:24 shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../../monitoring/lib.php');
    require_once('../lib_school.php');
    require_once('lib_plans.php');
    require_once('../../mou_ege/lib_ege.php');
   	require_once('../authbase.inc.php');    

    $did = optional_param('did', 0, PARAM_INT);   // Discipline id
    $pid = optional_param('pid', 0, PARAM_INT);   // Parallel number
    $planid = optional_param('planid', 0, PARAM_INT);   // Plan id
    $unitid = optional_param('unitid', 0, PARAM_INT);   // Unit id
    
	if ($action == 'excel'){
	    $table = table_lesson($rid, $sid, $yid, $pid, $did, $planid, $unitid);
		print_table_to_excel($table,1);
        exit();		
	}

    $currenttab = 'lessonplans';
    include('tabsplan.php');
    
    $edit_capability = has_capability('block/mou_school:editlessonsplan', $context);    

	if (has_capability('block/mou_school:viewlessonsplan', $context))	{
		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	    listbox_parallel_all("lessonplans.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;did=$did&amp;pid=", $pid);
	    listbox_discipline_parallel("lessonplans.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=$pid&amp;did=", $sid, $yid, $pid, $did);
	    listbox_plans("lessonplans.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=$pid&amp;did=$did&amp;planid=", $sid, $yid, $pid, $did, $planid);
	    listbox_units("lessonplans.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=$pid&amp;did=$did&amp;planid=$planid&amp;unitid=", $sid, $yid, $pid, $did, $planid, $unitid);
	    echo '</table>';
	    
	    if ($pid != 0 && $did != 0 && $planid != 0 && $unitid != 0)	{
	    	
	    	$edit_capability_discipline = has_capability_editlessonsplan($sid, $did);
				    	
		    $table = table_lesson($rid, $sid, $yid, $pid, $did, $planid, $unitid);
			print_color_table($table);
	
			if ($edit_capability || $edit_capability_discipline)	{
				$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'did' => $did, 'pid' => $pid,
								 'planid' => $planid, 'unitid' => $unitid, 'mode' => 'new', 'level' => 'lesson', 'action' => 'excel');
				echo '<table align="center" border=0><tr><td>';
			    print_single_button("editplan.php", $options, get_string('addlesson', 'block_mou_school'));
				echo '</td><td>';
			    print_single_button("lessonplans.php", $options, get_string("downloadexcel"));
				echo '</td></tr></table>';
			}	
		}
	}	

	print_footer();


function table_lesson($rid, $sid, $yid, $pid, $did, $planid, $unitid)
{
	global $CFG, $context, $edit_capability, $edit_capability_discipline;

    $table->head  = array (get_string('lessonplan','block_mou_school'),
    					   get_string('hours','block_mou_school'),
    					   get_string('action','block_mou_school'));
	$table->align = array ('left', 'center', 'center');
	$table->columnwidth = array (50, 10, 10);

    $table->class = 'moutable';
   	$table->width = '70%';

    $table->titlesrows = array(30);
	$table->titles = array();
    $table->titles[] = get_string('lessonplans', 'block_mou_school');
	$table->downloadfilename = 'lessonplans';
    $table->worksheetname = $table->downloadfilename;

	$sumhours = '-';
	$strlinkupdate = '-';

	$lessons =  get_records_sql("SELECT id, schoolid, unitid, number, name, hours, description
							   FROM {$CFG->prefix}monit_school_discipline_lesson_$rid
			     			   WHERE schoolid = $sid and unitid = $unitid
			     			   ORDER BY number");

    if ($lessons)  {
    	foreach ($lessons as $lesson)	{
	        $strname = '<font COLOR=green> '.$lesson->number. '. ' .$lesson->name . '</font>';
	                    /*
                  if (!empty($lesson->description))		{
                   $strname .= ' ('. $lesson->description . ')';
               }
               */
   
         	if ($edit_capability || $edit_capability_discipline)	{
				$title = get_string('editlesson','block_mou_school');
				$strlinkupdate = "<a title=\"$title\" href=\"editplan.php?level=lesson&amp;mode=edit&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=$pid&amp;did=$did&amp;planid=$planid&amp;unitid=$unitid}&amp;lid={$lesson->id}\">";
				$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";
				$title = get_string('deletelesson','block_mou_school');
		  	 	$strlinkupdate .= "<a title=\"$title\" href=\"delplan.php?level=lesson&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=$pid&amp;did=$did&amp;planid=$planid&amp;unitid=$unitid&amp;id={$lesson->id}\">";
				$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
			} else {
				$strlinkupdate = '';
			}

	    	$table->data[] = array($strname, $lesson->hours, $strlinkupdate);
    	}
    }

    return $table;
}
?>
