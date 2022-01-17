<?php  // $Id: tabsjrnl.php,v 1.3 2010/08/27 12:33:55 Oleg Exp $
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set


    if (empty($currenttab)) {
    	 error('You cannot call this script in that way');
    }
	    $toprow = array();
	
	    $toprow[] = new tabobject('journalclass', $CFG->wwwroot."/blocks/mou_school/journal/journalclass.php?rid=$rid&amp;yid=$yid&amp;sid=$sid",
	                get_string('journalclass', 'block_mou_school'));
	
	   	$toprow[] = new tabobject('attendance', $CFG->wwwroot."/blocks/mou_school/journal/attendance.php?rid=$rid&amp;yid=$yid&amp;sid=$sid",
	        	        get_string('attendance', 'block_mou_school'));
	
	    $toprow[] = new tabobject('totalmarks', $CFG->wwwroot."/blocks/mou_school/journal/totalmarks.php?rid=$rid&amp;yid=$yid&amp;sid=$sid",
	    	            get_string('totalmarks', 'block_mou_school'));
	
	   $tabs = array($toprow);
	
	
	/// Print out the tabs and continue!
	    print_tabs($tabs, $currenttab, NULL, NULL);  

?>
