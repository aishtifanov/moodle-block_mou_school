<?PHP // $Id: leaveschool.php,v 1.4 2010/08/23 08:47:59 Shtifanov Exp $

/* ÓÑÒÀÐÅÂØÈÉ ÑÊÐÈÏÒ. ÇÀÌÅÍÅÍ question_to_move
    require_once("../../../config.php");
	require_once($CFG->libdir.'/adminlib.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');

    $rid     = required_param('rid', PARAM_INT);                 // Rayon id
    $sid     = required_param('sid', PARAM_INT);                 // School id
    $yid = required_param('yid', PARAM_INT);          // Group id
    $gid = required_param('gid', PARAM_INT);          // Group id
    $delete  = required_param('uid', PARAM_INT);
	$confirm = optional_param('confirm');

    require_once('../authall.inc.php');

    $strtitle = get_string('pupil','block_mou_school');
    $strclasses = get_string('classes','block_mou_school');
	$strclass = get_string('class','block_mou_school');
	$strpupils = get_string('pupils', 'block_mou_school');

	$breadcrumbs = '<a href="'.$CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('title','block_mou_school').'</a>';
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/mou_school/class/classlist.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">$strclasses</a>";
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/mou_school/class/classpupils.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid\">$strpupils</a>";
	$breadcrumbs .= "-> $strtitle";
    print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);


	if (!$admin_is && !$region_operator_is) {
        error(get_string('accesstemporarylock', 'block_mou_school'));
	}

    $pupil = get_record('monit_school_pupil_card', 'userid', $delete, 'yearid', $yid);
    $user = get_record_sql("SELECT id, lastname, firstname FROM {$CFG->prefix}user WHERE id=$delete");
    $fullname = fullname($user);


 	if ($delete and confirm_sesskey()) {              // Delete a selected user, after confirmation

        // if (!has_capability('moodle/user:delete', $sitecontext)) {
            // error('You do not have the required permission to delete a user.');
        // }
       	$redirlink = "{$CFG->wwwroot}/blocks/mou_school/class/classpupils.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid";
       	
        if (!$user = get_record('user', 'id', $delete)) {
            error("No such user!", '', true);
        }

        $primaryadmin = get_admin();
        if ($user->id == $primaryadmin->id) {
            error('You are not allowed to delete the primary admin user!', '', true);
        }

        if ($confirm != md5($delete)) {
            $fullname = fullname($user, true);
            print_heading(get_string('deleteprofilepupil', 'block_mou_school'));
            $optionsyes = array('rid'=>$rid, 'sid'=>$sid, 'yid'=>$yid, 'gid'=>$gid, 'uid'=>$delete,
            					'confirm'=>md5($delete), 'sesskey'=>sesskey());
	        notice_yesno(get_string('leavecheckfull', 'block_mou_school', "'$fullname'"), 'leaveschool.php', $CFG->wwwroot.'/blocks/mou_school/class/classpupils.php', $optionsyes, $optionsyes, 'post', 'get');

        } else if (data_submitted() and !$user->deleted) {
            //following code is also used in auth sync scripts
            $updateuser = new object();
            $updateuser->id           = $user->id;
            $updateuser->deleted      = 1;
            $updateuser->timemodified = time();
            if (update_record('user', $updateuser)) {
            // if (set_field('user', 'deleted', 1, 'id', $user->id))   {
           		set_field('monit_school_pupil_card', 'deleted', 1, 'userid', $user->id);
		   		redirect($redirlink, get_string('leavededactivity', 'block_mou_school', fullname($user, true)), 3);
            } else {
           		redirect($redirlink, get_string('deletednot', '', fullname($user, true)), 5);
               // notify(get_string('deletednot', '', fullname($user, true)));
            }
        }
    }

	print_footer();
*/	
?>
