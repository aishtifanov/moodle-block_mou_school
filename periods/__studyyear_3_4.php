<?php // $Id: studyyear_3_4.php,v 1.2 2010/07/07 13:45:17 Shtifanov Exp $

	require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_school.php');

    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = required_param('sid', PARAM_INT);       // School id
	// $lastyid = optional_param('yid', 0, PARAM_INT);       // Year id
	
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
	
	$lastyid = $yid-1;

	$strcurryear = current_edu_year();
	if (!$year = get_record('monit_years', 'name', $strcurryear)) {	
		error('Current study year not found.', $redirlink);
	}	

	if (!$lastyear = get_record('monit_years', 'id', $year->id - 1)) {
		error('Old year not found.', $redirlink);
	}


	$tablesyears = array (	'mdl_monit_school_curriculum',
							'mdl_monit_school_curriculum_totals',
							'mdl_monit_school_discipline_plan',
							'mdl_monit_school_schedule_bells',
							'mdl_monit_school_term');
	foreach ($tablesyears  as $table)		{							
			$strsql = "UPDATE $table SET yearid=4 WHERE yearid=3";
			$db->Execute($strsql);
			notify ("$table yearid updated.", 'green');
	}							


	$newschoolsids = get_list_old_new_id ('monit_school', $yid);
	$tables = array (	// 'mdl_monit_school_assignments',
						'mdl_monit_school_class_termtype',
						'mdl_monit_school_component',
						'mdl_monit_school_curriculum_totals',
						'mdl_monit_school_discipline',
						'mdl_monit_school_discipline_domain',
						'mdl_monit_school_discipline_group',
						// 'mdl_monit_school_discipline_lesson',
						'mdl_monit_school_discipline_plan',
						'mdl_monit_school_discipline_unit',
						'mdl_monit_school_holidays',
						'mdl_monit_school_profiles_curriculum',
						'mdl_monit_school_room',
						'mdl_monit_school_schedule_bells',
						'mdl_monit_school_subgroup',
						'mdl_monit_school_subgroup_pupil',
						'mdl_monit_school_teacher',
						'mdl_monit_school_term',
						'mdl_monit_school_class_discipline', 
						'mdl_monit_school_class_schedule',
						'mdl_monit_school_curriculum', 
						'mdl_monit_school_class_smena');
	foreach ($tables as $table)		{
		$strsql = "SELECT DISTINCT schoolid FROM $table";
		if ($schoolsids = get_records_sql($strsql))	{
			foreach ($schoolsids as $schoolsid)	{
				if (isset($newschoolsids[$schoolsid->schoolid]) and !empty($newschoolsids[$schoolsid->schoolid]))	{
					$strsql = "UPDATE $table SET schoolid=". 
							   $newschoolsids[$schoolsid->schoolid] . " WHERE schoolid=" . $schoolsid->schoolid;
					$db->Execute($strsql);
				}  else {
					notify ("New schoolid not found for $schoolsid->schoolid");
				}
			}
		}	
		notify ("$table schoolid updated.", 'green');
	}
	 
	
	$newclassesids = get_classid_lastyear ($yid);
	// print_r($newclassesids); exit();
	
	$tablesclass = array (	'mdl_monit_school_class_discipline', 
							'mdl_monit_school_class_schedule',
							'mdl_monit_school_curriculum', 
							'mdl_monit_school_class_smena');
	foreach ($tablesclass as $table)		{
		$strsql = "SELECT DISTINCT classid FROM $table";
		if ($classesids = get_records_sql($strsql))	{
			// print_r($classesids); echo '<hr>'; continue;
			foreach ($classesids as $classesid)	{
				if (isset($newclassesids[$classesid->classid]) and !empty($newclassesids[$classesid->classid]))	{
					$strsql = "UPDATE $table SET classid=". 
							   $newclassesids[$classesid->classid] . " WHERE classid=" . $classesid->classid;
					$db->Execute($strsql);
				}  else {
					notify ("New classid not found for $classesid->classid");
				}
			}
		}	
		notify ("$table classid updated.", 'green');
	}
							
	
	notice("All update in electronic school complete.", "studyyear.php?rid=$rid&amp;sid=$sid");
    print_footer();
?>


