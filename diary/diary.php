<?php // $Id: diary.php,v 1.12 2012/02/13 10:32:24 shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_school.php'); 
    require_once($CFG->dirroot.'/calendar/lib.php');
    require_once('../lib_school.php');
	require_once('authdiary.inc.php');
	
	$GLDATESTART = array();
	$curryearfull = current_edu_year();
	$curyear = explode('/', $curryearfull);
	$datestart = $curyear[0].'-09-01';
	$dateend = $curyear[1].'-05-31';
	$datestartGLOB = $curyear[0].'-09-01';
	$dateendGLOB = $curyear[1].'-05-31';	
    $allweeksinyear = make_all_weeks_in_year($datestart, $dateend);
 
    if($strminipupilcard != '')	{
    		echo $strminipupilcard;
	
		    $currenttab = 'marks';
		    include('tabs_dairy.php');

			echo '<br>';
		    echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
			listbox_all_weeks_year("diary.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid=$uid&amp;nw=", $allweeksinyear, $nw);
			echo '</table>';

 			$table = table_diary ($yid, $rid, $sid, $gid, $uid, $nw);
			print_color_table($table);
    }

	print_footer();

    
  
function table_diary ($yid, $rid, $sid, $gid, $uid, $nw)
{
	global $CFG, $GLDAY, $GLDATESTART,$datestartGLOB, $dateendGLOB, $schedulemenu, $sh, $allweeksinyear;

	$strlesson = get_string('lessonplan', 'block_mou_school');
	$strtask = get_string('hometask','block_mou_school');
	
	$table->head  = array (get_string('day','block_mou_school'), get_string('numberlesson','block_mou_school'), get_string('lesson','block_mou_school')
							, get_string('task','block_mou_school'), get_string('mark','block_mou_school').'/'.get_string('attendance','block_mou_school'));
	$table->align = array ('left', 'center', 'left', 'left', 'center');
    $table->size = array ('17%', '5%', '20%', '48%', '10%');
	$table->columnwidth = array (21, 7);

    $table->class = 'moutable';
   	$table->width = '80%';
	$table->titlesrows = array(30);
    $table->titles = array();
    $table->titles[] = get_string('str_schedule_by_days', 'block_mou_school') . ' ('. $allweeksinyear[$nw].')';
    $table->downloadfilename = 'week_'.$gid.'_'.$nw;
    $table->worksheetname = $table->downloadfilename;
	
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
            $strsql = "SELECT id, lessonid, schedulebellsid, datestart, disciplineid 
					   FROM {$CFG->prefix}monit_school_class_schedule_$rid
                       WHERE classid = $gid and datestart='$strdate'";
            // echo $strsql . '<br>';           
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
					// echo '<pre>'; print_r($markstuders); echo '</pre>';
					foreach ($markstuders as $ms) {
						$mark1ids[$ms->scheduleid] = $ms->mark;
						$mark2ids[$ms->scheduleid] = $ms->mark2;
					}
				}
			
				$matrix = array();
                $matrix2 = array();
				foreach ($schedules as $schedule) {
				    //	echo $schedule->classdisciplineid.'<br>';
                    //print_r($schedule); echo '<hr>';
                     if (!isset($matrix[$schedule->schedulebellsid]->disciplineid))  {
				        $matrix[$schedule->schedulebellsid]->disciplineid = $schedule->disciplineid;
    					 if (!empty($schedule->lessonid))  {
    					 	$matrix[$schedule->schedulebellsid]->lessonname = $lessontemas[$schedule->lessonid];
    					 }
    				     if(isset($assignments[$schedule->id])){
    				     	$matrix[$schedule->schedulebellsid]->assignname = $assignments[$schedule->id];
    				     }
    					$table_td = $table_td2 = $reason_td = '';
    
    					if (!empty($attendancesids[$schedule->id]))	{
    						$reason_td = $attendancesids[$schedule->id];
    					} 
    				
    					if (!empty($mark1ids[$schedule->id]))	{
    						$table_td  = $mark1ids[$schedule->id];
    					} 
    
    					if (!empty($mark2ids[$schedule->id]))	{
    						$table_td2  = $mark2ids[$schedule->id];
    					} 
    					
    					if($table_td2 != '' || $table_td2 != 0) {
    						$table_td .= '/'.$table_td2;
    					}  
    					
    					if ($reason_td != '')	{
    						if ($table_td == '')  $table_td = $reason_td;
    						else  $table_td .= '/'.$reason_td;
    					}
                        if (empty($matrix[$schedule->schedulebellsid]->mark))    {
    					   $matrix[$schedule->schedulebellsid]->mark = $table_td;
                        }   
                         
                     } else {
                        $matrix2[$schedule->schedulebellsid]->disciplineid = $schedule->disciplineid;
    					 if (!empty($schedule->lessonid))  {
    					 	$matrix2[$schedule->schedulebellsid]->lessonname = $lessontemas[$schedule->lessonid];
    					 }
    				     if(isset($assignments[$schedule->id])){
    				     	$matrix2[$schedule->schedulebellsid]->assignname = $assignments[$schedule->id];
    				     }
                         
    					$table_td = $table_td2 = $reason_td = '';
    
    					if (!empty($attendancesids[$schedule->id]))	{
    						$reason_td = $attendancesids[$schedule->id];
    					} 
    				
    					if (!empty($mark1ids[$schedule->id]))	{
    						$table_td  = $mark1ids[$schedule->id];
    					} 
    
    					if (!empty($mark2ids[$schedule->id]))	{
    						$table_td2  = $mark2ids[$schedule->id];
    					} 
    					
    					if($table_td2 != '' || $table_td2 != 0) {
    						$table_td .= '/'.$table_td2;
    					}  
    					
    					if ($reason_td != '')	{
    						if ($table_td == '')  $table_td = $reason_td;
    						else  $table_td .= '/'.$reason_td;
    					}
                        if (empty($matrix2[$schedule->schedulebellsid]->mark))   {
    					   $matrix2[$schedule->schedulebellsid]->mark = $table_td;
                        }   							
                        // echo $table_td . '!!<hr>';
                        // echo '<pre>'; print_r($matrix2); echo '</pre>'; 
                     }   

				}    
				
				                
                // echo '<pre>'; print_r($matrix2); echo '</pre>';                                                                      
				$flag = false;
				if ($datestart >= $temdatestart && $datestart <= $temdateend)	{
	           
				    foreach ($lessonnums as $schedulebellsid => $lesnum)  {
           		 		
           		 		$strweek = '';
           		 		if (!$flag) {
           		 			$strweek = "$weekmenu[$i]<br><sub>[$rusformat]</sub>";
           		 			$flag = true;
           		 		}		
	    	            
	    	            $disciplinename = '-';
						if (isset($matrix[$schedulebellsid]->disciplineid))     {
                             $z = $matrix[$schedulebellsid]->disciplineid;
	            	         $discipline = get_record_select('monit_school_discipline', "id=$z", 'id, name'); 
	                		 $disciplinename = $discipline->name;  
					    } else continue;

						if (isset($matrix2[$schedulebellsid]->disciplineid))     {
                             $z = $matrix2[$schedulebellsid]->disciplineid;
	            	         $discipline2 = get_record_select('monit_school_discipline', "id=$z", 'id, name');
                             $disciplinename = '* ' .$disciplinename; 
	                		 $disciplinename .= ';<br>* ' . $discipline2->name . '.';  
					    } 
	         			
	         			$strthemeandassign = $strlesson . ': ';
						if (isset($matrix[$schedulebellsid]->lessonname))	{
							$strthemeandassign .= $matrix[$schedulebellsid]->lessonname;
						} 	
						
						$strthemeandassign .= '<br>' . $strtask . ': ';	
						if (isset($matrix[$schedulebellsid]->assignname)){
							$strthemeandassign .= $matrix[$schedulebellsid]->assignname;
						} 
                        
                        
						if (isset($matrix2[$schedulebellsid]->lessonname))	{
						    $strthemeandassign .= '<br>' . $strlesson . ': ';
							$strthemeandassign .= $matrix2[$schedulebellsid]->lessonname;
						} 	
						
							
						if (isset($matrix2[$schedulebellsid]->assignname)){
						    $strthemeandassign .= '<br>' . $strtask . ': ';
							$strthemeandassign .= $matrix2[$schedulebellsid]->assignname;
						} 

						$strmark = '';
						if (!empty($matrix[$schedulebellsid]->mark)){
							$strmark = $matrix[$schedulebellsid]->mark;
                            if (isset($matrix2[$schedulebellsid]->disciplineid))     {
                                $strmark .= ' (' .  $discipline->name . ')';
                            }    
                                
						}		

						if (!empty($matrix2[$schedulebellsid]->mark)){
						   // $strmark = '* '. $strmark;
							$strmark .= $matrix2[$schedulebellsid]->mark . ' (' .  $discipline2->name . ')';
						}		
						
                        if ($strmark == '') $strmark = '-';
						$table->data[] = array($strweek, $lesnum, $disciplinename, $strthemeandassign, $strmark);   	
			        }
				
		        }	else	{
		        	$tabledata = array("<font color=gray>$weekmenu[$i]<br><sub>[$rusformat]</sub></font>", '', '', '', '');
	        		$table->data[] = $tabledata;
				}
	        } else {
	        	$tabledata = array("<font color=gray>$weekmenu[$i]<br><sub>[$rusformat]</sub></font>", '', '', '', '');	
	        	$table->data[] = $tabledata;		        	
	        }   
		}
		$datestart = $datestart + DAYSECS;	
	}
									
    return $table;
}

?>