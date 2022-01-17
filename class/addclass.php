<?PHP // $Id: addclass.php,v 1.17 2011/10/21 07:10:11 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_att/lib_att.php');
    require_once('../lib_school.php');

    $mode = required_param('mode');    // new, add, edit, update
    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = required_param('sid', PARAM_INT);       // School id
    $yid = required_param('yid', PARAM_INT);       // Year id
    $gid = optional_param('gid', 0, PARAM_INT);      // Class id

	$context = get_context_instance(CONTEXT_SCHOOL, $sid);
	if (!has_capability('block/mou_school:editclasslist', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	
    
	$strclasses = get_string('classes','block_mou_ege');
	$strclass = get_string('class','block_mou_ege');
	$strpupils = get_string('pupils', 'block_mou_ege');

    if ($mode === "new" || $mode === "add" ) 	{
    	$strtitle = get_string('addclass', 'block_mou_ege');	
    } else {
    	$strtitle = get_string('updateclass', 'block_mou_ege');	
    }

	$breadcrumbs = '<a href="'.$CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('title','block_mou_school').'</a>';
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/mou_school/class/classlist.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">$strclasses</a>";
	$breadcrumbs .= "-> $strtitle";
	print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

    /*
    print_heading('Страница в стадии разарботки.', 'center', 3);
	print_footer();
    exit();
    */

	$rec->yearid = $yid;
	$rec->schoolid = $sid;
	$rec->rayonid = $rid;
	$rec->curriculumid = 0;
	$rec->teacherid = 0;
	$rec->name = '';
	$rec->parallelnum = 0;
	$rec->description = '';

	$redirlink = $CFG->wwwroot."/blocks/mou_school/class/classlist.php?mode=3&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid";
	
	if ($mode === 'add')  {
		$rec->name = required_param('name', PARAM_TEXT);
		$rec->description = required_param('description');
		$rec->teacherid = required_param('teacherid', PARAM_INT);
		$rec->parallelnum = (int) $rec->name;

		if (find_form_class_errors($rec, $err) == 0) {
			$rec->timecreated = time();
			if ($classid = insert_record('monit_school_class', $rec))	{
				notice(get_string('classadded','block_mou_ege'), $redirlink);
				
				$role_sotrudnik = get_record('role', 'shortname', 'sotrudnik');
			    $ctx = get_context_instance(CONTEXT_SCHOOL, $rec->schoolid);
	     		if (!role_assign_mou($role_sotrudnik->id, $rec->teacherid, $ctx->id))	{
					notify("Not assigned SOTRUDNIK {$rec->teacherid}.");
			    }
				
				$role_class_teacher = get_record('role', 'shortname', 'classteacher');
				$ctx = get_context_instance(CONTEXT_CLASS, $classid);
	     		if (!role_assign_mou($role_class_teacher->id, $rec->teacherid, $ctx->id))	{
	    			notify("Not assigned CLASS TEACHER {$rec->teacherid}.");
			    }
			} else {
				error(get_string('errorinaddingclass','block_mou_ege'), $redirlink);
			}	
		}
		else $mode = "new";
	}
	else if ($mode === 'edit')	{
		if ($gid > 0) 	{
			$class = get_record('monit_school_class', 'id', $gid);
			$rec->id = $class->id;
			$rec->name = $class->name;
			$rec->description = $class->description;
			$rec->parallelnum = (int) $rec->name;
			$rec->teacherid = $class->teacherid;
		}
	}
	else if ($mode === 'update')	{
        $frm = data_submitted();
        // print_r($frm); echo '<hr>';
		$class = get_record('monit_school_class', 'id', $gid);
		
		$classprofiles = array();	
		foreach($frm as $fieldname => $value)	{
		    $mask = substr($fieldname, 0, 7);
		    if ($mask == 'profile')  {
				$ids = explode('_', $fieldname);
  				$classprofiles[$ids[1]]= $value;
  			}
  		}

		$strsql = "SELECT DISTINCT profileid
				FROM {$CFG->prefix}monit_school_curriculum
				WHERE schoolid=$sid and classid = $gid
				ORDER by profileid";
		// echo $strsql; echo '<hr>';			
		if ($curriculums = get_records_sql ($strsql))	{
			// print_r($curriculums); echo '<hr>';
			foreach ($curriculums as $curriculum) 	{
				if (isset ($classprofiles[$curriculum->profileid])) continue;
				delete_records_select("monit_school_curriculum", "schoolid=$sid AND classid = $gid AND profileid={$curriculum->profileid}");
			}
		} else {
			// print_r($classprofiles); echo '<hr>';
			foreach ($classprofiles as $profileid => $classprofile)	{
				$strsql = "parallelnum = $class->parallelnum AND schoolid = $sid AND profileid = $profileid"; 
				if($curriculums = get_records_select('monit_school_curriculum', $strsql)) 	{
					foreach ($curriculums as $curriculum)	{
                   		if (!record_exists_select('monit_school_curriculum', "parallelnum = {$class->parallelnum} AND schoolid = $sid AND profileid = $profileid AND componentid = {$curriculum->componentid} AND disciplineid = {$curriculum->disciplineid} AND classid = $gid")) 	{
			          		$newrec->parallelnum = $class->parallelnum;
			          		$newrec->yearid 	 = $yid;
			          		$newrec->schoolid 	 = $sid;
			          		$newrec->classid 	 = $gid;
			          		$newrec->componentid = $curriculum->componentid;
			          		$newrec->profileid 	 = $profileid;
		            		$newrec->disciplineid = $curriculum->disciplineid;
		            		$newrec->hours = $curriculum->hours;
                            // print_r($newrec); echo '<hr>';
					        if (!insert_record('monit_school_curriculum', $newrec))	{
								error(get_string('errorinaddingcurriculum','block_mou_school'), $redirlink);
						    }
        				}
        			}	
				}
			}		
		}		

		$rec->id = $frm->classid;
		$rec->name = $frm->name;
		$rec->description = $frm->description;
        $rec->teacherid = $frm->teacherid;
        $rec->parallelnum = (int) $rec->name;

		if (find_form_class_errors($rec, $err) == 0) {
			$rec->timeadded = time();
			$class = get_record('monit_school_class', 'id', $rec->id);
			if ($class->teacherid != $rec->teacherid) {
				
				$role_class_teacher = get_record('role', 'shortname', 'classteacher');
				$ctx = get_context_instance(CONTEXT_CLASS, $rec->id);
	     		if (!role_unassign_mou($role_class_teacher->id, $class->teacherid, $ctx->id))	{
	    			notify("Not unassigned CLASS TEACHER {$class->teacherid}.");
			    }
	     		if (!role_assign_mou($role_class_teacher->id, $rec->teacherid, $ctx->id))	{
	    			notify("Not assigned CLASS TEACHER {$rec->teacherid}.");
			    }
			    
   				$role_sotrudnik = get_record('role', 'shortname', 'sotrudnik');
			    $ctx = get_context_instance(CONTEXT_SCHOOL, $sid);
	     		if (!role_assign_mou($role_sotrudnik->id, $rec->teacherid, $ctx->id))	{
					notify("Not assigned SOTRUDNIK {$rec->teacherid}.");
			    }

			}
			if (update_record('monit_school_class', $rec))	{
				 notice(get_string('classupdate', 'block_mou_ege'), $redirlink);
			} else
				error(get_string('errorinupdatingclass','block_mou_ege'), $redirlink);
		}
	}

	$rayon = get_record('monit_rayon', 'id', $rid);
	$school = get_record('monit_school', 'id', $sid);

	print_heading($strtitle, "center", 3);

    print_simple_box_start("center", "70%");
	?>
		
	<form name="addform" method="post" action="<?php if ($mode === 'new') echo "addclass.php?mode=add&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid";
													else  echo "addclass.php?mode=update&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid";?>">
	<center>
	<table cellpadding="5">
	<tr valign="top">
	    <td align="right"><b><?php  print_string("rayon","block_monitoring") ?>:</b></td>
	    <td align="left"> <?php p($rayon->name) ?> </td>
	</tr>
	<tr valign="top">
	    <td align="right"><b><?php  print_string("school","block_monitoring") ?>:</b></td>
	    <td align="left"> <?php p($school->name) ?> </td>
	</tr>
	<tr valign="top">
	    <td align="right"><b><?php  print_string('class', 'block_mou_ege') ?>:</b></td>
	    <td align="left">
			<input type="text" id="name" name="name" size="70" value="<?php p($rec->name) ?>" />
			<?php if (isset($err["name"])) formerr($err["name"]); ?>
	    </td>
	</tr>
	
	<tr>
		<td align="right"><b><?php  print_string("classteacher","block_mou_school") ?>:</b></td>
	    <td align="left"><?php
	    unset($choices);


    $teachersql = "SELECT u.id,  u.firstname, u.lastname, email 
                  FROM {$CFG->prefix}user u
	              LEFT JOIN {$CFG->prefix}monit_att_staff t ON t.userid = u.id
 	              WHERE t.schoolid=$sid AND u.deleted = 0 AND u.confirmed = 1";
	$teachersql .= ' ORDER BY u.lastname';

	$teachers = get_records_sql($teachersql);


  /*  $classes = get_records_sql ("SELECT * FROM {$CFG->prefix}monit_school_class
								  WHERE schoolid=$sid AND yearid=$yid
								  ORDER BY name" );

	$teacher11 = get_record_sql ("SELECT id, lastname, firstname FROM {$CFG->prefix}user
								  		WHERE id={$class->teacherid} ");
			$strteacher	= fullname ($teacher11);
	if ($teacher11->id !=0 )  {
		//	$r =  get_record('monit_school_discipline_domain', 'id', $did);
	        $d = $strteacher;
    }                         */
	//$d = get_record('monit_school_teacher','teacherid',$gid);
	//$choices[] = $d;
	if(!empty($teachers)) {
        foreach ($teachers as $teacher) 	{
			$choices[$teacher->id] = fullname($teacher) . " ($teacher->email)";
		}
    }

   	 choose_from_menu ($choices, "teacherid", $rec->teacherid, ""); 
   
   	 echo '</td> <tr valign="top"><td align="right"><b>';
	 print_string('profiles', 'block_mou_school');
	 echo ':</b></td> <td align="left">';

	$strsql = "SELECT DISTINCT profileid
				FROM {$CFG->prefix}monit_school_curriculum
				WHERE schoolid=$sid and classid = $gid
				ORDER by profileid";
	// echo $strsql; echo '<hr>';			
	// print_r($curriculums); echo '<hr>';
	if ($curriculums = get_records_sql ($strsql))	{
		foreach ($curriculums as $curriculum) {
			$profile = get_record ('monit_school_profiles_curriculum', 'id', $curriculum->profileid);
			echo "<input type=checkbox checked size=1 name=profile_{$profile->id} value='+'>".' '. $profile->name . '<br>';
		}
	} else {
		$findme = ','.$class->parallelnum.',';
		$profiles = get_records_sql ("SELECT * FROM {$CFG->prefix}monit_school_profiles_curriculum
									  WHERE schoolid=$sid ORDER BY name");
		if ($profiles)	{
	        foreach ($profiles as $profile) {
	        	$pos = strpos($profile->profilenumlist, $findme);
				if ($pos !== false) {
					echo "<input type=checkbox size=1 name=profile_{$profile->id} value='-'>".' '. $profile->name . '<br>';
				}
			}
		}			
	}
	echo '</td></tr>';
   

	   ?>
	    
	<tr valign="top">
	    <td align="right"><b><?php  print_string("description") ?>:</b></td>
	    <td align="left">
			<input type="text" id="description" name="description" size="70" value="<?php p($rec->description) ?>" />
	    </td>
	</tr>
	</table>
	   <div align="center">
	     <input type="hidden" name="classid" value="<?php p($gid)?>">
	 	 <input type="submit" name="addclass1" value="<?php print_string('savechanges')?>">
	  </div>
	 </center>
	</form>
	
	<?php
	print_simple_box_end();

		print_simple_box_start_old('center','', 'white');
		notify('<b>'. get_string('notifyclassprofile','block_mou_school').'</b>', '');
		print_simple_box_end_old();

	print_footer();


/// FUNCTIONS ////////////////////
function find_form_class_errors(&$rec, &$err)
{

  $textlib = textlib_get_instance();
  $rec->name = $textlib->strtoupper($rec->name);

  $rec->name = translit_english_utf8($rec->name);

  $symbols = array (' ', '\"', "\'", "`", '-', '#', '*', '+', '_', '=');
  foreach ($symbols as $sym)	{
	  $rec->name = str_replace($sym, '', $rec->name);
  }

  if ($classexist = get_record('monit_school_class', 'schoolid', $rec->schoolid, 'yearid', $rec->yearid, 'name', $rec->name))	{
    	if (isset($rec->id)) {
    		if ($classexist->id != $rec->id)		{
			    $err["name"] = get_string('classalreadyexist', 'block_mou_ege');
			}
		} else {
		    $err["name"] = get_string('classalreadyexist', 'block_mou_ege');
		}
	}
    if (empty($rec->name))	{
	    $err["name"] = get_string("missingname");
	}

    return count($err);
}

?>
