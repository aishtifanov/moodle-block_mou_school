<?php // $Id: attendance.php,v 1.13 2012/02/13 10:32:24 shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once($CFG->dirroot.'/calendar/lib.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_school.php');
	require_once('../authbase.inc.php');

	$did 	= optional_param('did', 0, PARAM_INT);	  // Curriculum id
    $termid	= optional_param('tid', 0, PARAM_INT);   // Teacher id
    $gid 	= optional_param('gid', 0, PARAM_INT);   // Class id
    $p 		= optional_param('p', 	0, PARAM_INT);   // Parallel number
    $period = optional_param('p', 	'day'); // Period time: day, week, month, year
	$uid 	= optional_param('uid', 0, PARAM_INT);
	$mon 	= optional_param('mon', 9, PARAM_INT);

    $currenttab = 'attendance';
    include('tabsjrnl.php');

	if (has_capability('block/mou_school:viewjournalclass', $context))	{
	 	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
    	$strlistclasses =  listbox_class_role("attendance.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;mon=$mon&amp;gid=", $rid, $sid, $yid, $gid);
    	echo $strlistclasses;
 	
		// listbox_class("attendance.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;mon=$mon&amp;gid=", $rid, $sid, $yid, $gid);
		//listbox_edu_year_months("journalclass.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;uid=0&amp;gid=$gid&amp;mon=", $rid, $sid, $yid, $uid, $gid, $mon);
		$monthmenu = array();
	    $monthmenu[9] = get_string('september', 'calendar');
	    $monthmenu[10] = get_string('october', 'calendar');
	    $monthmenu[11] = get_string('november', 'calendar');
	    $monthmenu[12] = get_string('december', 'calendar');
	    $monthmenu[1] = get_string('january', 'calendar');
	    $monthmenu[2] = get_string('february', 'calendar');
	    $monthmenu[3] = get_string('march', 'calendar');
	    $monthmenu[4] = get_string('april', 'calendar');
	    $monthmenu[5] = get_string('may', 'calendar');
	    $monthmenu[6] = get_string('june', 'calendar');
	    $monthmenu[7] = get_string('july', 'calendar');
	    $monthmenu[8] = get_string('august', 'calendar');
		$g = get_string('g','block_mou_school');
	    $eduyear = current_edu_year();
	    list($yfirst, $ysecond) = explode('/', $eduyear);
		
		for ($i=9; $i<=12; $i++){
			$monthmenu[$i] =$monthmenu[$i].'  '.$yfirst.$g;  
		}
		for ($i=1; $i<=8; $i++){
			$monthmenu[$i] = $monthmenu[$i].'  '.$ysecond.$g;
		}
         
        if ($mon>=9  && $mon<=12)   {
            $curryear = $yfirst;         
        } else {
            $curryear = $ysecond;
        }
           
		echo '<tr><td>'.get_string('month','block_mou_school').':</td><td>';
		popup_form("attendance.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;mon=", $monthmenu, "switchdisc", $mon, "", "", "", false);
		echo '</td></tr>';
	    echo '</table>';
	    
		if ($gid != 0)	{
			$table = table_attendance($rid, $sid, $yid, $gid, $did, $termid, $mon, $curryear);
			print_color_table($table);
		}
	} else {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}

    print_footer();
    

function table_attendance($rid, $sid, $yid, $gid, $did, $termid, $mon, $curryear)
{
	global $CFG;
	
	$month = get_string('fn_'.$mon,'block_mou_school');
	 $y=1;
     $display = calendar_days_in_month($mon, $y);
    // echo $display . '<hr>';
    //	echo $month;
	$table->dblhead->head1  = array (get_string('pupils', 'block_mou_school'), $month ,get_string('all', 'block_mou_school'));
	$table->dblhead->span1  = array ("rowspan=2", "colspan=$display", "rowspan=2");
	$table->align = array ('left', 'center', 'center');
	$table->columnwidth = array (20);
	
	
	$sheduleslist = array();
	for ($g=1; $g<=$display; $g++) {
		$table->dblhead->head2[]  = $g;
		$table->align[] = 'center';
		$table->columnwidth[] = 10;
		
		$ts = make_timestamp($curryear, $mon, $g);
		$daystart = date('Y-m-d', $ts); 
	//	echo($daystart).'<br>'; 

        $shedulesids= array();
		$strsql = "SELECT id FROM {$CFG->prefix}monit_school_class_schedule_$rid
		           WHERE classid=$gid AND datestart = '$daystart'";	  
		if ($shedules = get_records_sql($strsql)){
		 		foreach ($shedules as $sa)  {
			        $shedulesids[] = $sa->id;
			    }
			    $sheduleslist[$g] = implode(',', $shedulesids);
			}
   }
	
	// echo '<pre>'; print_r($sheduleslist); echo '</pre>'; echo '<hr>'; 
	
    $table->class = 'moutable';
   	$table->width = '90%';
    $table->titles = array();
    $table->titles[] = get_string('createschedule', 'block_mou_school');
    $table->worksheetname = 'createschedule';
	
	$tabledata = array();
    $studentsql = "SELECT u.id, u.username, u.firstname, u.lastname
                            FROM {$CFG->prefix}user u
                       LEFT JOIN {$CFG->prefix}monit_school_pupil_card m ON m.userid = u.id 
					   WHERE classid = $gid AND u.deleted = 0 AND u.confirmed = 1
					   ORDER BY u.lastname, u.firstname";

    if($students = get_records_sql($studentsql)) 	{
   	
		foreach ($students as $student) 	{
			$i = 0;
			$tableattendace = array();
			
			for ($g=1; $g<=$display; $g++)	{
				if (!empty($sheduleslist[$g])) {
				
					$strsql = "SELECT id, reason FROM {$CFG->prefix}monit_school_attendance_$rid
								WHERE (userid={$student->id}) AND (scheduleid in ({$sheduleslist[$g]}))";
					// echo $strsql . '<hr>'; 			
					if ($attendances = get_records_sql($strsql)){
						$count_attendance = 0;
						foreach ($attendances as $att){
							if (!empty($att->reason))	{
									$count_attendance++;
							} 
						}
						$tableattendace[$g] = $count_attendance;
					}
				}	
			}
			$tabledata = array("<div align=left><strong><a href=\"{$CFG->wwwroot}/blocks/mou_school/class/pupilcard.php?mode=4&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid={$student->id}\">".fullname($student)."</a></strong></div>");
			
			$summa = 0;
			for ($g=1; $g<=$display; $g++)	{						
				if (!empty($tableattendace[$g])) {
					$tabledata[] = $tableattendace[$g];
					$summa += $tableattendace[$g];
				} else {
					$tabledata[] = '';
				}
			}	
			
			if (!empty($summa)) {
				$tabledata[] = $summa;
			} else {
				$tabledata[] = '';
			}	
			
			$table->data[] = $tabledata;		
		}
	}
				
    return $table;
}
?>


