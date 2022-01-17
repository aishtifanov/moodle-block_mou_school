<?php
    if (empty($tabperiod)) {
        error('You cannot call this script in that way');
    }

    /*
    $periods = array ('day', 'week', 'room', 'roomlist', 'teacher', 'teacherlist');
    $toprow  = array();
    foreach ($periods as $p)	{
	$toprow[] = new tabobject($p, $CFG->wwwroot."/blocks/mou_school/schedule/viewschedule.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;p=$p",
                					  get_string($p, 'block_mou_school'));
    }
    $tabs = array($toprow);
    
    print_tabs($tabs, $tabperiod, NULL, NULL);
    */
    //Моё:
    $toprow   = array();
    $toprow[] = new tabobject('day', $CFG->wwwroot."/blocks/mou_school/schedule/viewschedule.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;p=day",
                'На день');
                
    $toprow[] = new tabobject('week', $CFG->wwwroot."/blocks/mou_school/schedule/viewschedule.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;sh=$sh&amp;nw=$nw&amp;idteacher=$idteacher&amp;idroom=$idroom&amp;p=week",
                'На неделю');
/*
    $toprow[] = new tabobject('room', $CFG->wwwroot."/blocks/mou_school/schedule/viewschedule.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;sh=$sh&amp;nw=$nw&amp;idteacher=$idteacher&amp;idroom=$idroom&amp;p=room",
                'Перекрёстное расписание по кабинетам');

    $toprow[] = new tabobject('roomlist', $CFG->wwwroot."/blocks/mou_school/schedule/viewschedule.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;sh=$sh&amp;nw=$nw&amp;idteacher=$idteacher&amp;idroom=$idroom&amp;p=roomlist",
   	            'Сводное расписание по кабинетам');

    $toprow[] = new tabobject('teacher', $CFG->wwwroot."/blocks/mou_school/schedule/viewschedule.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;sh=$sh&amp;nw=$nw&amp;idteacher=$idteacher&amp;idroom=$idroom&amp;p=teacher",
   	            'Перекрёстное расписание по учителям');
                
    $toprow[] = new tabobject('teacherlist', $CFG->wwwroot."/blocks/mou_school/schedule/viewschedule.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;sh=$sh&amp;nw=$nw&amp;idteacher=$idteacher&amp;idroom=$idroom&amp;p=teacherlist",
   	            'Сводное расписание по учителям');
*/
    $tabs = array($toprow);

    print_tabs($tabs, $tabperiod, NULL, NULL);
    
?>