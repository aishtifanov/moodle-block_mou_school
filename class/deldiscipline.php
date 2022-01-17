<?PHP // $Id: deldiscipline.php,v 1.5 2012/02/13 10:32:24 shtifanov Exp $

    require_once("../../../config.php");
	require_once($CFG->libdir.'/adminlib.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');

    $rid = required_param('rid', PARAM_INT);                 // Rayon id
    $sid = required_param('sid', PARAM_INT);                 // School id
    $yid = required_param('yid', PARAM_INT);          // Year id
    $gid = required_param('gid', PARAM_INT);          // Group id
	$did = required_param('did', PARAM_INT);		//Discipline id    
	$cdid = required_param('cdid', PARAM_INT);		
    $delete = optional_param('delete', '', PARAM_ALPHANUM); // delete confirmation hash
		
	require_login();

	$context = get_context_instance(CONTEXT_SCHOOL, $sid);
	if (!has_capability('block/mou_school:editclasslist', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

/*
	if (!$admin_is && !$region_operator_is) {
        error(get_string('accesstemporarylock', 'block_mou_school'));
	}
*/
	$redirlink = "classdisciplines.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid";

	if (!$classdiscipline = get_record ('monit_school_class_discipline', 'id', $cdid))	{
		error("Class discipline ID was incorrect (can't find it)", $redirlink);	
	}


    $strtitleblock = get_string('title','block_mou_school');
    $strclasses = get_string('classdisciplines','block_mou_school');
	$strtitle = get_string('deletediscipline','block_mou_school');

	$navlinks   = array();	
    $navlinks[] = array('name' => $strtitleblock, 'link' => $CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid", 'type' => 'misc');
    // $navlinks[] = array('name' => $strclasses, 'link' => $CFG->wwwroot."/blocks/mou_school/class/classlist.php?rid=$rid&amp;sid=$sid&amp;yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strclasses, 'link' => $redirlink, 'type' => 'misc');
    $navlinks[] = array('name' => $strtitle, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);

	print_header("$SITE->shortname: $strtitle", $SITE->fullname, $navigation);	
	
	if (record_exists_select_mou("monit_school_class_schedule_$rid", "classdisciplineid=$cdid"))	{
 		error(get_string('errorpredmetinshedule', 'block_mou_school'), $redirlink);
	}
		
 	if (!$delete) {
 		
    	$strdeletecheck = get_string('deletecheckfulldisc', 'block_mou_school', "'$classdiscipline->name'");
        notice_yesno($strdeletecheck,
                     "deldiscipline.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;cdid=$cdid&amp;did=$did&amp;delete=".md5($gid)."&amp;sesskey=$USER->sesskey",
                     $redirlink);

        print_footer();
        exit;
    }
		

    if ($delete != md5($gid)) {
        error("The check variable was wrong - try again", $redirlink);
    }

    if (!confirm_sesskey()) {
        print_error('confirmsesskeybad', 'error');
    }

    // OK checks done, delete the clsss discipline now.

    // add_to_log(SITEID, "course", "delete", "view.php?id=$course->id", "$course->fullname (ID $course->id)");

    $strdeletingcourse = get_string('deletingdisc', 'block_mou_school', "'$classdiscipline->name'");

/*
    $navlinks[] = array('name' => $stradministration, 'link' => "../$CFG->admin/index.php", 'type' => 'misc');
    $navlinks[] = array('name' => $strcategories, 'link' => "index.php", 'type' => 'misc');
    $navlinks[] = array('name' => $category->name, 'link' => "category.php?id=$course->category", 'type' => 'misc');
    $navlinks[] = array('name' => $strdeletingcourse, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
*/
    print_header("$SITE->shortname: $strdeletingcourse", $SITE->fullname, $navigation);

    // print_heading($strdeletingcourse, 'center', 4);
	
	delete_records('monit_school_class_discipline', 'id', $cdid);
	
    print_heading( get_string("deleteddisc", 'block_mou_school', "'$classdiscipline->name'"), 'center', 4);

	redirect($redirlink, '', 1);
 		    
?>
