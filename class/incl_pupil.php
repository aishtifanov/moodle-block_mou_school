<?php // $Id: incl_pupil.php,v 1.8 2012/02/10 08:52:54 shtifanov Exp $
/// This file to be included so we can assume config.php has already been included.

    require_once("../../../config.php");
    require_once("$CFG->libdir/gdlib.php");
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_school.php');    

    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = required_param('sid', PARAM_INT);       // School id
    $yid = required_param('yid', PARAM_INT);       // Year id
    $gid = required_param('gid', PARAM_INT);          // Group id
    $uid = optional_param('uid', 0, PARAM_INT);       // User id

    $breadcrumbs[0]->name = get_string('classes','block_mou_school');
    $breadcrumbs[0]->link = "{$CFG->wwwroot}/blocks/mou_school/class/classlist.php?rid=$rid&amp;sid=$sid&amp;yid=$yid";
	$breadcrumbs[1]->name =  get_string('pupils', 'block_mou_school');
	$breadcrumbs[1]->link = "{$CFG->wwwroot}/blocks/mou_school/class/classpupils.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid"; 
		    
	require_once('../authbase.inc.php');
	
	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
    listbox_class("pupil.php?mode=3&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=", $rid, $sid, $yid, $gid);
    listbox_pupils("pupil.php?mode=4&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid=", $rid, $sid, $yid, $gid, $uid);
	echo '</table>';

	if ($gid == 0 || $uid == 0 )  {
	    print_footer();
		exit;
	}


	// if ($admin_is)	{
		$profile->fields = array('pol', 'birthday');
		$profile->type 	 = array('bool', 'date');
	    $profile->numericfield = array();
	/*    
	} else {
		$profile->fields = array();
		$profile->type 	 = array();
	    $profile->numericfield = array();
	}
	*/

	$rayon = get_record('monit_rayon', 'id', $rid);

	$school = get_record('monit_school', 'id', $sid);

	$class = get_record('monit_school_class', 'id', $gid);

    $pupil = get_record('monit_school_pupil_card', 'userid', $uid, 'yearid', $yid);
    
    if (!$user1 = get_record('user', 'id', $uid) ) {
        error('No such pupil in this class!', '../index.php');
	}

   	$fullname = fullname($user1);

	$edit_capability = has_capability('block/mou_school:editclasslist', $context);

	$context_class = get_context_instance(CONTEXT_CLASS, $gid);
	$edit_capability_class = has_capability('block/mou_school:editclasslist', $context_class);

?>


