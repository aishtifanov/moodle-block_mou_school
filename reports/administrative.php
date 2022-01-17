<?php // $Id: administrative.php,v 1.14 2012/04/02 11:54:01 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_school.php'); 
   	require_once('../authbase.inc.php');
    require_once($CFG->dirroot.'/calendar/lib.php');
	
    $rpid 	 = optional_param('rpid', 0, PARAM_INT);       // Report id
    $termid  = optional_param('tid', 0, PARAM_INT);		//Term id
    $gid 	 = optional_param('gid', 0, PARAM_INT);			//Class id
    $nyear 	 = optional_param('nyear', 0, PARAM_INT);		// Numberofyear
    $teachid = optional_param('teachid', 0, PARAM_INT);
    $mon 	 = optional_param('mon', 9, PARAM_INT);
    $tid 	 = optional_param('tid', 0, PARAM_INT);       //Term type id
    $cntid   = optional_param('cntid', -1, PARAM_INT);       //Term type id
  
    if($action == 'excel' && $rpid==1){
 		$table = table_admin_teachers_marks ($yid, $rid, $sid, $teachid, $mon);
        print_table_to_excel($table);
        exit();   	
    }else if ($action == 'excel' && $rpid==2) 	{
		$table = table_administrative ($yid, $rid, $sid, $gid);
        print_table_to_excel($table);
        exit();
    }else if($action == 'excel' && $rpid==3)		{
 		$table = table_filling ($yid, $rid, $sid, $nyear);
        print_table_to_excel($table);
        exit();       	
    }else if($action == 'excel' && $rpid==4)		{
 		$table = table_admin_teachers_itog_marks ($yid, $rid, $sid, $teachid, $tid, $mon, $cntid);
        print_table_to_excel($table);
        exit();       	
    }

    ignore_user_abort(false); // see bug report 5352. This should kill this thread as soon as user aborts.
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();
	@raise_memory_limit("512M");
 	if (function_exists('apache_child_terminate')) {
	    @apache_child_terminate();
	}    

//

    $currenttab = 'administrative';
    include('tabsreports.php');

	if (has_capability('block/mou_school:viewreports', $context))	{
	    echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	    listbox_administrative("administrative.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;rpid=", $rid, $sid, $yid, $rpid);
		
		switch ($rpid){
			case '0':
			
			break;
			case '1':
				if ($rpid != 0){
					listbox_teaches_in_school("administrative.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;rpid=$rpid&amp;mon=$mon&amp;teachid=", $rid, $sid, $teachid);
										
					if ($teachid != 0)	{
						    listbox_edu_year_months("administrative.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;rpid=$rpid&amp;teachid=$teachid&amp;mon=", $rid, $sid, $yid, $uid=0, $gid=999, $mon);
							
							if($mon!=0){
								echo '</table>';
								$table = table_admin_teachers_marks ($yid, $rid, $sid, $teachid, $mon);
								print_color_table($table);								
							}
							$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'rpid' => $rpid, 'mon'=>$mon, 'teachid'=>$teachid,'action' => 'excel');
							echo '<table align="center" border=0><tr><td>';
						    print_single_button("administrative.php", $options, get_string("downloadexcel"));
							echo '</td>';
							echo '</tr></table>'; 	
					}
				} else {
					echo '</table>';
				}
			break;
			case '2':
				if ($rpid != 0){
					
					listbox_class("administrative.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;rpid=$rpid&amp;gid=", $rid, $sid, $yid, $gid);
					echo '</table>';					
					if ($gid != 0)	{
						    $table = table_administrative ($yid, $rid, $sid, $gid);
							print_color_table($table);
						    
							$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'rpid' => $rpid, 'gid' => $gid,'action' => 'excel');
							echo '<table align="center" border=0><tr><td>';
						    print_single_button("administrative.php", $options, get_string("downloadexcel"));
							echo '</td>';
							echo '</tr></table>'; 	
					}
				} else {
					echo '</table>';
				}
			break;
			case '3':
				listbox_years("administrative.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;rpid=$rpid&amp;nyear=",$rid, $sid, $yid, $nyear);
				if ($nyear!=0){
					$table = table_filling ($yid, $rid, $sid, $nyear);
					print_color_table($table);
					
					$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'rpid' => $rpid, 'nyear' => $nyear,'action' => 'excel');
					echo '<table align="center" border=0><tr><td>';
				    print_single_button("administrative.php", $options, get_string("downloadexcel"));
					echo '</td>';
					echo '</tr></table>'; 
				}	
			break;
			case '4':
				listbox_term_type("administrative.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;nyear=$nyear&amp;rpid=$rpid&amp;tid=", $tid);
                listbox_count_teacher("administrative.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;nyear=$nyear&amp;rpid=$rpid&amp;tid=$tid&cntid=", $cntid);
                notify ('<i>Замечание: если отчет с полным списком учителей не формируется системой, надо использовать формирование отчета по частям : 1..15, 16..30 и т.д.</i>');
   				if($tid != 0 && $cntid >=0){

                    $table = table_admin_teachers_itog_marks ($yid, $rid, $sid, $teachid, $tid, $mon, $cntid);
    				print_color_table($table);
                    
                    $options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'tid' => $tid, 'cntid' => $cntid,   
                                     'rpid' => $rpid, 'mon'=>$mon, 'teachid'=>$teachid,'action' => 'excel');
                    echo '<table align="center" border=0><tr><td>';
                    print_single_button("administrative.php", $options, get_string("downloadexcel"));
                    echo '</td>';
                    echo '</tr></table>'; 
                            
   				}	
                
			break;	
		}	
		
	    echo '</table>';
	} else {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}
    
    print_footer($SITE);



function table_admin_teachers_itog_marks ($yid, $rid, $sid, $teachid, $tid, $mon, $cntid)
{
	global $CFG;

	$table->head  = array (get_string('teacher','block_mou_school'), get_string('class','block_mou_school'), 
							get_string('predmet','block_mou_school'));

                                              
	$table->align = array ('left', 'center', 'left');
    $table->size = array ('40%', '10%', '40%');
	$table->columnwidth = array (20, 20, 20);

	if($tid != 0){
	  if($terms = get_records_sql("SELECT id,name FROM {$CFG->prefix}monit_school_term
                                    WHERE schoolid=$sid and termtypeid=$tid and yearid=$yid")){
	       foreach($terms as $term){
	           $table->head[]  = $term->name;
               $table->align[] = 'center';
               $table->size[] = '10%';
               $table->columnwidth[] = 10;
	       }
	  } 
	}
    
    $table->class = 'moutable';
   	$table->width = '80%';
	$table->titlesrows = array(30);
    $table->titles = array();
    $table->downloadfilename = 'teacher_itog_marks';
    $table->worksheetname = 'all_teacher_itog_marks';
//	$tabledata = array();

    $strsql = "SELECT id, termtypeid FROM {$CFG->prefix}monit_school_term
               WHERE schoolid=$sid and termtypeid=$tid";
    // echo $strsql .'<br>';            
    $terms = get_records_sql($strsql);

    $paral_types = array();
    $strsql = "SELECT parallelnum, termtypeid  FROM mdl_monit_school_class_termtype
              WHERE schoolid=$sid";
    if ($termtypeids = get_records_sql($strsql))    {
        foreach ($termtypeids as $tt)   {
            $paral_types[$tt->parallelnum] = $tt->termtypeid;
        }   
    }    
    
    $strsql = "SELECT id, classdisciplineid, termid FROM mdl_monit_school_marks_totals_term
               WHERE schoolid=$sid";
    if (!$itogsmark = get_records_sql($strsql))    {
        notify ("<b>Итоговые оценки не найдены.<b>");
        return $table; 
    }
        
    if ($rid != 0 && $sid != 0)  {
    
       $steachersql = "SELECT u.id, u.username, u.firstname, u.lastname FROM {$CFG->prefix}user u
      	              LEFT JOIN {$CFG->prefix}monit_att_staff t ON t.userid = u.id
      	              WHERE t.schoolid=$sid AND u.deleted = 0 AND u.confirmed = 1
                      ORDER BY u.lastname";
        if ($steachers = get_records_sql($steachersql))	{
            
            //echo '<pre>'; print_r($steachers); echo '</pre>'; 
            $curr = 0; 
            $cntid15 = $cntid + 14;
      		foreach ($steachers as $steach) {
           		$curr++;
                if ($cntid >= 1 && ($curr < $cntid || $curr > $cntid15)) continue;
                $flag_teach = true;
                
                $class_name = '';
                $discip_name = '';
                /*						
                $strsql =  "SELECT d.id as did, c.name as cname, d.classid as dclassid, d.name as dname, m.id as mid 
                            FROM {$CFG->prefix}monit_school_class_discipline d
            				inner join {$CFG->prefix}monit_school_class c ON c.id=d.classid
                            inner join {$CFG->prefix}monit_school_class_termtype t ON t.parallelnum=c.parallelnum
                            inner join {$CFG->prefix}monit_school_term m ON m.termtypeid=t.id
                            where d.schoolid=$sid and d.teacherid={$steach->id} and t.termtypeid=$tid and m.yearid=$yid";                
            	*/
                /*
                $strsql = "SELECT distinct d.id as cdid, c.name as cname, d.classid, d.name as dname
                            FROM mdl_monit_school_class_discipline d
                            inner join mdl_monit_school_class c ON c.id=d.classid
                            inner join mdl_monit_school_marks_totals_term tt ON tt.classdisciplineid=d.id
                            where d.schoolid=$sid and d.teacherid={$steach->id}";
                */
                $strsql = "SELECT d.id as cdid, c.name as cname, d.classid, d.name as dname, c.parallelnum
                           FROM mdl_monit_school_class_discipline d
                           inner join mdl_monit_school_class c ON c.id=d.classid
                           where d.schoolid=$sid and d.teacherid={$steach->id}"; 
                // echo $strsql . '<br>';                                            
                if($get_classes_for_teachers = get_records_sql($strsql)){
                    
                    foreach($get_classes_for_teachers as $get_classes_for_teacher) {
                        
                        if ($paral_types[$get_classes_for_teacher->parallelnum] != $tid) continue;

                        if(!$flag_teach) {
                            $tabledata = array(''); 
                        } else {
                            $tabledata = array('<b>'.fullname($steach).'</b>');                            
                        }

                        $tabledata[] = $get_classes_for_teacher->cname;                      
                        $tabledata[] = $get_classes_for_teacher->dname;

                        
                        if($terms){
                            foreach($terms as $term)  {
                                /*
                                $strsql = "SELECT count(termid) as cntmark
                                           FROM mdl_monit_school_marks_totals_term
                                           WHERE classdisciplineid={$get_classes_for_teacher->cdid} AND termid={$term->id} and mark>0";
                                $totals_term = get_record_sql($strsql);                         
                                $tabledata[] = $totals_term->cntmark;
                                */
                                
                                /*

                                $strsql = "SELECT id
                                           FROM mdl_monit_school_marks_totals_term
                                           WHERE classdisciplineid={$get_classes_for_teacher->cdid} AND termid={$term->id} and mark>0";
                                if($totals_term = get_record_sql($strsql)){                         
                                    $b = count($totals_term);
                                    if($b > 0){
                                       $tabledata[] = $b;
                                    }else{
                                       $tabledata[] = 0;
                                    } 
                                } else {
                                    $tabledata[] = 0;
                                }
                                */
                                
                                $b = count_totals_term_marks($itogsmark, $get_classes_for_teacher->cdid, $term->id);
                                $tabledata[] = $b;
                            }
                        } 
                        
                        $table->data[]= $tabledata;
                        $flag_teach = false;                
                    }
                } else {
                    $tabledata = array('<b>'.fullname($steach).'</b>');                    
                    $tabledata[] = '-';                      
                    $tabledata[] = '-';
                    if($terms){
                        foreach($terms as $term)  {
                               $tabledata[] = '-';
                        }
                    } 
                    $table->data[]= $tabledata;  
                }
                
    		}
       }
    }
		
    return $table;
}

function listbox_administrative($scriptname, $rid, $sid, $yid, $rpid)
{
	global $CFG;
	
 	$reportmenu = array();
 	$reportmenu[0] = get_string('selecttypeofreport', 'block_mou_school') . '...';
	if ($rid != 0 && $sid!= 0 && $yid!= 0)  {
        $reportmenu[1] = get_string('teachermarks', 'block_mou_school');
		$reportmenu[2] = get_string('allinfoaboutpupils', 'block_mou_school');
        $reportmenu[3] = get_string('fillingofclasses', 'block_mou_school');
        $reportmenu[4] = get_string('teacheritogmarks', 'block_mou_school');      
        //$reportmenu[3] = get_string('accesshistory', 'block_mou_school');   
	
	echo '<tr><td>'.get_string('typeofreport', 'block_mou_school').':</td><td>';
	popup_form($scriptname, $reportmenu, "switchreport", $rpid, "", "", "", false);
	echo '</td></tr>';	
	return 1;
	}
}	

function table_administrative ($yid, $rid, $sid, $gid)
{
	global $CFG;

	$table->head  = array (get_string('ordernumber','block_mou_school'), get_string('pupilfio','block_mou_school'), 
							get_string('pol','block_mou_school'), get_string('birthday','block_mou_school'), get_string('phonenumber','block_mou_school'));
	$table->align = array ('center', 'left', 'center', 'center', 'center');
    $table->size = array ('3%', '20%', '5%', '10%', '10%');
	$table->columnwidth = array (7, 30, 7, 15, 15);
    $table->class = 'moutable';
   	$table->width = '90%';
	$table->titlesrows = array(30);
    $table->titles = array();
    $table->titles[] = get_string('administratives', 'block_mou_school');
    $table->downloadfilename = 'administrative';
    $table->worksheetname = 'administrative';
   
    $studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.picture, 
						  u.phone1, u.phone2, m.classid, m.pol, m.birthday
                        FROM {$CFG->prefix}user u
                   LEFT JOIN {$CFG->prefix}monit_school_pupil_card m ON m.userid = u.id 
				   WHERE classid = $gid AND u.deleted = 0 AND u.confirmed = 1
				   ORDER BY u.lastname, u.firstname";		
	
    if($students = get_records_sql($studentsql)) {
    	$i=1;
		foreach ($students as $student){	
			$tabledata = array($i);
			$tabledata[] = "<div align=left><strong><a href=\"{$CFG->wwwroot}/blocks/mou_school/class/pupilcard.php?mode=4&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid={$student->id}\">".fullname($student)."</a></strong></div>";	
		
			if (!empty($student->pol))	{
         		$strsex = get_string ('sympol'.$student->pol, 'block_mou_school');
         	} else {
         		$strsex = '-';	
         	}
			$tabledata[] = $strsex;
			$tabledata[] = $student->birthday;
			$tabledata[] = $student->phone1;
		$i++;
		$table->data[] = $tabledata;		
		}       	
	
	}
						
    return $table;
}

function listbox_years($scriptname, $rid, $sid, $yid, $nyear)
{	
	global $CFG;
	

 	$yearmenu = array();
 	$yearmenu[0] = get_string('selectyear', 'block_mou_school') . '...';
    if ($years = get_records('monit_years'))  {
    	foreach ($years as $year)	{
	        $yearmenu[$year->id] = $year->name.get_string('g','block_mou_school');
	    }
	}	    
	
	echo '<tr><td>'.get_string('year', 'block_mou_school').':</td><td>';
	popup_form($scriptname, $yearmenu, "switchyear", $nyear, "", "", "", false);
	echo '</td></tr>';	
	return 1;
}

function table_filling ($yid, $rid, $sid, $nyear)
{
	global $CFG;

	$table->head  = array (get_string('class','block_mou_school'), get_string('numofpupils','block_mou_school'), 
							get_string('mediumfilling','block_mou_school'));
	$table->align = array ('left', 'center', 'center');
    $table->size = array ('60%', '15%', '15%');
	$table->columnwidth = array (20, 20, 20);
    $table->class = 'moutable';
   	$table->width = '50%';
	$table->titlesrows = array(30);
    $table->titles = array();
    $table->titles[] = get_string('fillingofclasses', 'block_mou_school');
    $table->downloadfilename = 'filling';
    $table->worksheetname = 'fillingofclasses';
	$tabledata = array();
	
	for($i = 1; $i <= $CFG->maxparallelnumber; $i++){
		$couninschool=0;
		if ($classes = get_records_sql("SELECT id, name, parallelnum  FROM {$CFG->prefix}monit_school_class
 								  WHERE yearid=$nyear AND schoolid=$sid AND parallelnum=$i
								  ORDER BY parallelnum, name")) 	{
	  		$countparallel = $countclass = 0;
  			foreach($classes as $class){
  				$countclass++;
  				$quantity  =  count_records('monit_school_pupil_card', 'classid', $class->id);
  				$countparallel += $quantity;
		   
				$table->data[] = array($class->name, $quantity , '');
			}
			$dolja = number_format($countparallel/$countclass, 2, ',', '');	
			$table->data[] = array(get_string('byparallel','block_mou_school'), $countparallel, $dolja);
		}
		//$couninschool++;
	}		
					
    return $table;
}


function table_admin_teachers_marks ($yid, $rid, $sid, $teachid, $mon)
{
	global $CFG;
	$fullname = '';
	if($teacher = get_record_sql("SELECT id, lastname, firstname FROM {$CFG->prefix}user
								where id=$teachid")){	
		$fullname = fullname($teacher);	
	}
	
	$table->head  = array (get_string('class','block_mou_school'), get_string('predmet','block_mou_school'), 
							get_string('numberofteachermarks','block_mou_school'));
	$table->align = array ('center', 'left', 'center');
    $table->size = array ('15%', '70%', '15%');
	$table->columnwidth = array (20, 20, 20);
    $table->class = 'moutable';
   	$table->width = '50%';
	$table->titlesrows = array(30, 30);
    $table->titles = array();
    $table->titles[] = get_string("curteachmarks", "block_mou_school", $fullname);
    $table->titles[] = $fullname;
    $table->downloadfilename = 'teacher_marks_'.$teachid;
    $table->worksheetname = 'all_teacher_marks';
	$tabledata = array();


	$eduyear = current_edu_year();
	list($yfirst, $ysecond) = explode('/', $eduyear);
		
	if ($mon >=9 && $mon <=12 ){
	   $curryear = $yfirst;  
	} else { 
	   $curryear = $ysecond;
	}
    $display = calendar_days_in_month($mon, $curryear);
    $ts = make_timestamp($curryear, $mon, 1);
	$daystart1 = date('Y-m-d', $ts); 		
    $ts = make_timestamp($curryear, $mon, $display);
	$daystart31 = date('Y-m-d', $ts); 		

    $class_name = '';
    $discip_name = '';
    /*						
    $strsql = "SELECT id, classid, name FROM {$CFG->prefix}monit_school_class_discipline
			   WHERE schoolid=$sid and teacherid=$teachid";
    */
    $strsql = "SELECT d.id, classid, d.name as disname, c.name as classname
               FROM mdl_monit_school_class_discipline d INNER JOIN mdl_monit_school_class c ON c.id=d.classid
               WHERE d.schoolid=$sid and d.teacherid=$teachid
               order by c.parallelnum";
    // echo   $strsql . '<br>';          
	if($get_classes_for_teachers = get_records_sql($strsql)){
	   /*
	    $aclassesids = array();
		foreach($get_classes_for_teachers as $get_classes_for_teacher) {
		    $aclassesids[] = $get_classes_for_teacher->classid;
        }
        $classesids = implode (',', $aclassesids);    
		*/	
        foreach($get_classes_for_teachers as $get_classes_for_teacher) {    
			// $ll = 0;
			// $tableattendace = array();
            $cnt = 0;
	      	$class_name = $get_classes_for_teacher->classname;
			$discip_name = $get_classes_for_teacher->disname;
            
	        
			$strsql = "SELECT id FROM {$CFG->prefix}monit_school_class_schedule_$rid
			           WHERE classdisciplineid = {$get_classes_for_teacher->id} AND datestart>='$daystart1' AND datestart<='$daystart31'";
            // echo   $strsql . '<br>';                       	  
			if ($shedules = get_records_sql($strsql)) {
			     $ashedulesids= array();		
				 foreach ($shedules as $sa)  {
                      $ashedulesids[] = $sa->id;
                 } 
                 $shedulesids = implode (',', $ashedulesids);    

    	         $marks = get_records_select("monit_school_marks_$rid", "scheduleid in ($shedulesids)", '' , 'id');
    
				 if($marks) {
					 $cnt = count($marks);   
				 }
			}	
			   			
			$table->data[]= array($class_name, $discip_name, $cnt);	
		} 
	}
			
    return $table;
}


function listbox_term_type($scriptname, $tid)
{	
	global $CFG;
	

 	$termtypemenu = array();
 	$termtypemenu[0] = get_string('choosetermtype', 'block_mou_school') . '...';
    if ($termtypes = get_records('monit_school_term_type'))  {
    	foreach ($termtypes as $termtype)	{
	        $termtypemenu[$termtype->id] = $termtype->name;
	    }
	}	    
	
	echo '<tr><td>'.get_string('typestudyperiod1', 'block_mou_school').':</td><td>';
	popup_form($scriptname, $termtypemenu, "switchtype", $tid, "", "", "", false);
	echo '</td></tr>';	
	return 1;
}


function count_totals_term_marks($itogsmark, $cdid, $tid)
{
    $cnt = 0;    
    foreach ($itogsmark as $im) {
        if ($im->classdisciplineid == $cdid &&  $im->termid == $tid)    {
            $cnt++;
        }
    }
    return $cnt;
}


function listbox_count_teacher($scriptname, $cntid)
{	
	global $CFG;
	

 	$termtypemenu = array();
    $termtypemenu[-1] = 'Выберите количество учителей ...';
 	$termtypemenu[0] = 'Все учителя';
    $termtypemenu[1] = 'с 1 по 15';
    $termtypemenu[16] = 'с 16 по 30';
    $termtypemenu[31] = 'с 31 по 45';
    $termtypemenu[46] = 'с 46 по 60';
    $termtypemenu[61] = 'с 61 по 75';
    $termtypemenu[76] = 'с 76 по 90';
    
	echo '<tr><td>Количество учителей:</td><td>';
	popup_form($scriptname, $termtypemenu, "switchcnt", $cntid, "", "", "", false);
	echo '</td></tr>';	
	return 1;
}

?>