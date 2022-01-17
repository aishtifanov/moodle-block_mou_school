<?php // $Id: planplans.php,v 1.8 2012/09/21 10:50:17 shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../../monitoring/lib.php');
    require_once('../lib_school.php');
    require_once('../../mou_ege/lib_ege.php');
	require_once('../authbase.inc.php');
	
    $did = optional_param('did', 0, PARAM_INT);   // Discipline id
    $pid = optional_param('pid', 0, PARAM_INT);   // Parallel number
    $planid = optional_param('planid', 0, PARAM_INT);   // Plan id
    $unitid = optional_param('unitid', 0, PARAM_INT);   // Unit id


    if ($action == 'excel') {
	    $table = table_plan($rid, $sid, $yid, $pid, $did);
		print_table_to_excel($table,1);
        exit();
	} else   if ($action == 'sum') {
			$strsql = "SELECT p.id, sum(l.hours) AS sumhours
					   FROM (mdl_monit_school_discipline_plan p 
                       INNER JOIN mdl_monit_school_discipline_unit u ON p.id = u.planid) 
                       INNER JOIN mdl_monit_school_discipline_lesson_$rid l ON u.id = l.unitid
					   GROUP BY p.id HAVING p.id=$planid";

			if ($sums = get_record_sql($strsql))	{
                set_field('monit_school_discipline_plan', 'total', $sums->sumhours, 'id', $planid);  
			}
	   
	}   

    $currenttab = 'planplans';
    include('tabsplan.php');
    
    $edit_capability = has_capability('block/mou_school:editlessonsplan', $context);
    
	if (has_capability('block/mou_school:viewlessonsplan', $context))	{
		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	    listbox_parallel_all("planplans.php?rid=$rid&sid=$sid&yid=$yid&did=$did&pid=", $pid);
	    listbox_discipline_parallel("planplans.php?rid=$rid&sid=$sid&yid=$yid&pid=$pid&did=", $sid, $yid, $pid, $did);
		echo '</table>';
		
	    if ($pid != 0 && $did != 0)	{
	    	$edit_capability_discipline = has_capability_editlessonsplan($sid, $did);	
			
		    $table = table_plan($rid, $sid, $yid, $pid, $did);
			print_color_table($table);
			
			if ($edit_capability || $edit_capability_discipline)	{
				$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'did' => $did, 'pid' => $pid,
								 'mode' => 'new', 'level' => 'plan', 'action' => 'excel');
				echo '<table align="center" border=0><tr><td>';
			    print_single_button("editplan.php", $options, get_string('addplan', 'block_mou_school'));
				echo '</td><td>';
			    print_single_button("planplans.php", $options, get_string("downloadexcel"));
				echo '</td></tr></table>';
			}
            
            notify("<i>Замечание: для пересчета общего количества часов по плану надо нажать на значок в виде знака суммы <img src='{$CFG->pixpath}/i/agg_sum.gif'>.</i>"); 	
		}
	} else {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}
	

	print_footer();



function table_plan($rid, $sid, $yid, $pid, $did)
{
	global $CFG, $context, $edit_capability, $edit_capability_discipline;
	
    $table->head  = array (get_string('planname','block_mou_school'),
    					   get_string('hours','block_mou_school'),
    					   get_string('action','block_mou_school'));
	$table->align = array ( 'left', 'center', 'center');
	$table->columnwidth = array (25,10, 10);

    $table->class = 'moutable';
   	$table->width = '60%';

    $table->titlesrows = array(30);
	$table->titles = array();
    $table->titles[] = get_string('lessonplans', 'block_mou_school');
	$table->downloadfilename = 'lessonplans';
    $table->worksheetname = $table->downloadfilename;

	$plans =  get_records_sql ("SELECT id, schoolid, yearid, disciplineid, parallelnum, total, name
								FROM {$CFG->prefix}monit_school_discipline_plan
							    WHERE schoolid = $sid and parallelnum = $pid and disciplineid = $did
							    ORDER BY name");

    if ($plans)	{
	    foreach ($plans as $plan)		{

            $title = get_string('sumplan', 'block_mou_school');
			$strlinkupdate  = "<a title=\"$title\" href=\"planplans.php?action=sum&rid=$rid&sid=$sid&yid=$yid&pid=$pid&did=$did&planid={$plan->id}\">";
			$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/agg_sum.gif\" alt=\"$title\" /></a>&nbsp;";

			if ($edit_capability || $edit_capability_discipline)	{
				$title = get_string('editplan','block_mou_school');
				$strlinkupdate .= "<a title=\"$title\" href=\"editplan.php?level=plan&mode=edit&rid=$rid&sid=$sid&yid=$yid&pid=$pid&did=$did&planid={$plan->id}\">";
				$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";
				$title = get_string('deleteplan','block_mou_school');
		  	 	$strlinkupdate .= "<a title=\"$title\" href=\"delplan.php?level=plan&rid=$rid&sid=$sid&yid=$yid&pid=$pid&did=$did&id={$plan->id}\">";
				$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
				$title = get_string('clearplan','block_mou_school');
		  	 	$strlinkupdate .= "<a title=\"$title\" href=\"clearplan.php?level=plan&rid=$rid&sid=$sid&yid=$yid&pid=$pid&did=$did&id={$plan->id}\">";
				$strlinkupdate .= "<img src=\"../i/goom.gif\" alt=\"$title\" /></a>&nbsp;";

			} else {
				$strlinkupdate = '';
			}
/*			
			if ($edit_capability)	{
				$title = get_string('clearplan','block_mou_school');
		  	 	$strlinkupdate .= "<a title=\"$title\" href=\"clearplan.php?level=plan&rid=$rid&sid=$sid&yid=$yid&pid=$pid&did=$did&id={$plan->id}\">";
				$strlinkupdate .= "<img src=\"../i/goom.gif\" alt=\"$title\" /></a>&nbsp;";
			}
*/
            $strplan = "<a href=\"unitplans.php?rid=$rid&sid=$sid&yid=$yid&pid=$pid&did=$did&planid={$plan->id}\">".$plan->name.'</a>';
	    	$table->data[] = array('<b>'.$strplan.'</b>', '<b>'.$plan->total.'<b>', $strlinkupdate);
	    }

	}

    return $table;
}
?>
