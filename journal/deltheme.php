<?PHP // $Id: deltheme.php,v 1.1 2012/05/23 08:13:04 shtifanov Exp $

    require_once("../../../config.php");
	require_once($CFG->libdir.'/adminlib.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');

    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = required_param('sid', PARAM_INT);       // School id
    $yid = required_param('yid', PARAM_INT);       // Year id
    $jid = required_param('jid',  PARAM_INT);   // Schedule id (jornal id)

	$cdid 	= optional_param('cdid', 0, PARAM_INT);	  // class_discipline (subgroup) id
    $termid	= optional_param('tid',  0, PARAM_INT);   // Semestr id
    $gid 	= optional_param('gid',  0, PARAM_INT);   // Class id
    $p 		= optional_param('p', 	 0, PARAM_INT);   // Parallel number
    $tyid	= optional_param('tyid',  0, PARAM_INT);   // Semestr id
    $themeid= optional_param('themeid',  0, PARAM_INT);   // Theme id
    $planid = optional_param('planid',  0, PARAM_INT);
    $nw = optional_param('nw', 0, PARAM_INT);   // Number of week in study year
	$confirm = optional_param('confirm');
	$id = required_param('id', PARAM_INT);
	
	require_login();
	
	$context = get_context_instance(CONTEXT_SCHOOL, $sid);
	$view_capability = has_capability('block/mou_school:viewjournalclass', $context);
	$edit_capability = has_capability('block/mou_school:editjournalclass', $context);

	$edit_capability_class = false;
	if ($gid != 0)  { 
		$context_class = get_context_instance(CONTEXT_CLASS, $gid);
		$edit_capability_class = has_capability('block/mou_school:editjournalclass', $context_class);
	}
	
	$edit_capability_discipline = false;
	if ($cdid != 0)  {
		$ctxdiscipline = get_context_instance(CONTEXT_DISCIPLINE, $cdid);
		$edit_capability_discipline = has_capability('block/mou_school:editjournalclass', $ctxdiscipline);
	}
	
	if (!$edit_capability && !$edit_capability_class && !$edit_capability_discipline)	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	
	
	$strjournal = get_string('journalclass','block_mou_school');
   	$strtitle = 'Удаление темы и задания урока';	

	$breadcrumbs = '<a href="'.$CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('title','block_mou_school').'</a>';
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/mou_school/journal/journalclass.php?rid=$rid&amp;yid=$yid&amp;cdid=$cdid&amp;gid=$gid&amp;tid=$termid&amp;sid=$sid\">$strjournal</a>";
	$breadcrumbs .= "-> $strtitle";
	print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);
    	
    $redirlink = "themes.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;cdid=$cdid&amp;planid=$planid&amp;nw=$nw";
    
    if ($confirm) {
   		delete_records('monit_school_assignments_'.$rid, 'id', $id);
        set_field('monit_school_class_schedule_'.$rid, 'lessonid', 0, 'id', $jid);
  		redirect($redirlink, 'Тема и задание урока удалены.', 2);
    } else {
        print_heading($strtitle);
        $optionsyes = array('rid'=>$rid, 'sid'=>$sid, 'yid'=>$yid, 'gid'=>$gid, 'cdid'=>$cdid,'planid'=>$planid,
        					'confirm'=>1, 'id'=>$id, 'nw' => $nw, 'jid' => $jid);
        notice_yesno('<b>Вы абсолютно уверены что хотите удалить тему и задание урока?</b>', 'deltheme.php', $redirlink, $optionsyes, $optionsyes, 'post', 'get');
    }
    
	print_footer();
?>
