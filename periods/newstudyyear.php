<?php // $Id: newstudyyear.php,v 1.6 2012/08/27 09:00:24 shtifanov Exp $

	require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_school.php');

    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = required_param('sid', PARAM_INT);       // School id
	$lastyid = optional_param('yid', 0, PARAM_INT);       // Year id
	$action = optional_param('action', '-');
	$lastyear = 0;
	$yid = $lastyid;
	 
    $strtitle = get_string('studyyears', 'block_mou_school');

	$breadcrumbs = '<a href="'.$CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('title','block_mou_school').'</a>';
	$breadcrumbs .= "-> $strtitle";
    print_header_mou("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

    print_heading($strtitle);

	$redirlink = "newstudyyear.php?rid=$rid&amp;yid=$yid&amp;sid=$sid";
	
	$admin_is = isadmin();
	$region_operator_is = ismonitoperator('region');
	if (!$admin_is) { // } && !$region_operator_is)	 {
		error('Only admin access this function.', $redirlink);
	}	
    
    // notice ("Переход уже осуществлен. Ждем следующего учебного года :-)", '../index.php');
    
	if ($action == 'create')	{
	   
        ignore_user_abort(false); 
        @set_time_limit(0);
        @ob_implicit_flush(true);
        @ob_end_flush();
    	@raise_memory_limit("512M");
     	if (function_exists('apache_child_terminate')) {
    	    @apache_child_terminate();
       	}    
       
        $yid = create_new_education_year();

        print_heading('Перевод средних школ в новый учебный год.', 'center', 3);
        create_eductional_ou('monit_school', $yid);
        
        print_heading('Перевод НПО И СПО в новый учебный год.', 'center', 3);
		create_eductional_ou('monit_college', $yid);
		
        print_heading('Перевод учреждений ДОД в новый учебный год.', 'center', 3);
		create_eductional_ou('monit_udod', $yid);		
		
        print_heading('Перевод  ДОУ в новый учебный год.', 'center', 3);
		create_eductional_ou('monit_education', $yid);
       
        print_heading('Перевод сотрудников колледжей в новый учебный год.', 'center', 3);
    	$newcollegesids = get_list_old_new_id ('monit_college', $yid);
    	update_monit_staff($newcollegesids, 'collegeid');
        copy_context_and_roleassignments($newcollegesids, CONTEXT_COLLEGE);
    	
        print_heading('Перевод сотрудников учреждений ДОД в новый учебный год.', 'center', 3);
    	$newudodsids = get_list_old_new_id ('monit_udod', $yid);
    	update_monit_staff($newudodsids, 'udodid');
        copy_context_and_roleassignments($newudodsids, CONTEXT_UDOD);
    	
        print_heading('Перевод сотрудников ДОУ в новый учебный год.', 'center', 3);        
    	$newdousids = get_list_old_new_id ('monit_education', $yid);
    	update_monit_staff($newdousids, 'douid');
        copy_context_and_roleassignments($newdousids, CONTEXT_DOU);
        
        print_heading('Перевод очереди в ДОУ в новый учебный год.', 'center', 3);
        update_queue_request($newdousids, 18);
       
        print_heading('Перевод сотрудников школ в новый учебный год.', 'center', 3);
       	$newschoolsids = get_list_old_new_id ('monit_school', $yid);
    	update_monit_staff($newschoolsids, 'schoolid');
        copy_context_and_roleassignments($newschoolsids, CONTEXT_SCHOOL);
        
        print_heading('Перевод очереди в школы в новый учебный год.', 'center', 3);
        update_queue_request($newschoolsids, 1);               
                
        for ($rayonid = 1; $rayonid <= 25; $rayonid++)  {
        	if ($rayon = get_record_select('monit_rayon', "id = $rayonid", 'id, name'))	{
	          	print_heading('Перевод классов и учеников школ района:' . $rayon->name . ' в новый учебный год.', 'center', 3);
                create_classes_and_move_pupil_in_neweduyear($newschoolsids, $rayonid , $yid);  	
	        }
        }     
	}  
    
    else if ($action  == 'updateschool')    {

        ignore_user_abort(false); 
        @set_time_limit(0);
        @ob_implicit_flush(true);
        @ob_end_flush();
    	@raise_memory_limit("512M");
     	if (function_exists('apache_child_terminate')) {
    	    @apache_child_terminate();
       	}    

	    $yid = create_new_education_year();
	    $newschoolsids = get_list_old_new_id ('monit_school', $yid);
        print_heading('Перевод таблиц Электронной школы в новый учебный год.', 'center', 3);  
        update_schoolid_in_mou_school($newschoolsids, $yid);
        update_term_and_holidays($yid);
        print_heading('Генерация новых учебных планов на основе предыдущих данных.', 'center', 3);        
        regenerate_monit_school_curriculum($newschoolsids, $yid);
        notify('<strong>Процесс перехода на новый учебный год завершен.</strong>', 'green');        
    }
    else if ($action == 'createsql')	{
	    $yid = create_new_education_year();
	    $newschoolsids = get_list_old_new_id ('monit_school', $yid);
        print_heading('Генерация скриптов для резервного копирования оценок и посещаемости и очистки таблиц с оценками и посещаемостью.', 'center', 3);
        backup_marks_in_mou_archive($newschoolsids, $yid);
	}   
    				
    print_tabs_years_link("newstudyyear.php?", $rid, $sid, $yid);
	
	$currenttab = 'studyyear';
    include('tabsup.php');
		
	$years = get_records('monit_years');
	
	if ($years )	{
		$table = new stdClass();
		$table->head  = array (	get_string('name', 'block_mou_school'), get_string('timestart', 'block_mou_school'),
								get_string('timeend', 'block_mou_school'), get_string('action', 'block_mou_school'));
	    $table->align = array ("center", "center", "center", "center");
 	    $table->size = array('10%', '10%', '10%', '5%');
	   	$table->width = '60%';
        $table->class = 'moutable';
       	// $table->align = array ("left", "left", "left");
       	
		
		foreach ($years as $year) {
				$lastyear = $year->id;
				$title = get_string('editstudyear','block_mou_school');
				$strlinkupdate = "<a title=\"$title\" href=\"editstudyear.php?mode=edit&amp;id={$year->id}\">";
				$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";
				$strdiscipline = $year->name;
				$table->data[] = array ($strdiscipline, convert_date($year->datestart, 'en', 'ru'),
										convert_date($year->dateend, 'en', 'ru'), $strlinkupdate);
		}
		print_color_table($table);
		
		$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $lastyear, 'action' => 'create');
		echo '<table align="center" border=0><tr><td>';
	    print_single_button("newstudyyear.php", $options, get_string('createnewyear','block_mou_school'));
		echo '</td><td>';
        $options = array('rid' => $rid, 'sid' => $sid, 'yid' => $lastyear, 'action' => 'updateschool');
	    print_single_button("newstudyyear.php", $options, 'Перевести таблицы Электронной школы в новый учебный год');
		echo '</td></tr><tr><td colspan=2>';
        $options = array('rid' => $rid, 'sid' => $sid, 'yid' => $lastyear, 'action' => 'createsql');
	    print_single_button("newstudyyear.php", $options, 'Создать SQL-скрипт для копирования оценок и очистки таблиц');
		echo '</td></tr></table><br>';
        
        print_simple_box_start_old('center', '70%', 'white');
        echo '<b>Инструкция по переходу на новый учебный год:</b><br>';
        echo '0. Предварительно проверяем условие в функции current_edu_year() (строка 216 в файле ../blocks/monitoring/lib.php). Если перевод выполняется в августе, то условие должно быть if(($m >= 1) && ($m <= 7)).<br>';
        echo '1. Сначала необходимо нажать на кнопку "Создать новый учебный год". Она выполняет ряд следующих операций:<br>';
        echo '- Перевод средних школ в новый учебный год.<br>
              - Перевод НПО И СПО в новый учебный год.<br>
              - Перевод учреждений ДОД в новый учебный год.<br>
              - Перевод ДОУ в новый учебный год.<br>
              - Перевод сотрудников НПО и СПО в новый учебный год.<br>
              - Перевод сотрудников учреждений ДОД в новый учебный год.<br>
              - Перевод сотрудников ДОУ в новый учебный год.<br>
              - Перевод сотрудников школ в новый учебный год.<br>
              - Перевод очереди в ДОУ в новый учебный год.<br>
              - Перевод очереди в школы в новый учебный год.<br>
              - Перевод классов и учеников школ области в новый учебный год.<br>';
        echo '2. Кнопка "Перевести таблицы Электронной школы в новый учебный год" выполнит следующие операции:<br>';      
        echo '- Перевод таблиц Электронной школы в новый учебный год.<br>
              - Генерация новых учебных планов на основе предыдущих данных.<br>
              (замечание: данную кнопку возможно придется нажимать несколько раз (т.к. сервер прерывает выполнение скрипта) пока<br />
              не появится зеленая надпись полужирным шрифтом "Процесс перехода на новый учебный год завершен".)<br>';
        echo '3. С помощью кнопки "Создать SQL-скрипт для копирования оценок и очистки таблиц" необходимо сгенерировать SQL-скрипт и запустить его на сервере в MySQL Query Browser или в MySQL Workbench. 
        Он выполнит следующие операции:<br>';      
        echo '- Выполнит запросы на копирование данных с четвертными и годовыми оценками в архив (БД mou_archive).<br>
              - Выполнит запросы на очистку таблиц с заданиями, посещаемостью, расписанием, текущими оценками.<br>';
        echo '4. В скрипте lib_school.php изменить значения констант ID_SCHOOL_FOR_DELETED и ID_SCHOOLS_FOR_DELETED на новый id "Школа выбывших ...".<br> 
                Запрос для поиска id-школы: SELECT id FROM mou.mdl_monit_school where yearid=? and name like \'%выбывших%\';';
        // echo '5. Если есть праздничные дни, которые выпадают на выходные, то надо подкорректировать даты выходных дней в таблице mdl_monit_school_holidays. У праздничных дней schoolid=0.<br>';
        echo '5. В файле \mou_att2\ref\attcriteria.php в строке 177 заменить $curryear+1 на $curryear.<br>';
        print_simple_box_end_old();
		
	}	else {
		notify(get_string('notfoundyears', 'block_mou_school'));
	}
	
    print_footer();



function create_new_education_year()
{       
    $yid = get_current_edu_year_id();
	$strcurryear = current_edu_year();
	if ($year = get_record('monit_years', 'name', $strcurryear)) {
		notify("Учебный год $strcurryear уже существует в системе.", 'green');
	} else {
		$rec = new stdClass();
		$rec->name = $strcurryear;
		$rec->datestart = date("Y") . '-09-01'; 
		$rec->dateend = date("Y")+1 . '-09-01';
		if ($yid = insert_record('monit_years', $rec))	{
			notify("Учебный год $strcurryear добавлен в систему.", 'green');				
		}
	}
    
    return $yid;
}


function create_eductional_ou ($table, $yid) 
{
	$lastyid = $yid-1;
	$edus = get_records($table, 'yearid', $lastyid);
	foreach($edus as $edu)	{
		if ($edu->isclosing == false)	{
			if (!$exists_ou = get_record_select($table, "yearid=$yid AND uniqueconstcode=$edu->uniqueconstcode", 'id, name')) {
			    $edu->yearid = $yid;
			    $newedu = addslashes_object($edu);
				if ($newid = insert_record($table, $newedu))	{
					// $schoolsids[$school->id] = $newid;  
		    	    notify("ОУ добавлено: {$edu->name} ($edu->id > $newid) ", 'green');
		    	}
		    } else {
					notify("ОУ уже существует: {$exists_ou->id} ({$edu->id}) > {$edu->name}", 'black');						    	
		    }	
	    }
	}
}


function update_monit_staff($newedusids, $fieldname)
{
	global $db;
		
	foreach ($newedusids as $oldeduid => $neweduid)	{
	   if (!record_exists_mou('monit_att_staff', $fieldname, $oldeduid))    {
	       notify("Сотрудников ОУ с $fieldname=$oldeduid не существует в БД.", 'black');
           continue;
	   }
	   $strsql = "UPDATE mdl_monit_att_staff SET $fieldname = ". $neweduid . " WHERE $fieldname = " . $oldeduid;
	   $db->Execute($strsql);
       notify("Сотрудники ОУ с $fieldname=$oldeduid переведены в новый учебный год.", 'green');
	}		
}	


function copy_context_and_roleassignments($newedusids, $ctxname)
{
	foreach ($newedusids as $oldeduid => $neweduid)	{
	   $ctxold = get_context_instance($ctxname, $oldeduid);
       if ($ctxnew = get_context_instance($ctxname, $neweduid)) {
            make_copy_roleassignments($ctxold, $ctxnew);
       }    
       notify("Сотрудникам ОУ с id=$neweduid назначены роли.", 'green');
       // if ($oldeduid > 2130) exit(0);
	}		
}	   


function make_copy_roleassignments($ctxold, $ctxnew)    
{

    $strsql = "SELECT id, roleid, contextid, userid, hidden, timestart, timeend, timemodified, modifierid, enrol, sortorder
              FROM mdl_role_assignments
              where contextid=$ctxold->id"; 
    if ($roleasigns = get_records_sql($strsql))    {
       foreach ($roleasigns  as $rs)  {
           // echo '<pre>'; print_r($rs); echo '</pre>';
           unset($rs->id);
           $rs->contextid = $ctxnew->id;
     	   if (!record_exists_select_mou('role_assignments', "roleid = $rs->roleid AND contextid = $rs->contextid AND userid = $rs->userid")) 	{
    	        if (!insert_record('role_assignments', $rs))	{
    	            print_object($rs);
    				error(get_string('errorinaddingmdl_role_assignments','block_mou_school'), '');
    		    }
    	   }
       } 
    }
}


function update_queue_request($newedusids, $edutypeid)
{
	global $db;
		
	foreach ($newedusids as $oldeduid => $neweduid)	{
	   if (!record_exists_mou('monit_queue_request', 'edutypeid',  $edutypeid, 'oid', $oldeduid))    {
           continue;
	   }
	   $strsql = "UPDATE mdl_monit_queue_request SET oid=$neweduid WHERE edutypeid=$edutypeid AND oid=$oldeduid";
	   $db->Execute($strsql);
        
       // НА БУДУЩЕЕ ПРОВЕРИТЬ !!!!!
       /*
	   if (!record_exists_mou('monit_queue_group', 'edutypeid',  $edutypeid, 'oid', $oldeduid))    {
           continue;
	   }
	   $strsql = "UPDATE mdl_monit_queue_group SET oid=$neweduid WHERE edutypeid=$edutypeid AND oid=$oldeduid";
	   $db->Execute($strsql);
       */
       
       notify("Очередь ОУ с edutypeid=$edutypeid и oid=$oldeduid переведена в новый учебный год.", 'green');
	}		
}	


function create_classes_and_move_pupil_in_neweduyear($newschoolsids, $rid, $yid)
{
    global $CFG;
        
    $lastyid = $yid-1;

	$classids = array();
	if ($classes = get_records_select('monit_school_class', "yearid=$lastyid AND rayonid=$rid", 'schoolid'))	{

		foreach ($classes as $class)	{
		    if ($class->schoolid == ID_SCHOOL_FOR_DELETED) continue;  
		    // print_r($class); echo '<hr>'; continue;
			$classid = $class->id;
			if ($class->parallelnum < 11)	{
				
				if (isset($newschoolsids[$class->schoolid]) && !empty($newschoolsids[$class->schoolid]))	{
	
					$num = (integer)$class->name;
					if (is_numeric($num))	{
						$contents = preg_replace("|[^а-яА-Я ]|i", NULL, $class->name);
						$newpn = $class->parallelnum + 1;
						$newname = $newpn . $contents;
					} else {
						$newpn 	 = $class->parallelnum;
						$newname = $class->name;
					}
					// echo  "$class->name ==> $newname ($newpn)<br>";
					unset($newclass);	
								
					$newclass->rayonid = $class->rayonid;
					$newclass->schoolid = $newschoolsids[$class->schoolid];					
					$newclass->yearid = $yid;
					$newclass->description = $class->description; 
					$newclass->name = $newname;
					$newclass->parallelnum = $newpn;
					$newclass->timecreated = time();
					// $newclass->curriculumid = $class->curriculumid;
					$newclass->teacherid = $class->teacherid;
					$newclass->classidold = $class->id;
					/*
					if (isset($class->curriculumid) && !empty($class->curriculumid))	{
						$newclass->curriculumid = $class->curriculumid;
					}	
				
					if (isset($class->teacherid) && !empty($class->teacherid))	{
						$newclass->teacherid = $class->teacherid;	
					}
					*/
					
					if (!record_exists('monit_school_class', 'yearid', $newclass->yearid, 'schoolid', $newclass->schoolid, 'name', $newclass->name)) {
						if ($newid = insert_record('monit_school_class', $newclass))	{
							$classids[$classid] = $newid;
							notify("Новый класс создан: $newclass->name ($classid -> $newid).", 'green');
							/*
							$newrec = get_record ('monit_school_class', 'yearid', $year->id, 'schoolid', $class->schoolid, 'name', $class->name, 'id');
							$classids[$class->id] = $newrec->id;
							notify("New class added: {$class->id} - $newid", 'blue', 'center');
							*/
							
						}	else	{
							print_r($newclass);
							error('Error insert monit_school_class.', 'studyyear.php');
						}
					} else {
							$strsql = "SELECT id FROM mdl_monit_school_class 
									   WHERE yearid = {$newclass->yearid} AND schoolid = {$newclass->schoolid} AND name = '{$newclass->name}'";
							// echo $strsql . '<hr>';
							if ($class_exist = get_record_sql($strsql))	{
								$classids[$classid] = $class_exist->id;
							}	else {
								$classids[$classid] = 0;
							}   	
							// $class_exist = get_record_select('', "yearid = {$newclass->yearid} AND schoolid = {$newclass->schoolid} AND name = {$newclass->name}");
							
							notify("Класс уже существует: {$newclass->schoolid} > {$classids[$classid]} > {$newclass->name}", 'red');
					}
                    
                    // create context class
                    if ($classids[$classid] > 0)    {
                	   $ctxold = get_context_instance(CONTEXT_CLASS, $classid);
                       if ($ctxnew = get_context_instance(CONTEXT_CLASS, $classids[$classid])) {
                            make_copy_roleassignments($ctxold, $ctxnew);
                       }    
                    }	
				}	
			}		
		}
	}
	unset($classes);
	// print_r($classids);
	 
	if ($pupilcards = get_records_select('monit_school_pupil_card', "yearid = $lastyid AND rayonid = $rid"))	{
		foreach ($pupilcards as $pupil)		{
		    if ($pupil->schoolid == ID_SCHOOL_FOR_DELETED) continue;
			if (isset($newschoolsids[$pupil->schoolid]) && !empty($newschoolsids[$pupil->schoolid]))	{
				if (isset($classids[$pupil->classid]) && !empty($classids[$pupil->classid]))	{ 
					$pupil->yearid = $yid;
					$pupil->schoolid = $newschoolsids[$pupil->schoolid];
					$pupil->classid = $classids[$pupil->classid];
					if (!record_exists('monit_school_pupil_card', 'yearid', $pupil->yearid, 'schoolid', $pupil->schoolid, 'userid', $pupil->userid)) {
						if ($newid = insert_record('monit_school_pupil_card', addslashes_object($pupil)))	{
							notify("Ученик переведен: classid = $pupil->classid  -> pupilid = $newid ", 'green');
						} else {
							print_r($pupil);
							notify('Error insert monit_school_pupil_card.');
							// error('Error insert monit_school_pupil_card.', 'studyyear.php');
						}
					} else {
						notify("Ученик уже был переведен: classid = $pupil->classid -> pupilid = $pupil->id", 'black');	
					}	
				} else {
					notify("??? Может быть 11 класс или же ученик уже был переведен: classid = $pupil->classid  -> pupilid = $pupil->id ");
				} 	
			} else {
				notify("!!! pupil->schoolid  = $pupil->schoolid not found!!! ");
			}
		}
	
        unset($classids);
	} else {
		notify("!!! Pupil in this rayon not found!!! ");
	}
}



function update_schoolid_in_mou_school($newschoolsids, $yid)
{
    global $CFG, $db;

    // print_object($newschoolsids);
    
    $lastyid = $yid-1;
    
    execute_sql("CREATE TABLE mou_archive.mdl_monit_school_class_termtype_$lastyid LIKE mou.mdl_monit_school_class_termtype");
    execute_sql("INSERT INTO mou_archive.mdl_monit_school_class_termtype_$lastyid SELECT * FROM mou.mdl_monit_school_class_termtype");
 
    execute_sql("CREATE TABLE mou_archive.mdl_monit_school_term_$lastyid LIKE mou.mdl_monit_school_term");
    execute_sql("INSERT INTO mou_archive.mdl_monit_school_term_$lastyid SELECT * FROM mou.mdl_monit_school_term");
   
   // update schoolid
   $tables = array ('mdl_monit_school_class_termtype',
					'mdl_monit_school_component',
					'mdl_monit_school_curriculum_totals',
					'mdl_monit_school_discipline',
					'mdl_monit_school_discipline_domain',
					'mdl_monit_school_discipline_plan',
					'mdl_monit_school_discipline_unit',
					'mdl_monit_school_holidays',
					'mdl_monit_school_profiles_curriculum',
					'mdl_monit_school_room',
					'mdl_monit_school_schedule_bells',
					'mdl_monit_school_subgroup',
					'mdl_monit_school_teacher',
					'mdl_monit_school_term',
					'mdl_monit_school_textbook');

    for ($i=1; $i<=23; $i++) {
        $tables[] = 'mdl_monit_school_discipline_lesson_'.$i;
    }
    
    $tables[] = 'mdl_monit_school_discipline_lesson_25';

    echo '<hr><hr>';
/*    
    foreach ($tables as $table)		{
        // delete_records_select($table, 'schoolid <  2118');
        $strsql = "DELETE FROM $table WHERE schoolid < 2118";
        $db->Execute($strsql);
    }    
*/    
	foreach ($tables as $table)		{
		$strsql = "SELECT DISTINCT schoolid FROM $table ORDER BY schoolid";
		if ($schoolsids = get_records_sql($strsql))	{
			foreach ($schoolsids as $schoolsid)	{
				if (isset($newschoolsids[$schoolsid->schoolid]) && !empty($newschoolsids[$schoolsid->schoolid]))	{
					$strsql = "UPDATE $table SET schoolid=". 
							   $newschoolsids[$schoolsid->schoolid] . " WHERE schoolid=" . $schoolsid->schoolid;
                    // echo $strsql .' <br>';            
					$db->Execute($strsql);
				}  else {
					notify ("Не найден новый id для школы с old_id=$schoolsid->schoolid. Возможно школа была закрыта.");
				}
			}
		}	
		notify ("Идентификаторы школ в таблице $table изменены.<hr>", 'green');
	}
    
    $strsql = "UPDATE mdl_monit_school_discipline_plan SET yearid=$yid";
	$db->Execute($strsql);    
}    



function update_term_and_holidays($yid)
{
    global $db;
    
    $lastyid = $yid-1;
  
    $curryear = date('Y');
    
    $strsql = "UPDATE mdl_monit_school_term SET yearid=$yid"; 
	$db->Execute($strsql);

    $dateend = $curryear . '-08-31';
    if (!record_exists_sql("SELECT id FROM mdl_monit_school_term where dateend > '$dateend'"))    {
        $strsql = "UPDATE mdl_monit_school_term SET datestart=DATE_ADD(datestart, INTERVAL 1 YEAR), dateend=DATE_ADD(dateend, INTERVAL 1 YEAR)";
        $db->Execute($strsql);
    }    
    
    if (!record_exists_sql("SELECT id FROM mdl_monit_school_holidays where dateend > '$dateend'"))    {
        $strsql = "UPDATE mdl_monit_school_holidays SET datestart=DATE_ADD(datestart, INTERVAL 1 YEAR), dateend=DATE_ADD(dateend, INTERVAL 1 YEAR)";    
	    $db->Execute($strsql);
    }    
    /*    
    $daysname = array('День Народного единства',    
                      'День защитника отечества', 
                      'Международный женский день',
                      'Праздник Весны и Труда',
                      'День Победы', 
                      'День России');
                                               
    $daystime = array('2011-11-04', '2012-02-23',  '2012-03-08', '2012-05-01', '2012-05-09', '2012-06-12'); 

    $strsql = "DELETE FROM mdl_monit_school_holidays WHERE schoolid=0";    
	$db->Execute($strsql);

    foreach($daysname as $i => $dname) {
        $rec->schoolid = 0;
        $rec->termtypeid = 0; 
        $rec->name = $dname;
        $rec->datestart = $daystime[$i]; 
        $rec->dateend = $daystime[$i];
        $rec->parallelnum = '1,2,3,4,5,6,7,8,9,10,11,12,0';
        if (!insert_record('monit_school_holidays', $rec))	{
            echo '<pre>'; print_r($rec); echo '</pre>';
			error('Ошибка добавления праздничного дня!');
	    }
    } 
    */         
}

    
// обработка таблицы 'mdl_monit_school_curriculum',    
function regenerate_monit_school_curriculum($newschoolsids, $yid)
{
    global $CFG, $db;
    
    $lastyid = $yid-1;
    $redirlink = "newstudyyear.php";

	$strsql = "SELECT DISTINCT schoolid FROM mdl_monit_school_curriculum WHERE yearid=$lastyid ORDER BY schoolid";
	if ($schoolsids = get_records_sql($strsql))	{
		foreach ($schoolsids as $schoolsid)	{
			if (isset($newschoolsids[$schoolsid->schoolid]) && !empty($newschoolsids[$schoolsid->schoolid]))	{
                    $sid = $newschoolsids[$schoolsid->schoolid];
	                notify ("Перевод Учебных планов школы с id=$sid.", 'green');                
					for ($p = 1; $p <= $CFG->maxparallelnumber; $p++)	{
						$strsql = "SELECT id, componentid, profileid, disciplineid, hours FROM mdl_monit_school_curriculum
                                   WHERE  parallelnum=$p and schoolid={$schoolsid->schoolid}
                                   ORDER BY disciplineid, hours DESC";
                        // echo $strsql . '<br>';            
                        if ($oldcurriculum = get_records_sql($strsql))  {
                            
                            // echo '<pre>'; print_r($oldcurriculum); echo '</pre>'; 
                        
                            // создаем массив объектов с индексом disciplineid для того, чтобы убрать повторяющиеся записи с disciplineid
                            $records = array();
                            foreach ($oldcurriculum as $oldcur) {
                                $index = $oldcur->componentid . '_' . $oldcur->profileid . '_' . $oldcur->disciplineid;
                                $records[$index]->componentid = $oldcur->componentid;
                                $records[$index]->profileid = $oldcur->profileid;
                                $records[$index]->disciplineid = $oldcur->disciplineid;
                                $records[$index]->hours = $oldcur->hours;
                            }  
                            
                            // echo '<hr>';  echo '<pre>'; print_r($records); echo '</pre>';
                            
                            // берем все id классов
                            $strsql = "SELECT id FROM {$CFG->prefix}monit_school_class
									   WHERE schoolid=$sid AND yearid=$yid AND parallelnum=$p";
                             // для каждого класса создаем копии записей из предыдущего учебного плана
							if ($classes = get_records_sql ($strsql))	{
                                // echo '<hr>'; echo '<pre>'; print_r($classes); echo '</pre>';							 
								foreach ($classes as $class) {
								    foreach ($records as $record)   {
								        $pid = $record->profileid;
                                        $cid = $record->componentid;
                                        $did = $record->disciplineid;
                                        $hour = $record->hours;
    	                        		if (!record_exists_select_mou('monit_school_curriculum', "parallelnum = $p AND schoolid = $sid AND profileid = $pid AND componentid = $cid AND disciplineid = $did AND classid = {$class->id}")) 	{
    						          		$newrec->parallelnum = $p;
    						          		$newrec->yearid 	 = $yid;
    						          		$newrec->schoolid 	 = $sid;
    						          		$newrec->classid 	 = $class->id;
    						          		$newrec->componentid = $cid;
    						          		$newrec->profileid 	 = $pid;
    					            		$newrec->disciplineid = $did;
    					            		$newrec->hours       = $hour;
    	                                    
    								        if (!insert_record('monit_school_curriculum', $newrec))	{
    								            echo '<br>'; print_r($newrec); echo '</br>';
    											error(get_string('errorinaddingcurriculum','block_mou_school'), $redirlink);
    									    }
    		            				}
                                    }    
		            			}
		            		}
                        }    
					}
            }
        }
    }           
   /*
    SELECT distinct componentid, profileid, disciplineid, hours FROM mdl_monit_school_curriculum
    where parallelnum=9 and schoolid=2343
    order by disciplineid, hours DESC 
    */
}

function backup_marks_in_mou_archive($newschoolsids, $yid)
{
    global $CFG, $db;
    
    $lastyid = $yid-1;
        
    // list of tables to copy archive and truncated    
    /* 
    execute_sql("CREATE TABLE mou_archive.mdl_monit_school_marks_19_4 LIKE mou.mdl_monit_school_marks_19");
    execute_sql("INSERT INTO mou_archive.mdl_monit_school_marks_19_4 SELECT * FROM mou.mdl_monit_school_marks_19");
    */
    
   	$tables = array ('mdl_monit_school_class_discipline',
                     // 'mdl_monit_school_class_termtype',
                     'mdl_monit_school_marks_totals_term',
                     'mdl_monit_school_marks_totals_year',
					 'mdl_monit_school_subgroup_pupil'                     
    );

    echo "CREATE DATABASE IF NOT EXISTS mou_archive;" . '<br>';
   	foreach ($tables as $tablename)		{
        $tablenamearchive =  $tablename . '_' . $lastyid;
        $strsql = "CREATE TABLE mou_archive.{$tablenamearchive} LIKE mou.{$tablename};";
        echo $strsql .' <br>'; 
        $strsql = "INSERT INTO mou_archive.{$tablenamearchive} SELECT * FROM mou.{$tablename};";
        echo $strsql .' <br>';
    }    

    foreach ($tables as $tablename)		{
        if ($tablename != 'mdl_monit_school_class_termtype')    {
            $strsql = "TRUNCATE TABLE mou.{$tablename};";
            echo $strsql .' <br>';
        }     
    }    

    $rayons = get_records('monit_rayon');
    
   	$tables2 = array ('mdl_monit_school_assignments', 
                      'mdl_monit_school_attendance',
					  'mdl_monit_school_class_schedule',
                      'mdl_monit_school_marks');

    /*   	    
   	foreach ($tables2 as $tablename)		{
        foreach ($rayons as $rayon) {
            $tablenamearchive =  $tablename . '_' .$rayon->id . '_' . $lastyid;
            $strsql = "CREATE TABLE mou_archive.{$tablenamearchive} LIKE mou.{$tablename};";
            echo $strsql .' <br>'; 
            $strsql = "INSERT INTO mou_archive.{$tablenamearchive} SELECT * FROM mou.{$tablename};";
            echo $strsql .' <br>';
        }
    } 
    */       

   	foreach ($tables2 as $tablename)		{        
        foreach ($rayons as $rayon) {
            $tablenamearchive =  $tablename . '_' .$rayon->id;
            $strsql = "TRUNCATE TABLE mou.{$tablenamearchive};";
            echo $strsql .' <br>'; 
        }
    }        

    echo "DELETE FROM mou.mdl_role_assignments where roleid=15;<br>";
    echo "DELETE FROM mou.mdl_context          where contextlevel=1060;<br>";
    
}
 
?>
