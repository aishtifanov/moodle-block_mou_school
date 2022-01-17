<?php // $Id: totalmarks.php,v 1.32 2012/05/30 07:43:53 shtifanov Exp $


/*

ALTER TABLE `mou`.`mdl_monit_school_marks_totals_year` ADD COLUMN `markexam` SMALLINT(6) UNSIGNED NOT NULL DEFAULT 0 AFTER `avgmark`,
 ADD COLUMN `markitog` SMALLINT(6) UNSIGNED NOT NULL DEFAULT 0 AFTER `markexam`;
 
*/
    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_school.php');
	require_once('../authbase.inc.php');    
    
	$cdid 	= optional_param('cdid', 0, PARAM_INT);	  // class_discipline (subgroup) id
    $gid 	= optional_param('gid',  0, PARAM_INT);   // Class id
    $period = optional_param('p', 	 'day'); // Period time: day, week, month, year, exam, itog
    $level  = optional_param('level', ''); // Period time: day, week, month, year, exam, itog
    $termid	= optional_param('termid',  0, PARAM_INT);   // Term id
    $tid	= optional_param('tid',  0, PARAM_INT);   // Term id
    
	$view_capability = has_capability('block/mou_school:viewjournalclass', $context);
	$edit_capability = has_capability('block/mou_school:editjournalclass', $context);

    switch ($action)	{
    	case 'excel':  $table = table_total_marks($rid, $sid, $yid, $gid, $cdid, $termid, true);
                       // echo $table;  
					   // print_r($table);   
					   print_table_to_excel($table);
        			   exit();
		case 'setstandart':	set_avg_marks_for_all_year($yid, $rid, $sid, $cdid, $gid);
		break;
	}

    $currenttab = 'totalmarks';
    include('tabsjrnl.php');

    if ($level == 'year')   {
     	$class = get_record_select('monit_school_class', "id=$gid", 'id, parallelnum');
        $class_termtype = get_record_select('monit_school_class_termtype', "schoolid=$sid AND parallelnum = {$class->parallelnum}", 'id, termtypeid');
        $school_terms = get_records_select('monit_school_term', "schoolid = $sid AND  termtypeid = {$class_termtype->termtypeid}", '', 'id, name, datestart, dateend');	  
        $studentsql = "SELECT userid FROM {$CFG->prefix}monit_school_pupil_card WHERE classid = $gid";
        
        if($students = get_records_sql($studentsql))	{
    		foreach ($students as $student) 	{
                set_total_marks_for_all_year($yid, $sid, $cdid, $student->userid, $school_terms, true);
            }
        }                
    } 
    
    if ($recs = data_submitted())  {
    	// print_r($recs); exit(0);
    	$edit_capability_class = false;
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
    	
        if ($period != 'year' && $period != 'exam' && $period != 'itog') {
		     set_avg_marks_term($yid, $rid, $sid, $cdid, $gid, $termid);
        }  
		    	
		$redirlink = "totalmarks.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;cdid=$cdid&amp;gid=$gid&amp;termid=$termid";
		// print_r($recs); echo '<hr>';
        // notice(get_string('succesavedata','block_monitoring'), $redirlink);
		foreach($recs as $fieldname => $mark)	{
	
    			
			$mask = substr($fieldname, 0, 4);
			if ($mask == 'fld_')	{
	           	$ids = explode('_', $fieldname);
	           	$termid = $ids[1];
	           	$userid = $ids[2];
	           	$mark = trim($mark);
                
                $strselect = "classdisciplineid = $cdid AND userid = $userid";
                $marktotal = get_record_select('monit_school_marks_totals_year', $strselect, 'id');
    			$rec->yearid = $yid;	
    			$rec->classdisciplineid = $cdid;
    			$rec->userid = $userid;			
    			$rec->avgmark = 0;
                $rec->mark = 0;
                $rec->markexam = 0;
                $rec->markitog = 0;

                switch($period) {
                    case 'year':
                        		// echo  $strselect .'<br>';
                        		if ($marktotal)	{
                        			set_field('monit_school_marks_totals_year', 'mark', $mark, 'id', $marktotal->id);
                        			// set_field('monit_school_marks_totals_year', 'avgmark', $avgmark,  'id', $marktotal->id);
                        		} else {		
          			    			$rec->mark = $mark;
                        			if (!insert_record('monit_school_marks_totals_year', $rec))	{
                        				print_r($rec);
                        				error(get_string('errorinaddingmarks_totals_year','block_mou_scholl'), $redirlink);
                        			}						
                        		}				
                    break;            

                    case 'exam':
                        		if ($marktotal)	{
                        			set_field('monit_school_marks_totals_year', 'markexam', $mark, 'id', $marktotal->id);
                        		} else {		
                        			$rec->markexam = $mark;
                        			if (!insert_record('monit_school_marks_totals_year', $rec))	{
                        				print_r($rec);
                        				error(get_string('errorinaddingmarks_totals_year','block_mou_scholl'), $redirlink);
                        			}						
                        		}				
                    break;    

                    case 'itog':
                        		if ($marktotal)	{
                        			set_field('monit_school_marks_totals_year', 'markitog', $mark, 'id', $marktotal->id);
                        			// set_field('monit_school_marks_totals_year', 'avgmark', $avgmark,  'id', $marktotal->id);
                        		} else {		
                        			$rec->markitog = $mark;
                       			
                        			if (!insert_record('monit_school_marks_totals_year', $rec))	{
                        				print_r($rec);
                        				error(get_string('errorinaddingmarks_totals_year','block_mou_scholl'), $redirlink);
                        			}						
                        		}				
                    break;
                    default:
                	           	if (empty($mark))	{
                	            	if (record_exists_mou('monit_school_marks_totals_term', 'userid', $userid, 'classdisciplineid', $cdid, 'termid', $termid))	{
                	            		delete_records('monit_school_marks_totals_term', 'userid', $userid, 'classdisciplineid', $cdid, 'termid', $termid);
                	            	}	
                	           	} else if (is_numeric($mark))	{
                	            	if (record_exists_mou('monit_school_marks_totals_term', 'userid', $userid, 'termid', $termid, 'classdisciplineid', $cdid))	{
                	            		if ($mark >= 1 && $mark <= 5)	{
                	            			set_field_select('monit_school_marks_totals_term', 'mark', $mark, "userid = $userid AND classdisciplineid = $cdid AND termid=$termid");
                						} else {
                							notify (get_string('notvalidotmetka', 'block_mou_school', $mark));
                						}    
                	            	} else {
                	            		if ($mark >= 1 && $mark <= 5)	{ 
                	            			$newrec->schoolid =$sid;
                	            			$newrec->classdisciplineid = $cdid;
                		            		$newrec->userid = $userid;
                		            		$newrec->termid = $termid;
                		            		$newrec->mark = $mark;
                		            		// $newrec->avgmark = $mark;
                		            		
                					        if (!$lastmark = insert_record('monit_school_marks_totals_term', $newrec))	{
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
		
        if ($period != 'year' && $period != 'exam' && $period != 'itog') {
    	 	$class = get_record_select('monit_school_class', "id=$gid", 'id, parallelnum');
    	    $class_termtype = get_record_select('monit_school_class_termtype', "schoolid=$sid AND parallelnum = {$class->parallelnum}", 'id, termtypeid');
    	    $school_terms = get_records_select('monit_school_term', "schoolid = $sid AND  termtypeid = {$class_termtype->termtypeid}", '', 'id, name');	  
            
    	    $studentsql = "SELECT userid FROM {$CFG->prefix}monit_school_pupil_card WHERE classid = $gid";
    		if($students = get_records_sql($studentsql)) 	{
    			foreach ($students as $student) 	{	
    				set_total_marks_for_all_year($yid, $sid, $cdid, $student->userid, $school_terms);
    			}
    		}
        }    	
        
        notify (get_string( 'succesavedata', 'block_mou_school'), 'green');				
	}

	if ($view_capability)	{

	 	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
    	$strlistclasses =  listbox_class_role("totalmarks.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=", $rid, $sid, $yid, $gid);
    	echo $strlistclasses;
    	$strlistpredmets =listbox_discipline_class_role("totalmarks.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;cdid=", $sid, $yid, $gid, $cdid);
    	echo $strlistpredmets;

		// listbox_class("totalmarks.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=", $rid, $sid, $yid, $gid);	
		// listbox_discipline_class("totalmarks.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;cdid=", $sid, $yid, $gid, $cdid);
		if ($classdiscipline = get_record_sql("SELECT id, schoolsubgroupid, disciplineid, teacherid FROM {$CFG->prefix}monit_school_class_discipline WHERE id=$cdid")) 	
		if ($classdiscipline->teacherid > 0)	{
			$user = get_record_sql("SELECT id, firstname, lastname FROM {$CFG->prefix}user WHERE id={$classdiscipline->teacherid}");
			$str = get_string('teacher','block_mou_school');
			echo '<tr><td>'.get_string('teacher','block_mou_school').':</td><td>';
			echo $user->lastname.' '.$user->firstname;
			echo '</td></tr>';
		} else {
			echo '</table>';
			notice(get_string('notassignteacher', 'block_mou_school'), "../class/classdisciplines.php?rid=$rid&amp;yid=$yid&amp;gid=$gid&amp;sid=$sid");
		}
		echo '</table>';
		
		if ($gid != 0 && $cdid != 0)		{
			
			$context_class = get_context_instance(CONTEXT_CLASS, $gid);
			$edit_capability_class = has_capability('block/mou_school:editjournalclass', $context_class);

			$ctxdiscipline = get_context_instance(CONTEXT_DISCIPLINE, $cdid);
			$edit_capability_discipline = has_capability('block/mou_school:editjournalclass', $ctxdiscipline);
           
            if ($tid != 0)  {
                if ($edit_capability || $edit_capability_class || $edit_capability_discipline)	{
                    set_avg_marks_term($yid, $rid, $sid, $cdid, $gid, $tid);
                }     
            }

			$table = table_total_marks($rid, $sid, $yid, $gid, $cdid, $termid);
			
			if (($termid != 0 || $period == 'year' || $period == 'exam' || $period == 'itog') && 
                 ($edit_capability || $edit_capability_class || $edit_capability_discipline))	{		
				echo  '<form name="marks" method="post" action="totalmarks.php">';
				echo  '<input type="hidden" name="rid" value="' . $rid . '">';
				echo  '<input type="hidden" name="sid" value="' . $sid . '">';
				echo  '<input type="hidden" name="yid" value="' . $yid . '">';
				echo  '<input type="hidden" name="gid" value="' . $gid . '">';
				echo  '<input type="hidden" name="cdid" value="' . $cdid . '">';
				echo  '<input type="hidden" name="termid" value="' . $termid . '">';
                echo  '<input type="hidden" name="p" value="' . $period . '">';
                echo  '<div align="center">';
				echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '">';
				echo  '</div>';
				print_color_table($table);
				echo  '<div align="center">';
				echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '">';
				echo  '</div></form>';
			} else {
				print_color_table($table);			
			}

			if ($edit_capability || $edit_capability_class || $edit_capability_discipline)	{
				$options = array('mode' => 'new' , 'rid' => $rid, 'sid' => $sid, 'yid' => $yid);
				echo '<table align="center" border=0><tr><td>';
				$options = array( 'rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'cdid' => $cdid, 'gid' => $gid, 'action' => 'setstandart');	
			 	// print_single_button("totalmarks.php", $options, get_string('checkandcreatmark', 'block_mou_school'));
				echo '</td><td>';
				$options = array( 'rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'cdid' => $cdid, 'gid' => $gid, 'action' => 'excel');	 	
			    print_single_button("totalmarks.php", $options, get_string("downloadexcel"));
				echo '</td></tr></table>';
			}	
		}	
	} else {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}
	
    print_footer();



function table_total_marks($rid, $sid, $yid, $gid, $cdid, $termid=0, $to_excel=false)
{
	global $CFG, $edit_capability, $edit_capability_class, $edit_capability_discipline, $period;

    $stryear = get_string('markyear', 'block_mou_school');
    $strexam = get_string('markexam', 'block_mou_school');
    $stritog = get_string('markitog', 'block_mou_school');        
  		
 	$class = get_record_select('monit_school_class', "id=$gid", 'id, name, parallelnum');
    $class_termtype = get_record_select('monit_school_class_termtype', "schoolid=$sid AND parallelnum = {$class->parallelnum}", 'id, termtypeid');
    $school_terms = get_records_select('monit_school_term', "schoolid = $sid AND  termtypeid = {$class_termtype->termtypeid}", '', 'id, name, datestart, dateend');	  
    $classdiscipline = get_record_sql("SELECT id, schoolsubgroupid, disciplineid, teacherid, name FROM {$CFG->prefix}monit_school_class_discipline WHERE id=$cdid");
            
	$colspan = count($school_terms);

	$redirlink = "totalmarks.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;cdid=$cdid";
    	
	if ($to_excel)	{
		$table->head  = array (get_string('pupils', 'block_mou_school'));
		$table->align = array ('left');
	    $table->size = array ('20%');
		$table->columnwidth = array (31);
	} else {
		if ($edit_capability || $edit_capability_class || $edit_capability_discipline)	{
		 	$stryear = "<div align=center><strong><a href=\"$redirlink&amp;p=year\">".$stryear."</a></strong></div>";
            $strexam = "<div align=center><strong><a href=\"$redirlink&amp;p=exam\">".$strexam."</a></strong></div>";
            $stritog = "<div align=center><strong><a href=\"$redirlink&amp;p=itog\">".$stritog."</a></strong></div>";            
		} 	
		$table->dblhead->head1  = array (get_string('pupils', 'block_mou_school'), get_string('studyperiods', 'block_mou_school'), $stryear, $strexam, $stritog);
		$table->dblhead->span1  = array ("rowspan=2", "colspan=$colspan", "rowspan=2", "rowspan=2", "rowspan=2");
		$table->align = array ('left', 'center', 'center', 'center', 'center');
	}	

	$shedules = array();
	foreach ($school_terms as $school_term) {
		if ($edit_capability || $edit_capability_class || $edit_capability_discipline)	{
		 	$edit_month = "<div align=center><strong><a href=\"$redirlink&amp;termid={$school_term->id}\">".$school_term->name."</a></strong></div>";
		} else {	
		 	$edit_month = $school_term->name;
		}	
		if ($to_excel)	{
			$table->head[]  = $edit_month;
            $table->align[] = 'center';
			$table->columnwidth[] = 12;
		} else {	
			$table->dblhead->head2[]  = $edit_month;
			$table->align[] = 'center';
		}		
	}
	
    $table->class = 'moutable';
   	$table->width = '90%';
   	if ($to_excel)	{
   	    
		$table->head[]  = $stryear;
        $table->align[] = 'center';
		$table->columnwidth[] = 10;
        
		$table->head[]  = $strexam;
        $table->align[] = 'center';
		$table->columnwidth[] = 15;
        
		$table->head[]  = $stritog;
        $table->align[] = 'center';
		$table->columnwidth[] = 11;
      
		$table->titlesrows = array(20,30,40,20,20,20);   	
	    $table->titles = array();
	    $table->titles[] = get_string('totalmarks', 'block_mou_school');
	    $rayon = get_record_select('monit_rayon', "id=$rid", 'id, name');
	    $table->titles[] = $rayon->name;
	    $school = get_record_select('monit_school', "id=$sid", 'id, name');
	    $table->titles[] = $school->name; 
	    $table->titles[] = get_string('class', 'block_mou_school'). ':'.$class->name;
	    
		if ($classdiscipline && $classdiscipline->teacherid > 0)	{
			$user = get_record_sql("SELECT id, firstname, lastname FROM {$CFG->prefix}user WHERE id={$classdiscipline->teacherid}");
			$str = get_string('teacher','block_mou_school');
			$strteacher= get_string('teacher','block_mou_school').':';
			$strteacher .= $user->lastname.' '.$user->firstname;
			
		} else {
			$strteacher  = get_string('notassignteacher', 'block_mou_school');
		}
		$table->titles[] = get_string('predmet', 'block_mou_school'). ':'. $classdiscipline->name; 		
		$table->titles[] = $strteacher;
	    
	    $table->worksheetname = 'totalmarks_'.$sid.'_'.$gid;
    	$table->downloadfilename = $table->worksheetname ;
	}    
    
	$tabledata = array();
	
    $studentsql = "SELECT u.id, u.username, u.firstname, u.lastname
                            FROM {$CFG->prefix}user u
                       LEFT JOIN {$CFG->prefix}monit_school_pupil_card m ON m.userid = u.id 
					   WHERE classid = $gid AND u.deleted = 0 AND u.confirmed = 1
					   ORDER BY u.lastname, u.firstname";

	if($students = get_records_sql($studentsql)) 	{
		
		foreach ($students as $student) 	{	
		  	if ($classdiscipline->schoolsubgroupid)	{
				if (!record_exists_select ('monit_school_subgroup_pupil', "schoolid = $sid AND userid = {$student->id} AND classdisciplineid = $cdid")) {
					continue;
				}	
			}
			

					
			$tabledata = array("<div align=left><strong><a href=\"{$CFG->wwwroot}/blocks/mou_school/class/pupilcard.php?mode=4&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid={$student->id}\">".fullname($student)."</a></strong></div>");		

			if($school_terms)  {
				foreach ($school_terms as $school_term) {
					
					$avgmark = $mark = '-';
					if ($markstuder = get_record_sql("SELECT id, mark, avgmark FROM {$CFG->prefix}monit_school_marks_totals_term
													  WHERE userid={$student->id} AND classdisciplineid=$cdid and termid={$school_term->id}"))	{
						$avgmark = number_format($markstuder->avgmark, 2, ',', '');
						$mark = $markstuder->mark;
					}
					
					if ($school_term->id == $termid && ($edit_capability || $edit_capability_class || $edit_capability_discipline))	{
	  					$tabledata[] = "<input type=text  name=fld_{$school_term->id}_{$student->id} size=2 MAXLENGTH=2 value=\"$mark\"> ($avgmark)";
					}  else {
						$tabledata[] = '<b>'.$mark.'</b> ('. $avgmark . ')';
					}
				}
			}		

			$strselect = "classdisciplineid = $cdid AND userid = $student->id";
			if ($marktotal = get_record_select('monit_school_marks_totals_year', $strselect, 'id, mark, avgmark, markexam, markitog'))	{
				$stravg = number_format($marktotal->avgmark, 2, ',', '');
                $mark = $marktotal->mark;
                if ($period == 'year' && ($edit_capability || $edit_capability_class || $edit_capability_discipline))  {
                    $tabledata[] = "<input type=text  name=fld_year_{$student->id} size=2 MAXLENGTH=2 value=\"$mark\"> ($stravg)";
                } else {
                    $tabledata[] = '<b>'.$mark.'</b> ('. $stravg . ')';    
                }
                $mark = $marktotal->markexam;
                if ($period == 'exam' && ($edit_capability || $edit_capability_class || $edit_capability_discipline))  {
                    $tabledata[] = "<input type=text  name=fld_exam_{$student->id} size=2 MAXLENGTH=2 value=\"$mark\"> ";
                } else {
                    $tabledata[] = '<b>'.$mark.'</b>';    
                }
                $mark = $marktotal->markitog;
                if ($period == 'itog' && ($edit_capability || $edit_capability_class || $edit_capability_discipline))  {
                    $tabledata[] = "<input type=text  name=fld_itog_{$student->id} size=2 MAXLENGTH=2 value=\"$mark\">";
                } else {
                    $tabledata[] = '<b>'.$mark.'</b>';    
                }
			} else {
                if ($period == 'year' && ($edit_capability || $edit_capability_class || $edit_capability_discipline))  {
                    $tabledata[] = "<input type=text  name=fld_year_{$student->id} size=2 MAXLENGTH=2 value=\"0\"> (0,00)";
                } else {
				    $tabledata[] = '-';
                }    
                if ($period == 'exam' && ($edit_capability || $edit_capability_class || $edit_capability_discipline))  {
                    $tabledata[] = "<input type=text  name=fld_exam_{$student->id} size=2 MAXLENGTH=2 value=\"0\">";
                } else {
				    $tabledata[] = '-';
                }    
                if ($period == 'itog' && ($edit_capability || $edit_capability_class || $edit_capability_discipline))  {
                    $tabledata[] = "<input type=text  name=fld_itog_{$student->id} size=2 MAXLENGTH=2 value=\"0\">";
                } else {
				    $tabledata[] = '-';
                }    
			}
				
			$table->data[] = $tabledata;
		}

        if (!$to_excel)	{
        	$tabledata = array("");		
        	if($school_terms)  {
        		foreach ($school_terms as $school_term) {
        		     if ($edit_capability || $edit_capability_class || $edit_capability_discipline)	{
             			 $title = get_string('setavgmarkdiscterm', 'block_mou_school');
            			 $strlinkupdate = "<a title=\"$title\" href=\"totalmarks.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;cdid=$cdid&amp;gid=$gid&amp;tid={$school_term->id}\">";
            			 $strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/tick_green_big.gif\" alt=\"$title\" /></a>&nbsp;";
             	         $tabledata[] = $strlinkupdate;
                     }   
                }
        		 $title = get_string('setavgmarkdiscterm', 'block_mou_school');
        		 $strlinkupdate = "<a title=\"$title\" href=\"totalmarks.php?level=year&rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;cdid=$cdid&amp;gid=$gid\">";
        		 $strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/tick_green_big.gif\" alt=\"$title\" /></a>&nbsp;";
                 $tabledata[] = $strlinkupdate;
                
                $table->data[] = $tabledata;
            }    
        }
   }
					
    return $table;
}

function set_avg_marks_term($yid, $rid, $sid, $cdid, $gid, $termid)
{
   	global $CFG;
	
    $school_term = get_record_select('monit_school_term', "id = $termid", 'id, name, datestart, dateend');	  
    
	$redirlink = "totalmarks.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;cdid=$cdid";
	
    $studentsql = "SELECT userid FROM {$CFG->prefix}monit_school_pupil_card WHERE classid = $gid";
    
    if($students = get_records_sql($studentsql))	{
	
		foreach ($students as $student) 	{
			
			$allmarks = $countmarks = 0;
			$strsql = "SELECT id FROM {$CFG->prefix}monit_school_class_schedule_$rid
        			   WHERE classdisciplineid=$cdid AND datestart >= '$school_term->datestart' AND datestart <= '$school_term->dateend'";	  

			if ($shedules = get_records_sql($strsql))	{
				foreach ($shedules as $shed)	{					
					if ($markstuder = get_record_select('monit_school_marks_'.$rid, "userid=$student->userid AND scheduleid=$shed->id", 'id, mark, mark2'))	{
						if (!empty($markstuder->mark))	{
							$allmarks += $markstuder->mark;
							$countmarks++;
						}
						if (!empty($markstuder->mark2))	{
							$allmarks += $markstuder->mark2;
							$countmarks++;
						}
					}
				}		 
			}
			
			if ($countmarks != 0)	{
				$avgmark = $allmarks/$countmarks;
				$division = round($avgmark);
				$strselect = "userid= $student->userid AND classdisciplineid=$cdid AND termid=$termid"; 
				if ($markrecord = get_record_select('monit_school_marks_totals_term', $strselect, 'id'))	{
					set_field('monit_school_marks_totals_term', 'mark',    $division, 'id', $markrecord->id);
					set_field('monit_school_marks_totals_term', 'avgmark', $avgmark, 'id', $markrecord->id);
				} else {	
					$rec->schoolid = $sid;
					$rec->userid = $student->userid;
					$rec->classdisciplineid = $cdid;
					$rec->termid = $termid;	
					$rec->mark = $division;
					$rec->avgmark = $avgmark;

					if (!insert_record('monit_school_marks_totals_term', $rec))	{
						print_r($rec);
						error(get_string('errorinaddingunit','block_mou_scholl'), $redirlink);
					}						
				}					
			} 	
	     }	
	 }
}    


function set_avg_marks_for_all_year($yid, $rid, $sid, $cdid, $gid)
{
	global $CFG;
	
 	$class = get_record_select('monit_school_class', "id=$gid", 'id, parallelnum');
    $class_termtype = get_record_select('monit_school_class_termtype', "schoolid=$sid AND parallelnum = {$class->parallelnum}", 'id, termtypeid');
    $school_terms = get_records_select('monit_school_term', "schoolid = $sid AND  termtypeid = {$class_termtype->termtypeid}", '', 'id, name, datestart, dateend');	  
    
	$redirlink = "totalmarks.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;cdid=$cdid";
	
    $studentsql = "SELECT userid FROM {$CFG->prefix}monit_school_pupil_card WHERE classid = $gid";
    
    if($students = get_records_sql($studentsql))	{
	
		foreach ($students as $student) 	{
			
			// calculate MARKS FOR ALL TERMS
			foreach ($school_terms as $school_term)	 {
			
				$termid = $school_term->id;

				$allmarks = $countmarks = 0;
				$strsql = "SELECT id FROM {$CFG->prefix}monit_school_class_schedule_$rid
            			   WHERE  classdisciplineid=$cdid AND datestart >= '$school_term->datestart' AND datestart <= '$school_term->dateend'";	  

				if ($shedules = get_records_sql($strsql))	{
					foreach ($shedules as $shed)	{					
						if ($markstuder = get_record_select('monit_school_marks_'.$rid, "userid=$student->userid AND scheduleid=$shed->id", 'id, mark, mark2'))	{
							if (!empty($markstuder->mark))	{
								$allmarks += $markstuder->mark;
								$countmarks++;
							}
							if (!empty($markstuder->mark2))	{
								$allmarks += $markstuder->mark2;
								$countmarks++;
							}
						}
					}		 
				}
				
				if ($countmarks != 0)	{
					$avgmark = $allmarks/$countmarks;
					$division = round($avgmark);
					$strselect = "userid= $student->userid AND classdisciplineid=$cdid AND termid=$termid"; 
					if ($markrecord = get_record_select('monit_school_marks_totals_term', $strselect, 'id'))	{
						set_field('monit_school_marks_totals_term', 'mark',    $division, 'id', $markrecord->id);
						set_field('monit_school_marks_totals_term', 'avgmark', $avgmark, 'id', $markrecord->id);
					} else {	
						$rec->schoolid = $sid;
						$rec->userid = $student->userid;
						$rec->classdisciplineid = $cdid;
						$rec->termid = $termid;	
						$rec->mark = $division;
						$rec->avgmark = $avgmark;

						if (!insert_record('monit_school_marks_totals_term', $rec))	{
							print_r($rec);
							error(get_string('errorinaddingunit','block_mou_scholl'), $redirlink);
						}						
					}					
				} 	
			}
			
			set_total_marks_for_all_year($yid, $sid, $cdid, $student->userid, $school_terms);
	     }	
	 }
}


function set_total_marks_for_all_year($yid, $sid, $cdid, $userid, $school_terms, $updatemarks=false)
{
	global $CFG, $redirlink;
	
	// calculate TOTAL MARKS FOR ALL YEAR
	$allmarks = $countmarks = $allavgmark = $lastmark = 0;
	$count_school_terms = count ($school_terms);
	foreach ($school_terms as $school_term) {
		$strsql = "SELECT id, mark, avgmark FROM {$CFG->prefix}monit_school_marks_totals_term
				  WHERE userid=$userid AND classdisciplineid=$cdid and termid={$school_term->id}";
		// echo $strsql . '<br>';		  
		if ($markstuder = get_record_sql($strsql))	{
			$allmarks   += $markstuder->mark;
			$allavgmark += $markstuder->avgmark;
            $lastmark = $markstuder->mark;
			$countmarks++;
		}
	}		
	
	// echo "allmarks=$allmarks<br>";
	// echo "allavgmark=$allavgmark<br>";
	if ($countmarks > 0 )	{  // $countmarks == $count_school_terms
		$avgmark = $allmarks/$countmarks; // $allavgmark/$countmarks;
		$division = round($avgmark); 
        $drob100 = ($avgmark - floor($avgmark))*100;
        $drob = (int)$drob100;
        
        /*
        echo "drob=$drob<br>";
        echo "userid=$userid<br>";
	    echo "division=$division<br>";
        echo "lastmark=$lastmark<hr>";
        */
        if (46 <= $drob && $drob <= 55) {
            $division = $lastmark;
            // echo "!!!!!!!!!!!!!!!!<hr>";
        } 
        
        /*
        if (abs($lastmark-$division) == 1) {
            if ($lastmark > $division)  {
                $division = $lastmark;
                echo "!!!!!!!!!!!!!!!!<hr>"; 
            }    
        }
        */
        
		$strselect = "classdisciplineid = $cdid AND userid = $userid";
		// echo   $strselect .'<br>';
		if ($marktotal = get_record_select('monit_school_marks_totals_year', $strselect, 'id'))	{
		    if ($updatemarks) {
			    set_field('monit_school_marks_totals_year', 'mark', $division, 'id', $marktotal->id);
            }    
			set_field('monit_school_marks_totals_year', 'avgmark', $avgmark,  'id', $marktotal->id);
		} else {		
			$rec->yearid = $yid;	
			$rec->classdisciplineid = $cdid;
			$rec->userid = $userid;			
			$rec->mark = $division;
			$rec->avgmark = $avgmark;
			
			if (!insert_record('monit_school_marks_totals_year', $rec))	{
				print_r($rec);
				error(get_string('errorinaddingmarks_totals_year','block_mou_scholl'), $redirlink);
			}						
		}				
	}
}	

?>
