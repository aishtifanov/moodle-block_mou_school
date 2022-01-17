<?PHP // $Id: delholiday.php,v 1.2 2010/08/23 08:48:06 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');

    $rid  = required_param('rid', PARAM_INT);       // Rayon id
	$sid  = required_param('sid', PARAM_INT);       // School id
	$yid = optional_param('yid', '0', PARAM_INT);       // Year id
	$id   = required_param('id', PARAM_INT);		// id profile | component | domain | ...
	$confirm = optional_param('confirm');

	$context = get_context_instance(CONTEXT_SCHOOL, $sid);
	if (!has_capability('block/mou_school:edittypestudyperiod', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

    if ($yid == 0)	{
    	$yid = get_current_edu_year_id();
    }

	$indexlink = $CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid";

	$breadcrumbs = "<a href=\"$indexlink\">".get_string('title','block_mou_school').'</a>';

    $strtitle = get_string('infholidays','block_mou_school');
    $redirlink = "{$CFG->wwwroot}/blocks/mou_school/periods/holidays.php?rid=$rid&amp;yid=$yid&amp;sid=$sid";
	$breadcrumbs .= "-> <a href=\"{$redirlink}\">$strtitle</a>";
	$strtitle = get_string('deletingholiday', 'block_mou_school');
	$breadcrumbs .= " -> $strtitle";

    print_header_mou("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

	$table = 'monit_school_holidays';
	if (!$record = get_record($table, 'id', $id)) {
		error(get_string('errorcurriculum', 'block_mou_school', $id), $indexlink);
	}

	if (isset($confirm)) {
		$check = false;
		if (!$check)  {
			delete_records($table, 'id', $id);
			// add_to_log(1, 'school', 'Curriculum deleted', 'delcurriculum.php', $USER->lastname.' '.$USER->firstname);
		}
		else 	{
			error(get_string('errorcurriculums2','block_mou_school', $id . ' (' . $table . ')'), $redirlink);
		}
		redirect($redirlink, get_string('deletecompleted','block_mou_school'), 20);
	}


	print_heading($strtitle .': ' .$record->name);
	notice_yesno(get_string('deletecheckfull', '', "<b>{$record->name}</b> ..."),
               "delholiday.php?id=$id&amp;sid=$sid&amp;rid=$rid&amp;confirm=1",
               $redirlink);

	print_footer();
?>
