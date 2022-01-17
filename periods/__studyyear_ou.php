<?php // $Id: studyyear_ou.php,v 1.1 2010/07/07 11:16:36 Shtifanov Exp $

	require_once("../../../config.php");
    require_once('../../monitoring/lib.php');

    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = required_param('sid', PARAM_INT);       // School id
	// $lastyid = optional_param('yid', 0, PARAM_INT);       // Year id
	$action = optional_param('action', '-');
	
	$lastyid = 0;
	$yid = $lastyid;
	 
    $strtitle = get_string('studyyears', 'block_mou_school');

	$breadcrumbs = '<a href="'.$CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('title','block_mou_school').'</a>';
	$breadcrumbs .= "-> $strtitle";
    print_header_mou("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

    print_heading($strtitle);

	$redirlink = "studyyear.php?rid=$rid&amp;yid=$yid&amp;sid=$sid";
	
	$admin_is = isadmin();
	$region_operator_is = ismonitoperator('region');
	if (!$admin_is && !$region_operator_is)	 {
		error('Only admin access this function.', $redirlink);
	}	

	$strcurryear = current_edu_year();
	if ($year = get_record('monit_years', 'name', $strcurryear)) {
		notify('New year already created.');
		$yid = $year->id; 
	} else {
		$rec->name = $strcurryear;
		$rec->datestart = date("Y") . '-09-01'; 
		$rec->dateend = date("Y")+1 . '-09-01';
		if ($yid = insert_record('monit_years', $rec))	{
			notify("New year add: {$rec->name}", 'green', 'center');
		}
	}
	
	switch($action) {
	 	case 'createschool':	
					create_eductional_ou ('monit_school', $yid, 'School');
					notice("Schools checked and added", "studyyear_ou.php?rid=$rid&amp;sid=$sid&amp;action=createcollege");
		break;
	 	case 'createcollege':
					create_eductional_ou ('monit_college', $yid, 'College');
					notice("College checked and added", "studyyear_ou.php?rid=$rid&amp;sid=$sid&amp;action=createudod");
		break;			
		case 'createudod':
					create_eductional_ou ('monit_udod', $yid, 'UDOD');		
					notice("UDODs checked and added", "studyyear_ou.php?rid=$rid&amp;sid=$sid&amp;action=createdou");
		break;			
		case 'createdou':
					create_eductional_ou ('monit_education', $yid, 'DOU');		
					notice("DOUs checked and added", "studyyear.php?rid=$rid&amp;sid=$sid");
		break;			
	}	 			
	// print_tabs_years_link("studyyear.php?", $rid, $sid, $yid);
	
	$currenttab = 'studyyear';
    include('tabsup.php');
		
	$years = get_records('monit_years');
	
	if ($years )	{
		$table->head  = array (	get_string('name', 'block_mou_school'), get_string('timestart', 'block_mou_school'),
								get_string('timeend', 'block_mou_school'), get_string('action', 'block_mou_school'));
	    $table->align = array ("center", "center", "center", "center");
 	    $table->size = array('10%', '10%', '10%', '5%');
	   	$table->width = '60%';
        $table->class = 'moutable';
       	// $table->align = array ("left", "left", "left");
       	
		
		foreach ($years as $year) {
				$lastyear = $year->id;
				$title = get_string('editstudyear','block_mou_school');
				$strlinkupdate = "<a title=\"$title\" href=\"editstudyear.php?mode=edit&amp;id={$year->id}\">";
				$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";
				$strdiscipline = $year->name;
				$table->data[] = array ($strdiscipline, convert_date($year->datestart, 'en', 'ru'),
										convert_date($year->dateend, 'en', 'ru'), $strlinkupdate);
		}
		print_color_table($table);
		
		$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $lastyear, 'action' => 'create');
		echo '<table align="center" border=0><tr><td>';
	    print_single_button("studyyear.php", $options, get_string('createnewyear','block_mou_school'));
		echo '</td><td>';
	    print_single_button("studyyearclass.php", $options, get_string('createnewyearforclasses','block_mou_school'));
		echo '</td></tr></table>';
		
	}	else {
		notify(get_string('notfoundyears', 'block_mou_school'));
	}
	
    print_footer();



function create_eductional_ou ($table, $yid, $type_ou) 
{
	$lastyid = $yid-1;
	$edus = get_records($table, 'yearid', $lastyid);
	foreach($edus as $edu)	{
		if ($edu->isclosing == false)	{
			if (!record_exists($table, 'yearid', $yid, 'uniqueconstcode', $edu->uniqueconstcode)) {
			    $edu->yearid = $yid;
			    $newedu = addslashes_object($edu);
				if ($newid = insert_record($table, $newedu))	{
					// $schoolsids[$school->id] = $newid;  
		    	    notify("$type_ou add: $newid > {$edu->uniqueconstcode}", 'green', 'left');
		    	}
		    } else {
					notify("$type_ou already exists: {$edu->uniqueconstcode}", 'red', 'left');						    	
		    }	
	    }
	}
}

?>


