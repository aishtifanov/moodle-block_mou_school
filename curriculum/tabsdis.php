<?php  // $Id: tabsdis.php,v 1.3 2010/08/23 08:48:06 Shtifanov Exp $
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set


    if (empty($currenttab)) {
        error('You cannot call this script in that way');
    }

    $toprow = array();
    if (has_capability('block/mou_school:editdiscipline', $context))	{
	   	$toprow[] = new tabobject('educationareas', "educationareas.php?sid=$sid&amp;rid=$rid&amp;yid=$yid",
	                get_string('educationareas', 'block_mou_school'));
	}
	                
   	$toprow[] = new tabobject('discipline', $CFG->wwwroot."/blocks/mou_school/curriculum/discipline.php?sid=$sid&amp;rid=$rid&amp;yid=$yid",
       	        get_string('discipline', 'block_mou_school'));
    if (has_capability('block/mou_school:editdiscipline', $context))	{       	        
	   	$toprow[] = new tabobject('disciplinegroups', $CFG->wwwroot."/blocks/mou_school/curriculum/disciplinegroups.php?rid=$rid&amp;sid=$sid&amp;yid=$yid",
	       	        get_string('disciplinegroups', 'block_mou_school'));
	}       	        
   	$toprow[] = new tabobject('discipteachers', $CFG->wwwroot."/blocks/mou_school/curriculum/discipteachers.php?rid=$rid&amp;sid=$sid&amp;yid=$yid",
       	        get_string('discipteachers', 'block_mou_school'));
    $toprow[] = new tabobject('textbook', $CFG->wwwroot."/blocks/mou_school/curriculum/textbooks_d.php?sid=$sid&amp;rid=$rid&amp;yid=$yid",
   	            get_string('textbooks_d', 'block_mou_school'));
       	        


    $tabs = array($toprow);
    print_tabs($tabs, $currenttab, NULL, NULL);

?>
