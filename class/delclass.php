<?PHP // $Id: delclass.php,v 1.5 2011/10/21 07:10:11 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_school.php');


    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = required_param('sid', PARAM_INT);       // School id
	// $cid = required_param('cid', PARAM_INT);		  // Curriculum id
    $yid = required_param('yid', PARAM_INT);       // Year id
    $gid = required_param('gid', PARAM_INT);          // Class id
	$confirm = optional_param('confirm');
	$action   = optional_param('action', 'action');
 
	require_login();

	$context = get_context_instance(CONTEXT_SCHOOL, $sid);
	if (!has_capability('block/mou_school:editclasslist', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

	$context_rayon = get_context_instance(CONTEXT_RAYON, $rid);
	$edit_capability_rayon = has_capability('block/mou_school:editclasslist', $context_rayon);

	$context_region = get_context_instance(CONTEXT_REGION, 1);
	$edit_capability_region = has_capability('block/mou_school:editclasslist', $context_region);


	if ($edit_capability_region) 	{ // || $edit_capability_rayon
	   $action = 'clear';
    }   

	$strtitle = get_string('pupil','block_mou_school');
	$strclasses = get_string('classes','block_mou_ege');
	$strclass = get_string('class','block_mou_ege');
	$strpupils = get_string('pupils', 'block_mou_ege');

	$breadcrumbs = '<a href="'.$CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('title','block_mou_school').'</a>';
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/mou_school/class/classlist.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">$strclasses</a>";
	$breadcrumbs .= "-> $strclass";
    print_header("$SITE->shortname: $strpupils", $SITE->fullname, $breadcrumbs);

	$redirlink = $CFG->wwwroot."/blocks/mou_school/class/classlist.php?mode=3&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid";
	if (!$class = get_record('monit_school_class', 'id', $gid)) {
        error("Class not found!", $redirlink);
	}

	if (isset($confirm)) {
		$countpupils = count_records('monit_school_pupil_card', 'classid',  $class->id);
		if ($countpupils == 0)		{
			delete_records('monit_school_class', 'id', $gid);
			add_to_log(1, 'mou_school', 'Class deleted', 'delclass.php', $USER->lastname.' '.$USER->firstname);
			redirect($redirlink , get_string('classdeleted', 'block_mou_ege', $class->name), 3);
		} else	{
            check_dead_shower($class->id);
		    if ($action == 'clear') {
    	 		$schoolout = get_record('monit_school', 'id', ID_SCHOOL_FOR_DELETED);
                if ($edit_capability_region) 	{ // || $edit_capability_rayon    	 		
				    $pupils = get_records('monit_school_pupil_card', 'classid',  $class->id);
				    foreach ($pupils as $pupil) {
				    	/*
		                role_unassign(0, $pupil->userid);
		           		delete_records('monit_school_pupil_card', 'userid', $pupil->userid);
		           		delete_records('user', 'id', $pupil->userid);
		           		*/
                        $user = get_record_sql("SELECT id, lastname, firstname FROM {$CFG->prefix}user WHERE id=$pupil->userid");
		           		move_pupil_in_leave_school($rid, $sid, $yid, $gid, $pupil);
				    }
	    			delete_records('monit_school_class', 'id', $gid);
		 			redirect($redirlink, get_string('classdeleted', 'block_mou_ege', $class->name), 3);
				}
		    }
			error(get_string('errorindelclass', 'block_mou_ege'), $redirlink);
		}
	}

	print_heading(get_string('deletingclass','block_mou_ege') .' :: ' .$class->name);

	notice_yesno(get_string('deletecheckfull', '', $class->name . ' ' . $strclass),
               "delclass.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;confirm=1&amp;action=$action", $redirlink);

	print_footer();
    

function check_dead_shower($classid)    
{
    global $CFG;
    
    if ($pupils = get_records_select('monit_school_pupil_card', "classid=$classid", '', 'id, userid'))  {
        foreach($pupils as $pupil)  {
            if ($user0 = get_record_select('user', "id = $pupil->userid", 'id, deleted'))    {
                if ($user0->deleted == 1)   {
                    delete_records('monit_school_pupil_card', 'id', $pupil->id);
                }
            } else {
                delete_records('monit_school_pupil_card', 'id', $pupil->id);
            }
        }    
    }
    
}    
?>