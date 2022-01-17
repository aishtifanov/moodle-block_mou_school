<?php  // $Id: tabsreports.php,v 1.6 2014/06/03 07:51:03 shtifanov Exp $
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set


    if (empty($currenttab)) {
        error('You cannot call this script in that way');
    }

    $toprow   = array();
    				      
    $link = "yid=$yid&rid=$rid&sid=$sid";
                              
	$toprow[] = new tabobject('administrative', $CFG->wwwroot."/blocks/mou_school/reports/administrative.php?$link",
                get_string('administrative', 'block_mou_school'));

    $toprow[] = new tabobject('performance', $CFG->wwwroot."/blocks/mou_school/reports/performance.php?$link",
   	            get_string('performance', 'block_mou_school'));
   	            
   	$toprow[] = new tabobject('attendance', $CFG->wwwroot."/blocks/mou_school/reports/attendance.php?$link",
   	            get_string('attendance', 'block_mou_school'));

   	$toprow[] = new tabobject('statistics', $CFG->wwwroot."/blocks/mou_school/reports/statistics.php?$link",
   	            get_string('statistics', 'block_mou_school'));

	$context_rayon = get_context_instance(CONTEXT_RAYON, $rid);
	$edit_capability_rayon = has_capability('block/mou_school:editclasslist', $context_rayon);
    if ($edit_capability_rayon) {
       	$toprow[] = new tabobject('rperformance', $CFG->wwwroot."/blocks/mou_school/reports/rperformance.php?$link",
       	            get_string('rperformance', 'block_mou_school'));
    }

    $tabs = array($toprow);

    print_tabs($tabs, $currenttab, NULL, NULL);
?>
