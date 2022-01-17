<?php // $Id: lib_report.php,v 1.3 2014/06/03 07:51:03 shtifanov Exp $

function count_mark(&$arr_marks, $mark)
{
	$i=0;
	foreach ($arr_marks as $onemark)	{
		if ($onemark == $mark) {
            $i++; 
		}
	}
	return $i;
}


function listbox_performance_reports($scriptname, $rid, $sid, $yid, $rpid)
{
	global $CFG;
 	$reportmenu = array();
 	$reportmenu[0] = get_string('selectcurrreport', 'block_mou_school') . '...';
	if ($rid != 0 && $sid!= 0 && $yid!= 0)  {
        // $currreportmenu[1] = get_string('classrukovodreport', 'block_mou_school');
        // $currreportmenu[2] = get_string('classrukovodreport_all_period', 'block_mou_school');
        $reportmenu[3] = 'Успеваемость по школе';        
        $reportmenu[4] = 'Успеваемость класса';
        $reportmenu[5] = 'Успеваемость и посещаемость ученика';
        $reportmenu[6] = 'Классному руководителю';
        $reportmenu[7] = 'Учителю по предмету';
        $reportmenu[11] = get_string('svodtabuspevaemosti', 'block_mou_school');        
    	echo '<tr><td>'.get_string('typeofreport', 'block_mou_school').':</td><td>';
    	popup_form($scriptname, $reportmenu, "switchreport", $rpid, "", "", "", false);
    	echo '</td></tr>';
    	return 1;
	}
}


function listbox_attendance_reports($scriptname, $rid, $sid, $yid, $rpid)
{
	global $CFG;
 	$reportmenu = array();
 	$reportmenu[0] = get_string('selectitogreport', 'block_mou_school') . '...';
	if ($rid != 0 && $sid!= 0 && $yid!= 0)  {
        $reportmenu[2] = get_string('svodtabposeshaemosti', 'block_mou_school');

	echo '<tr><td>'.get_string('typeofreport', 'block_mou_school').':</td><td>';
	popup_form($scriptname, $reportmenu, "switchreport", $rpid, "", "", "", false);
	echo '</td></tr>';
	return 1;
	}
}


function listbox_statistics_reports($scriptname, $rid, $sid, $yid, $rpid)
{
	global $CFG;
 	$reportmenu = array();
 	$reportmenu[0] = get_string('selectitogreport', 'block_mou_school') . '...';
	if ($rid != 0 && $sid!= 0 && $yid!= 0)  {
        $reportmenu[8] = 'Статистика оценок: Школа';
        $reportmenu[9] = 'Статистика оценок: Классы';
        $reportmenu[10] = 'Статистика оценок: Предметы';

	echo '<tr><td>'.get_string('typeofreport', 'block_mou_school').':</td><td>';
	popup_form($scriptname, $reportmenu, "switchreport", $rpid, "", "", "", false);
	echo '</td></tr>';
	return 1;
	}
}

function listbox_report_period($scriptname, $perid)
{
	global $CFG;
	
 	$periodreportmenu = array();
 	$periodreportmenu[0] = get_string('selectperiodreport', 'block_mou_school') . '...';
 	
    $periodreportmenu[1] = get_string('justcurrentperiod', 'block_mou_school');
    $periodreportmenu[2] = get_string('currentandprevperiod', 'block_mou_school');
    //$periodreportmenu[3] = get_string('alleducationperiod', 'block_mou_school');

	echo '<tr><td>'.get_string('typeofreport', 'block_mou_school').':</td><td>';
	popup_form($scriptname, $periodreportmenu, "switchperiodreport", $perid, "", "", "", false);
	echo '</td></tr>';
	return 1;
}


function table_period_of_report($yid, $rid, $sid, $gid, $perid, $termid)
{
	global $CFG;
	
	switch($perid){
		case 1:
			$table->head  = array (get_string('currentotlichniki','block_mou_school'),
									get_string('currentpotentialotlichniki','block_mou_school'), get_string('currenthoroshisti','block_mou_school'), 
									get_string('currentpotentialhoroshisti','block_mou_school'), get_string('currentneuspevaushie','block_mou_school'));
			$table->align = array ('center', 'center', 'center', 'center', 'center');
		    $table->size = array ('20%', '30%', '15%', '25%', '10%');
			$table->columnwidth = array (20, 20, 15, 30, 12);
		
		    $table->class = 'moutable';
			$table->titlesrows = array(30);
		    $table->titles = array();
		    $table->titles[] = get_string('classrukovodreport_all_period', 'block_mou_school');
		    $table->downloadfilename = 'current_period_report';
		    $table->worksheetname = 'current_period_report';
			$tabledata = array();

			$school_term = get_record_sql("SELECT id, name, datestart, dateend FROM {$CFG->prefix}monit_school_term WHERE id=$termid");
         //  print_r($school_term);
			$tabledata = array();
			$strsql= "SELECT id FROM {$CFG->prefix}monit_school_class_schedule_$rid
					  WHERE classid=$gid and '{$school_term->datestart}' <= datestart AND datestart <= '{$school_term->dateend}'";
			if ($shedules = get_records_sql($strsql))	{
			// print_r($shedules);
				$shedulearray = array();
				foreach ($shedules as $shedule){
					$shedulearray[] = $shedule->id;
				}
	
				$shedulelist = implode(',', $shedulearray);
				$strsql = "SELECT userid FROM {$CFG->prefix}monit_school_pupil_card WHERE classid = $gid";
				if($students = get_records_sql($strsql)) {
					$currotlich = $currhoroshisti = $neuspev = $troeshniki = $potential = $potential_otlich = $horoshisti = 0;
					foreach ($students as $student){
						if ($marks = get_records_select('monit_school_marks_'.$rid, "userid = $student->userid AND (scheduleid in ($shedulelist))")){
							$arr_marks = array();
							foreach ($marks as $mar){
								$arr_marks[] = $mar->mark;
							}
							if (!empty($arr_marks))	{
								if (count_mark($arr_marks, 2) > 0) {
									$neuspev++;	
								} else {
									$cntm = count_mark($arr_marks, 3);
									if ($cntm > 0)  {
										if($cntm <= 2)	{
											$potential++;
										}	else {
											$troeshniki++;
										}
									} else {
										$cntm = count_mark($arr_marks, 4);
										if($cntm > 0)	{
											if ($cntm <=2 )  {
												$potential_otlich++;
											} else {
												$horoshisti++;										
											}	
										} else {
											$currotlich++;
										}	 
									} 
								}
							}	
						}				
					}
				}
				$tabledata[] = $currotlich;
				$tabledata[] = $potential_otlich;
				$tabledata[] = $horoshisti;
				$tabledata[] = $potential;
				$tabledata[] = $neuspev;	
					
			}else{
				$tabledata[] = '-';
				$tabledata[] = '-';
				$tabledata[] = '-';
				$tabledata[] = '-';
				$tabledata[] = '-';			
			}
			$table->data[] = $tabledata;				
		break;
        
		case 2:
			$class = get_record('monit_school_class', 'id', $gid);
			$class_termtype = get_record('monit_school_class_termtype', 'schoolid', $sid, 'parallelnum', $class->parallelnum);
			
			$term_name = get_record_sql("SELECT name, datestart, dateend, termtypeid FROM {$CFG->prefix}monit_school_term WHERE id=$termid");
			
			$get_previous_period = get_record_sql("SELECT name, datestart, dateend, termtypeid FROM {$CFG->prefix}monit_school_term 
													WHERE dateend<'$term_name->datestart'");
		//	print_r($get_previous_period);										
			$table->head  = array (get_string('typeuspevaemosti','block_mou_school'), get_string('kollichestvo', 'block_mou_school'), get_string('currentperiod','block_mou_school').'<br>'.$term_name->name, get_string('kollichestvo', 'block_mou_school'),
                            get_string('previousperiod','block_mou_school').'<br>'.$get_previous_period->name);
			$table->align = array ('center', 'right', 'center', 'left', 'center');
		//	$table->size = array ('30%', '','5%', '30%','', '5%');
		//	$table->head[] = get_string('previousperiod','block_mou_school').'<br>'.$get_previous_period->name;
			//$table->align[] = 'left';
		   // $table->size[] = '90%';
			$table->columnwidth = array (20, 20, 15, 20, 20);
		    $table->class = 'moutable';
		    $table->width = '70%';
			$table->titlesrows = array(50);
		    $table->titles = array();
		    $table->titles[] = get_string('classrukovodreport_all_period', 'block_mou_school');
		    $table->downloadfilename = 'current_period_report';
		    $table->worksheetname = 'current_period_report';
		    
		    $tablrdata = array();

		//	$tabledata[1] = '<b>'.get_string('na5','block_mou_school');
			$reiting_name = array(get_string('na5','block_mou_school'), get_string('with_once_4','block_mou_school'),
                            get_string('4_and_5','block_mou_school'),get_string('with_once_3','block_mou_school'),
                            get_string('3_and_2','block_mou_school'));
                            

                
			$strsql = "SELECT id FROM {$CFG->prefix}monit_school_class_schedule_$rid
					  WHERE classid=$gid and '{$term_name->datestart}' <= datestart AND datestart <= '{$term_name->dateend}'";		
            $shedulearray = array(0);
			if ($shedules = get_records_sql($strsql))	{
				foreach ($shedules as $shedule){
					$shedulearray[] = $shedule->id;
				}
	        }

			$strsql2 = "SELECT id FROM {$CFG->prefix}monit_school_class_schedule_$rid
					  WHERE classid=$gid and '{$get_previous_period->datestart}' <= datestart AND datestart <= '{$get_previous_period->dateend}'";						
			$shedulearray2 = array(0);
			if ($shedules2 = get_records_sql($strsql2))	{
				foreach ($shedules2 as $shedule2){
					$shedulearray2[] = $shedule2->id;
				}
	        }

            $fullname  = $fullname2 = $fullname3 = $fullname4 = $fullname5 = $fullname6 = $fullname7 = $fullname8 = $fullname44 = $fullname88 ='';
				$shedulelist = implode(',', $shedulearray);
				$strsql = "SELECT userid FROM {$CFG->prefix}monit_school_pupil_card WHERE classid = $gid";
				if($students = get_records_sql($strsql)) {
					$currotlich = $currhoroshisti = $neuspev = $troeshniki = $potential = $potential_otlich = $horoshisti = 0;
					//$fullname = '';
                    foreach ($students as $student){
					 //$fullname = '';
						if ($marks = get_records_select('monit_school_marks_'.$rid, "userid = $student->userid AND (scheduleid in ($shedulelist))")){
							$arr_marks = array();
							$names = $names2 = $names3 = $name = array();
							foreach ($marks as $mar){
								$arr_marks[] = $mar->mark;
							}
                            
							if (!empty($arr_marks))	{
							 
							//	$user = get_record('user', 'id', $marks->userid);
                                $username  = get_record('user', 'id', $student->userid);
                               // $fullname = $fullname2 = $fullname3 = $fullname4 = $fullname44 = $fullname5 = $fullname6 = $fullname7 = $fullname8 = $fullname88 = '';
								if (count_mark($arr_marks, 2) > 0) {//$fullname44 = '';
								    $fullname44 .= fullname($username).'<br>';
									$neuspev++;	
								} else {
									$cntm = count_mark($arr_marks, 3);
									if ($cntm > 0)  {//$fullname4 = $fullname44 = '';
										if($cntm <= 2)	{
										  $fullname4 .= fullname($username).'<br>';
										  $potential++;
                                            
										}else {
										  $fullname44 .= fullname($username).'<br>';
										  $neuspev ++;
										  $troeshniki++;
										}
									} else {
										$cntm = count_mark($arr_marks, 4);
                                        
										if($cntm > 0)	{//$fullname2 = '';
											if ($cntm <=2 )  {
											 // $fullname = $fullname3 = '';
                                              $fullname .= fullname($username).'<br>';
											  $potential_otlich++;
											} else {
											  $fullname3 .= fullname($username).'<br>';
											  $horoshisti++;										
											}	
										} else {
                                            $fullname2 .= fullname($username).'<br>';
											$currotlich++;
										}	 
									} 
								}
							}	
						}				
					}
				}

				$shedulelist2 = implode(',', $shedulearray2);
				$strsql = "SELECT userid FROM {$CFG->prefix}monit_school_pupil_card WHERE classid = $gid";
				if($students = get_records_sql($strsql)) {
					$currotlich2 = $currhoroshisti = $neuspev2 = $troeshniki2 = $potential2 = $potential_otlich2 = $horoshisti2 = 0;
					//$fullname = '';
                    foreach ($students as $student){
					 //$fullname = '';
						if ($marks = get_records_select('monit_school_marks_'.$rid, "userid = $student->userid AND (scheduleid in ($shedulelist2))")){
							$arr_marks = array();
							$names = $names2 = $names3 = $name = array();
							foreach ($marks as $mar){
								$arr_marks[] = $mar->mark;
							}
                            
							if (!empty($arr_marks))	{
							 
							//	$user = get_record('user', 'id', $marks->userid);
                                $username  = get_record('user', 'id', $student->userid);
                                
								if (count_mark($arr_marks, 2) > 0) {//$fullname88 = '';
								    $fullname88 .= fullname($username).'<br>';
									$neuspev2++;	
								} else {
									$cntm = count_mark($arr_marks, 3);
									if ($cntm > 0)  {//$fullname8 = $fullname88 = '';
										if($cntm <= 2)	{
										  $fullname8 .= fullname($username).'<br>';
										  $potential2++;
                                            
										}else {
										  $fullname88 .= fullname($username).'<br>';
										  $neuspev2++;
										  $troeshniki2++;
										}
									} else {
										$cntm = count_mark($arr_marks, 4);
                                        //$fullname6 = $fullname7 = '';
										if($cntm > 0)	{$fullname5 = '';
											if ($cntm <=2 )  {
    										  
                                              $fullname6 .= fullname($username).'<br>';
											  $potential_otlich2++;
											} else {
											  $fullname7 .= fullname($username).'<br>';
											  $horoshisti2++;										
											}	
										} else {
                                            $fullname5 .= fullname($username).'<br>';
											$currotlich2++;
										}	 
									} 
								}
							}	
						}				
					}
				}
                foreach($reiting_name as $id=>$value){
                    switch($id){
                        case 0:
                            $tabledata[1] = '<b>'.$value.'</b>';
                            $tabledata[2] = $currotlich;
                            $tabledata[3] = $fullname2;
                            $tabledata[4] = $currotlich2;
                            $tabledata[5] = $fullname5;
                            $table->data[] = $tabledata;
                        break;
                        case 1:
                            $tabledata[1] = '<b>'.$value.'</b>';
                            $tabledata[2] = $potential_otlich;
                            $tabledata[3] = $fullname;
                            $tabledata[4] = $potential_otlich2;
                            $tabledata[5] = $fullname6;
                            $table->data[] = $tabledata;
                        break;
                        case 2:
                            $tabledata[1] = '<b>'.$value.'</b>';
                            $tabledata[2] = $horoshisti;
                            $tabledata[3] = $fullname3;
                            $tabledata[4] = $horoshisti2;
                            $tabledata[5] = $fullname7;
                            $table->data[] = $tabledata;
                        break;
                        case 3:
                            $tabledata[1] = '<b>'.$value.'</b>';
                            $tabledata[2] = $potential;
                            $tabledata[3] = $fullname4;
                            $tabledata[4] = $potential2;
                            $tabledata[5] = $fullname8;
                            $table->data[] = $tabledata; 
                        break;
                        case 4:
                            $tabledata[1] = '<b>'.$value.'</b>';
                            $tabledata[2] = $neuspev;
                            $tabledata[3] = $fullname44;
                            $tabledata[4] = $neuspev2;
                            $tabledata[5] = $fullname88;
                            $table->data[] = $tabledata; 
                        break;
                    }  
                  }          

		    
		break;
		case 3:
		
		break;
	}

    return $table;
}

function table_prereport_for_current_term ($yid, $rid, $sid, $gid)
{
	global $CFG;

	$table->head  = array (get_string('periods','block_mou_school'), get_string('currentotlichniki','block_mou_school'),
							get_string('currentpotentialotlichniki','block_mou_school'), get_string('currenthoroshisti','block_mou_school'), 
							get_string('currentpotentialhoroshisti','block_mou_school'), get_string('currentneuspevaushie','block_mou_school'));
	$table->align = array ('center', 'center', 'center', 'center', 'center', 'center');
    $table->size = array ('15%', '15%', '25%', '10%', '25%', '10%');
	$table->columnwidth = array (20, 20, 12, 15, 30, 12);

    $table->class = 'moutable';
	$table->titlesrows = array(30);
    $table->titles = array();
    $table->titles[] = get_string('classrukovodreport', 'block_mou_school');
    $table->downloadfilename = 'itogi';
    $table->worksheetname = 'currentuspandpos';
	$tabledata = array();
	
	$currdate = date('Y-m-d');		
	$class = get_record('monit_school_class', 'id', $gid);
	$class_termtype = get_record('monit_school_class_termtype', 'schoolid', $sid, 'parallelnum', $class->parallelnum);
	$school_terms = get_records_sql("SELECT id, name, datestart, dateend FROM {$CFG->prefix}monit_school_term
									WHERE schoolid=$sid and datestart <= '$currdate' and termtypeid={$class_termtype->termtypeid}");

	foreach ($school_terms as $school_term)	{
		$tabledata = array($school_term->name);
		$strsql= "SELECT id FROM {$CFG->prefix}monit_school_class_schedule_$rid
				  WHERE  classid=$gid and '{$school_term->datestart}' <= datestart AND datestart <= '{$school_term->dateend}'";
		if ($shedules = get_records_sql($strsql))	{
			$shedulearray = array();
			foreach ($shedules as $shedule){
				
				$shedulearray[] = $shedule->id;
			}

			$shedulelist = implode(',', $shedulearray);
			$strsql = "SELECT userid FROM {$CFG->prefix}monit_school_pupil_card WHERE classid = $gid";
			if($students = get_records_sql($strsql)) {
				$currotlich = $currhoroshisti = $neuspev = $troeshniki = $potential = $potential_otlich = $horoshisti = 0;
				foreach ($students as $student){
					if ($marks = get_records_select('monit_school_marks_'.$rid, "userid = $student->userid AND (scheduleid in ($shedulelist))")){
						$arr_marks = array();
						foreach ($marks as $mar){
							$arr_marks[] = $mar->mark;
						}
						if (!empty($arr_marks))	{
							if (count_mark($arr_marks, 2) > 0) {
								$neuspev++;	
							} else {
								$cntm = count_mark($arr_marks, 3);
								if ($cntm > 0)  {
									if($cntm <= 2)	{
										$potential++;
									}	else {
										$troeshniki++;
									}
								} else {
									$cntm = count_mark($arr_marks, 4);
									if($cntm > 0)	{
										if ($cntm <=2 )  {
											$potential_otlich++;
										} else {
											$horoshisti++;										
										}	
									} else {
										$currotlich++;
									}	 
								} 
							}
						}	
	
					}				
				}
			}
			$tabledata[] = $currotlich;
			$tabledata[] = $potential_otlich;
			$tabledata[] = $horoshisti;
			$tabledata[] = $potential;
			$tabledata[] = $neuspev;		
		}else{
			$tabledata[] = '-';
			$tabledata[] = '-';
			$tabledata[] = '-';
			$tabledata[] = '-';
			$tabledata[] = '-';			
		}
		$table->data[] = $tabledata;	
	}	
    return $table;
}


function get_shapka_report($table)
{
    $ret = '';
    
    $ret .= '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
    if (isset($table->allsrednee))  { 
        $ret .=  '<tr><td>Общий средний балл класса: <b>'.str_replace('.', ',', $table->allsrednee).'</b></td></tr>';
    }
    if (isset($table->allkachestvo))  { 
        $ret .=  '<tr><td>Общий % кач. зн. по предметам: <b>'. str_replace('.', ',', $table->allkachestvo) . '%</b></td></tr>';
    }    
    if (isset($table->allSOU))  { 
        $ret .=  '<tr><td>Общий % СОУ (степень обуч-ти учащихся): <b>'.str_replace('.', ',', $table->allSOU).'%</b></td></tr>';
    }    
    if (isset($table->alluspevemost))  { 
        $ret .=  '<tr><td>Общий % успеваемости: <b>'. str_replace('.', ',', $table->alluspevemost) . '%</b></td></tr>';
    }    
    if (isset($table->allkachestvo))  { 
        $ret .= '<tr><td>Общий % кач. зн. класса: <b>'.str_replace('.', ',', $table->allkachestvo).'%</b></td></tr>';
    }  
    $ret .= '<tr><td>На конец периода: <b>'.$table->countpupils .'</b></td></tr>';
    $ret .= '<tr><td>Нет оценок: <b>'. $table->nomark .'</b></td></tr>';
    $ret .= '</table>';
    
    return $ret;
}


function table_school_performance($yid, $rid, $sid)
{
    global $CFG;
    
    $buffer = '<table width="100%" border=1 align=center  cellpadding="5" cellspacing="1"  class="moutable">
                <tr>
                <th rowspan=3 align=center width="10%" class="header">Класс</th>
                <th colspan=11 width="60%" class="header">Ученики</th>
                <th rowspan=3 align=center width="10%" class="header">Ср. балл</th>
                <th rowspan=3 align=center width="10%" class="header">Общий % кач. зн. класса</th>
                <th rowspan=3 align=center width="10%" class="header">Общий СОУ (%)</th>
                </tr>
                <tr>
                <th rowspan=2 align=center class="header">Всего</th>
                <th colspan=3 class="header">Отличники</th>
                <th colspan=2 class="header">Хорошо</th>
                <th colspan=2 class="header">Успевающие</th>
                <th colspan=3 class="header">Неуспевающие</th>
                </tr>
                <tr>
                <th align=center class="header">Всего</th>
                <th align=center class="header">%</th>
                <th class="header">ФИО</th>
                <th align=center class="header">Всего</th>
                <th align=center class="header">%</th>
                <th align=center class="header">Всего</th>
                <th align=center class="header">%</th>
                <th align=center class="header">Всего</th>
                <th align=center class="header">%</th>
                <th class="header">ФИО</th>
                </tr>';
                
                
    $sql = "SELECT id, name  FROM {$CFG->prefix}monit_school_class WHERE yearid=$yid AND schoolid=$sid ORDER BY parallelnum, name";               
	if ($classes = get_records_sql_menu($sql)) {
        $sql = "SELECT id, parallelnum  FROM {$CFG->prefix}monit_school_class WHERE yearid=$yid AND schoolid=$sid ORDER BY parallelnum, name";
        $parallels = get_records_sql_menu($sql);
         	   
        $sql = "create temporary table temp1
                SELECT sched.schoolid, sched.classid, marks.userid, marks.mark
                FROM mdl_monit_school_class_schedule_{$rid} sched
                inner join mdl_monit_school_marks_{$rid} marks on sched.id=marks.scheduleid
                where sched.schoolid=$sid and marks.mark>0";
        // print $sql . '<br />';                
        execute_sql($sql, false);        

        $sql = "insert into temp1 SELECT sched.schoolid, sched.classid, marks.userid, marks.mark2 as mark
                FROM mdl_monit_school_class_schedule_{$rid} sched
                inner join mdl_monit_school_marks_{$rid} marks on sched.id=marks.scheduleid
                where sched.schoolid=$sid and marks.mark2>0";
        // print $sql . '<br />';                
        execute_sql($sql, false);                

        $sql = "create temporary table temp2
                select classid, userid, min(mark) as minmark from temp1
                group by userid";
        execute_sql($sql, false);
       
       $sumparallels = array();
       $currparalel = 0;
	   foreach ($classes as $classid => $classname)    {
	       
	        if ($currparalel>0 && $currparalel != $parallels[$classid])    {
                if (isset($sumparallels[$currparalel])) {
	               $cntpupil = $sumparallels[$currparalel]->countpupils;
	               $buffer .= "<tr><td align=center>$currparalel Параллель</td><td align=center>$cntpupil</td>";
                   for ($i=5; $i>=2; $i--)  {
                        $buffer .= '<td align=center>' . $sumparallels[$currparalel]->kols[$i] . '</td>';
                        $percent = round($sumparallels[$currparalel]->kols[$i]/$cntpupil*100, 2);
                        $percent = str_replace('.', ',', $percent);  
                        $buffer .= "<td align=center>$percent</td>";
                        if ($i==5 || $i==2){
                            $buffer .= "<td align=left></td>";
                        } 
                   }
                    $avgball = round ($sumparallels[$currparalel]->sumavg/$sumparallels[$currparalel]->countclass, 2); 
                    $buffer .= "<td align=center>$avgball</td>";
                    if ($cntpupil > 0)   {
                        $kachestvo = calculate_kachestvo($sumparallels[$currparalel]->kols, $cntpupil);
                        $SOU = calculate_SOU($sumparallels[$currparalel]->kols, $cntpupil);
                        $kachestvo = str_replace('.', ',', $kachestvo);
                        $SOU = str_replace('.', ',', $SOU);
                    } else {
                        $kachestvo = $SOU = 0;
                    }     
                    $buffer .= "<td align=center>$kachestvo</td>";
                    $buffer .= "<td align=center>$SOU</td>";
                        
                   $buffer .= '</tr>';
                }   
	        } 
             
	        $currparalel = $parallels[$classid];
            // количество учеников с 0, 1, 2, 3, 4, 5
	        $kolpupil = array(0,0,0,0,0,0);
            
            $countpupils = count_records('monit_school_pupil_card', 'classid',  $classid, 'deleted', 0);
            if (!isset($sumparallels[$currparalel])) {
                $sumparallels[$currparalel] = new stdClass();
                $sumparallels[$currparalel]->countpupils = 0;
                $sumparallels[$currparalel]->kols = array(0,0,0,0,0,0);
                $sumparallels[$currparalel]->countclass = 0;
                $sumparallels[$currparalel]->sumavg = 0;
            }
            
            $sumparallels[$currparalel]->countpupils += $countpupils;
            $sumparallels[$currparalel]->countclass++;
	        $buffer .= "<tr><td align=center>$classname</td><td align=center>$countpupils</td>";
           
	        $tname = 'temp'.$classid;   
    
            $sql = "create temporary table $tname
                    select userid, concat(u.lastname, ' ', u.firstname) as fullname, minmark from temp2
                    inner join mdl_user u on u.id=temp2.userid
                    where classid=$classid";
            execute_sql($sql, false);
            
            $sql = "select minmark, group_concat(fullname) as fio, count(userid) as kol from $tname
                    group by minmark
                    order by minmark";
	        if ($marks = get_records_sql($sql)) {
	            // print_object($marks);
                if (isset($marks[5]))   {
                    $mark = $marks[5];
                    $percent = round($mark->kol/$countpupils*100, 2);
                    $kolpupil[5] = $mark->kol;
                    $sumparallels[$currparalel]->kols[5] += $mark->kol;
                    $fio = str_replace(',', ',<br />', $mark->fio);
                    $percent = str_replace('.', ',', $percent);
                    $buffer .= "<td align=center>$mark->kol</td><td align=center>$percent</td><td align=left>$fio</td>";
                } else {
                    $buffer .= "<td align=center>0</td><td align=center>0</td><td align=center></td>";
                }
                if (isset($marks[4]))   {
                    $mark = $marks[4];
                    $percent = round($mark->kol/$countpupils*100, 2);
                    $kolpupil[4] = $mark->kol;
                    $sumparallels[$currparalel]->kols[4] += $mark->kol;
                    $percent = str_replace('.', ',', $percent);
                    $buffer .= "<td align=center>$mark->kol</td><td align=center>$percent</td>";
                } else {
                    $buffer .= "<td align=center>0</td><td align=center>0</td>";
                }
                if (isset($marks[3]))   {
                    $mark = $marks[3];
                    $percent = round($mark->kol/$countpupils*100, 2);
                    $kolpupil[3] = $mark->kol;
                    $sumparallels[$currparalel]->kols[3] += $mark->kol;
                    $percent = str_replace('.', ',', $percent);
                    $buffer .= "<td align=center>$mark->kol</td><td align=center>$percent</td>";
                } else {
                    $buffer .= "<td align=center>0</td><td align=center>0</td>";
                }
                if (isset($marks[2]))   {
                    $mark = $marks[2];
                    $percent = round($mark->kol/$countpupils*100, 2);
                    $kolpupil[2] = $mark->kol;
                    $sumparallels[$currparalel]->kols[2] += $mark->kol;
                    $fio = str_replace(',', ',<br />', $mark->fio);
                    $percent = str_replace('.', ',', $percent);
                    $buffer .= "<td align=center>$mark->kol</td><td align=center>$percent</td><td align=left>$fio</td>";
                } else {
                    $buffer .= "<td align=center>0</td><td align=center>0</td><td align=center></td>";
                }
                
                if ($avgball = get_field_sql("select round(avg(mark),2) from  temp1 where classid=$classid"))    {
                    $avgball = str_replace('.', ',', $avgball);
                    $buffer .= "<td align=center>$avgball</td>";
                    $sumparallels[$currparalel]->sumavg += $avgball;    
                } else {
                    $buffer .= "<td align=center>-</td>";
                }
                if ($countpupils > 0)   {
                    $kachestvo = calculate_kachestvo($kolpupil, $countpupils);
                    $SOU = calculate_SOU($kolpupil, $countpupils);
                    $kachestvo = str_replace('.', ',', $kachestvo);
                    $SOU = str_replace('.', ',', $SOU);
                } else {
                    $kachestvo = $SOU = '-';
                }     
                $buffer .= "<td align=center>$kachestvo</td>";
                $buffer .= "<td align=center>$SOU</td>";
	        }   else {
                $buffer .= "<td align=center>-</td align=center><td>-</td><td align=center>-</td>";
                $buffer .= "<td align=center>-</td><td align=center>-</td>";
                $buffer .= "<td align=center>-</td><td align=center>-</td>";
                $buffer .= "<td align=center>-</td><td align=center>-</td><td align=center>-</td>";
                $buffer .= "<td align=center>-</td><td align=center>-</td><td align=center>-</td>";
	        }
	        $buffer .= "</tr>";   
	   }

                if (isset($sumparallels[$currparalel])) {
	               $cntpupil = $sumparallels[$currparalel]->countpupils;
	               $buffer .= "<tr><td align=center>$currparalel Параллель</td><td align=center>$cntpupil</td>";
                   for ($i=5; $i>=2; $i--)  {
                        $buffer .= '<td align=center>' . $sumparallels[$currparalel]->kols[$i] . '</td>';
                        $percent = round($sumparallels[$currparalel]->kols[$i]/$cntpupil*100, 2);
                        $percent = str_replace('.', ',', $percent);  
                        $buffer .= "<td align=center>$percent</td>";
                        if ($i==5 || $i==2){
                            $buffer .= "<td align=left></td>";
                        } 
                   }
                    $avgball = round ($sumparallels[$currparalel]->sumavg/$sumparallels[$currparalel]->countclass, 2);
                    $avgball = str_replace('.', ',', $avgball); 
                    $buffer .= "<td align=center>$avgball</td>";
                    if ($cntpupil > 0)   {
                        $kachestvo = calculate_kachestvo($sumparallels[$currparalel]->kols, $cntpupil);
                        $SOU = calculate_SOU($sumparallels[$currparalel]->kols, $cntpupil);
                        $kachestvo = str_replace('.', ',', $kachestvo);
                        $SOU = str_replace('.', ',', $SOU);
                    } else {
                        $kachestvo = $SOU = 0;
                    }     
                    $buffer .= "<td align=center>$kachestvo</td>";
                    $buffer .= "<td align=center>$SOU</td>";
                        
                   $buffer .= '</tr>';
                }   
                
        $kolpupil = array(0,0,0,0,0,0);
        $cntpupil = 0;
        $countclass = 0;
        $sumavg = 0;
        foreach ($sumparallels as $sumparallel) {
            $cntpupil += $sumparallel->countpupils;
            $countclass += $sumparallel->countclass;
            $sumavg += $sumparallel->sumavg;
            for ($i=5; $i>=2; $i--)  {
                $kolpupil[$i] += $sumparallel->kols[$i];
            }    
        }
        
        $buffer .= "<tr><td align=center>Школа</td><td align=center>$cntpupil</td>";
        for ($i=5; $i>=2; $i--)  {
            $buffer .= '<td align=center>' . $kolpupil[$i] . '</td>';
            $percent = round($kolpupil[$i]/$cntpupil*100, 2);
            $percent = str_replace('.', ',', $percent);  
            $buffer .= "<td align=center>$percent</td>";
            if ($i==5 || $i==2){
                $buffer .= "<td align=left></td>";
            } 
        }
        
        $avgball = round ($sumavg/$countclass, 2);
        $avgball = str_replace('.', ',', $avgball);  
        $buffer .= "<td align=center>$avgball</td>";
        
        if ($cntpupil > 0)   {
            $kachestvo = calculate_kachestvo($kolpupil, $cntpupil);
            $SOU = calculate_SOU($kolpupil, $cntpupil);
            $kachestvo = str_replace('.', ',', $kachestvo);
            $SOU = str_replace('.', ',', $SOU);
        } else {
            $kachestvo = $SOU = 0;
        }     
        $buffer .= "<td align=center>$kachestvo</td>";
        $buffer .= "<td align=center>$SOU</td>";
            
       $buffer .= '</tr>';

       //  print_object($sumparallels);
	}
    $buffer .= "</table>";
                
    return $buffer;
}


function calculate_kachestvo($kolpupil, $countpupils)
{
     return round(($kolpupil[5] + $kolpupil[4])/$countpupils *100, 2); // $kachestvo =
} 


function calculate_SOU($kolpupil, $countpupils)
{
    return round((0.16*$kolpupil[2] + 0.36*$kolpupil[3] + 0.64*$kolpupil[4] + $kolpupil[5])/$countpupils *100, 2);
    
}


function table_class_performance($yid, $rid, $sid, $gid, $termid, $isforclassteacher=false)
{
	global $CFG;

    if ($isforclassteacher) {
        $school_term = get_record_select('monit_school_term', "id=$termid" , 'id, datestart, dateend'); // приходит с URL
    }
    
    $table= new stdClass();
	$table->head  = array (get_string('ordernumber','block_mou_school'), get_string('pupilfio','block_mou_school'));
	$table->align = array ('center', 'left');
    $table->size = array ('3%', '40%');
    $table->wrap = array (0, 0);
	$table->columnwidth = array (7, 30);
	
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
    
	$table->head[]  = 'Ср. балл';
	$table->align[] = 'center';
	$table->columnwidth[] = 10;

	$table->head[]  = 'Рейтинг';
	$table->align[] = 'center';
	$table->columnwidth[] = 10;

    $table->class = 'moutable';
	$table->titlesrows = array(30);
    $table->titles = array();
    $table->titles[] = get_string('administratives', 'block_mou_school');
    $table->downloadfilename = 'itogi_'.$sid.'_'.$gid;
    $table->worksheetname = $table->downloadfilename;

	$class = get_record('monit_school_class', 'id', $gid);
	$class_termtype = get_record('monit_school_class_termtype', 'schoolid', $sid, 'parallelnum', $class->parallelnum);
	$school_terms = get_records_select('monit_school_term', "schoolid = $sid AND  termtypeid = {$class_termtype->termtypeid}");

    $studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, m.classid
                        FROM {$CFG->prefix}user u
                   LEFT JOIN {$CFG->prefix}monit_school_pupil_card m ON m.userid = u.id
				   WHERE classid = $gid AND u.deleted = 0 AND u.confirmed = 1
				   ORDER BY u.lastname, u.firstname";

    if($students = get_records_sql($studentsql)) {
        
        $countpupils = count ($students);
        $table->countpupils = $countpupils;
         
		if(!$classdisciplines){
			notify(get_string('class_doesnt_have_predmet','block_mou_school'), "green", 'center');
		}
        if ($acdids = get_records_select_menu('monit_school_class_discipline', "schoolid=$sid and classid=$gid", '', 'id as id1, id as id2'))   {
            $cdids = implode (',', $acdids); 
        } else {
            $cdids = 0;
        }
        
        
        if ($isforclassteacher) {
        	   $sql ="create temporary table mdl_temp_marks0
                      SELECT marks.id, marks.userid, sched.classdisciplineid, marks.mark
                      FROM mdl_monit_school_class_schedule_{$rid} sched 
                      inner join mdl_monit_school_marks_{$rid} marks on sched.id=marks.scheduleid
                      WHERE classdisciplineid in ($cdids) and sched.datestart >= '$school_term->datestart'  AND sched.datestart <= '$school_term->dateend' 
                      ORDER BY datestart";
                // print $sql . '<br />';                 
                execute_sql($sql, false);        
        
                $sql = "insert into mdl_temp_marks0 SELECT marks.id, marks.userid, sched.classdisciplineid, marks.mark2 as mark
                        FROM mdl_monit_school_class_schedule_{$rid} sched
                        inner join mdl_monit_school_marks_{$rid} marks on sched.id=marks.scheduleid
                        WHERE classdisciplineid in ($cdids) and sched.datestart >= '$school_term->datestart' AND sched.datestart <= '$school_term->dateend' and marks.mark2>0";
                // print $sql . '<br />';                
                execute_sql($sql, false);
                
                $sql = "create temporary table mdl_temp_marks
                        select id, userid, classdisciplineid, round(avg(mark), 2) as mark
                        from mdl_temp_marks0
                        group by userid, classdisciplineid";
                // print $sql . '<br />';                
                execute_sql($sql, false);

        } else {   
        
            if ($termid == -1)  {
                $sql = "create temporary table mdl_temp_marks
                        SELECT id, userid, classdisciplineid, avg(mark) as mark
                        FROM mdl_monit_school_marks_totals_term m
                        where classdisciplineid in ($cdids)
                        group by userid, classdisciplineid";
            } else {
                /*
                $sql = "create temporary table mdl_temp_marks
                        SELECT id, userid, classdisciplineid, mark
                        FROM mdl_monit_school_marks_totals_term m
                        where schoolid=$sid and termid=$termid";
                */
                
                $sql = "create temporary table mdl_temp_marks
                        SELECT id, userid, classdisciplineid, mark FROM mdl_monit_school_marks_totals_term
                        where termid=$termid and classdisciplineid in ($cdids)";
                        
            }
            execute_sql($sql, false);
           // print $sql . '<br />';
        }
        
        // $countdisclass = count($classdisciplines);
                
        $kolmarks = array();
    	$i=1;

        if($classdisciplines) foreach ($classdisciplines as $discip)	{
		    $kolmarks[$discip->id] = array(0,0,0,0,0,0);  // // количество оценок с 0, 1, 2, 3, 4, 5
        }    
        $pupilmarks = array();
        $rating = array();
        $neuspevat = array();
        $table->nomark = 0;
        foreach ($students as $student) {
            $pupilmarks[$student->id] = array();
            if($classdisciplines){
                $summark = 0;
                $countdis = 0;
 				foreach ($classdisciplines as $discip)	{
                    $sql = "SELECT mark FROM mdl_temp_marks WHERE userid={$student->id} and classdisciplineid={$discip->id}"; 				   
					if ($mark = get_record_sql($sql))	{
					    if ($mark->mark <= 3) $neuspevat[$student->id] = 3;
                        $pupilmarks[$student->id][$discip->id] = $mark->mark;
                        $summark += $mark->mark;
                        $countdis++;
                    }
                }
                // средний балл ученика
                if ($summark > 0)   {
                    $pupilmarks[$student->id]['sred'] = round ($summark/$countdis, 2);
                } else {
                    $pupilmarks[$student->id]['sred'] = 0;
                    $table->nomark++;
                }
                $rating[$student->id] = $pupilmarks[$student->id]['sred'];                
             }
        }   
        
		arsort($rating);        
		reset($rating);
		$maxmark = current($rating);
		// echo $maxsm; 
		$placerating = array();
		$mesto = 1;
		foreach ($rating as $studentid => $pupilmark) {
    		if ($pupilmark == $maxmark)	{
    			$placerating[$studentid] = $mesto;
    		} else {
    			$placerating[$studentid] = ++$mesto;
    			$maxmark = $pupilmark ; 
    		}	 
		}	                     
            
        
		foreach ($students as $student){
			$tabledata = array($i);
			$tabledata[] = "<div align=left><strong><a href=\"{$CFG->wwwroot}/blocks/mou_school/class/pupilcard.php?mode=4&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid={$student->id}\">".fullname($student)."</a></strong></div>";

            if($classdisciplines){
 				foreach ($classdisciplines as $discip)	{
					$strmark = '';
 				    /*
                    $sql = "SELECT mark FROM mdl_temp_marks WHERE userid={$student->id} and classdisciplineid={$discip->id}"; 				   
					if ($mark = get_record_sql($sql))	{
						$strmark = $mark->mark;
                        $summark += $mark->mark;
                        $kolmarks[$discip->id][$mark->mark]++;
					}
                    */
                    if (isset($pupilmarks[$student->id][$discip->id]))  {
                        $mark = $pupilmarks[$student->id][$discip->id];
						$strmark = $mark;
                        if ($isforclassteacher) {
                            $tempmark = round ($mark);
                            $kolmarks[$discip->id][$tempmark]++;
                            $strmark = str_replace('.', ',', $strmark);
                        } else {    
                            $kolmarks[$discip->id][$mark]++;
                        }    
                    } 
					$tabledata[] = $strmark;
				}
                // средний балл ученика
                if (isset($pupilmarks[$student->id]['sred']))  {
                    $tabledata[] = str_replace('.', ',', $pupilmarks[$student->id]['sred']);
                } else {
                    $tabledata[] = '';
                }
                // рейтинг 
                $tabledata[] = $placerating[$student->id];
            }				
            $i++;
            $table->data[] = $tabledata;
		}

        $tabledatasred = array('', '<b>Средний балл по предмету</b>');
        $tdatakachestvo = array('', '<b>% кач. зн. по предмету</b>');
        $tdataSOU = array('', '<b>СОУ(%) по предмету</b>');
        // print_object($kolmarks);
        $sumsred = $sumkachestvo = $sumSOU = 0;
        foreach ($classdisciplines as $discip)	{
            // средний балл по предмету
            // $countpupils =  array_sum($kolmarks); 
            if ($countpupils > 0)   {
                $summark = 0;
                foreach ($kolmarks[$discip->id] as $i => $m) {
                    $summark += $i*$m;
                }

                $sred = round($summark/$countpupils, 2);
                $sumsred += $sred;
                $tabledatasred[] = '<b>'.str_replace('.', ',', $sred).'</b>';
                
                // % кач. зн. по предмету
                $kachestvo = calculate_kachestvo($kolmarks[$discip->id], $countpupils);
                $sumkachestvo += $kachestvo;  
                $tdatakachestvo[] = '<b>'.str_replace('.', ',', $kachestvo).'</b>';
                
                // СОУ (%)                                        
                $SOU = calculate_SOU($kolmarks[$discip->id], $countpupils);
                $sumSOU += $SOU; 
                $tdataSOU[] = '<b>'.str_replace('.', ',', $SOU).'</b>';
            } else {
                $tabledatasred[] = $tdatakachestvo[] = $tdataSOU[] = ''; 
            } 
        }
        $table->allsrednee = round($sumsred/$countdis, 2);
        $tabledatasred[] = '<b>'.str_replace('.', ',', $table->allsrednee).'</b>';
        
        $table->allkachestvo =  round($sumkachestvo/$countdis, 2);
        $tdatakachestvo[] = '<b>'.str_replace('.', ',', $table->allkachestvo).'</b>';
        
        $table->allSOU = round($sumSOU/$countdis, 2);
        $tdataSOU[] = '<b>'.str_replace('.', ',', $table->allSOU).'</b>';
        
        // print_object($neuspevat);     
        $table->alluspevemost = round (($countpupils - count($neuspevat))/$countpupils * 100, 2);  
    
        $tabledatasred[] = $tdatakachestvo[] = $tdataSOU[] = '';
        $table->data[] = $tabledatasred;
        $table->data[] = $tdatakachestvo;
        $table->data[] = $tdataSOU;
            
        /*
        $tabledata = array('', '<b>Средний балл по предмету</b>');
		foreach ($classdisciplines as $discip)	{
            $sql = "SELECT round(avg(mark), 2) as sred FROM mdl_temp_marks WHERE classdisciplineid={$discip->id}"; 				   
			$strmark = '';
			if ($mark = get_record_sql($sql))	{
				$strmark = $mark->sred;
			} 
			$tabledata[] = '<b>'.$strmark.'</b>';
		}
        $tabledata[] = '';
        $tabledata[] = '';
        $table->data[] = $tabledata;
        */
        
	}
    
    return $table;
}


function table_class_performance_dop($yid, $rid, $sid, $gid, $termid)
{
	global $CFG;

    $table = new stdClass();
	$table->head  = array ('Успеваемость', 'Количество', '% в классе', 'ФИО');
	$table->align = array ('left', 'center', 'center', 'left');
	
    // вариант посторения таблицы по итоговым оценкам
     if ($acdids = get_records_select_menu('monit_school_class_discipline', "schoolid=$sid and classid=$gid", '', 'id as id1, id as id2'))   {
            $cdids = implode (',', $acdids); 
     } else {
            $cdids = 0;
     }
     
     $sql = "create temporary table mdl_temp1
     SELECT userid, mark
     FROM mdl_monit_school_marks_totals_term m
     where classdisciplineid in ($cdids)";
     // print $sql . '<br />';                
     execute_sql($sql, false);
     
    // вариант посторения таблицы по всем полученным оценкам
    /*
    $sql = "create temporary table mdl_temp1
            SELECT marks.userid, marks.mark
            FROM mdl_monit_school_class_schedule_{$rid} sched
            inner join mdl_monit_school_marks_{$rid} marks on sched.id=marks.scheduleid
            where sched.classid=$gid and marks.mark>0";
    // print $sql . '<br />';                
    execute_sql($sql, false);        

    $sql = "insert into mdl_temp1 SELECT marks.userid, marks.mark2 as mark
            FROM mdl_monit_school_class_schedule_{$rid} sched
            inner join mdl_monit_school_marks_{$rid} marks on sched.id=marks.scheduleid
            where sched.classid=$gid and marks.mark2>0";
    // print $sql . '<br />';                
    execute_sql($sql, false);
    */
   $studentsql = "SELECT u.id, u.username, u.firstname, u.lastname
                        FROM {$CFG->prefix}user u
                   LEFT JOIN {$CFG->prefix}monit_school_pupil_card m ON m.userid = u.id
				   WHERE classid = $gid AND u.deleted = 0 AND u.confirmed = 1
				   ORDER BY u.lastname, u.firstname";

    if($students = get_records_sql($studentsql)) {

        $countpupils = count ($students);
        $table->countpupils = $countpupils;
        
        $table->five = array();
        $table->four = array();
        $table->four1 = array();
        $table->three = array();
        $table->three1 = array();
        $table->two = array();
        
        foreach ($students as $student) {            
            $minmark = get_field_sql("select min(mark) as minmark from mdl_temp1 where userid=$student->id");
            
            switch ($minmark)   {
                case 5: $table->five[$student->id] = fullname($student);
                break;
                
                case 4: $table->four[$student->id] = fullname($student);
                        $count4 = get_field_sql("select count(mark) as countmark from mdl_temp1 where userid=$student->id and mark=4");
                        if ($count4 == 1)   {
                            $table->four1[$student->id] = fullname($student);
                        }
                break;

                case 3: $table->three[$student->id] = fullname($student);
                        $count3 = get_field_sql("select count(mark) as countmark from mdl_temp1 where userid=$student->id and mark=3");
                        if ($count3 == 1)   {
                            $table->three1[$student->id] = fullname($student);
                        }
                break;

                case 2: $table->two[$student->id] = fullname($student);
                break;
            }
        } 
        
        $table->data[] = array('Отличники (всего)', count($table->five), round (count($table->five)/$countpupils*100, 2) . '%', implode(',<br />', $table->five));   
        $table->data[] = array('Хорошисты (с одной "4")', count($table->four1), round (count($table->four1)/$countpupils*100, 2) . '%', implode(',<br />', $table->four1));
        $table->data[] = array('Хорошисты (всего)', count($table->four), round (count($table->four)/$countpupils*100, 2) . '%', implode(',<br />', $table->four));
        $table->data[] = array('Успевающие (с одной "3")', count($table->three1), round (count($table->three1)/$countpupils*100, 2) . '%', implode(',<br />', $table->three1));
        $table->data[] = array('Успевающие (всего)', count($table->three), round (count($table->three)/$countpupils*100, 2) . '%', implode(',<br />', $table->three));
        $table->data[] = array('Неуспевающие (всего)', count($table->two), round (count($table->two)/$countpupils*100, 2) . '%', implode(',<br />', $table->two));
    }       
    return $table;
}


function table_pupil_performance($yid, $rid, $sid, $gid, $termid, $uid)
{
	global $CFG;

	$school_term = get_record_select('monit_school_term', "id=$termid" , 'id, datestart, dateend'); // приходит с URL
    
	$table->head  = array (get_string('ordernumber','block_mou_school'), get_string('disciplines','block_mou_school'), 'Оценки', 
                           'Средний балл', 'Опоздания', 'Пропуски<br />(Всего)', 'Пропуски <br />(по болезни)', 'Итог');
	$table->align = array ('center', 'left', 'left', 'center', 'center', 'center', 'center', 'center');
    $table->size = array ('3%', '40%', '20%');
	$table->columnwidth = array (7, 30);
	
	if ($classdisciplines = get_records_sql("SELECT id, schoolid, classid, schoolsubgroupid, disciplineid, teacherid, name
                                             FROM {$CFG->prefix}monit_school_class_discipline
										     WHERE classid=$gid
                                             ORDER BY name")){
        
        if ($acdids = get_records_select_menu('monit_school_class_discipline', "classid=$gid", '', 'id as id1, id as id2'))   {
            $cdids = implode (',', $acdids); 
        } else {
            $cdids = 0;
        }

	   $sql ="create temporary table mdl_temp_attendance
              SELECT a.id, sched.classdisciplineid, a.reason
              FROM mdl_monit_school_class_schedule_{$rid} sched 
              inner join mdl_monit_school_attendance_{$rid} a on sched.id=a.scheduleid
              WHERE a.userid=$uid AND sched.datestart >= '$school_term->datestart'  AND sched.datestart <= '$school_term->dateend' 
             ";

        // print $sql . '<br />';                 
        execute_sql($sql, false);        
    

	   $sql ="create temporary table mdl_temp_marks
              SELECT marks.id, sched.classdisciplineid, marks.mark, marks.mark2
              FROM mdl_monit_school_class_schedule_{$rid} sched 
              inner join mdl_monit_school_marks_{$rid} marks on sched.id=marks.scheduleid
              WHERE marks.userid=$uid AND sched.datestart >= '$school_term->datestart'  AND sched.datestart <= '$school_term->dateend' 
              ORDER BY datestart";

        // print $sql . '<br />';                 
        execute_sql($sql, false);        

/*
        $sql = "insert into mdl_temp_marks SELECT marks.id, sched.classdisciplineid, marks.mark2 as mark
                FROM mdl_monit_school_class_schedule_{$rid} sched
                inner join mdl_monit_school_marks_{$rid} marks on sched.id=marks.scheduleid
                WHERE marks.userid=$uid AND  sched.classdisciplineid
                      sched.datestart >= '$school_term->datestart' AND sched.datestart <= '$school_term->dateend' and marks.mark2>0";
        print $sql . '<br />';                
        execute_sql($sql, false);                
*/   
        $i = 1;  
        $uatendancesum = $vatendancesum = $oatendancesum = 0;  
                                                      
		foreach ($classdisciplines as $classdiscipline) {
		      $strmark = '';
		      if ($marks1 = get_record_sql("select id, group_concat(mark) as marks from mdl_temp_marks
                                         where classdisciplineid={$classdiscipline->id} and mark>0 and mark2=0"))    {
                    $strmark .= str_replace (',', ', ', $marks1->marks);
              }                                                                                
		      if ($marks2 = get_record_sql("select id, group_concat(mark, '/', mark2) as marks from mdl_temp_marks
                                         where classdisciplineid={$classdiscipline->id} and mark>0 and mark2>0"))    {
                    // print_object($marks2);
                    if (!empty($marks2->marks)) {                                               
                        $strmark .= ', '. str_replace (',', ', ', $marks2->marks);
                        $sql = "insert into mdl_temp_marks SELECT marks.id, sched.classdisciplineid, marks.mark2 as mark, 0 as mark2
                                FROM mdl_monit_school_class_schedule_{$rid} sched
                                inner join mdl_monit_school_marks_{$rid} marks on sched.id=marks.scheduleid
                                WHERE marks.userid=$uid AND  sched.classdisciplineid={$classdiscipline->id} AND
                                      sched.datestart >= '$school_term->datestart' AND sched.datestart <= '$school_term->dateend' and marks.mark2>0";
                        // print $sql . '<br />';                
                        execute_sql($sql, false);                
                    }    
              }
              $avg =  get_field_sql("select ROUND(avg(mark), 2) as a from mdl_temp_marks where classdisciplineid={$classdiscipline->id} and mark>0");                                                                                              
		
              $oatendance = get_field_sql("SELECT count(id) as cnt FROM mdl_temp_attendance m
                                            WHERE classdisciplineid={$classdiscipline->id} and (reason='о' or reason='О')");
              
              $uatendance =  get_field_sql("SELECT count(id) as cnt FROM mdl_temp_attendance m
                                            WHERE classdisciplineid={$classdiscipline->id} and (reason='у' or reason='У')");

              $vatendance =  get_field_sql("SELECT count(id) as cnt FROM mdl_temp_attendance m
                                            WHERE classdisciplineid={$classdiscipline->id}");
                                            
              $itog = get_field_sql("SELECT mark FROM mdl_monit_school_marks_totals_term
                                      where userid = $uid and termid=$termid and classdisciplineid={$classdiscipline->id}");                                             
        	
              $table->data[] = array($i++, $classdiscipline->name, $strmark, $avg, $oatendance, $vatendance, $uatendance, $itog);
              $uatendancesum += $uatendance;
              $vatendancesum += $vatendance;
              $oatendancesum += $oatendance; 
              
		}
        
        $table->data[] = array('', '<b>Итого:</b>', '', '', '<b>'.$oatendancesum.'</b>', '<b>'.$vatendancesum.'</b>', '<b>'.$uatendancesum.'</b>', '');
	}
    
    return $table;    
}    



function listbox_teachers_in_discipline($scriptname, $rid, $sid, $did, $teachid)
{
  global $CFG;

  $strtitle = get_string('selectateacher', 'block_mou_school') . '...';
  $teachersmenu = array();
  $teachersmenu[0] = $strtitle;

  if ($rid != 0 && $sid != 0)  {

       $teachersql = "SELECT u.id, u.firstname, u.lastname, u.email
                        FROM {$CFG->prefix}monit_school_teacher t
    	            		LEFT JOIN  {$CFG->prefix}user u ON t.teacherid = u.id
    	            		WHERE t.schoolid=$sid and t.disciplineid=$did
				   			ORDER BY  u.lastname";
	   if ($steachers = get_records_sql($teachersql))  {
	  		foreach ($steachers as $steach) {
	  		//	$name = truncate_school_name($school->name);
				$teachersmenu[$steach->id] = fullname($steach);
			}
       }
  }

  echo '<tr><td>'.get_string('teacher','block_mou_school').':</td><td>';
  popup_form($scriptname, $teachersmenu, "teachid", $teachid, "", "", "", false);
  echo '</td></tr>';
  return 1;
}


function listbox_all_school_periods($scriptname, $sid, $yid, $termid)
{
  global $CFG;

  $strtitle = get_string('selectperiod', 'block_mou_school') . ' ...';
  $termmenu = array();
  $termmenu[0] = $strtitle;
  $termmenu[-1] = 'Весь учебный год';
  

  if ($sid != 0 && $yid != 0)   {
        if ($school_terms = get_records_select('monit_school_term', "schoolid = $sid", '', 'id, name, datestart, dateend')) 	{
			foreach ($school_terms as $st) {
				$termmenu[$st->id] =$st->name;
			}
		} else {
  			echo '<tr><td>'.get_string('studyperiod','block_mou_school').':</td><td>';
			print_string('notfoundtypestudyperiod', 'block_mou_school');
			echo '</td></tr>';
		    return 0;
		}	  
  }

  echo '<tr><td>'.get_string('studyperiod','block_mou_school').':</td><td>';
  popup_form($scriptname, $termmenu, 'switcterm', $termid, '', '', '', false);
  echo '</td></tr>';
  return 1;
}


function table_teacher_predmet($yid, $rid, $sid, $did, $teachid, $termid)
{
	global $CFG;

    $starttermid = $termid;
    
    $table->dblhead->head1  = array (get_string ('class','block_mou_school'), get_string ('periods','block_mou_school'),
                                    get_string ('numofstudents','block_mou_school'), 'Успеваемость',
                                    'Средний балл', '% успев.',  '% кач. зн', 'СОУ (%)'); // 8
    $table->dblhead->span1  = array ("rowspan=2", "rowspan=2", "rowspan=2", "colspan=9", "rowspan=2", "rowspan=2", "rowspan=2", "rowspan=2"); // 8
	$table->align = array ('center', 'center', 'center', 'center', 'center', 'center', 'center', 'center');
	$table->columnwidth = array (8, 13, 10, 4, 4, 3, 5, 4, 4, 3, 3, 7, 10, 10, 10, 10);
	//$table->dblhead->size = array ('25%','25%','15%','15%','15%','15%','15%','15%');
	$table->wraphead = 'nowrap';

    $table->dblhead->head2 = array('Отл', 'Хор', 'Уд', 'Неуд',	'Н/А', 'ОСВ', 'ЗЧ',	'НЗ', 'Нет оценки');
    for ($i=1; $i<=9; $i++)  {
		$table->align[] = 'center';
		$table->size[] = '5%';
		// $table->columnwidth[] = 10;
    }
    
    $table->class = 'moutable';
   	// $table->width = '95%';

	$table->titles = array();
    $table->titles[] = 'Отчет: Учителю по предмету';
    $table->titlesrows = array(30);
    $table->worksheetname = $yid;
    $table->downloadfilename = 'teacher_predmet'.$rid.'_'.$sid.'_'.$teachid.'_'.$did.'.doc'; 

    $sql = "create temporary table mdl_temp_marks
            SELECT id, userid, classdisciplineid, termid, mark FROM mdl_monit_school_marks_totals_term
            where schoolid=$sid";
    execute_sql($sql, false);

    $sql = "SELECT cd.id, cd.classid, sc.name, sc.parallelnum 
            FROM mdl_monit_school_class_discipline cd
            inner join mdl_monit_school_class sc on sc.id=cd.classid
            where cd.schoolid=$sid and cd.teacherid=$teachid and cd.disciplineid=$did
            order by sc.parallelnum";
            
    // print $sql . '<br />';
    if ($classes = get_records_sql($sql))   {
        
        foreach ($classes  as $class)   {
            
            $gid = $class->classid;
            
 			$countpupils = count_records('monit_school_pupil_card', 'classid',  $gid, 'deleted', 0);

            /*
            if ($acdids = get_records_select_menu('monit_school_class_discipline', "schoolid=$sid and classid=$gid", '', 'id as id1, id as id2'))   {
                $cdids = implode (',', $acdids); 
            } else {
                $cdids = 0;
            }
            */
            if (!$cdids = get_field_select('monit_school_class_discipline', 'id', "schoolid=$sid and classid=$gid and disciplineid=$did"))   {
                $cdids = 0;
            }    
            
            if ($starttermid == 0)   {
                $sql ="SELECT st.id, st.name, ct.termtypeid FROM mdl_monit_school_class_termtype ct
                       inner join mdl_monit_school_term st using (schoolid,termtypeid)
                       where ct.schoolid=$sid  and ct.parallelnum=$class->parallelnum 
                       order by st.name";
                // $terms =  get_records_sql($sql);                       
                $schoolterms =  get_records_sql($sql);
                $terms = array();
                $terms[] = reset($schoolterms); 
            } else {
                $termtypeid = get_field_select('monit_school_class_termtype', 'termtypeid', "schoolid=$sid and parallelnum=$class->parallelnum");                
                $terms = array();
                if ($termid > 0)    {
                    $term = get_record_select('monit_school_term', "id=$termid");
                    if ($term->termtypeid == $termtypeid) {
                        $terms[] = $term;    
                    }
                } else {
                    $schoolterms = get_records_select('monit_school_term', "schoolid=$sid");
                    foreach ($schoolterms as $sterm)    {
                        if ($sterm->termtypeid == $termtypeid) {
                            $terms[] = $sterm;    
                        }
                    }
                }     
            }
            
            $currclassid = 0;//$class->id;
            
            foreach ($terms as $term)   {
                
                if ($currclassid == $class->id) {
                    $classname = '';
                } else {
                    $classname = $class->name;
                    $currclassid = $class->id;
                }    
                $tabledata = array ($classname, $term->name, $countpupils);
                            
                $kolmarks = array(0,0,0,0,0,0); 
                for ($i=5; $i>=2; $i--)  {
                    $sql = "SELECT count(mark) as countmark FROM mdl_temp_marks 
                            where mark=$i and termid=$term->id and classdisciplineid in ($cdids)";
                    // print $sql . '<br />';        
                    $kolmarks[$i] = get_field_sql ($sql);
                    $tabledata[] = $kolmarks[$i];
                }    
                $tabledata[] = 0;
                $tabledata[] = 0;
                $tabledata[] = 0;
                $tabledata[] = 0;
                $summark = array_sum($kolmarks);
                $tabledata[] = $countpupils - $summark;
                $summarks = 0;
                foreach ($kolmarks as $i => $m) {
                    $summarks += $i*$m;
                }
                if ($summark > 0)   {
                    $tabledata[] = str_replace('.', ',', round ($summarks / $summark, 2));
                } else {
                    $tabledata[] = 0;
                }    
                $procent = round ((($kolmarks[5] + $kolmarks[4] + $kolmarks[3]) / $countpupils *100), 2);
                
                $tabledata[] = str_replace('.', ',', $procent);
                
                // % кач. зн. по предмету
                $kachestvo = calculate_kachestvo($kolmarks, $countpupils);
                $tabledata[] = str_replace('.', ',', $kachestvo);
                    
                // СОУ (%)                                        
                $SOU = calculate_SOU($kolmarks, $countpupils);
                $tabledata[] = str_replace('.', ',', $SOU);
                
                $table->data[] = $tabledata;
            }
        }
        
    }        
    return $table;
}



?>