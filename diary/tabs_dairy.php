<?php  // $Id: tabs_dairy.php,v 1.5 2012/01/13 11:40:08 shtifanov Exp $
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set


		    if (empty($currenttab)) {
		        error('You cannot call this script in that way');
		    }

		    $toprow = array();
		    $toprow[] = new tabobject('marks', "diary.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid=$uid",
		    	            get_string('weekmarks', 'block_mou_school'));

		    $toprow[] = new tabobject('discmarks', "discmarks.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid=$uid",
		    	            get_string('discmarks', 'block_mou_school'));
		
		    $toprow[] = new tabobject('totalmarks', "tmarksdiary.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid=$uid",
		    	            get_string('totalmarks', 'block_mou_school'));
                     
		    $toprow[] = new tabobject('prevtotalmarks', "prevtmarksdiary.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid=$uid",
		    	            get_string('prevtotalmarks', 'block_mou_school'));
                
		    $tabs = array($toprow);
		    print_tabs($tabs, $currenttab, NULL, NULL);

?>
