<?php  // $Id: tabsplan.php,v 1.10 2010/08/24 12:38:54 Shtifanov Exp $
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set

/*$string['importplan'] = 'Импорт предметных планов';
			$string['plan']='план';   //1
			$string['unit']='раздел'; //2
			$string['llesson']='урок'; //3
*/
    if (empty($currenttab)) {
        error('You cannot call this script in that way');
    }

    $toprow   = array();
    $toprow[] = new tabobject('planplans', $CFG->wwwroot."/blocks/mou_school/plans/planplans.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;did=$did&amp;pid=$pid&amp;planid=$planid&amp;unitid=$unitid",
                get_string('planplans', 'block_mou_school'));

    $toprow[] = new tabobject('unitplans', $CFG->wwwroot."/blocks/mou_school/plans/unitplans.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;did=$did&amp;pid=$pid&amp;planid=$planid&amp;unitid=$unitid",
                get_string('unitplans', 'block_mou_school'));

    $toprow[] = new tabobject('lessonplans', $CFG->wwwroot."/blocks/mou_school/plans/lessonplans.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;did=$did&amp;pid=$pid&amp;planid=$planid&amp;unitid=$unitid",
                get_string('lessonplans', 'block_mou_school'));

	// if (has_capability('block/mou_school:editlessonsplan', $context))	{
		$toprow[] = new tabobject('importplan', $CFG->wwwroot."/blocks/mou_school/plans/importplan.php?sid=$sid&amp;rid=$rid&amp;yid=$yid&amp;did=$did&amp;pid=$pid",
    	            get_string('importplan', 'block_mou_school'));
	// }
    $tabs = array($toprow);

    print_tabs($tabs, $currenttab, NULL, NULL);

?>
