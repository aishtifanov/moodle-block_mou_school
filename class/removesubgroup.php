<?PHP // $Id: removesubgroup.php,v 1.6 2012/02/21 06:34:41 shtifanov Exp $


/* 	ÓÑÒÀÐÅÂØÈÉ ÑÊÐÈÏÒ
    require_once('../../../config.php');
    $rid = required_param('rid', PARAM_INT);
    $mode = required_param('mode', PARAM_ALPHA);
    $fid = 1;
	$sid = required_param('sid', PARAM_INT);;
	$cid = optional_param('cid',0, PARAM_INT);
    $gid = required_param('gid', PARAM_INT);
	$did = optional_param('did',0, PARAM_INT);
	$sub = optional_param('sub',0, PARAM_INT);

    $action   = optional_param('action', '');
	$admin_is = isadmin();
	$creator_is = iscreator();

    if (!$admin_is && !$creator_is ) {
        error(get_string('adminaccess', 'block_mou_school'), '../index.php');
    }


    if ($mode === "1" || $mode === "add" )
    $straddperiod = get_string('addteachdiscip','block_mou_school');
	else $straddperiod = get_string('editteachdiscip','block_mou_school');

	$strcurriculums = get_string('curriculums','block_mou_school');
	$strclasses = get_string('editteachdiscip','block_mou_school');
	$breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/mou_school/index.php">'.get_string('title','block_mou_school').'</a>';
	$breadcrumbs .= '-> <a href="'.$CFG->wwwroot.'/blocks/mou_school/curriculum/curriculum.php?rid=$rid&amp;yid=$yid&amp;sid=$sid">'.get_string('school','block_mou_school').'</a>';
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/mou_school/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid&amp;rid=$rid\">$strcurriculums</a>";
	$breadcrumbs .= "-> $strclasses";

    print_header("$SITE->shortname: $strclasses", $SITE->fullname, $breadcrumbs);

   		if ($frm = data_submitted())   {
			if (!empty($frm->add) and !empty($frm->addselect) and confirm_sesskey()) {
				foreach ($frm->addselect as $addteacher) {
				      if (record_exists('monit_school_teacher', 'teacherid', $addteacher, 'disciplineid', $gid))	{
                          notify('Error in adding teacher!','block_mou_school');
				    } else {
				        $rec->teacherid = $addteacher;
						$rec->disciplineid = $gid;
						$rec->schoolid = $sid;
				    	if (insert_record('monit_school_teacher', $rec)){
				    	} else{
				    		error('Error in adding teacher!');
				    	}
				    	}
                  //redirect("$CFG->wwwroot/blocks/mou_school/curriculum/editteachdiscip.php?mode=2&amp;sid=$sid&amp;did=$did&amp;rid=$rid");
  	            }
			} else if (!empty($frm->remove) and !empty($frm->removeselect) and confirm_sesskey()) {

				foreach ($frm->removeselect as $removeschool) {
					delete_records('monit_school_teacher', 'schoolid', $sid , 'teacherid', $removeschool, 'disciplineid', $gid);
					// add_to_log(1, 'dean', 'curator deleted', '/blocks/dean/gruppa/curatorsgroups.php', $USER->lastname.' '.$USER->firstname);
				}
			} else if (!empty($frm->showall)) {
				unset($frm->searchtext);
				$frm->previoussearch = 0;
			}

		}

	$rec->teacher = "";
	$rec->name = "";
	$rec->shortname = "";
	print_heading($straddperiod, "center", 3);

    $dname = get_record('monit_school_discipline','id',$cid,'schoolid',$sid,'disciplinedomainid',$gid);
	print_heading($dname->name, "center", 2);

           /*
		 $disiplines = get_records_sql("SELECT  d.id, d.name as dname,  s.name as sname,  p.userid
											FROM {$CFG->prefix}monit_school_discipline d
											LEFT JOIN {$CFG->prefix}monit_school_subgroup s
											LEFT JOIN {$CFG->prefix}monit_school_class_subgroup c ON s.id = c.schoolsubgroupid
											LEFT JOIN {$CFG->prefix}monit_school_subgroup_pupil p ON c.id = p.subgroupid ON d.id = s.disciplineid
											WHERE c.classid = $gid");
             */
     if ($admin_is  || $region_operator_is) {

	    if ($disciplines = get_records_sql("SELECT  * FROM {$CFG->prefix}monit_school_class_subgroup
										WHERE schoolid=$sid and classid = $gid")){
			$discipmenu = array();
		 	$discipmenu[0] = get_string('discipselect','block_mou_school');

			 foreach ($disciplines as $discipline) {
		     $discipmenu[$discipline->id] = $discipline->name;
			}

		}
		print_r($discipline->id);
       //  print_r($disciplines);
		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		echo '<tr><td>'.get_string('disciplines','block_mou_school').':</td><td>';
	 	popup_form ("removesubgroup.php?mode=1&amp;rid=$rid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;sub=0&amp;did=",$discipmenu, 'disciplines', $did, "", "", "", false);
		echo '</td></tr>';

     	if ($did != 0)  {
	 	     if ($subgroups = get_records_sql("SELECT id, schoolid, disciplineid, name, shortname FROM {$CFG->prefix}monit_school_subgroup WHERE schoolid=$sid and disciplineid = $did")){
			       $submenu = array();
				   $submenu[0] = get_string('discipselect','block_mou_school');

			       foreach ($subgroups as $subgr) {
					     $submenu[$subgr->id] = $subgr->name;
						}
                  // print_r($subgroups);
					echo '<tr><td>'.get_string('subgroups','block_mou_school').':</td><td>';
					popup_form ("removesubgroup.php?mode=1&amp;rid=$rid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;did=$did&amp;sub=",$submenu, 'submenu', $sub, "", "", "", false);
					echo '</td></tr>';
					echo '</table>';

			 } else {
				echo '</table>';
			 	notify('ksdfgvdhsfljkjdfgdsj');
			 }
		} else {
			echo '</table>';
		}
		// print_r($sub);
     }

     // print_r($submenuselect);
     if ($sub!=0 && $did!=0){

		$studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.maildisplay,
									  u.city, u.country, u.lastlogin as disciplines , u.picture, u.lang,
									  u.timezone as pswtxt, u.timemodified as timeappeal, u.lastaccess, m.classid
		                            FROM {$CFG->prefix}user u
		                       LEFT JOIN {$CFG->prefix}monit_school_pupil_card m ON m.userid = u.id
		                       WHERE classid=$gid AND u.deleted = 0 AND u.confirmed = 1";

			 $students = get_records_sql($studentsql);

		     $sbgroups = get_records_sql("SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.maildisplay,
									  u.city, u.country, u.lastlogin as disciplines , u.picture, u.lang,
									  u.timezone as pswtxt, u.timemodified as timeappeal, u.lastaccess, m.subgroupid,c.schoolsubgroupid,
									  c.name
		                            FROM {$CFG->prefix}user u
		                       LEFT JOIN {$CFG->prefix}monit_school_subgroup_pupil m ON m.userid = u.id
		                       LEFT JOIN {$CFG->prefix}monit_school_class_subgroup c ON m.subgroupid = c.schoolsubgroupid
		                       LEFT JOIN {$CFG->prefix}monit_school_subgroup s ON c.schoolsubgroupid = s.id
		                       WHERE classid=$gid AND u.deleted = 0 AND u.confirmed = 1 AND s.disciplineid=$did AND m.subgroupid={$subgr->id}");

	        $idstudents   = array();
		    $substudentsmenu = array();
            //   print_r($sbgroups);
		 	if ($sbgroups)	{
		 		foreach ($sbgroups as $sbgroup)	{
		 			//$steachmenu [] = $allsteach->id;
		 			$substudentsmenu[$sbgroup->id] = fullname($sbgroup);
		 			$idstudents[] = $sbgroup->id;
		 		}
		 	}
               //  print_r($substudentsmenu);
			$schoolmenu = array();

		    if ($students)	{
		  		foreach ($students as $sstud) {
		  		//	$name = truncate_school_name($school->name);
					$schoolmenu[$sstud->id] = fullname($sstud);

				}
			}
		    print_simple_box_start("center");
			$sesskey = !empty($USER->id) ? $USER->sesskey : '';
?>
			<form name="formpoint" id="formpoint" method="post" action="<?php echo "editteachdiscip.php?mode=2&amp;sid=$sid&amp;gid=$gid"?>">
			<input type="hidden" name="rid" value="<?php echo $rid ?>" />
			<input type="hidden" name="yid" value="<?php echo $yid ?>" />
			<input type="hidden" name="sesskey" value="<?php echo $sesskey ?>" />
			<table align="center" border="0" cellpadding="5" cellspacing="0">
		    <tr>
		      <td valign="top"> <?php echo get_string('discipteachers', 'block_mou_school');  ?>  </td>
		      <td></td>
		      <td valign="top"> <?php echo get_string('schoolteachers', 'block_mou_school');?> </td>
		    </tr>
		    <tr>
		      <td valign="top">
		          <select name="removeselect[]" size="20" id="removeselect"  multiple
		                  onFocus="document.formpoint.add.disabled=true;
		                           document.formpoint.remove.disabled=false;
		                           document.formpoint.addselect.selectedIndex=-1;" />
<?php
	          if (!empty($substudentsmenu))	{
	              foreach ($substudentsmenu as $key => $pm) {
	                  echo "<option value=\"$key\">" . $pm . "</option>\n";
	              }
	          }
?>
	          </select></td>
	      <td valign="top">
	        <br />
	        <input name="add" type="submit" id="add" value="&larr;" />
	        <br />
	        <input name="remove" type="submit" id="remove" value="&rarr;" />
	        <br />
	      </td>
	      <td valign="top">
	          <select name="addselect[]" size="20" id="addselect"  multiple
	                  onFocus="document.formpoint.add.disabled=false;
	                           document.formpoint.remove.disabled=true;
	                           document.formpoint.removeselect.selectedIndex=-1;">
<?php
	          if (!empty($schoolmenu))	{
	              foreach ($schoolmenu as $key => $sm) {
	              	if (!in_array($key, $idstudents))	{
	                  echo "<option value=\"$key\">" . $sm . "</option>\n";
	                }
	              }
	          }
?>
	         </select>
		       </td>
		    </tr>
		  </table>
		</form>

<?php
print_simple_box_end();
	  	}

	print_footer();

/// FUNCTIONS ////////////////////
function find_form_curr_errors(&$rec, &$err, $mode='add') {

       if (empty($rec->name))	{
		    $err["name"] = get_string("missingname");
		}

    return count($err);
}
*/
?>