<?php // $Id: performance.php,v 1.2 2014/06/03 07:51:03 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_school.php');
   	require_once('../authbase.inc.php');
    require_once('lib_report.php');
   	
    $rpid 	= optional_param('rpid', 0, PARAM_INT);       // Report id
    $termid = optional_param('termid', 0, PARAM_INT);		//Term id
    $gid 	= optional_param('gid', 0, PARAM_INT);			//Class id
	$perid  = optional_param('perid', 0, PARAM_INT);		//Sort reports by period
    $uid 	= optional_param('uid', 0, PARAM_INT);			//User id
    $did 	= optional_param('did', 0, PARAM_INT);			//Predmet id
    $teachid= optional_param('teachid', 0, PARAM_INT);			//Teacherid id
    
    $scriptname = basename($_SERVER['PHP_SELF']);	// echo '<hr>'.basename(me());

    if ($action == 'word') 	{
        switch ($rpid)  {
            case 3: 
                    $fn = 'school_performance_'.$rid.'_'.$sid.'.doc';
                	header("Content-type: application/vnd.ms-word");
                	header("Content-Disposition: attachment; filename=\"{$fn}\"");
                	header("Expires: 0");
                	header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
                	header("Pragma: public");
                    $buffer = table_school_performance($yid, $rid, $sid, $action);
                    
                    print $buffer;
                    exit();
            case 4: 
                    $fn = 'class_performance_'.$rid.'_'.$sid.'_'.$gid.'.doc';
                	header("Content-type: application/vnd.ms-word");
                	header("Content-Disposition: attachment; filename=\"{$fn}\"");
                	header("Expires: 0");
                	header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
                	header("Pragma: public");
                    $table = table_class_performance($yid, $rid, $sid, $gid, $termid);
                    $buffer =  get_shapka_report($table);            
                    $buffer .= '<br />';
            		$buffer .= print_table($table, true);
                    $table = table_class_performance_dop($yid, $rid, $sid, $gid, $termid);
                    $buffer .= '<br />';
                    $buffer .= print_table($table, true);
                    print $buffer;
                    exit();
	       case 5:
                    $fn = 'pupil_performance_'.$rid.'_'.$sid.'_'.$gid.'_'.$uid.'.doc';
                	header("Content-type: application/vnd.ms-word");
                	header("Content-Disposition: attachment; filename=\"{$fn}\"");
                	header("Expires: 0");
                	header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
                	header("Pragma: public");
                    $table = table_pupil_performance($yid, $rid, $sid, $gid, $termid, $uid);
            		$buffer = print_table($table, true);
                    print $buffer;
                    exit();
            case 6: 
                    $fn = 'class_teacher_'.$rid.'_'.$sid.'_'.$gid.'.doc';
                	header("Content-type: application/vnd.ms-word");
                	header("Content-Disposition: attachment; filename=\"{$fn}\"");
                	header("Expires: 0");
                	header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
                	header("Pragma: public");
                    $table = table_class_performance($yid, $rid, $sid, $gid, $termid, true);
            		$buffer = print_table($table, true);
                    print $buffer;
                    exit();
        }                    
	} 


    if ($action == 'excel') 	{
        switch ($rpid)  {
            case 1: $table = table_prereport_for_current_term ($yid, $rid, $sid, $gid);
                    print_table_to_excel($table);
                    exit();

            case 2: $table = table_period_of_report($yid, $rid, $sid, $gid, $perid, $termid);
                    print_table_to_excel($table);
                    exit();

            case 7: 
                /*
                    $fn = 'teacher_predmet'.$rid.'_'.$sid.'_'.$teachid.'_'.$did.'.doc';
                	header("Content-type: application/vnd.ms-word");
                	header("Content-Disposition: attachment; filename=\"{$fn}\"");
                	header("Expires: 0");
                	header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
                	header("Pragma: public");
                */                    
                    $table = table_teacher_predmet($yid, $rid, $sid, $did, $teachid, $termid);
                    print_table_to_excel($table);
                    exit();
            case 11:                    
            		$table = table_itogi ($yid, $rid, $sid, $gid);
                    print_table_to_excel($table);
                    exit();
        }
    }
    
    $currenttab = 'performance';
    include('tabsreports.php');
    
    // notice(get_string('vstadii', 'block_mou_att'), "../index.php?rid=$rid&yid=$yid&sid=$sid");

	if (has_capability('block/mou_school:viewreports', $context))	{
		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		listbox_performance_reports("$scriptname?rid=$rid&yid=$yid&sid=$sid&rpid=", $rid, $sid, $yid, $rpid);
		switch ($rpid){
			case '0':
			break;
	
			case '1':
					listbox_class("$scriptname?rid=$rid&sid=$sid&yid=$yid&rpid=$rpid&gid=", $rid, $sid, $yid, $gid);
					if ($gid != 0)	{
						if ($class = get_record('monit_school_class','id',$gid)){
							$user = get_record_sql("SELECT id, firstname, lastname FROM {$CFG->prefix}user
														WHERE id={$class->teacherid}");
																		
							echo '<tr><td>'.get_string('teacher','block_mou_school').':</td><td>';
							echo $user->lastname.' '.$user->firstname;
							echo '</td></tr>';						
						}
						echo '</table>';
						
						$table = table_prereport_for_current_term ($yid, $rid, $sid, $gid);
						print_color_table($table);
						
						$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'rpid' => $rpid, 'gid' => $gid,'action' => 'excel');
						echo '<table align="center" border=0><tr><td>';
					    print_single_button("$scriptname", $options, get_string("downloadexcel"));
						echo '</td>';
						echo '</tr></table>';
					}	
			break;
			
			case '2':
					listbox_class("$scriptname?rid=$rid&sid=$sid&yid=$yid&rpid=$rpid&gid=", $rid, $sid, $yid, $gid);
					
                  //  echo '</table>'; //notice(get_string('vstadii', 'block_mou_att'), "../index.php?rid=$rid&yid=$yid&sid=$sid");
                    
					if ($gid != 0)	{
						if ($class = get_record('monit_school_class','id',$gid)){
							$user = get_record_sql("SELECT id, firstname, lastname FROM {$CFG->prefix}user
														WHERE id={$class->teacherid}");
																		
							echo '<tr><td>'.get_string('teacher','block_mou_school').':</td><td>';
							echo $user->lastname.' '.$user->firstname;
							echo '</td></tr>';						
						}
						listbox_report_period("$scriptname?rid=$rid&sid=$sid&yid=$yid&rpid=$rpid&gid=$gid&perid=", $perid);
						echo '</table>';
						
						if ($perid != 0)  {
							
							$class = get_record('monit_school_class', 'id', $gid);
							$class_termtype = get_record('monit_school_class_termtype', 'schoolid', $sid, 'parallelnum', $class->parallelnum);
							
							switch($perid){
								case 1:
								$current_date = date('Y-m-d');	

								if($term = get_record_sql("SELECT id, name, datestart, dateend FROM {$CFG->prefix}monit_school_term
																		WHERE schoolid=$sid and datestart <= '$current_date' and dateend >= '$current_date' and termtypeid={$class_termtype->termtypeid}")){
									
									print_heading(get_string('currentperiod','block_mou_school').' - '.$term->name, "center", 3);
									
									$table = table_period_of_report($yid, $rid, $sid, $gid, $perid, $term->id);
									print_color_table($table);	

									$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'rpid' => $rpid, 'gid' => $gid,'perid' => $perid, 'termid'=>$term->id, 'action' => 'excel');
									echo '<table align="center" border=0><tr><td>';
								    print_single_button("$scriptname", $options, get_string("downloadexcel"));
									echo '</td></tr></table>';
								}else{
									notice(get_string('notenteredperiod', 'block_mou_school'), "$CFG->wwwroot/blocks/mou_school/index.php");
								}
							
								break;
								case 2:
									$current_date = date('Y-m-d');	
									if($term = get_record_sql("SELECT id, name, datestart, dateend FROM {$CFG->prefix}monit_school_term
																		WHERE schoolid=$sid and datestart <= '$current_date' and dateend >= '$current_date' and termtypeid={$class_termtype->termtypeid}")){
										$table = table_period_of_report($yid, $rid, $sid, $gid, $perid, $term->id);
										print_color_table($table);
                                        
                                        $options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'rpid' => $rpid, 'gid' => $gid,'perid' => $perid, 'termid'=>$term->id, 'action' => 'excel');
                        				echo '<table align="center" border=0><tr><td>';
                        				print_single_button("$scriptname", $options, get_string("downloadexcel"));
                        			   	echo '</td><td></table>';			
									}
								break;
								case 3:
								
								break;
							}	
						}

					}

			break;

            // Успеваемость школы 			
			case '3':
					$table = table_school_performance($yid, $rid, $sid);
					print $table;
                    $options = array('action' => 'word', 'rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'rpid' => $rpid);
    				echo '<p></p><table align="center" border=0><tr><td>';
    				print_single_button("$scriptname", $options, get_string("downloadword", 'block_monitoring'));
    			   	echo '</td><td></table>';			
			break;             
            
			// Успеваемость класса
            case '4':
					listbox_class("$scriptname?rid=$rid&sid=$sid&yid=$yid&rpid=$rpid&gid=", $rid, $sid, $yid, $gid);
					
                  //  echo '</table>'; //notice(get_string('vstadii', 'block_mou_att'), "../index.php?rid=$rid&yid=$yid&sid=$sid");
                    
					if ($gid != 0)	{
						if ($class = get_record('monit_school_class','id',$gid)){
							$user = get_record_sql("SELECT id, firstname, lastname FROM {$CFG->prefix}user
														WHERE id={$class->teacherid}");
																		
							echo '<tr><td>'.get_string('teacher','block_mou_school').':</td><td>';
							echo $user->lastname.' '.$user->firstname;
							echo '</td></tr>';						
						}
                        listbox_terms("$scriptname?rid=$rid&sid=$sid&yid=$yid&rpid=$rpid&gid=$gid&termid=", $sid, $yid, $gid, $termid); // , true
						// listbox_report_period("$scriptname?rid=$rid&sid=$sid&yid=$yid&rpid=$rpid&gid=$gid&perid=", $perid);
    					echo '</table>';
                        
                        $table = table_class_performance($yid, $rid, $sid, $gid, $termid);
                        echo get_shapka_report($table);            
    					print_color_table($table);
                        $table = table_class_performance_dop($yid, $rid, $sid, $gid, $termid);
                        print_color_table($table);
                        $options = array('action' => 'word', 'rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'rpid' => $rpid, 'gid' => $gid, 'termid' => $termid);
        				echo '<p></p><table align="center" border=0><tr><td>';
        				print_single_button("$scriptname", $options, get_string("downloadword", 'block_monitoring'));
        			   	echo '</td><td></table><br /><br />';
                        print_simple_box_start('center', '50%');
                        echo get_help4();
                        print_simple_box_end(); 
                        
                   }        			
			break;                                    

            // Успеваемость и посещаемость ученика
			case '5':
					listbox_class("$scriptname?rid=$rid&sid=$sid&yid=$yid&rpid=$rpid&gid=", $rid, $sid, $yid, $gid);
					
                  //  echo '</table>'; //notice(get_string('vstadii', 'block_mou_att'), "../index.php?rid=$rid&yid=$yid&sid=$sid");
                    
					if ($gid != 0)	{
						if ($class = get_record('monit_school_class','id',$gid)){
							$user = get_record_sql("SELECT id, firstname, lastname FROM {$CFG->prefix}user
							                         WHERE id={$class->teacherid}");
																		
							echo '<tr><td>'.get_string('teacher','block_mou_school').':</td><td>';
							echo $user->lastname.' '.$user->firstname;
							echo '</td></tr>';						
						}
                        listbox_terms("$scriptname?rid=$rid&sid=$sid&yid=$yid&rpid=$rpid&gid=$gid&termid=", $sid, $yid, $gid, $termid); // , true
                        listbox_pupils("$scriptname?rid=$rid&sid=$sid&yid=$yid&rpid=$rpid&gid=$gid&&termid=$termid&uid=", $rid, $sid, $yid, $gid, $uid);
						// listbox_report_period("$scriptname?rid=$rid&sid=$sid&yid=$yid&rpid=$rpid&gid=$gid&perid=", $perid);
    					echo '</table>';
                        if ($uid > 0)   {
                            $table = table_pupil_performance($yid, $rid, $sid, $gid, $termid, $uid);
                            print_color_table($table);
                            
                            $options = array('action' => 'word', 'rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'rpid' => $rpid, 'gid' => $gid, 'termid' => $termid, 'uid' => $uid );
            				echo '<p></p><table align="center" border=0><tr><td>';
            				print_single_button("$scriptname", $options, get_string("downloadword", 'block_monitoring'));
            			   	echo '</td><td></table>';
                        }    
                   }        			
			break;  
            
			// Отчет: Классному руководителю
            case '6':
					listbox_class("$scriptname?rid=$rid&sid=$sid&yid=$yid&rpid=$rpid&gid=", $rid, $sid, $yid, $gid);
					
                  //  echo '</table>'; //notice(get_string('vstadii', 'block_mou_att'), "../index.php?rid=$rid&yid=$yid&sid=$sid");
                    
					if ($gid != 0)	{
						if ($class = get_record('monit_school_class','id',$gid)){
							$user = get_record_sql("SELECT id, firstname, lastname FROM {$CFG->prefix}user
														WHERE id={$class->teacherid}");
																		
							echo '<tr><td>'.get_string('teacher','block_mou_school').':</td><td>';
							echo $user->lastname.' '.$user->firstname;
							echo '</td></tr>';						
						}
                        listbox_terms("$scriptname?rid=$rid&sid=$sid&yid=$yid&rpid=$rpid&gid=$gid&termid=", $sid, $yid, $gid, $termid); // , true
						// listbox_report_period("$scriptname?rid=$rid&sid=$sid&yid=$yid&rpid=$rpid&gid=$gid&perid=", $perid);
    					echo '</table>';
                        
                        if ($termid > 0)    {
                            $table = table_class_performance($yid, $rid, $sid, $gid, $termid, true);
        					print_color_table($table);
                            
                            $options = array('action' => 'word', 'rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'rpid' => $rpid, 'gid' => $gid, 'termid' => $termid);
            				echo '<p></p><table align="center" border=0><tr><td>';
            				print_single_button($scriptname, $options, get_string("downloadword", 'block_monitoring'));
            			   	echo '</td><td></table>';
                        }    
                   }        			
			break;                                                                            

			// Отчет: Учителю по предмету
            case '7':
            	    listbox_discipline_school("$scriptname?rid=$rid&sid=$sid&yid=$yid&rpid=$rpid&did=", $sid, $yid, $did);

					if ($did > 0)	{
					    listbox_teachers_in_discipline("$scriptname?rid=$rid&sid=$sid&yid=$yid&rpid=$rpid&did=$did&teachid=", $rid, $sid, $did, $teachid);
                        listbox_all_school_periods("$scriptname?rid=$rid&sid=$sid&yid=$yid&rpid=$rpid&did=$did&teachid=$teachid&termid=", $sid, $yid, $termid);
    					echo '</table>';
                        if ($teachid > 0)   {

                            $table = table_teacher_predmet($yid, $rid, $sid, $did, $teachid, $termid);
    					    print_color_table($table);
                        
                            $options = array('action' => 'excel', 'rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'rpid' => $rpid, 'did' => $did, 'teachid' => $teachid, 'termid' => $termid);
            				echo '<p></p><table align="center" border=0><tr><td>';
            				print_single_button($scriptname, $options, get_string("downloadexcel"));
            			   	echo '</td><td></table>';

                        }    
                   } else {
                       echo '</table>';
                   }        			
			break;
            
			case '11':
			//notify(get_string('vstadii','block_mou_school'),'green','center');
				listbox_class("$scriptname?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;rpid=$rpid&amp;gid=", $rid, $sid, $yid, $gid);
				if ($gid != 0){				
	
					echo '</table>';
				    $table = table_itogi ($yid, $rid, $sid, $gid);
					print_color_table($table);
					$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'rpid' => $rpid, 'gid' => $gid,'action' => 'excel');
					echo '<table align="center" border=0><tr><td>';
				    print_single_button($scriptname, $options, get_string("downloadexcel"));
					echo '</td>';
					echo '</tr></table>';
				}
	
			break;
            
            

		}
	
	    echo '</table>';
	} else {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}

    print_footer();



function table_itogi ($yid, $rid, $sid, $gid)
	{
		global $CFG;

		$table->head  = array (get_string('ordernumber','block_mou_school'), get_string('pupilfio','block_mou_school'),
								get_string('periods','block_mou_school'));
		$table->align = array ('center', 'left', 'left');
	    $table->size = array ('3%', '40%', '15%');
	    $table->wrap = array (0, 0, 1);
		$table->columnwidth = array (7, 30, 12);
		
		if ($classdisciplines = get_records_sql("SELECT id, schoolid, classid, schoolsubgroupid, disciplineid, teacherid, name
                                                 FROM {$CFG->prefix}monit_school_class_discipline
											     WHERE classid=$gid")){
			foreach ($classdisciplines as $classdiscipline){
					$strdiscipline = $classdiscipline->name;
					if ($discipline = get_record ('monit_school_discipline', 'id', $classdiscipline->disciplineid))	{
						$strdiscipline = $discipline->shortname;
					}					
					$table->head[]  = $strdiscipline;
					$table->align[] = 'center';
					$table->columnwidth[] = 10;

			}
		}

	    $table->class = 'moutable';
    	$table->titlesrows = array(30);
	    $table->titles = array();
	    $table->titles[] = get_string('administratives', 'block_mou_school');
	    $table->downloadfilename = 'itogi_'.$sid.'_'.$gid;
	    $table->worksheetname = $table->downloadfilename;

		$class = get_record('monit_school_class', 'id', $gid);
		$class_termtype = get_record('monit_school_class_termtype', 'schoolid', $sid, 'parallelnum', $class->parallelnum);
		$school_terms = get_records_select('monit_school_term', "schoolid = $sid AND  termtypeid = {$class_termtype->termtypeid}");

	    $studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.picture,
							  u.phone1, u.phone2, m.classid, m.pol, m.birthday, m.pswtxt
                            FROM {$CFG->prefix}user u
                       LEFT JOIN {$CFG->prefix}monit_school_pupil_card m ON m.userid = u.id
					   WHERE classid = $gid AND u.deleted = 0 AND u.confirmed = 1
					   ORDER BY u.lastname, u.firstname";

        if($students = get_records_sql($studentsql)) {
			if(!$classdisciplines){
				notify(get_string('class_doesnt_have_predmet','block_mou_school'), "green", 'center');
			}
        	$i=1;
			foreach ($students as $student){

				$tabledata = array($i++);
				$tabledata[] = "<div align=left><strong><a href=\"{$CFG->wwwroot}/blocks/mou_school/class/pupilcard.php?mode=4&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid={$student->id}\">".fullname($student)."</a></strong></div>";

				$colspan = count($school_terms);

				$strterms = '';
				if($school_terms) foreach ($school_terms as $school_term) {
                    $tabledata[] = $school_term->name;
                    if($classdisciplines) foreach ($classdisciplines as $discip)	{
				        $strmark = '';
						$mark = get_record_sql("SELECT mark FROM {$CFG->prefix}monit_school_marks_totals_term
												WHERE userid={$student->id} and classdisciplineid={$discip->id} and termid={$school_term->id}");
						if ($mark)	{
							$strmark = $mark->mark; //   . '<br>';
						} else {
							$strmark =  ''; //'-<br>';
						}
						$tabledata[] = $strmark;
                    }
                    $table->data[] = $tabledata;
                    $tabledata = array('', '');				
                }
			}
		}
	    return $table;
}

function get_help4()
{
    $message = 'Отчет «Успеваемость класса» за выбранный учебный период заполняется автоматически после выставления 
ученикам класса в данном отчетном периоде всех итоговых оценок по всем предметам. Вторая таблица отчета также строится 
на основе итоговых оценок.<br />
Для подсчета аналитических параметров в отчете используются следующие формулы:<br />
Средний балл = (n1*1 + n2*2 + n3*3 + n4*4 + n5*5)/ (n1 + n2 + n3 + n4 + n5);<br />
% кач. зн. по предмету = ((n4 + n5)*100%) / кол-во учеников в классе;<br /> 
% СОУ (степень обуч-ти учащихся) =  ((n2*0,14 + n3*0,36 + n4*0,64 + n5*1 )*100%)/ кол-во учащихся класса;<br />
где n2 – кол-во оценок «2»;<br />
где n3 – кол-во оценок «3»;<br />
где n4 – кол-во оценок «4»;<br />
где n5 – кол-во оценок «5».<br />
% кач. зн. класса = ((кол-во отличников + кол-во хорошистов) * 100%) / кол-во учеников в классе.<br />
Рейтинг формируется в соответствии со средними баллами учащихся.    
';
    
   return $message;  
}
?>