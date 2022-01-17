<?php // $Id: lib_school.php,v 1.34 2014/05/19 12:29:38 shtifanov Exp $

/*
VAR                    FOREIGN KEY               PRIMARY KEY
===============================================================================
$rid               rayonid                    mdl_monit_rayon.id
$sid               schoolid               mdl_monit_school.id
$yid               yearid                    mdl_monit_years.id
$gid               classid                    mdl_monit_school_class.id
$uid               userid                    mdl_user.id
$did               disciplineid          mdl_monit_school_discipline.id
$tid               termid                    mdl_monit_school_term.id
$cid               componentid               mdl_monit_school_component.id
$pid               profileid               mdl_monit_school_profiles_curriculum.id
$jid               scheduleid               mdl_monit_school_class_schedule.id
$hid               holidaysid               mdl_monit_school_holidays.id

$cdid               classdisciplineid     mdl_monit_school_class_discipline.id
$ddid               disciplinedomainid     mdl_monit_school_discipline_domain.id
$dgid               dgoupid                    mdl_monit_school_discipline_group.id     

$planid               planid                    mdl_monit_school_discipline_plan.id
$unitid               unitid                    mdl_monit_school_discipline_unit.id
$termtypeid          termtypeid               mdl_monit_school_term_type.id
     
$period                                    Period time: day, week, month, year
$level                                         Level plans: plan, unit, lesson
$wid                                         Day number in week
$pid               parallelnum               Class parallel number

CONFLICT!!!!!

$cid               curriculumid          mdl_monit_school_curriculum.id
$tid               teacherid               mdl_user.id
*/


// Запрос для поиска id-школы SELECT id FROM mou.mdl_monit_school where yearid = 14 and name like '%выбывших%';
define('ID_SCHOOL_FOR_DELETED', 8713);
define('ID_SCHOOLS_FOR_DELETED', '8713, 7544, 6957, 6369, 5776, 5180, 4585, 3990, 3385, 2769, 2116');
/***
 * This function makes a role-assignment (a role for a user or group in a particular context)
 * @param $roleid - the role of the id
 * @param $userid - userid
 * @param $contextid - id of the context
 * @param $timestart - time this assignment becomes effective
 * @param $timeend - time this assignemnt ceases to be effective
 * @uses $USER
 * @return id - new id of the assigment
 */
function role_assign_mou($roleid, $userid, $contextid, $timestart=0, $timeend=0, $hidden=0, $enrol='manual',$timemodified='') {
    global $USER, $CFG;

    if (!$timemodified) {
        $timemodified = time();
    }

/// Check for existing entry
    if ($userid) {
        $ra = get_record('role_assignments', 'roleid', $roleid, 'contextid', $contextid, 'userid', $userid, 
                         'id, roleid, contextid, userid, hidden, timestart, timeend, timemodified, modifierid, enrol, sortorder');
    } else {
    	return false;
        // $ra = get_record('role_assignments', 'roleid', $roleid, 'contextid', $context->id, 'groupid', $groupid);
    }

    if (empty($ra)) {             // Create a new entry
        $ra = new object();
        $ra->roleid = $roleid;
        $ra->contextid = $contextid;
        $ra->userid = $userid;
        $ra->hidden = $hidden;
        $ra->enrol = $enrol;
    /// Always round timestart downto 100 secs to help DBs to use their own caching algorithms
    /// by repeating queries with the same exact parameters in a 100 secs time window
        $ra->timestart = round($timestart, -2);
        $ra->timeend = $timeend;
        $ra->timemodified = $timemodified;
        $ra->modifierid = empty($USER->id) ? 0 : $USER->id;

        if (!$ra->id = insert_record('role_assignments', $ra)) {
            return false;
        }

    } else {                      // We already have one, just update it
        $ra->id = $ra->id;
        $ra->hidden = $hidden;
        $ra->enrol = $enrol;
    /// Always round timestart downto 100 secs to help DBs to use their own caching algorithms
    /// by repeating queries with the same exact parameters in a 100 secs time window
        $ra->timestart = round($timestart, -2);
        $ra->timeend = $timeend;
        $ra->timemodified = $timemodified;
        $ra->modifierid = empty($USER->id) ? 0 : $USER->id;

        if (!update_record('role_assignments', $ra)) {
            return false;
        }
    }

    return true;
}


/**
 * Deletes one or more role assignments.   You must specify at least one parameter.
 * @param $roleid
 * @param $userid
 * @param $contextid
 * @return boolean - success or failure
 */
function role_unassign_mou($roleid, $userid, $contextid) {
    global $USER, $CFG;

	if (!$success = delete_records_select('role_assignments', "roleid=$roleid AND userid=$userid AND contextid= $contextid"))	{
		notify('!!!> Not deleted role_assignments.');
	}		

	// delete lastaccess records
	/*
	if (!delete_records_select('user_lastaccess', "userid=$userid AND courseid=$courseid"))	{
		notify('!!!> Not deleted user_lastaccess.');
	}
	*/
	
    return $success;
}


// Display list rayons as popup_form
function listbox_rayons_role($scriptname, &$rid)
{
	global $CFG, $USER;//, $admin_is, $region_operator_is;

	$ret = false;	
  	$listrayons = '';

	 $strsql = "SELECT a.id, roleid, contextid, contextlevel, instanceid, path  
				FROM mdl_role_assignments a	RIGHT JOIN mdl_context ctx ON a.contextid=ctx.id
			   WHERE userid={$USER->id}";
	 // echo $strsql . '<hr>';
	 if ($ctxs = get_records_sql($strsql))	{
	 		// echo '<pre>'; print_r($ctxs); echo '</pre>';
			foreach($ctxs as $ctx1)	{
				switch ($ctx1->contextlevel)	{
					case CONTEXT_SYSTEM: if ($ctx1->roleid == 1)	{ 
											$listrayons = -1;
										 }
										 break;					 	
					case CONTEXT_REGION: if ($ctx1->roleid == 8 || $ctx1->roleid == 10)	{
										 	$listrayons = -1;
										 }
										 break;		
    				case CONTEXT_RAYON:  if ($ctx1->roleid == 8 || $ctx1->roleid == 10) {
    										$listrayons .= 	$ctx1->instanceid . ',';
										 }
								 		 break;
					case CONTEXT_SCHOOL: 
					case CONTEXT_DISCIPLINE:
					case CONTEXT_CLASS: $contexts = explode('/', $ctx1->path);
										// print_r($contexts);
										$ctxrayon = get_record('context', 'id', $contexts[3]);
										$listrayons .= $ctxrayon->instanceid . ',';
					break;
	 			}
	 			
	 			if 	($listrayons == -1) break;
			}
	 }		 


   //  echo $listrayons . '!!!<br>';
    
	 if ($listrayons == '') 	{
	 	return false;
	 } else if 	($listrayons == -1) 	{
	 	$strsql = "SELECT id, name FROM {$CFG->prefix}monit_rayon ORDER BY number";
	 } else {	
	 	$listrayons .= '0';
	 	$strsql = "SELECT id, name FROM {$CFG->prefix}monit_rayon WHERE id in ($listrayons)ORDER BY number";
	 }
 	
 	$rayonmenu = array();
	// echo $strsql . '<hr>';
  	if($allrayons = get_records_sql($strsql))   {
  		// print_r($allrayons);
  		if (count($allrayons) > 1) {
  			$rayonmenu[0] = get_string('selectarayon', 'block_monitoring').'...';
	 		 foreach ($allrayons as $rayon) 	{
	      		$rayonmenu[$rayon->id] = $rayon->name;
	  	 	 }
		    $ret =  '<tr> <td>'.get_string('rayon', 'block_monitoring').': </td><td>';
		    $ret .= popup_form($scriptname, $rayonmenu, 'switchrayon', $rid, '', '', '', true);
		  	$ret .= '</td></tr>';
	  	 	 
  		} else {
  			$rayon = current($allrayons);
  			// $rayonmenu[$rayon->id] = $rayon->name;
  			$rid = $rayon->id;
  			$ret =  '<tr> <td>'.get_string('rayon', 'block_monitoring').': </td><td>';
		    $ret .= "<b>".$rayon->name."</b>";
		  	$ret .= '</td></tr>';
  		}
  	}

   	
	return $ret;
}

// Display list schools as popup_form
function listbox_schools_role($scriptname, &$rid, &$sid, $yid)
{
	global $CFG, $USER;

	$ret = false;
  
 	if ($rid == 0)  return false;
   
  	$listschools = '';

	$strsql = "SELECT a.id, roleid, contextid, contextlevel, instanceid, path 
				FROM mdl_role_assignments a	RIGHT JOIN mdl_context ctx ON a.contextid=ctx.id
			   WHERE userid={$USER->id}";
	  // echo $strsql . '<hr>';
	if ($ctxs = get_records_sql($strsql))	{
	 		// print_r($ctxs);
			foreach($ctxs as $ctx1)	{
				switch ($ctx1->contextlevel)	{
					case CONTEXT_SYSTEM: if ($ctx1->roleid == 1)	{ 
											$listschools = -1;
										 }
										 break;	
										 				 	
					case CONTEXT_REGION: if ($ctx1->roleid == 8 || $ctx1->roleid == 10)	{
										 	$listschools = -1;
										 }
										 break;	
										 	
    				case CONTEXT_RAYON:  if ($ctx1->roleid == 8 || $ctx1->roleid == 10) {
    										$listschools = -1;
										 }
								 		 break;
								 		 
					case CONTEXT_SCHOOL: $listschools .= $ctx1->instanceid . ',';
										 break;
					
					case CONTEXT_DISCIPLINE:
					case CONTEXT_CLASS:  
										$contexts = explode('/', $ctx1->path);
										$ctxschool = get_record('context', 'id', $contexts[4]);
										$listschools .= $ctxschool->instanceid . ',';
										break;
	 			}
	 			
	 			if 	($listschools == -1) break;
			}
	 }		 
	
	 if ($listschools == '') 	{
	 	return false;
	 } else if 	($listschools == -1) 	{
	 	$strsql = "SELECT id, name  FROM {$CFG->prefix}monit_school
					WHERE rayonid = $rid AND isclosing=0 AND yearid=$yid
 					ORDER BY number";
	 } else {	
	 	$listschools .= '0';
	 	$strsql = "SELECT id, rayonid, name FROM {$CFG->prefix}monit_school
		 			 WHERE rayonid = $rid AND isclosing=0 AND yearid=$yid AND id in ($listschools)
   					 ORDER BY number";
	 }
 
 
	$schoolmenu = array();
	// echo $strsql . '<hr>';
    if ($arr_schools =  get_records_sql($strsql))	{
    	if (count($arr_schools) > 1) {
	   		$schoolmenu[0] = get_string('selectaschool','block_monitoring').' ...';
	  		foreach ($arr_schools as $school) {
				$len = strlen ($school->name);
				if ($len > 200)  {
					// $school->name = substr($school->name, 0, 200) . ' ...';
					$school->name = substr($school->name,0,strrpos(substr($school->name,0, 210),' ')) . ' ...';
				}
				$schoolmenu[$school->id] =$school->name;
			}
		 	$ret =  '<tr><td>'.get_string('school', 'block_monitoring').':</td><td>';
  			$ret .=  popup_form($scriptname, $schoolmenu, 'switchschool', $sid, '', '', '', true);
  			$ret .= '</td></tr>';
		} else {
  			$school = current($arr_schools);
  			// $schoolmenu[$school->id] = $school->name;
  			$sid = $school->id;
  			$rid = $school->rayonid;
		 	$ret =  '<tr><td>'.get_string('school', 'block_monitoring').':</td><td>';
  			$ret .=  "<b>$school->name</b>";
  			$ret .= '</td></tr>';
		} 
  	} else {
  		$ret = false;
  	}
	
	  
  return $ret;
}

function listbox_typeoftask($scriptname, $tyid)
{
  global $CFG;

  $typeoftaskmenu = array();
  $typeoftaskmenu[0] = get_string('selecttypeoftask', 'block_mou_school').'...';

	if($types = get_records_select('monit_school_type_assignment', '', '', 'id, name')){
		foreach($types as $type){
			$typeoftaskmenu[$type->id] = $type->name;
		}
	}

  echo '<tr> <td>'.get_string('typeoftask', 'block_mou_school').': </td><td>';
  popup_form($scriptname, $typeoftaskmenu, 'switchtypeoftask', $tyid, '', '', '', false);
  echo '</td></tr>';
  return 1;
}

function listbox_theme($scriptname, $rid, $sid, $planid, $themeid)
{
  global $CFG;

  $thememenu = array();
  
  $thememenu[0] = get_string('selecttypeoftask', 'block_mou_school').'...';
  
	if($units = get_records_select("monit_school_discipline_unit", "planid=$planid", '', 'id, number')){
	
		foreach($units as $unit){
				if($themes = get_records_select("monit_school_discipline_lesson_$rid", "schoolid=$sid and unitid={$unit->id}", '', 'id,number, name')){
					foreach($themes as $theme){
						$thememenu[$theme->id] = $unit->number.'.'.$theme->number.'. '.$theme->name;
					}
				}
		}
		
	}

  echo '<tr> <td>'.get_string('lessonplan', 'block_mou_school').': </td><td>';
  popup_form($scriptname, $thememenu, 'switchtheme', $themeid, '', '', '', false);
  echo '</td></tr>';
  return 1;
}

function listbox_class_role($scriptname, $rid, $sid, $yid, &$gid)
{
  	global $CFG, $USER;

	$ret = false;
  
 	if ($rid == 0 || $sid == 0)  return false;
   
  	$listclasses = '';

	$strsql = "SELECT a.id, roleid, contextid, contextlevel, instanceid, path 
				FROM mdl_role_assignments a	RIGHT JOIN mdl_context ctx ON a.contextid=ctx.id
			   WHERE userid={$USER->id}";
	  // echo $strsql . '<hr>';
	if ($ctxs = get_records_sql($strsql))	{
	 		
			foreach($ctxs as $ctx1)	{
				// print_r($ctx1); echo '<hr>';
				switch ($ctx1->contextlevel)	{
					case CONTEXT_SYSTEM: if ($ctx1->roleid == 1)	{ 
											$listclasses = -1;
										 }
										 break;	
										 				 	
					case CONTEXT_REGION: if ($ctx1->roleid == 8  || $ctx1->roleid == 10)	{
										 	$listclasses = -1;
										 }
										 break;	
										 	
    				case CONTEXT_RAYON:  if ($ctx1->roleid == 8 || $ctx1->roleid == 10) {
    										$listclasses = -1;
										 }
								 		 break;
								 		 
					case CONTEXT_SCHOOL: if ($ctx1->roleid <= 12)	{
    										$listclasses = -1;
										 }
										 break;
					/*
					case CONTEXT_DISCIPLINE: // $listclasses = -1;
										print_r($ctx1); echo '<hr>';
										$contexts = explode('/', $ctx1->path);
										$ctxpredmet = get_record('context', 'id', $contexts[6]);
										$cdid = $ctxpredmet->instanceid;
										$strsql = "SELECT id, classid FROM {$CFG->prefix}monit_school_class_discipline WHERE id=$cdid";
										if ($classdiscipline = get_record_sql ($strsql))	{
											$listclasses .= $classdiscipline->classid . ',';	
										}
										 break;
					*/					 
					case CONTEXT_DISCIPLINE:
					case CONTEXT_CLASS:  
										$contexts = explode('/', $ctx1->path);
										$ctxclass = get_record('context', 'id', $contexts[5]);
										$listclasses .= $ctxclass->instanceid . ',';
										break;
	 			}
	 			if ($listclasses == -1)  break;	
			}
	 }		 

	 // echo $listclasses . '<hr>';
	 
	 if ($listclasses == '') 	{
	 	return false;
	 } else if ($listclasses == -1) 	{
	 	$strsql = "SELECT id, name  FROM {$CFG->prefix}monit_school_class
	  				WHERE yearid=$yid AND schoolid=$sid
					ORDER BY parallelnum, name";
	 } else {	
	 	$listclasses .= '0';
	 	$strsql = "SELECT id, name  FROM {$CFG->prefix}monit_school_class
	  				WHERE yearid=$yid AND schoolid=$sid AND id in ($listclasses)
					ORDER BY parallelnum, name";
	 }

	// echo $listclasses;
		 
	$classmenu = array();
	// echo $strsql;
    if ($arr_group =  get_records_sql($strsql))	{
    	if (count($arr_group) > 1) {
	   		$classmenu[0] = get_string('selectaclass','block_mou_ege').' ...';
			foreach ($arr_group as $gr) {
				$classmenu[$gr->id] =$gr->name;
			}
		 	$ret =  '<tr><td>'.get_string('class', 'block_mou_school').':</td><td>';
  			$ret .=  popup_form($scriptname, $classmenu, 'switchgroup', $gid, '', '', '', true);
  			$ret .= '</td></tr>';
		} else {
  			$class = current($arr_group);
  			// $schoolmenu[$school->id] = $school->name;
  			$gid = $class->id;
		 	$ret =  '<tr><td>'.get_string('class', 'block_mou_school').':</td><td>';
  			$ret .=  "<b>$class->name</b>";
  			$ret .= '</td></tr>';
		} 
  	} else {
  		$ret = false;
  	}

  return $ret;
}


function listbox_discipline_class_role($scriptname, $sid, $yid, $gid, &$cdid)
{
  	global $CFG, $USER;

	$ret = false;
  
 	if ($sid == 0 || $gid == 0)  return false;
   
  	$listcdid = '';

	$strsql = "SELECT a.id, roleid, contextid, contextlevel, instanceid, path 
				FROM mdl_role_assignments a	RIGHT JOIN mdl_context ctx ON a.contextid=ctx.id
			   WHERE userid={$USER->id}";
	// echo $strsql . '<hr>';
	if ($ctxs = get_records_sql($strsql))	{
	 		
			foreach($ctxs as $ctx1)	{
				// print_r($ctx1); echo '<hr>';
				switch ($ctx1->contextlevel)	{
					case CONTEXT_SYSTEM: if ($ctx1->roleid == 1)	{ 
											$listcdid = -1;
										 }
										 break;	
										 				 	
					case CONTEXT_REGION: if ($ctx1->roleid == 8  || $ctx1->roleid == 10)	{
										 	$listcdid = -1;
										 }
										 break;	
										 	
    				case CONTEXT_RAYON:  if ($ctx1->roleid == 8 || $ctx1->roleid == 10) {
    										$listcdid = -1;
										 }
								 		 break;
								 		 
					case CONTEXT_SCHOOL: if ($ctx1->roleid <= 12)	{
    										$listcdid = -1;
										 }
										 break;
										 
					case CONTEXT_CLASS: $context_class = get_context_instance(CONTEXT_CLASS, $gid);
										if ($ctx1->instanceid == $context_class->instanceid)	{
											$listcdid = -1;	
										} 
										// print_r($ctx1); echo '<hr>';
										// print_r($context_class); echo '<hr>';
										
										break;
					
					case CONTEXT_DISCIPLINE: 
										if ($listcdid != -1)	{
										    $contexts = explode('/', $ctx1->path);
											$ctxpredmet = get_record('context', 'id', $contexts[6]);
											$listcdid .= $ctxpredmet->instanceid . ',';
										}	
										 break;
	 			}
	 			
	 			if 	($listcdid == -1) 	break;
			}
	 }		 

	 if ($listcdid == '') 	{
	 	return false;
	 } else if 	($listcdid == -1) 	{
	 	$strsql =  "SELECT id, name FROM {$CFG->prefix}monit_school_class_discipline
	  				WHERE classid = $gid 
					ORDER BY name";
	 } else {	
	 	$listcdid .= '0';
	 	$strsql =  "SELECT id, name FROM {$CFG->prefix}monit_school_class_discipline
	  				WHERE classid = $gid AND id in ($listcdid)
					ORDER BY name";
	 }

	// echo $listcdid;
		 
	$disciplinemenu = array();	
	// echo $strsql;
    if ($disciplines =  get_records_sql ($strsql))	{
    	if (count($disciplines) > 1) {
  			$disciplinemenu[0] = get_string('selectdiscipline', 'block_mou_school') . '...';
			foreach ($disciplines as $d) 	{
				$disciplinemenu[$d->id] = $d->name;
			}
		 	$ret =  '<tr><td>'.get_string('predmet', 'block_mou_school').':</td><td>';
  			$ret .=  popup_form($scriptname, $disciplinemenu, 'switchdisc', $cdid, '', '', '', true);
  			$ret .= '</td></tr>';
		} else {
  			$discipline = current($disciplines);
  			// $schoolmenu[$school->id] = $school->name;
  			$cdid = $discipline->id;
		 	$ret =  '<tr><td>'.get_string('classdiscipline', 'block_mou_school').':</td><td>';
  			$ret .=  "<b>$discipline->name</b>";
  			$ret .= '</td></tr>';
		} 
  	} else {
  		$ret = false;
  	}

  return $ret;
}


// Display list parallel number as popup_form
function listbox_parallelnum($scriptname, $rid, $sid, $yid, $pid)
{
  global $CFG;

  $strtitle = get_string('selectaparallelnum', 'block_mou_school') . ' ...';
  $groupmenu = array();

  $parmenu[0] = $strtitle;

  if ($sid != 0 && $yid != 0)   {
		$arr_group = get_records_sql ("SELECT distinct parallelnum  FROM {$CFG->prefix}monit_school_class
	 								  WHERE yearid=$yid AND schoolid=$sid
									  ORDER BY parallelnum");
		  if ($arr_group) 	{
				foreach ($arr_group as $gr) {
					$parmenu[$gr->parallelnum] =$gr->parallelnum;
				}
		  }
  }

  echo '<tr><td>'.get_string('parallelnum','block_mou_school').':</td><td>';
  popup_form($scriptname, $parmenu, 'switchpar', $pid, '', '', '', false);
  echo '</td></tr>';
  return 1;
}


// Display list parallel number as popup_form
function listbox_parallel_all($scriptname, $pid)
{
  global $CFG;

  $strtitle = get_string('selectaparallelnum', 'block_mou_school') . ' ...';
  $groupmenu = array();
  $parmenu[0] = $strtitle;
  for ($i = 1; $i <= $CFG->maxparallelnumber; $i++) {
	  	$parmenu[$i] = $i;
  }

  echo '<tr><td>'.get_string('parallelnum','block_mou_school').':</td><td>';
  popup_form($scriptname, $parmenu, 'switchpar', $pid, '', '', '', false);
  echo '</td></tr>';
  return 1;
}


// Display list discipline for all school
function listbox_discipline_school($scriptname, $sid, $yid, $did)
{
  global $CFG;

  $strtitle = get_string('selectdiscipline', 'block_mou_school') . '...';
  $disciplinemenu = array();

  $disciplinemenu[0] = $strtitle;

  if ($yid != 0 && $sid != 0)  {

		$disciplines =  get_records_sql ("SELECT id, name  FROM  {$CFG->prefix}monit_school_discipline
										  WHERE schoolid=$sid
										  ORDER BY name");
		if ($disciplines)	{
			foreach ($disciplines as $discipline) 	{
				$disciplinemenu[$discipline->id] = $discipline->name;
			}
		}
  }

  echo '<tr><td>'.get_string('predmet','block_mou_school').':</td><td>';
  popup_form($scriptname, $disciplinemenu, "switchdisc", $did, "", "", "", false);
  echo '</td></tr>';
  return 1;
}

// Display list discipline for parallel
function listbox_discipline_parallel($scriptname, $sid, $yid, $pid, $did)
{
  global $CFG;

  $strtitle = get_string('selectdiscipline', 'block_mou_school') . '...';
  $disciplinemenu = array();

  $disciplinemenu[0] = $strtitle;

  if ($yid != 0 && $sid != 0 && $pid != 0)  {

		$disciplines =  get_records_sql ("SELECT DISTINCT disciplineid FROM {$CFG->prefix}monit_school_curriculum
										  WHERE schoolid = $sid and parallelnum = $pid");
		if ($disciplines)	{
			foreach ($disciplines as $d) 	{
				$discipline =  get_record('monit_school_discipline', 'id', $d->disciplineid);
				$disciplinemenu[$discipline->id] = $discipline->name;
			}
		}
  }

  echo '<tr><td>'.get_string('classdisciplines','block_mou_school').':</td><td>';
  popup_form($scriptname, $disciplinemenu, "switchdisc", $did, "", "", "", false);
  echo '</td></tr>';
  return 1;
}

// Display list discipline for class (data gives from monit_school_curriculum)
/*
function listbox_discipline_class($scriptname, $sid, $yid, $gid, $cdid)
{
  global $CFG;

  $strtitle = get_string('selectdiscipline', 'block_mou_school') . '...';
  $disciplinemenu = array();

  $disciplinemenu[0] = $strtitle;

  if ($yid != 0 && $sid != 0 && $gid != 0)  {

		$disciplines =  get_records_sql ("SELECT DISTINCT disciplineid FROM {$CFG->prefix}monit_school_curriculum
										  WHERE yearid = $yid and schoolid = $sid and classid = $gid");
		if ($disciplines)	{
			foreach ($disciplines as $d) 	{
				$classdiscipline =  get_record('monit_school_class_discipline', 'classid', $gid, 'disciplineid', $d->disciplineid);
				$disciplinemenu[$classdiscipline->id] = $classdiscipline->name;
			}
		}
  }

  echo '<tr><td>'.get_string('classdisciplines','block_mou_school').':</td><td>';
  popup_form($scriptname, $disciplinemenu, "switchdisc", $cdid, "", "", "", false);
  echo '</td></tr>';
  return 1;
}
*/

// Display list discipline for class (data gives from monit_school_class_discipline)
// Old name listbox_discipline_class_subgroup
function listbox_discipline_class($scriptname, $sid, $yid, $gid, $cdid)
{
  global $CFG;

  $strtitle = get_string('selectdiscipline', 'block_mou_school') . '...';
  $disciplinemenu = array();

  $disciplinemenu[0] = $strtitle;

  if ($yid != 0 && $sid != 0 && $gid != 0)  {

		$disciplines =  get_records_sql ("SELECT id, name FROM {$CFG->prefix}monit_school_class_discipline
										  WHERE classid = $gid ");
		if ($disciplines)	{
			foreach ($disciplines as $d) 	{
				$disciplinemenu[$d->id] = $d->name;
			}
		}
  }

  echo '<tr><td>'.get_string('classdisciplines', 'block_mou_school').':</td><td>';
  popup_form($scriptname, $disciplinemenu, "switchdisc", $cdid, "", "", "", false);
  echo '</td></tr>';
  return 1;
}

// Display list school profiles
function listbox_profiles($scriptname, $sid, $pid)
{
  global $CFG;

  $strtitle = get_string('selectprofile', 'block_mou_school') . '...';
  $profilemenu = array();

  $profilemenu[0] = $strtitle;

  if ($sid != 0)  {

		$profiles =  get_records_select('monit_school_profiles_curriculum', "schoolid=$sid", '', 'id, name');
		if ($profiles)	{
			foreach ($profiles as $p) 	{
				$profilemenu[$p->id] = $p->name;
			}
		}
  }

  echo '<tr><td>'.get_string('curriculumprofiles','block_mou_school').':</td><td>';
  popup_form($scriptname, $profilemenu, "switchprofile", $pid, "", "", "", false);
  echo '</td></tr>';
  return 1;
}


// Display list school profiles
function listbox_components($scriptname, $sid, $cid)
{
  global $CFG;

  $strtitle = get_string('selectcomponent', 'block_mou_school') . '...';
  $componentmenu = array();

  $componentmenu[0] = $strtitle;

  if ($sid != 0)  {

		$components =  get_records_select('monit_school_component', "schoolid = $sid", '', 'id, name');
		if ($components)	{
			foreach ($components as $p) 	{
				$componentmenu[$p->id] = $p->name;
			}
		}
  }

  echo '<tr><td>'.get_string('curriculumcomponent','block_mou_school').':</td><td>';
  popup_form($scriptname, $componentmenu, "switchcomponent", $cid, "", "", "", false);
  echo '</td></tr>';
  return 1;
}

function listbox_discipline_subgroup($scriptname, $sid, $yid, $gid, $did)
{
  global $CFG;

  $strtitle = get_string('sbselect', 'block_mou_school') . '...';
  $disciplinemenu = array();

  $disciplinemenu[0] = $strtitle;

  if ($yid != 0 && $sid != 0 && $gid != 0)  {
		$classdisciplines = get_records_sql ("SELECT id, name FROM {$CFG->prefix}monit_school_class_discipline
									WHERE classid=$gid and schoolsubgroupid <> 0
									ORDER BY name");
		if ($classdisciplines)	{
			foreach ($classdisciplines as $cd) {
				$disciplinemenu[$cd->id] = $cd->name;
			}
		}
  }

  echo '<tr><td>'.get_string('subgroup','block_mou_school').':</td><td>';
  popup_form($scriptname, $disciplinemenu, "switchdisc", $did, "", "", "", false);
  echo '</td></tr>';
  return 1;
}

function print_tabs_schedule($scriptname, $tab)
{
    global $CFG;
/*
    $toprow = array();
    $toprow[] = new tabobject('0', $scriptname. "&amp;nd=0",
    	            get_string('numday_0', 'block_mou_ege'));
    for ($i=1; $i<=$maxcnt; $i++)	{
	    $toprow[] = new tabobject($i, $scriptname. "&amp;nd=$i",
    	            get_string('numday_i', 'block_mou_ege', $i));
    }

    $tabs = array($toprow);
    print_tabs($tabs, $tab, NULL, NULL);
*/
}

function listbox_popup_viewbytime($scriptname, $sid, $yid, $rid)
{
  global $CFG;

  $strtitle = get_string('viewselect', 'block_mou_school') . '...';
  $disciplinemenu = array();

  $disciplinemenu[0] = $strtitle;

  if ($yid != 0 && $sid != 0 && $gid != 0)  {
		$classdisciplines = get_records_sql ("SELECT id, name FROM {$CFG->prefix}monit_school_class_discipline
									WHERE classid=$gid and schoolsubgroupid <> 0
									ORDER BY name");
		if ($classdisciplines)	{
			foreach ($classdisciplines as $cd) {
				$disciplinemenu[$cd->id] = $cd->name;
			}
		}
  }

  echo '<tr><td>'.get_string('subgroup','block_mou_school').':</td><td>';
  popup_form($scriptname, $disciplinemenu, "switchdisc", $did, "", "", "", false);
  echo '</td></tr>';
  return 1;
}


// Display list days in week
function listbox_weekday($scriptname, $sid, $yid, $wid)
{
	
  $strtitle = get_string('selectweekdaynum', 'block_mou_school') . '...';
  $options = array();

  $options[0] = $strtitle;

  if ($yid != 0 && $sid != 0)  {

        $options[1] = get_string('monday', 'calendar');
        $options[2] = get_string('tuesday', 'calendar');
        $options[3] = get_string('wednesday', 'calendar');
        $options[4] = get_string('thursday', 'calendar');
        $options[5] = get_string('friday', 'calendar');
        $options[6] = get_string('saturday', 'calendar');
        $options[7] = get_string('sunday', 'calendar');
  }

  echo '<tr><td>'.get_string('weekdaynum','block_mou_school').':</td><td>';
  popup_form($scriptname, $options, "switchweek", $wid, "", "", "", false);
  echo '</td></tr>';
  return 1;
}

function listbox_terms($scriptname, $sid, $yid, $gid, &$termid, $allterm=false)
{
  global $CFG;

  $strtitle = get_string('selectperiod', 'block_mou_school') . ' ...';
  $termmenu = array();
  $termmenu[0] = $strtitle;

  if ($sid != 0 && $yid != 0 && $gid != 0)   {
  		$class = get_record_select('monit_school_class', "id = $gid", 'id ,parallelnum');
 		if ($class_termtype = get_record_select('monit_school_class_termtype', "schoolid = $sid AND parallelnum = $class->parallelnum", 'termtypeid'))	{
			  if ($school_terms = get_records_select('monit_school_term', "schoolid = $sid AND  termtypeid = {$class_termtype->termtypeid}", '', 'id, name, datestart, dateend')) 	{
					foreach ($school_terms as $st) {
						$termmenu[$st->id] =$st->name;
						if ($termid == 0) {
							$strday = date("Y-m-d", time());
							if ($st->datestart <= $strday && $strday <= $st->dateend)		{
								$termid = $st->id;
							}	 
						}
					}
                    
                    if ($allterm)   {
                        $termmenu[-1] = 'Все периоды';
                    }
			  }
		} else {
  			echo '<tr><td>'.get_string('studyperiod','block_mou_school').':</td><td>';
			print_string('notfoundtypestudyperiod', 'block_mou_school');
			echo '</td></tr>';
		    return 0;
		}	  
  }

  echo '<tr><td>'.get_string('studyperiod','block_mou_school').':</td><td>';
  popup_form($scriptname, $termmenu, 'switcterm', $termid, '', '', '', false);
  echo '</td></tr>';
  return 1;
}

function listbox_edu_year_months($scriptname, $rid, $sid, $yid, $uid, $gid, $mon)

{
  global $CFG;
  
	if ($yid != 0 && $sid != 0 && $gid !=0)  {
	
		$monthmenu = array();
	    $monthmenu[9] = get_string('september', 'calendar');
	    $monthmenu[10] = get_string('october', 'calendar');
	    $monthmenu[11] = get_string('november', 'calendar');
	    $monthmenu[12] = get_string('december', 'calendar');
	    $monthmenu[1] = get_string('january', 'calendar');
	    $monthmenu[2] = get_string('february', 'calendar');
	    $monthmenu[3] = get_string('march', 'calendar');
	    $monthmenu[4] = get_string('april', 'calendar');
	    $monthmenu[5] = get_string('may', 'calendar');
	    $monthmenu[6] = get_string('june', 'calendar');
	    $monthmenu[7] = get_string('july', 'calendar');
	    $monthmenu[8] = get_string('august', 'calendar');
	
		$g = get_string('g','block_mou_school');
	    $eduyear = current_edu_year();
	    list($yfirst, $ysecond) = explode('/', $eduyear);
		
		for ($i=9; $i<=12; $i++){
			$monthmenu[$i] =$monthmenu[$i].'  '.$yfirst. $g;  
		}
		for ($i=1; $i<=8; $i++){
			$monthmenu[$i] = $monthmenu[$i].'  '.$ysecond. $g;
		}
	
	  echo '<tr><td>'.get_string('month','block_mou_school').':</td><td>';
	  popup_form($scriptname, $monthmenu, "switchdisc", $mon, "", "", "", false);
	  echo '</td></tr>';
	  return 1;
	}
}


function make_all_weeks_in_year($datestart, $dateend)
{
  global $CFG, $GLDATESTART;

  $allweeksinyear = array();

	$arr_date = explode('-', $datestart);
	$datestart = make_timestamp ($arr_date[0], $arr_date[1], $arr_date[2], 12);
	$arr_date = explode('-', $dateend);
	$dateend = make_timestamp ($arr_date[0], $arr_date[1], $arr_date[2], 12);
	
	$numdayinweek = date('w', $datestart);
	if ($numdayinweek == 0) 	{
		$datestart = $datestart + DAYSECS;
	} else {
		$datestart = $datestart - DAYSECS*($numdayinweek-1);
	}
	
	$num = 1;				
	while ($datestart < $dateend)  {
	
		$GLDATESTART[$num] = $datestart;
		$firstdayweek = date("d.m.y", $datestart);
	
		$lastdayweek = date("d.m.y", $datestart + DAYSECS*6);
		$datestart = $datestart + 7*DAYSECS;
		$allweeksinyear[$num] = "$num: $firstdayweek - $lastdayweek";
		$num++;	
	}

  return $allweeksinyear;				
}



function listbox_all_weeks_year($scriptname, $allweeksinyear, &$nw)
{
  global $CFG, $GLDATESTART;

  $allweeksmenu = array();

  foreach ($allweeksinyear as $num => $stroneweek)	{
  			$allweeksmenu[$num] = $stroneweek;
  }
  
  if ($nw == 0)	{
  	$currtime = time();
  	foreach ($GLDATESTART as $numweek => $unixtime)	{
  		if ($unixtime > $currtime) { 
  			$nw = $numweek-1;
  			break;
  		}	
  	}
  }
  
  if ($nw == 0) $nw = 1;
  
  echo '<tr><td>'.get_string('oneweek', 'block_mou_school').':</td><td>';
  popup_form($scriptname, $allweeksmenu, 'switcweek', $nw, '', '', '', false);
  echo '</td></tr>';
  return 1;
}


function get_list_old_new_id ($table, $yid)
{
	$lastyid = $yid-1;
	
	$edusids = array();
	$edus = get_records($table, 'yearid', $lastyid);
	foreach($edus as $edu)	{
		if ($edu->isclosing == false)	{
			$edusids[$edu->uniqueconstcode]->oldid = $edu->id;
		}	
	}
	$edus = get_records($table, 'yearid', $yid);
	foreach($edus as $edu)	{
		if ($edu->isclosing == false)	{
			$edusids[$edu->uniqueconstcode]->newid = $edu->id;
		}	
	}
	
	$newedusids = array();
	foreach ($edusids as $eid)	{
		$newedusids[$eid->oldid] = $eid->newid;
	}
	
	return $newedusids;
}


function get_classid_lastyear ($yid)
{
	$newedusids = array();

	$classes = get_records_select('monit_school_class', "yearid = $yid", 'id, classidold');
	foreach($classes as $class)	{
		$newedusids[$class->classidold] = $class->id;
	}
	
	return $newedusids;
}

/*
// INSERT INTO mdl_config 
// maxparallelnumber = 12
function get_max_parallel_number()
{
	if ($config = get_record('config', 'name', 'maxparallelnumber'))	{
		if (isset($config->value)) return $config->value;
	} else {
		$rec->name = 'maxparallelnumber';
		$rec->value = 12;
		insert_record('config', $rec);
		return $rec->value;
	}
}
*/


function has_capability_editlessonsplan($sid, $did)	
{
	global $CFG;
	
	$edit_capability_discipline = false;	

 	$strsql = "SELECT id FROM {$CFG->prefix}monit_school_class_discipline
				WHERE schoolid = $sid and disciplineid = $did";
			
	if ($classdisciplines =  get_records_sql ($strsql))	{
		foreach ($classdisciplines as $cd) 	{
			$ctx1 = get_context_instance(CONTEXT_DISCIPLINE, $cd->id);
			// print_r($ctx1); echo '<hr>';
			/*if (record_exists_select('role_assignments', "roleid = 15 and contextid = {$ctx1->id} and userid = {$USER->id}"))	{
				$edit_capability_discipline = true;
				break;
			}*/	
			
			if (has_capability('block/mou_school:editlessonsplan', $ctx1))	{
				$edit_capability_discipline = true;
				break;
			}
		}
	}
	
	return 	$edit_capability_discipline;
}		


function listbox_term_weeks($scriptname, $allweekmenu, $termid, $numweek)
{
  global $CFG;

  $weekmenu = array();
  
  if ($termid != 0)   {
  		foreach ($allweekmenu[$termid] as $num => $stroneweek)	{
  			$weekmenu[$num] = $stroneweek;
  		}
  }

  echo '<tr><td>'.get_string('oneweek', 'block_mou_school').':</td><td>';
  popup_form($scriptname, $weekmenu, 'switcweek', $numweek, '', '', '', false);
  echo '</td></tr>';
  return 1;
}


function make_term_weeks_menu($sid, $yid, $gid)
{
  global $CFG;

  $allweekmenu = array();

  if ($sid != 0 && $yid != 0 && $gid != 0)   {
	$class = get_record_select('monit_school_class', "id = $gid", 'id, parallelnum');
	$class_termtype = get_record_select('monit_school_class_termtype', "schoolid = $sid AND parallelnum = {$class->parallelnum}" , 'termtypeid');
	$school_terms = get_records_select('monit_school_term', "schoolid = $sid AND  termtypeid = {$class_termtype->termtypeid}", '', 'id, name, datestart, dateend');
    if ($school_terms) 	{
	  		$num = 1;
	  		$allfirstdays = array();
			foreach ($school_terms as $school_term) 	{
				
				$arr_date = explode('-', $school_term->datestart);
				$datestart = make_timestamp ($arr_date[0],  $arr_date[1], $arr_date[2], 12);
				$arr_date = explode('-', $school_term->dateend);
				$dateend = make_timestamp ($arr_date[0],  $arr_date[1], $arr_date[2], 12);
				
				$numdayinweek = date('w', $datestart);
				if ($numdayinweek == 0) 	{
					$datestart = $datestart + DAYSECS;
				} else {
					$datestart = $datestart - DAYSECS*($numdayinweek-1);
				}
				
				$allweekmenu[$school_term->id] = array();
				
				while ($datestart < $dateend)  {
					$firstdayweek = date("d.m.y", $datestart);
					$lastdayweek = date("d.m.y", $datestart + DAYSECS*6);
					$datestart = $datestart + 7*DAYSECS;
					if (!in_array($firstdayweek, $allfirstdays)) {
						$allfirstdays[$num] = $firstdayweek;
						$allweekmenu[$school_term->id][$num] = "$num: $firstdayweek - $lastdayweek";
						$num++;				
					} else  {
						$key = array_search($firstdayweek, $allfirstdays);
						$allweekmenu[$school_term->id][$key] = "$key-СЏ: $firstdayweek - $lastdayweek";
					}
				}
			}
	  }
  }

  return $allweekmenu;
}


function listbox_weekday_with_date($scriptname, $nw, $wid)
{
	global $CFG, $GLDATESTART, $GLDAY;
 	$weekmenu = array();
 	$weekmenu[0] = get_string('selectweekdaynum', 'block_mou_school') . '...';
	if ($nw != 0)  {
        $weekmenu[1] = get_string('monday', 'calendar');
        $weekmenu[2] = get_string('tuesday', 'calendar');
        $weekmenu[3] = get_string('wednesday', 'calendar');
        $weekmenu[4] = get_string('thursday', 'calendar');
        $weekmenu[5] = get_string('friday', 'calendar');
        $weekmenu[6] = get_string('saturday', 'calendar');
        $weekmenu[7] = get_string('sunday', 'calendar');     
		$datestart = $GLDATESTART[$nw];
        for ($i=1; $i<=7; $i++)  {
        	$GLDAY[$i] = date("Y-m-d", $datestart);
        	$dayweek = date("d.m.y", $datestart);
			$weekmenu[$i] .= ': '.$dayweek;
			$datestart = $datestart + DAYSECS;
		}
	}	
	echo '<tr><td>'.get_string('weekdaynum', 'block_mou_school').':</td><td>';
	popup_form($scriptname, $weekmenu, "switchdayinweek", $wid, "", "", "", false);
	echo '</td></tr>';	
	return 1;
}	

function check_holiday($holidays, $class, $strday)
{
	global 	$datestartGLOB,	$dateendGLOB;	

	$ret = false;
	
	if ($strday < $datestartGLOB || $strday > $dateendGLOB) {
		return true;	
	} 
	
	$strclass = ',' . $class->parallelnum . ',';
	if ($holidays) foreach ($holidays as $holiday)	{
		$strhol = '0,'. $holiday->parallelnum;
		$pos = strpos($strhol, $strclass);
		if ($pos === false) continue;
		if ($holiday->datestart <= $strday && $strday <= $holiday->dateend)		{
			$ret = true;
			break;
		} 
	}

	return $ret;
}

function listbox_teaches_in_school($scriptname, $rid, $sid, $teachid)
{
  global $CFG;

  $strtitle = get_string('selectateacher', 'block_mou_school') . '...';
  $teachersmenu = array();

  $teachersmenu[0] = $strtitle;

  if ($rid != 0 && $sid != 0)  {

	   $steachersql = "SELECT u.id, u.username, u.firstname, u.lastname FROM {$CFG->prefix}user u
	  	              LEFT JOIN {$CFG->prefix}monit_att_staff t ON t.userid = u.id
	  	              WHERE t.schoolid=$sid AND u.deleted = 0 AND u.confirmed = 1";
	   $steachersql .= ' ORDER BY u.lastname';
       $steachers = get_records_sql($steachersql);

       $ssharedsql = "SELECT u.id, u.firstname, u.lastname
                        FROM mdl_user u  INNER JOIN mdl_monit_att_staff t ON t.userid = u.id
                        INNER JOIN mdl_monit_att_staffshared ss ON t.id = ss.staffid
                        WHERE ss.schoolid=$sid AND u.deleted = 0 AND u.confirmed = 1
                        ORDER BY u.lastname";
       if ($sshareds = get_records_sql($ssharedsql)) {
            foreach ($sshareds as $sshared) {
                $steachers[$sshared->id] = $sshared;          
            }
       }                

	   if ($steachers)	{
	  		foreach ($steachers as $steach) {
	  		//	$name = truncate_school_name($school->name);
				$teachersmenu[$steach->id] = fullname($steach);
			}
       }
  }

  echo '<tr><td>'.get_string('teacher','block_mou_school').':</td><td>';
  popup_form($scriptname, $teachersmenu, "switchteacher", $teachid, "", "", "", false);
  echo '</td></tr>';
  return 1;
}


function move_pupil_in_leave_school($rid, $sid, $yid, $gid, $pupil)
{
	global $CFG, $schoolout, $user; 
	    	 
	$rec->userid = $pupil->userid;
	$rec->county  = '';
	$rec->rayon  = '';
	$rec->naspunkt = '';
	$rec->school = '';
	$rec->class = '';
	$rec->rayoninid = $rid;
	$rec->schoolinid = $sid;
	$rec->classinid  = $gid;
	$dateout = date('Y-m-d');	    	
	$rec->dateout  = $dateout;	
	    	 
	if (!insert_record('monit_school_movepupil', $rec))   {
	  error(get_string('errorinupdateprofilepupil','block_mou_ege'), $redirlink);
	}	
	
    $strsql = "SELECT id FROM {$CFG->prefix}monit_nsop_pupil where userid=$pupil->userid";
    if ($nsop = get_record_sql($strsql)) {
        $pupil_card_u->id = $pupil->id;
        $pupil_card_u->schoolid = 0;
        $pupil_card_u->classid =  0;
        $pupil_card_u->nsop =  1;
        $pupil_card_u->typeemployment = 8;
        update_record('monit_school_pupil_card', $pupil_card_u);
        
        $msg = get_string('leaveandnsop', 'block_mou_school', fullname($user, true));
	    $msg .= '"' . mb_substr($schoolout->name, 0,  64) . '... "';
        notify($msg, 'green');
		return;  
    }

	$msg = get_string('leavededactivity', 'block_mou_school', fullname($user, true));
	$msg .= '"' . mb_substr($schoolout->name, 0,  64) . '... "';

	// find  classid  in ID_SCHOOL_FOR_DELETED school
	if($class1 = get_record('monit_school_class', 'id', $gid)) 	{
		// print_r($class1);
        		
		$classname = $class1->name;
		
		$id_specialschool = ID_SCHOOL_FOR_DELETED;
        if($classes = get_records_sql("SELECT id, name FROM {$CFG->prefix}monit_school_class
		                                WHERE yearid=$yid and schoolid=$id_specialschool")){
                             	
			foreach ($classes as $class) {
				if ($classname == $class->name) {  
				    $pupil_card_u->id = $pupil->id;
   	                $pupil_card_u->rayonid = 25;
   	                $pupil_card_u->schoolid = ID_SCHOOL_FOR_DELETED;
   	                $pupil_card_u->classid = $class->id;
	                update_record('monit_school_pupil_card', $pupil_card_u);
	                notify($msg, 'green');
					return;  
				}
			}
        }    	               
		$rec->rayonid = 25; 
		$rec->schoolid = ID_SCHOOL_FOR_DELETED;
		$rec->yearid = $yid;
		$rec->name = $classname;
		$rec->parallelnum = $class1->parallelnum;
		$rec->timecreated = time();
		$rec->timeadded = time();
		
		if(!$newid = insert_record('monit_school_class', $rec)) {
			error('There are some errors in insert new class.', $redirlink);				               			
		}     
		$pupil_card_u->id = $pupil->id;
		$pupil_card_u->rayonid = 25;
		$pupil_card_u->schoolid = ID_SCHOOL_FOR_DELETED;
		$pupil_card_u->classid = $newid;
		update_record('monit_school_pupil_card', $pupil_card_u); 
        notify($msg, 'green');
		return;  
	}
}	


function record_exists_mou($table, $field1='', $value1='', $field2='', $value2='', $field3='', $value3='') {

    global $CFG;

    $select = where_clause($field1, $value1, $field2, $value2, $field3, $value3);

    return record_exists_sql('SELECT id FROM '. $CFG->prefix . $table .' '. $select);
}

?>