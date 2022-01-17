<?php // $Id: journalclass.php,v 1.51 2012/10/17 10:58:24 shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once($CFG->dirroot.'/calendar/lib.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_school.php');    
	require_once('../authbase.inc.php');    

	$view_capability = has_capability('block/mou_school:viewjournalclass', $context);
	$edit_capability = has_capability('block/mou_school:editjournalclass', $context);
       
	$cdid 	= optional_param('cdid', 0, PARAM_INT);	  // class_discipline (subgroup) id
    $termid	= optional_param('tid',  0, PARAM_INT);   // Semestr id
    $gid 	= optional_param('gid',  0, PARAM_INT);   // Class id
    $p 		= optional_param('p', 	 0, PARAM_INT);   // Parallel number
    $period = optional_param('p', 	 'day'); // Period time: day, week, month, year
    $jid 	= optional_param('jid',  0, PARAM_INT);   // Schedule id (jornal id)
    $prevjid 	= optional_param('prevjid',  0, PARAM_INT);   // Schedule id (jornal id)    
    $nextjid 	= optional_param('nextjid',  0, PARAM_INT);   // Schedule id (jornal id)    

    if ($recs = data_submitted())  {
    	$edit_capability_class = false;
        $jid = 0;
        
        if (isset($recs->saveprev))  $jid = $prevjid;
        
        if (isset($recs->savenext))  $jid = $nextjid;
        
		if ($gid != 0)  { 
			$context_class = get_context_instance(CONTEXT_CLASS, $gid);
			$edit_capability_class = has_capability('block/mou_school:editjournalclass', $context_class);
		}
		
		$edit_capability_discipline = false;
		if ($cdid != 0)  {
			$ctxdiscipline = get_context_instance(CONTEXT_DISCIPLINE, $cdid);
			$edit_capability_discipline = has_capability('block/mou_school:editjournalclass', $ctxdiscipline);
		}

		if (!$edit_capability && !$edit_capability_class && !$edit_capability_discipline)	{
			error(get_string('permission', 'block_mou_school'), '../index.php');
		}	
    	
		$redirlink = "journalclass.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;cdid=$cdid&amp;gid=$gid&amp;tid=$termid";
		// echo '<pre>'; print_r($recs); echo '</pre>'; echo '<hr>';
        // notice(get_string('succesavedata','block_monitoring'), $redirlink);

        save_marks_fast($recs);
        // save_marks_slow($recs);
	}



	$GLDATESTART = array();
	$curyear = date('Y');
	$datestart = $curyear.'-09-01';
	$curyear++;
	$dateend = $curyear.'-05-31';	

    $currenttab = 'journalclass';
    include('tabsjrnl.php');

	if ($view_capability)	{

	 	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
    	// $strlistclasses =  listbox_class_role("journalclass.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;cdid=$cdid&amp;tid=$termid&amp;gid=", $rid, $sid, $yid, $gid);
        $strlistclasses =  listbox_class_role("journalclass.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&gid=", $rid, $sid, $yid, $gid);
    	echo $strlistclasses;
    	$strlistpredmets =listbox_discipline_class_role("journalclass.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&cdid=", $sid, $yid, $gid, $cdid);
    	
    	if ($strlistpredmets)	{
    		echo $strlistpredmets;
    	} else if ($gid) {
    		echo '</table>';
    		notice (get_string('classdisciplinesnotfound', 'block_mou_school'), "../class/classdisciplines.php?rid=$rid&amp;yid=$yid&amp;gid=$gid&amp;sid=$sid");
    	}	
    	
		// listbox_class("journalclass.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;cdid=$cdid&amp;tid=$termid&amp;gid=", $rid, $sid, $yid, $gid);
		// listbox_discipline_class("journalclass.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;tid=$termid&amp;cdid=", $sid, $yid, $gid, $cdid);
	
		if ($gid != 0 && $cdid != 0)		{

			$context_class = get_context_instance(CONTEXT_CLASS, $gid);
			$edit_capability_class = has_capability('block/mou_school:editjournalclass', $context_class);

			$classdiscipline = get_record_sql("SELECT id, schoolsubgroupid, disciplineid, teacherid FROM {$CFG->prefix}monit_school_class_discipline WHERE id=$cdid");
			
			$ctxdiscipline = get_context_instance(CONTEXT_DISCIPLINE, $cdid);
			$edit_capability_discipline = has_capability('block/mou_school:editjournalclass', $ctxdiscipline);
			
			    // id, schoolid, classid, , teacherid, name, shortname, descriptions
			if ($classdiscipline->teacherid > 0)	{
				$user = get_record_sql("SELECT id, firstname, lastname FROM {$CFG->prefix}user
											WHERE id={$classdiscipline->teacherid}");
				$str = get_string('teacher','block_mou_school');
				echo '<tr><td>'.get_string('teacher','block_mou_school').':</td><td>';
				echo $user->lastname.' '.$user->firstname;
				echo '</td></tr>';
				listbox_terms("journalclass.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;cdid=$cdid&amp;gid=$gid&amp;tid=", $sid, $yid, $gid, $termid);		
			} else {
				echo '</table>';
				notice(get_string('notassignteacher', 'block_mou_school'), "../class/classdisciplines.php?rid=$rid&amp;yid=$yid&amp;gid=$gid&amp;sid=$sid");
				//$CFG->wwwroot."/blocks/mou_school/curriculum/discipteachers.php?rid=$rid&amp;yid=$yid&amp;sid=$sid");
				/*
				$user->id = 0;
				echo '<tr><td>'.get_string('teacher','block_mou_school').':</td><td>';
				print_string('notassignteacher', 'block_mou_school');
				echo '</td></tr>';
				listbox_terms("journalclass.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;cdid=$cdid&amp;gid=$gid&amp;tid=", $sid, $yid, $gid, $termid);
				*/			
			}
			echo '</table>';
		
		
			if ($termid != 0)	{	
			    
				$currenttab = 'marks';
			    include('tabsjrnl2.php');				
			
				//	print_heading(get_string('classpupils','block_mou_school'),'center',2);
				
				$table = table_journal($rid, $sid, $yid, $gid, $cdid, $termid, $jid);
				
				if ($jid != 0 && ($edit_capability || $edit_capability_class || $edit_capability_discipline))	{		
					echo  '<form name="marks" method="post" action="journalclass.php">';
					echo  '<input type="hidden" name="rid" value="' . $rid . '">';
					echo  '<input type="hidden" name="sid" value="' . $sid . '">';
					echo  '<input type="hidden" name="yid" value="' . $yid . '">';
					echo  '<input type="hidden" name="gid" value="' . $gid . '">';
					echo  '<input type="hidden" name="cdid" value="' . $cdid . '">';
					echo  '<input type="hidden" name="tid" value="' . $termid . '">';
					echo  '<input type="hidden" name="jid" value="' . $jid . '">';
					echo  '<div align="center">';
                    echo  '<input type="submit" name="saveprev" value="Предыдущий урок">';
					echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '">';
                    echo  '<input type="submit" name="savenext" value="Следующий урок">';
					echo  '<p></p></div>';
					print_color_table($table);
					echo  '<div align="center">';
                    echo  '<input type="submit" name="saveprev" value="Предыдущий урок">';
					echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '">';
                    echo  '<input type="submit" name="savenext" value="Следующий урок">';
					echo  '<input type="hidden" name="prevjid" value="' . $table->prevjid . '">';
					echo  '<input type="hidden" name="nextjid" value="' . $table->nextjid . '">';                    
					echo  '</div></form>';
				} else {
					print_color_table($table);			
				}
			
			/*  ВЫБЫВШИХ СДЕЛАТЬ ПО ДРУГОМУ
				if ($table2 = table_journal_outpupil($rid, $sid, $yid, $gid, $cdid, $termid, $jid))	{
					print_heading(get_string('outed','block_mou_school'),'center',2);
					print_color_table($table2);	
				}
			*/ 
			    echo '<p></p>';
                print_simple_box_start('center', '30%', 'white');
			   	print_heading('Перечень отметок в журнале', 'center', 4);
				
				echo '<small>Минимальный балл: <b>1</b><br>';
				echo 'Максимальный балл: <b>5</b><br>';
				echo 'Пропуск по неуважительной причине: <b>Н</b> или <b>н</b><br>';
				echo 'Пропуск по уважительной причине: <b>У</b> или <b>у</b><br>';
				// echo 'Опоздание на урок: <b>О</b> или <b>о</b></br>';
				// echo 'Отсутствие на уроке по неопределенной причине: <b>-</b></small><br><br>';
				echo '<i>Замечание: для удаления отметки в журнале введите 0(ноль).</i>';
				
			   	// print_heading('Для удаления отметки в журнале введите 0(ноль)', 'center', 3);				
		  		print_simple_box_end();		
			}
		} else {
			echo '</table>';
		}
	} else {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}
 	
	

    print_footer();

    
function table_journal($rid, $sid, $yid, $gid, $cdid, $termid, $jid = 0)
{
	global $CFG, $edit_capability, $edit_capability_class, $edit_capability_discipline, $classdiscipline;
	
	$strpupils = get_string('pupils', 'block_mou_school');
	$title_full_months = array(' ');// array('<b>'.$strpupils.'</b>');
	 
	$table->head  = array ($strpupils);
	$table->align = array ('left');
	$table->size = array ('20%');
	$table->columnwidth = array (20);
	
	$school_term = get_record_select('monit_school_term', "id=$termid" , 'id, datestart, dateend'); // приходит с URL
	$strsql = "SELECT id, lessonid, datestart  FROM {$CFG->prefix}monit_school_class_schedule_$rid
              WHERE classdisciplineid=$cdid AND datestart >= '$school_term->datestart' AND datestart <= '$school_term->dateend' 
			  ORDER BY datestart";
	
    // echo $strsql . '<hr>';
     		  	  
 	$schedulesids = array();
	if ($schedules = get_records_sql($strsql))	{
		 foreach ($schedules as $schedule)	{
		 	 $schedulesids[] =  $schedule->id;
			 $date = explode('-', $schedule->datestart);
			 $month = get_string('sn_'.$date[1],'block_mou_school');	 
			 $full_month = $month.' '.$date[2];
			 $title_full_months[] = '<b>'.$full_month.'</b>'; 
			 					 		
			 $title = get_string('editmarks','block_mou_school');
			 $strlinkupdate = "<a title=\"$title\" href=\"setmarks.php?mod=1&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;cdid=$cdid&amp;gid=$gid&amp;tid=$termid&amp;jid={$schedule->id}\">";
			 $strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";

    		 $title = get_string('taskslessons', 'block_mou_school');
			 $strlinkupdate .= " <a title=\"$title\" href=\"setheme.php?mod=1&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;cdid=$cdid&amp;gid=$gid&amp;tid=$termid&amp;jid={$schedule->id}&amp;themeid={$schedule->lessonid}&amp;ret=0\">";
			 $strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/grades.gif\" alt=\"$title\" /></a>&nbsp;";           			
			
			 if ($jid == 0 && ($edit_capability || $edit_capability_class || $edit_capability_discipline))	{
		 	 	$edit_month = "<div align=center><strong><a href=\"{$CFG->wwwroot}/blocks/mou_school/journal/journalclass.php?mod=1&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;cdid=$cdid&amp;gid=$gid&amp;tid=$termid&amp;jid={$schedule->id}\">".$full_month."</a></strong><br>".$strlinkupdate."</div>";
		 	 } else {	
			 	$edit_month = $full_month;
			 }	
			 $table->head[] = $edit_month;	
			 $table->align[] = 'center';
			 $table->size[] = '5%';
		     $table->columnwidth[] = 10;  		
		 }
		 
		 $schedulelist = implode(',', $schedulesids);
	}

    // echo '<pre>'; print_r($schedulesids); echo '</pre><hr>';
    if ($jid != 0)  {
        $key = array_search($jid, $schedulesids);
        if ($key == 0)    {
            $table->prevjid = 0;   
        } else {
            $table->prevjid = $schedulesids[$key-1];
        }
        if (isset($schedulesids[$key+1]))   {
            $table->nextjid = $schedulesids[$key+1];
        } else {
            $table->nextjid = 0;
        }
        // echo $table->prevjid . '<br>' . $table->nextjid ;
    }     
    
    
      
    $table->head[] = $strpupils;
	$title_full_months[] = ' ';    
	$table->align[] = 'left';
	$table->size[] = '20%';
	$table->columnwidth[] = 20;
					  
	$table->head[] = get_string('srmark','block_mou_school');	
	$title_full_months[] = '<b>'. get_string('srmark','block_mou_school') . '</b>';
	$table->align[] = 'center';
	$table->size[] = '5%';
	$table->columnwidth[] = 10; 
	
	$table->head[] = get_string('periodmarks','block_mou_school');
	$title_full_months[] =	'<b>'.get_string('periodmarks','block_mou_school').'</b>';
	$table->align[] = 'center';
	$table->size[] = '5%';
	$table->columnwidth[] = 10; 
	 	
    $table->class = 'moutable';
   	$table->width = '80%';
    $table->titles = array();
    $table->titles[] = get_string('createschedule', 'block_mou_school');
    $table->worksheetname = 'createschedule';
    
	$tabledata = array();

    $studentsql = "SELECT u.id, u.username, u.firstname, u.lastname
                            FROM {$CFG->prefix}user u
                       LEFT JOIN {$CFG->prefix}monit_school_pupil_card m ON m.userid = u.id 
					   WHERE classid = $gid AND u.deleted = 0 AND u.confirmed = 1
					   ORDER BY u.lastname, u.firstname";

   if($students = get_records_sql($studentsql)) {
   		$count_print_title = 0;
		foreach ($students as $student) {
			$count_print_title++;
			if ($classdiscipline->schoolsubgroupid)	{
				if (!record_exists_select ('monit_school_subgroup_pupil', "schoolid = $sid AND userid = {$student->id} AND classdisciplineid = $cdid")) {
					continue;
				}	
			}
			
	 		if ($edit_capability || $edit_capability_class)	{
	 			$pupilcard = 'pupilcard.php';
			} else {
				$pupilcard = 'pupil.php';
			}	  
			
			$tabledata = array("<div align=left><strong><a href=\"{$CFG->wwwroot}/blocks/mou_school/class/{$pupilcard}?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid={$student->id}\">".fullname($student)."</a></strong></div>");
			
			$allmarks = 0;
			$countmarks = 0;
			if ($schedules){	
				$attendancesids = array();
				$strsql = "SELECT id, scheduleid, reason FROM {$CFG->prefix}monit_school_attendance_$rid
					       WHERE userid = $student->id AND scheduleid IN ($schedulelist)";
			    if ($attendances = get_records_sql($strsql))	{
			    	foreach ($attendances as $attendance)	{
			    		$attendancesids[$attendance->scheduleid] = $attendance->reason; 
			    	}
			    }
			    
			    $mark1ids = array();
			    $mark2ids = array();
				$strsql = "SELECT id, scheduleid, mark, mark2 FROM {$CFG->prefix}monit_school_marks_$rid
					       WHERE userid = $student->id AND scheduleid IN ($schedulelist)";
				// echo $strsql . '<br>'; 	       
				if ($markstuders = get_records_sql($strsql))	{
					// print_r($markstuders); echo '<hr>';
					foreach ($markstuders as $ms) {
						$mark1ids[$ms->scheduleid] = $ms->mark;
						$mark2ids[$ms->scheduleid] = $ms->mark2;
					}
				}
				// print_object($mark1ids); echo '<hr>';
				// print_r($mark2ids); echo '<hr>';
				
                foreach ($schedules as $schedule)	{

					$table_td = $table_td2 = $reason_td = '';

					if (!empty($attendancesids[$schedule->id]))	{
						$reason_td = $attendancesids[$schedule->id];
					} 
				
					if (!empty($mark1ids[$schedule->id]))	{
						$table_td  = $mark1ids[$schedule->id];
						$allmarks += $table_td;
						$countmarks++;
					} 

					if (!empty($mark2ids[$schedule->id]))	{
						$table_td2  = $mark2ids[$schedule->id];
						$allmarks  += $table_td2;
						$countmarks++;
					} 
					
					
					if ($schedule->id == $jid && ($edit_capability || $edit_capability_class || $edit_capability_discipline))	{
						if ($reason_td != '')	{
							if ($table_td == '')  $table_td = $reason_td;
						}
						$tabledata[] = "<input type=text  name=f_{$schedule->id}_{$student->id} size=2 MAXLENGTH=2 value=\"$table_td\">";
					} else {
						if($table_td2 != '' || $table_td2 != 0) {
							$table_td .= '/'.$table_td2;
						}  
						
						if ($reason_td != '')	{
							if ($table_td == '')  $table_td = $reason_td;
							else  $table_td .= '/'.$reason_td;
						}
						$tabledata[] = $table_td;							
					}				
				}				
			}
			
			$tabledata[] = '<b>'. fullname($student) . '</b>';
			
			if ($countmarks != 0)	{
				$division = number_format($allmarks/$countmarks, 2, ',', '');
				$tabledata[] = $division; 
			} else {
				$tabledata[] = '-';
			}	

			$strselect = "userid=$student->id AND classdisciplineid=$cdid AND termid=$termid"; 
			if ($markrecord = get_record_select('monit_school_marks_totals_term', $strselect, 'id, mark, avgmark'))	{
				$tabledata[] = '<b>' . $markrecord->mark . '</b>'; // round($allmarks/$countmarks); // !!!
			} else {
				$tabledata[] = '-';
			}		
			
			$table->data[] = $tabledata;
			
			if ($count_print_title == 10)	{
				$tabledata = array();
				foreach ($title_full_months as $headbottom)	{
					$tabledata[] = $headbottom;
				}				
				$table->data[] = $tabledata;	
				$count_print_title = 0;
			}	
		}		
	}
	// echo $count_print_title ;
	if ($count_print_title > 5)	{
		$tabledata = array();
		foreach ($title_full_months as $headbottom)	{
			$tabledata[] = $headbottom;
		}
		$table->data[] = $tabledata;						
	}	
	
    return $table;
}


/*
function table_journal_outpupil($rid, $sid, $yid, $gid, $cdid, $termid, $jid = 0)
{
	global $CFG, $edit_capability, $edit_capability_class, $edit_capability_discipline;

	$table->head  = array (get_string('pupils', 'block_mou_school'));
	$table->align = array ('left');
	$table->size = array ('20%');
	$table->columnwidth = array (20);
	
	$school_term = get_record_select('monit_school_term', "id=$termid" , 'id, datestart, dateend'); // приходит с URL
	$strsql = "SELECT * FROM {$CFG->prefix}monit_school_class_schedule_$rid s
              LEFT JOIN {$CFG->prefix}monit_school_movepupil m ON s.classid = m.classinid
              WHERE  m.classinid = $gid AND m.schoolinid = s.schoolid AND m.rayoninid = $rid AND m.dateout >= '$school_term->datestart' AND m.dateout <= '$school_term->dateend' 
			  ORDER BY dateout";	  
//	$title = 'df;ogjdojgh';	
 
	if ($shedules = get_records_sql($strsql))	{
		 foreach ($shedules as $shed)	{
			 $date = explode('-', $shed->dateout);
			 $month = get_string('sn_'.$date[1],'block_mou_school');	 
			 $full_month = $month.'<br>'.$date[2];
		 //	 $edit_month = "<div align=center><strong><a href=\"{$CFG->wwwroot}/blocks/mou_school/journal/journalclass.php?mod=1&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;cdid=$cdid&amp;gid=$gid&amp;tid=$termid&amp;jid={$shed->id}\">".$full_month."</a></strong></div>";
			 //$edit_month = $edit_month . "<alt=\"$title\" /></a>&nbsp;";
			 $table->head[] = $full_month;	
			 $table->align[] = 'center';
			 $table->size[] = '5%';
		     $table->columnwidth[] = 10;  		
		 }
	}
					  
	$table->head[] = get_string('srmark','block_mou_school');	
	$table->align[] = 'center';
	$table->size[] = '5%';
	$table->columnwidth[] = 10; 
	
	$table->head[] = get_string('periodmarks','block_mou_school');	
	$table->align[] = 'center';
	$table->size[] = '5%';
	$table->columnwidth[] = 10; 
	 	
    $table->class = 'moutable';
   	$table->width = '80%';
    $table->titles = array();
    $table->titles[] = get_string('createschedule', 'block_mou_school');
    $table->worksheetname = 'createschedule';
	
	$tabledata = array();

        $studentsql = "SELECT u.id, u.username, u.firstname, u.lastname
                            FROM {$CFG->prefix}user u
                       LEFT JOIN {$CFG->prefix}monit_school_movepupil m ON m.userid = u.id 
					   WHERE m.classinid = $gid AND m.schoolinid = $sid AND m.rayoninid = $rid
					   ORDER BY u.lastname, u.firstname";
					   
					   
    if($students = get_records_sql($studentsql)) {
		foreach ($students as $student) {			
			$tabledata = array("<div align=left><strong><a href=\"{$CFG->wwwroot}/blocks/mou_school/class/pupilcard.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid={$student->id}\">".fullname($student)."</a></strong></div>");
			
			$allmarks = 0;
			$countmarks = 0;
			if ($shedules){				
				foreach ($shedules as $shed)	{
				    $attendance = get_record("monit_school_attendance_$rid",  'userid', $student->id, 'scheduleid', $shed->id);
					$markstuder = get_record('monit_school_marks_'.$rid, 'userid', $student->id, 'scheduleid', $shed->id);

					$table_td = '';
				
					if (!empty($markstuder->mark))	{
						$table_td = $markstuder->mark;
						$allmarks += $markstuder->mark;
						$countmarks++;
					} elseif (!empty($attendance->reason))	{
						$table_td = $attendance->reason;
					//	$allreasons += $markstuder->mark;
						//$countmarks++;
					} 

					if ($shed->id == $jid && ($edit_capability || $edit_capability_class || $edit_capability_discipline))	{
						$tabledata[] = "<input type=text  name=f_{$shed->id}_{$student->id} size=2 MAXLENGTH=2 value=\"$table_td\">";
					}	else {
						$tabledata[] = $table_td;						
					}				

					
					
				}				
			}
			if ($countmarks != 0)	{
				$division = number_format($allmarks/$countmarks, 2, ',', '');
				$tabledata[] = $division; 
				$tabledata[] = round($allmarks/$countmarks); // !!!
			} else {
				$tabledata[] = '-';	
				$tabledata[] = '-';
			}	
			$table->data[] = $tabledata;	
		}		
	} else {
		$table = false;
	}			
    return $table;
}
*/


function save_marks_fast($recs)
{
  global $CFG, $rid;     

    $attendances = array();
    $userids = array();
    $marks = array();
    
    $tablename = 'monit_school_marks_'.$rid;
    $otmetki = array('Н','н','У','у','О','о');
    
	foreach($recs as $fieldname => $value)	{
        if ($value == '') continue;
		$mask = substr($fieldname, 0, 2);
		if ($mask == 'f_')	{
           	$ids = explode('_', $fieldname);
           	// $scheduleids[] = $ids[1];
           	$userids[] = $ids[2];
            $mark = trim($value);
            if (in_array($mark,$otmetki))   {
                $attendances[$ids[2]] = $mark; 
            }  else  if ($mark >= 0 && $mark <= 5)	{
           	   $marks[$ids[2]] = $mark;
            } 
        }
    }        
     
    // print_r($userids); echo 'U<hr>';
    // print_r($marks); echo 'M<hr>';
    // print_r($attendances); echo 'A<hr>';
    
    if (empty($userids))    return;
    
    $strusersids = implode(',', $userids);  
    if ($oldmarks = get_records_select($tablename, "scheduleid = $recs->jid AND  userid in ($strusersids)", '', 'id, userid, mark'))    {
        // echo '<pre>'; print_r($oldmarks); echo '</pre>'; echo '<hr>';
        $omarks = array();
        foreach ($oldmarks as $oldmark) {
            $omarks[$oldmark->userid] = $oldmark->mark; 
        }
        // print_r($omarks); echo 'O<hr>';
        $diffmarks = array_diff_assoc ($marks, $omarks);
        // print_r($diffmarks); echo 'D<hr>';
        foreach ($diffmarks as $userid => $diffmark)   {
            if (isset($omarks[$userid])) {
                if ($marks[$userid] == 0)   {
            		delete_records($tablename, 'userid', $userid, 'scheduleid', $recs->jid);
            		delete_records('monit_school_attendance_'.$rid, 'userid', $userid, 'scheduleid', $recs->jid);
                    // echo "DELETE $tablename $userid => $marks[$userid] <br>";    
                   // echo "DELETE monit_school_attendance_$rid $userid => $marks[$userid] <br>";
                } else {
                   // echo "UPDATE $userid => $marks[$userid] <br>";
           		   set_field($tablename, 'mark', $diffmark, 'userid', $userid, 'scheduleid', $recs->jid);
                }    
            } else {
                if ($marks[$userid] > 0)   {
                    // echo "INSERT $userid => $marks[$userid] <br>";
            		$newrec->userid = $userid;
            		$newrec->scheduleid = $recs->jid;
            		$newrec->mark = $diffmark;
            		$newrec->mark2 = 0;
            		$newrec->datedone = date('Y-m-d');
			        if (!$lastmark = insert_record($tablename, $newrec))	{
						error(get_string('errorinaddingmark','block_mou_school'), $redirlink);
				    }
                }        
            }    
        }
        
    } else {
        if (!empty($marks)) {
            foreach ($marks as $userid => $mark)   {
                if (isset($marks[$userid]) && $marks[$userid] > 0) {
                    // echo "INSERT $userid => $marks[$userid] <br>";
            		$newrec->userid = $userid;
            		$newrec->scheduleid = $recs->jid;
            		$newrec->mark = $mark;
            		$newrec->mark2 = 0;
            		$newrec->datedone = date('Y-m-d');
			        if (!$lastmark = insert_record($tablename, $newrec))	{
						error(get_string('errorinaddingmark','block_mou_school'), $redirlink);
				    }
                }    
            }
        }    
    }

    if ($oldmarks = get_records_select('monit_school_attendance_'.$rid, "scheduleid = $recs->jid AND  userid in ($strusersids)", '', 'id, userid, reason'))    {
        // echo '<pre>'; print_r($oldmarks); echo '</pre>'; echo '<hr>';
        $omarks = array();
        foreach ($oldmarks as $oldmark) {
            $omarks[$oldmark->userid] = $oldmark->reason; 
        }
        // print_r($omarks); echo 'O<hr>';
        $diffmarks = array_diff_assoc ($attendances, $omarks);
        // print_r($diffmarks); echo 'D<hr>';
        if (empty($diffmarks)) $diffmarks = array_diff_assoc ($omarks, $attendances);
        foreach ($diffmarks as $userid => $diffmark)   {
            if (isset($omarks[$userid])) {
               // echo "UPDATE $userid => $attendances[$userid] <br>";
               if (isset($attendances[$userid])) {
                    set_field('monit_school_attendance_'.$rid, 'reason', $attendances[$userid], 'userid', $userid, 'scheduleid', $recs->jid);
               } else if ($marks[$userid] == 0)   {
               		delete_records($tablename, 'userid', $userid, 'scheduleid', $recs->jid);
            		delete_records('monit_school_attendance_'.$rid, 'userid', $userid, 'scheduleid', $recs->jid);
               }     
            } else {
               // echo "INSERT $userid => $attendances[$userid] <br>";
        		$newrec->userid = $userid;
        		$newrec->scheduleid = $recs->jid;
        		$newrec->reason = $attendances[$userid];
		        if (!$lastmark = insert_record('monit_school_attendance_'.$rid, $newrec))	{
					error(get_string('errorinaddingmark','block_mou_school'), $redirlink);
			    }
            }    
        }
        
    } else {
        if (!empty($attendances))   {
            foreach ($attendances as $userid => $mark)   {
                if (isset($attendances[$userid])) {
                    // echo "INSERT $userid => $attendances[$userid] <br>";
            		$newrec->userid = $userid;
            		$newrec->scheduleid = $recs->jid;
            		$newrec->reason = $attendances[$userid];
			        if (!$lastmark = insert_record('monit_school_attendance_'.$rid, $newrec))	{
						error(get_string('errorinaddingmark','block_mou_school'), $redirlink);
				    }
                        
                }    
            }
        }    
    }
}


function save_marks_slow($recs)
{
  global $CFG, $rid;     

	foreach($recs as $fieldname => $mark)	{

		$mask = substr($fieldname, 0, 2);
		if ($mask == 'f_')	{
           	$ids = explode('_', $fieldname);
           	$scheduleid = $ids[1];
           	$userid = $ids[2];
           	$mark = trim($mark);
           	if (empty($mark))	{
            	if (record_exists_mou('monit_school_marks_'.$rid, 'userid', $userid, 'scheduleid', $scheduleid))	{
            		delete_records('monit_school_marks_'.$rid, 'userid', $userid, 'scheduleid', $scheduleid);
            	}	
            	if (record_exists_mou('monit_school_attendance_'.$rid, 'userid', $userid, 'scheduleid', $scheduleid))	{
            		delete_records('monit_school_attendance_'.$rid, 'userid', $userid, 'scheduleid', $scheduleid);
            	}	
           	} else if (is_numeric($mark))	{
            	if (record_exists_mou('monit_school_marks_'.$rid, 'userid', $userid, 'scheduleid', $scheduleid))	{
            		if ($mark >= 1 && $mark <= 5)	{
            			set_field('monit_school_marks_'.$rid, 'mark', $mark, 'userid', $userid, 'scheduleid', $scheduleid);
					} else {
						notify (get_string('notvalidotmetka', 'block_mou_school', $mark));
					}    
            	} else {
            		if ($mark >= 1 && $mark <= 5)	{ 
	            		$newrec->userid = $userid;
	            		$newrec->scheduleid = $scheduleid;
	            		$newrec->mark = $mark;
	            		$newrec->mark2 = 0;
	            		$newrec->datedone = date('Y-m-d');
				        if (!$lastmark = insert_record('monit_school_marks_'.$rid, $newrec))	{
							error(get_string('errorinaddingmark','block_mou_school'), $redirlink);
					    }
					} else {
						notify (get_string('notvalidotmetka', 'block_mou_school', $mark));
					}    
            	}	
           	} else 	{
        		$otmetki = array('Н','н','У','у','О','о','-');  
            	if (record_exists_mou('monit_school_attendance_'.$rid, 'userid', $userid, 'scheduleid', $scheduleid))	{
            		if (in_array($mark,$otmetki))	{	            		
	            		set_field('monit_school_attendance_'.$rid, 'reason', $mark, 'userid', $userid, 'scheduleid', $scheduleid);
	            	} else {
						notify (get_string('notvalidotmetka', 'block_mou_school', $mark));							
					}  	
            	} else {
            		if (in_array($mark,$otmetki))	{  
	            		$newrec->userid = $userid;
	            		$newrec->scheduleid = $scheduleid;
	            		$newrec->reason = $mark;
				        if (!$lastmark = insert_record('monit_school_attendance_'.$rid, $newrec))	{
							error(get_string('errorinaddingmark','block_mou_school'), $redirlink);
					    }
					} else {
						notify (get_string('notvalidotmetka', 'block_mou_school', $mark));							
					}    
            	}	
            } 
        }   
	}
}

?>