<?php // $Id: studyyearclass.php,v 1.5 2010/07/30 08:44:39 Shtifanov Exp $

/*
ALTER TABLE `mou`.`mdl_monit_school_class` ADD COLUMN `classidold` INT(10) UNSIGNED DEFAULT AFTER `listmidatesids`;
*/
	require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_school.php');

    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = required_param('sid', PARAM_INT);       // School id
	$yid = optional_param('yid', 0, PARAM_INT);       // Year id
	$rayonid = optional_param('id', 1, PARAM_INT);

    if ($yid == 0)	{
    	$yid = get_current_edu_year_id();
    }

    $strtitle = get_string('createnewyearforclasses', 'block_mou_school');

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
	
    ignore_user_abort(false); // see bug report 5352. This should kill this thread as soon as user aborts.
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();
	@raise_memory_limit("512M");
 	if (function_exists('apache_child_terminate')) {
	    @apache_child_terminate();
	}    
		

	$strcurryear = current_edu_year();
	if (!$year = get_record('monit_years', 'name', $strcurryear)) {	
		error('Current study year not found.', $redirlink);
	}	
	if (!$lastyear = get_record('monit_years', 'id', $year->id - 1)) {
		error('Old year not found.', $redirlink);
	}
		
	if ($rayon = get_record('monit_rayon', 'id', $rayonid))	{
		print_heading($rayon->name);	
	} else {
		notice("All update complete.", "studyyear.php?rid=$rid&amp;sid=$sid");
	}
	
	
		
	$newschoolsids = get_list_old_new_id ('monit_school', $year->id);
	// print_r($newschoolsids);	exit (0);

	$classids = array();

	if ($classes = get_records_select('monit_school_class', "yearid = {$lastyear->id} AND rayonid = $rayonid", 'schoolid'))	{

		foreach ($classes as $class)	{
		    // print_r($class); echo '<hr>'; continue;
			$classid = $class->id;
			if ($class->parallelnum < 11)	{
				
				if (isset($newschoolsids[$class->schoolid]) && !empty($newschoolsids[$class->schoolid]))	{
	
					$num = (integer)$class->name;
					if (is_numeric($num))	{
						$contents = preg_replace("|[^а-яА-Я ]|i", NULL, $class->name);
						$newpn = $class->parallelnum + 1;
						$newname = $newpn . $contents;
					} else {
						$newpn 	 = $class->parallelnum;
						$newname = $class->name;
					}
					// echo  "$class->name ==> $newname ($newpn)<br>";
					unset($newclass);	
								
					$newclass->rayonid = $class->rayonid;
					$newclass->schoolid = $newschoolsids[$class->schoolid];					
					$newclass->yearid = $year->id;
					$newclass->description = $class->description; 
					$newclass->name = $newname;
					$newclass->parallelnum = $newpn;
					$newclass->timecreated = time();
					$newclass->curriculumid = $class->curriculumid;
					$newclass->teacherid = $class->teacherid;
					$newclass->classidold = $class->id;
					/*
					if (isset($class->curriculumid) && !empty($class->curriculumid))	{
						$newclass->curriculumid = $class->curriculumid;
					}	
				
					if (isset($class->teacherid) && !empty($class->teacherid))	{
						$newclass->teacherid = $class->teacherid;	
					}
					*/
					
					if (!record_exists('monit_school_class', 'yearid', $newclass->yearid, 'schoolid', $newclass->schoolid, 'name', $newclass->name)) {
						if ($newid = insert_record('monit_school_class', $newclass))	{
							$classids[$classid] = $newid;
							notify("New class added: $classid -> $newid", 'green', 'center');
							/*
							$newrec = get_record ('monit_school_class', 'yearid', $year->id, 'schoolid', $class->schoolid, 'name', $class->name, 'id');
							$classids[$class->id] = $newrec->id;
							notify("New class added: {$class->id} - $newid", 'blue', 'center');
							*/
							
						}	else	{
							print_r($class);
							error('Error insert monit_school_class.', 'studyyear.php');
						}
					} else {
							$strsql = "SELECT id FROM mdl_monit_school_class 
									   WHERE yearid = {$newclass->yearid} AND schoolid = {$newclass->schoolid} AND name = '{$newclass->name}'";
							// echo $strsql . '<hr>';
							if ($class_exist = get_record_sql($strsql))	{
								$classids[$classid] = $class_exist->id;
							}	else {
								$classids[$classid] = 0;
							}   	
							// $class_exist = get_record_select('', "yearid = {$newclass->yearid} AND schoolid = {$newclass->schoolid} AND name = {$newclass->name}");
							
							notify("Class already exists: {$newclass->schoolid} > {$classids[$classid]} > {$newclass->name}", 'red', 'left');
					}	
				}	
			}		
		}
	}
	
	// print_r($classids);	exit (0);
	 
	if ($pupilcards = get_records_select('monit_school_pupil_card', "yearid = {$lastyear->id} AND rayonid = $rayonid"))	{
		foreach ($pupilcards as $pupil)		{
			if (isset($newschoolsids[$pupil->schoolid]) && !empty($newschoolsids[$pupil->schoolid]))	{
				if (isset($classids[$pupil->classid]) && !empty($classids[$pupil->classid]))	{ 
					$pupil->yearid = $year->id;
					$pupil->schoolid = $newschoolsids[$pupil->schoolid];
					$pupil->classid = $classids[$pupil->classid];
					if (!record_exists('monit_school_pupil_card', 'yearid', $pupil->yearid, 'schoolid', $pupil->schoolid, 'userid', $pupil->userid)) {
						if ($newid = insert_record('monit_school_pupil_card', addslashes_object($pupil)))	{
							notify("New pupil added: $pupil->classid  -> $newid ", 'green', 'center');
						} else {
							print_r($pupil);
							notify('Error insert monit_school_pupil_card.');
							// error('Error insert monit_school_pupil_card.', 'studyyear.php');
						}
					} else {
						notify("Pupil already exists: $pupil->classid  -> $pupil->id");	
					}	
				} else {
					notify("??? May be 11 class or pupil already exists: $pupil->classid  -> $pupil->id ");
				} 	
			} else {
				notify("!!! $pupil->schoolid not found!!! ");
			}
		}
	
		$rayonid++;  

		notice("New pupilcards added.", "studyyearclass.php?rid=$rid&amp;sid=$sid&yid=$yid&amp;id=$rayonid");
		if ($rayonid<=25) {
			redirect("studyyearclass.php?rid=$rid&amp;sid=$sid&yid=$yid&amp;id=$rayonid", "New pupilcards added.", 3);
		} else {
			notice("All class and pupil updated", "studyyear.php?rid=$rid&amp;sid=$sid");
		}	
		
	} else {
		notify("!!! Pupil not found!!! ");
	}

	// redirect('studyyear.php', 'Update complete', 60);	

    print_footer();
    
    // delete FROM `mou`.`mdl_monit_school_class` where yearid=3
    // DELETE FROM `mou`.`mdl_monit_school_pupil_card` where yearid=3
    // ALTER TABLE `mou`.`mdl_monit_school_pupil_card` AUTO_INCREMENT = 25536
    // ALTER TABLE `mou`.`mdl_monit_school_class` AUTO_INCREMENT = 1453

?>


