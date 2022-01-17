<?PHP // $Id: delcurriculum.php,v 1.9 2012/10/02 05:51:43 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_school.php');

	$part = required_param('part', PARAM_ALPHA);    // p, com, dis, dd, cur
    $rid  = required_param('rid', PARAM_INT);       // Rayon id
	$sid  = required_param('sid', PARAM_INT);       // School id
	$id   = required_param('id', PARAM_INT);		// id profile | component | domain | ...
	$confirm = optional_param('confirm');

	$context = get_context_instance(CONTEXT_SCHOOL, $sid);
	if (!has_capability('block/mou_school:editdiscipline', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

	$yid = get_current_edu_year_id();
	$indexlink = $CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid";

	$breadcrumbs = "<a href=\"$indexlink\">".get_string('title','block_mou_school').'</a>';

    switch ($part)	{
    	case 'p':
				    $strtitle = get_string('profile','block_mou_school');
				    $redirlink = "{$CFG->wwwroot}/blocks/mou_school/curriculum/profiles.php?rid=$rid&amp;yid=$yid&amp;sid=$sid";
					$breadcrumbs .= "-> <a href=\"{$redirlink}\">$strtitle</a>";
					$strtitle = get_string('deletingprofile', 'block_mou_school');
					$breadcrumbs .= " -> $strtitle";
					$table = 'monit_school_profiles_curriculum';
    	break;
    	case 'com':
				    $strtitle = get_string('components','block_mou_school');
				    $redirlink = "{$CFG->wwwroot}/blocks/mou_school/curriculum/components.php?rid=$rid&amp;yid=$yid&amp;sid=$sid";
					$breadcrumbs .= "-> <a href=\"{$redirlink}\">$strtitle</a>";
					$strtitle = get_string('deletingcomponent', 'block_mou_school');
					$breadcrumbs .= "-> $strtitle";
					$table = 'monit_school_component';
    	break;
    	case 'dd':
				    $strtitle = get_string('educationareas','block_mou_school');
				    $redirlink = "{$CFG->wwwroot}/blocks/mou_school/curriculum/educationareas.php?rid=$rid&amp;yid=$yid&amp;sid=$sid";
					$breadcrumbs .= "-> <a href=\"{$redirlink}\">$strtitle</a>";
					$strtitle = get_string('deletingeducationarea', 'block_mou_school');
					$breadcrumbs .= "-> $strtitle";
					$table = 'monit_school_discipline_domain';
    	break;
    	case 'dis':
			    	$strtitle = get_string('discipline','block_mou_school');
				    $redirlink = "{$CFG->wwwroot}/blocks/mou_school/curriculum/discipline.php?rid=$rid&amp;yid=$yid&amp;sid=$sid";
					$breadcrumbs .= "-> <a href=\"{$redirlink}\">$strtitle</a>";
					$strtitle = get_string('deletingdiscipline', 'block_mou_school');
					$breadcrumbs .= "-> $strtitle";
					$table = 'monit_school_discipline';
    	break;
    	case 'dg':
			    	$strtitle = get_string('disciplinegroups','block_mou_school');
				    $redirlink = "{$CFG->wwwroot}/blocks/mou_school/curriculum/disciplinegroups.php?rid=$rid&amp;yid=$yid&amp;sid=$sid";
					$breadcrumbs .= "-> <a href=\"{$redirlink}\">$strtitle</a>";
					$strtitle = get_string('deletingdisciplinegroup', 'block_mou_school');
					$breadcrumbs .= "-> $strtitle";
					$table = 'monit_school_discipline_group';
    	break;
    	case 'cur':
    	break;
    }

    print_header_mou("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

	if (!$record = get_record($table, 'id', $id)) {
		error(get_string('errorcurriculum', 'block_mou_school', $id), $indexlink);
	}

    $linktable = '';
	if (isset($confirm)) {
		$check = false;
	    switch ($part)	{
	    	case 'p':   if (record_exists('monit_school_curriculum', 'profileid', $id, 'schoolid', $sid, 'yearid', $yid))	 {
	    	                $linktable = 'monit_school_curriculum';
		            		$check = true;
		            	}
	    	break;
	    	case 'com':
						if (record_exists('monit_school_curriculum', 'componentid', $id, 'schoolid', $sid, 'yearid', $yid))	{
						    $linktable = 'monit_school_curriculum';
		            		$check = true;
		            	}
	    	break;
	    	case 'dd':
						if (record_exists('monit_school_discipline', 'disciplinedomainid', $id, 'schoolid', $sid))	{
						    $linktable = 'monit_school_discipline';
		            		$check = true;
		            	}
	    	break;
	    	case 'dis':
						if (record_exists_mou('monit_school_curriculum', 'disciplineid', $id, 'yearid', $yid))	{
						    $linktable = 'monit_school_curriculum';
		            		$check = true;
		            	}  
						
						if (record_exists_mou('monit_school_class_discipline', 'disciplineid', $id))	{
						    $linktable = 'monit_school_class_discipline';
		            		$check = true;
		            	}  

						if (record_exists_mou('monit_school_class_schedule_'.$rid, 'disciplineid', $id))	{
						    $linktable = 'monit_school_class_schedule_'.$rid;
		            		$check = true;
		            	}  
						
						if (!$check) {
							delete_records('monit_school_teacher', 'disciplineid', $id);
							delete_records('monit_school_subgroup', 'disciplineid', $id);
		            	}
	    	break;
	    	case 'dg':
	    	break;
	    	case 'cur':
	    	break;
	    }

		if (!$check)  {
			delete_records($table, 'id', $id);
			// add_to_log(1, 'school', 'Curriculum deleted', 'delcurriculum.php', $USER->lastname.' '.$USER->firstname);
		}
		else 	{
			error(get_string('errorcurriculums2','block_mou_school', $id . ' (' . $table . ')[' . $linktable . ']'), $redirlink);
		}
		redirect($redirlink, get_string('deletecompleted','block_mou_school'), 20);
	}


	print_heading($strtitle .': ' .$record->name);
	notice_yesno(get_string('deletecheckfull', '', "<b>{$record->name}</b> ..."),
               "delcurriculum.php?part=$part&amp;id=$id&amp;sid=$sid&amp;rid=$rid&amp;confirm=1",
               $redirlink);

	print_footer();
?>
