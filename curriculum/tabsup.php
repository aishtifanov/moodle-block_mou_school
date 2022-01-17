<?php  // $Id: tabsup.php,v 1.5 2009/08/14 07:21:57 Shtifanov Exp $
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set


    if (empty($currenttab)) {
        error('You cannot call this script in that way');
    }

    $toprow = array();
  	$toprow[] = new tabobject('profiles', "profiles.php?rid=$rid&amp;sid=$sid&amp;yid=$yid",
       	        get_string('profiles', 'block_mou_school'));
   	$toprow[] = new tabobject('components', "components.php?sid=$sid&amp;rid=$rid&amp;yid=$yid",
       	        get_string('components', 'block_mou_school'));
   	$toprow[] = new tabobject('nagruzka', $CFG->wwwroot."/blocks/mou_school/curriculum/nagruzka.php?rid=$rid&amp;sid=$sid&amp;yid=$yid",
       	        get_string('nagruzka', 'block_mou_school'));
   	$toprow[] = new tabobject('setdiscipline', "setdiscipline.php?sid=$sid&amp;rid=$rid&amp;yid=$yid",
                get_string('setdiscipline', 'block_mou_school'));
   	$toprow[] = new tabobject('curriculum', $CFG->wwwroot."/blocks/mou_school/curriculum/curriculum.php?rid=$rid&amp;sid=$sid&amp;yid=$yid",
       	        get_string('curriculum', 'block_mou_school'));


    $tabs = array($toprow);
    print_tabs($tabs, $currenttab, NULL, NULL);

?>
