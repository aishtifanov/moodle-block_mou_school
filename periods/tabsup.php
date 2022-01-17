<?php  // $Id: tabsup.php,v 1.9 2012/08/22 09:10:06 shtifanov Exp $
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set


    if (empty($currenttab)) {
        error('You cannot call this script in that way');
    }

    $toprow2 = array();
   	if (isadmin())	{  // || $region_operator_is)	 {
   	    
	 	$toprow2[] = new tabobject('studyyear', "newstudyyear.php?sid=$sid&amp;rid=$rid&amp;yid=$yid", 
				     get_string('studyyears', 'block_mou_school'));
	}
    
   	$toprow2[] = new tabobject('typestudyperiod', $CFG->wwwroot."/blocks/mou_school/periods/typestudyperiod.php?mode=1&amp;sid=$sid&amp;rid=$rid&amp;yid=$yid",
       	        get_string('typestudyperiod', 'block_mou_school'));
   	$toprow2[] = new tabobject('studyperiod', $CFG->wwwroot."/blocks/mou_school/periods/studyperiod.php?mode=1&amp;sid=$sid&amp;rid=$rid&amp;yid=$yid",
       	        get_string('borderstudyperiods', 'block_mou_school'));
   	$toprow2[] = new tabobject('holidays', $CFG->wwwroot."/blocks/mou_school/periods/holidays.php?mode=1?mode=1&amp;sid=$sid&amp;rid=$rid&amp;yid=$yid",
       	        get_string('holidays', 'block_mou_school'));

    $tabs2 = array($toprow2);
    print_tabs($tabs2, $currenttab, NULL, NULL);

?>
