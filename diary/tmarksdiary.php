<?php // $Id: tmarksdiary.php,v 1.5 2012/02/21 06:34:41 shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_school.php'); 
    require_once($CFG->dirroot.'/calendar/lib.php');
    require_once('../lib_school.php');
	require_once('authdiary.inc.php');

	
    if($strminipupilcard != '')	{
    	
    		echo $strminipupilcard;

		    $currenttab = 'totalmarks';
		    include('tabs_dairy.php');

 			$table = table_diary_totalmarks ($yid, $rid, $sid, $gid, $uid);
			print_color_table($table);
    }

	print_footer();

    
  
function table_diary_totalmarks  ($yid, $rid, $sid, $gid, $uid)
{
	global $CFG, $class;

 	// $class = get_record('monit_school_class', 'id', $gid);
	$class_termtype = get_record_select('monit_school_class_termtype', "schoolid=$sid AND parallelnum = {$class->parallelnum}", 'id, termtypeid');

	$school_terms = get_records_select('monit_school_term', "schoolid = $sid AND  termtypeid = {$class_termtype->termtypeid}", '', 'id, name');	  
	
	$colspan = count($school_terms);
	
	$table->dblhead->head1  = array (get_string("ordernumber","block_mou_school"),
									 get_string("predmet","block_mou_school"), 
									 get_string("teacher","block_mou_school"), 
									 get_string('studyperiods', 'block_mou_school'), 
									 get_string('year', 'block_mou_school'));
	$table->dblhead->span1  = array ("rowspan=2", "rowspan=2", "rowspan=2", "colspan=$colspan", "rowspan=2");
	$table->align = array ('left',  'left',  'left', 'center', 'center');
	$table->columnwidth = array (10, 20, 20);
	
	$redirlink = "tmarksdiary.php?yid=$yid";

	foreach ($school_terms as $school_term) {
	 	$edit_month = $school_term->name;
		$table->dblhead->head2[]  = $edit_month;
		$table->align[] = 'center';
		$table->columnwidth[] = 10;	
	}
	
    $table->class = 'moutable';
   	$table->width = '90%';
    $table->titles = array();
    $table->titles[] = get_string('totalmarks', 'block_mou_school');
    $table->worksheetname = 'totalmarks';
    
	$tabledata = array();


	$strsql = "SELECT id, disciplineid, teacherid FROM {$CFG->prefix}monit_school_class_discipline
						WHERE classid=$gid
						ORDER BY name";
  // echo $strsql; 							
									
	if ($classdisciplines = get_records_sql ($strsql))	{
		$num = 1;
		foreach ($classdisciplines as $classdiscipline) {
			$strdiscipline = $strteacher = '-';  
			if ($discipline = get_record_select('monit_school_discipline', "id = $classdiscipline->disciplineid", 'id, name'))	{
				$strdiscipline = $discipline->name;
			}	
				
			if (!empty($classdiscipline->teacherid))	{
				$teacher = get_record_select('user', "id={$classdiscipline->teacherid}" , 'id, lastname, firstname');
				$strteacher	= fullname ($teacher);
			}
				
		    $tabledata = array($num.'.', $strdiscipline, $strteacher);		

			if($school_terms)  {
				foreach ($school_terms as $school_term) {
				
					$avgmark = $mark = '-';
                    $strsql = "SELECT id, mark, avgmark FROM {$CFG->prefix}monit_school_marks_totals_term
                                WHERE userid=$uid AND classdisciplineid=$classdiscipline->id AND termid={$school_term->id}";
                    // echo   $strsql . '<br />';          
					if ($markstuder = get_record_sql($strsql))	{
						$avgmark = number_format($markstuder->avgmark, 2, ',', '');
						$mark = $markstuder->mark;
						$tabledata[] = '<b>'.$mark.'</b> ('. $avgmark . ')';
					} else {
						$tabledata[] = '-';
					}
				}
			}		

			$strselect = "classdisciplineid = $classdiscipline->id AND userid = $uid";
			if ($marktotal = get_record_select('monit_school_marks_totals_year', $strselect, 'id, mark, avgmark'))	{
				$stravg = number_format($marktotal->avgmark, 2, ',', '');
				$tabledata[] = '<b>'.$marktotal->mark.'</b> ('. $stravg . ')';
			} else {
				$tabledata[] = '-';
			}
			
			$table->data[] = $tabledata;
			$num++;
		}
   }
				
   return $table;
}
?>