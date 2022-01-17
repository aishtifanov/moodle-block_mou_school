<?php // $Id: unitplans.php,v 1.5 2012/02/21 06:34:41 shtifanov Exp $

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
    
	if ($action == 'excel')		{
	    $table = table_unit($rid, $sid, $yid, $pid, $did, $planid);
		print_table_to_excel($table,1);
        exit();		
	}

    $currenttab = 'unitplans';
    include('tabsplan.php');
    
    $edit_capability = has_capability('block/mou_school:editlessonsplan', $context);

	if (has_capability('block/mou_school:viewlessonsplan', $context))	{
		
		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	    listbox_parallel_all("unitplans.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;did=$did&amp;pid=", $pid);
	    listbox_discipline_parallel("unitplans.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=$pid&amp;did=", $sid, $yid, $pid, $did);
	    listbox_plans("unitplans.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=$pid&amp;did=$did&amp;planid=", $sid, $yid, $pid, $did, $planid);
	    echo '</table>';
	    if ($pid != 0 && $did != 0 && $planid != 0)	{
	    	
	    	$edit_capability_discipline = has_capability_editlessonsplan($sid, $did);
				    	
		    $table = table_unit($rid, $sid, $yid, $pid, $did, $planid);
			print_color_table($table);
			
			if ($edit_capability || $edit_capability_discipline)	{
				$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'did' => $did, 'pid' => $pid, 
								'planid' => $planid, 'mode' => 'new', 'level' => 'unit', 'action' => 'excel');
				echo '<table align="center" border=0><tr><td>';
			    print_single_button("editplan.php", $options, get_string('addunit', 'block_mou_school'));
				echo '</td><td>';
			    print_single_button("unitplans.php", $options, get_string("downloadexcel"));
				echo '</td></tr></table>';
			}	
		}
	} else {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}

	print_footer();



function table_unit($rid, $sid, $yid, $pid, $did, $planid)
{
	global $CFG, $context, $edit_capability, $edit_capability_discipline;

    $table->head  = array (get_string('unitplan','block_mou_school'),
    					   get_string('lessonplan','block_mou_school'),
    					   get_string('hours','block_mou_school'),
    					   get_string('action','block_mou_school'));
	$table->align = array ('left', 'left', 'center', 'center');
	$table->columnwidth = array (30, 40, 10, 10);

    $table->class = 'moutable';
   	$table->width = '90%';

    $table->titlesrows = array(30);
	$table->titles = array();
    $table->titles[] = get_string('lessonplans', 'block_mou_school');
	$table->downloadfilename = 'lessonplans';
    $table->worksheetname = $table->downloadfilename;

	$strlinkupdate = '-';

	$units =  get_records_sql("SELECT id, schoolid, planid, number, name, description
							   FROM {$CFG->prefix}monit_school_discipline_unit
			     			   WHERE planid = $planid
			     			   ORDER BY number");

    if ($units)  {
    	foreach ($units as $unit)	{
	

            $strname = '<b>'.$unit->number. '. ' .$unit->name . '</b>';
            $strname = "<a href=\"lessonplans.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=$pid&amp;did=$did&amp;planid=$planid&amp;unitid=$unit->id\">".$strname.'</a>';
             /*
                  if (!empty($unit->description))		{
                   $strname .= ' ('. $unit->description . ')';
               }
               */
			if ($edit_capability || $edit_capability_discipline)	{
				$title = get_string('editunit','block_mou_school');
				$strlinkupdate = "<a title=\"$title\" href=\"editplan.php?level=unit&amp;mode=edit&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=$pid&amp;did=$did&amp;planid=$planid&amp;unitid={$unit->id}\">";
				$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";
				$title = get_string('deleteunit','block_mou_school');
		  	 	$strlinkupdate .= "<a title=\"$title\" href=\"delplan.php?level=unit&amp;sid=$sid&amp;rid=$rid&amp;yid=$yid&amp;pid=$pid&amp;did=$did&amp;planid=$planid&amp;id={$unit->id}\">";
				$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
			} else {
				$strlinkupdate = '';
			}

			$lessons =  get_records_sql("SELECT id, schoolid, unitid, number, name, hours, description
									   FROM {$CFG->prefix}monit_school_discipline_lesson_$rid
					     			   WHERE schoolid = $sid and unitid = {$unit->id}
					     			   ORDER BY number");
					     			   
			$sumhours = 0;
			if ($lessons)  foreach ($lessons as $lesson)	{
				$sumhours += $lesson->hours;
			}	
	    	$table->data[] = array($strname, '', '<b>'.$sumhours.'</b>', $strlinkupdate);
	    	
	    	
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
						$strlinkupdate = "<a title=\"$title\" href=\"editplan.php?level=lesson&amp;mode=edit&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=$pid&amp;did=$did&amp;planid=$planid&amp;unitid={$unit->id}&amp;lid={$lesson->id}\">";
						$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";
						$title = get_string('deletelesson','block_mou_school');
				  	 	$strlinkupdate .= "<a title=\"$title\" href=\"delplan.php?level=lesson&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=$pid&amp;did=$did&amp;planid=$planid&amp;unitid={$unit->id}&amp;id={$lesson->id}\">";
						$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
					} else {
						$strlinkupdate = '';
					}

			    	$table->data[] = array('', $strname, $lesson->hours, $strlinkupdate);
		    	}
		    }

    	}
        
        $plan = get_record_select('monit_school_discipline_plan', "id=$planid", 'id, total');
        $table->data[] = array('', '',  '<b>'.$plan->total.'<b>', '');
        
    }


    return $table;
}
?>
