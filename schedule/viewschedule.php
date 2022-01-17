<?php  

    require_once("../../../config.php");
    require_once("$CFG->libdir/formslib.php");
    require_once('../../monitoring/lib.php');// для ../authbase.inc.php
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_school.php');// для ../authbase.inc.php 
	require_once('../authbase.inc.php');//
    require_once('lib.php');

    if (!has_capability('block/mou_school:viewschedule', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}

    //
    $tabperiod = optional_param('p', 'week');
    $sh = optional_param('sh', 0, PARAM_INT); 		//display view
    $nw = optional_param('nw', 0, PARAM_INT);   // Number of week in study year
    
    $idteacher = optional_param('idteacher', 0, PARAM_INT);//id выбранного Учителя при переходе с вкладки
    $idroom = optional_param('idroom', 0, PARAM_INT);//id выбранного Кабинета при переходе с вкладки
    $printxls = optional_param('prxls', '0');

    $cald = optional_param('cald', '0');
    $calm = optional_param('calm', '0');
    $calg = optional_param('calg', '0');
    
    $GLDATESTART = array();
    $curryearfull = current_edu_year();
    $curyear = explode('/', $curryearfull);
    $datestartGLOB = $curyear[0].'-09-01';
    $dateendGLOB = $curyear[1].'-05-31';	
    $allweeksinyear = make_all_weeks_in_year($datestartGLOB, $dateendGLOB);

    switch ($printxls){
          //////Просмотр расписания по дням             
          case 'day':
            $table = table_schedule_day_view('day', 'table');            //
            print_table_to_excel($table);
            
          break;
          //////Просмотр расписания по неделям
          case 'week':
            
          
            $table = table_schedule_week_view_converter('day', 'table', $nw);            //
            //echo '<pre>'; print_r($table->data); echo '</pre>'; 
            print_table_to_excel($table);      
    
          break;
          /*
          //////Просмотр расписания по кабинетам             
          case 'room':
            $table = table_schedule_week_view_converter('room', 'table', $nw);            //
            print_table_to_excel($table);             
   
          break;
          //////Просмотр расписания по кабинетав списком             
          case 'roomlist':
            $table = table_schedule_week_view_converter('room', 'listroom', $nw);            //
            print_table_to_excel($table);             
    
          break;
          //////Просмотр расписания по учителям             
          case 'teacher':
            $table = table_schedule_week_view_converter('teacher', 'table', $nw);            //
            print_table_to_excel($table);             
   
          break;
          //////Просмотр расписания по учителям списком            
          case 'teacherlist':
            $table = table_schedule_week_view_converter('teacher', 'listteacher', $nw);            //
            print_table_to_excel($table);             
   
          break;
          */
    }//конец switch ($tabperiod)
  
    
   
    //Вывод вкладок операций Просмотра расписания и Создание расписания
    if (has_capability('block/mou_school:editschedule', $context))	{
        $currenttab = 'viewschedule';
	    include('tab_act.php');
	}

    //Вывод вкладок периодов День Неделя Месяц Год
    include('tab_periods.php');

    //По периодам    
    switch ($tabperiod){
      //////Просмотр расписания по дням             
      case 'day': 
                // include('form_day.php');

            	$wid = optional_param('wid', 0, PARAM_INT);   // Day number in week

            	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
            	listbox_all_weeks_year("viewschedule.php?rid=$rid&sid=$sid&yid=$yid&wid=$wid&p=$tabperiod&nw=", $allweeksinyear, $nw);
                listbox_weekday_with_date("viewschedule.php?rid=$rid&sid=$sid&yid=$yid&nw=$nw&p=$tabperiod&wid=", $nw, $wid);
                echo '</table>'; 	
                
                if ($wid != 0)		{   
                    
                    $strdaystart = $GLDAY[$wid];
                    list($calg, $calm, $cald) = explode ('-', $strdaystart);
                   
                    // $tablehead = table_schedulehead_day($wid);
                    // print_table($tablehead);
                            
                    $table = table_schedule_day_view('day', 'table');
                    print_table($table);
                   
               		$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 
                                     'cald' => $cald,  'calm' => $calm, 'calg' => $calg, 'p'=> $tabperiod, 
                                     'nw' => $nw, 'prxls' => 'day', 'action' => 'excel');
            		echo '<table align="center" border=0><tr><td>';
            		print_single_button("viewschedule.php", $options, get_string("downloadexcel"));
            	   	echo '</td><td>';
                }
                 
      break;
      //////Просмотр расписания по неделям
      case 'week':

            	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
            	listbox_all_weeks_year("viewschedule.php?rid=$rid&sid=$sid&yid=$yid&p=$tabperiod&nw=", $allweeksinyear, $nw);
                echo '</table>'; 	
                
                if ($nw != 0)		{   

                    $table = table_schedule_week_view(1, 'day', 'table');
               		$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 
                                     'cald' => $cald,  'calm' => $calm, 'calg' => $calg, 'p'=> $tabperiod, 
                                     'nw' => $nw, 'prxls' => 'week', 'action' => 'excel');
            		echo '<table align="center" border=0><tr><td>';
            		print_single_button("viewschedule.php", $options, get_string("downloadexcel"));
            	   	echo '</td><td>';
                }   

       
      break;
//////Просмотр расписания по кабинетам             
      case 'room':

            	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
            	listbox_all_weeks_year("viewschedule.php?rid=$rid&sid=$sid&yid=$yid&p=$tabperiod&nw=", $allweeksinyear, $nw);
                $rooms = get_records_select('monit_school_room', "schoolid=$sid", '', 'id, name');
                listbox_room("viewschedule.php?rid=$rid&sid=$sid&yid=$yid&sh=$sh&p=$tabperiod&nw=$nw&idroom=", $rooms, $idroom);
                echo '</table>'; 
                
                
                if ($idroom != 0 and $nw != 0){   

                    $table = table_schedule_week_view(1, 'room', 'table');
               		$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 
                                     'cald' => $cald,  'calm' => $calm, 'calg' => $calg, 'p'=> $tabperiod, 
                                     'nw' => $nw, 'idroom' => $idroom, 'prxls' => 'room', 'action' => 'excel');
            		echo '<table align="center" border=0><tr><td>';
            		print_single_button("viewschedule.php", $options, get_string("downloadexcel"));
            	   	echo '</td><td>';
                }   
          
      break;
//////Просмотр расписания по кабинетав списком             
      case 'roomlist':

            	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
            	listbox_all_weeks_year("viewschedule.php?rid=$rid&sid=$sid&yid=$yid&p=$tabperiod&nw=", $allweeksinyear, $nw);
                $rooms = get_records_select('monit_school_room', "schoolid=$sid", '', 'id, name');
                listbox_room("viewschedule.php?rid=$rid&sid=$sid&yid=$yid&sh=$sh&p=$tabperiod&nw=$nw&idroom=", $rooms, $idroom);
                echo '</table>'; 
                
                
                if ($idroom != 0 and $nw != 0){   

                    $table = table_schedule_week_view(1, 'room', 'listroom', '50%');
               		$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 
                                     'cald' => $cald,  'calm' => $calm, 'calg' => $calg, 'p'=> $tabperiod, 
                                     'nw' => $nw, 'idroom' => $idroom, 'prxls' => 'roomlist', 'action' => 'excel');
            		echo '<table align="center" border=0><tr><td>';
            		print_single_button("viewschedule.php", $options, get_string("downloadexcel"));
            	   	echo '</td><td>';
                }
  
      break;
//////Просмотр расписания по учителям             
      case 'teacher':

            	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
            	listbox_all_weeks_year("viewschedule.php?rid=$rid&sid=$sid&yid=$yid&p=$tabperiod&nw=", $allweeksinyear, $nw);
                $teachers = get_records_sql("SELECT  teacherid, lastname, firstname, schoolid
                                             FROM   mou.mdl_monit_school_teacher, mou.mdl_user
                                             WHERE mou.mdl_monit_school_teacher.teacherid=mou.mdl_user.id
                                                   and schoolid=$sid
                                             ORDER BY lastname;");
                listbox_teacher("viewschedule.php?rid=$rid&sid=$sid&yid=$yid&sh=$sh&p=$tabperiod&nw=$nw&idteacher=", $teachers, $idteacher);
                echo '</table>'; 
                
                
                if ($idteacher != 0 and $nw != 0){   

                    $table = table_schedule_week_view(1, 'teacher', 'table');
               		$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 
                                     'cald' => $cald,  'calm' => $calm, 'calg' => $calg, 'p'=> $tabperiod, 
                                     'nw' => $nw, 'prxls' => 'teacher', 'action' => 'excel');
            		echo '<table align="center" border=0><tr><td>';
            		print_single_button("viewschedule.php", $options, get_string("downloadexcel"));
            	   	echo '</td><td>';
                }   

      break;
//////Просмотр расписания по учителям списком            
      case 'teacherlist':

            	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
            	listbox_all_weeks_year("viewschedule.php?rid=$rid&sid=$sid&yid=$yid&p=$tabperiod&nw=", $allweeksinyear, $nw);
                $teachers = get_records_sql("SELECT  teacherid, lastname, firstname, schoolid
                                             FROM   mou.mdl_monit_school_teacher, mou.mdl_user
                                             WHERE mou.mdl_monit_school_teacher.teacherid=mou.mdl_user.id
                                                   and schoolid=$sid
                                             ORDER BY lastname;");
                listbox_teacher("viewschedule.php?rid=$rid&sid=$sid&yid=$yid&sh=$sh&p=$tabperiod&nw=$nw&idteacher=", $teachers, $idteacher);
                echo '</table>'; 
                
                
                if ($idteacher != 0 and $nw != 0){   

                    $table = table_schedule_week_view(1, 'teacher', 'listteacher', '50%');
               		$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 
                                     'cald' => $cald,  'calm' => $calm, 'calg' => $calg, 'p'=> $tabperiod, 
                                     'nw' => $nw, 'prxls' => 'teacherlist', 'action' => 'excel');
            		echo '<table align="center" border=0><tr><td>';
            		print_single_button("viewschedule.php", $options, get_string("downloadexcel"));
            	   	echo '</td><td>';
                }   
 
      break;
    }//конец switch ($tabperiod)

    
    
?>