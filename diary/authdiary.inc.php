<?php // $Id: authdiary.inc.php,v 1.4 2012/01/13 10:46:39 shtifanov Exp $

    $rid   = optional_param('rid', 0, PARAM_INT); //year
    $sid   = optional_param('sid', 0, PARAM_INT); //year
    $yid   = required_param('yid', PARAM_INT); //year
   
    $uid = optional_param('uid', 0, PARAM_INT);      // User id
    $gid = optional_param('gid', 0, PARAM_INT);	  // Class id (was group id in dean)	
    $did = optional_param('did', 0, PARAM_INT);	  // Discipline id
    $cdid = optional_param('cdid', 0, PARAM_INT);	  // Class discipline id    
    $period = optional_param('p', 'week'); // Period time: day, week, month, year
    $cv = optional_param('cv', 0, PARAM_INT);   // View by times (0) or rooms (1) 	
    $nw = optional_param('nw', 0, PARAM_INT);   // Number of week in study year
//	$wid = optional_param('wid', 0, PARAM_INT);   // Day number in week
	$sh = optional_param('sh', 0, PARAM_INT); 		//display view
	$cw = optional_param('cw', 0, PARAM_INT);    //classwork
    $day  = optional_param('cal_d', 0, PARAM_INT); //Day
    $mon  = optional_param('mon', 9, PARAM_INT); //Month
    $yr   = optional_param('cal_y', 0, PARAM_INT); //year

	$strtitle = get_string('diary', 'block_mou_school');
	$strcard = get_string('title', 'block_mou_school');		
	$redirlink = "{$CFG->wwwroot}/blocks/mou_school/index.php";
	
	$breadcrumbs = "<a href=\"{$redirlink}\">$strcard</a>";
	$breadcrumbs .= "->$strtitle";
	print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);	
	
	$admin_is = isadmin();
	$pupil_is = ispupil();
	$region_operator_is = ismonitoperator('region');
	$rayon_operator_is  = ismonitoperator('rayon', 0, 0, 0, true);
	if (!$admin_is && !$pupil_is && !$region_operator_is && !$rayon_operator_is) {
        error(get_string('adminaccess', 'block_monitoring'), "$CFG->wwwroot/login/index.php");
	}

	$scriptname = basename($_SERVER['PHP_SELF']);	// echo '<hr>'.basename(me());
	
	if ($admin_is || $region_operator_is || $rayon_operator_is)	{
		// $uid = 121612;
		$strlistrayons  =  listbox_rayons_role("$scriptname?sid=0&amp;yid=$yid&amp;rid=", $rid);
		$strlistschools =  listbox_schools_role("$scriptname?rid=$rid&amp;yid=$yid&amp;sid=", $rid, $sid, $yid);

		if (!$strlistrayons && !$strlistschools)   { 
			error(get_string('permission', 'block_mou_school'), '../index.php');
		}
		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		echo $strlistrayons;
		echo $strlistschools;
		
		if ($rid == 0 || $sid == 0) {
			echo '</table>';
		    print_footer();
		 	exit();
		}

	    listbox_class("$scriptname?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=", $rid, $sid, $yid, $gid);
	    listbox_pupils("$scriptname?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid=", $rid, $sid, $yid, $gid, $uid);
		echo '</table>';

	} else {
		$uid = $USER->id;
	}
	
	$strminipupilcard = '';
   if($user = get_record_select('user', "id = $uid", 'id, lastname, firstname')){
	    //	echo $uid;
    	if($card = get_record_select('monit_school_pupil_card', "userid = $user->id AND  yearid = $yid", 'id, rayonid, schoolid, classid')){
    	
			$rayon = get_record_select('monit_rayon', "id = $card->rayonid", 'id, name');
    		$school = get_record_select('monit_school', "id = $card->schoolid", 'id, name, uniqueconstcode');
    		$class = get_record_select('monit_school_class', "id = $card->classid", 'id, name, parallelnum');
      		$rid = $rayon->id;
      		$sid = $school->id;
      		$gid = $class->id;
      		
 			$strminipupilcard = print_heading(get_string('diary', 'block_mou_school').': '.$user->lastname.' '.$user->firstname, 'center', 3, '', true);  		

			$strminipupilcard .= '<br>';
		    $strminipupilcard .= '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	  
		  	$strminipupilcard .= '<tr><td>'.get_string('rayon', 'block_mou_school').':</td><td>';
		  	$strminipupilcard .= $rayon->name;
		  	$strminipupilcard .= '</td></tr>';
		  	
		  	$strminipupilcard .= '<tr><td>'.get_string('school', 'block_mou_school').':</td><td>';
		  	$strminipupilcard .= $school->name;
		  	$strminipupilcard .= '</td></tr>';
		  	
		  	$strminipupilcard .= '<tr><td>'.get_string('class', 'block_mou_school').':</td><td>';
		  	$strminipupilcard .= $class->name;
		  	$strminipupilcard .= '</td></tr>';

	  	  	$strminipupilcard .= '</table>';
 
    	}
    }

	
?>