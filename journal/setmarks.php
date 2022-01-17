<?php // $Id: setmarks.php,v 1.18 2012/02/13 10:32:24 shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once($CFG->dirroot.'/calendar/lib.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_school.php');    
/*	require_once('../authbase.inc.php');    

	$view_capability = has_capability('block/mou_school:viewjournalclass', $context);
	$edit_capability = has_capability('block/mou_school:editjournalclass', $context);
*/
    $rid = required_param('rid', PARAM_INT);    // Rayon id
    $sid = required_param('sid', PARAM_INT);    // School id
    $yid = required_param('yid', PARAM_INT);    // Year id
    $gid = required_param('gid', PARAM_INT);   // Class id
    $jid = required_param('jid',  PARAM_INT);   // Schedule id (jornal id)
   
	$cdid 	= optional_param('cdid', 0, PARAM_INT);	  // class_discipline (subgroup) id
    $termid	= optional_param('tid',  0, PARAM_INT);   // Semestr id
    $p 		= optional_param('p', 	 0, PARAM_INT);   // Parallel number
    $period = optional_param('p', 	 'day'); // Period time: day, week, month, year

    $tyid	= optional_param('tyid',  0, PARAM_INT);   // Type of task

	$context = get_context_instance(CONTEXT_SCHOOL, $sid);
	$edit_capability = has_capability('block/mou_school:editjournalclass', $context);

	$context_class = get_context_instance(CONTEXT_CLASS, $gid);
	$edit_capability_class = has_capability('block/mou_school:editjournalclass', $context_class);
	
	$ctxdiscipline = get_context_instance(CONTEXT_DISCIPLINE, $cdid);
	$edit_capability_discipline = has_capability('block/mou_school:editjournalclass', $ctxdiscipline);
	
	if (!$edit_capability && !$edit_capability_class && !$edit_capability_discipline)	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	
	
	$strjournal = get_string('journalclass','block_mou_school');
   	$strtitle = get_string('setmarks', 'block_mou_school');	
   //	$strtitle = get_string('updateclass', 'block_mou_ege');	

	$breadcrumbs = '<a href="'.$CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('title','block_mou_school').'</a>';
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/mou_school/journal/journalclass.php?rid=$rid&amp;yid=$yid&amp;cdid=$cdid&amp;gid=$gid&amp;tid=$termid&amp;sid=$sid\">$strjournal</a>";
	$breadcrumbs .= "-> $strtitle";
	print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);
	
    if ($recs = data_submitted())  {
        //  print_r($recs);
        //	$shd = get_record('monit_school_schedule','id', $recs->jid);
    	
		$redirlink = "setmarks.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;cdid=$cdid&amp;gid=$gid&amp;jid=$jid&amp;tid=$termid";
		$usermarks = array();
		$userattends = array();
		foreach($recs as $fieldname => $mark)	{
		    $mask = substr($fieldname, 0, 2);
		    switch ($mask)  {
				case 'm1': $ids = explode('Z', $fieldname);
							if (is_numeric($mark) && $mark >= 0 && $mark <= 5)	{
           						$usermarks[$ids[1]]->mark = $mark;
           					}	
  				break;
				case 'm2': 	$ids = explode('Z', $fieldname);
							if (is_numeric($mark) && $mark >= 0 && $mark <= 5)	{				           	
           						$usermarks[$ids[1]]->mark2 = $mark;
           					}	
  				break;
				case 'a1':	$ids = explode('Z', $fieldname);
			        		  
		            		//if (!empty($mark) && in_array($mark, $otmetki))	{	            		
	           					$userattends[$ids[1]]->reason = $mark;
	           				// }
  				break;
  			}	
  		}
  		
		if(isset($recs->name)){
			
			$newrec->schoolid = $recs->sid;
        	$newrec->classdisciplineid = $recs->cdid;
        	$newrec->scheduleid = $recs->jid;
        	$newrec->name = $recs->name;
        	$newrec->datestart = '0000-00-00';
        	$newrec->datefinish = '0000-00-00';
        	$newrec->description = ' ';
        	$newrec->type_ass = $recs->tyid;
        	
   			if($existid = get_record_select('monit_school_assignments_'.$rid, "schoolid=($newrec->schoolid) AND classdisciplineid={$newrec->classdisciplineid} AND scheduleid = {$newrec->scheduleid}", 'id')){
 				$newrec->id = $$existid->id;
				if(!update_record('monit_school_assignments_'.$rid,$newrec)){
        			error(get_string('errorinedingtheme','block_mou_school'));
        		}     				
  			}
			else if(!insert_record('monit_school_assignments_'.$rid, $newrec)){
        		error(get_string('errorinaddingtheme','block_mou_school'));
        	}
		}

		// print_r($usermarks); exit(0);
		foreach ($usermarks as $fieldname => $usermark)  {
			$id = 1;
			$ids = explode('_', $fieldname);
			if ($ids[0] == 0 && empty($usermark->mark) &&  empty($usermark->mark2))	continue;
			if ($ids[0] > 0)	{
				$id = 	$ids[0];
				$currentusermark = get_record_select('monit_school_marks_'.$rid, "id = $id", 'id, userid, scheduleid, mark, mark2');
				/*
				if (empty($usermark->mark) &&  empty($usermark->mark2) )  {
					delete_records('monit_school_marks_'.$rid, 'id', $id);
				} else {
				*/	
					$mark = $mark2 = 0;
					if (isset($usermark->mark))	{
						$mark = $usermark->mark;
					}
					if (isset($usermark->mark2))	{
						$mark2 = $usermark->mark2;
					}

					$currentusermark->mark  = $mark;
					$currentusermark->mark2 = $mark2;

	       			if (!update_record('monit_school_marks_'.$rid, $currentusermark))	{
	       				//print_r($currentusermark);
						error(get_string('errorinupdatingmark','block_mou_school'), $redirlink);
		  			}
		  		// }
			} else {
				$mark = $mark2 = 0;
				if (isset($usermark->mark))	{
					$mark = $usermark->mark;
				}
				if (isset($usermark->mark2))	{
					$mark2 = $usermark->mark2;
				}
				
        		$newrec->userid = $ids[1];
        		$newrec->scheduleid = $jid;
        		$newrec->mark = $mark;
        		$newrec->mark2 = $mark2;
        		$newrec->datedone = date('Y-m-d');
		        if (!insert_record('monit_school_marks_'.$rid, $newrec))	{
					error(get_string('errorinaddingmark','block_mou_school'), $redirlink);
	    	    }
	    	}
		}
		
		// print_r($userattends); // exit(0);
		$otmetki = array('Н','н','У','у','О','о','-');		
		foreach ($userattends as $fieldname => $userattend)  {
			$id = 1;
			$ids = explode('_', $fieldname);
			if (empty($userattend->reason) && $ids[0] == 0)	continue;
		
			if ($ids[0] > 0)	{
				$id = 	$ids[0];
				$currentusermark = get_record_select('monit_school_marks_'.$rid, "id = $id", 'id, userid, scheduleid, mark, mark2');
				if (empty($userattend->reason))  {
					delete_records('monit_school_attendance_'.$rid, 'id', $id);
				} else {
					if (in_array($userattend->reason, $otmetki)) {
	            		if (!set_field('monit_school_attendance_'.$rid, 'reason', "$userattend->reason", 'id', $id)) {
		       			//	print_r($currentusermark);
							error(get_string('errorinupdatingreason','block_mou_school'), $redirlink);
			  			}
			  		}	
		  		}
			} else {
				
				$reason = '';
				if (isset($userattend->reason))	{
					$reason = $userattend->reason;
				}
				if (in_array($userattend->reason, $otmetki)) {
	        		$newrec->userid = $ids[1];
	        		$newrec->scheduleid = $jid;
	        		$newrec->reason = "$reason";
	            	if (!record_exists_mou('monit_school_attendance_'.$rid, 'userid', $newrec->userid, 'scheduleid', $newrec->scheduleid))	{
				        if (!insert_record('monit_school_attendance_'.$rid, $newrec))	{
							error(get_string('errorinaddingreason','block_mou_school'), $redirlink);
			    	    }
			    	}    
		    	}    
	    	}
	    }	
			
		redirect($redirlink, get_string('succesavedata','block_mou_school'), 30);
	}


	$GLDATESTART = array();
	$curyear = date('Y');
	$datestart = $curyear.'-09-01';
	$curyear++;
	$dateend = $curyear.'-05-31';	

 	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	echo '<br>';
	if($strlistclasses = get_record_select('monit_school_class', "id = $gid", 'id, name')){
		echo '<tr><td>'.get_string('class','block_mou_school').':</td><td>';
		echo '<b>'. $strlistclasses->name;
		echo '</td></tr>';			
	}
	
	if($disc = get_record_select('monit_school_class_discipline', "id=$cdid", 'id, disciplineid')){
		if ($strlistpredmets = get_record_select('monit_school_discipline', "id = {$disc->disciplineid}", 'id, name'))	{
			echo '<tr><td>'.get_string('predmet','block_mou_school').':</td><td>';
			echo '<b>'. $strlistpredmets->name;
			echo '</td></tr>';	
		} else {
			 echo '</table>';
    		notice (get_string('classdisciplinesnotfound', 'block_mou_school'), "../class/classdisciplines.php?rid=$rid&amp;yid=$yid&amp;gid=$gid&amp;sid=$sid");
		}
	}

	if ($gid != 0 && $cdid != 0)		{

		echo '<tr><td>'.get_string('teacher','block_mou_school').':</td><td>';		

		$classdiscipline = get_record_sql("SELECT id, schoolsubgroupid, disciplineid, teacherid FROM {$CFG->prefix}monit_school_class_discipline WHERE id=$cdid");		
		if ($classdiscipline->teacherid > 0)	{
			$user = get_record_sql("SELECT id, firstname, lastname FROM {$CFG->prefix}user
									WHERE id={$classdiscipline->teacherid}");
			echo '<b>'.$user->lastname.' '.$user->firstname.'</b>';
		} else {
			print_string('notassignteacher', 'block_mou_school');
		}
		echo '</td></tr>';
				
		$schedule = get_record_select('monit_school_class_schedule_'.$rid, "id = $jid", 'id, lessonid, datestart');
			
		$rusformatdate = convert_date($schedule->datestart, 'en', 'ru');

		echo '<tr><td>'.get_string('lessondate','block_mou_school').':</td><td>';
		echo '<b>'. $rusformatdate.' '.get_string('g','block_mou_school');
		echo '</td></tr>';

		$planname = $unitname = $themename = '-';
		if ($schedule->lessonid)	{
			$theme = get_record_select("monit_school_discipline_lesson_$rid", "id=$schedule->lessonid", 'id, unitid, name, number');
			$themename = $theme->number.'. '.$theme->name;
			
			$unit = get_record_select("monit_school_discipline_unit", "id = $theme->unitid", 'id, planid, name, number');
			$unitname = $unit->number.'. '.$unit->name; 

			$plan = get_record_select("monit_school_discipline_plan", "id = $unit->planid", 'id, name');
			$planname = $plan->name; 
		}
		
  	    echo '<tr><td>'.get_string('planplan','block_mou_school').':</td><td>';
		echo $planname; 
  		echo '</td></tr>';

  	    echo '<tr><td>'.get_string('unitplan','block_mou_school').':</td><td>';
		echo $unitname; 
  		echo '</td></tr>';

		echo '<tr> <td>'.get_string('lessonplan', 'block_mou_school').': </td><td>';
		echo $themename; 
  		echo '</td></tr></table>';
	
		if ($termid != 0)	{	
		    
			//	print_heading(get_string('classpupils','block_mou_school'),'center',2);
			
			$table = table_journal($rid, $sid, $yid, $gid, $cdid, $termid, $jid);
			
			if ($jid != 0)	{		
				echo  '<form name="marks" method="post" action="setmarks.php">';
				echo  '<input type="hidden" name="rid" value="' . $rid . '">';
				echo  '<input type="hidden" name="sid" value="' . $sid . '">';
				echo  '<input type="hidden" name="yid" value="' . $yid . '">';
				echo  '<input type="hidden" name="gid" value="' . $gid . '">';
				echo  '<input type="hidden" name="cdid" value="' . $cdid . '">';
				echo  '<input type="hidden" name="tid" value="' . $termid . '">';
				echo  '<input type="hidden" name="jid" value="' . $jid . '">';
				echo  '<input type="hidden" name="tyid" value="' . $tyid . '">';
				print_color_table($table);
				echo  '<div align="center">';
				echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '">';
				echo  '</div></form>';
			} else {
				print_color_table($table);			
			}
		
		/*
			if ($table2 = table_journal_outpupil($rid, $sid, $yid, $gid, $cdid, $termid, $jid))	{
				print_heading(get_string('outed','block_mou_school'),'center',2);
				print_color_table($table2);	
			}
		*/
		    print_simple_box_start('center', '30%', 'white');
		   	print_heading('Перечень отметок в журнале', 'center', 4);
			
			echo '<small>Минимальный балл: <b>1</b><br>';
			echo 'Максимальный балл: <b>5</b><br>';
			echo 'Пропуск по неуважительной причине: <b>Н</b> или <b>н</b><br>';
			echo 'Пропуск по уважительной причине: <b>У</b> или <b>у</b><br>';
			echo 'Опоздание на урок: <b>О</b> или <b>о</b></br>';
			echo 'Отсутствие на уроке: <b>-</b></small><br><br>';
			echo '<i>Замечания: <br> - для удаления оценки в журнале введите 0(ноль);<br> - для удаления посещаемости очистите поле (т.е. удалите букву).</i>';
			
			
	  		print_simple_box_end();		
		}
	} else {
		echo '</table>';
	}


    print_footer();

    
function table_journal($rid, $sid, $yid, $gid, $cdid, $termid, $jid)
{
	global $CFG, $classdiscipline;
	
	$table->head  = array (get_string('pupils', 'block_mou_school'));
	$table->align = array ('left');
	$table->size = array ('20%');
	$table->columnwidth = array (20);
	
	$table->head[] = get_string('mark1','block_mou_school');	
	$table->align[] = 'center';
	$table->size[] = '5%';
	$table->columnwidth[] = 10; 

			  
	$table->head[] = get_string('mark2','block_mou_school');	
	$table->align[] = 'center';
	$table->size[] = '5%';
	$table->columnwidth[] = 10; 
	
	$table->head[] = get_string('attendance','block_mou_school');	
	$table->align[] = 'center';
	$table->size[] = '5%';
	$table->columnwidth[] = 10; 
	 	
    $table->class = 'moutable';
   	$table->width = '60%';
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
		foreach ($students as $student) {
			
			if ($classdiscipline->schoolsubgroupid)	{
				if (!record_exists_select_mou('monit_school_subgroup_pupil', "schoolid = $sid AND userid = {$student->id} AND classdisciplineid = $cdid")) {
					continue;
				}	
			}
			
 			$pupilcard = 'pupilcard.php';
			
			$tabledata = array("<div align=left><strong><a href=\"{$CFG->wwwroot}/blocks/mou_school/class/{$pupilcard}?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid={$student->id}\">".fullname($student)."</a></strong></div>");
			
			$allmarks = $allmarks2 = 0;
			$countmarks = $countmarks2 = 0;

			$mark1 = $mark2 = $atted = '';
			
		    if ($attendance = get_record_select('monit_school_attendance_'.$rid,  "userid = $student->id AND scheduleid = $jid", 'id, reason')) {
		    	$attendanceid = $attendance->id;
		    	$atted = $attendance->reason;
		    } else {
		    	$attendanceid = 0;
		    }

			if ($markstuder = get_record_select('monit_school_marks_'.$rid, "userid=$student->id AND scheduleid=$jid" ,'id, mark, mark2')) 	{
				$markstuderid = $markstuder->id;
				if (!empty($markstuder->mark))	{
					$mark1 = $markstuder->mark;	
				}
				
				if (!empty($markstuder->mark2))	{
					$mark2 = $markstuder->mark2;	
				}

			} else {
				$markstuderid = 0;
			}


/*				
			if (!empty($markstuder->mark))	{
				
			} else	if (!empty($markstuder->mark2))	{
				
			} elseif (!empty($attendance->reason))	{
				
			} 
*/					

			$tabledata[] = "<input type=text  name=m1Z{$markstuderid}_{$student->id} size=2 MAXLENGTH=2 value=\"$mark1\">";
			$tabledata[] = "<input type=text  name=m2Z{$markstuderid}_{$student->id} size=2 MAXLENGTH=2 value=\"$mark2\">";
			$tabledata[] = "<input type=text  name=a1Z{$attendanceid}_{$student->id} size=2 MAXLENGTH=2 value=\"$atted\">";
			
			$table->data[] = $tabledata;				
		}		
	}			
    return $table;
}

/*
function table_journal_outpupil($rid, $sid, $yid, $gid, $cdid, $termid, $jid = 0)
{
	global $CFG ;

	$table->head  = array (get_string('pupils', 'block_mou_school'));
	$table->align = array ('left');
	$table->size = array ('20%');
	$table->columnwidth = array (20);
	
	$school_term = get_record('monit_school_term', 'id', $termid); // приходит с URL
	$strsql = "SELECT s.id, s.datestart FROM {$CFG->prefix}monit_school_class_schedule_$rid s
              LEFT JOIN {$CFG->prefix}monit_school_movepupil m ON s.classid = m.classinid
              WHERE  m.classinid = $gid AND m.schoolinid = s.schoolid AND m.rayoninid = $rid AND m.dateout >= '$school_term->datestart' AND m.dateout <= '$school_term->dateend' 
			  ORDER BY dateout";	  
//	$title = 'df;ogjdojgh';	
 
	if ($shedules = get_records_sql($strsql))	{
		 foreach ($shedules as $shed)	{
			 $date = explode('-', $shed->datestart);
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
				    $attendance = get_record('monit_school_attendance_'.$rid,  'userid', $student->id, 'scheduleid', $shed->id);
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

					if ($shed->id == $jid)	{
						$tabledata[] = "<input type=text  name=fld_{$shed->id}_{$student->id} size=2 MAXLENGTH=2 value=\"$table_td\">".' '.
									   "<input type=text  name=flt_{$shed->id}_{$student->id} size=2 MAXLENGTH=2 value=\"$table_td2\">";

					}	else {
						$tabledata[] = $table_td;						
					}				

					
					
				}				
			}
			if ($countmarks != 0)	{
				$division = number_format($allmarks/$countmarks, 2, ',', '');
				$tabledata[] = $division; 
				$tabledata[] = round($division); // !!!
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
?>