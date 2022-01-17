<?php  // $Id: tabsclasses.php,v 1.4 2010/08/23 08:48:02 Shtifanov Exp $
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set


    if (empty($currenttab)) {
        error('You cannot call this script in that way');
    }
    $toprow = array();
    $toprow[] = new tabobject('classlist', $CFG->wwwroot."/blocks/mou_school/class/classlist.php?rid=$rid&amp;yid=$yid&amp;sid=$sid",
                get_string('classlist', 'block_mou_school'));

    $toprow[] = new tabobject('classpupils', $CFG->wwwroot."/blocks/mou_school/class/classpupils.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;gid=$gid",
                get_string('classpupils', 'block_mou_school'));

    $toprow[] = new tabobject('classdisciplines', $CFG->wwwroot."/blocks/mou_school/class/classdisciplines.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;gid=$gid",
                get_string('classdisciplines', 'block_mou_school'));

    $toprow[] = new tabobject('subgroups', $CFG->wwwroot."/blocks/mou_school/class/subgroups.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;gid=$gid",
                get_string('subgroups', 'block_mou_school'));

	if (has_capability('block/mou_school:editclasslist', $context))	{
	    $toprow[] = new tabobject('importclasses', $CFG->wwwroot."/blocks/mou_school/class/importclasses.php?sid=$sid&amp;rid=$rid&amp;yid=$yid",
	    	            get_string('importclasses', 'block_mou_school'));
	}    	            

    $tabs = array($toprow);
    print_tabs($tabs, $currenttab, NULL, NULL);

?>
