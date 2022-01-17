<?php // $Id: createschedschool.php,v 1.12 2012/02/13 10:32:25 shtifanov Exp $

    exit();
    
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

	$GLDATESTART = array();
	$curryearfull = current_edu_year();
	$curyear = explode('/', $curryearfull);
	$datestartGLOB = $curyear[0].'-09-01';
	$dateendGLOB = $curyear[1].'-05-31';	
    $allweeksinyear = make_all_weeks_in_year($datestartGLOB, $dateendGLOB);

	$strcurdate = date("Y-m-d");
	$rusformat = date("d.m.y");	
	$datestart = $GLDATESTART[$nw];
	$strdate = date("Y-m-d", $datestart);
	
	if ($recs = data_submitted()){
		// print_r($recs); exit();
		
		$schedules = array();
		foreach($recs as $fieldname => $value)	{

		    $mask = substr($fieldname, 0, 2);
		    switch ($mask)  {
				case 'd_': 	$ids = explode('Z', $fieldname);
		            		$schedules[$ids[1]]->disciplineid = $value;
		            		$schedules[$ids[1]]->roomid = 0; // !!!!!!!!!!!!!!!!!!!!!!!!! BADDD!
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
                // id, schoolid, classid, classdisciplineid, lessonid, teacherid, roomid, datestart, schedulebellsid, disciplineid
				if (!$currentshed = get_record_select('monit_school_class_schedule_'.$rid, "id=$id", 'id, classdisciplineid, datestart, schedulebellsid, disciplineid'))	{
					error ("monit_school_class_schedule_$rid with id=$id not found.");
				}   
			
				if ($schedule->disciplineid == 0)  {
					delete_records_select('monit_school_class_schedule_'.$rid, "classid = $ids[3] AND datestart = '{$currentshed->datestart}' AND schedulebellsid = {$currentshed->schedulebellsid}");
					//notify("Delete $currentshed->classdisciplineid : $currentshed->disciplineid", 'green');
				} else {
					$oldschedules = get_records_select('monit_school_class_schedule_'.$rid, "classid = $ids[3] AND datestart = '{$currentshed->datestart}' AND schedulebellsid = {$currentshed->schedulebellsid}", '', 'id, disciplineid');
	          		$oldschedule  = current($oldschedules);
	          		if ($oldschedule->disciplineid == $schedule->disciplineid) {
	          			continue;
	          		}
					
        	
					$strsql = "SELECT id FROM {$CFG->prefix}monit_school_class_discipline
							   WHERE classid=$ids[3] and disciplineid = {$schedule->disciplineid}";
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
			       			//	print_r($sched);
								error(get_string('errorinupdatingschedule','block_mou_school'), $redirlink);
				  			} else {
				  				//notify("Update $sched->classdisciplineid : $sched->disciplineid", 'green');
				  			}
						} else {
							notify($msg);
						}	
					} else {
						delete_records_select('monit_school_class_schedule_'.$rid, "classid = $ids[3] AND schedulebellsid = {$currentshed->schedulebellsid} AND datestart = '{$currentshed->datestart}'");
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
						   WHERE classid=$ids[3] and disciplineid = {$schedule->disciplineid}";
				if ($classdisciplines = get_records_sql ($strsql))	{
					foreach ($classdisciplines as $classdiscipline) {
						$sched->schoolid = $sid;
   						$sched->classid  = $ids[3];
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
	}

    $currenttab = 'createschedule';
    include('tab_act.php');

    $currenttab = 'scheduleschool';
    include('tab_create.php');
    
 	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';	
	listbox_all_weeks_year("createschedschool.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=0&amp;nw=", $allweeksinyear, $nw);
	echo '</table>';
	
	if ($nw!=0){
 		echo  '<form name="timelessons" method="post" action="createschedschool.php">';
		echo  '<input type="hidden" name="pid" value="' . $pid . '">';
		echo  '<input type="hidden" name="gid" value="' . $gid . '">';
		echo  '<input type="hidden" name="nw" value="'  .  $nw . '">';
		echo  '<input type="hidden" name="rid" value="' . $rid . '">';
		echo  '<input type="hidden" name="sid" value="' . $sid . '">';
		
		if ($table = table_createschedule_week ($rid, $yid, $gid, $sid, $nw, $allweeksinyear[$nw],  
			"createschedschool.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;wid="))	{
			print_color_table($table);	
		}
		
		echo  '<div align="center">';
		echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '"></div>';
		echo  '</form>';
	
	}

    print_footer();
 
 
function table_createschedule_week ($rid, $yid, $gid, $sid, $nw, $strweek, $scriptname)
{
	global $CFG, $GLDATESTART, $datestartGLOB, $dateendGLOB;

	$classes = get_records_sql ("SELECT id, name  FROM {$CFG->prefix}monit_school_class
 								  WHERE yearid=$yid AND schoolid=$sid
								  ORDER BY parallelnum, name");

	$table->head  = array (get_string('day','block_mou_school'), get_string('numberlesson','block_mou_school'));
	$table->align = array ('left', 'center');
    $table->size = array ('5%', '2%');
	$table->columnwidth = array (10, 7);

    foreach ($classes as $class)	{
    	$table->head[] = $class->name;
    	$table->align[] = 'left';
    	$table->size[] = '12%'; 
     	$table->columnwidth[] = 20;
    }
    $table->class = 'moutable';
   	$table->width = '100%';
	$table->titlesrows = array(30);
    $table->titles = array();
    $table->titles[] = get_string('lesson', 'block_mou_school');
    $table->downloadfilename = 'week';
    $table->worksheetname = 'lesson';
			
	$arr_date = explode ('-', $datestartGLOB);
	$temdatestart = make_timestamp ($arr_date[0],  $arr_date[1], $arr_date[2], 12);
	$arr_date = explode ('-', $dateendGLOB);
	$temdateend = make_timestamp ($arr_date[0],  $arr_date[1], $arr_date[2], 12);
	
	$roomsoptions = array();
    $roomsoptions[0] = '-';
	if ($rooms = get_records_select('monit_school_room', "schoolid=$sid", '', 'id, name'))	{
		foreach ($rooms as $room) 	{
			$roomsoptions[$room->id] = $room->name;
		}
	}
	
	$datestart = $GLDATESTART[$nw];

	$SCHDATE = array();
	
    for ($i=1; $i<=7; $i++)  {
 
     	$dn = strtolower (date('l', $datestart));
    	$strdayname = get_string ($dn, 'calendar');
    	$strdate = date("d.m.y", $datestart);
        $SCHDATE[$i] = date("Y-m-d", $datestart);
   	
		if ($datestart >= $temdatestart && $datestart <= $temdateend)	{
			$tabledata = array("$strdayname<sub><br>[$strdate]</sub>");
			$tabledata[0] .= "<input type=hidden name=t_{$i} value=\"$SCHDATE[$i]\">";
		} else {
			$tabledata[0] = "<font color=gray>$strdayname<sub>[$strdate]</sub></font>";
		}	 
       	foreach ($classes as $class)	{
       		$tabledata[$class->id] = '';
       	}	

		
       	$flag = true;    	
		$strdate = date("Y-m-d", $datestart);
		$rusformat = date("d.m.y", $datestart);
		$lessonnums = array();

		$tabledata[1] = '<table border=0>';

		if ($datestart >= $temdatestart && $datestart <= $temdateend)	{
		
			if ($timelessons = get_records_select('monit_school_schedule_bells', "schoolid = $sid AND weekdaynum = $i", 'smena, lessonnum, timestart'))     {
		    	foreach ($timelessons as $timelesson)      {
	   	  			$j = $timelesson->lessonnum;
					$lessonnums[$timelesson->id] = $j;
		          	$tabledata[1] .= '<tr><td nowrap>'. $timelesson->smena . '.' . $j . '</td></tr>';
					  		          	
			          foreach ($classes as $class)	{
				        	
							$cldisciplines =  get_records_sql ("SELECT DISTINCT disciplineid FROM {$CFG->prefix}monit_school_class_discipline
										                        WHERE  classid = {$class->id} ");		
							
							$disciplinemenu = array();
							$disciplinemenu[0] = '-';
							if ($cldisciplines)	{
								foreach ($cldisciplines as $cld) 	{
									if($discipline = get_record_select('monit_school_discipline', "id = $cld->disciplineid", 'id, shortname')){
									$disciplinemenu[$discipline->id] = $discipline->shortname;					
									}
								}
								// print_r($disciplinemenu); echo '<hr>';
							
								$strsql = "classid = {$class->id} AND datestart = '{$SCHDATE[$i]}' AND schedulebellsid = {$timelesson->id}";
								// echo $strsql;
									
							  	if ($schedules = get_records_select('monit_school_class_schedule_'.$rid, $strsql, '', 'id, disciplineid'))	{
					          		$cntsched = count($schedules);
					          		$link2disc = $link2room = '';
					          		if ($cntsched == 2)	{
						          		$schedule1 = current($schedules);
						          		$schedule2 = end($schedules);
						          		if ($schedule1->disciplineid != $schedule2->disciplineid) {
						          			$link2disc = $disciplinemenu[$schedule1->disciplineid] . '/' . $disciplinemenu[$schedule2->disciplineid];
						          			// $link2room = $roomsoptions[$schedule1->roomid] . '/' . $roomsoptions[$schedule2->roomid]; 
						          		}	
									}
									
									$alink  = "createscheduleday.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$class->id&amp;nw=$nw&amp;wid=$i";
									if ($link2disc) {
										// $tabledata[$class->id] .= '<small>'.$link2disc . '</small>';
										$tabledata[$class->id] .= '<small><a href="'.$alink.'">'.$link2disc . '</a></small>'; 
										// $tabledata[3] .= $link2room;
									}	else {    
										$schedule = current($schedules);
							  		  	$ids = $schedule->id; 
										$tabledata[$class->id] .= choose_from_menu ($disciplinemenu, "d_Z{$ids}_{$i}_{$timelesson->id}_{$class->id}", $schedule->disciplineid, '0', "", "", true);
									}	
									// print_r($schedule); echo '<hr>';
									//$tabledata[$class->id] .= choose_from_menu ($roomsoptions, "r_Z{$ids}_{$i}_{$timelesson->id}", $schedule->roomid, '0', "", "", true);
								} else {
									$tabledata[$class->id] .= choose_from_menu ($disciplinemenu, "d_Z0_{$i}_{$timelesson->id}_{$class->id}", 0, '0', "", "", true);	
									// $tabledata[$class->id] .= choose_from_menu ($roomsoptions, "r_Z0_{$i}_{$timelesson->id}", 0, '0', "", "", true);					
								}  							
								
							}	
	
					  		$tabledata[$class->id] .= '<br>';
					  		// echo $tabledata[$class->id]; 
					  }	
	    	 	}
			}
		}	
		$tabledata[1] .= '</table>';
		$temparr = array();
		$temparr[] = $tabledata[0];
		$temparr[] = $tabledata[1];
		foreach ($tabledata as $key => $ttt) {
			if ($key <= 1) continue;
		 	$temparr[]= $ttt;
		}	
		 	
		$table->data[] = $temparr;
		
  		$datestart = $datestart + DAYSECS;
    }
   
    return $table;
	}   
?>