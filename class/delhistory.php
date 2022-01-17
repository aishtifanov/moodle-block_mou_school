<?PHP // $Id: delhistory.php,v 1.2 2010/08/23 08:47:56 Shtifanov Exp $

    require_once("../../../config.php");
	require_once($CFG->libdir.'/adminlib.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');

    $rid = required_param('rid', PARAM_INT);                 // Rayon id
    $sid = required_param('sid', PARAM_INT);                 // School id
    $yid = required_param('yid', PARAM_INT);          // Year id
    $gid = required_param('gid', PARAM_INT);          // Group id
    $uid  = required_param('uid', PARAM_INT);
	$id   = required_param('id', PARAM_INT);
	$confirm = optional_param('confirm');

	require_login();

	$context = get_context_instance(CONTEXT_SCHOOL, $sid);
	if (!has_capability('block/mou_school:editclasslist', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	


    $pupil = get_record('monit_school_pupil_card', 'userid', $uid, 'yearid', $yid);
    $user = get_record_sql("SELECT id, lastname, firstname FROM {$CFG->prefix}user WHERE id=$uid");
    $fullname = fullname($user);

    $strtitle = get_string('delitinhistory','block_mou_school');

	$breadcrumbs = '<a href="'.$CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('title','block_mou_school').'</a>';
	$breadcrumbs .= '-> <a href="'.$CFG->wwwroot."/blocks/mou_school/class/movingpupils.php?rid=0&amp;sid=0\">".get_string('movingpupils','block_mou_school').'</a>';
	$breadcrumbs .= '-> <a href="'.$CFG->wwwroot."/blocks/mou_school/class/history.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;gid=$gid&amp;uid=$uid\">".get_string('history','block_mou_school').'</a>';
	$breadcrumbs .= "-> $strtitle";
    print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

	if (!$admin_is && !$region_operator_is) {
        // error(get_string('accesstemporarylock', 'block_mou_school'));
		notice(get_string('deletepupil','block_monitoring'), $CFG->wwwroot."/blocks/mou_school/class/classpupils.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid");
        
	}
 	$redirlink = "{$CFG->wwwroot}/blocks/mou_school/class/movingpupils.php?rid=0&amp;sid=0";

	if (isset($confirm)) {
		
		delete_records('monit_school_movepupil', 'id', $id);
		redirect($redirlink, get_string('deletecompleted','block_mou_school'), 20);
	}


	print_heading(get_string('delitinhistory','block_mou_school'));
	notice_yesno(get_string('delmovpupchek','block_mou_school', '', "<b>$fullname</b> ..."),
               "delhistory.php?id=$id&amp;sid=$sid&amp;rid=$rid&amp;gid=$gid&amp;yid=$yid&amp;uid=$uid&amp;confirm=1",
               $redirlink);

	print_footer();	
?>