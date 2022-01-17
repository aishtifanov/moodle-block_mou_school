<?php // $Id: movepupil.php,v 1.11 2010/08/23 08:47:59 Shtifanov Exp $

    require_once("../../../config.php");
    require_once("$CFG->libdir/gdlib.php");
    require_once('../../monitoring/lib.php');
	require_once('../../mou_ege/lib_ege.php');

    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = required_param('sid', PARAM_INT);       // School id
	// $cid = required_param('cid', PARAM_INT);		  // Curriculum id
    $yid = required_param('yid', PARAM_INT);       // Year id
    $gid = required_param('gid', PARAM_INT);          // Group id
    $delete = optional_param('uid', 0, PARAM_INT);       // User id
    $mode = optional_param('mode', 0, PARAM_INT);        // Mode: 0, 1, 2, 3, 4, 9, 99 Can(or can't) show groups
    $rid2 = optional_param('rid2', $rid, PARAM_INT);          // Rayon id
    $sid2 = optional_param('sid2', $sid, PARAM_INT);       // School id
    $gid2 = optional_param('gid2', 0, PARAM_INT);          // Group id
	$action   = optional_param('action', '');

	if ($yid == 0)	{
    	$yid = get_current_edu_year_id();
    }

	require_login();

	$context = get_context_instance(CONTEXT_SCHOOL, $sid);
	if (!has_capability('block/mou_school:editclasslist', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

    $strtitle = get_string('pupil','block_mou_school');
    $strclasses = get_string('classes','block_mou_school');
	$strclass = get_string('class','block_mou_school');
	$strpupils = get_string('pupils', 'block_mou_school');

	$breadcrumbs = '<a href="'.$CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('title','block_mou_school').'</a>';
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/mou_school/class/classlist.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">$strclasses</a>";
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/mou_school/class/classpupils.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid\">$strpupils</a>";
	$breadcrumbs .= "-> $strtitle";
    print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);


	$rayon = get_record('monit_rayon', 'id', $rid);

	$school = get_record('monit_school', 'id', $sid);

	$class = get_record('monit_school_class', 'id', $gid);

    $pupil = get_record('monit_school_pupil_card', 'userid', $delete, 'yearid', $yid);

	$redirlink = "{$CFG->wwwroot}/blocks/mou_school/class/classpupils.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid2";
    if ($action == 'move')	{
    	
       	  $pupil->rayonid = $rid2;
       	  $pupil->schoolid = $sid2;
       	  $pupil->classid = $gid2;
	        if (!update_monit_record('monit_school_pupil_card', $pupil))	{
					error(get_string('errorinupdateprofilepupil','block_mou_ege'), "{$CFG->wwwroot}/blocks/mou_school/class/pupil.php?mode=4&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid=$delete");
 		    }
 		    
	    	$rec->userid  = $delete;
	    	$rec->county  = '';
	    	$rec->rayon  = '';
	    	$rec->naspunkt = '';
	    	$rec->school = '';
	    	$rec->class = '';
	    	$rec->rayoninid = $rid;
	    	$rec->schoolinid = $sid;
	    	$rec->classinid  = $gid;
  			$dateout = date('Y-m-d');	    	
	    	$rec->dateout  = $dateout;		 
			   
 		    if ($newid = insert_record('monit_school_movepupil', $rec)) {
		            redirect("classpupils.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;gid=$gid", get_string('movingdone', 'block_mou_school', $newid), 0);
     		} else {	    
		            error('Error in insert pupil record.');
		    }
		    
          redirect($redirlink, get_string("changessaved"), 0);
    }


    if (!$user1 = get_record('user', 'id', $delete) ) {
        error('No such pupil in this class!', '../index.php');
	}

   	$fullname = fullname($user1);



?>
<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">
<tr valign="top">
    <td align="left"><b><?php  print_string('rayon', 'block_monitoring') ?>:</b></td>
    <td align="left"> <?php p($rayon->name) ?> </td>
</tr>
<tr valign="top">
    <td align="left"><b><?php  print_string('school', 'block_monitoring') ?>:</b></td>
    <td align="left"> <?php echo $school->name ?> </td>
</tr>
<tr valign="top">
    <td align="left"><b><?php  print $strclass; ?>:</b></td>
    <td align="left"> <?php p($class->name) ?> </td>
</tr>
</table>
<?php


    print_heading(get_string('pupilmovein', 'block_mou_school', $fullname), "center", 3);

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		
	$context = get_context_instance(CONTEXT_RAYON, $rid);
	if (has_capability('block/mou_school:editclasslist', $context))	{
		listbox_rayons("movepupil.php?mode=1&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;uid=$delete&amp;gid=$gid&amp;sid2=$sid2&amp;gid2=$gid2&amp;rid2=", $rid2);
	}	
	listbox_schools("movepupil.php?mode=2&amp;rid=$rid&amp;yid=$yid&amp;uid=$delete&amp;gid=$gid&amp;sid=$sid&amp;rid2=$rid2&amp;gid2=$gid2&amp;sid2=", $rid2, $sid2, $yid);
    listbox_class("movepupil.php?mode=3&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;uid=$delete&amp;gid=$gid&amp;rid2=$rid2&amp;sid2=$sid2&amp;gid2=", $rid2, $sid2, $yid, $gid2);
	echo '</table>';

	if ($mode == 3)  {
		/* 
		$context = get_context_instance(CONTEXT_REGION, 1);
		if (!has_capability('block/mou_school:editclasslist', $context))	{
	        error(get_string('accesstemporarylock', 'block_mou_school'), '../index.php');
		}
		*/
		$options = array('rid' => $rid, 'sid' => $sid, 'gid' => $gid, 'yid' => $yid,  'uid' => $delete,
						 'rid2' => $rid2, 'sid2' => $sid2, 'gid2' => $gid2, 'action' => 'move');
		echo '<table align="center" border=0><tr><td>';
	    print_single_button("movepupil.php", $options, get_string('makepupilmovein', 'block_mou_school'));
		echo '</td></tr></table>';
	}

    print_footer();
?>

