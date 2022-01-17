<?php  // $Id: tabspupil.php,v 1.7 2010/09/02 06:56:51 Shtifanov Exp $
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set


    if (empty($currenttab) or empty($user)) {
        error('You cannot call this script in that way');
    }

    $toprow = array();
	if (has_capability('block/mou_school:viewclasslist', $context))	{
	    $toprow[] = new tabobject('profile', $CFG->wwwroot."/blocks/mou_school/class/pupil.php?mode=4&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid={$user->id}",
	                get_string('profilepupil', 'block_mou_school'));
	}                

	if ($edit_capability || $edit_capability_class)	{
	    $toprow[] = new tabobject('pupilcard', $CFG->wwwroot."/blocks/mou_school/class/pupilcard.php?mode=4&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid={$user->id}",
					get_string('pupilcard', 'block_mou_school'));
	}				

	if (has_capability('block/mou_school:viewclasslist', $context))	{
	    $toprow[] = new tabobject('officialdocs', $CFG->wwwroot."/blocks/mou_school/class/docoffice.php?mode=4&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid={$user->id}",
					get_string('docoffice', 'block_mou_school'));
					
		$toprow[] = new tabobject('review', $CFG->wwwroot."/blocks/mou_school/class/review.php?mode=4&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid={$user->id}",
					get_string('reviewpupil', 'block_mou_school'));
	}				
					
    $tabs = array($toprow);
    print_tabs($tabs, $currenttab, NULL, NULL);

?>
