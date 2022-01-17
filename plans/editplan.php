<?PHP // $Id: editplan.php,v 1.7 2012/02/13 10:32:24 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_att/lib_att.php');
    require_once('../lib_school.php');


    $mode 	= required_param('mode');    // new, add, edit, update
    $level 	= required_param('level');   // plan, unit, lesson
    $yid 	= required_param('yid', PARAM_INT); // Year id
    $rid 	= required_param('rid', PARAM_INT);      // Rayon id
    $sid 	= required_param('sid', PARAM_INT);      // School id
    $did 	= required_param('did', PARAM_INT);   // Discipline id
    $pid 	= optional_param('pid', 0, PARAM_INT);   // Parallel number
    $planid = optional_param('planid', 0, PARAM_INT);   // Plan id
    $unitid = optional_param('unitid', 0, PARAM_INT);   // Unit id
    $lid 	= optional_param('lid', 0, PARAM_INT);   // Lesson id

	$context = get_context_instance(CONTEXT_SCHOOL, $sid);
	$edit_capability = has_capability('block/mou_school:editlessonsplan', $context);
	
	$edit_capability_discipline = has_capability_editlessonsplan($sid, $did);

	if (!$edit_capability && !$edit_capability_discipline)	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

    if ($mode === "new" || $mode === "add" ) {
    	$strtitle =  get_string('add'.$level, 'block_mou_school');
    } else {
    	$strtitle = get_string('edit'.$level, 'block_mou_school');
    }

	$strlessonplan = get_string($level.'plans', 'block_mou_school');

	$breadcrumbs = '<a href="'.$CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('title','block_mou_school').'</a>';
	$breadcrumbs .= "-> <a href=\"{$level}plans.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;did=$did&amp;pid=$pid&amp;planid=$planid&amp;unitid=$unitid\">$strlessonplan</a>";
	$breadcrumbs .= "-> $strtitle";
    print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

    /*
    print_heading('Страница в стадии разарботки.', 'center', 3);
	print_footer();
    exit();
    */

	$rec->yearid = $yid;
	$rec->schoolid = $sid;
	$rec->disciplineid = $did;
	$rec->parallelnum = '';
	$rec->name = '';
	$rec->number = '';
	$rec->textbooksids = '';
	$rec->description = '';
	$redirlink = "{$level}plans.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;did=$did&amp;pid=$pid&amp;planid=$planid&amp;unitid=$unitid";

	switch ($mode)   {
		case 'new': $rec->hours = 1;
		break;

		case 'add':
					switch ($level)	{
						case 'plan':
									$rec->name = required_param('name');
									$rec->parallelnum = $pid;
									$rec->description = required_param('description');

									if (find_form_plan_errors($rec, $err) == 0) {
										if (insert_record('monit_school_discipline_plan', $rec))	{
											 // add_to_log(1, 'dean', 'one academygroup added', $redirlink, $USER->lastname.' '.$USER->firstname);
											// notice(get_string('addedplan','block_mou_school'), $redirlink);
											 redirect($redirlink, get_string('addedplan','block_mou_school'), 0);
										} else	{
											error(get_string('errorinaddingplan','block_mou_scholl'), $redirlink);
										}
									}
									else $mode = "new";
						break;
						case 'unit':
									$rec->name = required_param('name');
									$rec->number = required_param('number', PARAM_INT);
									$rec->planid = $planid;
									$rec->description = required_param('description');

									if (find_form_unit_errors($rec, $err) == 0) {
										if (insert_record('monit_school_discipline_unit', $rec))	{
											 // add_to_log(1, 'dean', 'one academygroup added', $redirlink, $USER->lastname.' '.$USER->firstname);
											 // notice(get_string('addedunit','block_mou_school'), $redirlink);
											 redirect( $redirlink, get_string('addedunit','block_mou_school'), 0);
										} else	{
											error(get_string('errorinaddingunit','block_mou_scholl'), $redirlink);
										}
									}
									else $mode = "new";

						break;
						case 'lesson':
									$rec->name = required_param('name');
									$rec->number = required_param('number', PARAM_INT);
									$rec->hours = required_param('hours', PARAM_INT);
									$rec->unitid = $unitid;
									$rec->description = required_param('description');

									if (find_form_lesson_errors($rec, $err) == 0) {
										if (insert_record('monit_school_discipline_lesson_'.$rid, $rec))	{
											 // add_to_log(1, 'dean', 'one academygroup added', $redirlink, $USER->lastname.' '.$USER->firstname);
											 // notice(get_string('addedlesson','block_mou_school'), $redirlink);
											 redirect($redirlink, get_string('addedlesson','block_mou_school'), 0);
										} else	{
											error(get_string('errorinaddinglesson','block_mou_scholl'), $redirlink);
										}
									}
									else $mode = "new";
						break;
					}
		break;

		case 'edit':
					switch ($level)	{
						case 'plan':
									if ($planid > 0) 	{
										$plan = get_record('monit_school_discipline_plan', 'id', $planid);
										$rec->id = $plan->id;
										$rec->name = $plan->name;
										$rec->description = $plan->description;
									}

						break;
						case 'unit':
									if ($unitid > 0) 	{
										$unit = get_record('monit_school_discipline_unit', 'id', $unitid);
										$rec->id = $unit->id;
										$rec->name = $unit->name;
										$rec->number = $unit->number;
										$rec->description = $unit->description;
									}
						break;
						case 'lesson':
									if ($lid > 0) 	{
										$lesson = get_record('monit_school_discipline_lesson_'.$rid, 'id', $lid);
										$rec->id = $lesson->id;
										$rec->name = $lesson->name;
										$rec->number = $lesson->number;
										$rec->hours = $lesson->hours;
										$rec->description = $lesson->description;
									}
						break;
					}
		break;

		case 'update':
					switch ($level)	{
						case 'plan':
									$rec = get_record('monit_school_discipline_plan', 'id', $planid);
									$rec->name = required_param('name');
									$rec->description = required_param('description');

									if (find_form_plan_errors($rec, $err) == 0) {

										if (update_record('monit_school_discipline_plan', $rec))	{
											 // add_to_log(1, 'dean', 'academygroup update', $redirlink, $USER->lastname.' '.$USER->firstname);
											// notice(get_string('updateplan','block_mou_school'), $redirlink);
											 redirect($redirlink, get_string('updateplan','block_mou_school'), 0);
										} else	{
											error(get_string('errorinupdatingplan','block_mou_school'), $redirlink);
										}
									}
						break;
						case 'unit':
									$rec = get_record('monit_school_discipline_unit', 'id', $unitid);
									$rec->name = required_param('name');
									$rec->number = required_param('number', PARAM_INT);
									$rec->description = required_param('description');

									if (find_form_unit_errors($rec, $err) == 0) {

										if (update_record('monit_school_discipline_unit', $rec))	{
											 // add_to_log(1, 'dean', 'academygroup update', $redirlink, $USER->lastname.' '.$USER->firstname);
											 // notice(get_string('updateunit','block_mou_school'), $redirlink);
											 redirect($redirlink, get_string('updateunit','block_mou_school'), 0);
										} else	{
											error(get_string('errorinupdatingunit','block_mou_school'), $redirlink);
										}
									}
						break;
						case 'lesson':
									$rec = get_record('monit_school_discipline_lesson_'.$rid, 'id', $lid);
									$rec->name = required_param('name');
									$rec->number = required_param('number', PARAM_INT);
									$rec->hours = required_param('hours', PARAM_INT);
									$rec->description = required_param('description');

									if (find_form_lesson_errors($rec, $err) == 0) {

										if (update_record('monit_school_discipline_lesson_'.$rid, $rec))	{
											 // add_to_log(1, 'dean', 'academygroup update', $redirlink, $USER->lastname.' '.$USER->firstname);
											 // notice(get_string('updatelesson','block_mou_school'), $redirlink);
											redirect($redirlink, get_string('updatelesson','block_mou_school'), 0);
										} else	{
											error(get_string('errorinupdatinglesson','block_mou_school'), $redirlink);
										}
									}
						break;
					}
		break;
	}

	if ($mode === 'new')  {
		$mode = 'add';
	} else {
		$mode = 'update';
	}

	$rayon = get_record('monit_rayon', 'id', $rid);

	$school = get_record('monit_school', 'id', $sid);

	$discipline =  get_record('monit_school_discipline', 'id', $did);

	print_heading($strtitle, "center", 3);

    print_simple_box_start("center", "70%");
	?>
	
	<form name="addform" method="post" action="editplan.php">
	<input type="hidden" name="mode" value="<?php p($mode)?>">
	<input type="hidden" name="level" value="<?php p($level)?>">
	<input type="hidden" name="rid" value="<?php p($rid)?>">
	<input type="hidden" name="sid" value="<?php p($sid)?>">
	<input type="hidden" name="yid" value="<?php p($yid)?>">
	<input type="hidden" name="did" value="<?php p($did)?>">
	<input type="hidden" name="pid" value="<?php p($pid)?>">
	<input type="hidden" name="planid" value="<?php p($planid)?>">
	<input type="hidden" name="unitid" value="<?php p($unitid)?>">
	<input type="hidden" name="lid" value="<?php p($lid)?>">
	<center>
	<table cellpadding="5">
	<tr valign="top">
	    <td align="right"><b><?php  print_string("rayon","block_monitoring") ?>:</b></td>
	    <td align="left"> <?php p($rayon->name) ?> </td>
	</tr>
	<tr valign="top">
	    <td align="right"><b><?php  print_string("school","block_monitoring") ?>:</b></td>
	    <td align="left"> <?php p($school->name) ?> </td>
	</tr>
	<tr valign="top">
	    <td align="right"><b><?php  print_string('parallelnum','block_mou_school') ?>:</b></td>
	    <td align="left"> <?php p($pid) ?> </td>
	</tr>
	<tr valign="top">
	    <td align="right"><b><?php  print_string('predmet','block_mou_school') ?>:</b></td>
	    <td align="left"> <?php p($discipline->name) ?> </td>
	</tr>
	<?php
	switch ($level)	{
		case 'plan':
					?>
					<tr valign="top">
					    <td align="right"><b><?php  print_string('planname', 'block_mou_school') ?>:</b></td>
					    <td align="left">
							<input type="text" id="name" name="name" size="100" value="<?php p($rec->name) ?>" />
							<?php if (isset($err["name"])) formerr($err["name"]); ?>
					    </td>
					</tr>
					
					<?php
		break;
		case 'unit': $plan = get_record('monit_school_discipline_plan', 'id', $planid);

					?>
					<tr valign="top">
					    <td align="right"><b><?php  print_string('lessonplan','block_mou_school') ?>:</b></td>
					    <td align="left"> <?php p($plan->name) ?> </td>
					</tr>
					
					<tr valign="top">
					    <td align="right"><b><?php  print_string('numberunit', 'block_mou_school') ?>:</b></td>
					    <td align="left">
							<input type="text" id="number" name="number" size="5" value="<?php p($rec->number) ?>" />
							<?php if (isset($err["number"])) formerr($err["number"]); ?>
					    </td>
					</tr>
					<tr valign="top">
					    <td align="right"><b><?php  print_string('unitplan', 'block_mou_school') ?>:</b></td>
					    <td align="left">
							<input type="text" id="name" name="name" size="100" value="<?php p($rec->name) ?>" />
							<?php if (isset($err["name"])) formerr($err["name"]); ?>
					    </td>
					</tr>

					<?php
		break;
		case 'lesson': $plan = get_record('monit_school_discipline_plan', 'id', $planid);
					   $unit = get_record('monit_school_discipline_unit', 'id', $unitid);
					?>
					<tr valign="top">
					    <td align="right"><b><?php  print_string('lessonplan','block_mou_school') ?>:</b></td>
					    <td align="left"> <?php p($plan->name) ?> </td>
					</tr>
					<tr valign="top">
					    <td align="right"><b><?php  print_string('unitplan','block_mou_school') ?>:</b></td>
					    <td align="left"> <?php p($unit->name) ?> </td>
					</tr>
					
					<tr valign="top">
					    <td align="right"><b><?php  print_string('numberlesson', 'block_mou_school') ?>:</b></td>
					    <td align="left">
							<input type="text" id="number" name="number" size="5" value="<?php p($rec->number) ?>" />
							<?php if (isset($err["number"])) formerr($err["number"]); ?>
					    </td>
					</tr>
					<tr valign="top">
					    <td align="right"><b><?php  print_string('lessonplan', 'block_mou_school') ?>:</b></td>
					    <td align="left">
							<input type="text" id="name" name="name" size="100" value="<?php p($rec->name) ?>" />
							<?php if (isset($err["name"])) formerr($err["name"]); ?>
					    </td>
					</tr>
					<tr valign="top">
					    <td align="right"><b><?php  print_string('hourslesson', 'block_mou_school') ?>:</b></td>
					    <td align="left">
							<input type="text" id="hours" name="hours" size="5" value="<?php p($rec->hours) ?>" />
							<?php if (isset($err["hours"])) formerr($err["hours"]); ?>
					    </td>
					</tr>
					<?php
		break;
	}
	?>
	<tr valign="top">
	    <td align="right"><b><?php  print_string("description") ?>:</b></td>
	    <td align="left">
			<input type="text" id="description" name="description" size="100" value="<?php p($rec->description) ?>" />
	    </td>
	</tr>
	</table>
	   <div align="center">
	 	 <input type="submit" name="addclass1" value="<?php print_string('savechanges')?>">
	  </div>
	 </center>
	</form>
	
	<?php
    print_simple_box_end();

	print_footer();


/// FUNCTIONS ////////////////////
function find_form_plan_errors(&$rec, &$err)
{
    if (empty($rec->name))	{
	    $err["name"] = get_string("missingname");
	}
    return count($err);
}

function find_form_unit_errors(&$rec, &$err)
{
    if (empty($rec->name))	{
	    $err["name"] = get_string("missingname");
	}
    if (empty($rec->number))	{
	    $err["number"] = get_string("missingname");
	}

    return count($err);
}


function find_form_lesson_errors(&$rec, &$err)
{
    if (empty($rec->name))	{
	    $err["name"] = get_string("missingname");
	}
    if (empty($rec->number))	{
	    $err["number"] = get_string("missingname");
	}
    if (empty($rec->hours))	{
	    $err["hours"] = get_string("missingname");
	}

    return count($err);
}

?>
