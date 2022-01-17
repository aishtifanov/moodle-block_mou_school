<?php // $Id: from_our_obl.php,v 1.6 2010/08/27 10:38:33 Shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_att/lib_att.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_school.php');

    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = required_param('sid', PARAM_INT);       // School id
    $gid = required_param('gid', PARAM_INT);          // Class id
    $newuser = optional_param('newuser', false);  // Add new user
 //   $lable = optional_param('labeled', 0);  // Add new user
    $rid2 = optional_param('rid2', 0, PARAM_INT);          // Rayon id
    $sid2 = optional_param('sid2', 0, PARAM_INT);       // School id
    $gid2 = optional_param('gid2', 0, PARAM_INT);          // Group id
    $yid = optional_param('yid', '0', PARAM_INT);       // Year id

    if ($yid == 0)	{
    	$yid = get_current_edu_year_id();
    }

	require_login();

	$context = get_context_instance(CONTEXT_SCHOOL, $sid);
	if (!has_capability('block/mou_school:editclasslist', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

    $strpupil = get_string('pupils','block_mou_school');
    $strclasses = get_string('classes','block_mou_ege');
    $strtitle = get_string('movepupil','block_mou_school');

	$breadcrumbs = '<a href="'.$CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('title','block_mou_school').'</a>';
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/mou_school/class/classlist.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">$strclasses</a>";
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/mou_school/class/classpupils.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid\">$strpupil</a>";	
	$breadcrumbs .= "-> $strtitle";
    print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);
    
    $strtitle = get_string('chooseform','block_mou_ege');
	print_heading($strtitle, "center", 2);
	
	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_rayons("from_our_obl.php?rid=$rid&amp;sid=$sid&amp;gid=$gid&amp;sid2=0&amp;yid=$yid&amp;gid2=0&amp;rid2=", $rid2);
	listbox_schools("from_our_obl.php?rid=$rid&amp;sid=$sid&amp;gid=$gid&amp;rid2=$rid2&amp;yid=$yid&amp;gid2=0&amp;sid2=", $rid2, $sid2, $yid);
    listbox_class("from_our_obl.php?rid=$rid&amp;sid=$sid&amp;gid=$gid&amp;rid2=$rid2&amp;sid2=$sid2&amp;yid=$yid&amp;gid2=", $rid2, $sid2, $yid, $gid2);
	echo '</table>';
						
	if ($frm = data_submitted()){

		$redirlink = "{$CFG->wwwroot}/blocks/mou_school/class/classpupils.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid";
		$is_move = false;		
		// выбираем всех учеников школы выбытия
		if ($pupilsschool = get_records_sql("SELECT u.lastname, u.firstname, u.id
						FROM {$CFG->prefix}user u
						LEFT JOIN {$CFG->prefix}monit_school_pupil_card p ON p.userid = u.id
						WHERE p.classid = $gid2 and p.rayonid = $rid2 and p.schoolid = $sid2 and p.yearid=$yid")) {
				
				foreach ($pupilsschool as $pupils){
					// если ученик ещё числится в школе выбытия
					if(($pupils->firstname == $frm->firstname . ' ' .$frm->surename) && ($pupils->lastname == $frm->lastname)) {
		
						 $pupil = get_record_sql("SELECT id FROM {$CFG->prefix}monit_school_pupil_card 
						 						  WHERE	userid = {$pupils->id} and yearid=$yid"); 			
						 $pupil->rayonid = $rid;
				       	 $pupil->schoolid = $sid;
				       	 $pupil->classid = $gid;

				       	 // print_r($pupil);
				       	 // зачисляем в школу прибытия
					     if (!update_record('monit_school_pupil_card', $pupil))	{
					     	  print_r($pupil);	
							  error(get_string('errorinupdateprofilepupil','block_mou_ege'), $redirlink);
				 		 }
				 		 // сохраняем историю его перемещений	
				 		 $rec->userid = $pupils->id;
				    	 $rec->county  = '';
				    	 $rec->rayon  = '';
				    	 $rec->naspunkt = '';
				    	 $rec->school = '';
				    	 $rec->class = '';
				    	 $rec->rayoninid = $rid2;
				    	 $rec->schoolinid = $sid2;
				    	 $rec->classinid  = $gid2;
			  			 $dateout = date('Y-m-d');	    	
				    	 $rec->dateout  = $dateout;	
				    	 
						 if (!insert_record('monit_school_movepupil', $rec))   {
						 	  print_r($rec);
		 		    	 	  error(get_string('errorinupdateprofilepupil','block_mou_ege'), $redirlink);
		 		    	 }
						// notify(get_string('calloperator','block_mou_ege'),'green');						
						notice(get_string('movingdone','block_mou_school'), $redirlink);
						$is_move = true; 
						break;
						// 
					}
				}		
		}
		
		if (!$is_move)	{
			notify(get_string('pupilnotfound1', 'block_mou_school', $frm->lastname . ' ' . $frm->firstname . ' ' .$frm->surename));
		}		

		$is_move = false;
		$id_specialschool = ID_SCHOOL_FOR_DELETED;
		// выбираем всех учеников школы "Школа выбывших учителей и учеников"
		if ($pupilsschool = get_records_sql("SELECT u.lastname, u.firstname, u.id
						FROM {$CFG->prefix}user u
						LEFT JOIN {$CFG->prefix}monit_school_pupil_card p ON p.userid = u.id
						WHERE p.schoolid = $id_specialschool")) {
				foreach ($pupilsschool as $pupils){
					// если ученик уже числится в школе "Бывших"
					if(($pupils->firstname == $frm->firstname . ' ' .$frm->surename) && ($pupils->lastname == $frm->lastname)) {
							$fullname = $pupils->lastname.' '.$pupils->firstname;
					
								 // $pupil->id = $pupils->id;
	 						 	 $pupil = get_record_sql("SELECT id FROM {$CFG->prefix}monit_school_pupil_card 
						 		 	 					  WHERE	userid = {$pupils->id} and yearid=$yid"); 			
  								 $pupil->rayonid = $rid;
						       	 $pupil->schoolid = $sid;
						       	 $pupil->classid = $gid;
						       	 // зачисляем в школу прибытия
							     if (!update_record('monit_school_pupil_card', $pupil))	{
									  error(get_string('errorinupdateprofilepupil','block_mou_ege'), $redirlink);
						 		 }
						 		
						 		 // сохраняем историю его перемещений	
						 		 $rec->userid = $pupils->id;
						    	 $rec->county  = '';
						    	 $rec->rayon  = '';
						    	 $rec->naspunkt = '';
						    	 $rec->school = '';
						    	 $rec->class = '';
						    	 $rec->rayoninid = 25;
						    	 $rec->schoolinid = ID_SCHOOL_FOR_DELETED;
						    	 $rec->classinid  = 0;
					  			 $dateout = date('Y-m-d');	    	
						    	 $rec->dateout  = $dateout;	
						    	 
 	   							 if (!insert_record('monit_school_movepupil', $rec))   {
				 		    	 	  error(get_string('errorinupdateprofilepupil','block_mou_ege'), $redirlink);
				 		    	 }
				 		    	 
			 		    		notice(get_string('pupilmoved', 'block_mou_school',$fullname), $redirlink);
					}
				}		
		 }		
		if (!$is_move)	{
			notify(get_string('pupilnotfound2','block_mou_school', $frm->lastname . ' ' . $frm->firstname . ' ' .$frm->surename));
		}		
	} else {
		$frm->lastname = $frm->firstname = $frm->surename = ''; 
	}
	
	
	if ($rid != 0 && $sid != 0 && $yid != 0 && $gid != 0)  {

		?>

		<form name="addform" method="post" action="from_our_obl.php">
		<center>
		<table cellpadding="5">
	
		<tr valign="top">
		    <td align="right"><b><?php  print_string("lastname") ?>:</b></td>
		    <td align="left">
				<input name="lastname" type="text" id="lastname" value="<?php echo $frm->lastname  ?>" size="50" />
		    </td>
		</tr>
	
		<tr valign="top">
		    <td align="right"><b><?php  print_string("firstname") ?>:</b></td>
		    <td align="left">
				<input name="firstname" type="text" id="firstname" value="<?php echo $frm->firstname ?>" size="50" />
		    </td>
		</tr>
		
		<tr valign="top">
		    <td align="right"><b><?php  print_string("surename","block_mou_ege") ?>:</b></td>
		    <td align="left">
				<input name="surename" type="text" id="surename" value="<?php echo $frm->surename ?>" size="50" />
		    </td>
		</tr>
		
		</table>
	  </div>
		 </center>
	
	
		<?php

		echo  '<input type="hidden" name="rid" value="' .  $rid . '">';
		echo  '<input type="hidden" name="sid" value="' .  $sid . '">';
		echo  '<input type="hidden" name="yid" value="' .  $yid . '">';
		echo  '<input type="hidden" name="gid" value="' .  $gid . '">';
		echo  '<input type="hidden" name="rid2" value="' . $rid2 . '">';
		echo  '<input type="hidden" name="sid2" value="' . $sid2 . '">';
		echo  '<input type="hidden" name="gid2" value="' . $gid2 . '">';
		echo  '<div align="center">';
		echo  '<input type="submit" name="next" value="'. get_string('next','block_mou_ege') . '"></div>';
		echo  '</form>';
	}
	print_footer();
?>