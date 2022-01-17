<?php // $Id: discipteachers.php,v 1.7 2011/08/01 08:38:16 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_school.php');
	require_once('../authbase.inc.php');
    
    $did = optional_param('did', '0', PARAM_INT);       
    
    switch ($action)	{
    	case 'excel': $table = table_discipline ($yid, $rid, $sid);
    				  // print_r($table);
        			  print_table_to_excel($table,1);
        			  exit();
        			  
		case 'clear':   $did = optional_param('did', '0', PARAM_INT);
						if ($did != 0) 	{
					        // if (!set_field('monit_school_textbook', 'textbooksids', '', 'yearid', $yid , 'schoolid', $sid, 'discegeid', $did))  {
							if (!delete_records('monit_school_teacher', 'disciplineid', $did, 'schoolid', $sid))  {
					             notify("Could not delete records in monit_school_teacher.");
					        }
	 				   }
	 				   break;


		case 'setstandart':
						$redirlink = "discipteachers.php?sid=$sid&amp;yid=$yid&amp;rid=$rid";
						$textlib = textlib_get_instance();
						
						if ($disciplines = get_records_sql("SELECT id, name FROM {$CFG->prefix}monit_school_discipline  WHERE schoolid=$sid ORDER by name"))  {
				
                            $strsql = "SELECT s.userid, a.appointment FROM mdl_monit_att_staff s 
                                       INNER JOIN mdl_monit_att_appointment a ON s.id=a.staffid
                                       WHERE schoolid=$sid";
							if ($teachers = get_records_sql($strsql))	{
								
								foreach ($disciplines as $discipline)	{
									$dname = substr($discipline->name, 0, 8);
									$dname = $textlib->strtolower($dname);
									
									foreach ($teachers as $teacher)	{
										$pos = $textlib->strpos($teacher->appointment, $dname);
										 // echo $dname . '  === ' . $teacher->appointment_ped . '<br>';
										if ($pos !== false) {
											if (!record_exists('monit_school_teacher', 'schoolid', $sid, 'teacherid', $teacher->userid, 'disciplineid', $discipline->id))	{
										        $rec->teacherid = $teacher->userid;
												$rec->disciplineid = $discipline->id;
												$rec->schoolid = $sid;
										    	if (insert_record('monit_school_teacher', $rec)){
										    	//	notify('Your record added');
										    	} else{
										    		error('Error in adding teacher!', $redirlink);
										    	}
										    }	
											// echo $discipline->name . '  === ' . $teacher->appointment_ped . '<br>';
										}
									}
								}
							}	
						}
						break;
						
	}					 

	
	$currenttab = 'discipteachers';
    include('tabsdis.php');

	if (has_capability('block/mou_school:viewdiscipline', $context))	{
		
		$table = table_discipline ($yid, $rid, $sid);
	    print_color_table($table);
	
		if (has_capability('block/mou_school:editdiscipline', $context))	{
			$options = array('mode' => 'new' , 'rid' => $rid, 'sid' => $sid, 'yid' => $yid);
			echo '<table align="center" border=0><tr><td>';
		    print_single_button("adddiscip.php", $options, get_string('adddiscip','block_mou_school'));
			echo '</td><td>';
			$options = array('mode' => 'new' , 'rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'action' => 'setstandart');	
		 	print_single_button("discipteachers.php", $options, get_string('setautomaticteachers', 'block_mou_school'));
			echo '</td></tr><tr><td align=center colspan=2>';
			$options = array('mode' => 'new' , 'rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'action' => 'excel');	 	
		    print_single_button("discipteachers.php", $options, get_string("downloadexcel"));
			echo '</td></tr></table>';
		}	
		
	} else {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}
		

    print_footer();



function table_discipline ($yid, $rid, $sid)
{
	global $CFG, $context;
	
	$edit_capability = has_capability('block/mou_school:editdiscipline', $context);

	$table->head  = array (	get_string('predmet', 'block_mou_school'),
							get_string('shortname', 'block_mou_school'),
							get_string('teachers', 'block_mou_school'),
							get_string('action', 'block_mou_school'));
    $table->align = array ("left", "center", "left", "center");
    $table->size = array ('30%', '10%', '40%', '10%');
	$table->columnwidth = array (35, 10, 40);    
	$table->class = 'moutable';
   	$table->width = '80%';

	$table->titles = array();
    $table->titles[] = get_string('discipteachers', 'block_mou_school');
    $table->titlesrows = array(30);
    $table->worksheetname = 'discipteachers';
    $table->downloadfilename = 'discipteachers';

   if ($discipline = get_records_sql("SELECT id, name, shortname FROM {$CFG->prefix}monit_school_discipline
   									  WHERE schoolid=$sid
   									  ORDER by name"))  {

       foreach ($discipline as $disc) {

            $strteachers = '';
       	 	$teachers = get_records_sql("SELECT * FROM {$CFG->prefix}monit_school_teacher
       	 								 WHERE schoolid=$sid AND disciplineid={$disc->id}");
            if ($teachers)  {
                  foreach ($teachers as $teach)  {
                    $user=get_record_sql("SELECT id, lastname, firstname FROM {$CFG->prefix}user
	              						  WHERE id={$teach->teacherid}");
	            	$fullname = fullname($user);
 		           	$strteachers .= '* ' . $fullname . '<br>';
                  }
            } else {
            	$strteachers = '-';
         	}

			if ($edit_capability)	{
				$title = get_string('editteachdiscip','block_mou_school');
		   		$strlinkupdate = "<a title=\"$title\" href=\"editteachdiscip.php?sid=$sid&amp;yid=$yid&amp;rid=$rid&amp;did={$disc->id}\">";
				$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";
				$title = get_string('clearteachers','block_mou_school');
		   		$strlinkupdate .= "<a title=\"$title\" href=\"discipteachers.php?action=clear&amp;sid=$sid&amp;did={$disc->id}&amp;yid=$yid&amp;rid=$rid\">";
				$strlinkupdate .= "<img src=\"{$CFG->wwwroot}/blocks/mou_school/i/goom.gif\" alt=\"$title\" /></a>&nbsp;";
				// $title = get_string('disciplines','block_mou_school');
			} else	{
				$strlinkupdate = '-';
			}

            $table->data[] = array ($disc->name, $disc->shortname, $strteachers, $strlinkupdate);

		}
	}	else {
		$table->data[] = array ('', '', '', '');
	}

    return $table;
}
?>

