<?PHP // $Id: delaction.php,v 1.3 2010/08/23 08:47:56 Shtifanov Exp $

    require_once("../../../config.php");
	require_once($CFG->libdir.'/adminlib.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');

    $rid = required_param('rid', PARAM_INT);                 // Rayon id
    $sid = required_param('sid', PARAM_INT);                 // School id
    $yid = required_param('yid', PARAM_INT);          // Year id
    $gid = required_param('gid', PARAM_INT);          // Group id
    $delete  = required_param('uid', PARAM_INT);
	$confirm = optional_param('confirm');
	$uid = required_param('uid', PARAM_INT);          // Group id
	$portid = required_param('portid', PARAM_INT);
	
	require_login();
		
	$context = get_context_instance(CONTEXT_SCHOOL, $sid);
	if (!has_capability('block/mou_school:editclasslist', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

    $pupil = get_record('monit_school_pupil_card', 'userid', $delete, 'yearid', $yid);
    $user = get_record_sql("SELECT id, lastname, firstname FROM {$CFG->prefix}user WHERE id=$delete");
    $fullname = fullname($user);

    $straction = get_string('deleteprofileaction','block_mou_school');
    $strclasses = get_string('classes','block_mou_school');
	$strclass = get_string('class','block_mou_school');
	$strpupils = get_string('pupils', 'block_mou_school');
	$strpupil = get_string('pupil', 'block_mou_school');

	$breadcrumbs = '<a href="'.$CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('title','block_mou_school').'</a>';
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/mou_school/class/classlist.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">$strclasses</a>";
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/mou_school/class/classpupils.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid\">$strpupils</a>";
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/mou_school/class/docoffice.php?mode=4&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid=$uid\">$strpupil</a>";
    $breadcrumbs .= "-> $straction";
	print_header("$SITE->shortname: $straction", $SITE->fullname, $breadcrumbs);

/*
	if (!$admin_is && !$region_operator_is) {
        error(get_string('accesstemporarylock', 'block_mou_school'));
	}
*/

 	if ($delete) {              // Delete a selected user, after confirmation

        // if (!has_capability('moodle/user:delete', $sitecontext)) {
            // error('You do not have the required permission to delete a user.');
        // }
		$redirlink = "{$CFG->wwwroot}/blocks/mou_school/class/docoffice.php?mode=4&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid=$uid";
		
        if (!$user = get_record('user', 'id', $delete)) {
            error("No such user!", '', true);
        }

        $primaryadmin = get_admin();
        if ($user->id == $primaryadmin->id) {
            error("You are not allowed to delete the primary admin user!", '', true);
        }

        if ($confirm != md5($delete)) {
            $fullname = fullname($user, true);
            print_heading(get_string('deleteprofileaction', 'block_mou_school'));
            $optionsyes = array('rid'=>$rid, 'sid'=>$sid, 'yid'=>$yid, 'gid'=>$gid, 'portid'=>$portid,'uid'=>$uid,
            					'confirm'=>md5($delete), 'mode'=>4,);
	        notice_yesno(get_string('deletecheckfullaction', 'block_mou_school', "'$fullname'"), 'delaction.php', $redirlink, $optionsyes, $optionsyes, 'post', 'get');

        } else {

           		delete_records('monit_school_portfolio', 'id', $portid);
		   		redirect($redirlink, get_string('actiondeleted', 'block_mou_school'), 2);

        }
    }

	print_footer();
?>
