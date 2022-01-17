<?php  

    if (empty($currenttab)) {
        error('You cannot call this script in that way');
    }
    
    $toprow   = array();
    $toprow[] = new tabobject('viewschedule',   $CFG->wwwroot."/blocks/mou_school/schedule/viewschedule.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;act=view",
                get_string('viewschedule', 'block_mou_school'));
    /*
    $toprow[] = new tabobject('createschedule', $CFG->wwwroot."/blocks/mou_school/schedule/viewschedule.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;act=create",
   	            get_string('createschedule', 'block_mou_school'));
    */
    $toprow[] = new tabobject('createschedule', $CFG->wwwroot."/blocks/mou_school/schedule/createschedule.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;act=create",
   	            get_string('createschedule', 'block_mou_school'));
    //createschedule.php?sid=2779&rid=1&yid=5

    $tabs = array($toprow);

    print_tabs($tabs, $currenttab, NULL, NULL);

?>