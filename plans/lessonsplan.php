<?php // $Id: lessonsplan.php,v 1.15 2010/08/23 08:48:13 Shtifanov Exp $

/*
    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../../monitoring/lib.php');
    require_once('../lib_school.php');
    require_once('../../mou_ege/lib_ege.php');

    $rid = required_param('rid', PARAM_INT);      // Rayon id
    $sid = required_param('sid', PARAM_INT);      // School id
	// $cid = required_param('cid', PARAM_INT);	  // Curriculum id
    $did = optional_param('did', 0, PARAM_INT);   // Discipline id
    // $gid = optional_param('gid', 0, PARAM_INT);   // Class id
    $pid = optional_param('pid', 0, PARAM_INT);   // Parallel number
    $yid = optional_param('yid', '0', PARAM_INT); // Year id
    $level = optional_param('level', 'plan');   // plan, unit, lesson
    $planid = optional_param('planid', 0, PARAM_INT);   // Plan id
    $unitid = optional_param('unitid', 0, PARAM_INT);   // Unit id

    if ($yid == 0)	{
    	$yid = get_current_edu_year_id();
    }

    require_once('../authall.inc.php');

	$action   = optional_param('action', 'action');
    if ($action == 'excel' && $level=='plan') {
	    $table = table_plan($rid, $sid, $yid, $pid, $did);
		print_table_to_excel($table,1);
        exit();
	}elseif ($action == 'excel' && $level=='unit'){
	    $table = table_unit($rid, $sid, $yid, $pid, $did, $planid);
		print_table_to_excel($table,1);
        exit();		
	}elseif ($action == 'excel' && $level=='lesson'){
	    $table = table_lesson($rid, $sid, $yid, $pid, $did, $planid, $unitid);
		print_table_to_excel($table,1);
        exit();		
	}

	$strtitle = get_string($level.'plans', 'block_mou_school');

	$breadcrumbs = '<a href="'.$CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('title','block_mou_school').'</a>';
	$breadcrumbs .= "-> $strtitle";
    print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);


    if ($admin_is  || $region_operator_is) {  // || $rayon_operator_is)  {
		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		listbox_rayons("lessonsplan.php?sid=0&amp;yid=$yid&amp;rid=", $rid);
		listbox_schools("lessonsplan.php?rid=$rid&amp;yid=$yid&amp;sid=", $rid, $sid, $yid);
		echo '</table>';
	} else  if ($rayon_operator_is)  {
		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		listbox_schools("lessonsplan.php?rid=$rid&amp;yid=$yid&amp;sid=", $rid, $sid, $yid);
		echo '</table>';
	}  else if ($school_operator_is) {
		print_heading($strtitle.': '.$school->name, "center", 3);
	}

	if ($rid == 0 ||  $sid == 0) {
	    print_footer();
	 	exit();
	}

	if ($rayon_operator_is && $rayon_operator_is != $rid)  {
		notify(get_string('selectownrayon', 'block_monitoring'));
	    print_footer();
		exit();
	}

//	print_heading($strclasses, "center");

	print_tabs_years_link("lessonsplan.php?", $rid, $sid, $yid);

    $currenttab = $level;
    include('tabsplan.php');

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
    listbox_parallel_all("lessonsplan.php?level=$level&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;did=$did&amp;pid=", $pid);
    listbox_discipline_parallel("lessonsplan.php?level=$level&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=$pid&amp;did=", $sid, $yid, $pid, $did);


    switch ($level)		{
    	case 'plan':	echo '</table>';
					    if ($pid != 0 && $did != 0)	{
						    $table = table_plan($rid, $sid, $yid, $pid, $did);
							print_color_table($table);

							$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'did' => $did, 'pid' => $pid,
											 'mode' => 'new', 'level' => $level, 'action' => 'excel');
							echo '<table align="center" border=0><tr><td>';
						    print_single_button("editplan.php", $options, get_string('add'.$level, 'block_mou_school'));
							echo '</td><td>';
						    print_single_button("lessonsplan.php", $options, get_string("downloadexcel"));
							echo '</td></tr></table>';
						}
		break;

    	case 'unit':
			    	    listbox_plans("lessonsplan.php?level=$level&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=$pid&amp;did=$did&amp;planid=", $sid, $yid, $pid, $did, $planid);
			    	    echo '</table>';
					    if ($pid != 0 && $did != 0 && $planid != 0)	{
						    $table = table_unit($rid, $sid, $yid, $pid, $did, $planid);
							print_color_table($table);

							$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'did' => $did, 'pid' => $pid, 
											'planid' => $planid, 'mode' => 'new', 'level' => $level, 'action' => 'excel');
							echo '<table align="center" border=0><tr><td>';
						    print_single_button("editplan.php", $options, get_string('add'.$level, 'block_mou_school'));
							echo '</td><td>';
						    print_single_button("lessonsplan.php", $options, get_string("downloadexcel"));
							echo '</td></tr></table>';
							
						}
		break;

    	case 'lesson':
			    	    listbox_plans("lessonsplan.php?level=$level&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=$pid&amp;did=$did&amp;planid=", $sid, $yid, $pid, $did, $planid);
			    	    listbox_units("lessonsplan.php?level=$level&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=$pid&amp;did=$did&amp;planid=$planid&amp;unitid=", $sid, $yid, $pid, $did, $planid, $unitid);
			    	    echo '</table>';
					    if ($pid != 0 && $did != 0 && $planid != 0 && $unitid != 0)	{
						    $table = table_lesson($rid, $sid, $yid, $pid, $did, $planid, $unitid);
							print_color_table($table);

							$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'did' => $did, 'pid' => $pid,
											 'planid' => $planid, 'unitid' => $unitid, 'mode' => 'new', 'level' => $level, 'action' => 'excel');
							echo '<table align="center" border=0><tr><td>';
						    print_single_button("editplan.php", $options, get_string('add'.$level, 'block_mou_school'));
							echo '</td><td>';
						    print_single_button("lessonsplan.php", $options, get_string("downloadexcel"));
							echo '</td></tr></table>';
						}
		break;
    }

	print_footer();



function table_plan($rid, $sid, $yid, $pid, $did)
{
	global $CFG;

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

	$plans =  get_records_sql ("SELECT id, schoolid, yearid, disciplineid, parallelnum, name
								FROM {$CFG->prefix}monit_school_discipline_plan
							    WHERE yearid = $yid and schoolid = $sid and parallelnum = $pid and disciplineid = $did
							    ORDER BY name");

    if ($plans)	{
	    foreach ($plans as $plan)		{
			$strsql = "SELECT p.id, sum(l.hours) AS sumhours
					   FROM (mdl_monit_school_discipline_plan p INNER JOIN mdl_monit_school_discipline_unit u ON p.id = u.planid) INNER JOIN mdl_monit_school_discipline_lesson l ON u.id = l.unitid
					   GROUP BY p.id HAVING p.id={$plan->id}";

			$sumhours = '-';
			if ($sums = get_record_sql($strsql))	{
				$sumhours = $sums->sumhours;
			}

			$title = get_string('editplan','block_mou_school');
			$strlinkupdate = "<a title=\"$title\" href=\"editplan.php?level=plan&amp;mode=edit&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=$pid&amp;did=$did&amp;planid={$plan->id}\">";
			$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";
			$title = get_string('deleteplan','block_mou_school');
	  	 	$strlinkupdate .= "<a title=\"$title\" href=\"delplan.php?level=plan&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=$pid&amp;did=$did&amp;id={$plan->id}\">";
			$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";

            $strplan = "<a href=\"lessonsplan.php?level=unit&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=$pid&amp;did=$did&amp;planid={$plan->id}\">".$plan->name.'</a>';
	    	$table->data[] = array('<b>'.$strplan.'</b>', $sumhours, $strlinkupdate);
	    }

	}

    return $table;
}



function table_unit($rid, $sid, $yid, $pid, $did, $planid)
{
	global $CFG;

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
			     			   WHERE schoolid = $sid and planid = $planid
			     			   ORDER BY number");

    if ($units)  {
    	foreach ($units as $unit)	{

			$strsql = "SELECT u.id, sum(l.hours) AS sumhours
					   FROM mdl_monit_school_discipline_unit u INNER JOIN  mdl_monit_school_discipline_lesson l ON u.id = l.unitid
					   GROUP BY u.id HAVING u.id={$unit->id}";

			$sumhours = '-';
			if ($sums = get_record_sql($strsql))	{
				$sumhours = $sums->sumhours;
			}

            $strname = '<b>'.$unit->number. '. ' .$unit->name . '</b>';
            $strname = "<a href=\"lessonsplan.php?level=lesson&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=$pid&amp;did=$did&amp;planid=$planid&amp;unitid=$unit->id\">".$strname.'</a>';
             /*
                  if (!empty($unit->description))		{
                   $strname .= ' ('. $unit->description . ')';
               }
               */

			$title = get_string('editunit','block_mou_school');
			$strlinkupdate = "<a title=\"$title\" href=\"editplan.php?level=unit&amp;mode=edit&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=$pid&amp;did=$did&amp;planid=$planid&amp;unitid={$unit->id}\">";
			$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";
			$title = get_string('deleteunit','block_mou_school');
	  	 	$strlinkupdate .= "<a title=\"$title\" href=\"delplan.php?level=unit&amp;sid=$sid&amp;rid=$rid&amp;yid=$yid&amp;pid=$pid&amp;did=$did&amp;planid=$planid&amp;id={$unit->id}\">";
			$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";

	    	$table->data[] = array($strname, '', '<b>'.$sumhours.'</b>', $strlinkupdate);

			$lessons =  get_records_sql("SELECT id, schoolid, unitid, number, name, hours, description
									   FROM {$CFG->prefix}monit_school_discipline_lesson
					     			   WHERE schoolid = $sid and unitid = {$unit->id}
					     			   ORDER BY number");

		    if ($lessons)  {
		    	foreach ($lessons as $lesson)	{
  	                    $strname = '<font COLOR=green> '.$lesson->number. '. ' .$lesson->name . '</font>';
  	                    /*
                    if (!empty($lesson->description))		{
	                    $strname .= ' ('. $lesson->description . ')';
	                }
	                */
					$title = get_string('editlesson','block_mou_school');
					$strlinkupdate = "<a title=\"$title\" href=\"editplan.php?level=lesson&amp;mode=edit&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=$pid&amp;did=$did&amp;planid=$planid&amp;unitid={$unit->id}&amp;lid={$lesson->id}\">";
					$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";
					$title = get_string('deletelesson','block_mou_school');
			  	 	$strlinkupdate .= "<a title=\"$title\" href=\"delplan.php?level=lesson&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=$pid&amp;did=$did&amp;planid=$planid&amp;unitid={$unit->id}&amp;id={$lesson->id}\">";
					$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";

			    	$table->data[] = array('', $strname, $lesson->hours, $strlinkupdate);
		    	}
		    }

    	}
    }


    return $table;
}


function table_lesson($rid, $sid, $yid, $pid, $did, $planid, $unitid)
{
	global $CFG;

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
							   FROM {$CFG->prefix}monit_school_discipline_lesson
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
			$title = get_string('editlesson','block_mou_school');
			$strlinkupdate = "<a title=\"$title\" href=\"editplan.php?level=lesson&amp;mode=edit&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=$pid&amp;did=$did&amp;planid=$planid&amp;unitid=$unitid}&amp;lid={$lesson->id}\">";
			$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";
			$title = get_string('deletelesson','block_mou_school');
	  	 	$strlinkupdate .= "<a title=\"$title\" href=\"delplan.php?level=lesson&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=$pid&amp;did=$did&amp;planid=$planid&amp;unitid=$unitid&amp;id={$lesson->id}\">";
			$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";

	    	$table->data[] = array($strname, $lesson->hours, $strlinkupdate);
    	}
    }

    return $table;
}


// Display list discipline for parallel
function listbox_plans($scriptname, $sid, $yid, $pid, $did, $planid)
{
  global $CFG;

  $strtitle = get_string('selectlessonplan', 'block_mou_school') . '...';
  $planmenu = array();

  $planmenu[0] = $strtitle;

  if ($yid != 0 && $sid != 0 && $pid != 0 && $did != 0)  {

		$plans =  get_records_sql ("SELECT id, name FROM {$CFG->prefix}monit_school_discipline_plan
										  WHERE yearid = $yid and schoolid = $sid and parallelnum = $pid and disciplineid = $did");
		if ($plans)	{
			foreach ($plans as $p) 	{
				$planmenu[$p->id] = $p->name;
			}
		}
  }

  echo '<tr><td>'.get_string('lessonplans','block_mou_school').':</td><td>';
  popup_form($scriptname, $planmenu, "switchplan", $planid, "", "", "", false);
  echo '</td></tr>';
  return 1;
}


// Display list discipline for parallel
function listbox_units($scriptname, $sid, $yid, $pid, $did, $planid, $unitid)
{
  global $CFG;

  $strtitle = get_string('selectunitsplan', 'block_mou_school') . '...';
  $unitmenu = array();

  $unitmenu[0] = $strtitle;

  if ($yid != 0 && $sid != 0 && $pid != 0 && $did != 0 && $planid != 0)  {

		$units =  get_records_sql("SELECT id, schoolid, planid, number, name, description
								   FROM {$CFG->prefix}monit_school_discipline_unit
				     			   WHERE schoolid = $sid and planid = $planid
				     			   ORDER BY number");

		if ($units)	{
			foreach ($units as $u) 	{
				$unitmenu[$u->id] = $u->name;
			}
		}
  }

  echo '<tr><td>'.get_string('unitplans','block_mou_school').':</td><td>';
  popup_form($scriptname, $unitmenu, "switchunit", $unitid, "", "", "", false);
  echo '</td></tr>';
  return 1;
}
*/

?>


