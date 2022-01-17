<?php // $Id: statistics.php,v 1.1 2014/06/03 06:41:47 shtifanov Exp $

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

    if ($action == 'excel') 	{
        switch ($rpid)  {
            case 8: $table = table_school_statistics($yid, $rid, $sid, $termid);
                    print_table_to_excel($table);
                    exit();
 
        }
    }
    
    $currenttab = 'statistics';
    include('tabsreports.php');
    
    // notice(get_string('vstadii', 'block_mou_att'), "../index.php?rid=$rid&yid=$yid&sid=$sid");

	if (has_capability('block/mou_school:viewreports', $context))	{
		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		listbox_statistics_reports("$scriptname?rid=$rid&yid=$yid&sid=$sid&rpid=", $rid, $sid, $yid, $rpid);
		switch ($rpid){
            // Статистика оценок: Школа			
			case '8':
                    listbox_all_school_periods("$scriptname?rid=$rid&sid=$sid&yid=$yid&rpid=$rpid&did=$did&teachid=$teachid&termid=", $sid, $yid, $termid);
    				echo '</table>';
                    if ($termid > 0)   {
    					$table = table_school_statistics($yid, $rid, $sid, $termid);
    					print_color_table($table);
                        $options = array('action' => 'excel', 'rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'rpid' => $rpid, 'termid' => $termid);
        				echo '<p></p><table align="center" border=0><tr><td>';
        				print_single_button("$scriptname", $options, get_string("downloadexcel"));
        			   	echo '</td><td></table>';
                    }       			
			break;             
                                                                                        

			// Статистика оценок: Класс
            case '9':
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
                        listbox_terms("$scriptname?rid=$rid&sid=$sid&yid=$yid&rpid=$rpid&gid=$gid&termid=", $sid, $yid, $gid, $termid, true); // , true
						// listbox_report_period("$scriptname?rid=$rid&sid=$sid&yid=$yid&rpid=$rpid&gid=$gid&perid=", $perid);
    					echo '</table>';
                        
                        if ($termid != 0)   {
                        
                            $table = table_class_statistics($yid, $rid, $sid, $gid, $termid);
                            print_color_table($table);
                            
                            $options = array('action' => 'word', 'rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'rpid' => $rpid, 'gid' => $gid, 'termid' => $termid);
            				echo '<p></p><table align="center" border=0><tr><td>';
            				print_single_button("$scriptname", $options, get_string("downloadexcel"));
            			   	echo '</td><td></table>';
                        }    
                   }        			
			break;                                    

			// Статистика оценок: Класс
            case '10':
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
                        listbox_terms("$scriptname?rid=$rid&sid=$sid&yid=$yid&rpid=$rpid&gid=$gid&termid=", $sid, $yid, $gid, $termid, true); // , true
						// listbox_report_period("$scriptname?rid=$rid&sid=$sid&yid=$yid&rpid=$rpid&gid=$gid&perid=", $perid);
    					echo '</table>';
                        
                        if ($termid != 0)   {
                        
                            $table = table_predmet_statistics($yid, $rid, $sid, $gid, $termid);
                            print_color_table($table);
                            
                            $options = array('action' => 'word', 'rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'rpid' => $rpid, 'gid' => $gid, 'termid' => $termid);
            				echo '<p></p><table align="center" border=0><tr><td>';
            				print_single_button("$scriptname", $options, get_string("downloadexcel"));
            			   	echo '</td><td></table>';
                        }    
                   }        			
			break;                                    

		}
	
	    echo '</table>';
	} else {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}

    print_footer();



function table_school_statistics($yid, $rid, $sid, $termid)
{
    global $CFG;
    
    if ($termid > 0)    {
        $school_term = get_record_select('monit_school_term', "id=$termid" , 'id, datestart, dateend');
        $strselect = " AND sched.datestart >= '$school_term->datestart'  AND sched.datestart <= '$school_term->dateend'";
    }  else {
        $strselect = '';
    }    
    
	$table->head  = array (get_string('class','block_mou_school'), get_string('numofstudents','block_mou_school'),
                            '5', '4', '3', '2', '% кач. зн. класса', 'Рейтинг');
	$table->align = array ('center', 'center', 'center', 'center', 'center', 'center', 'center', 'center');
    // $table->size = array ('20%', '30%', '15%', '25%', '10%');
	$table->columnwidth = array (7, 12, 5, 5, 5, 5, 8, 10);
    $table->class = 'moutable';
	$table->titlesrows = array(30);
    $table->titles = array();
    $table->titles[] = 'Статистика оценок: Школа';
    $table->downloadfilename = 'school_statistics'. $sid;
    $table->worksheetname = $table->downloadfilename;
    
    $sql = "SELECT id, name  FROM {$CFG->prefix}monit_school_class WHERE yearid=$yid AND schoolid=$sid ORDER BY parallelnum, name";               
	if ($classes = get_records_sql_menu($sql)) {
  
        $sql = "create temporary table mdl_temp1
                SELECT sched.schoolid, sched.classid, marks.userid, marks.mark
                FROM mdl_monit_school_class_schedule_{$rid} sched
                inner join mdl_monit_school_marks_{$rid} marks on sched.id=marks.scheduleid
                where sched.schoolid=$sid and marks.mark>0 $strselect";
        // print $sql . '<br />';                
        execute_sql($sql, false);        

        $sql = "insert into mdl_temp1 SELECT sched.schoolid, sched.classid, marks.userid, marks.mark2 as mark
                FROM mdl_monit_school_class_schedule_{$rid} sched
                inner join mdl_monit_school_marks_{$rid} marks on sched.id=marks.scheduleid
                where sched.schoolid=$sid and marks.mark2>0 $strselect";
        // print $sql . '<br />';                
        execute_sql($sql, false);                

        $sql = "create temporary table mdl_temp_count_mark
                select classid, mark, count(mark) as countmark from mdl_temp1
                group by classid, mark";
        execute_sql($sql, false);
        // print $sql . '<br />';
       
       $marks = array();
       $rating = array();
       $kachestvo = array();
       $countpupils = array();
	   foreach ($classes as $classid => $classname)    {
	       $countpupils[$classid] = count_records('monit_school_pupil_card', 'classid',  $classid, 'deleted', 0);
           /*
           $pupils = get_records_select_menu('monit_school_pupil_card', "classid=$classid and deleted=0", '', 'id, userid');
           $kolpupil = array(0,0,0,0,0,0);
           foreach ($pupils as $userid) {
                 if ($minmark = get_field_sql("select min(mark) as minmark from mdl_temp1 where userid=$userid"))   {
                    $kolpupil[$minmark]++;
                 }   
           }
           $kachestvo[$classid] = calculate_kachestvo($kolpupil, $countpupils[$classid]);
           */
           for ($i=5; $i>=2; $i--)  {
                $marks[$classid][$i] = get_field_select('temp_count_mark', 'countmark', "classid=$classid and mark = $i");
           }
           $nummarks = array_sum($marks[$classid]);
           if ($nummarks > 0)   {
                $kachestvo[$classid] = round (($marks[$classid][5]+$marks[$classid][4])/$nummarks*100, 2);
           } else {
                $kachestvo[$classid] = ''; 
           } 
           $rating[$classid] = $kachestvo[$classid];
       }     

		arsort($rating);        
		reset($rating);
		$maxmark = current($rating);
		$placerating = array();
		$mesto = 1;
		foreach ($rating as $classid => $pupilmark) {
    		if ($pupilmark == $maxmark)	{
    			$placerating[$classid] = $mesto;
    		} else {
    			$placerating[$classid] = ++$mesto;
    			$maxmark = $pupilmark ; 
    		}	 
		}	                	       
       
	   foreach ($classes as $classid => $classname)    {
           // print $classname . '<br />'; 
	       $tabledata = array($classname, $countpupils[$classid]);
           for ($i=5; $i>=2; $i--)  {
                $tabledata[] = $marks[$classid][$i];
           }
           $tabledata[] = $kachestvo[$classid];
           $tabledata[] = $placerating[$classid];
           $table->data[] = $tabledata;  
       }
    }        
                
    return $table;
}



function table_class_statistics($yid, $rid, $sid, $gid, $termid)
{
	global $CFG;
    
	$table->head  = array ('№', get_string('class','block_mou_school'), 
                            '5', '4', '3', '2', '% кач. зн. класса', 'Рейтинг');
	$table->align = array ('center', 'left', 'center', 'center',  'center', 'center', 'center', 'center');
    // $table->size = array ('20%', '30%', '15%', '25%', '10%');
	$table->columnwidth = array (5, 7, 5, 5, 5, 5, 8, 10);
    $table->class = 'moutable';
	$table->titlesrows = array(30);
    $table->titles = array();
    $table->titles[] = 'Статистика оценок: Класс';
    $table->downloadfilename = 'class_statistics'. $sid . '_' . $gid;
    $table->worksheetname = $table->downloadfilename;
	

    if ($termid > 0)    {
        $school_term = get_record_select('monit_school_term', "id=$termid" , 'id, datestart, dateend');
        $strselect = " AND sched.datestart >= '$school_term->datestart'  AND sched.datestart <= '$school_term->dateend'";
    }  else {
        $strselect = '';
    }    
    
    $sql = "create temporary table mdl_temp1
            SELECT sched.schoolid, sched.classid, marks.userid, marks.mark
            FROM mdl_monit_school_class_schedule_{$rid} sched
            inner join mdl_monit_school_marks_{$rid} marks on sched.id=marks.scheduleid
            where sched.schoolid=$sid and sched.classid=$gid and marks.mark>0 $strselect";
    // print $sql . '<br />';                
    execute_sql($sql, false);        
    
    $sql = "insert into mdl_temp1 SELECT sched.schoolid, sched.classid, marks.userid, marks.mark2 as mark
            FROM mdl_monit_school_class_schedule_{$rid} sched
            inner join mdl_monit_school_marks_{$rid} marks on sched.id=marks.scheduleid
            where sched.schoolid=$sid and sched.classid=$gid and marks.mark2>0 $strselect";
    // print $sql . '<br />';                
    execute_sql($sql, false);                
    
    $sql = "create temporary table mdl_temp_count_mark
            select userid, mark, count(mark) as countmark from mdl_temp1
            group by userid, mark";
    execute_sql($sql, false);
    // print $sql . '<br />';
        
    $studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, m.classid
                        FROM {$CFG->prefix}user u
                   LEFT JOIN {$CFG->prefix}monit_school_pupil_card m ON m.userid = u.id
				   WHERE classid = $gid AND u.deleted = 0 AND u.confirmed = 1
				   ORDER BY u.lastname, u.firstname";

    if($students = get_records_sql($studentsql)) {
        
       
        $marks = array();
        $rating = array();
        $kachestvo = array();
        foreach ($students as $student){
           $marks[$student->id] = array();
            
           for ($i=5; $i>=2; $i--)  {
                $cntmark = get_field_select('temp_count_mark', 'countmark', "userid = $student->id and mark = $i");
                $marks[$student->id][$i] = $cntmark;
           }
           $nummarks = array_sum($marks[$student->id]);
           
           if ($nummarks > 0)   {           
                $kachestvo[$student->id] = round (($marks[$student->id][5]+$marks[$student->id][4])/$nummarks*100, 2);
           } else {
                $kachestvo[$student->id] = '';
           }     

           $rating[$student->id] = $kachestvo[$student->id];
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

        $n = 1;
		foreach ($students as $student){
			$tabledata = array($n++);
			$tabledata[] = "<div align=left><strong><a href=\"{$CFG->wwwroot}/blocks/mou_school/class/pupilcard.php?mode=4&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid={$student->id}\">".fullname($student)."</a></strong></div>";
         
           for ($i=5; $i>=2; $i--)  {
                $tabledata[] = $marks[$student->id][$i];
           }
                 
           $tabledata[] = $kachestvo[$student->id];
           $tabledata[] = $placerating[$student->id];
           $table->data[] = $tabledata;  
        }

	}
    
    return $table;
}



function table_predmet_statistics($yid, $rid, $sid, $gid, $termid)
{
	global $CFG;
    
	$table->head  = array ('№', get_string('disciplines','block_mou_school'), 
                            '5', '4', '3', '2', '% кач. зн. класса', 'Рейтинг');
	$table->align = array ('center', 'left', 'center', 'center',  'center', 'center', 'center', 'center');
    // $table->size = array ('20%', '30%', '15%', '25%', '10%');
	$table->columnwidth = array (5, 7, 5, 5, 5, 5, 8, 10);
    $table->class = 'moutable';
	$table->titlesrows = array(30);
    $table->titles = array();
    $table->titles[] = 'Статистика оценок: Предметы';
    $table->downloadfilename = 'class_statistics'. $sid . '_' . $gid;
    $table->worksheetname = $table->downloadfilename;
	

    if ($termid > 0)    {
        $school_term = get_record_select('monit_school_term', "id=$termid" , 'id, datestart, dateend');
        $strselect = " AND sched.datestart >= '$school_term->datestart'  AND sched.datestart <= '$school_term->dateend'";
    }  else {
        $strselect = '';
    }    
    
    $sql = "create temporary table mdl_temp1
            SELECT sched.schoolid, sched.classid, sched.classdisciplineid, marks.userid, marks.mark
            FROM mdl_monit_school_class_schedule_{$rid} sched
            inner join mdl_monit_school_marks_{$rid} marks on sched.id=marks.scheduleid
            where sched.schoolid=$sid and sched.classid=$gid and marks.mark>0 $strselect";
    // print $sql . '<br />';                
    execute_sql($sql, false);        
    
    $sql = "insert into mdl_temp1 SELECT sched.schoolid, sched.classid, sched.classdisciplineid, marks.userid, marks.mark2 as mark
            FROM mdl_monit_school_class_schedule_{$rid} sched
            inner join mdl_monit_school_marks_{$rid} marks on sched.id=marks.scheduleid
            where sched.schoolid=$sid and sched.classid=$gid and marks.mark2>0 $strselect";
    // print $sql . '<br />';                
    execute_sql($sql, false);                
    
    $sql = "create temporary table mdl_temp_count_mark
            select classdisciplineid, mark, count(mark) as countmark from mdl_temp1
            group by classdisciplineid, mark";
    execute_sql($sql, false);
    // print $sql . '<br />';
        
                                                                //  schoolid, classid, schoolsubgroupid, disciplineid, teacherid,
	if ($classdisciplines = get_records_sql("SELECT id, name
                                             FROM {$CFG->prefix}monit_school_class_discipline
										     WHERE classid=$gid")){
        $marks = array();
        $rating = array();
        $kachestvo = array();
        foreach ($classdisciplines as $classdiscipline)   {
           $marks[$classdiscipline->id] = array();
            
           for ($i=5; $i>=2; $i--)  {
                $cntmark = get_field_select('temp_count_mark', 'countmark', "classdisciplineid = $classdiscipline->id and mark = $i");
                $marks[$classdiscipline->id][$i] = $cntmark;
           }
           $nummarks = array_sum($marks[$classdiscipline->id]);
           
           if ($nummarks > 0)   {           
                $kachestvo[$classdiscipline->id] = round (($marks[$classdiscipline->id][5]+$marks[$classdiscipline->id][4])/$nummarks*100, 2);
           } else {
                $kachestvo[$classdiscipline->id] = '';
           }     

            $rating[$classdiscipline->id] = $kachestvo[$classdiscipline->id];
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

        $n = 1;
		foreach ($classdisciplines as $classdiscipline){
			$tabledata = array($n++);
			$tabledata[] = $classdiscipline->name;
         
           for ($i=5; $i>=2; $i--)  {
                $tabledata[] = $marks[$classdiscipline->id][$i];
           }
                 
           $tabledata[] = $kachestvo[$classdiscipline->id];
           $tabledata[] = $placerating[$classdiscipline->id];
           $table->data[] = $tabledata;  
        }

	}
    
    return $table;
}

?>

