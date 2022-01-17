<?php // $Id: createschedule.php,v 1.49 2012/10/03 09:29:39 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_school.php');
	require_once('../authbase.inc.php');    

	if (!has_capability('block/mou_school:editschedule', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

	define("MAX_SYMBOLS_LISTBOX_DISCIPLINE", 50);
	
    $gid = optional_param('gid', 0, PARAM_INT);   // Class id
    $pid = optional_param('pid', 0, PARAM_INT);   // Parallel number
    $termid = optional_param('termid', 0, PARAM_INT); // Term id
    $nw = optional_param('nw', 0, PARAM_INT);   // Number of week in study year 
    $ncpyw = optional_param('ncpyw', 0, PARAM_INT);   // Number of week to copy

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
	if ($nw == 0) $datestart = $GLDATESTART[1];		
	else 		  $datestart = $GLDATESTART[$nw];
	$strdate = date("Y-m-d", $datestart);
//	echo $datestart;
    if ($recs = data_submitted())  {

		if (isset($recs->setexample)){
			redirect("shedmaster.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;nw=$nw&amp;gid=$gid", '', 0);
		}

		$redirlink = "createschedule.php?sid=$sid&amp;yid=$yid&amp;rid=$rid";
		$schedules = array();
		foreach($recs as $fieldname => $value)	{
		    $mask = substr($fieldname, 0, 2);
		    switch ($mask)  {
				case 'd_': 	$ids = explode('Z', $fieldname);
		            		$schedules[$ids[1]]->disciplineid = $value;
  				break;
				case 'r_': 	$ids = explode('Z', $fieldname);
		            		$schedules[$ids[1]]->roomid = $value;
  				break;
				case 't_': 	$ids = explode('_', $fieldname);
		            		$datesstarts[$ids[1]] = $value;
  				break;

  			}
  			
  		}

		// print_r($schedules); exit();  
		
			foreach ($schedules as $key => $schedule)  {
				$id = 1;
				$ids = explode('_', $key);
				if ($schedule->disciplineid == 0 && $ids[0] == 0)	continue;
				if ($ids[0] > 0)	{
	
					$id = 	$ids[0];
       				if (!$currentshed = get_record_select('monit_school_class_schedule_'.$rid, "id=$id", 'id, classdisciplineid, datestart, schedulebellsid, disciplineid'))	{
        					error ("monit_school_class_schedule_$rid with id=$id not found.");
        			}   
				
					if ($schedule->disciplineid == 0)  {
						delete_records_select('monit_school_class_schedule_'.$rid, "classid = $gid AND datestart = '{$currentshed->datestart}' AND schedulebellsid = {$currentshed->schedulebellsid}");
						//notify("Delete $currentshed->classdisciplineid : $currentshed->disciplineid", 'green');
					} else {
					    // id, schoolid, classid, classdisciplineid, lessonid, teacherid, roomid, datestart, schedulebellsid, disciplineid   
						$oldschedules = get_records_select('monit_school_class_schedule_'.$rid, "classid = $gid AND datestart = '{$currentshed->datestart}' AND schedulebellsid = {$currentshed->schedulebellsid} ", 'id, roomid, disciplineid' );
		          		$oldschedule  = current($oldschedules);
		          		if ($oldschedule->disciplineid == $schedule->disciplineid && $oldschedule->roomid == $schedule->roomid) {
		          			continue;
		          		}
	        	
						$strsql = "SELECT id FROM {$CFG->prefix}monit_school_class_discipline
								   WHERE classid=$gid and disciplineid = {$schedule->disciplineid}";
						$classdisciplines = get_records_sql ($strsql);
	
						$cntcldis = count($classdisciplines);
						$cntshed = count($oldschedules);
						
						if ($cntcldis == 1 && $cntshed == 1)	{
							$classdiscipline = current($classdisciplines);
							$sched = get_record_select('monit_school_class_schedule_'.$rid, "id=$id", 'id, classdisciplineid, roomid, disciplineid');
							$sched->classdisciplineid = $classdiscipline->id; 
							$sched->disciplineid = $schedule->disciplineid;
							$sched->roomid = $schedule->roomid;
							// $msg = check_schedule($sched);
							$msg = '';
						
							if ($msg == '')	{
				       			if (!update_record('monit_school_class_schedule_'.$rid, $sched))	{
				       				print_r($sched);
									error(get_string('errorinupdatingschedule','block_mou_school'), $redirlink);
					  			} else {
					  				//notify("Update $sched->classdisciplineid : $sched->disciplineid", 'green');
					  			}
							} else {
								notify($msg);
							}	
						} else {
							delete_records_select('monit_school_class_schedule_'.$rid, "classid = $gid AND datestart = '{$currentshed->datestart}' AND schedulebellsid = {$currentshed->schedulebellsid}");
							//notify("Delete $currentshed->classdisciplineid : $currentshed->disciplineid", 'green');
							$id = 0;
						}
					}
					 
				} else {
					$id = 0;
				}	
				
				if ($id == 0)	{
					// insert
					$strsql = "SELECT id FROM {$CFG->prefix}monit_school_class_discipline
							   WHERE classid=$gid and disciplineid = {$schedule->disciplineid}";
					if ($classdisciplines = get_records_sql ($strsql))	{
						foreach ($classdisciplines as $classdiscipline) {
							$sched->schoolid = $sid;
	   						$sched->classid  = $gid;
	   						$sched->classdisciplineid = $classdiscipline->id;
	   						$sched->disciplineid = $schedule->disciplineid;
	   						$sched->lessonid  = 0; // !!!!
	   				
			   				if ($classdiscipline = get_record_sql("SELECT id, teacherid FROM {$CFG->prefix}monit_school_class_discipline 
							   									   WHERE id={$sched->classdisciplineid}"))	{
								$sched->teacherid = $classdiscipline->teacherid;
			   				} else {
			   					$sched->teacherid = 0;
			   				}
		        		
			     			$sched->roomid = $schedule->roomid;
			     			$sched->datestart = $datesstarts[$ids[1]];
			   				$sched->schedulebellsid = $ids[2];
			   				
							if (record_exists_select_mou('monit_school_class_schedule_'.$rid, 
												 "classdisciplineid = {$sched->classdisciplineid} AND 
												  datestart = '{$sched->datestart}' AND 
                                                  schedulebellsid = {$sched->schedulebellsid}")) continue;			   				
							$msg = ''; // check_schedule($sched);
							if ($msg == '')	{
				       			if (!insert_record('monit_school_class_schedule_'.$rid, $sched))	{
				       				print_r($sched);
									error(get_string('errorininseringschedule','block_mou_school'), $redirlink);
					  			} else {
					  				//notify("Insert $sched->classdisciplineid : $sched->disciplineid", 'green');
					  			}
							} else {
								notify($msg);
							}
						}
					}			
				}				
		}		

		if (isset($recs->nextweek))		{
			$nw1 = copy_shedule_for_next_week($rid, $sid, $gid, $nw);
			$strcopysched = get_string('shedulecopytoweek', 'block_mou_school');
			notify("$strcopysched {$allweeksinyear[$nw1]}.", 'green');
			redirect("createschedule.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;nw=$nw1", '', 0);
			// notice('',"createschedule.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;nw=$nw1");
		}

		if (isset($recs->prevweek))		{
			$nw1 = copy_shedule_for_prev_week($rid, $sid, $gid, $nw);
			$strcopysched = get_string('shedulecopytoweek', 'block_mou_school');
			notify("$strcopysched {$allweeksinyear[$nw1]}.", 'green');
			redirect("createschedule.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;nw=$nw1", '', 0);
			// notice('',"createschedule.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;nw=$nw1");
		}
	
		if (isset($recs->anyweek))		{
			if ($ncpyw <= 0 || $ncpyw >= 50)	{
				notice(get_string('errorcopyanyweek', 'block_mou_school'), "createschedule.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;nw=$nw");
				// $nw1 = copy_shedule_for_prev_week($rid, $sid, $gid, $nw);
				// $strcopysched = get_string('shedulecopytoweek', 'block_mou_school');
				// notify("$strcopysched {$allweeksinyear[$nw1]}.", 'green');
				// redirect("createschedule.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;nw=$nw1", '', 0);
				// notice('',"createschedule.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;nw=$nw1");
			} else {
				 $nw1 =  copy_shedule_for_any_week($rid, $sid, $gid, $nw, $ncpyw);
				 $strcopysched = get_string('shedulecopytoweek', 'block_mou_school');
				 notify("$strcopysched {$allweeksinyear[$nw1]}.", 'green');
				 redirect("createschedule.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;nw=$nw1", '', 0);
			}	
		}
	
		if (isset($recs->term)){
			$holidays = get_records_select('monit_school_holidays', "schoolid = $sid OR schoolid = 0", 'id, datestart, dateend, parallelnum');
			$class = get_record_sql("SELECT id, parallelnum FROM {$CFG->prefix}monit_school_class WHERE id=$gid");
			$nw1 = $nw;
			do {
				$datestartnw1 = $GLDATESTART[$nw1];
			    for ($i=1; $i<=7; $i++)  {
			    	$strdatestartnw1 = date("Y-m-d", $datestartnw1);
   					$checkholiday = check_holiday($holidays, $class, $strdatestartnw1);
   					if ($checkholiday) break;
   					$datestartnw1 = $datestartnw1 + DAYSECS;
   				}	
				copy_shedule_for_next_week($rid, $sid, $gid, $nw1);
				if (!$checkholiday) {
					$strcopysched = get_string('shedulecopytoweek', 'block_mou_school');
					notify("$strcopysched {$allweeksinyear[$nw1]}.", 'green'); 
				}	
				$nw1++;	
				if (!isset($GLDATESTART[$nw1]) || $nw1 > 40) break;
			} while(!$checkholiday && $nw1 <= 40);

			redirect("createschedule.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;nw=$nw1", '', 0);
			// notice('',"createschedule.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;nw=$nw1");
		}	
        
		if (isset($recs->clear))		{
			clear_shedule_for_this_week($rid, $sid, $gid, $nw);
            $nw1 = $nw + 1;
			$strcopysched = 'Расписание удалено. Неделя №'; // get_string('shedulclear', 'block_mou_school');
			notify("$strcopysched {$allweeksinyear[$nw]}.", 'green');
			redirect("createschedule.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;nw=$nw1", '', 10);
			// notice('',"createschedule.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;nw=$nw1");
		}
        
	}
	
    $currenttab = 'createschedule';
    include('tab_act.php');

    $currenttab = 'schedule';
    include('tab_create.php');

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_class("createschedule.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;nw=$nw&amp;gid=", $rid, $sid, $yid, $gid);
	listbox_all_weeks_year("createschedule.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;nw=", $allweeksinyear, $nw);
	
//	echo $strdate;
	if ($gid != 0)		{

		echo '</table>';
	    echo  '<form name="timelessons" method="post" action="createschedule.php">';
		echo  '<input type="hidden" name="rid" value="' . $rid . '">';
		echo  '<input type="hidden" name="sid" value="' . $sid . '">';
		echo  '<input type="hidden" name="yid" value="' . $yid . '">';
		echo  '<input type="hidden" name="gid" value="' . $gid . '">';
		echo  '<input type="hidden" name="nw" value="' .  $nw . '">';
		if ($table = table_createschedule ($rid, $yid, $gid, $sid, $nw, $allweeksinyear[$nw],  
				"createschedule.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;wid="))	{
			print_color_table($table);	
			echo  '<div align="center">';
			echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '"><hr>';
			echo  '<input type="submit" name="setexample" value="'. get_string('autocreateshedule', 'block_mou_school') . '"><hr>';

		//if ($strcurdate <= $strdate){
			echo  '<input type="submit" name="nextweek" value="'. get_string('copysheduletonextweek', 'block_mou_school') . '">';
			echo  '<input type="submit" name="term" value="'. get_string('copysheduletoterm', 'block_mou_school') . '"><hr>';
			$stemp = get_string('copysheduletoanyweek', 'block_mou_school') . ' ';
			echo $stemp; 
			echo  ' <input type="text" value="' . $ncpyw . '" size="3" maxlength="3" name="ncpyw" />';			
			echo  ' <input type="submit" name="anyweek" value="'. get_string('makecopyshedule', 'block_mou_school') . '"><hr>';
			// echo  '<input type="submit" name="prevweek" value="'. get_string('copysheduletoprevweek', 'block_mou_school') . '">';
			// echo  '<input type="submit" name="year" value="'. get_string('copysheduletoyear', 'block_mou_school') . '"></div>';
            // echo  '<input type="submit" name="clear" value="'. get_string('clearshedule', 'block_mou_school') . '"><hr></div>';					
            echo  '<input type="submit" name="clear" value="Очистить расписание на этой неделе"><hr></div>';
		// }
		}
			
		echo  '</form>';
	}

	echo '</table>';
	
    print_footer();



function table_createschedule ($rid, $yid, $gid, $sid, $nw, $strweek, $scriptname)
{
	global $CFG, $GLDATESTART, $datestartGLOB, $dateendGLOB, $arr_date, $temdateend, $temdatestart;

	$table->head  = array (
						   get_string('weekdaynum', 'block_mou_school'),
						   get_string('smena', 'block_mou_school') . '. ' . 
	 					   get_string('lessonnum','block_mou_school') . '. ' .
						   get_string('predmet','block_mou_school'),
						   get_string('room','block_mou_school'));

	$table->align = array ('left', 'left', 'left');
    $table->size = array ('15%', '50%', '35%');
	$table->columnwidth = array (10,  30, 30);
    // $table->datatype = array ('char', 'char');
    $table->class = 'moutable';
   	$table->width = '80%';
    // $table->size = array ('10%', '10%');
    $table->titles = array();
    $table->titles[] = get_string('createschedule', 'block_mou_school');
    $table->worksheetname = 'createschedule';
    
    $strholidays = get_string('holidays', 'block_mou_school');

	$disciplinemenu = array();
	$disciplinemenu[0] = '-';
	$cldisciplines =  get_records_sql ("SELECT DISTINCT disciplineid FROM {$CFG->prefix}monit_school_class_discipline
									  	WHERE classid = $gid ");
	if ($cldisciplines)	{
		foreach ($cldisciplines as $cld) 	{
			$discipline = get_record_select('monit_school_discipline', "id=$cld->disciplineid", 'id, name');
			if (mb_strlen($discipline->name, 'UTF-8') > MAX_SYMBOLS_LISTBOX_DISCIPLINE)	{
				$disciplinemenu[$discipline->id] = mb_substr($discipline->name, 0,  MAX_SYMBOLS_LISTBOX_DISCIPLINE, 'UTF-8') . ' ...'; 
			}  else {
				$disciplinemenu[$discipline->id] = $discipline->name;	
			}
		}
	}
	
    $roomsoptions = array();
    $roomsoptions[0] = '-';
	if ($rooms = get_records_select('monit_school_room', "schoolid=$sid", '', 'id, name'))	{
		foreach ($rooms as $room) 	{
			if (mb_strlen($room->name, 'UTF-8') > 25)	{
				$roomname = mb_substr($room->name, 0,  25, 'UTF-8') . ' ...'; 
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

	$datestart = $GLDATESTART[$nw];

	$SCHDATE = array();
	
    for ($i=1; $i<=7; $i++)  {
 
     	$dn = strtolower (date('l', $datestart));
    	$strdayname = get_string ($dn, 'calendar');
    	$strdate = date("d.m.y", $datestart);
        // $daysoptions[date("Y-m-d", $datestart)] = $strdayname . ': ' . $strdate;
        $SCHDATE[$i] = date("Y-m-d", $datestart);
   	
   		$checkholiday = check_holiday($holidays, $class, $SCHDATE[$i]);
   		
		if ($datestart >= $temdatestart && $datestart <= $temdateend && !$checkholiday)	{
			$tabledata = array("$strdayname<sub><br>[$strdate]</sub>");
			$tabledata[0] .= "<input type=hidden name=t_{$i} value=\"$SCHDATE[$i]\">";
		} else {
			$tabledata[0] = "<font color=gray>$strdayname<sub>[$strdate]</sub></font>";
		}	 
   	
		
       	$flag = true;    	
		$strdate = date("Y-m-d", $datestart);
		$rusformat = date("d.m.y", $datestart);
		$lessonnums = array();

		$tabledata[2] = $tabledata[3] = '';
		$tabledata[1] = '<table border=0>';
		
		if ($datestart >= $temdatestart && $datestart <= $temdateend && !$checkholiday)	{
		
			if ($timelessons = get_records_select('monit_school_schedule_bells', "schoolid = $sid AND weekdaynum = $i", 'smena, lessonnum', 'id, smena, lessonnum, timestart, timeend'))     {
				
				$idtimelesson = array();
				$idschedulesday = array();
                
				if ($schedulesdaycheck = get_records_select('monit_school_class_schedule_'.$rid, "classid = $gid AND datestart = '{$SCHDATE[$i]}'", '', 'id, schedulebellsid'))	{
					foreach ($schedulesdaycheck as $sdc)	{
						$idtimelesson[$sdc->schedulebellsid] = 0;
						$idschedulesday[$sdc->schedulebellsid] = $sdc->id;		
					}
				}
				
		    	foreach ($timelessons as $timelesson)      {
		    		$idtimelesson[$timelesson->id] = 1;
		    		
	   	  			$j = $timelesson->lessonnum;
					$lessonnums[$timelesson->id] = $j;
		          	$tabledata[1] .= '<tr><td>' . $j . '</td></tr>';
		          	$tabledata[2] .= $timelesson->smena . '.' . $j . '. ';
		          
		          	if ($schedules = get_records_select('monit_school_class_schedule_'.$rid, "classid = $gid AND datestart = '{$SCHDATE[$i]}' AND schedulebellsid = {$timelesson->id}", '', 'id, disciplineid, roomid'))	{
		          		$cntsched = count($schedules);
		          		$link2disc = $link2room = '';
		          		if ($cntsched == 2)	{
			          		$schedule1 = current($schedules);
			          		$schedule2 = end($schedules);
			          		if ($schedule1->disciplineid != $schedule2->disciplineid) {
			          			$link2disc = $disciplinemenu[$schedule1->disciplineid] . ' / ' . $disciplinemenu[$schedule2->disciplineid];
			          			$link2room = $roomsoptions[$schedule1->roomid] . ' / ' . $roomsoptions[$schedule2->roomid]; 
			          		}	
						} else if ($cntsched > 2)  {
      		          		foreach($schedules as $schedule1)   {
    			          			$link2disc .= $disciplinemenu[$schedule1->disciplineid] . ' / ';
    			          			$link2room .= $roomsoptions[$schedule1->roomid] . ' / '; 
   			          		}	
						}
						
						$alink  = "createscheduleday.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;nw=$nw&amp;wid=$i";
						if ($link2disc) {
							$tabledata[2] .= '<small><a href="'.$alink.'">'.$link2disc . '</a></small>'; 
							$tabledata[3] .= '<small>'.$link2room . '</small>';
						}	else {    
							$schedule = current($schedules);
			          		$ids = $schedule->id; 
							$tabledata[2] .= choose_from_menu ($disciplinemenu, "d_Z{$ids}_{$i}_{$timelesson->id}", $schedule->disciplineid, '0', "", "", true);
						  	$tabledata[3] .= choose_from_menu ($roomsoptions, "r_Z{$ids}_{$i}_{$timelesson->id}", $schedule->roomid, '0', "", "", true);
						}  	
					} else {
						$tabledata[2] .= choose_from_menu ($disciplinemenu, "d_Z0_{$i}_{$timelesson->id}", 0, '0', "", "", true);
					  	$tabledata[3] .= choose_from_menu ($roomsoptions, "r_Z0_{$i}_{$timelesson->id}", 0, '0', "", "", true);
						
					}  	
				  	$tabledata[2] .= '<br>';
				  	$tabledata[3] .= '<br>';
	    	 	}
	    	 	
			    if (isset($idtimelesson))	{
			    	foreach ($idtimelesson as $key => $idts)	{
			    		if ($idts == 0)	{
			    			notify ('Unknown time lesson id '. $key . '. Deleted schedule id '. $idschedulesday[$key]. '.');
			    			delete_records('monit_school_class_schedule_'.$rid, 'id', $idschedulesday[$key]);
							if (record_exists_mou('monit_school_marks_'.$rid, 'scheduleid', $idschedulesday[$key]))	{
								notify ('Mark exists for schedule id '. $idschedulesday[$key]. '.');
							}	
	
			    		} 
			    	}	
				}	

			}
		}	else	{

		  $strsql = "schoolid = 0 AND datestart = '$SCHDATE[$i]'";
		  if ($prazdnik = get_record_select('monit_school_holidays', $strsql, 'id'))	{
		      $strholidays = get_string('prazdnik', 'block_mou_school'); 
		  }    
		  $tabledata[2] = '<font color=gray>' . $strholidays . '</font>';
		}
		$tabledata[1] .= '</table>'; 
		$table->data[] = array($tabledata[0], $tabledata[2], $tabledata[3]);
		
  		$datestart = $datestart + DAYSECS;
    }
   
    return $table;
}




function copy_shedule_for_next_week($rid, $sid, $gid, $nw)
{
	global $CFG, $GLDATESTART;
	
	$nwnext = $nw + 1;
	$datestart = $GLDATESTART[$nw];
	$strdate = date("Y-m-d", $datestart);
    $holidays = get_records_select('monit_school_holidays', "schoolid = $sid OR schoolid = 0", 'id, datestart, dateend, parallelnum');
    
	$class = get_record_sql("SELECT id, parallelnum FROM {$CFG->prefix}monit_school_class WHERE id=$gid");
	$checkholiday = false;
		
	for ($n=1; $n<=6; $n++)		{
		
		if($shedules = get_records_select('monit_school_class_schedule_'.$rid, "classid = $gid AND datestart = '$strdate'", '',  
                                           'id, schoolid, classid, classdisciplineid, lessonid, teacherid, roomid, datestart, schedulebellsid, disciplineid' )){
			$daynextweekday = $datestart + WEEKSECS;					
			$strnextweekday = date("Y-m-d", $daynextweekday);
			$checkholiday = check_holiday($holidays, $class, $strnextweekday);
			
			if ($checkholiday)	{
				$nwnext = $nw + 2;
				$daynextweekday += WEEKSECS;					
				$strnextweekday = date("Y-m-d", $daynextweekday);
				$checkholiday = check_holiday($holidays, $class, $strnextweekday);
			}
			
			if (!$checkholiday)	{
				if (!record_exists_select_mou('monit_school_class_schedule_'.$rid, "classid = $gid AND datestart = '$strnextweekday'")){
					foreach($shedules as $shedule)	{
						unset($shedule->id);
						$shedule->datestart = $strnextweekday;
						$shedule->lessonid  = 0;
						insert_record('monit_school_class_schedule_'.$rid, $shedule);
					}		
				}
			} else {
				$strholid = get_string('holidayslower', 'block_mou_school');
				notify ("$strnextweekday $strholid.");
			}							
		}
		
		$datestart = $datestart + DAYSECS;
		$strdate = date("Y-m-d", $datestart);
	}
	
	return $nwnext;
}	


function copy_shedule_for_prev_week($rid, $sid, $gid, $nw)
{
	global $CFG, $GLDATESTART;
	
	$nwnext = $nw - 1;
	$datestart = $GLDATESTART[$nw];
	$strdate = date("Y-m-d", $datestart);
	$holidays = get_records_select('monit_school_holidays', "schoolid = $sid OR schoolid = 0", 'id, datestart, dateend, parallelnum');
	$class = get_record_sql("SELECT id, parallelnum FROM {$CFG->prefix}monit_school_class WHERE id=$gid");
	$checkholiday = false;
		
	for ($n=1; $n<=6; $n++)		{
	   
		if($shedules = get_records_select('monit_school_class_schedule_'.$rid, "classid = $gid AND datestart = '$strdate'", '',  
                                           'id, schoolid, classid, classdisciplineid, lessonid, teacherid, roomid, datestart, schedulebellsid, disciplineid' )){
			$daynextweekday = $datestart - WEEKSECS;					
			$strnextweekday = date("Y-m-d", $daynextweekday);
			$checkholiday = check_holiday($holidays, $class, $strnextweekday);
			
			if (!$checkholiday)	{
				if (!record_exists_select_mou('monit_school_class_schedule_'.$rid, "classid = $gid AND datestart = '$strnextweekday'")){
					foreach($shedules as $shedule)	{
						unset($shedule->id);
						$shedule->datestart = $strnextweekday;
						$shedule->lessonid  = 0;
						insert_record('monit_school_class_schedule_'.$rid, $shedule);
					}		
				}
			} else {
				$strholid = get_string('holidayslower', 'block_mou_school');
				notify ("$strnextweekday $strholid.");
			}							
		}
		
		$datestart = $datestart + DAYSECS;
		$strdate = date("Y-m-d", $datestart);
	}
	
	return $nwnext;
}	


function copy_shedule_for_any_week($rid, $sid, $gid, $nw, $ncpyw)
{
	global $CFG, $GLDATESTART;
	
	$nwnext = $ncpyw;
	
	$deltanw = $ncpyw - $nw;
	$datestart = $GLDATESTART[$nw];
	$strdate = date("Y-m-d", $datestart);
	$holidays = get_records_select('monit_school_holidays', "schoolid = $sid OR schoolid = 0", 'id, datestart, dateend, parallelnum');
	$class = get_record_sql("SELECT id, parallelnum FROM {$CFG->prefix}monit_school_class WHERE id=$gid");
	$checkholiday = false;
		
	for ($n=1; $n<=6; $n++)		{
		if($shedules = get_records_select('monit_school_class_schedule_'.$rid, "classid = $gid AND datestart = '$strdate'", '',  
                                           'id, schoolid, classid, classdisciplineid, lessonid, teacherid, roomid, datestart, schedulebellsid, disciplineid' )){
			$daynextweekday = $datestart + $deltanw*WEEKSECS;					
			$strnextweekday = date("Y-m-d", $daynextweekday);
			$checkholiday = check_holiday($holidays, $class, $strnextweekday);
			
			if (!$checkholiday)	{
				if (!record_exists_select_mou('monit_school_class_schedule_'.$rid, "classid = $gid AND datestart = '$strnextweekday'")){
					foreach($shedules as $shedule)	{
						unset($shedule->id);
						$shedule->datestart = $strnextweekday;
						$shedule->lessonid  = 0;
						insert_record('monit_school_class_schedule_'.$rid, $shedule);
					}		
				}
			} else {
				$strholid = get_string('holidayslower', 'block_mou_school');
				notify ("$strnextweekday $strholid.");
			}							
		}
		
		$datestart = $datestart + DAYSECS;
		$strdate = date("Y-m-d", $datestart);
	}
	
	return $nwnext;
}	


function clear_shedule_for_this_week($rid, $sid, $gid, $nw)
{
	global $CFG, $GLDATESTART, $rid;
	
	$nwnext = $nw;
	$datestart = $GLDATESTART[$nw];
	$strdate = date("Y-m-d", $datestart);
    $holidays = get_records_select('monit_school_holidays', "schoolid = $sid OR schoolid = 0", 'id, datestart, dateend, parallelnum');
    
	$class = get_record_sql("SELECT id, parallelnum FROM {$CFG->prefix}monit_school_class WHERE id=$gid");
	$checkholiday = false;
		
	for ($n=1; $n<=6; $n++)		{
		
		if($shedules = get_records_select('monit_school_class_schedule_'.$rid, "classid = $gid AND datestart = '$strdate'", '',  
                                           'id, schoolid, classid, classdisciplineid, lessonid, teacherid, roomid, datestart, schedulebellsid, disciplineid' )){
		    foreach($shedules as $shedule)	{
		        if (record_exists_mou('monit_school_marks_'.$rid, 'scheduleid', $shedule->id))  {
		              notify('Предмет нельзя удалиь из расписания, т.к. в журнале уже проставлены оценки');
		        } else {
		            delete_records_select('monit_school_class_schedule_'.$rid, "id = $shedule->id");  
		        }
		    }  
		}
		
		$datestart = $datestart + DAYSECS;
		$strdate = date("Y-m-d", $datestart);
	}
	
	return $nwnext;
}	

?>