<?php // $Id: index.php,v 1.40 2011/09/21 06:39:10 shtifanov Exp $

    require_once("../../config.php");
    require_once('../monitoring/lib.php');

    require_login();
    
    $rid = optional_param('rid', 0, PARAM_INT);       // Rayon id
    $sid = optional_param('sid', 0, PARAM_INT);       // School id
    $yid = optional_param('yid', 0, PARAM_INT);       // School id


    $yid = get_current_edu_year_id();
    
    $strmonit = get_string('mouschooltitle','block_mou_school');
    print_header_mou("$SITE->shortname: $strmonit", $SITE->fullname, $strmonit);
    
    print_heading($strmonit);

    $table->align = array ('right', 'left');
    // $table->class = 'moutable';
    
	$index_items = array();
	
	$pupil_is = ispupil();
	if  ($pupil_is) 	{
		$index_items = array(10);
	}	
	
	$admin_is = isadmin();
	$region_operator_is = ismonitoperator('region');
	if  ($admin_is || $region_operator_is) 	{
		$index_items = array(0,1,2,3,4,5,6,7,8,9,10,11);
	}	

	// $context = get_context_instance(CONTEXT_SYSTEM);
	// $context = get_context_instance(CONTEXT_RAYON, 1);

	$strsql = "SELECT a.id, roleid, contextid, contextlevel, userid  
				FROM mdl_role_assignments a	JOIN mdl_context ctx ON a.contextid=ctx.id
			   WHERE userid={$USER->id}";
	if ($ctxs = get_records_sql($strsql))	{
		
		foreach($ctxs as $ctx1)	{
			// print_r($ctx1); echo '<hr>';
			switch ($ctx1->contextlevel)	{
				case CONTEXT_REGION:
    			case CONTEXT_RAYON:  if ($ctx1->roleid == 8)	{
									 	$idx_rayon = array(0,1,2,3,4,5,6,7,8,9,10,11);
    								 	$index_items = array_merge ($idx_rayon, $index_items);
    								 }	
       						         break;

    			case CONTEXT_SCHOOL: if ($ctx1->roleid < 13)	{
									 	$idx_school = array(0,1,2,3,4,5,6,7,8);
									 } else {
									 	$idx_school = array(0,1, 8);
									 }	
 								 	 $index_items = array_merge ($idx_school, $index_items);
       						         break;
       						         
    			case CONTEXT_CLASS:  $idx_class = array(0,1,3,6,8);
    								 $index_items = array_merge ($idx_class, $index_items);
       						         break;

     			case CONTEXT_DISCIPLINE:  $idx_class = array(0,1,3,4,5,6,8);
    								 $index_items = array_merge ($idx_class, $index_items);
       						         break;
			}
		}
		
		$index_items = array_unique($index_items);
		sort($index_items);
	}	
	
	// print_r($index_items); echo '<hr>';
	
	$tabledata = array();	 

	$tabledata[] = array('<strong><a href="'.$CFG->wwwroot."/blocks/mou_school/periods/typestudyperiod.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">".get_string('studyperiods','block_mou_school').'</a></strong>',
                      get_string('description_stdyearperiod','block_mou_school'));

	$tabledata[] = array('<strong><a href="'.$CFG->wwwroot."/blocks/mou_school/curriculum/discipline.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">".get_string('discipline','block_mou_school').'</a></strong>',
                      get_string('description_discipline','block_mou_school'));

	$tabledata[] = array('<strong><a href="'.$CFG->wwwroot."/blocks/mou_school/curriculum/profiles.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">".get_string('curriculums','block_mou_school').'</a></strong>',
                      get_string('description_curriculums','block_mou_school'));

	$tabledata[] = array('<strong><a href="'.$CFG->wwwroot."/blocks/mou_school/class/classlist.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">".get_string('classespredmet','block_mou_school').'</a></strong>',
                      get_string('description_classespredmet','block_mou_school'));

	$tabledata[] = array('<strong><a href="'.$CFG->wwwroot."/blocks/mou_school/plans/planplans.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">".get_string('lessonsplan','block_mou_school').'</a></strong>',
                      get_string('description_lessonsplan','block_mou_school'));

	$tabledata[] = array('<strong><a href="'.$CFG->wwwroot."/blocks/mou_school/schedule/viewschedule.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">".get_string('schedule','block_mou_school').'</a></strong>',
                      get_string('description_schedule','block_mou_school'));

	$tabledata[] = array('<strong><a href="'.$CFG->wwwroot."/blocks/mou_school/journal/journalclass.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">".get_string('journalclass','block_mou_school').'</a></strong>',
                      get_string('description_journalclass','block_mou_school'));

	$tabledata[] = array('<strong><a href="'.$CFG->wwwroot."/blocks/mou_school/reports/administrative.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">".get_string('reports', 'block_mou_school').'</a></strong>',
                      get_string('description_reports','block_mou_school'));
                      
	$tabledata[] = array('<strong><a href="'.$CFG->wwwroot."/file.php/1/step_by_step.pdf\">".get_string('instruction', 'block_mou_school').'</a></strong>',
                      get_string('description_instruction', 'block_mou_school') . ' (обновлено 7 октября 2010 года, добавлено описание "Расписание на день" и "Итоговые оценки").');

	$tabledata[] = array('<strong><a href="'.$CFG->wwwroot."/blocks/mou_school/class/movingpupils.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">".get_string('movingpupils','block_mou_school').'</a></strong>',
                          get_string('description_movingpupils','block_mou_school')); 	                          

	$tabledata[] = array('<strong><a href="'.$CFG->wwwroot."/blocks/mou_school/diary/diary.php?yid=$yid\">".get_string('diary','block_mou_school').'</a></strong>',
                      get_string('description_diary','block_mou_school'));

   	$tabledata[] = array('<strong><a href="'.$CFG->wwwroot."/blocks/mou_school/textbook/textbook.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">".get_string('textbooks', 'block_mou_ege').'</a></strong>',
 	                          get_string('description_textbook','block_mou_ege'));

	if (!empty($index_items))	{			
		foreach ($index_items as $index_item)	{
			$table->data[] = $tabledata[$index_item];
		}
  		print_table($table);
	}


    print_footer($SITE);

?>