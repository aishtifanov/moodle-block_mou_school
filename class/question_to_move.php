<?PHP // $Id: question_to_move.php,v 1.5 2010/08/23 08:48:01 Shtifanov Exp $

    require_once("../../../config.php");
	require_once($CFG->libdir.'/adminlib.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');

    $rid = required_param('rid', PARAM_INT);                 // Rayon id
    $sid = required_param('sid', PARAM_INT);                 // School id
    $yid = required_param('yid', PARAM_INT);          // Year id
    $gid = required_param('gid', PARAM_INT);          // Group id
    $move  = required_param('uid', PARAM_INT);
    $uid = required_param('uid', PARAM_INT);
	$confirm = optional_param('confirm');

	require_login();

	$context = get_context_instance(CONTEXT_SCHOOL, $sid);
	if (!has_capability('block/mou_school:editclasslist', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

    $pupil = get_record('monit_school_pupil_card', 'userid', $move, 'yearid', $yid);
    $user = get_record_sql("SELECT id, lastname, firstname FROM {$CFG->prefix}user WHERE id=$move");
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

/*
	$context = get_context_instance(CONTEXT_REGION, 1);
	if (!has_capability('block/mou_school:editclasslist', $context))	{
        error(get_string('accesstemporarylock', 'block_mou_school'), '../index.php');
	}
*/

 	if ($move and confirm_sesskey()) {              // Delete a selected user, after confirmation

		$redirlink = "{$CFG->wwwroot}/blocks/mou_school/class/movepupil.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid";
		
        if (!$user = get_record('user', 'id', $move)) {
            error("No such user!", '', true);
        }

        $primaryadmin = get_admin();
        if ($user->id == $primaryadmin->id) {
            error("You are not allowed to move the primary admin user!", '', true);
        }

        if ($confirm != md5($move)) {
            $fullname = fullname($user, true);
            print_heading(get_string('movepupil', 'block_mou_school'));
            $optionsyes = array('rid'=>$rid, 'sid'=>$sid, 'yid'=>$yid, 'gid'=>$gid, 'uid'=>$move,
            					'confirm'=>md5($move), 'sesskey'=>sesskey());
	        notice_yesno(get_string('movecheckfull', 'block_mou_school', "'$fullname'"), "regionmovepupil.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;gid=$gid&amp;uid=$uid", $redirlink, $optionsyes, $optionsyes, 'post', 'get');

        }
    }

	print_footer();
?>
