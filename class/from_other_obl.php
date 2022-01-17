<?php // $Id: from_other_obl.php,v 1.5 2010/08/23 08:47:58 Shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_att/lib_att.php');
    require_once('../../mou_ege/lib_ege.php');


    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = required_param('sid', PARAM_INT);       // School id
    $gid = required_param('gid', PARAM_INT);          // Class id
    $newuser = optional_param('newuser', false);  // Add new user
    //$lable = optional_param('labeled', 0);  // Add new user
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


    $strtitle = get_string('enterform','block_mou_ege');
	print_heading($strtitle, "center", 2);
	
	
	$redirlink = "{$CFG->wwwroot}/blocks/mou_school/class/classpupils.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid";
	$class = get_record('monit_school_class', 'id', $gid);
		
    if ($newuser and confirm_sesskey())   {           // Create a new user

	    $rayon = get_record('monit_rayon', 'id', $rid);
	    // $currtime = time();

	    // $code = '-'.$rid . '-' . $sid . '-' . $gid . '-' . $USER->id;
	    $code = get_pupil_username($rid, $sid, $class);


        $user->auth      = "manual";
        $user->firstname = "Имя Отчество";
        $user->lastname  = "Фамилия";
        $user->username  = $code;
        $pswtxt = gen_psw($user->username);
        $user->password = hash_internal_user_password($pswtxt);
        $user->email     = $code.'@temp.ru';    // time()
        $user->city      = $rayon->name;
        $user->country   = 'RU';
        $user->lang      = 'ru_utf8';
        $user->icq 		 = '';
        $user->skype	 = '';
        $user->yahoo 	 = '';
        $user->msn       = '';
        $user->display   = 1;
        $user->mnethostid = $CFG->mnet_localhost_id;
        $user->mailformat    = 1;
        $user->maildigest    = 0;
        $user->autosubscribe = 1;
        $user->htmleditor    = 1;
        $user->emailstop     = 0;
        $user->trackforums   = 1;

        $user->confirmed = 1;
        $user->timemodified = time();
        $user->description = '';

        if (!$user->id = insert_record("user", $user)) 	{
           if (!$user = get_record("user", "username", $code)) 	{  
              error("Could not start a new user!", $redirlink );
	       }
 	    }

        $uid = $user->id;
        unset($user);
        if (!$pupil = get_record('monit_school_pupil_card', 'userid', $uid, 'yearid', $yid))	{
     		$pupil->yearid 			= $yid;
            $pupil->pswtxt 			= $pswtxt;
            $pupil->userid 			= $uid;
            $pupil->rayonid 		= $rid;
            $pupil->schoolid 		= $sid;
            $pupil->classid 		= $gid;
            $pupil->timemodified 	= time();

		    if (record_exists('monit_school_pupil_card', 'userid', $pupil->userid, 'yearid', $yid))	 {
		    	$u = get_record('user', 'id', $pupil->userid);
				notice(get_string('existpupil', 'block_mou_school', fullname($u)), $redirlink);
			}

		    if (record_exists('user', 'id', $pupil->userid))	 {
				if (insert_record('monit_school_pupil_card', $pupil))	{
					// add_to_log(1, 'monitoring', 'operator added', '/blocks/monitoring/users/operators.php?level=$levelmonit&amp;sid=$sid&amp;rid=$rid', $USER->lastname.' '.$USER->firstname);
				} else  {
					error(get_string('errorinaddingpupil','block_mou_school'), $redirlink);
				}
		    }
          	unset($pupil);
        }
    }
						
	if ($frm = data_submitted())	{
		
 		 $rec->userid = $uid;
    	 $rec->county  = $frm->county;
    	 $rec->rayon  = $frm->rayon;
    	 $rec->naspunkt = $frm->naspunkt;
    	 $rec->school = $frm->school;
    	 $rec->class = $frm->class;
    	 $rec->rayoninid = 0;
    	 $rec->schoolinid = 0;
    	 $rec->classinid  = 0;
		 $dateout = date('Y-m-d');	    	
    	 $rec->dateout  = $dateout;	
    	 
		 if (!insert_record('monit_school_movepupil', $rec))   {
    	 	  error(get_string('errorinupdateprofilepupil','block_mou_ege'), $redirlink);
    	 }		
		  redirect($CFG->wwwroot."/blocks/mou_school/class/pupilcard.php?mode=4&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid=$uid", '', 0);
	}

	if ($rid != 0 && $sid != 0 && $yid != 0 && $gid != 0)  {	
		?>
	
		<form name="addform" method="post" action="from_other_obl.php">
		<center>
		<table cellpadding="5">
	
		<tr valign="top">
		    <td align="right"><b><?php  print_string("county","block_mou_ege") ?>:</b></td>
		    <td align="left">
				<input name="county" type="text" id="county" value="<?php  ?>" size="70" />
		    </td>
		</tr>
	
		<tr valign="top">
		    <td align="right"><b><?php  print_string("rayon","block_mou_ege") ?>:</b></td>
		    <td align="left">
				<input name="rayon" type="text" id="rayon" value="<?php  ?>" size="70" />
		    </td>
		</tr>
		
		<tr valign="top">
		    <td align="right"><b><?php  print_string("naspunkt","block_mou_ege") ?>:</b></td>
		    <td align="left">
				<input name="naspunkt" type="text" id="naspunkt" value="<?php  ?>" size="70" />
		    </td>
		</tr>
		
			<tr valign="top">
		    <td align="right"><b><?php  print_string("school","block_mou_ege") ?>:</b></td>
		    <td align="left">
				<input name="school" type="text" id="school" value="<?php  ?>" size="70" />
		    </td>
		</tr>
		
			<tr valign="top">
		    <td align="right"><b><?php  print_string("class","block_mou_ege") ?>:</b></td>
		    <td align="left">
				<input name="class" type="text" id="class" value="<?php  ?>" size="10" />
		    </td>
		</tr>
		
		</table>
	  	</div>
		 </center>
		<?php
	
		echo  '<input type="hidden" name="rid" value="' .  $rid . '">';
		echo  '<input type="hidden" name="sid" value="' .  $sid . '">';
		echo  '<input type="hidden" name="yid" value="' .  $yid . '">';
		echo  '<input type="hidden" name="gid" value="' . $gid . '">';
		echo  '<input type="hidden" name="newuser" value="' . 'true' . '" />';
		echo  '<input type="hidden" name="sesskey" value="' . $USER->sesskey .'">';				
		echo  '<div align="center">';
		echo  '<input type="submit" name="next" value="'. get_string('next','block_mou_ege') . '"></div>';
		echo  '</form>';
	}
	print_footer();
?>