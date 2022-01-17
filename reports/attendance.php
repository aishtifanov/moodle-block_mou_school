<?php // $Id: attendance.php,v 1.1 2014/06/03 06:41:47 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_school.php');
   	require_once('../authbase.inc.php');
    require_once('lib_report.php');    

    $rpid = optional_param('rpid', 0, PARAM_INT);       // Report id
    $termid = optional_param('tid', 0, PARAM_INT);		//Term id
    $gid = optional_param('gid', 0, PARAM_INT);			//Class id
    
    $scriptname = basename($_SERVER['PHP_SELF']);	// echo '<hr>'.basename(me());

    if ($action == 'excel' && $rpid == 1) 	{
		$table = table_itogi ($yid, $rid, $sid, $gid);
        print_table_to_excel($table);
        exit();
	}	


    $currenttab = 'attendance';
    include('tabsreports.php');

	if (has_capability('block/mou_school:viewreports', $context))	{
		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		listbox_attendance_reports("$scriptname?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;rpid=", $rid, $sid, $yid, $rpid);
		switch ($rpid){
			case '2':
	
				listbox_class("$scriptname?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;rpid=$rpid&amp;gid=", $rid, $sid, $yid, $gid);
				listbox_terms("$scriptname?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;rpid=$rpid&amp;gid=$gid&amp;tid=", $sid, $yid, $gid, $termid);
	
				if ($gid != 0 && $termid != 0){				
	           		$table = table_itogi_attendance ($yid, $rid, $sid, $gid, $termid);
	           		print_color_table($table);
					$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'rpid' => $rpid, 'action' => 'excel');
					echo '<table align="center" border=0><tr><td>';
				  //  print_single_button("$scriptname", $options, get_string("downloadexcel"));
					echo '</td>';
					echo '</tr></table>';
				}
			break;
		}
	
	    echo '</table>';
	} else {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}
	    
    print_footer($SITE);




function table_itogi_attendance ($yid, $rid, $sid, $gid, $termid)
	{
		global $CFG;

		$table->dblhead->head1  = array (get_string('ordernumber','block_mou_school'),get_string('pupilfio','block_mou_school'),
								get_string('dni','block_mou_school'),get_string('lesson','block_mou_school'));
		$table->dblhead->span1  = array ("rowspan=2", "rowspan=2","colspan=2", "colspan=2");

		$table->align = array ('center', 'left','center', 'center');
	    $table->size = array ('7%','40%', '10%', '10%');
		$table->columnwidth = array (7, 30, 20, 10);

	    $table->dblhead->head2[]  = get_string('all','block_mou_school');
	    $table->align[] = 'center';
	    $table->columnwidth[] = 10;

	    $table->dblhead->head2[] = get_string('byuvagprich','block_mou_school');
		$table->align[] = 'center';
		$table->columnwidth[] = 10;

		$table->dblhead->head2[]  = get_string('all','block_mou_school');
	    $table->align[] = 'center';
	    $table->columnwidth[] = 10;

	    $table->dblhead->head2[] = get_string('byuvagprich','block_mou_school');
		$table->align[] = 'center';
		$table->columnwidth[] = 10;
        $table->class = 'moutable';
		$table->titlesrows = array(10);

		$studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.picture,
							    u.phone1, u.phone2, m.classid, m.pol, m.birthday, m.pswtxt
	                           FROM {$CFG->prefix}user u
		                       LEFT JOIN {$CFG->prefix}monit_school_pupil_card m ON m.userid = u.id
							   WHERE classid = $gid AND u.deleted = 0 AND u.confirmed = 1
							   ORDER BY u.lastname, u.firstname";

        if($students = get_records_sql($studentsql)) {

   			$school_term = get_record('monit_school_term', 'id', $termid); // приходит с URL

			$aday=array();
			$uday=array();
			$ales=array();
			$ules=array();
			foreach ($students as $student)	{
				 $aday[$student->id] = $uday[$student->id] = 0;
 				 $ales[$student->id] = $ules[$student->id] = 0;
			}


            if ($school_term){
                $schoolstart = explode('-',$school_term->datestart);
            	$schoolend = explode('-',$school_term->dateend);
				$timestart = make_timestamp ($schoolstart[0],$schoolstart[1],$schoolstart[2],12);
				$timeend   = make_timestamp ($schoolend[0],$schoolend[1],$schoolend[2],12);
	   			for ($idate = $timestart; $idate<=$timeend; $idate += DAYSECS)		{
	   				$datecurr = date('Y-m-d', $idate);

					if ($schedulesql = get_records_sql("SELECT id FROM {$CFG->prefix}monit_school_class_schedule_$rid
									                    WHERE classid=$gid AND datestart = '$datecurr'")){
						foreach ($students as $student)	{
							$i=1;
				        	$a = $u = 0;
				        	if ($schedulesql)  foreach ($schedulesql as $schedules)	{

								if ($attendance = get_record_sql("SELECT id, reason FROM {$CFG->prefix}monit_school_attendance_$rid
											                      WHERE userid={$student->id} and scheduleid={$schedules->id}"))
								{
		                            $a++;
									$ales[$student->id]++;

		                            if ($attendance->reason == 'у' or $attendance->reason == 'У'){
			                            $ules[$student->id]++;
		          	   					$u++;
		                            }
		                        }
							}

							if ($u == count($schedules))	{
								$uday[$student->id]++;
							 	$aday[$student->id]++;
							} else if ($a == count($schedules))	{
							 	$aday[$student->id]++;
							}

						}
					}
	                // print_r($schedulesql); echo '<hr>';
				}
            }


		    }

		    if($students = get_records_sql($studentsql)) {
		    	$i = 1;
				foreach ($students as $student)	{
					$tabledata = array($i.'.');
					$tabledata[] = "<div align=left><strong><a href=\"{$CFG->wwwroot}/blocks/mou_school/class/pupilcard.php?mode=4&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid={$student->id}\">".fullname($student)."</a></strong></div>";
	                $tabledata[] = $aday[$student->id];
	                $tabledata[] = $uday[$student->id];
	                $tabledata[] = $ales[$student->id];
	                $tabledata[] = $ules[$student->id];
					$i++;
					$table->data[] = $tabledata;
				}
		    }              // print_r($attendances);

           return $table;
		}
?>

