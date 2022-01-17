<?php // $Id: studyyear_apraou.php,v 1.2 2011/08/01 08:38:16 shtifanov Exp $

	require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_school.php');

    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = required_param('sid', PARAM_INT);       // School id
	// $lastyid = optional_param('yid', 0, PARAM_INT);       // Year id
	
	$lastyid = 0;
	$yid = $lastyid;
	 
    $strtitle = get_string('studyyears', 'block_mou_school');

	$breadcrumbs = '<a href="'.$CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('title','block_mou_school').'</a>';
	$breadcrumbs .= "-> $strtitle";
    print_header_mou("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

    print_heading($strtitle);

	$redirlink = "studyyear.php?rid=$rid&amp;yid=$yid&amp;sid=$sid";
	
	$admin_is = isadmin();
	$region_operator_is = ismonitoperator('region');
	if (!$admin_is && !$region_operator_is)	 {
		error('Only admin access this function.', $redirlink);
	}	

	$strcurryear = current_edu_year();
	if ($year = get_record('monit_years', 'name', $strcurryear)) {
		notify('New year already created.');
		$yid = $year->id; 
	} else {
		$rec->name = $strcurryear;
		$rec->datestart = date("Y") . '-09-01'; 
		$rec->dateend = date("Y")+1 . '-09-01';
		if ($yid = insert_record('monit_years', $rec))	{
			notify("New year add: {$rec->name}", 'green', 'center');
		}
	}
	
	$lastyid = $yid-1;

	$strcurryear = current_edu_year();
	if (!$year = get_record('monit_years', 'name', $strcurryear)) {	
		error('Current study year not found.', $redirlink);
	}	

	if (!$lastyear = get_record('monit_years', 'id', $year->id - 1)) {
		error('Old year not found.', $redirlink);
	}


	$newschoolsids = get_list_old_new_id ('monit_school', $yid);
	// print_r($newschoolsids);
	update_monit_staff($newschoolsids, 'schoolid');
	// update_monit_accreditation($newschoolsids, 'schoolid');
	notify("Schools id updated.", 'green', 'left');
	
	$newudodsids = get_list_old_new_id ('monit_udod', $yid);
	update_monit_staff($newudodsids, 'udodid');
	// update_monit_accreditation($newudodsids, 'udodid');
	notify("UDODs id updated.", 'green', 'left');
	
	$newcollegesids = get_list_old_new_id ('monit_college', $yid);
	update_monit_staff($newcollegesids, 'collegeid');
	notify("Colleges id updated.", 'green', 'left');
	// update_monit_accreditation($newcollegesids, 'collegeid');
	
	// $newdousids = get_list_old_new_id ('monit_education', $yid);
	// update_monit_staff($newdousids, 'douid');
	// update_monit_accreditation($newdousids, 'douid');
	// notify("DOUs id updated.", 'green', 'left');

	notice("All update complete.", "studyyear.php?rid=$rid&amp;sid=$sid");
    print_footer();


function update_monit_staff($newedusids, $fieldname)
{
	global $db;
		
	foreach ($newedusids as $oldeduid => $neweduid)	{
			$strsql = "UPDATE mdl_monit_att_staff SET $fieldname = ". 
					   $neweduid . " WHERE $fieldname = " . $oldeduid;
			$db->Execute($strsql);
	}		
}	
			
function update_monit_accreditation($newedusids, $fieldname)
{			
	global $db;
	
	foreach ($newedusids as $oldeduid => $neweduid)		{			
			$strsql = "UPDATE mdl_monit_accreditation SET $fieldname = ". 
					   $neweduid . " WHERE $fieldname = " . $oldeduid;		 		
			$db->Execute($strsql);
	}
}
	

function rename_folder_schools($newschoolsids)	{
	
	global $CFG;	

	foreach ($newschoolsids as $oldid => $newid)	{
		$oldbasedir = $CFG->dataroot . '/1/schools/' . $oldid;
		$newbasedir = $CFG->dataroot . '/1/schools/' . $newid;
		
  		if ($files = get_directory_list($oldbasedir)) 	{
               foreach ($files as $key => $file) {
                    $icon = mimeinfo('icon', $file);
                    $ffurl = "$CFG->wwwroot/file.php/1/schools/$newid/$file";
                    echo '<img align="middle" src="'.$CFG->pixpath.'/f/'.$icon.'" class="icon" alt="'.$icon.'" />'.
		                       '<a href="'.$ffurl.'" >'.$file.'</a><br />';
                    if (!file_exists($newbasedir)) {
			            if (mkdir($newbasedir, $CFG->directorypermissions)) {
							rename($oldbasedir.'/'.$file, $newbasedir . '/' . $file);
							echo $oldbasedir.'/'.$file . '====>' . $newbasedir . '/' . $file . '<br/>';
								// exit();	
		                }	else 	{
	             	        echo '<div class="notifyproblem" align="center">ERROR: Could not find or create a directory ('. 
                    		     $newbasedir .')</div>'."<br />\n";
		                }
		       		} else {
							rename($oldbasedir.'/'.$file, $newbasedir . '/' . $file);
							echo $oldbasedir.'/'.$file . '====>' . $newbasedir . '/' . $file . '<br/>';
		       		}
				}
		}
	}			
}		                   
		

?>


