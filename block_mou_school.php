<?php // $Id: block_mou_school.php,v 1.22 2011/10/25 05:43:33 shtifanov Exp $


class block_mou_school extends block_list {

    function init() {
        $this->title = get_string('mouschool','block_mou_school');
        $this->version = 2010210400;
    }

    function get_content() {
        global $CFG;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if (empty($this->instance)) {
            $this->content = '';
        } else {
            $this->load_content();
        }

        return $this->content;
        }

    function load_content() {
        global $CFG, $yearmonit, $USER;

		// $yid = 5;// $yearmonit;  !!!!!!!!!!!!!!!!!!!!1

        $year = date("Y");
        $m = date("n");
        if(($m >= 1) && ($m <=7)) {  /// !!!!!!!!
    		$y = $year-1;
        } else {
    		$y = $year;
    		$year = $year+1;
        }
    
    	$cey = "$y/$year";
    	if ($year = get_record_select('monit_years', "name = '$cey'", 'id'))	{
      		$yid = $year->id;
    	} else {
      		$yid = 0;
    	}            
            
		$rid = $sid = 0;

		$index_items = array(9);
		
		$pupil_is = ispupil();
		if  ($pupil_is) 	{
			$index_items = array(8);
		}	
		
		$admin_is = isadmin();
		$region_operator_is = ismonitoperator('region');
		if  ($admin_is || $region_operator_is) 	{
			$index_items = array(0,1,2,3,4,5,6,7,8,9);
		}	

		// $context = get_context_instance(CONTEXT_SYSTEM);
		// $context = get_context_instance(CONTEXT_RAYON, 1);

		$strsql = "SELECT a.id, roleid, contextid, contextlevel, userid  
					FROM mdl_role_assignments a	JOIN mdl_context ctx ON a.contextid=ctx.id
				   WHERE userid={$USER->id}";
		if ($ctxs = get_records_sql($strsql))	{
			
			foreach($ctxs as $ctx1)	{
				switch ($ctx1->contextlevel)	{
					case CONTEXT_REGION:
        			case CONTEXT_RAYON:  if ($ctx1->roleid == 8)	{
										 	$idx_rayon = array(0,1,2,3,4,5,6,7,8,9);
        								 	$index_items = array_merge ($idx_rayon, $index_items);
        								 }	
           						         break;

        			case CONTEXT_SCHOOL: if ($ctx1->roleid < 13)	{
									 		$idx_school = array(0,1,2,3,4,5,6,7,9);
									 	} else {
									 		$idx_school = array(0,1);
									 	}	
        								 $index_items = array_merge ($idx_school, $index_items);
           						         break;
           						         
        			case CONTEXT_CLASS:  $idx_class = array(0,1,3,6,9);
        								 $index_items = array_merge ($idx_class, $index_items);
           						         break;

        			case CONTEXT_DISCIPLINE:  $idx_discipline = array(0,1,3,4,5,6,9);
        								 $index_items = array_merge ($idx_discipline, $index_items);
           						         break;
           						         
				}
			}
			
			$index_items = array_unique($index_items);
			sort($index_items);
		}		 
		
		$items[] = '<a href="'.$CFG->wwwroot."/blocks/mou_school/periods/typestudyperiod.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">".get_string('studyperiods', 'block_mou_school').'</a>';
		$icons[] = '<img src="'.$CFG->wwwroot.'/blocks/mou_school/i/curric.gif" height="16" width="16" alt="" />';

		$items[] = '<a href="'.$CFG->wwwroot."/blocks/mou_school/curriculum/discipline.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">".get_string('discipline', 'block_mou_school').'</a>';
		$icons[] = '<img src="'.$CFG->wwwroot.'/blocks/mou_ege/i/textbooks.gif" height="16" width="16" alt="" />';

		$items[] = '<a href="'.$CFG->wwwroot."/blocks/mou_school/curriculum/profiles.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">".get_string('curriculums','block_mou_school').'</a>';
		$icons[] = '<img src="'.$CFG->wwwroot.'/blocks/mou_school/i/curric.gif" height="16" width="16" alt="" />';

		$items[] = '<a href="'.$CFG->wwwroot."/blocks/mou_school/class/classlist.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">".get_string('classespredmet','block_mou_school').'</a>';
        $icons[] = '<img src="'.$CFG->pixpath.'/i/group.gif" height="16" width="16" alt="" />';

		$items[] = '<a href="'.$CFG->wwwroot."/blocks/mou_school/plans/planplans.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">".get_string('lessonsplan','block_mou_school').'</a>';
 	    $icons[] = '<img src="'.$CFG->pixpath.'/i/settings.gif" height="16" width="16" alt="" />';

    	$items[] = '<a href="'.$CFG->wwwroot."/blocks/mou_school/schedule/viewschedule.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">".get_string('schedule','block_mou_school').'</a>';
 	    $icons[] = '<img src="'.$CFG->pixpath.'/i/settings.gif" height="16" width="16" alt="" />';

        $items[] = '<a href="'.$CFG->wwwroot."/blocks/mou_school/journal/journalclass.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">".get_string('journalclass','block_mou_school').'</a>';
        $icons[] = '<img src="'.$CFG->wwwroot.'/blocks/mou_school/i/journal.gif" height="16" width="16" alt="" />';

        $items[] = '<a href="'.$CFG->wwwroot."/blocks/mou_school/reports/administrative.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">".get_string('reports', 'block_mou_school').'</a>';
	    $icons[] = '<img src="'.$CFG->pixpath.'/i/report.gif" height="16" width="16" alt="" />';

        $items[] = '<a href="'.$CFG->wwwroot."/blocks/mou_school/diary/diary.php?yid=$yid\">".get_string('diary','block_mou_school').'</a>';
        $icons[] = '<img src="'.$CFG->wwwroot.'/blocks/mou_school/i/journal.gif" height="16" width="16" alt="" />';
    
    	$items[] = '<a href="'.$CFG->wwwroot.'/file.php/1/step_by_step.pdf">'.get_string('instruction', 'block_mou_school').'</a>';
	    $icons[] = '<img src="'.$CFG->pixpath.'/i/info.gif" height="16" width="16" alt="" />';


		
		if (!empty($index_items))	{			
			foreach ($index_items as $index_item)	{
				$this->content->items[] = $items[$index_item];
				$this->content->icons[] = $icons[$index_item];
			}

		    $this->content->footer = '<a href="'.$CFG->wwwroot.'/blocks/mou_school/index.php">'.get_string('mouschool', 'block_mou_school').'</a>'.' ...';
 		}
   
	}	    

  }
  
  /*
  function get_context_with_max_access()
{		
	$strsql = "SELECT roleid, contextid, contextlevel, userid  FROM mdl_role_assignments a
				JOIN mdl_context ctx ON a.contextid=ctx.id
			   WHERE userid={$USER->id} and contextlevel>1000";
	if ($ctxx = get_records_sql($strsql))	{
		$minctxlevel = 2000;
		foreach($ctxx as $ctx1)	{
			if ($ctx1->contextlevel < $minctxlevel)	{
				$minctxlevel = $ctx1->contextlevel; 
				$contextid = $ctx1->contextid; 
			}
		}	
		$context = get_context_instance_by_id($contextid);
	} else {
		$context = false;
	}		   
	
	return  $context;
}	 
*/
 ?>
