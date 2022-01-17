<?php // $Id: themes.php,v 1.20 2012/10/24 11:38:19 shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once($CFG->dirroot.'/calendar/lib.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_school.php');    
	require_once('../plans/lib_plans.php');

    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = optional_param('sid', '0', PARAM_INT);     // School id
    $yid = optional_param('yid', '0', PARAM_INT);     // Year id
	    
	$cdid 	= optional_param('cdid', 0, PARAM_INT);	  // class_discipline (subgroup) id
    $termid	= optional_param('tid',  0, PARAM_INT);   // Semestr id
    $gid 	= optional_param('gid',  0, PARAM_INT);   // Class id
    $p 		= optional_param('p', 	 0, PARAM_INT);   // Parallel number
    $period = optional_param('p', 	 'day'); // Period time: day, week, month, year
    $jid 	= optional_param('jid',  0, PARAM_INT);   // Schedule id (jornal id)
    $planid = optional_param('planid',  0, PARAM_INT);
    $thid 	= optional_param('thid',  0, PARAM_INT);
    $nw = optional_param('nw', 0, PARAM_INT);   // Number of week in study year 

	$GLDATESTART = array();
	$curryearfull = current_edu_year();
	$curyear = explode('/', $curryearfull);
	$datestartGLOB = $curyear[0].'-09-01';
	$dateendGLOB = $curyear[1].'-05-31';	
    $allweeksinyear = make_all_weeks_in_year($datestartGLOB, $dateendGLOB);

    $breadcrumbs[0]->link = "journalclass.php?rid=$rid&amp;yid=$yid&amp;cdid=$cdid&amp;gid=$gid&amp;tid=$termid&amp;sid=$sid";
    $breadcrumbs[0]->name = get_string('journalclass','block_mou_school');
	require_once('../authbase.inc.php');  


	$view_capability = has_capability('block/mou_school:viewjournalclass', $context);
	$edit_capability = has_capability('block/mou_school:editjournalclass', $context);

	$edit_capability_class = false;
	if ($gid != 0)  {
		$context_class = get_context_instance(CONTEXT_CLASS, $gid);
		$edit_capability_class = has_capability('block/mou_school:editjournalclass', $context_class);
	}		

	$edit_capability_discipline = false;
	if ($cdid != 0)  {
		$ctxdiscipline = get_context_instance(CONTEXT_DISCIPLINE, $cdid);
		$edit_capability_discipline = has_capability('block/mou_school:editjournalclass', $ctxdiscipline);
	}

    if ($recs = data_submitted())  {
        
        if (isset($recs->saveprev))  $nw--;
        
        if (isset($recs->savenext))  $nw++;
        
		if (!$edit_capability && !$edit_capability_class && !$edit_capability_discipline)	{
			error(get_string('permission', 'block_mou_school'), '../index.php');
		}	

		$sh = array();
		foreach($recs as $fieldname => $value)	{
	    $mask = substr($fieldname, 0, 2);
		    switch ($mask)  {
					case 's_': 	$ids = explode('_', $fieldname);
								$sh[$ids[1]] = $value;					
	  				break;	
	  			}
	  	}
		  		
	  	foreach($sh as $id => $lessonid)    {
			if(!set_field('monit_school_class_schedule_'.$rid, 'lessonid', $lessonid, 'id', $id))    {
				error(get_string('errorinaddingtheme','block_mou_school'));
			}			
	  	}
	  		notify(get_string('succesavedata','block_mou_school'), 'green');
	}	

    $currenttab = 'journalclass';
    include('tabsjrnl.php');

	if ($view_capability)	{

		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
    	$strlistclasses =  listbox_class_role("themes.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=", $rid, $sid, $yid, $gid);
    	echo $strlistclasses;
    	$strlistpredmets =listbox_discipline_class_role("themes.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;cdid=", $sid, $yid, $gid, $cdid);
    	if ($cdid != 0)  {
    		$ctxdiscipline = get_context_instance(CONTEXT_DISCIPLINE, $cdid);
    		$edit_capability_discipline = has_capability('block/mou_school:editjournalclass', $ctxdiscipline);
    	}
    
    	
		if ($strlistpredmets)	{
    		echo $strlistpredmets;
    	} else if ($gid) {
    		echo '</table>';
    		notice (get_string('classdisciplinesnotfound', 'block_mou_school'), "../class/classdisciplines.php?rid=$rid&amp;yid=$yid&amp;gid=$gid&amp;sid=$sid");
    	}	
		
		
		if ($gid != 0 && $cdid != 0)		{

    		listbox_plans2("themes.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;cdid=$cdid&amp;planid=", $sid, $yid, $gid, $cdid, $planid);
    		listbox_all_weeks_year("themes.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;cdid=$cdid&amp;planid=$planid&amp;nw=", $allweeksinyear, $nw);

			 
			echo '</table>';
			if ($planid != 0)    {
				$currenttab = 'themes';
			    include('tabsjrnl2.php');		

				$table = table_themes_of_lessons($rid, $sid, $yid, $gid, $planid, $thid, $cdid, $nw);
				
				if ($planid!=0 && ($edit_capability || $edit_capability_class || $edit_capability_discipline))	{		
					echo  '<form name="marks" method="post" action="themes.php">';
					echo  '<input type="hidden" name="rid" value="' . $rid . '">';
					echo  '<input type="hidden" name="sid" value="' . $sid . '">';
					echo  '<input type="hidden" name="yid" value="' . $yid . '">';
					echo  '<input type="hidden" name="gid" value="' . $gid . '">';
					echo  '<input type="hidden" name="cdid" value="' . $cdid . '">';
					echo  '<input type="hidden" name="planid" value="' . $planid . '">';
					echo  '<input type="hidden" name="thid" value="' . $thid . '">';
					echo  '<input type="hidden" name="nw" value="' . $nw . '">';					
					print_color_table($table);
					echo  '<div align="center">';
                    echo  '<input type="submit" name="saveprev" value="'. get_string('saveprev', 'block_mou_school') . '">';
					echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '">';
                    echo  '<input type="submit" name="savenext" value="'. get_string('savenext', 'block_mou_school') . '">';
					echo  '</div></form>';
				} else {
					print_color_table($table);			
				}
            }    

		} else {
			echo '</table>';
		}
	} else {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}
 	
    notify ('<br><i>Замечание: если в названии тематического плана присутствует имя класса (без пробелов и с прописной буквой), <br>то выполняется автоматическая привязка плана к классу.<i>', 'black');   
    print_footer();

    
function table_themes_of_lessons($rid, $sid, $yid, $gid, $planid, $thid, $cdid, $nw)
{
	global $CFG, $edit_capability, $edit_capability_class, $edit_capability_discipline, $GLDATESTART, $termid;
	
	// $classdiscipline = get_record('monit_school_class_discipline', 'id', $cdid);

	$table->head  = array (get_string('date', 'block_mou_school'),get_string('lessonplan', 'block_mou_school'),
							get_string('task', 'block_mou_school'),get_string('typeoftask', 'block_mou_school'),
							get_string('action', 'block_mou_school'));
	$table->align = array ('center','center','left','left','center',);
	$table->size = array ('10%','35%','30%','15%','10%',);
	$table->columnwidth = array (10,35,30,15,10,);
    $table->class = 'moutable';
   	$table->width = '60%';
    $table->titles = array();
    $table->titles[] = get_string('journalclass', 'block_mou_school');
    $table->worksheetname = 'journalclass';
	
	$datestart = $GLDATESTART[$nw];
	$firstdayweek = date("Y-m-d", $datestart);;
	$lastdayweek = date("Y-m-d", $datestart + DAYSECS*6);
	
    $strtitle = get_string('selectlessontheme', 'block_mou_school') . '...';
    $thememenu = array();

    $thememenu[0] = $strtitle;

	if($units = get_records_select("monit_school_discipline_unit", "planid=$planid", 'number', 'id, number')){
		foreach($units as $unit){
			if($themes = get_records_select("monit_school_discipline_lesson_$rid", "schoolid=$sid and unitid={$unit->id}", 'number', 'id, number, name')){
				foreach($themes as $theme){
					$thememenu[$theme->id] = $unit->number.'.'.$theme->number.'. '.$theme->name;
				}
			}
		}
	}
	$strsql = "classdisciplineid = $cdid AND datestart >= '$firstdayweek' AND datestart <= '$lastdayweek'";
	// echo $strsql;
	if ($schedules = get_records_select("monit_school_class_schedule_$rid", $strsql, 'datestart', 'id, lessonid, datestart'))		{
	//if ($schedules = get_records_select("monit_school_class_schedule_$rid","schoolid = $sid and classid = $gid and classdisciplineid = $cdid", 'datestart', 'id, lessonid, datestart'))		{	
		foreach($schedules as $schedule){
			$tabledata = array();
			$rusformatdate = convert_date($schedule->datestart, 'en', 'ru');
			$tabledata[] = $rusformatdate;
			$tabledata[] = choose_from_menu ($thememenu, "s_{$schedule->id}", $schedule->lessonid, '0', "", "", true);
			
            $params = "mod=1&rid=$rid&sid=$sid&yid=$yid&cdid=$cdid&gid=$gid&tid=$termid&jid={$schedule->id}&themeid={$schedule->lessonid}&nw=$nw&ret=1"; 
            $title = get_string('taskslessons', 'block_mou_school');
            $strlinkupdate = " <a title=\"$title\" href=\"setheme.php?$params\">";
            $strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/grades.gif\" alt=\"$title\" /></a>&nbsp;";           			
            
			if($task = get_record_select('monit_school_assignments_'.$rid, "scheduleid = $schedule->id", 'id, type_ass, name')){		
				$tabledata[] = $task->name;
				if ($typeoftask = get_record_select('monit_school_type_assignment', "id = $task->type_ass", 'id, name'))	{
					$tabledata[] = $typeoftask->name;
				} else {
					$tabledata[] = '';
			    }
    			$title = get_string('deletetheme', 'block_mou_school')  . ' и задание';
    		    $strlinkupdate .= "<a title=\"$title\" href=\"deltheme.php?id=$task->id&{$params}\">";
    			$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
			} else {
				$tabledata[] = '';	
				$tabledata[] = '';
			}
			
            			
			$tabledata[] = $strlinkupdate;
			 
			$table->data[] = $tabledata;	
		}
			
	}
			
    return $table;
}



?>