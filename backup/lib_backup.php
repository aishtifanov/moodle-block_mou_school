<?php

    function list_table($rid, $sid)
    {
        global $CFG;

       
        //Список таблиц, условий (поле , значение) для backup
        /*
          0-> таблица, 
          1-> поле запроса,
          2-> значение запроса,
          3-> название по русски,
          4-> использовать ли таблицу в формировании архива.
        */
        $listdbtable   = array();
        $listdbtable[] = array('monit_school', 'id', $sid, 'Школы', 1);
        $listdbtable[] = array("monit_school_assignments_$rid", 'schoolid', $sid, 'Задания', 1);
        // !!!!!!!!!!!!!!!!!!!!!!!!!!!
        // $listdbtable[] = array("monit_school_attendance_$rid", 'schoolid', $sid, 'Посещаемость', 1);
        $listdbtable[] = array('monit_school_class', 'schoolid', $sid, 'Классы', 1);
        $listdbtable[] = array('monit_school_class_discipline', 'schoolid', $sid, 'Дисциплины классов', 1);
        $listdbtable[] = array("monit_school_class_schedule_$rid", 'schoolid', $sid, 'Расписание', 1);
        $listdbtable[] = array('monit_school_class_smena', 'schoolid', $sid, 'Смены', 1);
        $listdbtable[] = array('monit_school_class_termtype', 'schoolid', $sid, 'Тип учебного периода', 1);
        $listdbtable[] = array('monit_school_component', 'schoolid', $sid, 'Компоненты учебного плана', 1);
        $listdbtable[] = array('monit_school_curriculum', 'schoolid', $sid, 'Учебные планы', 1);
        $listdbtable[] = array('monit_school_curriculum_totals', 'schoolid', $sid, 'Учебные планы', 1);
        $listdbtable[] = array('monit_school_discipline', 'schoolid', $sid, 'Дисциплины', 1);
        $listdbtable[] = array('monit_school_discipline_domain', 'schoolid', $sid, 'Предметная область', 1);
        $listdbtable[] = array('monit_school_discipline_group', 'schoolid', $sid, 'Группы', 1);
        $listdbtable[] = array("monit_school_discipline_lesson_$rid", 'schoolid', $sid, 'Уроки', 1);
        $listdbtable[] = array('monit_school_discipline_plan', 'schoolid', $sid, 'Учебный план', 1);
        $listdbtable[] = array('monit_school_discipline_unit', 'schoolid', $sid, 'Раздел дисциплины', 1);
        $listdbtable[] = array('monit_school_holidays', 'schoolid', $sid, 'Каникулы', 1);
        $listdbtable[] = array('monit_school_marks_totals_term', 'schoolid', $sid, 'Итоговы оценки по четвертям', 1);
        $listdbtable[] = array('monit_school_portfolio', 'schoolid', $sid, 'Портфолио', 1);
        $listdbtable[] = array('monit_school_profiles_curriculum', 'schoolid', $sid, 'Учебные планы', 1);
        $listdbtable[] = array('monit_school_pupil_card', 'schoolid', $sid, 'Карточки учащихся', 1);
        $listdbtable[] = array('monit_school_room', 'schoolid', $sid, 'Кабинеты', 1);
        $listdbtable[] = array('monit_school_schedule_bells', 'schoolid', $sid, 'Звонки', 1);
        $listdbtable[] = array('monit_school_subgroup', 'schoolid', $sid, 'Подгруппы', 1);
        $listdbtable[] = array('monit_school_subgroup_pupil', 'schoolid', $sid, 'Учащиеся подгруппы', 1);
        $listdbtable[] = array('monit_school_teacher', 'schoolid', $sid, 'Учителя', 1);
        $listdbtable[] = array('monit_school_term', 'schoolid', $sid, 'Четверти', 1);
        $listdbtable[] = array('monit_school_textbook', 'schoolid', $sid, 'Учебники', 1);
        $listdbtable[] = array('monit_school_umk', 'schoolid', $sid, 'УМК', 1);
        $listdbtable[] = array('monit_att_staff', 'schoolid', $sid, 'Аттестация', 1);
        $listdbtable[] = array('user', 'schoolid', $sid, 'Пользователи', 0);
        $listdbtable[] = array("monit_school_marks_$rid", 'schoolid', $sid, 'Оценки', 0);
        
        return $listdbtable;    
    }

    function create_xml_mouschool($rid, $sid)
    {
        global $CFG, $db;
        
        //Получаем список таблиц    
        $listdbtable = list_table($rid, $sid);

        $namefile = "backup$sid".date("_Y_M_j_H_i_s").".xml";
        $namefilezip = "backup$sid".date("_Y_M_j_H_i_s").".zip";
            
        print_heading('Резервное копирование данных Электронной школы');
        // notify('Началось востановление', 'green', 'left');
        print_simple_box_start("center", '50%', 'white');

	    //Start the main table
	    echo '<table cellpadding=5><tr><td align=right><b>';
	    echo get_string("name").':</b></td><td>';
	    echo $namefilezip;
	    echo "</td></tr>";
	    //Start the main tr, where all the backup progress is done
	    echo "<tr><td colspan=\"2\">";
	    //Start the main ul
	    echo "<ul>";
        
            
        $xml=new DomDocument('1.0','utf-8');
        $mou_school_backup = $xml->appendChild($xml->createElement('mou_school_backup'));
        $monit_schools = $mou_school_backup->appendChild($xml->createElement('monit_schools'));
        //Пробегаем по backup школы
    
        foreach ($listdbtable as $sdbtable) {
                if($sdbtable[4] == 1){
                    // notify( $sdbtable[0] , 'green', 'left');
                    echo "<li>Копирование данных: '".$sdbtable[3].'\'</li>';
                    $sqlrequestsql = "SELECT * FROM {$CFG->prefix}{$sdbtable[0]} WHERE {$sdbtable[1]} = {$sdbtable[2]}";
                    if($requesttable = get_records_sql( $sqlrequestsql)) {
                        $columntablesql =  "SELECT column_name
                                            FROM information_schema.columns
                                            WHERE  TABLE_SCHEMA = 'mou' AND table_name = '{$CFG->prefix}{$sdbtable[0]}'";
                         if($columntable = get_records_sql($columntablesql)) {
                             foreach ($requesttable as $backup) {
                                $$sdbtable[0] = $monit_schools->appendChild($xml->createElement($sdbtable[0]));
                                foreach ($columntable as $columnt) {
                                    $name = $$sdbtable[0]->appendChild($xml->createElement($columnt->column_name));
                                    $namecolumn = $columnt->column_name;
                                    $name->appendChild($xml->createTextNode($backup->$namecolumn));
                                }//конец foreach
                            }//конец foreach
                        }//конец if
                    }//конец if
                }//конец if
        }//конец foreach

        //добавляем user
        echo "<li>Копирование данных: 'Пользователи'</li>";
        $listusersql = "SELECT u.id, u.auth, u.confirmed, u.policyagreed, u.deleted,
                                 u.mnethostid, u.username, u.password, u.idnumber, u.firstname, 
                                 u.lastname, u.email, u.emailstop, u.icq, u.skype, u.yahoo, 
                                 u.aim, u.msn, u.phone1, u.phone2, u.institution, u.department, 
                                 u.address, u.city, u.country, u.lang, u.theme, u.timezone, 
                                 u.firstaccess, u.lastaccess, u.lastlogin, u.currentlogin, 
                                 u.lastip, u.secret, u.picture, u.url, u.description, 
                                 u.mailformat, u.maildigest, u.maildisplay, u.htmleditor,
                                 u.ajax, u.autosubscribe, u.trackforums, u.timemodified,
                                 u.trustbitmask, u.imagealt, u.screenreader
                             FROM {$CFG->prefix}user AS u, {$CFG->prefix}monit_school_pupil_card AS p
                             WHERE u.id = p.userid AND p.schoolid = $sid";
                
        if($requesttable = get_records_sql( $listusersql)) {
        $columntablesql =  "SELECT column_name
                            FROM information_schema.columns
                            WHERE  TABLE_SCHEMA = 'mou' AND table_name = '{$CFG->prefix}user'";
            if($columntable = get_records_sql($columntablesql)) {
                foreach ($requesttable as $backup) {
                    $user = $monit_schools->appendChild($xml->createElement('user'));
                    foreach ($columntable as $columnt) {
                        $name = $user->appendChild($xml->createElement($columnt->column_name));
                         $namecolumn = $columnt->column_name;
                         $name->appendChild($xml->createTextNode($backup->$namecolumn));
                    }//конец foreach
                }//конец foreach
            }//конец if
        }//конец if
             
        //добавляем оценки mark
        echo "<li>Копирование данных: 'Оценки'</li>";
        //определяем номер района
        $regionid = get_record_sql( "SELECT rayonid FROM {$CFG->prefix}monit_school WHERE id=$sid");
        $listusersql = "SELECT u.id
                             FROM {$CFG->prefix}user AS u, {$CFG->prefix}monit_school_pupil_card AS p
                             WHERE u.id = p.userid AND p.schoolid = $sid";
        if($requestuser = get_records_sql( $listusersql)) {
            foreach ($requestuser as $useridlist) {   
                $markslistsql = "SELECT id, userid, scheduleid, mark, mark2, datedone
                                    FROM {$CFG->prefix}monit_school_marks_{$regionid->rayonid}
                                    WHERE userid = {$useridlist->id}";
                if($requesttable = get_records_sql($markslistsql)) {
                    $columntablesql =  "SELECT column_name
                                            FROM information_schema.columns
                                            WHERE  TABLE_SCHEMA = 'mou' AND table_name = '{$CFG->prefix}monit_school_marks_{$regionid->rayonid}'";
                    if($columntable = get_records_sql($columntablesql)) {
                        foreach ($requesttable as $backup) {
                            $user = $monit_schools->appendChild($xml->createElement("monit_school_marks_".$regionid->rayonid));
                            foreach ($columntable as $columnt) {
                                $name = $user->appendChild($xml->createElement($columnt->column_name));
                                 $namecolumn = $columnt->column_name;
                                 $name->appendChild($xml->createTextNode($backup->$namecolumn));
                            }//конец foreach
                        }//конец foreach
                    }//конец if
                }//конец if
            }//конец foreach
        }//конец if     
            
        $basedir = make_upload_directory("0/mou_school_backup/".$sid);
                    
        $xml->formatOutput = true;
        $dir = "0/mou_school_backup/$sid";
        $rootsave = $CFG->dataroot.'/'.$dir.'/'.$namefile ;
        $xml->save($rootsave);
            
        //Convert them to full paths
        $files = array();
        $files[] = "$basedir/$namefile";
        $status = zip_files($files, "$basedir/$namefilezip");
        unlink("$basedir/$namefile");
             
        //id, schoolid, name, databackup
        $addelement->schoolid = $sid;
        $addelement->namеb = $namefilezip;
        $addelement->databackup = date("Y-n-j H:i:s");
        $strsql = "INSERT INTO {$CFG->prefix}monit_school_backup ( SCHOOLID, NAMEB, DATABACKUP )";
        $strsql .= "VALUES ( $addelement->schoolid, '$addelement->namеb', '$addelement->databackup')"; 
        if (!$db->Execute($strsql)){
            print_r($addelement);    
        }  
            
	    //Ends th main ul
	    echo "</ul>";
	    //End the main tr, where all the backup is done
	    echo "</td></tr>";
    	    //End the main table
	    echo "</table>";
        
  	    if (!$status) {
	        error ("Резервное копирование не завершено.");
	    }
        print_simple_box('Резервное копирование успешно завершено.',"center");

	    print_simple_box_end();

        $regionid = get_record_sql( "SELECT rayonid FROM {$CFG->prefix}monit_school WHERE id=$sid");
        $url = "backup.php?rid={$regionid->rayonid}&sid=$sid";
        
        redirect($url , '', 20);              
    }
    
    function open_xml_mouschool($rid, $sid, $backupid)
    {
        
        global $CFG;        
        
        
        //Список таблиц, условий (поле , значение) для backup
        $listdbtables = list_table($rid, $sid);
        
        $listdbtable = array();
        $frm = data_submitted();
        foreach ($listdbtables AS $list){
            if( isset($frm->$list[0]) ){
                $listdbtable[] = $list;
            }//Конец if
        }//Конец foreach
        
        print_heading('Восстановление резервной копии данных Электронной школы');
        // notify('Началось востановление', 'green', 'left');
        print_simple_box_start("center", '50%', 'white');

        $monit_backupsql = "SELECT nameb FROM {$CFG->prefix}monit_school_backup WHERE id = $backupid";
        if($monit_backup = get_record_sql($monit_backupsql)) {

    	    //Start the main table
    	    echo '<table cellpadding=5><tr><td align=right><b>';
    	    echo get_string("name").':</b></td><td>';
    	    echo $monit_backup->nameb;
    	    echo "</td></tr>";
    	    //Start the main tr, where all the backup progress is done
    	    echo "<tr><td colspan=\"2\">";
    	    //Start the main ul
    	    echo "<ul>";
    
            //Дирректория
            $basedir = make_upload_directory("0/mou_school_backup/".$sid);
            //Имя файла
            $namefile = $monit_backup->nameb; 
            $zipfile = "$basedir/$namefile";
            $status = unzip_file ($zipfile, "$basedir/", false);
            $namexmlfile = substr($namefile, 0, -3)."xml" ;
            if($xml1= simplexml_load_file("$basedir/$namexmlfile")){
                //Пробегаем по backup школы
                foreach ($listdbtable as $sdbtable) {
                    //
                    // notify( $sdbtable[4] , 'green', 'left');
               	    echo "<li>Восстановление данных: '".$sdbtable[3].'\'</li>';
                    $nametableadd = $sdbtable[0];
                    foreach ($xml1->monit_schools->$nametableadd as $backuplist) {
                        foreach ($backuplist as $backup) {
                            if( isset($backup->id) ){
                                //таблица monit_school;
                                if(get_record_sql("SELECT id FROM {$CFG->prefix}{$nametableadd} WHERE id = $backup->id") ){
                                    if (!update_record($nametableadd, $backup)) {
                                        error ("Процесс востановления не завершен.");
                                    }
                                }   else{
                                    if (!insert_record_att($nametableadd , $backup))    {
                                        error ("Процесс востановления не завершен.");
                                    } 
                                }//Конец if else
                            }//Конец if
                        }//Конец foreach
                    }//Конец foreach
                }//Конец foreach
  
            }//Конец if 

            unlink("$basedir/$namexmlfile"); 
            
            //notify('Завершилось востановление', 'green', 'left');
            $regionid = get_record_sql( "SELECT rayonid FROM {$CFG->prefix}monit_school WHERE id=$sid");
            $url = "backup.php?rid={$regionid->rayonid}&sid=$sid";

    	    //Ends th main ul
    	    echo "</ul>";
    	    //End the main tr, where all the backup is done
    	    echo "</td></tr>";
        	    //End the main table
    	    echo "</table>";
      	    if (!$status) {
    	        error ("Процесс востановления не завершен.");
    	    }
            print_simple_box('Процесс востановления успешно завершен.',"center");
    
    	    print_simple_box_end();
            
            redirect($url , '', 20);  
        }//Конец if 

        return 1; 
    }
    
    function listcheckbox_table($rid, $sid){
        
    	global $CFG;
        
        //Формируем таблицу
        $table->id = 'backup'; 
        $table->head  = array ('Имя таблица',  
                               'Востановить');
        $table->align = array ("left",
                               "center");
        $table->class = 'moutable';
        $table->size = array('50%',
                             '20%');
       	$table->width = '40%';
        $table->class = 'moutable';
    
        $table->titles = array();
        $table->titles[] = 'Востановление';
        $table->worksheetname = 'backup';
        $table->data = array();
        //Список таблиц, условий (поле , значение) для backup
        $listdbtable = list_table($rid, $sid);
        
        foreach ($listdbtable as $backup) {
            //значение по умолчанию
            $name = $backup[0];
            $formcheckbox = "<input type=checkbox name=$name >";
            //Добавляет строку к таблице
            $table->data[] = array($backup[3] ,
                                   $formcheckbox);
        }

        return $table;
    }

    
    function delbackup_xml_mouschool($backupid)
    {
        global $CFG; 
        
        $monit_backupsql = "SELECT nameb, schoolid FROM {$CFG->prefix}monit_school_backup WHERE id = $backupid";
        if($monit_backup = get_record_sql($monit_backupsql)) {
            //Дирректория
            $basedir = make_upload_directory("0/mou_school_backup/".$monit_backup->schoolid);
            //Имя backup файла 
            $namefile = $monit_backup->nameb;
            //Запрос на удаление записи
            $delbackupsql = "DELETE FROM {$CFG->prefix}monit_school_backup WHERE id = $backupid";
            get_record_sql($delbackupsql);
            //удаление backup файла
            unlink("$basedir/$namefile");
        }
                
        return 1;
    }
    
    //Функция просмотра списка существующих файлов backup школы
    function table_backup_mouschool($rid, $sid, $download_capability_xml , $restoring_capability_xml)
    {
        global $CFG;

        $table->titles = array();
        $table->titles[] = get_record_sql("SELECT name FROM {$CFG->prefix}monit_school WHERE id = $sid")->name;
        $table->worksheetname = "backup_".$sid ;
        $table->downloadfilename = "backup_".$sid;
        $table->width = '60%';
        $table->class = 'moutable';
        $table->titlesrows = array (30,30);
        $table->data = array();
        
        if($download_capability_xml){
            $table->head  = array ('Имя файла', 'Дата создания', 'Востановление', 'Удаление');
            $table->align = array ("left", "center", "center", "center");
            $table->size = array('10%', '10%', '5%', '5%');
            $table->columnwidth = array ( 45, 45, 20, 20);  
        }else{
            $table->head  = array ('Имя файла', 'Дата создания');
            $table->align = array ("left", "center");
            $table->size = array('10%', '10%');
            $table->columnwidth = array ( 45, 45);
        }

        $schoolbackupsql ="SELECT id, schoolid, nameb, databackup FROM {$CFG->prefix}monit_school_backup WHERE schoolid = $sid";
        //Если выполняется запрос
        if($schoolbackup = get_records_sql($schoolbackupsql)) {
            //Пробегаем по backup школы
            foreach ($schoolbackup as $backup) {
                //Добавляем имя файла с сылкой либо без ссылки в зависимости от возможности
                if($download_capability_xml){
                    $element->namefile = "<a href='{$CFG->wwwroot}/file.php/0/mou_school_backup/$sid/{$backup->nameb}' >".$backup->nameb."</a>";
                }else{
                    $element->namefile = $backup->nameb;
                }//конец if else
                $element->data = $backup->databackup;
                if($restoring_capability_xml){
                    /*
                    $element->action = "<form method = post>
                                            <input type=hidden value='restoringxml' name=action> 
                                            <input type=hidden value=$rid name=rid>
                                            <input type=hidden value=$sid name=sid>
                                            <input type=hidden value={$backup->id} name=backupid>
                                            <input type=submit value='Востановить'>
                                        </form>";
                    */
                    $element->action = "<a href='backup.php?rid=$rid&sid=$sid&backupid={$backup->id}&restor=true'>Востановить</a>";
                    $element->actiondel = "<form method = post>
                                            <input type=hidden value='deletexml' name=deletexml> 
                                            <input type=hidden value=$rid name=rid>
                                            <input type=hidden value=$sid name=sid>
                                            <input type=hidden value={$backup->id} name=backupid>
                                            <input type=submit value='Удалить'>
                                        </form>";
                    $table->data[] = array($element->namefile, $element->data, $element->action, $element->actiondel);
                }else{
                    $table->data[] = array($element->namefile, $element->data);
                }//конец if else
            }//конец foreach
        }//Конец if

        return $table;
    }



function insert_record_att($table, $dataobject, $returnid=true, $primarykey='id', $clearprimarykey = false) {

    global $db, $CFG, $empty_rs_cache;

    if (empty($db)) {
        return false;
    }

/// Check we are handling a proper $dataobject
    if (is_array($dataobject)) {
        debugging('Warning. Wrong call to insert_record(). $dataobject must be an object. array found instead', DEBUG_DEVELOPER);
        $dataobject = (object)$dataobject;
    }

/// Temporary hack as part of phasing out all access to obsolete user tables  XXX
    if (!empty($CFG->rolesactive)) {
        if (in_array($table, array('user_students', 'user_teachers', 'user_coursecreators', 'user_admins'))) {
            if (debugging()) { var_dump(debug_backtrace()); }
            error('This SQL relies on obsolete tables ('.$table.')!  Your code must be fixed by a developer.');
        }
    }

    if (defined('MDL_PERFDB')) { global $PERF ; $PERF->dbqueries++; };

/// In Moodle we always use auto-numbering fields for the primary key
/// so let's unset it now before it causes any trouble later
    if ($clearprimarykey)   {
        unset($dataobject->{$primarykey});    
    }

/// Get an empty recordset. Cache for multiple inserts.
    if (empty($empty_rs_cache[$table])) {
        /// Execute a dummy query to get an empty recordset
        if (!$empty_rs_cache[$table] = $db->Execute('SELECT * FROM '. $CFG->prefix . $table .' WHERE '. $primarykey  .' = \'-1\'')) {
            return false;
        }
    }

    $rs = $empty_rs_cache[$table];

/// Postgres doesn't have the concept of primary key built in
/// and will return the OID which isn't what we want.
/// The efficient and transaction-safe strategy is to
/// move the sequence forward first, and make the insert
/// with an explicit id.
    if ( $CFG->dbfamily === 'postgres' && $returnid == true ) {
        if ($nextval = (int)get_field_sql("SELECT NEXTVAL('{$CFG->prefix}{$table}_{$primarykey}_seq')")) {
            $dataobject->{$primarykey} = $nextval;
        }
    }

/// Begin DIRTY HACK
    if ($CFG->dbfamily == 'oracle') {
        oracle_dirty_hack($table, $dataobject); // Convert object to the correct "empty" values for Oracle DB
    }
/// End DIRTY HACK

/// Under Oracle, MSSQL and PostgreSQL we have our own insert record process
/// detect all the clob/blob fields and change their contents to @#CLOB#@ and @#BLOB#@
/// saving them into $foundclobs and $foundblobs [$fieldname]->contents
/// Same for mssql (only processing blobs - image fields)
    if ($CFG->dbfamily == 'oracle' || $CFG->dbfamily == 'mssql' || $CFG->dbfamily == 'postgres') {
        $foundclobs = array();
        $foundblobs = array();
        db_detect_lobs($table, $dataobject, $foundclobs, $foundblobs);
    }

/// Under Oracle, if the primary key inserted has been requested OR
/// if there are LOBs to insert, we calculate the next value via
/// explicit query to the sequence.
/// Else, the pre-insert trigger will do the job, because the primary
/// key isn't needed at all by the rest of PHP code
    if ($CFG->dbfamily === 'oracle' && ($returnid == true || !empty($foundclobs) || !empty($foundblobs))) {
    /// We need this here (move this function to dmlib?)
        include_once($CFG->libdir . '/ddllib.php');
        $xmldb_table = new XMLDBTable($table);
        $seqname = find_sequence_name($xmldb_table);
        if (!$seqname) {
        /// Fallback, seqname not found, something is wrong. Inform and use the alternative getNameForObject() method
            debugging('Sequence name for table ' . $table->getName() . ' not found', DEBUG_DEVELOPER);
            $generator = new XMLDBoci8po();
            $generator->setPrefix($CFG->prefix);
            $seqname = $generator->getNameForObject($table, $primarykey, 'seq');
        }
        if ($nextval = (int)$db->GenID($seqname)) {
            $dataobject->{$primarykey} = $nextval;
        } else {
            debugging('Not able to get value from sequence ' . $seqname, DEBUG_DEVELOPER);
        }
    }

/// Get the correct SQL from adoDB
    if (!$insertSQL = $db->GetInsertSQL($rs, (array)$dataobject, true)) {
        return false;
    }

/// Under Oracle, MSSQL and PostgreSQL, replace all the '@#CLOB#@' and '@#BLOB#@' ocurrences to proper default values
/// if we know we have some of them in the query
    if (($CFG->dbfamily == 'oracle' || $CFG->dbfamily == 'mssql' || $CFG->dbfamily == 'postgres') &&
      (!empty($foundclobs) || !empty($foundblobs))) {
    /// Initial configuration, based on DB
        switch ($CFG->dbfamily) {
            case 'oracle':
                $clobdefault = 'empty_clob()'; //Value of empty default clobs for this DB
                $blobdefault = 'empty_blob()'; //Value of empty default blobs for this DB
                break;
            case 'mssql':
            case 'postgres':
                $clobdefault = 'null'; //Value of empty default clobs for this DB (under mssql this won't be executed
                $blobdefault = 'null'; //Value of empty default blobs for this DB
                break;
        }
        $insertSQL = str_replace("'@#CLOB#@'", $clobdefault, $insertSQL);
        $insertSQL = str_replace("'@#BLOB#@'", $blobdefault, $insertSQL);
    }

/// Run the SQL statement
    if (!$rs = $db->Execute($insertSQL)) {
        debugging($db->ErrorMsg() .'<br /><br />'.s($insertSQL));
        if (!empty($CFG->dblogerror)) {
            $debug=array_shift(debug_backtrace());
            error_log("SQL ".$db->ErrorMsg()." in {$debug['file']} on line {$debug['line']}. STATEMENT:  $insertSQL");
        }
        return false;
    }

/// Under Oracle and PostgreSQL, finally, update all the Clobs and Blobs present in the record
/// if we know we have some of them in the query
    if (($CFG->dbfamily == 'oracle' || $CFG->dbfamily == 'postgres') &&
      !empty($dataobject->{$primarykey}) &&
      (!empty($foundclobs) || !empty($foundblobs))) {
        if (!db_update_lobs($table, $dataobject->{$primarykey}, $foundclobs, $foundblobs)) {
            return false; //Some error happened while updating LOBs
        }
    }

/// If a return ID is not needed then just return true now (but not in MSSQL DBs, where we may have some pending tasks)
    if (!$returnid && $CFG->dbfamily != 'mssql') {
        return true;
    }

/// We already know the record PK if it's been passed explicitly,
/// or if we've retrieved it from a sequence (Postgres and Oracle).
    if (!empty($dataobject->{$primarykey})) {
        return $dataobject->{$primarykey};
    }

/// This only gets triggered with MySQL and MSQL databases
/// however we have some postgres fallback in case we failed
/// to find the sequence.
    $id = $db->Insert_ID();

/// Under MSSQL all the Clobs and Blobs (IMAGE) present in the record
/// if we know we have some of them in the query
    if (($CFG->dbfamily == 'mssql') &&
      !empty($id) &&
      (!empty($foundclobs) || !empty($foundblobs))) {
        if (!db_update_lobs($table, $id, $foundclobs, $foundblobs)) {
            return false; //Some error happened while updating LOBs
        }
    }

    if ($CFG->dbfamily === 'postgres') {
        // try to get the primary key based on id
        if ( ($rs = $db->Execute('SELECT '. $primarykey .' FROM '. $CFG->prefix . $table .' WHERE oid = '. $id))
             && ($rs->RecordCount() == 1) ) {
            trigger_error("Retrieved $primarykey from oid on table $table because we could not find the sequence.");
            return (integer)reset($rs->fields);
        }
        trigger_error('Failed to retrieve primary key after insert: SELECT '. $primarykey .
                      ' FROM '. $CFG->prefix . $table .' WHERE oid = '. $id);
        return false;
    }

    return (integer)$id;
}    
?>