<?php // $Id: setheme.php,v 1.19 2012/06/04 11:46:33 shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once($CFG->dirroot.'/calendar/lib.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_school.php');    
    require_once('../plans/lib_plans.php');  

    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = required_param('sid', PARAM_INT);       // School id
    $yid = required_param('yid', PARAM_INT);       // Year id
	$cdid 	= optional_param('cdid', 0, PARAM_INT);	  // class_discipline (subgroup) id
    $termid	= optional_param('tid',  0, PARAM_INT);   // Semestr id
    $gid 	= optional_param('gid',  0, PARAM_INT);   // Class id
    $p 		= optional_param('p', 	 0, PARAM_INT);   // Parallel number
    $jid 	= required_param('jid',  PARAM_INT);   // Schedule id (jornal id)
    $tyid	= optional_param('tyid',  0, PARAM_INT);   // Semestr id
    $themeid= optional_param('themeid',  0, PARAM_INT);   // Theme id
    $planid = optional_param('planid',  0, PARAM_INT);
    $ret = optional_param('ret', 1, PARAM_INT);
    $nw = optional_param('nw', 0, PARAM_INT);   // Number of week in study year
        
	$context = get_context_instance(CONTEXT_SCHOOL, $sid);
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
	
	if (!$edit_capability && !$edit_capability_class && !$edit_capability_discipline)	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	
	
	$strjournal = get_string('journalclass','block_mou_school');
   	$strtitle = get_string('editlesson', 'block_mou_school');	

	$breadcrumbs = '<a href="'.$CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('title','block_mou_school').'</a>';
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/mou_school/journal/journalclass.php?rid=$rid&amp;yid=$yid&amp;cdid=$cdid&amp;gid=$gid&amp;tid=$termid&amp;sid=$sid\">$strjournal</a>";
	$breadcrumbs .= "-> $strtitle";
	print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

    if ($recs = data_submitted())  {
    	// echo '<pre>'; print_r($recs); echo '</pre>';
    	
        if ($ret == 0) {
            $redirlink = "journalclass.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;cdid=$cdid&amp;gid=$gid&amp;tid=$termid";            
        } else {
            $redirlink = "themes.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;cdid=$cdid&amp;planid=$planid&amp;nw=$nw";
        }
    	// $redirlink = "setheme.php?mod=1&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;cdid=$cdid&amp;gid=$gid&amp;tid=$termid&amp;jid=$jid&amp;themeid=$themeid&amp;planid=$planid";

		$tasks = array();
		foreach($recs as $fieldname => $value){
			$mask = substr($fieldname, 0, 2);
	        if ($mask == 't_')	{
	        	$ids = explode('_', $fieldname);
	        	$tasks[$ids[1]]->taskid = $ids[2];
	        	$tasks[$ids[1]]->taskname = $value;
   	        	$fldname = 'type_assignment'.$ids[1];
	        	$tasks[$ids[1]]->type_ass = $recs->{$fldname};
	        }
		}
		
        // echo '<pre>'; print_r($tasks); echo '</pre>'; 
		foreach ($tasks as $numtask => $task)	{

    		if (!get_magic_quotes_gpc()) {
    	        foreach ($task as $key => $data) {
    	            $task->$key = addslashes(clean_text(stripslashes(trim($task->$key)), FORMAT_MOODLE));
    	        }
    	    } else {
    	        foreach ($task as $key => $data) {
    	            $task->$key = clean_text(trim($task->$key), FORMAT_MOODLE);
    	        }
    	    }

            // $task = addslashes_object($task);
            	    	
			if ($task->taskid == 0 && empty($task->taskname)) continue;
	    	
    		if (record_exists_mou('monit_school_assignments_'.$rid, 'id', $task->taskid))	{
    			if (empty($task->taskname)) {
    				delete_records('monit_school_assignments_'.$rid, 'id', $task->taskid);
    			} else {
    			    $rec1->id = $task->taskid;
     		        $rec1->name = $task->taskname;
                    $rec1->type_ass = $task->type_ass;
       				// set_field('monit_school_assignments_'.$rid, 'name', $task->taskname, 'id', $task->taskid);
            		if (!update_record('monit_school_assignments_'.$rid, $rec1))	{
    					error(get_string('errorinupdatingtheme','block_mou_school'), $redirlink);
    			    }
       			}	
        	} else {
				$newrec->schoolid = $sid;
				$newrec->classdisciplineid = $cdid;
				$newrec->scheduleid = $jid;
        		$newrec->name = $task->taskname;
        		$date_of_schedule = get_record_select('monit_school_class_schedule_'.$rid, "id = $jid", 'id, datestart');
	        	$newrec->datestart = $date_of_schedule->datestart;
	        	// $newrec->datefinesh = $date_of_schedule->datestart;
	        	// $newrec->description = '';
	        	$fieldname = 'type_assignment'.$numtask;
	        	$newrec->type_ass = $recs->{$fieldname};

        		if (!insert_record('monit_school_assignments_'.$rid, $newrec))	{
					error(get_string('errorinaddingtheme','block_mou_school'), $redirlink);
			    }
  			}
		}
		redirect($redirlink, '', 0);
	}	
	
	$GLDATESTART = array();
	$curyear = date('Y');
	$datestart = $curyear.'-09-01';
	$curyear++;
	$dateend = $curyear.'-05-31';	

 	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	echo '<br><br>';

	if($strlistclasses = get_record_select('monit_school_class', "id = $gid", 'id, name')){
		echo '<tr><td>'.get_string('class','block_mou_school').':</td><td>';
		echo '<b>'. $strlistclasses->name;
		echo '</td></tr>';			
	}
	
	if($disc = get_record_select('monit_school_class_discipline', "id=$cdid", 'id, disciplineid')){
		if ($strlistpredmets = get_record_select('monit_school_discipline', "id={$disc->disciplineid}", 'id, name')){
			echo '<tr><td>'.get_string('predmet','block_mou_school').':</td><td>';
			echo '<b>'. $strlistpredmets->name;
			echo '</td></tr>';	
		} else if ($gid) {
			 echo '</table>';
    		notice (get_string('classdisciplinesnotfound', 'block_mou_school'), "../class/classdisciplines.php?rid=$rid&amp;yid=$yid&amp;gid=$gid&amp;sid=$sid");
		}

		$schedule = get_record_select('monit_school_class_schedule_'.$rid, "id = $jid", 'id, datestart');
			
		$rusformatdate = convert_date($schedule->datestart, 'en', 'ru');

		echo '<tr><td>'.get_string('lessondate','block_mou_school').':</td><td>';
		echo '<b>'. $rusformatdate.' '.get_string('g','block_mou_school');
		echo '</td></tr>';

		$planname = $unitname = $themename = '-';
		if ($themeid)	{
			$theme = get_record_select("monit_school_discipline_lesson_$rid", "id=$themeid", 'id, unitid, name, number');
			$themename = $theme->number.'. '.$theme->name;
			
            $unitname = $planname = 'не найден';
			if ($unit = get_record_select("monit_school_discipline_unit", "id = $theme->unitid", 'id, planid, name, number'))    {
			     $unitname = $unit->number.'. '.$unit->name; 
    			if ($plan = get_record_select("monit_school_discipline_plan", "id = $unit->planid", 'id, name')) {
    			     $planname = $plan->name; 
    			} 
			} 
		}
		
  	    echo '<tr><td>'.get_string('planplan','block_mou_school').':</td><td>';
		echo $planname; 
  		echo '</td></tr>';

  	    echo '<tr><td>'.get_string('unitplan','block_mou_school').':</td><td>';
		echo $unitname; 
  		echo '</td></tr>';

		echo '<tr> <td>'.get_string('lessonplan', 'block_mou_school').': </td><td>';
		echo $themename; 
  		echo '</td></tr></table>';
			// listbox_plans2("setheme.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;cdid=$cdid&amp;tid=$termid&amp;jid=$jid&amp;planid=", $sid, $yid, $gid, $cdid, $planid);
			// listbox_theme("setheme.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;cdid=$cdid&amp;tid=$termid&amp;jid=$jid&amp;planid=$planid&amp;themeid=", $rid, $sid, $planid, $themeid);			
	}
	echo '</table>';
				
		echo  '<form name="themes" method="post" action="setheme.php">';	
		echo  '<input type="hidden" name="rid" value="' . $rid . '">';
		echo  '<input type="hidden" name="sid" value="' . $sid . '">';
		echo  '<input type="hidden" name="yid" value="' . $yid . '">';
		echo  '<input type="hidden" name="gid" value="' . $gid . '">';
		echo  '<input type="hidden" name="cdid" value="' . $cdid . '">';
		echo  '<input type="hidden" name="tid" value="' . $termid . '">';
		echo  '<input type="hidden" name="jid" value="' . $jid . '">';
		echo  '<input type="hidden" name="tyid" value="' . $tyid . '">';
		echo  '<input type="hidden" name="themeid" value="' . $themeid . '">';
		echo  '<input type="hidden" name="planid" value="' . $planid . '">';
        echo  '<input type="hidden" name="nw" value="' . $nw . '">';
        echo  '<input type="hidden" name="ret" value="' . $ret . '">';
		$table = table_task ($yid, $rid, $sid, $tyid, $jid, $cdid, $gid);
		print_color_table($table);		
		echo  '<div align="center">';
		echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '">';
		echo  '</div></form>';

    notify ('<p><i>Замечание: в поле "Задание" добавлена возможность использования кавычек и апострофов.</i>' ,'black');
			
    print_footer();

 
function table_task ($yid, $rid, $sid, $tyid, $jid, $cdid, $gid)
{
	global $CFG;

	$table->head  = array (	get_string('typeoftask', 'block_mou_school'), get_string('task', 'block_mou_school'));
    $table->align = array ("left", "center");
    $table->class = 'moutable';
  	$table->width = '60%';
    $table->size = array ('30%', '70%');

    $typeoftaskmenu = array();
    $typeoftaskmenu[0] = get_string('selecttypeoftask', 'block_mou_school').'...';

	if($types = get_records_select('monit_school_type_assignment', '', '', 'id, name')){
		foreach($types as $type){
			$typeoftaskmenu[$type->id] = $type->name;
		}
	}

	$MAX_NUM_OF_TASK = 5;
	$numoftask = 1;
	if ($schedule = get_record_select('monit_school_class_schedule_'.$rid, "id = $jid", 'id'))	{
        // print_r($schedule);
        $ishometask = false;
		if($tasks = get_records_select('monit_school_assignments_'.$rid, "scheduleid = $schedule->id", '', 'id, type_ass, name'))	{
		    // print_r($tasks);
			foreach($tasks as $task)	{
			    if ($task->type_ass == 2)    {
			         $ishometask = true;
			    }
                $taskname = s($task->name);
				$insidetable = "<input type=text  name=t_{$numoftask}_{$task->id} size=60 value=\"$taskname\"/>";
				$type = choose_from_menu ($typeoftaskmenu, "type_assignment".$numoftask, $task->type_ass, '', "", "", true);
				$table->data[] = array ($type, $insidetable);
				$numoftask++;					
			}
		}
		
		for($i = $numoftask; $i <= $MAX_NUM_OF_TASK; $i++) {
		    if (!$ishometask && $numoftask == 1) {
    			$insidetable = "<input type=text  name=t_{$i}_0 size=60 value='-'>";
    			$type2 = choose_from_menu ($typeoftaskmenu, 'type_assignment'.$i, 2, '', "", "", true);
                $ishometask = true;
		    } else {
    			$insidetable = "<input type=text  name=t_{$i}_0 size=60 value=''>";
    			$type2 = choose_from_menu ($typeoftaskmenu, 'type_assignment'.$i, '', '', "", "", true);
		    }
			$table->data[] = array ($type2, $insidetable);             
		}
	}

    return $table;
}


   
function find_form_theme_errors(&$recs, &$err)
{
  $textlib = textlib_get_instance();
  $recs->name = $textlib->strtoupper($recs->name);

  $recs->name = translit_english_utf8($recs->name);

  $symbols = array (' ', '\"', "\'", "`", '-', '#', '*', '+', '_', '=');
  
  foreach ($symbols as $sym)	{
	  $recs->name = str_replace($sym, '', $recs->name);
  }
  
  if (empty($recs->name))	{
	    $err["name"] = get_string("missingname");
  }

    return count($err);
}
?>