<?php  // $Id: tabsjrnl2.php,v 1.1 2010/08/31 12:32:58 Oleg Exp $
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set


      if (empty($currenttab)) {
    	 error('You cannot call this script in that way');
    }
	    
	    $toprow = array();
	
	    $toprow[] = new tabobject('marks', $CFG->wwwroot."/blocks/mou_school/journal/journalclass.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;cdid=$cdid&amp;gid=$gid",
	                get_string('marks', 'block_mou_school'));
	
	   	$toprow[] = new tabobject('themes', $CFG->wwwroot."/blocks/mou_school/journal/themes.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;cdid=$cdid&amp;gid=$gid",
	        	        get_string('lessonplans', 'block_mou_school'));
	
	   $tabs = array($toprow);
	
	
	/// Print out the tabs and continue!
	    print_tabs($tabs, $currenttab, NULL, NULL);   	 




    




?>
