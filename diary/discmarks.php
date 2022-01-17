<?php // $Id: discmarks.php,v 1.6 2012/02/13 10:32:24 shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_school.php'); 
    require_once($CFG->dirroot.'/calendar/lib.php');
    require_once('../lib_school.php');
	require_once('authdiary.inc.php');
    
    $termid	= optional_param('tid',  0, PARAM_INT);   // Semestr id
        
/*	
	$GLDATESTART = array();
	$curryearfull = current_edu_year();
	$curyear = explode('/', $curryearfull);
	$datestart = $curyear[0].'-09-01';
	$dateend = $curyear[1].'-05-31';
	$datestartGLOB = $curyear[0].'-09-01';
	$dateendGLOB = $curyear[1].'-05-31';	
    $allweeksinyear = make_all_weeks_in_year($datestart, $dateend);
*/ 
    if($strminipupilcard != '')	{
    		echo $strminipupilcard;
	
		    $currenttab = 'discmarks';
		    include('tabs_dairy.php');

			echo '<br>';
		    echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		    listbox_discipline_class("discmarks.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid=$uid&amp;cdid=", $sid, $yid, $gid, $cdid);
			listbox_terms("discmarks.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;cdid=$cdid&amp;gid=$gid&amp;uid=$uid&amp;tid=", $sid, $yid, $gid, $termid);            
			echo '</table>';
			
			if ($cdid > 0)	{
	 			$table = table_diary_discipline ($yid, $rid, $sid, $gid, $uid, $cdid, $termid);
				print_color_table($table);
			}	
    }

	print_footer();

    
  
function table_diary_discipline ($yid, $rid, $sid, $gid, $uid, $cdid, $termid)
{
	global $CFG; // , $GLDAY, $GLDATESTART,$datestartGLOB, $dateendGLOB, $schedulemenu, $sh, $allweeksinyear;


	$classdiscipline =  get_record_select('monit_school_class_discipline', "id = $cdid", 'id, name');
	 
	$strlesson = get_string('lessonplan', 'block_mou_school');
	$strtask = get_string('hometask','block_mou_school');
	
	$table->head  = array (get_string('day','block_mou_school'), get_string('task','block_mou_school'), 
							get_string('mark','block_mou_school').'/'.get_string('attendance','block_mou_school'));
	$table->align = array ('center', 'left', 'center');
    $table->size = array ('17%', '48%', '10%');
	$table->columnwidth = array (21, 40, 20);

    $table->class = 'moutable';
   	$table->width = '80%';
	$table->titlesrows = array(30);
    $table->titles = array();
    $table->titles[] = get_string('discmarks', 'block_mou_school') . ' ('. $classdiscipline->name .')';
    $table->downloadfilename = 'diary_predmet_'.$cdid;
    $table->worksheetname = $table->downloadfilename;
	


/*
    $weekmenu[1] = get_string('monday', 'calendar');
    $weekmenu[2] = get_string('tuesday', 'calendar');
    $weekmenu[3] = get_string('wednesday', 'calendar');
    $weekmenu[4] = get_string('thursday', 'calendar');
    $weekmenu[5] = get_string('friday', 'calendar');
    $weekmenu[6] = get_string('saturday', 'calendar');
    $weekmenu[7] = get_string('sunday', 'calendar');
 	
 	$arr_date = explode ('-', $datestartGLOB);
	$temdatestart = make_timestamp ($arr_date[0],  $arr_date[1], $arr_date[2], 12);
	$arr_date = explode ('-', $dateendGLOB);
	$temdateend = make_timestamp ($arr_date[0],  $arr_date[1], $arr_date[2], 12);
	
    
	$datestart = $GLDATESTART[$nw];
	$SCHDATE = array();
	
    for ($i=1; $i<=7; $i++)  { 
     	
		$dn = strtolower (date('l', $datestart));
    	$strdayname = get_string ($dn, 'calendar');
    	$strdate = date("d.m.y", $datestart);
        $SCHDATE[$i] = date("Y-m-d", $datestart);

    	$flag = true;    	
		$strdate = date("Y-m-d", $datestart);
		$rusformat = date("d.m.y", $datestart);
		$lessonnums = array();
		
	
		if ($timelessons = get_records_select('monit_school_schedule_bells', "schoolid = $sid AND weekdaynum = $i", 'lessonnum', 'id, lessonnum'))     {
	    	foreach ($timelessons as $timelesson)      {
	          $lessonnums[$timelesson->id] = $timelesson->lessonnum;
    	 	}
*/
		 	    	 	// , classdisciplineid, disciplineid
	$school_term = get_record_select('monit_school_term', "id=$termid" , 'id, datestart, dateend'); // приходит с URL
	$strsql = "SELECT id, lessonid, schedulebellsid, datestart 
    			 FROM {$CFG->prefix}monit_school_class_schedule_$rid
           		 WHERE classdisciplineid=$cdid AND datestart >= '$school_term->datestart' AND datestart <= '$school_term->dateend'
    			 ORDER BY datestart";
    // echo   $strsql;                  
	if ($schedules = get_records_sql($strsql))	{
                               		 	
				$schedulesids = array();
				$schedulesids_lessons = array();
				foreach ($schedules as $schedule) {
					$schedulesids[] =  $schedule->id;
					if (!empty($schedule->lessonid))  {
						$schedulesids_lessons[] = $schedule->lessonid;
					}	
				}	
				$schedulelist = implode(',', $schedulesids);
				$schedulelist_lesson = implode(',', $schedulesids_lessons);
		
				$lessontemas = array();
				if (!empty($schedulelist_lesson)) {
					$strsql = "SELECT id, name FROM {$CFG->prefix}monit_school_discipline_lesson_$rid
						       WHERE id IN ($schedulelist_lesson)";
				    if ($lessontema_recs = get_records_sql($strsql))	{
				    	foreach ($lessontema_recs as $lr1)	{
				    		$lessontemas[$lr1->id] = $lr1->name;
				    	}
				    }
				}    
				
				$assignments = array();
				$strsql = "SELECT id, scheduleid, name FROM {$CFG->prefix}monit_school_assignments_$rid
					       WHERE type_ass = 2 AND scheduleid IN ($schedulelist)";
			    if ($assignments_recs = get_records_sql($strsql))	{
			    	foreach ($assignments_recs as $ar1)	{
			    		$assignments[$ar1->scheduleid] = $ar1->name;
			    	}
			    }
					
				$attendancesids = array();
				$strsql = "SELECT id, scheduleid, reason FROM {$CFG->prefix}monit_school_attendance_$rid
					       WHERE userid = $uid AND scheduleid IN ($schedulelist)";
			    if ($attendances = get_records_sql($strsql))	{
			    	foreach ($attendances as $attendance)	{
			    		$attendancesids[$attendance->scheduleid] = $attendance->reason; 
			    	}
			    }
			    
			    $mark1ids = array();
			    $mark2ids = array();
				$strsql = "SELECT id, scheduleid, mark, mark2 FROM {$CFG->prefix}monit_school_marks_$rid
					       WHERE userid = $uid AND scheduleid IN ($schedulelist)";
				// echo $strsql . '<br>'; 	       
				if ($markstuders = get_records_sql($strsql))	{
					// print_r($markstuders); echo '<hr>';
					foreach ($markstuders as $ms) {
						$mark1ids[$ms->scheduleid] = $ms->mark;
						$mark2ids[$ms->scheduleid] = $ms->mark2;
					}
				}
			
				$matrix = array();
				$allmarks = $allattendance = $countmarks = $countschedule = 0; 
				foreach ($schedules as $schedule) {
					$countschedule++;
					
					$strthemeandassign = '';
					
      		 		 $strweek = convert_date($schedule->datestart, 'en', 'ru'); // "$weekmenu[$i]<br><sub>[$rusformat]</sub>";

					 //	echo $schedule->classdisciplineid.'<br>';
				     // $matrix[$schedule->schedulebellsid]->disciplineid = $schedule->disciplineid;

					 if (!empty($schedule->lessonid))  {
					 	$strthemeandassign = $strlesson . ': ' . $lessontemas[$schedule->lessonid];
					 }
				     
				     if ($strthemeandassign != '')  $strthemeandassign .= '<br>' . $strtask . ': ';
				     if(isset($assignments[$schedule->id])){
						$strthemeandassign .= $assignments[$schedule->id];
				     }

					$table_td = $table_td2 = $reason_td = '';

					if (!empty($attendancesids[$schedule->id]))	{
						$reason_td = $attendancesids[$schedule->id];
						$allattendance++;
					} 
				
					if (!empty($mark1ids[$schedule->id]))	{
						$table_td  = $mark1ids[$schedule->id];
						$allmarks  += $table_td;
						$countmarks++; 
					} 

					if (!empty($mark2ids[$schedule->id]))	{
						$table_td2  = $mark2ids[$schedule->id];
						$allmarks  += $table_td2;
						$countmarks++;
					} 
					
					if($table_td2 != '' || $table_td2 != 0) {
						$table_td .= '/'.$table_td2;
					}  
					
					if ($reason_td != '')	{
						if ($table_td == '')  $table_td = $reason_td;
						else  $table_td .= '/'.$reason_td;
					}
					
					$strmark = $table_td;							
				
					$table->data[] = array($strweek, $strthemeandassign, $strmark);		
			
		        }	
                if ($countmarks == 0)   {
                    $avgmark = 0.0;
                } else {
                    $avgmark = $allmarks/$countmarks;
                }
		        $stravg = number_format($avgmark, 2, ',', '');
				$table->data[] = array('', '<b>' . get_string('srmarkdisc', 'block_mou_school') . '</b>', '<b>'.$stravg.'<b>');
				$table->data[] = array($countschedule . ' ' . get_string('llessonaov', 'block_mou_school'), '<b>' . get_string('countpropusk', 'block_mou_school') . '</b>', '<b>'.$allattendance.'<b>');				
	}
									
    return $table;
}

?>