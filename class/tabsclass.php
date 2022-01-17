<?php  // $Id: tabsclass.php,v 1.5 2009/08/14 07:21:57 Shtifanov Exp $
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set


    if (empty($currenttab)) {
        error('You cannot call this script in that way');
    }

    $toprow = array();
    $toprow[] = new tabobject('listsubgroup', $CFG->wwwroot."/blocks/mou_school/class/subgroups.php?tn=listsubgroup&amp;rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;gid=$gid",
                get_string('listsubgroup', 'block_mou_school'));

    $toprow[] = new tabobject('pupilssubgroup', $CFG->wwwroot."/blocks/mou_school/class/subgroups.php?tn=pupilssubgroup&amp;rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;gid=$gid",
                get_string('pupilssubgroup', 'block_mou_school'));

    $toprow[] = new tabobject('setinsubgroups', $CFG->wwwroot."/blocks/mou_school/class/setinsubgroups.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;gid=$gid",
                get_string('setinsubgroups', 'block_mou_school'));

    $tabs = array($toprow);

    print_tabs($tabs, $currenttab, NULL, NULL);

?>
