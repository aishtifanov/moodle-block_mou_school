<?PHP // $Id: clearplan.php,v 1.2 2012/02/13 10:32:24 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');

	$level 	= optional_param('level', 'plan');   // plan, unit, lesson
    $rid  	= required_param('rid', PARAM_INT);       // Rayon id
	$sid  	= required_param('sid', PARAM_INT);       // School id
	$yid 	= required_param('yid', PARAM_INT);          // Year id	
	$id   	= required_param('id', PARAM_INT);		// id plan | unit | lesson | ...
    $did 	= optional_param('did', 0, PARAM_INT);   // Discipline id
    $pid 	= optional_param('pid', 0, PARAM_INT);   // Parallel number
    $planid = optional_param('planid', 0, PARAM_INT);   // Plan id
    $unitid = optional_param('unitid', 0, PARAM_INT);   // Unit id
	$confirm = optional_param('confirm');

	$context = get_context_instance(CONTEXT_SCHOOL, $sid);
	if (!has_capability('block/mou_school:editlessonsplan', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

	$indexlink = $CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid";
	/*
    switch ($level)	{
    	case 'plan': $planid = $id;
		break;
    	case 'unit': $unitid = $id;
    	break;
    }
	*/	

	$breadcrumbs = "<a href=\"$indexlink\">".get_string('title','block_mou_school').'</a>';
    $strtitle = get_string($level.'plans', 'block_mou_school');
    $redirlink = "{$CFG->wwwroot}/blocks/mou_school/plans/{$level}plans.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;pid=$pid&amp;did=$did&amp;planid=$planid&amp;unitid=$unitid";
	$breadcrumbs .= "-> <a href=\"{$redirlink}\">$strtitle</a>";
	$strtitle = get_string('deleting'.$level, 'block_mou_school');
	$breadcrumbs .= " -> $strtitle";
	$table = 'monit_school_discipline_'.$level;
    print_header_mou("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

	if (!$record = get_record($table, 'id', $id)) {
		error(get_string('errorcurriculum', 'block_mou_school', $id), $indexlink);
	}

	if (isset($confirm)) {
		$check = false;
	    switch ($level)	{
	    	case 'plan': 
						$units =  get_records_sql("SELECT id
							   FROM {$CFG->prefix}monit_school_discipline_unit
			     			   WHERE planid = $planid
			     			   ORDER BY number");

    					if ($units)  {
    						foreach ($units as $unit)	{
   								delete_records_select('monit_school_discipline_lesson_'.$rid, "schoolid = $sid and unitid = {$unit->id}");
   							}
						}	   	

						delete_records_select('monit_school_discipline_unit', "planid = $id");
	    	break;
	    	case 'unit': $check = true;
	    	break;
	    	case 'lesson': $check = true;
	    	break;
	    }

		if (!$check)  {
			delete_records($table, 'id', $id);
			// add_to_log(1, 'school', 'Curriculum deleted', 'delcurriculum.php', $USER->lastname.' '.$USER->firstname);
		}
		else 	{
			error(get_string('errorcurriculums2','block_mou_school', $id . ' (' . $table . ')'), $redirlink);
		}
		redirect($redirlink, get_string('deletecompleted', 'block_mou_school'), 0);
	}


	print_heading($strtitle .': ' .$record->name);
	notice_yesno(get_string('deletecheckfull', '', "<b>{$record->name}</b> ..."),
               "clearplan.php?level=$level&amp;id=$id&amp;sid=$sid&amp;rid=$rid&amp;yid=$yid&amp;pid=$pid&amp;did=$did&amp;confirm=1&amp;planid=$planid&amp;unitid=$unitid",
               $redirlink);

	print_footer();
?>
