<?php

function lessons_schedule_sql($typesql)
{
    global  $CFG, $yid, $rid, $sid, $cald, $calm, $calg, $idroom, $idteacher;
    //определяем день недели
    switch ($typesql){
        case 'day':
                    $lessons = get_records_sql("
                                SELECT
                                    schedule.id, schedule.schoolid, schedule.classid, schedule.classdisciplineid,
                                    schedule.lessonid, schedule.teacherid, schedule.roomid,
                                    schedule.datestart,
                                    schedule.schedulebellsid, schedule.disciplineid,
                                    discipline.name      AS namediscipline,
                                    discipline.shortname AS shortnamediscipline,
                                    schedule_bells.timestart, schedule_bells.timeend,
                                    schedule_bells.lessonnum, schedule_bells.weekdaynum
                                FROM
                                    {$CFG->prefix}monit_school_class_schedule_$rid AS schedule,
                                    {$CFG->prefix}monit_school_discipline     AS discipline,
                                    {$CFG->prefix}monit_school_schedule_bells AS schedule_bells
                                WHERE
                                    schedule.schoolid=$sid
                                    and schedule.datestart = '$calg-$calm-$cald'
                                    and discipline.id = schedule.disciplineid
                                    and schedule_bells.id = schedule.schedulebellsid
                                ORDER BY schedule.datestart, schedule_bells.weekdaynum, schedule_bells.lessonnum");
        break;
        case 'room':

                    $lessons = get_records_sql("
                                SELECT
                                    schedule.id, schedule.schoolid, schedule.classid, schedule.classdisciplineid,
                                    schedule.lessonid, schedule.teacherid, schedule.roomid,
                                    schedule.datestart,
                                    schedule.schedulebellsid, schedule.disciplineid,
                                    discipline.name      AS namediscipline,
                                    discipline.shortname AS shortnamediscipline,
                                    schedule_bells.timestart, schedule_bells.timeend,
                                    schedule_bells.lessonnum, schedule_bells.weekdaynum
                                FROM
                                    {$CFG->prefix}monit_school_class_schedule_$rid AS schedule,
                                    {$CFG->prefix}monit_school_discipline     AS discipline,
                                    {$CFG->prefix}monit_school_schedule_bells AS schedule_bells
                               WHERE
                                    schedule.schoolid  = $sid
                                    and schedule.datestart = '$calg-$calm-$cald'
                                    and schedule.roomid    = $idroom
                                    and discipline.id = schedule.disciplineid
                                    and schedule_bells.id = schedule.schedulebellsid
                               ORDER BY schedule.datestart, schedule_bells.weekdaynum, schedule_bells.lessonnum"); 
 
        break;
        case 'teacher':
    
                    $lessons = get_records_sql("
                                SELECT
                                    schedule.id, schedule.schoolid, schedule.classid, schedule.classdisciplineid,
                                    schedule.lessonid, schedule.teacherid, schedule.roomid,
                                    schedule.datestart,
                                    schedule.schedulebellsid, schedule.disciplineid,
                                    discipline.name      AS namediscipline,
                                    discipline.shortname AS shortnamediscipline,
                                    schedule_bells.timestart, schedule_bells.timeend,
                                    schedule_bells.lessonnum, schedule_bells.weekdaynum
                                FROM
                                    {$CFG->prefix}monit_school_class_schedule_$rid AS schedule,
                                    {$CFG->prefix}monit_school_discipline     AS discipline,
                                    {$CFG->prefix}monit_school_schedule_bells AS schedule_bells
                               WHERE
                                    schedule.schoolid  = $sid
                                    and schedule.datestart = '$calg-$calm-$cald'
                                    and schedule.teacherid    = $idteacher
                                    and discipline.id = schedule.disciplineid
                                    and schedule_bells.id = schedule.schedulebellsid
                               ORDER BY schedule.datestart, schedule_bells.weekdaynum, schedule_bells.lessonnum");

        break;
                                                                    
    }
    
    return $lessons;
}

function table_schedule_day_view($typesql, $typetable,  $tablewidth = '90%')
{ 
    global  $CFG, $yid, $rid, $sid, $tabperiod, $schedulemenu, $cald, $calm, $calg;

    //запрос наименования школы
    $schoolsql= get_record_sql ("SELECT id, name 
                                 FROM {$CFG->prefix}monit_school
                                 WHERE id=$sid");
        
    
   
    //запрос классов выбранной школы
    $classes = get_records_sql ("SELECT id, name  
                               FROM {$CFG->prefix}monit_school_class
                               WHERE schoolid=$sid
                               ORDER BY parallelnum, name");
    
    //Запрос расписания на день
    $lessons = lessons_schedule_sql($typesql); 
    
    //массив titles для 
    $table1->titles   = array();
    $table1->titles[] = $schoolsql->name;
    $table1->titles[] = "$cald.$calm.$calg";
    $table1->titlesrows = array (30,30);
    //проверяем, что классы есть у данной школы   
    if($classes <> NULL)    {
         //ширина таблицы
         $table1->width = $tablewidth;
         //параметр позволяет выводить таблицу масштабируя по ширине страницы
         $table1->class = 'environmenttable generaltable';
         //задаём заголовок таблицы уроков
         $table1->head  = array ();
         //выравнивание заголовка  
         $table1->align  = array ();
         $table1->columnwidth = array ();
         $table1->size  = array ();
         //номер урока
         $table1->head[]  = '№ урока';
         $table1->align[] = 'center';
         $table1->columnwidth[] = 10;
         $table1->size[]  = '50';
         //период
         $table1->head[]  = 'Время';
         $table1->align[] = 'center';
         $table1->columnwidth[] = 10;
         $table1->size[]  = '70';
         
         //table либо list
         switch ($typetable){
             //////Просмотр table             
             case 'table':
                         //добавления в заголовок наименование классов школы
                         foreach ($classes as $class) {
                              $table1->head[]  = $class->name;
                              $table1->align[] = 'center';
                              $table1->columnwidth[] = 15;
                              $table1->size[]  = '100';
                         }
             break;
             //listteacher
             case 'listteacher':
                         //предмет
                         $table1->head[]  = 'Предмет';
                         $table1->align[] = 'center';
                         $table1->columnwidth[] = 15;
                         $table1->size[]  = '30%';
                         //Кабинет
                         $table1->head[]  = 'Кабинет';
                         $table1->align[] = 'center';
                         $table1->columnwidth[] = 15;
                         $table1->size[]  = '30%';
                         //класс
                         $table1->head[]  = 'Класс';
                         $table1->align[] = 'center';
                         $table1->columnwidth[] = 15;
                         $table1->size[]  = '10%';              
             break;
             //listroom
             case 'listroom':
                         //предмет
                         $table1->head[]  = 'Предмет';
                         $table1->align[] = 'center';
                         $table1->columnwidth[] = 15;
                         $table1->size[]  = '30%';
                         //Кабинет
                         $table1->head[]  = 'Учитель';
                         $table1->align[] = 'center';
                         $table1->columnwidth[] = 15;
                         $table1->size[]  = '30%';
                         //класс
                         $table1->head[]  = 'Класс';
                         $table1->align[] = 'center';
                         $table1->columnwidth[] = 15;
                         $table1e->size[]  = '10%';         
             break;
         }
         
         
         
         
         $table1->downloadfilename = "day_{$sid}_$cald-$calm-$calg";
         $table1->worksheetname = $table1->downloadfilename;
    //завершили формирование заголовка таблицы уроков
    }
    
    //форирование таблицы данных уроков                                    
    $tableschedule->data  = array (); 
    
    //Проверяем что есть уроки в этот день      
    if($lessons <> NULL)    {
        
        
        
        //table либо list
         switch ($typetable){
             //////Просмотр table             
             case 'table':
                        
                        //день недели
                        foreach ($lessons as $week) {
                            //номер урока
                            $tableschedule->data[$week->schedulebellsid]['urok'] = $week->lessonnum;
                            //время урока
                            $tableschedule->data[$week->schedulebellsid]['nachalo'] = '<CENTER>'.$week->timestart.'<br>-<br>'.$week->timeend.'</CENTER>';
                        }
                        
                        //Как бы заранее обнуляем массив уроков в расписании 
                        foreach ($classes as $class) {
                            foreach ($lessons as $lesson) {
                                $tableschedule->data[$lesson->schedulebellsid][$class->id] = '<CENTER>-</CENTER>';
                            }
                        }
                        
                        //Изменяем заранее обнулённый массив на уроки
                        foreach ($lessons as $lesson)   {
                            //
                            if($tableschedule->data[$lesson->schedulebellsid][$lesson->classid]=='<CENTER>-</CENTER>')  {
                              //
                                if($lesson->shortnamediscipline == NULL)    {
                                    $tableschedule->data[$lesson->schedulebellsid][$lesson->classid] = '<CENTER>'.$lesson->namediscipline.'</CENTER>';
                                } else {
                                    $tableschedule->data[$lesson->schedulebellsid][$lesson->classid] = '<CENTER>'.$lesson->shortnamediscipline.'</CENTER>';  
                                }
                            } else {
                              //
                                if($lesson->shortnamediscipline == NULL)    {
                                    $tableschedule->data[$lesson->schedulebellsid][$lesson->classid] = $tableschedule->data[$lesson->schedulebellsid][$lesson->classid].'<CENTER> - <br>'.$lesson->namediscipline.'</CENTER>';
                                } else {
                                    $tableschedule->data[$lesson->schedulebellsid][$lesson->classid] = $tableschedule->data[$lesson->schedulebellsid][$lesson->classid].'<CENTER> - <br>'.$lesson->shortnamediscipline.'</CENTER>';  
                                }
                            }
                            // добавляем аудитории
                            //запрос имени аудитории
                            $roomsql = get_records_sql("SELECT id, schoolid, name 
                                                        FROM {$CFG->prefix}monit_school_room 
                                                        WHERE schoolid=$sid and id=$lesson->roomid");
                            // проверяем что она есть
                            if($roomsql<>NULL)  {
                                foreach ($roomsql as $roomname) {
                                //приписываем аудиторию
                                    $tableschedule->data[$lesson->schedulebellsid][$lesson->classid] = $tableschedule->data[$lesson->schedulebellsid][$lesson->classid].'<center> каб. '.$roomname->name.'</center>';
                                }       
                            }
                            //завершаем добавление аудиторий
                            
                            // добавляем преподователей
                             //запрос преподователя
                            $teachersql = get_records_sql("SELECT id, firstname, lastname 
                                                           FROM {$CFG->prefix}user 
                                                           WHERE id=$lesson->teacherid");
                            // проверяем что он есть
                            if($teachersql<>NULL)   {
                                foreach ($teachersql as $teachername) {
                                //приписываем преподователя
                                    $tableschedule->data[$lesson->schedulebellsid][$lesson->classid] = $tableschedule->data[$lesson->schedulebellsid][$lesson->classid].'<center>'.$teachername->lastname.'<br>';
                                //
                                    foreach (explode(" ", $teachername->firstname) as $no) {
                                        $tableschedule->data[$lesson->schedulebellsid][$lesson->classid] = $tableschedule->data[$lesson->schedulebellsid][$lesson->classid].substr($no, 0, 2).'.';
                                    }
                                }       
                            }
                            //завершаем добавления преподователей
                        }
                        //окончание изменения обнулённого массива           
                        
             break;
             //listteacher
             case 'listteacher':
             
                               //Как бы заранее обнуляем массив таблицы списка занятости кабинета
                               foreach ($lessons as $lesson) {
                                    $tableschedule->data[$lesson->id]['numlessons'] = '<CENTER>-</CENTER>';
                                    $tableschedule->data[$lesson->id]['timelessons'] = '<CENTER>-</CENTER>';
                                    $tableschedule->data[$lesson->id]['disipline'] = '<CENTER>-</CENTER>';
                                    $tableschedule->data[$lesson->id]['room'] = '<CENTER>-</CENTER>';
                                    $tableschedule->data[$lesson->id]['classlessons'] = '<CENTER>-</CENTER>';
                                }
                              
                                //Изменяем заранее обнулённый массив на уроки
                                foreach ($lessons as $lesson) {
                                    //номер урока
                                    $tableschedule->data[$lesson->id]['numlessons'] = $lesson->lessonnum;
                                    //время урока
                                    $tableschedule->data[$lesson->id]['timelessons'] = '<CENTER>'.$lesson->timestart.'<br>-<br>'.$lesson->timeend.'</CENTER>';
                                
                                    //Добавляем наименование предмета  
                                    if($lesson->shortnamediscipline == NULL){
                                        $tableschedule->data[$lesson->id]['disipline'] = '<CENTER>'.$lesson->namediscipline.'</CENTER>';
                                    }else{
                                        $tableschedule->data[$lesson->id]['disipline'] = '<CENTER>'.$lesson->shortnamediscipline.'</CENTER>';  
                                    }
                                    //завершаем добавление аудиторий
                           
                                    // добавляем Кабинет
                                    //запрос кабинета
                                    $roomsql = get_records_sql("SELECT  id, name 
                                                                FROM    {$CFG->prefix}monit_school_room 
                                                                WHERE   id=$lesson->roomid");
                                
                                    // проверяем что он есть
                                    if($roomsql<>NULL){
                                        foreach ($roomsql as $roomname) {
                                            //приписываем преподователя
                                            $tableschedule->data[$lesson->id]['room'] = '<center>'.$roomname->name.'</center>';
                                        }       
                                    }
                                    //завершаем добавления преподователей

                                    //Добавляем класс
                                    $classsql = get_records_sql("SELECT id, name  
                                                                FROM    {$CFG->prefix}monit_school_class 
                                                                WHERE   id=$lesson->classid");
                                    
                                    // проверяем что она есть
                                    if($classsql<>NULL){
                                        foreach ($classsql as $classname) {
                                        //приписываем наименование класса
                                            $tableschedule->data[$lesson->id]['classlessons'] = '<center>'.$classname->name.'</center>';
                                        }       
                                    }
                                    //Завершаем добавление класса
                                    
                              }
                              //окончание изменения обнулённого массива
                                    
             break;
             //listroom
             case 'listroom':
                         
                            //Как бы заранее обнуляем массив таблицы списка занятости кабинета
                            foreach ($lessons as $lesson) {
                                $tableschedule->data[$lesson->id]['numlessons'] = '<CENTER>-</CENTER>';
                                $tableschedule->data[$lesson->id]['timelessons'] = '<CENTER>-</CENTER>';
                                $tableschedule->data[$lesson->id]['disipline'] = '<CENTER>-</CENTER>';
                                $tableschedule->data[$lesson->id]['teacher'] = '<CENTER>-</CENTER>';
                                $tableschedule->data[$lesson->id]['classlessons'] = '<CENTER>-</CENTER>';
                            }
                          
                            //Изменяем заранее обнулённый массив на уроки
                            foreach ($lessons as $lesson) {
                                //номер урока
                                $tableschedule->data[$lesson->id]['numlessons'] = $lesson->lessonnum;
                                //время урока
                                $tableschedule->data[$lesson->id]['timelessons'] = '<CENTER>'.$lesson->timestart.'<br>-<br>'.$lesson->timeend.'</CENTER>';
                            
                                //Добавляем наименование предмета  
                                if($lesson->shortnamediscipline == NULL){
                                    $tableschedule->data[$lesson->id]['disipline'] = '<CENTER>'.$lesson->namediscipline.'</CENTER>';
                                }else{
                                    $tableschedule->data[$lesson->id]['disipline'] = '<CENTER>'.$lesson->shortnamediscipline.'</CENTER>';  
                                }

                                // добавляем преподователей
                                //запрос преподователя
                                $teachersql = get_records_sql("SELECT   id, firstname, lastname
                                                              FROM      {$CFG->prefix}user
                                                              WHERE     id=$lesson->teacherid");
                                // проверяем что он есть
                                if($teachersql<>NULL){
                                    foreach ($teachersql as $teachername) {
                                        //приписываем преподователя
                                        $tableschedule->data[$lesson->id]['teacher'] = '<center>'.$teachername->lastname.'<br>';
                                        //
                                        foreach (explode(" ", $teachername->firstname) as $no) {
                                            $tableschedule->data[$lesson->id]['teacher'] = $tableschedule->data[$lesson->id]['teacher'].substr($no, 0, 2).'.';
                                        }
                                    }       
                                }
                                //завершаем добавления преподователей
                       
                                //Добавляем класс
                                $classsql = get_records_sql("SELECT     id, name  
                                                            FROM        {$CFG->prefix}monit_school_class 
                                                            WHERE       id=$lesson->classid");
                                // проверяем что она есть
                                if($classsql<>NULL){
                                    foreach ($classsql as $classname) {
                                        //приписываем наименование класса
                                        $tableschedule->data[$lesson->id]['classlessons'] = '<center>'.$classname->name.'</center>';
                                    }       
                                }
                                //Завершаем добавление класса
                          }
                          //окончание изменения обнулённого массива     
                                 
             break;
         }//Конец switch 
 
    }//Конец if 

    $table1->data = array();
    foreach ($tableschedule->data as $tablerow)  {
         $tablexlsrow = array();
           foreach ($tablerow as $td)  {
            $tablexlsrow[] = $td;
           }
         $table1->data[] = $tablexlsrow;
   }
    
    return $table1;
} 


function table_schedule_week_view($seetable, $typesql, $typetable, $tablewidth = '90%')
{ 
    global  $CFG, $GLDATESTART, $GLDAY, $yid, $rid, $sid, $tabperiod, $schedulemenu, $cald, $calm, $calg, $nw;
    
 	$weekmenu = array();
   
	if ($nw != 0)  {
		$datestart = $GLDATESTART[$nw];
        for ($i=1; $i<=7; $i++)  {
        	$GLDAY[$i] = date("Y-m-d", $datestart);
        	$dayweek = date("d.m.y", $datestart);
			$weekmenu[$i] = $dayweek;
            list($cald, $calm, $calg) = explode ('.', $weekmenu[$i] );
            if ($seetable == 1)  {
                //Сформируем и выведим таблицу дня недели
                $tablehead->head  = array (); 
                $tablehead->head[]  = name_day_in_week($i).' '.$cald.'.'.$calm.'.'.$calg.' ' ;
                $tablehead->width = $tablewidth;
                print_table($tablehead);
                //Завершили и вывели
                //Сформируем и выведим таблицу расписания
                $table = array();//
                $table = table_schedule_day_view($typesql, $typetable, $tablewidth);//
                print_table($table);
                //Завершили и вывели
            }//Конец if
			$datestart = $datestart + DAYSECS;
            $weekmenu[$i] = $i.': '.name_day_in_week($i).' '.$weekmenu[$i] ;
		}
	}	

    return $weekmenu;   
}

function table_schedule_week_view_converter($typesql, $typetable, $nw)
{ 
    global  $CFG, $GLDATESTART, $GLDAY, $yid, $rid, $sid, $tabperiod, $schedulemenu, $cald, $calm, $calg;
    
    $weekmenu = array();
    
    $table->data = array();//
	if ($nw != 0)  {
		$datestart = $GLDATESTART[$nw];
        for ($i=1; $i<=7; $i++)  {
        	$GLDAY[$i] = date("Y-m-d", $datestart);
        	$dayweek = date("d.m.y", $datestart);
            $weekmenu[$i] = $dayweek;
            list($cald, $calm, $calg) = explode ('.', $weekmenu[$i] );
            //Сформируем таблицу расписания
            $tableschedule = array();
            $tableschedule = table_schedule_day_view($typesql, $typetable);
            //Добавили дату
            $table->data[][] = name_day_in_week($i).' '.$cald.'.'.$calm.'.'.$calg.' ' ;
            $table->data[] = $tableschedule->head;
            //
            foreach ($tableschedule->data as $tablerow)  {
                $tablexlsrow = array();
                foreach ($tablerow as $td)  {
                    $tablexlsrow[] = $td;
                }
                $table->data[] = $tablexlsrow;
            }
            //$table->titles = $tableschedule->titles;
            $table->columnwidth = $tableschedule->columnwidth;
            $table->head = $tableschedule->head;
            $table->align = $tableschedule->align;
            //Завершили
			$datestart = $datestart + DAYSECS;
		}
	}
    //запрос наименования школы
    $schoolsql= get_record_sql ("SELECT id, name 
                                 FROM {$CFG->prefix}monit_school
                                 WHERE id=$sid");
    //массив titles для 
    $table->titles   = array();
    $table->titles[] = $schoolsql->name;
    
    $table->titlesrows = array (30,30);
    $table->class = 'moutable';
    $table->width = '90%';
    $table->downloadfilename = "week{$typesql}_{$sid}_$nw";
    //$table->downloadfilename = "qwerqwr";
    $table->worksheetname = $table->downloadfilename;	

    return $table;   
}

function listbox_room($scriptname, $rooms, &$idroom){
      
    global $CFG;

    $roomsview = array();
    $roomsview[0]='-';
    foreach ($rooms as $roomsvi){
        $roomsview[$roomsvi->id]=$roomsvi->name;
    }
    echo '<br><tr><td>Кабинет:</td><td>';
    popup_form($scriptname, $roomsview, 'switcroom', $idroom, '', '', '', false);
    echo '</td></tr>';

    return 1;
}

function listbox_teacher($scriptname, $teachers, &$idteacher){
      
    global $CFG;

    $teachersview = array();
    $teachersview[0]='-';
    foreach ($teachers as $teachersvi)	{
        $teachersview[$teachersvi->teacherid]= $teachersvi->lastname.' '.$teachersvi->firstname;
    }
    echo '<br><tr><td>Учитель:</td><td>';
    popup_form($scriptname, $teachersview, 'switcteacher', $idteacher, '', '', '', false);
    echo '</td></tr>';
     
    return 1;
} 

function name_day_in_week($numday)
{
    //определяем день недели
    switch ($numday){
        case '1':
                 $name  = get_string('monday', 'calendar');
        break;
        case '2':                             	                                  
                 $name  = get_string('tuesday', 'calendar');
        break;                                                            
        case '3':                                 
                 $name  = get_string('wednesday', 'calendar');
        break;                                                            
        case '4':                                 
                 $name  = get_string('thursday', 'calendar');
        break;                                                            
        case '5':                                 
                 $name  = get_string('friday', 'calendar');
        break;                                                            
        case '6':                                 
                 $name  = get_string('saturday', 'calendar');
        break;                                                            
        case '7':                                 
                 $name  = get_string('sunday', 'calendar');
        break;
    }
    
    return $name;
}

?>