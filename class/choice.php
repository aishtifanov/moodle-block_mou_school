<?php // $Id: choice.php,v 1.5 2010/08/23 08:47:54 Shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_att/lib_att.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_school.php');

    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = optional_param('sid', '0', PARAM_INT);     // School id
    $yid = optional_param('yid', '0', PARAM_INT);     // Year id
    $gid = optional_param('gid', 0, PARAM_INT);   // Class id
    $newuser = optional_param('newuser', false);  // Add new user
    $lable = optional_param('labeled', 0);  // Add new user    
    
    $breadcrumbs[0]->name = get_string('classes','block_mou_school');
    $breadcrumbs[0]->link = "{$CFG->wwwroot}/blocks/mou_school/class/classlist.php?rid=$rid&amp;sid=$sid&amp;yid=$yid";
	    
	require_once('../authbase.inc.php');

	if (has_capability('block/mou_school:editclasslist', $context))	{

		if ($frm = data_submitted()){
			switch ($lable){
				case '1':
					redirect("from_our_obl.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid", '', 0);
				exit;
				case '2':
					redirect("from_other_obl.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid", '', 0);
				exit;
				case '3':
					if ($gid != 0)	{
						$redirlink = "classlist.php?rid=$rid&amp;yid=$yid&amp;sid=$sid";
						
						$class = get_record('monit_school_class', 'id', $gid);					
					    if ($newuser and confirm_sesskey())   {           // Create a new user				
						    $rayon = get_record('monit_rayon', 'id', $rid);
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
				 	           if (!$user = get_record("user", "username", $code)) 	{   // half finished user from another time
				  	              error("Could not start a new user!");
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
					                	// error("--> Can not add <b>teacher</b> in staff: $user->username ($user->lastname $user->firstname)"); //TODO: localize
									}
							    }
					          	unset($pupil);
					        }
					       redirect($CFG->wwwroot."/blocks/mou_school/class/pupilcard.php?mode=4&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid=$uid", '', 0);
					    }
					}	
				exit;
			}
		}
		
		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	    listbox_class("classpupils.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=", $rid, $sid, $yid, $gid);
		echo '</table>';

		if ($gid != 0)  {

			print_heading(get_string('addpupil', 'block_mou_ege') , "center", 3);
	
			print_simple_box_start ('center', "40%", 'white') ; 
			echo '<form name=form method=post action=choice.php>';
			echo '<p>';
			echo '<input type="radio" name="labeled" value="1" id="labeled_1" checked="checked"/>';
			echo '<label for="labeled_1">Ученик прибыл из другой школы района или Белгородской области.</label>';
			echo '</p>';
			echo '<p>';
			echo '<input type="radio" name="labeled" value="2" id="labeled_2" />';
			echo '<label for="labeled_2">Ученик прибыл из другой области России.</label>';
			echo '</p>';
			echo '<p>';
			echo '<input type="radio" name="labeled" value="3" id="labeled_3" />';
			echo '<label for="labeled_3">Ученик ранее не числился ни в одной школе.</label>';
			echo '</p>';
			print_simple_box_end();
/*			
			echo '<table align="center" border=0><tr><td>';
			$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'gid' => $gid, 'newuser' => 'true', 'sesskey' => $USER->sesskey);
		    print_single_button("choice.php", $options, get_string('next','block_mou_ege'));
			echo '</td></tr></table>';
*/			

			?>	<table align="center"><tr><td>
				    <div align="center">
						<input type="hidden" name="rid" value="<?php echo $rid ?>" />
						<input type="hidden" name="sid" value="<?php echo $sid ?>" />
						<input type="hidden" name="yid" value="<?php echo $yid ?>" />
						<input type="hidden" name="gid" value="<?php echo $gid ?>" />
						<input type="hidden" name="newuser" value="true" />
						<input type="hidden" name="sesskey" value="<?php echo $USER->sesskey ?>">
						<input type="submit" name="addteacher" value="<?php print_string('next','block_mou_ege')?>">
					</div>
				  </td></tr></table>
			 </form> 
			<?php
		}
	}
	
		print_footer();
?>

