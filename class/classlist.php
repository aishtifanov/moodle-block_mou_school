<?php // $Id: classlist.php,v 1.20 2010/09/27 10:58:51 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
	require_once $CFG->dirroot.'/grade/export/lib.php';
    require_once('../lib_school.php');
	require_once('../authbase.inc.php');
	//require_once 'grade_export_ods.php';

	$gid = optional_param('gid', 0, PARAM_INT);   // Class id

	$edit_capability = has_capability('block/mou_school:editclasslist', $context);

    switch ($action)  {
    	case 'excel':
			    	$table = table_classlist ($yid, $rid, $sid);
			    	// print_r($table);
			        print_table_to_excel($table, 1);
			        exit();
		case 'ods':
					$table = table_classlist_ods ($yid, $rid, $sid);
			    	// print_r($table);
			        // print_table_to_ods($table, 1);
			        exit();
	}

    $currenttab = 'classlist';
    include('tabsclasses.php');

	if (has_capability('block/mou_school:viewclasslist', $context))	{
	    $table = table_classlist ($yid, $rid, $sid);
		print_color_table($table);
		echo '<table align="center" border=0><tr><td>';

		if ($edit_capability)	{
			$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'mode' => 'new');
		    print_single_button("addclass.php", $options, get_string('addclass','block_mou_school'));
			echo '</td><td>';
		}

		$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'sesskey' => $USER->sesskey, 'action' => 'excel');
	    print_single_button("classlist.php", $options, get_string('downloadexcel_school', 'block_mou_ege'));
	    echo '</td><td>';

		$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'sesskey' => $USER->sesskey, 'action' => 'ods');
	    print_single_button("classlist.php", $options, get_string('downloadods_school', 'block_mou_ege'));
		echo '</td></tr></table>';
	} else {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}

    print_footer();




function table_classlist ($yid, $rid, $sid)
{
	global $CFG, $rayon,  $edit_capability;

	$context_rayon = get_context_instance(CONTEXT_RAYON, $rid);
	$edit_capability_rayon = has_capability('block/mou_school:editclasslist', $context_rayon);
	
	$table->head  = array (get_string('class','block_mou_school'), get_string("numofstudents","block_mou_school"),
						   get_string('classteacher','block_mou_school'), get_string("profile","block_mou_school"),
						   get_string("action","block_mou_school"));
	$table->align = array ('center', 'center', 'left', 'left', 'center');
    $table->size = array ('7%', '7%', '20%', '20%', '10%');
	$table->columnwidth = array (7, 7, 20, 20, 9);
    // $table->datatype = array ('char', 'char');
    $table->class = 'moutable';
   	$table->width = '80%';
    // $table->size = array ('10%', '10%');
    $table->titles = array();
    $table->titles[] = get_string('classes', 'block_mou_school');
    $table->titlesrows = array(30);
    $table->worksheetname = 'classes';
	$table->downloadfilename = 'classes_'.$sid;


	$classes = get_records_sql ("SELECT * FROM {$CFG->prefix}monit_school_class
									  WHERE schoolid=$sid AND yearid=$yid
									  ORDER BY parallelnum, name");
	if ($classes)	{

		foreach ($classes as $class) {
			
			$context_class = get_context_instance(CONTEXT_CLASS, $class->id);
			$edit_capability_class = has_capability('block/mou_school:editclasslist', $context_class);
			
			if ($edit_capability_class)	{
				$title = get_string('pupils','block_mou_ege');
				$strclass = "<strong><a title=\"$title\" href=\"classpupils.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid={$class->id}\">$class->name</a></strong>";
			}	else {
				$strclass = '<strong>'.$class->name.'</strong>';
			}

			$strprofile = $strteacher = $strlinkupdate = '-';

            if (isset($class->parallelnum) && !empty($class->parallelnum)) 	{
            	$strsql = "SELECT DISTINCT profileid
							FROM {$CFG->prefix}monit_school_curriculum
							WHERE schoolid=$sid and classid = {$class->id} and parallelnum = {$class->parallelnum}
							ORDER by profileid";
				// echo $strsql; echo '<hr>';			
            	$curriculums = get_records_sql ($strsql);
				// print_r($curriculums); echo '<hr>';
				if ($curriculums)	{
					$numcurricul = count($curriculums);
					$strzvezda = '';
					if ($numcurricul > 1) $strzvezda = '* ';
					 
					$strprofile = '';
					foreach ($curriculums as $curriculum) {
						$profile = get_record ('monit_school_profiles_curriculum', 'id', $curriculum->profileid);
						$strprofile .= $strzvezda . "<a href = \"../curriculum/setdiscipline.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid={$curriculum->profileid}\">" . $profile->name . '</a><br>';
					}
				}
			}

            if (isset($class->teacherid) && !empty($class->teacherid)) {
            	$teacher = get_record_sql ("SELECT id, lastname, firstname FROM {$CFG->prefix}user
									  		WHERE id={$class->teacherid} ");
				$strteacher	= fullname ($teacher);
            }
		
			if ($edit_capability)	{
				$title = get_string('editclass','block_mou_school');
				$strlinkupdate = "<a title=\"$title\" href=\"addclass.php?mode=edit&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid={$class->id}\">";
				$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";

				$title = get_string('assignroles','role');
			    $strlinkupdate .= "<a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/mou_school/roles/assign.php?contextid={$context_class->id}\">";
				$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/roles.gif\" alt=\"$title\" /></a>&nbsp;";

	
				$title = get_string('deleteclass','block_mou_school');
			    $strlinkupdate .= "<a title=\"$title\" href=\"delclass.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid={$class->id}\">";
				$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";

				if ($edit_capability_rayon)	{
					$title = get_string('clearclass','block_mou_school');
					$strlinkupdate .= "<a title=\"$title\" href=\"delclass.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid={$class->id}&amp;action=clear\">";
					// $strlinkupdate .=  "<img src=\"{$CFG->wwwroot}/blocks/mou_ege/i/goom.gif\" alt=\"$title\" /></a>&nbsp;";
					$strlinkupdate .= "<img src=\"{$CFG->wwwroot}/blocks/mou_school/i/btn_move.png\" alt=\"$title\" /></a>&nbsp;";					
			    }
			} else	{
				$strlinkupdate = '-';
			}

			// $strgroup = "<strong><a href=$CFG->wwwroot/blocks/school/curr_courses.php?fid=$fid&amp;sid=$sid&amp;cid={$curr->id}>$curr->name</a></strong>";

			$countpupils = count_records('monit_school_pupil_card', 'classid',  $class->id, 'deleted', 0);
			$table->data[] = array ($strclass, $countpupils, $strteacher, $strprofile, $strlinkupdate);
		}

    }

    return $table;
}


function table_classlist_ods ($yid, $rid, $sid)
{
global $CFG;
        require_once($CFG->dirroot.'/lib/odslib.class.php');

       // $export_tracking = $this->track_exports();

        $strgrades = get_string('grades');

    /// Calculate file name
        $downloadfilename = 1;
    /// Creating a workbook
        $workbook = new MoodleODSWorkbook("-");
    /// Sending HTTP headers
        $workbook->send($downloadfilename);
    /// Adding the worksheet
        $myxls =& $workbook->add_worksheet($strgrades);
        
        $myxls->write_string(0,0,get_string('class','block_mou_school'));
        $myxls->write_string(0,1,get_string("numofstudents","block_mou_school"));
        $myxls->write_string(0,2,get_string('classteacher','block_mou_school'));
        $myxls->write_string(0,3,get_string("profile","block_mou_school"));
       // $myxls->write_string(0,4, get_string("action","block_mou_school"));
        //$myxls->write_string(0,5,get_string("action","block_mou_school"));
        $pos=4;
        
 	$classes = get_records_sql ("SELECT * FROM {$CFG->prefix}monit_school_class
									  WHERE schoolid=$sid AND yearid=$yid
									  ORDER BY parallelnum, name");
	if ($classes)	{
	$i=0;
		foreach ($classes as $class) {
			$i++;
			$strprofile = $strteacher = '-';

            if (isset($class->parallelnum) && !empty($class->parallelnum)) 	{
            	$curriculums = get_records_sql ("SELECT DISTINCT profileid
            								FROM {$CFG->prefix}monit_school_curriculum
											WHERE schoolid=$sid and classid = {$class->id} and parallelnum = {$class->parallelnum}
											ORDER by profileid");
				// print_r($curriculums); echo '<hr>';
				if ($curriculums)	{
					$strprofile = '';
					foreach ($curriculums as $curriculum) {
						$profile = get_record ('monit_school_profiles_curriculum', 'id', $curriculum->profileid);
						$strprofile .= $profile->name;
					}
				}
			}

            if (isset($class->teacherid) && !empty($class->teacherid)) {
            	$teacher = get_record_sql ("SELECT id, lastname, firstname FROM {$CFG->prefix}user
									  		WHERE id={$class->teacherid} ");
				$strteacher	= fullname ($teacher);
            }

			$countpupils = count_records('monit_school_pupil_card', 'classid',  $class->id, 'deleted', 0);
            $myxls->write_string($i,0,$class->name);
            $myxls->write_string($i,1,$countpupils);
            $myxls->write_string($i,2,$strteacher);
            $myxls->write_string($i,3,$strprofile);

		}

    }       

		$workbook->close();

        exit;
}	

/*
function table_classlist_ods ($yid, $rid, $sid)
{
	global $CFG, $admin_is, $region_operator_is, $rayon_operator_is, $school_operator_is, $rayon;


	$table->head  = array (get_string('class','block_mou_school'), get_string("numofstudents","block_mou_school"),
						   get_string('classteacher','block_mou_school'), get_string("profile","block_mou_school"),
						   get_string("action","block_mou_school"));
	$table->align = array ('center', 'center', 'left', 'left', 'center');
    $table->size = array ('7%', '7%', '20%', '20%', '7%');
	$table->columnwidth = array (7, 7, 20, 20, 9);
    // $table->datatype = array ('char', 'char');
    $table->class = 'moutable';
   	$table->width = '75%';
    // $table->size = array ('10%', '10%');
    $table->titles = array();
    $table->titles[] = get_string('classes', 'block_mou_school');
    $table->titlesrows = array(30);
    $table->worksheetname = 'classes';
	$table->downloadfilename = 'classes_'.$sid;


	$classes = get_records_sql ("SELECT * FROM {$CFG->prefix}monit_school_class
									  WHERE schoolid=$sid AND yearid=$yid
									  ORDER BY parallelnum, name");
	if ($classes)	{

		foreach ($classes as $class) {

			$strprofile = $strteacher = '-';

            if (isset($class->parallelnum) && !empty($class->parallelnum)) 	{
            	$curriculums = get_records_sql ("SELECT DISTINCT profileid
            								FROM {$CFG->prefix}monit_school_curriculum
											WHERE schoolid=$sid and classid = {$class->id} and parallelnum = {$class->parallelnum}
											ORDER by profileid");
				// print_r($curriculums); echo '<hr>';
				if ($curriculums)	{
					$strprofile = '';
					foreach ($curriculums as $curriculum) {
						$profile = get_record ('monit_school_profiles_curriculum', 'id', $curriculum->profileid);
						$strprofile .= "* <a href = \"..\curriculum\setdiscipline.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid={$curriculum->profileid}\">" . $profile->name . '</a><br>';
					}
				}
			}

            if (isset($class->teacherid) && !empty($class->teacherid)) {
            	$teacher = get_record_sql ("SELECT id, lastname, firstname FROM {$CFG->prefix}user
									  		WHERE id={$class->teacherid} ");
				$strteacher	= fullname ($teacher);
            }

			$title = get_string('editclass','block_mou_school');
			$strlinkupdate = "<a title=\"$title\" href=\"addclass.php?mode=edit&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid={$class->id}\">";
			$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";


			$title = get_string('pupils','block_mou_ege');
			$countpupils = count_records('monit_school_pupil_card', 'classid',  $class->id, 'deleted', 0);
			$table->data[] = array ("<strong><a title=\"$title\" href=\"classpupils.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid={$class->id}\">$class->name</a></strong>",
									$countpupils, $strteacher, $strprofile, $strlinkupdate);
		}

    }

    return $table;
}
*/
?>


