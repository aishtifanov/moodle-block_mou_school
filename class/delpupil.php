<?PHP // $Id: delpupil.php,v 1.14 2011/02/04 11:03:47 shtifanov Exp $

    require_once("../../../config.php");
	require_once($CFG->libdir.'/adminlib.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_school.php');

    $rid = required_param('rid', PARAM_INT);                 // Rayon id
    $sid = required_param('sid', PARAM_INT);                 // School id
    $yid = required_param('yid', PARAM_INT);          // Year id
    $gid = required_param('gid', PARAM_INT);          // Group id
    $delete  = required_param('uid', PARAM_INT);
	$confirm = optional_param('confirm');

	require_login();

	$context = get_context_instance(CONTEXT_SCHOOL, $sid);
	if (!has_capability('block/mou_school:editclasslist', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

    $pupil = get_record_select('monit_school_pupil_card', "userid = $delete AND yearid = $yid", 'id, userid');
    if (!$user = get_record_sql("SELECT id, lastname, firstname FROM {$CFG->prefix}user WHERE id=$delete")) {
            error("No such user!");
    }
    $fullname = fullname($user);

    $strtitle = get_string('pupil','block_mou_school');
    $strclasses = get_string('classes','block_mou_school');
	$strclass = get_string('class','block_mou_school');
	$strpupils = get_string('pupils', 'block_mou_school');

	$breadcrumbs = '<a href="'.$CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('title','block_mou_school').'</a>';
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/mou_school/class/classlist.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">$strclasses</a>";
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/mou_school/class/classpupils.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid\">$strpupils</a>";
	$breadcrumbs .= "-> $strtitle";
    print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

	$redirlink = "{$CFG->wwwroot}/blocks/mou_school/class/classpupils.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid";

/*
	$context = get_context_instance(CONTEXT_REGION, 1);
	if (!has_capability('block/mou_school:editclasslist', $context))	{
        error(get_string('accesstemporarylock', 'block_mou_school'), '../index.php');
	}
*/

 	if ($delete and confirm_sesskey()) {              // Delete a selected user, after confirmation

        // if (!has_capability('moodle/user:delete', $sitecontext)) {
            // error('You do not have the required permission to delete a user.');
        // }
        $primaryadmin = get_admin();
        if ($user->id == $primaryadmin->id) {
            error("You are not allowed to delete the primary admin user!", '', true);
        }

        if ($confirm != md5($delete)) {
            $fullname = fullname($user, true);
            print_heading(get_string('pupilleaveschool', 'block_mou_school'));
            $optionsyes = array('rid'=>$rid, 'sid'=>$sid, 'yid'=>$yid, 'gid'=>$gid, 'uid'=>$delete,
            					'confirm'=>md5($delete), 'sesskey'=>sesskey());
	        notice_yesno(get_string('leavecheckfull', 'block_mou_school', "'$fullname'"), 'delpupil.php', $redirlink, $optionsyes, $optionsyes, 'post', 'get');
	    } else {
	    	 $schoolout = get_record('monit_school', 'id', ID_SCHOOL_FOR_DELETED);
                     
             move_pupil_in_leave_school($rid, $sid, $yid, $gid, $pupil);
             
			 redirect($redirlink, '', 5); 
	    }
    }

	print_footer();	
?>