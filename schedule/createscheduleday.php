<?php // $Id: createscheduleday.php,v 1.10 2012/03/01 12:09:56 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_school.php');
	require_once('../authbase.inc.php');    

	if (!has_capability('block/mou_school:editschedule', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

	// define("MAX_SYMBOLS_LISTBOX", 40);
	
    $gid = optional_param('gid', 0, PARAM_INT);   // Class id
    $pid = optional_param('pid', 0, PARAM_INT);   // Parallel number
    $termid = optional_param('termid', 0, PARAM_INT); // Term id
    $nw = optional_param('nw', 0, PARAM_INT);   // Number of week in study year
	$wid = optional_param('wid', 0, PARAM_INT);   // Day number in week	 
	$sday = optional_param('sday');   // Day in "Y-m-d" format

	$GLDATESTART = array();
	$curryearfull = current_edu_year();
	$curyear = explode('/', $curryearfull);
	$datestartGLOB = $curyear[0].'-09-01';
	$dateendGLOB = $curyear[1].'-05-31';	
    $allweeksinyear = make_all_weeks_in_year($datestartGLOB, $dateendGLOB);
	
	$arr_date = explode ('-', $datestartGLOB);
	$temdatestart = make_timestamp ($arr_date[0],  $arr_date[1], $arr_date[2], 12);
	$arr_date = explode ('-', $dateendGLOB);
	$temdateend = make_timestamp ($arr_date[0],  $arr_date[1], $arr_date[2], 12);
		
	$strcurdate = date("Y-m-d");
	$rusformat = date("d.m.y");	
	$datestart = $GLDATESTART[$nw];
	$strdate = date("Y-m-d", $datestart);
//	echo $datestart;

    if ($recs = data_submitted())  {

		// print_r($recs); echo '<hr>';
		
		$redirlink = "createscheduleday.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;nw=$nw&amp;wid=$wid";
		
		$schedules = array();
		foreach($recs as $fieldname => $value)	{
		    $mask = substr($fieldname, 0, 2);
		    switch ($mask)  {
				case 'd_': 	$ids = explode('Z', $fieldname);
		            		$schedules[$ids[1]]->classdisciplineid = $value;
  				break;
				case 'r_': 	$ids = explode('Z', $fieldname);
		            		$schedules[$ids[1]]->roomid = $value;
  				break;
  			}
  		}
  		
		//print_r($schedules); exit();
		
		foreach ($schedules as $key => $schedule)  {
			$ids = explode('_', $key);
			if ($schedule->classdisciplineid == 0 && $ids[0] == 0)	continue;
			if ($ids[0] > 0)	{
				// update
				$id = $ids[0];
				if ($schedule->classdisciplineid == 0)  {
					delete_records_select('monit_school_class_schedule_'.$rid, "id = $id");
						//notify("Delete $currentshed->classdisciplineid : $currentshed->disciplineid", 'green');
				} else {
       				if (!$currentshed = get_record_select('monit_school_class_schedule_'.$rid, "id=$id", 'id, classdisciplineid, roomid, datestart, schedulebellsid, disciplineid'))	{
        					error ("monit_school_class_schedule_$rid with id=$id not found.");
        			}   
	          		if ($currentshed->classdisciplineid == $schedule->classdisciplineid && 
					  	$currentshed->roomid == $schedule->roomid) {
		          			continue;
      				}

					$strsql = "SELECT id, teacherid, disciplineid FROM {$CFG->prefix}monit_school_class_discipline
							   WHERE id={$schedule->classdisciplineid}";
					$classdiscipline = get_record_sql ($strsql);

					$updateshed->id = $id; 
					$updateshed->classdisciplineid = $schedule->classdisciplineid; 
					$updateshed->disciplineid = $classdiscipline->disciplineid;
					$updateshed->teacherid = $classdiscipline->teacherid;
					$updateshed->roomid = $schedule->roomid;

	       			if (!update_record('monit_school_class_schedule_'.$rid, $updateshed))	{
	       				print_r($updateshed);
						error(get_string('errorinupdatingschedule','block_mou_school'), $redirlink);
		  			} 
		  		}	
			} else {
				// insert
				$strsql = "SELECT id, teacherid, disciplineid FROM {$CFG->prefix}monit_school_class_discipline
						   WHERE id={$schedule->classdisciplineid}";
				$classdiscipline = get_record_sql ($strsql);

				$newsched->schoolid = $sid;
				$newsched->classid  = $gid;
				$newsched->classdisciplineid = $schedule->classdisciplineid; 
				$newsched->disciplineid = $classdiscipline->disciplineid;
				$newsched->lessonid  = 0; // !!!!
				$newsched->teacherid = $classdiscipline->teacherid;				
     			$newsched->roomid = $schedule->roomid;
     			$newsched->datestart = $sday;
   				$newsched->schedulebellsid = $ids[2];

	   				
				if (record_exists_select_mou('monit_school_class_schedule_'.$rid, 
										 "classdisciplineid = {$newsched->classdisciplineid} AND 
										  datestart = '{$newsched->datestart}' AND
                                          schedulebellsid = {$newsched->schedulebellsid}")) continue;			   				
       			if (!insert_record('monit_school_class_schedule_'.$rid, $newsched))	{
	       			print_r($newsched);
					error(get_string('errorininseringschedule','block_mou_school'), $redirlink);
				} 
			}
		}
		redirect($redirlink, get_string('succesavedata', 'block_mou_school'), 30);		
	}
  

    $currenttab = 'createschedule';
    include('tab_act.php');

    $currenttab = 'scheduleday';
    include('tab_create.php');

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_class("createscheduleday.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;nw=$nw&amp;wid=$wid&amp;gid=", $rid, $sid, $yid, $gid);
	listbox_all_weeks_year("createscheduleday.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;wid=$wid&amp;nw=", $allweeksinyear, $nw);
    listbox_weekday_with_date("createscheduleday.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;nw=$nw&amp;wid=", $nw, $wid);	
	
//	echo $strdate;
	if ($gid != 0 && $wid != 0)		{

		echo '</table>';
	    echo  '<form name="timelessons" method="post" action="createscheduleday.php">';
		echo  '<input type="hidden" name="rid" value="' . $rid . '">';
		echo  '<input type="hidden" name="sid" value="' . $sid . '">';
		echo  '<input type="hidden" name="yid" value="' . $yid . '">';
		echo  '<input type="hidden" name="gid" value="' . $gid . '">';
		echo  '<input type="hidden" name="nw" value="' .  $nw . '">';
		echo  '<input type="hidden" name="wid" value="' . $wid . '">';
		echo  '<input type="hidden" name="sday" value="' .$GLDAY[$wid] . '">';		

		if ($table = table_createscheduleday ($yid, $rid, $sid, $gid, $nw, $wid))	{
			print_color_table($table);
			// print_table	
			echo  '<div align="center">';
			echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '"><hr>';
	
		}
			
		echo  '</form>';
	}

	echo '</table>';
	
    print_footer();



function table_createscheduleday ($yid, $rid, $sid, $gid, $nw, $wid)
{
	global $CFG, $GLDAY, $GLDATESTART, $datestartGLOB, $dateendGLOB, $arr_date, $temdateend, $temdatestart;

	$table->head  = array (get_string('smena', 'block_mou_school') . '. ' .
						   get_string('lessonnum','block_mou_school'),
						   get_string('timelessons', 'block_mou_school'),
						   get_string('predmet','block_mou_school'),
						   get_string('teacher','block_mou_school'),
						   get_string('room','block_mou_school'));

	$table->wrap = array('nowrap', 'nowrap', 'nowrap', 'nowrap', 'nowrap'); 
	$table->align = array ('center', 'center', 'center');
    // $table->size = array ('10%', '30%', '10%');
	$table->columnwidth = array (10,  15, 35, 30, 15);
    // $table->datatype = array ('char', 'char');
    $table->class = 'moutable';
   	$table->width = '90%';
    $table->titles = array();
    $table->titles[] = get_string('createschedule', 'block_mou_school');
    $table->worksheetname = 'createscheduleday';
    $table->downloadfilename = 'day';
    
    $strholidays = get_string('holidays', 'block_mou_school');

	$disciplinemenu = array();
	$disciplinemenu[0] = '-';

    $strdaystart = $GLDAY[$wid]; // $GLDAY[$i] = date("Y-m-d" .....
		
	$strsql = "SELECT id, name FROM {$CFG->prefix}monit_school_class_discipline
               WHERE classid=$gid";
	if ($classdisciplines = get_records_sql($strsql)){
		foreach ($classdisciplines as $classdiscipline)  {
			$disciplinemenu[$classdiscipline->id] = $classdiscipline->name;	
		}
	}
	
    $roomsoptions = array();
    $roomsoptions[0] = '-';
	if ($rooms = get_records_select('monit_school_room', "schoolid=$sid", '', 'id, name'))	{
		foreach ($rooms as $room) 	{
			if (mb_strlen($room->name, 'UTF-8') > 15)	{
				$roomname = mb_substr($room->name, 0,  15, 'UTF-8') . ' ...'; 
			}  else {
	  			$roomname = $room->name;	
			}
			$roomsoptions[$room->id] = $roomname;
		}
	}		

	if (!$holidays = get_records_select('monit_school_holidays', "schoolid = $sid OR schoolid = 0", '', 'id, datestart, dateend, parallelnum'))	{
		notify(get_string('notfoundholiday', 'block_mou_school'));
	}

	$class = get_record_sql("SELECT id, parallelnum FROM {$CFG->prefix}monit_school_class WHERE id=$gid");

	$checkholiday = check_holiday($holidays, $class, $strdaystart);
   		
	$lessonnums = array();

	$strlessonnum = $strtimelesson = $strdisciplines = $strrooms = '';


	if (!$checkholiday)	{
		if ($timelessons = get_records_select('monit_school_schedule_bells', "schoolid = $sid AND weekdaynum = $wid", 'smena, lessonnum', 'id, smena, lessonnum, timestart, timeend'))     {
			
			$idtimelesson = array();
			$idschedulesday = array();
			if ($schedulesdaycheck = get_records_select('monit_school_class_schedule_'.$rid, "classid = $gid AND datestart = '$strdaystart'", '', 'id, schedulebellsid'))	{
				foreach ($schedulesdaycheck as $sdc)	{
					$idtimelesson[$sdc->schedulebellsid] = 0;
					$idschedulesday[$sdc->schedulebellsid] = $sdc->id;		
				}
			}
				
	    	foreach ($timelessons as $timelesson)      {
	    		$idtimelesson[$timelesson->id] = 1;
   	  			$strlessonnum = $timelesson->smena . '.' . $timelesson->lessonnum;
   	  			$strtimelesson =  substr($timelesson->timestart, 0, 5) . '-' . substr($timelesson->timeend, 0, 5);
		          
		        $strdisciplines = $strrooms = $strteacher = '';  
		        $i = 0;
                $strselect = "classid = $gid AND datestart = '$strdaystart' AND schedulebellsid = {$timelesson->id}";
                // echo   $strselect . '<br>';
		        if ($schedules = get_records_select('monit_school_class_schedule_'.$rid, $strselect, '', 'id, classdisciplineid, teacherid, roomid'))	{
		        	foreach ($schedules as $schedule)	{
		        		$i++;
		          		$ids = $schedule->id; 
						$strdisciplines .= choose_from_menu ($disciplinemenu, "d_Z{$ids}_{$i}_{$timelesson->id}", $schedule->classdisciplineid, '0', "", "", true) . '<br>';
					  	$strrooms 		.= choose_from_menu ($roomsoptions,   "r_Z{$ids}_{$i}_{$timelesson->id}", $schedule->roomid, '0', "", "", true) . '<br>';
					  	// $teachermenu  = get_teachermenu($sid, $schedule->disciplineid);
					  	// $strteacher  .= choose_from_menu ($teachermenu, "t_{$schedule->id}", $schedule->teacherid, "", "", "", true);
					  	if ($schedule->teacherid > 0) {
		  		    	    $user = get_record_sql("SELECT id, lastname, firstname FROM {$CFG->prefix}user
      							  					WHERE id={$schedule->teacherid}");
       						$strteacher .= fullname($user). '<br>';
					  	}
		        	} 
				} 

				while ($i < 4)	{
					$i++;
					$strdisciplines .= choose_from_menu ($disciplinemenu, "d_Z0_{$i}_{$timelesson->id}", 0, '0', "", "", true). '<br>';
				  	$strrooms 		.= choose_from_menu ($roomsoptions, "r_Z0_{$i}_{$timelesson->id}", 0, '0', "", "", true). '<br>';
				  	$strteacher 	.= '-' . '<br>';
				}  	
			
				$table->data[] = array($strlessonnum, $strtimelesson, $strdisciplines, $strteacher, $strrooms);
			}
		}	
	}	else	{
	    $strsql = "schoolid = 0 AND datestart = '$GLDAY[$wid]'";
		if ($prazdnik = get_record_select('monit_school_holidays', $strsql, 'id'))	{
		      $strholidays = get_string('prazdnik', 'block_mou_school'); 
		}    

		$strdisciplines = '<font color=gray>' . $strholidays . '</font>';
		$table->data[] = array($strdisciplines, $strdisciplines, $strdisciplines, $strdisciplines, $strdisciplines);		
	}
   
    if (isset($idtimelesson))	{
    	foreach ($idtimelesson as $key => $idts)	{
    		if ($idts == 0)	{
    			notify ('Unknown time lesson id '. $key . '. Deleted schedule id '. $idschedulesday[$key]. '.');
    			delete_records('monit_school_class_schedule_'.$rid, 'id', $idschedulesday[$key]);	
    		} 
    	}	
	}	
    return $table;
}


function get_teachermenu($sid, $did)
{	
	global $CFG;
	
	$teachermenu = array();
 	$teachers = get_records_sql("SELECT id, schoolid, teacherid  FROM {$CFG->prefix}monit_school_teacher
								 WHERE schoolid=$sid AND disciplineid=$did");
	if ($teachers)  {
  	  $teachermenu[0] = get_string('selectateacher', 'block_mou_school') . ' ........................................';
      foreach ($teachers as $teach)  {
    	    $user=get_record_sql("SELECT id, lastname, firstname FROM {$CFG->prefix}user
      							  WHERE id={$teach->teacherid}");
       		$teachermenu[$teach->teacherid] = fullname($user);
      }
   }
   
   return $teachermenu;
}      

?>