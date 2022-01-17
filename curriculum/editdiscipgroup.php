<?PHP // $Id: editdiscipgroup.php,v 1.2 2010/08/23 08:48:04 Shtifanov Exp $

    require_once('../../../config.php');
    require_once('../../monitoring/lib.php');
    require_once('../lib_school.php');

    $rid = required_param('rid', PARAM_INT);
	$sid = required_param('sid', PARAM_INT);
	$yid = required_param('yid', PARAM_INT);
    $dgid = required_param('dgid', PARAM_INT);            // Discipline group id

	$context = get_context_instance(CONTEXT_SCHOOL, $sid);
	if (!has_capability('block/mou_school:editdiscipline', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

    $disgroup = get_record('monit_school_discipline_group', 'id', $dgid);

   	$indexlink = $CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid";
	$breadcrumbs = "<a href=\"$indexlink\">".get_string('title','block_mou_school').'</a>';
	$strtitle = get_string('disciplinegroup','block_mou_school');
    $redirlink = "{$CFG->wwwroot}/blocks/mou_school/curriculum/disciplinegroups.php?rid=$rid&amp;yid=$yid&amp;sid=$sid";
	$breadcrumbs .= "-> <a href=\"{$redirlink}\">$strtitle</a>";
   	$strtitle = get_string('editdiscipgroup','block_mou_school', $disgroup->name);
	$breadcrumbs .= "-> $strtitle";
    print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

   		if ($frm = data_submitted())   {
			if (!empty($frm->add) and !empty($frm->addselect) and confirm_sesskey()) {
				foreach ($frm->addselect as $adddisip) {
				     if (record_exists('monit_school_discipline', 'id', $adddisip))	{
				      	set_field('monit_school_discipline', 'dgroupid', $dgid, 'id', $adddisip); //, 'schoolid', $sid);

				    } else {
 						notify('Error in adding discipline group!');
 					}
                  //redirect("$CFG->wwwroot/blocks/mou_school/curriculum/editteachdiscip.php?mode=2&amp;sid=$sid&amp;did=$did&amp;rid=$rid");
  	            }
			} else if (!empty($frm->remove) and !empty($frm->removeselect) and confirm_sesskey()) {

				foreach ($frm->removeselect as $removedisc) {
				      	set_field('monit_school_discipline', 'dgroupid', 0, 'id', $removedisc); //, 'schoolid', $sid);
					// add_to_log(1, 'dean', 'curator deleted', '/blocks/dean/gruppa/curatorsgroups.php', $USER->lastname.' '.$USER->firstname);
				}
			}
		}

   /*
	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
    listbox_discipline_school("editteachdiscip.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;did=", $sid, $yid, $did);
  	echo '</table>';
   */

 	if ($sid != 0 && $dgid !=0)  {
 	   $discgroupsmenu   = array();
 	   $idsdiscipline = array();
 	   if ($discgroups = get_records_sql("SELECT id, name, shortname FROM {$CFG->prefix}monit_school_discipline
               							  WHERE schoolid = $sid AND dgroupid = $dgid
               							  ORDER by name"))  {

	 		foreach ($discgroups as $dg)	{
	 			$discgroupsmenu[$dg->id] = $dg->name;
	 			$idsdiscipline[] = $dg->id;
	 		}
	   }

 	   $schooldisciplinemenu   = array();
 	   if ($schooldiscs = get_records_sql("SELECT id, name, shortname FROM {$CFG->prefix}monit_school_discipline
               							  WHERE schoolid = $sid
               							  ORDER by name"))  {

	 		foreach ($schooldiscs as $sd)	{
	 			$schooldisciplinemenu[$sd->id] = $sd->name;
	 		}
	   }


        print_heading($strtitle, "center", 3);
	    print_simple_box_start("center", '50%');

	    $sesskey = !empty($USER->id) ? $USER->sesskey : '';
?>

<form name="formpoint" id="formpoint" method="post" action="editdiscipgroup.php">
<input type="hidden" name="rid" value="<?php echo $rid ?>" />
<input type="hidden" name="yid" value="<?php echo $yid ?>" />
<input type="hidden" name="sid" value="<?php echo $sid ?>" />
<input type="hidden" name="dgid" value="<?php echo $dgid ?>" />
<input type="hidden" name="sesskey" value="<?php echo $sesskey ?>" />
<table align="center" border="0" cellpadding="5" cellspacing="0">
    <tr>
      <td valign="top"> <?php echo get_string('groupdiscipline', 'block_mou_school');  ?>  </td>
      <td></td>
      <td valign="top"> <?php echo get_string('discipline', 'block_mou_school');?> </td>
    </tr>
    <tr>
      <td valign="top">
          <select name="removeselect[]" size="20" id="removeselect"  multiple
                  onFocus="document.formpoint.add.disabled=true;
                           document.formpoint.remove.disabled=false;
                           document.formpoint.addselect.selectedIndex=-1;" />
          <?php
          if (!empty($discgroupsmenu))	{
              foreach ($discgroupsmenu as $key => $pm) {
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
          if (!empty($schooldisciplinemenu))	{
              foreach ($schooldisciplinemenu as $key => $sm) {
              	if (!in_array($key, $idsdiscipline))	{
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