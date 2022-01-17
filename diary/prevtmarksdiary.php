<?php // $Id: prevtmarksdiary.php,v 1.3 2012/02/21 06:34:41 shtifanov Exp $

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

		    $currenttab = 'prevtotalmarks';
		    include('tabs_dairy.php');

        
            $prevyid = $yid - 1;

            $prevschool = get_record_sql("SELECT id FROM {$CFG->prefix}monit_school
            						      WHERE uniqueconstcode={$school->uniqueconstcode} and yearid=$prevyid");
                                            
            $prevpupilcard = get_record_sql("SELECT id, classid FROM {$CFG->prefix}monit_school_pupil_card
            						         WHERE userid=$uid and yearid=$prevyid");
            
            $prevclass = get_record_sql("SELECT id, name, parallelnum  FROM {$CFG->prefix}monit_school_class
            						     WHERE id=$prevpupilcard->classid"); 
        

            print_heading(get_string('class', 'block_mou_school').': '.$prevclass->name); 
                           
 			$table = table_diary_totalmarks_prev($prevyid, $uid);
			print_color_table($table);
    }

	print_footer();

    
  
function table_diary_totalmarks_prev($prevyid, $uid)
{
	global $CFG, $prevclass, $prevschool;


 	// $class = get_record('monit_school_class', 'id', $gid);
	$class_termtype = get_record_sql("SELECT id, termtypeid FROM mou_archive.{$CFG->prefix}monit_school_class_termtype_$prevyid
    						          WHERE schoolid={$prevschool->id} AND parallelnum = {$prevclass->parallelnum}");

	$school_terms = get_records_sql("SELECT id, name FROM mou_archive.{$CFG->prefix}monit_school_term_$prevyid
    						         WHERE schoolid = {$prevschool->id} AND  termtypeid = {$class_termtype->termtypeid}");	  
	
	$colspan = count($school_terms);
	
	$table->dblhead->head1  = array (get_string("ordernumber","block_mou_school"),
									 get_string("predmet","block_mou_school"), 
									 get_string("teacher","block_mou_school"), 
									 get_string('studyperiods', 'block_mou_school'), 
									 get_string('year', 'block_mou_school'));
	$table->dblhead->span1  = array ("rowspan=2", "rowspan=2", "rowspan=2", "colspan=$colspan", "rowspan=2");
	$table->align = array ('left',  'left',  'left', 'center', 'center');
	$table->columnwidth = array (10, 20, 20);
	
	$redirlink = "tmarksdiary.php?yid=$prevyid";

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


	$strsql = "SELECT id, disciplineid, teacherid FROM mou_archive.{$CFG->prefix}monit_school_class_discipline_$prevyid
						WHERE schoolid={$prevschool->id} and classid={$prevclass->id}
						ORDER BY name";
    // echo $strsql . '<br>'; 							
									
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
                    $strsql = "SELECT id, mark, avgmark FROM mou_archive.{$CFG->prefix}monit_school_marks_totals_term_$prevyid
                               WHERE termid={$school_term->id} and classdisciplineid=$classdiscipline->id and userid=$uid and schoolid={$prevschool->id}";
                    // echo $strsql . '<br>';                               
					if ($markstuder = get_record_sql($strsql))	{
						$avgmark = number_format($markstuder->avgmark, 2, ',', '');
						$mark = $markstuder->mark;
						$tabledata[] = '<b>'.$mark.'</b> ('. $avgmark . ')';
					} else {
						$tabledata[] = '-';
					}
				}
			}		

			if ($marktotal = get_record_sql("SELECT id, mark, avgmark FROM mou_archive.{$CFG->prefix}monit_school_marks_totals_year_$prevyid
                                where classdisciplineid = $classdiscipline->id AND userid = $uid"))	{
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