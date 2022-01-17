<?PHP // $Id: setinsubgroups.php,v 1.4 2010/09/02 06:56:51 Shtifanov Exp $

    require_once('../../../config.php');
    require_once('../../monitoring/lib.php');
    require_once('../lib_school.php');

    $rid = required_param('rid', PARAM_INT);
	$sid = required_param('sid', PARAM_INT);
	$yid = required_param('yid', PARAM_INT);
 	$gid = required_param('gid', PARAM_INT);   		// Class id
    $cdid = optional_param('cdid', 0, PARAM_INT);   // Class discipline id (subgroupid)

	$context = get_context_instance(CONTEXT_SCHOOL, $sid);
	$edit_capability = has_capability('block/mou_school:editclasslist', $context);

	$context_class = get_context_instance(CONTEXT_CLASS, $gid);
	$edit_capability_class = has_capability('block/mou_school:editclasslist', $context_class);
	
	if (!$edit_capability && !$edit_capability_class)	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

    $classdiscipline = get_record('monit_school_class_discipline', 'id', $cdid);

   	$indexlink = $CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid";
	$breadcrumbs = "<a href=\"$indexlink\">".get_string('title','block_mou_school').'</a>';
	$strtitle = get_string('subgroups', 'block_mou_school');
    $redirlink = "{$CFG->wwwroot}/blocks/mou_school/class/subgroups.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid";
	$breadcrumbs .= "-> <a href=\"{$redirlink}\">$strtitle</a>";
   	$strtitle = get_string('editsetinsubgroups','block_mou_school', $classdiscipline->name);
	$breadcrumbs .= "-> $strtitle";
    print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

	// Processing submitted data
   		if ($frm = data_submitted())   {
			if (!empty($frm->add) and !empty($frm->addselect) and confirm_sesskey()) {
				foreach ($frm->addselect as $addpupil) {
				      if (record_exists('monit_school_subgroup_pupil', 'userid', $addpupil, 'schoolid', $sid, 'classdisciplineid', $cdid))	{
                          notify('Error in adding pupil!');
				    } else {
						$rec->schoolid 	= $sid;
				        $rec->userid 	= $addpupil;
						$rec->classdisciplineid = $cdid;
				    	if (!insert_record('monit_school_subgroup_pupil', $rec)){
				    		error('Error in adding pupil!');
				    	}
				    }
                  //redirect("$CFG->wwwroot/blocks/mou_school/curriculum/editteachdiscip.php?mode=2&amp;sid=$sid&amp;did=$did&amp;rid=$rid");
  	            }
			} else if (!empty($frm->remove) and !empty($frm->removeselect) and confirm_sesskey()) {

				foreach ($frm->removeselect as $removepupil) {
					delete_records('monit_school_subgroup_pupil', 'schoolid', $sid , 'userid', $removepupil, 'classdisciplineid', $cdid);
					// add_to_log(1, 'dean', 'curator deleted', '/blocks/dean/gruppa/curatorsgroups.php', $USER->lastname.' '.$USER->firstname);
				}
			} else if (!empty($frm->showall)) {
				unset($frm->searchtext);
				$frm->previoussearch = 0;
			}

		}

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
    listbox_discipline_subgroup("setinsubgroups.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;cdid=", $sid, $yid, $gid, $cdid);
  	echo '</table>';
  	
	if ($gid != 0 && $cdid !=0)  {
       $studentsql = "SELECT u.id, u.firstname, u.lastname
                      FROM {$CFG->prefix}monit_school_subgroup_pupil t
    	              LEFT JOIN  {$CFG->prefix}user u ON t.userid = u.id
     				  WHERE t.schoolid=$sid and t.classdisciplineid=$cdid
  					  ORDER BY  u.lastname";
	   $dstudents = get_records_sql($studentsql);

  	    $studentsql = "SELECT u.id, u.firstname, u.lastname
                      	FROM {$CFG->prefix}user u
               			LEFT JOIN {$CFG->prefix}monit_school_pupil_card m ON m.userid = u.id
               			WHERE m.classid=$gid AND u.deleted = 0 AND u.confirmed = 1
				   		ORDER BY  u.lastname";

	    $cstudents = get_records_sql($studentsql);

 	    $idsstudents  = array();
	    $dstudentmenu = array();
	 	if ($dstudents)	{
	 		foreach ($dstudents as $dstud)	{
	 			$dstudentmenu[$dstud->id] = fullname($dstud);
	 			$idsstudents[] = $dstud->id;
	 		}
	 	}

		$schoolmenu = array();

	    if ($cstudents)	{
	  		foreach ($cstudents as $cstud) {
	  			$schoolmenu[$cstud->id] = fullname($cstud);
			}
		}
	    print_simple_box_start("center", '70%');
	    // print_heading($strtitle, "center", 3);
	    $sesskey = !empty($USER->id) ? $USER->sesskey : '';
?>

<form name="formpoint" id="formpoint" method="post" action="setinsubgroups.php">
<input type="hidden" name="rid" value="<?php echo $rid ?>" />
<input type="hidden" name="sid" value="<?php echo $sid ?>" />
<input type="hidden" name="yid" value="<?php echo $yid ?>" />
<input type="hidden" name="gid" value="<?php echo $gid ?>" />
<input type="hidden" name="cdid" value="<?php echo $cdid ?>" />
<input type="hidden" name="sesskey" value="<?php echo $sesskey ?>" />
<table align="center" border="0" cellpadding="5" cellspacing="0">
    <tr>
      <td valign="top"> <?php echo $strtitle;
      							//get_string('discipteachers', 'block_mou_school');
      					?>  </td>
      <td></td>
      <td valign="top"> <?php echo get_string('classpupils', 'block_mou_school');?> </td>
    </tr>
    <tr>
      <td valign="top">
          <select name="removeselect[]" size="20" id="removeselect"  multiple
                  onFocus="document.formpoint.add.disabled=true;
                           document.formpoint.remove.disabled=false;
                           document.formpoint.addselect.selectedIndex=-1;" />
          <?php
          if (!empty($dstudentmenu))	{
              foreach ($dstudentmenu as $key => $pm) {
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
              	if (!in_array($key, $idsstudents))	{
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

?>