<?php
    if (empty($currenttab)) {
        error('You cannot call this script in that way');
    }
    /*
    $periods = array ('scheduleschool', 'scheduleclass', 'scheduleday', 'timelessons', 'rooms');
    $toprow  = array();
    foreach ($periods as $p)	{
	$toprow[] = new tabobject($p, $CFG->wwwroot."/blocks/mou_school/schedule/viewschedule.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;act=create&amp;create=$p",
                					  get_string($p, 'block_mou_school'));
    }
    $tabs = array($toprow);
    
    print_tabs($tabs, $tabschedulecreate, NULL, NULL);
    */
    
    //то что было:
    $toprow   = array();
    /*
    $toprow[] = new tabobject('scheduleschool', $CFG->wwwroot."/blocks/mou_school/schedule/createschedschool.php?rid=$rid&amp;yid=$yid&amp;sid=$sid",
                get_string('scheduleschool', 'block_mou_school'));
    */            
    $toprow[] = new tabobject('schedule', $CFG->wwwroot."/blocks/mou_school/schedule/createschedule.php?rid=$rid&amp;yid=$yid&amp;sid=$sid",
                get_string('scheduleclass', 'block_mou_school'));

    $toprow[] = new tabobject('scheduleday', $CFG->wwwroot."/blocks/mou_school/schedule/createscheduleday.php?rid=$rid&amp;yid=$yid&amp;sid=$sid",
                get_string('scheduleday', 'block_mou_school'));

    $toprow[] = new tabobject('timelessons', $CFG->wwwroot."/blocks/mou_school/schedule/timelessons.php?sid=$sid&amp;rid=$rid&amp;yid=$yid",
   	            get_string('timelessons', 'block_mou_school'));

    $toprow[] = new tabobject('rooms', $CFG->wwwroot."/blocks/mou_school/schedule/rooms.php?sid=$sid&amp;rid=$rid&amp;yid=$yid",
   	            get_string('rooms', 'block_mou_school'));

    $tabs = array($toprow);

    print_tabs($tabs, $currenttab, NULL, NULL);
    
?>