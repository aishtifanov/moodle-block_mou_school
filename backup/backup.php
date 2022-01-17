<?php
	require_once("../../../config.php");
    require_once("$CFG->libdir/formslib.php");
    require_once('../../monitoring/lib.php');// для ../authbase.inc.php
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_school.php');// для ../authbase.inc.php 
	require_once('../authbase.inc.php');//
    require_once('lib_backup.php');
    
    //возможность создания backup школы
    $create_capability_xml = has_capability('block/mou_school:createbackup', $context);
    //возможность востановления данных из backup школы
    $restoring_capability_xml = has_capability('block/mou_school:restoringbackup', $context);
    //возможность загрузки файла backup школы на ПК
    $download_capability_xml = has_capability('block/mou_school:downloadbackup', $context);
    //возможность просмотра списка существующих файлов backup школы
    $view_capability_listbackupxml = has_capability('block/mou_school:viewbackup', $context);
    
    //Выбор действия
    $action = optional_param('action', '0');
    //Удаление backup файла
    //Вывести востановление
    $restor = optional_param('restor', false);
    //Удаление backup файла
    $deletexml = optional_param('deletexml', '0');
    //Выбор backup школы
    $backupid = optional_param('backupid', '0');
    
    //создание backup школы
    if ($action == 'createxml' AND $create_capability_xml){
        ignore_user_abort(false); // see bug report 5352. This should kill this thread as soon as user aborts.
        @set_time_limit(0);
        @ob_implicit_flush(true);
        @ob_end_flush();
        
        //Вызов функции создания backup школы
        create_xml_mouschool($rid, $sid);
    }//конец if
    
    //Выполнение востановления backup школы
    if ($action == 'restoringxml' AND $restoring_capability_xml){
        ignore_user_abort(false); // see bug report 5352. This should kill this thread as soon as user aborts.
        @set_time_limit(0);
        @ob_implicit_flush(true);
        @ob_end_flush();
        
        //Вызов функции востановления backup школы
        open_xml_mouschool($rid, $sid, $backupid);
    }//конец if 
    
    //Выполнение удаления backup файла
    if ($deletexml == 'deletexml' AND $restoring_capability_xml){
        //Вызов функции удаления backup файла
        delbackup_xml_mouschool($backupid);
    }//конец if 
    
    //Вывод таблицы списка backup школы
    if ($view_capability_listbackupxml AND !$restor){
        //Вызов функции просмотра списка существующих файлов backup школы
        $table = table_backup_mouschool($rid, $sid, $download_capability_xml , $restoring_capability_xml);
        if (isset($table->data))	{
	 		 //Выводим таблицу
             print_color_table($table);
		}//конец if
        
        //Вывод кнопки создания backup школы
        if ($create_capability_xml){
            $options = array('rid' => $rid, 'sid' => $sid,  'action' => 'createxml');
            echo '<table align="center" border=0><tr><td>';
            print_single_button("backup.php", $options, 'Создать backup');
            echo '</td><td>';
        }//конец if
        
    }//конец if
    
    //Вывод списка таблиц для востановления backup школы
    if ($restor AND $restoring_capability_xml){
        $table = listcheckbox_table($rid, $sid);
        if (isset($table->data))	{
            echo '<form name="backup" method="post" action="backup.php">';
			echo  '<input type="hidden" name="rid" value="' . $rid . '">';
			echo  '<input type="hidden" name="sid" value="' . $sid . '">';
			echo  '<input type="hidden" name="backupid" value="' . $backupid . '">';            
			echo  '<input type="hidden" name="restor" value="true">';
			echo  '<input type="hidden" name="action" value=restoringxml>';
            
	 		//Выводим таблицу
            print_color_table($table);
            //Вывод кнопки сохранение групп учавствующих в рейтинге

            echo '<table align="center" border=0><tr><td>';
            echo '<a href="javascript:select_all_in(\'TABLE\',null,\'backup\');">'.get_string('selectall', 'quiz').'</a> / ';
            echo '<a href="javascript:deselect_all_in(\'TABLE\',null,\'backup\');">'.get_string('selectnone', 'quiz').'</a><br>';
            echo  '<input type="submit" name="res" value="Востановить">';
            echo '</td><td>';
            echo '</form>';
        }//конец if
    }//конец if
    
    
?>