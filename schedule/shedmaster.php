<?php // $Id: shedmaster.php,v 1.4 2012/02/13 10:32:25 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_school.php');
	require_once('../authbase.inc.php');    

	if (!has_capability('block/mou_school:editschedule', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

    $gid = optional_param('gid', 0, PARAM_INT);   // Class id
    $pid = optional_param('pid', 0, PARAM_INT);   // Parallel number
    $termid = optional_param('termid', 0, PARAM_INT); // Term id
    $nw = optional_param('nw', 1, PARAM_INT);   // Number of week in study year 
	$COUNT_OF_DAY_IN_WEEK = optional_param('cdw', 6, PARAM_INT);
	$MAX_LESSON_IN_DAY = optional_param('mld', 5, PARAM_INT);

	$GLDATESTART = array();
	$curryearfull = current_edu_year();
	$curyear = explode('/', $curryearfull);
	$datestartGLOB = $curyear[0].'-09-01';
	$dateendGLOB = $curyear[1].'-05-31';	
    $allweeksinyear = make_all_weeks_in_year($datestartGLOB, $dateendGLOB);

    $currenttab = 'createschedule';
    include('tab_act.php');

    $currenttab = 'schedule';
    include('tab_create.php');
    

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_class("shedmaster.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;nw=$nw&amp;gid=", $rid, $sid, $yid, $gid);
	listbox_all_weeks_year("shedmaster.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;nw=", $allweeksinyear, $nw);

	echo  '<form name="timelessons" method="post" action="shedmaster2.php">';
		?>
			<tr valign="top">
			    <td align="left"><?php  print_string('maxdayinweek', 'block_mou_school') ?>:</td>
			    <td align="left">
					<input type="text" id="maxdayinweek" name="cdw" size="3" value="<?php echo $COUNT_OF_DAY_IN_WEEK ?>" />
			    </td>
			</tr>
			
			<tr valign="top">
			    <td align="left"><?php  print_string('maxlessinday', 'block_mou_school') ?>:</td>
			    <td align="left">
					<input type="text" id="maxlessinday" name="mld" size="3" value="<?php echo $MAX_LESSON_IN_DAY ?>" />
			    </td>
			</tr>	
		<?php
	
	if ($gid != 0)		{
		
	    echo '</table>';
		echo  '<input type="hidden" name="rid" value="' . $rid . '">';
		echo  '<input type="hidden" name="sid" value="' . $sid . '">';
		echo  '<input type="hidden" name="yid" value="' . $yid . '">';
		echo  '<input type="hidden" name="gid" value="' . $gid . '">';
		echo  '<input type="hidden" name="nw" value="' .  $nw . '">';
		//echo  '<input type="hidden" name="cdw" value="' .  $COUNT_OF_DAY_IN_WEEK . '">';
		//echo  '<input type="hidden" name="mld" value="' .  $MAX_LESSON_IN_DAY . '">';
   		echo  '<div align="center">';
		echo  '<input type="submit" name="next" value="'. get_string('next', 'block_mou_school') . '"></div>';	
		echo  '</form>';
	}
	echo '</table>';
	
    print_footer();


/*
function check_schedule($sched)
{
	 global $CFG, $rid;
	 
	// check empty room	
	if (!empty($sched->roomid)) {
		if ($check = get_record_select('monit_school_class_schedule_'.$rid, "schoolid=$sched->schoolid AND roomid=$sched->roomid AND datestart='$sched->datestart' AND  schedulebellsid=$sched->schedulebellsid"))	{
			$room = get_record('monit_school_room', 'id', $sched->roomid);
			$rusdate = convert_date($sched->datestart, 'en', 'ru');
			$timeles = get_record('monit_school_schedule_bells', 'id', $sched->schedulebellsid);
			return "Кабинет $room->name уже занят $rusdate на $timeles->lessonnum -ом уроке!"; 
		}
	}	
	
	// check empty teacher
	if (!empty($sched->teacherid)) {
		if ($check = get_record_select('monit_school_class_schedule_'.$rid, "schoolid=$sched->schoolid AND teacherid=$sched->teacherid AND datestart='$sched->datestart' AND  schedulebellsid=$sched->schedulebellsid"))	{
			$user = get_record_sql("SELECT id, firstname, lastname FROM {$CFG->prefix}user WHERE id=$sched->teacherid");
			$rusdate = convert_date($sched->datestart, 'en', 'ru');
			$timeles = get_record('monit_school_schedule_bells', 'id', $sched->schedulebellsid);
			return "Учитель $user->lastname $user->firstname уже занят(а) $rusdate на $timeles->lessonnum -ом уроке!"; 
		}
	}	

	// check empty class
	if ($check = get_record_select('monit_school_class_schedule_'.$rid, "schoolid=$sched->schoolid AND classid=$sched->classid AND datestart='$sched->datestart' AND  schedulebellsid=$sched->schedulebellsid"))	{
		$class = get_record_sql("SELECT id, name FROM {$CFG->prefix}monit_school_class WHERE id=$sched->classid");
		$rusdate = convert_date($sched->datestart, 'en', 'ru');
		$timeles = get_record('monit_school_schedule_bells', 'id', $sched->schedulebellsid);
		return "Класс $class->name уже занят $rusdate на $timeles->lessonnum -ом уроке!"; 
	}
	
	return '';
	
}
*/
?>


