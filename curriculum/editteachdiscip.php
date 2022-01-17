<?PHP // $Id: editteachdiscip.php,v 1.8 2011/10/20 12:29:28 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../../monitoring/lib.php');
    require_once('../lib_school.php');

    $rid = required_param('rid', PARAM_INT);
	$sid = required_param('sid', PARAM_INT);
	$yid = required_param('yid', PARAM_INT);
    $did = required_param('did', PARAM_INT);            // Discipline id

   	$context = get_context_instance(CONTEXT_SCHOOL, $sid);
	if (!has_capability('block/mou_school:editdiscipline', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	


    $discipline = get_record('monit_school_discipline', 'id', $did);

   	$indexlink = $CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid";
	$breadcrumbs = "<a href=\"$indexlink\">".get_string('title','block_mou_school').'</a>';
	$strtitle = get_string('discipteachers','block_mou_school');
    $redirlink = "{$CFG->wwwroot}/blocks/mou_school/curriculum/discipteachers.php?rid=$rid&amp;yid=$yid&amp;sid=$sid";
	$breadcrumbs .= "-> <a href=\"{$redirlink}\">$strtitle</a>";
   	$strtitle = get_string('editteachdiscip','block_mou_school', $discipline->name);
	$breadcrumbs .= "-> $strtitle";
    print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

   		if ($frm = data_submitted())   {
			if (!empty($frm->add) and !empty($frm->addselect) and confirm_sesskey()) {
				foreach ($frm->addselect as $addteacher) {
				      if (record_exists('monit_school_teacher', 'teacherid', $addteacher, 'disciplineid', $did))	{
                          notify('Error in adding teacher!','block_mou_school');
				    } else {
				        $rec->teacherid = $addteacher;
						$rec->disciplineid = $did;
						$rec->schoolid = $sid;
				    	if (insert_record('monit_school_teacher', $rec)){
				    	//	notify('Your record added');
				    	} else{
				    		error('Error in adding teacher!');
				    	}
				    }
                  //redirect("$CFG->wwwroot/blocks/mou_school/curriculum/editteachdiscip.php?mode=2&amp;sid=$sid&amp;did=$did&amp;rid=$rid");
  	            }
			} else if (!empty($frm->remove) and !empty($frm->removeselect) and confirm_sesskey()) {

				foreach ($frm->removeselect as $removeschool) {
					delete_records('monit_school_teacher', 'schoolid', $sid , 'teacherid', $removeschool, 'disciplineid', $did);
					// add_to_log(1, 'dean', 'curator deleted', '/blocks/dean/gruppa/curatorsgroups.php', $USER->lastname.' '.$USER->firstname);
				}
			} else if (!empty($frm->showall)) {
				unset($frm->searchtext);
				$frm->previoussearch = 0;
			}

		}

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
    listbox_discipline_school("editteachdiscip.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;did=", $sid, $yid, $did);
  	echo '</table>';

       $teachersql = "SELECT u.id, u.firstname, u.lastname, u.email
                        FROM {$CFG->prefix}monit_school_teacher t
    	            		LEFT JOIN  {$CFG->prefix}user u ON t.teacherid = u.id
    	            		WHERE t.schoolid=$sid and t.disciplineid=$did
				   			ORDER BY  u.lastname";
	   $dteachers = get_records_sql($teachersql);

	   $steachersql = "SELECT u.id, u.firstname, u.lastname, u.email 
	                  FROM {$CFG->prefix}user u
	  	              LEFT JOIN {$CFG->prefix}monit_att_staff t ON t.userid = u.id
	   	              WHERE t.schoolid=$sid AND u.deleted = 0 AND u.confirmed = 1";
	   $steachersql .= ' ORDER BY u.lastname';

       $steachers = get_records_sql($steachersql);
       
       $ssharedsql = "SELECT u.id, u.firstname, u.lastname, u.email
                        FROM mdl_user u  INNER JOIN mdl_monit_att_staff t ON t.userid = u.id
                        INNER JOIN mdl_monit_att_staffshared ss ON t.id = ss.staffid
                        WHERE ss.schoolid=$sid AND u.deleted = 0 AND u.confirmed = 1
                        ORDER BY u.lastname";
        if ($sshareds = get_records_sql($ssharedsql)) {
            // echo '<pre>'; print_r($sshareds); echo '</pre>'; 
            foreach ($sshareds as $sshared) {
                $steachers[$sshared->id] = $sshared;          
            }
        }                


 	if ($sid != 0 && $did !=0)  {
        $idsteachers   = array();
	    $dteachmenu = array();
	 	if ($dteachers)	{
	 		foreach ($dteachers as $dteach)	{
	 			//$steachmenu [] = $allsteach->id;
	 			$dteachmenu[$dteach->id] = fullname($dteach) . " ($dteach->email)";
	 			$idsteachers[] = $dteach->id;
	 		}
	 	}

		$schoolmenu = array();

	    if ($steachers)	{
	  		foreach ($steachers as $steach) {
	  		//	$name = truncate_school_name($school->name);
				$schoolmenu[$steach->id] = fullname($steach)  . " ($steach->email)";;

			}
		}
	    print_simple_box_start("center", '70%');
	    // print_heading($strtitle, "center", 3);
	    $sesskey = !empty($USER->id) ? $USER->sesskey : '';
?>

<form name="formpoint" id="formpoint" method="post" action="<?php echo "editteachdiscip.php?mode=2&amp;sid=$sid&amp;did=$did"?>">
<input type="hidden" name="rid" value="<?php echo $rid ?>" />
<input type="hidden" name="yid" value="<?php echo $yid ?>" />
<input type="hidden" name="sesskey" value="<?php echo $sesskey ?>" />
<table align="center" border="0" cellpadding="5" cellspacing="0">
    <tr>
      <td valign="top"> <?php echo $strtitle;
      							//get_string('discipteachers', 'block_mou_school');
      					?>  </td>
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
          if (!empty($dteachmenu))	{
              foreach ($dteachmenu as $key => $pm) {
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
              	if (!in_array($key, $idsteachers))	{
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

  ////////////
	print_footer();

?>