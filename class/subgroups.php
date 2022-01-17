<?php // $Id: subgroups.php,v 1.10 2012/02/13 10:32:24 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_school.php');
	require_once('../authbase.inc.php');    

    $gid = optional_param('gid', 0, PARAM_INT);   // Class id
    $tabname = optional_param('tn', 'listsubgroup');   // Tab name

	$edit_capability = has_capability('block/mou_school:editclasslist', $context);

	$context_class = get_context_instance(CONTEXT_CLASS, $gid);
	$edit_capability_class = has_capability('block/mou_school:editclasslist', $context_class);

	$action   = optional_param('action', '');
    if ($action == 'excel') {
		$table = table_listsubgroup($rid, $sid, $yid, $gid);
        print_table_to_excel($table, 1);
        exit();
	}

    $currenttab = 'subgroups';
    include('tabsclasses.php');

	if (has_capability('block/mou_school:viewclasslist', $context))	{
		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		listbox_class("subgroups.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=", $rid, $sid, $yid, $gid);
		echo '</table>';

		if ($gid != 0)  {

		    $currenttab = $tabname;
		    include('tabsclass.php');
		    /*
		    if ($tabname == 'listsubgroup') {
		    	$table = table_listsubgroup ($rid, $sid, $yid, $gid);
		    } else if ($tabname == 'pupilssubgroup') {
		    	$table = table_pupilssubgroup ($rid, $sid, $yid, $gid);
		    }
			*/
			$funcname = 'table_' . $tabname;
			$table = $funcname ($rid, $sid, $yid, $gid);
			print_color_table($table);
	
	   		$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'gid' => $gid, 'action' => 'excel');
			echo '<table align="center" border=0><tr><td>';
			print_single_button("subgroups.php", $options, get_string("downloadexcel"));
		   	echo '</td></tr></table>';
		}   	
    }

    print_footer();
    
    
function table_listsubgroup($rid, $sid, $yid, $gid)
{
		global $CFG, $edit_capability, $edit_capability_class;
		
       	$table->head  = array (	get_string("ordernumber","block_mou_school"),
		   						get_string('subgroups', 'block_mou_school'),
       							get_string('classpupils', 'block_mou_school'),
								get_string("action", "block_mou_school"));
								
								// get_string('pol', 'block_mou_school'), get_string('birthday', 'block_mou_school'));
  		$table->align = array ("center", "left", "left", "center");
  		$table->size = array ('5%', '40%', '35%', '5%');
  		$table->columnwidth = array (7, 40, 30, 7);
        $table->class = 'moutable';
        $table->titles = array();
   		$table->titles[] = get_string('subgroups', 'block_mou_school');
	    $table->titlesrows = array(30);
	    $table->worksheetname = 'subgroups';
	 	$table->downloadfilename = 'subgroups'.$gid;
	         
		$studentsql = "SELECT u.id, u.firstname, u.lastname
                      	FROM {$CFG->prefix}user u
               			LEFT JOIN {$CFG->prefix}monit_school_pupil_card m ON m.userid = u.id
               			WHERE m.classid=$gid AND u.deleted = 0 AND u.confirmed = 1";

	    $students = get_records_sql($studentsql);

		$classdisciplines = get_records_sql ("SELECT * FROM {$CFG->prefix}monit_school_class_discipline
									WHERE classid=$gid and schoolsubgroupid <> 0
									ORDER BY name");
		if ($classdisciplines)	{
			$num = 0;
			foreach ($classdisciplines as $classdiscipline) {
				$strpupils  = '';
				$strdiscipline = $classdiscipline->name;

				
	    		if ($students)   {
	    			foreach ($students as $student)  {
	    				if (record_exists('monit_school_subgroup_pupil', 'classdisciplineid', $classdiscipline->id, 'userid', $student->id, 'schoolid', $sid))	{
        					$fullname = fullname($student);
                			$strpupils .= '* ' . $fullname . '<br>';
                		}
					}		
	    		}

				if ($edit_capability || $edit_capability_class)	{
					$title = get_string('setinsubgroups','block_mou_school');
					$strlinkupdate = "<a title=\"$title\" href=\"setinsubgroups.php?mode=edit&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;&cdid={$classdiscipline->id}\">";
					$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";
/*
					$title = get_string('editdiscipline','block_mou_school');
					$strlinkupdate = "<a title=\"$title\" href=\"editdiscipline.php?mode=edit&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid\">";
					$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";
					$title = get_string('deletediscipline','block_mou_school');
				    $strlinkupdate .= "<a title=\"$title\" href=\"deldiscipline.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid\">";
					$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
*/					
				}
					// $strgroup = "<strong><a href=$CFG->wwwroot/blocks/school/curr_courses.php?fid=$fid&amp;sid=$sid&amp;cid={$curr->id}>$curr->name</a></strong>";
				else	{
					$strlinkupdate = '-';
				}
				$num++;

				$table->data[] = array ($num, $strdiscipline, $strpupils, $strlinkupdate);
			}
		}

    return $table;
}


function table_pupilssubgroup($rid, $sid, $yid, $gid)
{
		global $CFG, $context;
		
       	$table->head  = array (	get_string("ordernumber","block_mou_school"),
       							get_string('classpupils', 'block_mou_school'),
  								get_string('subgroups', 'block_mou_school'));
  								//get_string('pol', 'block_mou_school'), 
						  		//get_string('birthday', 'block_mou_school'));
								// get_string("action", "block_mou_school"));
								
								// 
  		$table->align = array ("center", "left", "left", "center");
  		$table->size = array ('5%', '25%', '30%', '5%');
        $table->class = 'moutable';
        
		$studentsql = "SELECT u.id, u.firstname, u.lastname
                      	FROM {$CFG->prefix}user u
               			LEFT JOIN {$CFG->prefix}monit_school_pupil_card m ON m.userid = u.id
               			WHERE m.classid=$gid AND u.deleted = 0 AND u.confirmed = 1";

	    $students = get_records_sql($studentsql);

        if(!empty($students)) {
        	$num = 0;
        	foreach ($students as $student) {
        		$fullname = fullname($student);
                $strsubgroup = '';

                if ($subgroups = get_records_select('monit_school_subgroup_pupil', "userid={$student->id} and schoolid=$sid")) {
					foreach ($subgroups as $subgroup)	{
						$sb = get_record('monit_school_class_discipline', 'id', $subgroup->classdisciplineid);
	                    $strsubgroup .= '* ' . $sb->name . '</br>';
 					} 
 				}	

				if ($pol = get_record('monit_school_pupil_card', 'userid', $student->id, 'yearid', $yid)){
					$strsex = $pol->pol;
					if ($strsex == '')	{
						$strsex = '-';
					}
					$strbirthday = $pol->birthday;
					if ($strbirthday == '0000-00-00')	{
						$strbirthday = '-';
					}
				} else {
					$strsex = '-';
					$strbirthday = '-';
				}
				$num++;

                $table->data[] = array ($num, $fullname, $strsubgroup); // $strsex, convert_date($strbirthday, 'en', 'ru'));
        	}

        }

    return $table;
}

?>