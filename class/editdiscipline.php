<?php // $Id: editdiscipline.php,v 1.5 2010/08/23 08:47:58 Shtifanov Exp $
/*

	ÓÑÒÀÐÅÂØÈÉ ÑÊÐÈÏÒ 
//
    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../../mou_att/lib_att.php');
	
    $rid = required_param('rid', PARAM_INT);    // Rayon id
    $sid = required_param('sid', PARAM_INT);    // School id
    $yid = optional_param('yid', 0, PARAM_INT); // Year id
    $gid = required_param('gid', PARAM_INT);   	// Class id
	$did = required_param('did', PARAM_INT); 	// DISCIPLINE ID    
    $uid = optional_param('uid', 0, PARAM_INT); // Teacher id
    $cdid = required_param('cdid', PARAM_INT);  // monit_school_class_discipline  
    
	if ($yid == 0)	{
    	$yid = get_current_edu_year_id();;
    }

    require_once('../authall.inc.php');

	$action   = optional_param('action', '');
    if ($action == 'excel') {
		$table = classdisciplines ($yid);
        print_table_to_excel($table, 1);

        exit();
	}

    $strtitle = get_string('classdisciplines','block_mou_school');
    $strclasses = get_string('classdisciplines','block_mou_school');
	
	
	$breadcrumbs = '<a href="'.$CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('title','block_mou_school').'</a>';
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/mou_school/class/classdisciplines.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid\">$strclasses</a>";
	$breadcrumbs .= "-> $strtitle";
    print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);
	
	if ($flds = data_submitted())  {
		 // print_r($flds);
		
		$redirlink = "{$CFG->wwwroot}/blocks/mou_school/class/classdisciplines.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid";
	
		$rec->schoolid = $sid;
		$rec->teacherid = $flds->teacherid;
		$rec->disciplineid = $did;
		$rec->classid = $gid;
		$rec->id = $cdid;
		if (update_record('monit_school_class_discipline', $rec))	{
				 add_to_log(1, 'school', 'curriculum update', $redirlink, $USER->lastname.' '.$USER->firstname);
				 notice(get_string('curriculumupdate','block_school'), $redirlink);
		} else{
				error(get_string('errorinupdatingcurr','block_school'), $redirlink);
		}
					
	}
	
 
	if ($rid != 0 && $sid != 0 && $yid != 0 && $gid != 0)  {

		$classdisciplines = get_record_sql ("SELECT * FROM {$CFG->prefix}monit_school_class_discipline
										WHERE schoolid=$sid and classid=$gid AND id=$cdid
										ORDER BY name");
	
		$discipline = get_record ('monit_school_discipline', 'id', $classdisciplines->disciplineid);
		
		$arr_group_disipline = get_record_sql("SELECT * FROM {$CFG->prefix}monit_school_discipline_group
		 								  WHERE schoolid=$sid AND disciplinedomainid={$discipline->disciplinedomainid} AND id={$discipline->dgroupid}");
	   	
		
		if ($arr_group_disipline->name)	{
			$str_group_disipline = $arr_group_disipline->name;
		}	else  {
			$str_group_disipline = get_string('nodisc', 'block_mou_school');
		}
		
  	 	$teachers = get_records_sql("SELECT * FROM {$CFG->prefix}monit_school_teacher
   	 								 WHERE schoolid=$sid AND disciplineid=$did");
        if ($teachers)  {
              foreach ($teachers as $teach)  {
                $user=get_record_sql("SELECT id, lastname, firstname FROM {$CFG->prefix}user
              						  WHERE id={$teach->teacherid}");
	           	$teachermenu[$teach->teacherid] = fullname($user);
              }
        } else {
        	error(get_string('notassignteacher', 'block_mou_school'), "{$CFG->wwwroot}/blocks/mou_school/curriculum/editteachdiscip.php?sid=$sid&yid=$yid&rid=$yid&did=$did");
        }
        
	    $hours = get_record_sql("SELECT hours FROM {$CFG->prefix}monit_school_curriculum
	   							 WHERE yearid=$yid and schoolid=$sid and classid=$gid and disciplineid=$did");
	   	if ($hours)	{
	   		$strhours = $hours->hours;
	   	} else {
	   		$strhours = '-';
	   	}						 
        
		
		print_heading (get_string('editingdiscipline', 'block_mou_school'), "center");
		print_simple_box_start("center", '60%');
		
?>
	<form name="addform" method="post" action="editdiscipline.php">
	<center>
	<table cellpadding="5">

	<tr valign="top">
	    <td align="right"><b><?php  print_string("discgroup","block_mou_school") ?>:</b></td>
	    <td align="left"> <?php echo $str_group_disipline ?></td>
	</tr>

	<tr valign="top">
	    <td align="right"><b><?php  print_string('predmet','block_mou_school') ?>:</b></td>
	    <td align="left"> <?php echo $discipline->name ?></td>
	</tr>

	<tr valign="top">
	    <td align="right"><b><?php  print_string('teacher','block_mou_school') ?>:</b></td>
		<td align="left">  <?php   choose_from_menu ($teachermenu, 'teacherid', $uid); ?></td>
	</tr>

	<tr valign="top">
	    <td align="right"><b><?php  print_string('hoursweek','block_mou_school') ?>:</b></td>
	    <td align="left"> <?php echo $strhours ?></td>
	</tr>

	</table>
	</center>
<?php

		echo  '<input type="hidden" name="rid" value="' .  $rid . '">';
		echo  '<input type="hidden" name="gid" value="' .  $gid . '">';
		echo  '<input type="hidden" name="sid" value="' .  $sid . '">';
		echo  '<input type="hidden" name="yid" value="' .  $yid . '">';
		echo  '<input type="hidden" name="did" value="' .  $did . '">';
		echo  '<input type="hidden" name="cdid" value="' . $cdid . '">';
		echo  '<input type="hidden" name="uid" value="' .  $uid . '">';
		echo  '<div align="center">';
		echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '"></div>';
		echo  '</form>';
	
   }	
	print_simple_box_end();
	
    print_footer();
*/    
?>
