<?PHP // $Id: addperiod.php,v 1.5 2010/08/23 08:48:06 Shtifanov Exp $

    require_once('../../../config.php');
    require_once('../../monitoring/lib.php');

    $mode = required_param('mode', PARAM_ALPHA);    // new, add, edit, update
    $rid = required_param('rid', PARAM_INT);
    $sid = required_param('sid', PARAM_INT);
    $yid = required_param('yid', PARAM_INT);
   	$tid = optional_param('tid', 0, PARAM_INT);			// Term id

	$context = get_context_instance(CONTEXT_SCHOOL, $sid);
	if (!has_capability('block/mou_school:edittypestudyperiod', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

    if ($mode === "new" || $mode === "add" ) {
    	$straddperiod = get_string('addperiod','block_mou_school');
    } else {
    	$straddperiod = get_string('updateperiod','block_mou_school');
    }

	$strtitle = get_string('studyperiod','block_mou_school');

	$breadcrumbs = '<a href="'.$CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('title','block_mou_school').'</a>';
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/mou_school/periods/studyperiod.php?yid=$yid&amp;sid=$sid&amp;rid=$rid\">$strtitle</a>";
	$breadcrumbs .= "-> $straddperiod";
    print_header("$SITE->shortname: $straddperiod", $SITE->fullname, $breadcrumbs);

	$rec->schoolid = $sid;
	$redirlink = "studyperiod.php?yid=$yid&amp;sid=$sid&amp;rid=$rid";

	if ($mode === 'add')  {
		$rec->name = required_param('name');
		$rec->datestart = required_param('datestart');
		$rec->dateend = required_param('dateend');

		if (find_form_curr_errors($rec, $err) == 0) {
		    $rec->datestart = convert_date($rec->datestart, 'ru', 'en');
		    $rec->dateend = convert_date($rec->dateend, 'ru', 'en');
			if (insert_record('monit_school_term', $rec))	{
				 // add_to_log(1, 'school', 'one curriculum added', $redirlink, $USER->lastname.' '.$USER->firstname);
				 notice(get_string('curriculumadded','block_school'), $redirlink);
			} else
				error(get_string('errorinaddingcurr','block_school'), $redirlink);
		}
		else $mode = "new";
	}
	else if ($mode === 'edit')	{
		if ($tid > 0) 	{
			$curr = get_record('monit_school_term', 'id', $tid);
			$rec->id = $curr->id;
			$rec->datestart = $curr->datestart;
			$rec->name = $curr->name;
			$rec->dateend = $curr->dateend;
		}
	}
	else if ($mode === 'update')	{
		$curr = get_record('monit_school_term', 'id', $tid);
		$rec->name = $curr->name;

		$rec->id = $tid;
		$rec->datestart = required_param('datestart');
		$rec->dateend = required_param('dateend');

        // print_r($rec);

		if (find_form_curr_errors($rec, $err) == 0) {
		    $rec->datestart = convert_date($rec->datestart, 'ru', 'en');
		    $rec->dateend = convert_date($rec->dateend, 'ru', 'en');
			if (update_record('monit_school_term', $rec))	{
				 // add_to_log(1, 'school', 'curriculum update', $redirlink, $USER->lastname.' '.$USER->firstname);
				 notice(get_string('termupdate','block_mou_school'), $redirlink);
			} else	{
				error(get_string('errorinupdatingcurr','block_mou_school'), $redirlink);
			}
		}
	}

	print_heading($straddperiod, "center", 3);

    print_simple_box_start("center", '50%');

	if ($mode === 'new') $newmode='add';
	else 				 $newmode='update';

?>

<form name="addform" method="post" action="addperiod.php">
<center>
<table cellpadding="5">
<tr valign="top">
    <td align="right"><b><?php  print_string('name') ?>:</b></td>
    <td align="left">	 <?php  p($rec->name) ?>  </td>
</tr>
<?php
	 $date_term = convert_date($rec->datestart, 'en', 'ru');
	 $fieldname = 'datestart';
	 echo '<tr valign="top"><td align="right"><b>';
     print_string('timestart', 'block_mou_school');
     echo ':</b></td> <td align="left">';
  	 echo '<input type="text"  name="'. $fieldname . '" size="10" value="' . $date_term . '" />';
	 if (isset($err[$fieldname])) formerr($err[$fieldname]);
     echo '</td> </tr>';

	 $date_term = convert_date($rec->dateend, 'en', 'ru');
	 $fieldname = 'dateend';
	 echo '<tr valign="top"><td align="right"><b>';
     print_string('timeend', 'block_mou_school');
     echo ':</b></td> <td align="left">';
  	 echo '<input type="text"  name="'. $fieldname . '" size="10" value="' . $date_term . '" />';
	 if (isset($err[$fieldname])) formerr($err[$fieldname]);
     echo '</td></tr></table>';

if (!isregionviewoperator() && !israyonviewoperator())  {  ?>
   <div align="center">
     <input type="hidden" name="mode" value="<?php echo $newmode ?>">
     <input type="hidden" name="yid" value="<?php echo $yid ?>">
     <input type="hidden" name="rid" value="<?php echo $rid ?>">
     <input type="hidden" name="sid" value="<?php echo $sid ?>">
     <input type="hidden" name="tid" value="<?php echo $tid ?>">
 	 <input type="submit" name="adddisc" value="<?php print_string('savechanges')?>">
  </div>
<?php  }  ?>
 </center>
</form>

<?php
    print_simple_box_end();

	print_footer();


/// FUNCTIONS ////////////////////
function find_form_curr_errors(&$rec, &$err)
{
    if (empty($rec->datestart))		{
	    $err["datestart"] = get_string("missingname");
	} else if (!is_date($rec->datestart)) {
  		$err['datestart'] = get_string('missingdate', 'block_mou_att');
  	}
    if (empty($rec->dateend))	{
	    $err["dateend"] = get_string("missingname");
	} else if (!is_date($rec->dateend)) {
  		$err['dateend'] = get_string('missingdate', 'block_mou_att');
  	}

    return count($err);
}

?>