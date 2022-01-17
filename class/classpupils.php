<?php // $Id: classpupils.php,v 1.25 2012/02/13 10:32:24 shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../../mou_att/lib_att.php');
    require_once('../lib_school.php');

    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = optional_param('sid', '0', PARAM_INT);     // School id
    $yid = optional_param('yid', '0', PARAM_INT);     // Year id
    $gid = optional_param('gid', 0, PARAM_INT);   // Class id
    $newuser = optional_param('newuser', false);  // Add new user
    
    $breadcrumbs[0]->name = get_string('classes','block_mou_school');
    $breadcrumbs[0]->link = "{$CFG->wwwroot}/blocks/mou_school/class/classlist.php?rid=$rid&amp;sid=$sid&amp;yid=$yid";
	    
	require_once('../authbase.inc.php');

	$edit_capability = has_capability('block/mou_school:editclasslist', $context);

    switch ($action)  {
    	case 'excel':
					$table = table_classpupils ($yid, $rid, $sid, $gid);
			    	// print_r($table);
			        print_table_to_excel($table, 1);
			        exit();
		case 'ods':
			        exit();
	}


    if ($gid != 0)	{

		$context_class = get_context_instance(CONTEXT_CLASS, $gid);
		$edit_capability_class = has_capability('block/mou_school:editclasslist', $context_class);
    	
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
	        // $user->lang      = $CFG->lang;
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

	        // print_r($user);
	        // if (!$user = get_record("user", "username", "teacher"))	 {
		        if (!$user->id = insert_record("user", $user)) 	{
	 	           if (!$user = get_record("user", "username", $code)) 	{   // half finished user from another time
	  	              error("Could not start a new user!");
	   		       }
	     	    }
	     	// }

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
					notice(get_string('existpupil', 'block_mou_school', fullname($u)), "classlist.php?rid=$rid&amp;yid=$yid&amp;sid=$sid");
				}

			    if (record_exists('user', 'id', $pupil->userid))	 {
					if (insert_record('monit_school_pupil_card', $pupil))	{
						// add_to_log(1, 'monitoring', 'operator added', '/blocks/monitoring/users/operators.php?level=$levelmonit&amp;sid=$sid&amp;rid=$rid', $USER->lastname.' '.$USER->firstname);
					} else  {
						error(get_string('errorinaddingpupil','block_mou_school'), "classlist.php?rid=$rid&amp;yid=$yid&amp;sid=$sid");
	                	// error("--> Can not add <b>teacher</b> in staff: $user->username ($user->lastname $user->firstname)"); //TODO: localize
					}
			    }
	          	unset($pupil);
	        }
	        // exit();
	        redirect($CFG->wwwroot."/blocks/mou_school/pupils/pupilcard.php?mode=4&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid=$uid", 0);
	    }
    }

	
    $currenttab = 'classpupils';
    include('tabsclasses.php');

	if (has_capability('block/mou_school:viewclasslist', $context))	{
		
		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	    // listbox_class("classpupils.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=", $rid, $sid, $yid, $gid);
    	$strlistclasses =  listbox_class_role("classpupils.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=", $rid, $sid, $yid, $gid);
    	echo $strlistclasses;
		echo '</table>';
	
		if ($gid != 0)	{
	    	$table = table_classpupils ($yid, $rid, $sid, $gid);
	    	echo '<div align=center>';
			// $table->print_html();
			print_color_table($table);
	       	echo '</div>';
	       	
			if ($edit_capability || $edit_capability_class)	{
				echo '<table align="center" border=0><tr><td>';
				$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'gid' => $gid, 'newuser' => 'true', 'sesskey' => $USER->sesskey);
			    print_single_button("choice.php?mode=4", $options, get_string('addpupil','block_mou_ege'));
				echo '</td><td>';
				
				$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'gid' => $gid, 'sesskey' => $USER->sesskey, 'action' => 'excel');
			    print_single_button("classpupils.php", $options, get_string('downloadexcel_class', 'block_mou_ege'));
				echo '</td></tr></table>';
			}
	       	
		}	
	} else {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}


    print_footer();
    
    
    
function table_classpupils ($yid, $rid, $sid, $gid)
{
		global $USER, $CFG, $edit_capability, $edit_capability_class;
		
	/*
		$arr_group = get_records_sql ("SELECT id, name  FROM {$CFG->prefix}monit_school_class
	 								  WHERE yearid=$yid AND schoolid=$sid AND id=$gid
									  ORDER BY name");
	*/								  
		$table->head  = array ('', get_string('fullname'), get_string('pol', 'block_mou_school'),
								get_string('birthday', 'block_mou_school'),
							   get_string('username') . '/<br>' . get_string('startpassword', 'block_mou_school'),
							   get_string('email'), get_string('action'));
							   
		$table->align = array ('center', 'left', 'center', 'center', 'center', 'left', 'center');
	    $table->size = array ('5%', '20%', '7%', '10%', '10%', '18%', '7%');
		$table->columnwidth = array (0, 35, 10, 15, 20,20,0);
	    // $table->datatype = array ('char', 'char');
	    $table->class = 'moutable';
	   	$table->width = '90%';
	    // $table->size = array ('10%', '10%');
	    $table->titles = array();
	    $table->titles[] = get_string('listclass', 'block_mou_school');
	    $table->titlesrows = array(30);
	    $table->worksheetname = 'listclass';
	    $table->downloadfilename = 'class_'.$gid;


        $studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.picture, 
							  m.classid, m.pol, m.birthday, m.pswtxt
                            FROM {$CFG->prefix}user u
                       LEFT JOIN {$CFG->prefix}monit_school_pupil_card m ON m.userid = u.id 
					   WHERE classid = $gid AND u.deleted = 0 AND u.confirmed = 1
					   ORDER BY u.lastname, u.firstname";

        // print_r($studentsql); echo '<hr>';

        if($students = get_records_sql($studentsql)) {
        	
             foreach ($students as $student) {
             		$stremail = $strsex = $strbd = '-';
             		
             		if (!empty($student->pol))	{
             			$strsex = get_string ('sympol'.$student->pol, 'block_mou_school');
             		}

             		if ($student->birthday != '0000-00-00')	{
             			$strbd = convert_date($student->birthday, 'en', 'ru');
             		}

             		if (!empty($student->email))	{
             			$stremail = $student->email;
             		}

					if ($edit_capability || $edit_capability_class)	{
						$redirlink = "{$CFG->wwwroot}/blocks/mou_school/class/pupilcard.php?mode=4&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid={$student->id}";
						
						$title = get_string('editprofilepupil','block_mou_ege');
						$strlinkupdate = "<a href=\"{$CFG->wwwroot}/blocks/mou_school/class/pupilcard.php?mode=4&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid={$student->id}\">";
						$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";
						
						/*
						$title = get_string('pupilleaveschool','block_mou_ege');
					    $strlinkupdate .= "<a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/mou_school/class/leaveschool.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid={$student->id}&amp;sesskey=$USER->sesskey\">";
						$strlinkupdate .= "<img src=\"{$CFG->wwwroot}/blocks/mou_school/i/leave.png\" alt=\"$title\" /></a>&nbsp;";
						*/
						
						$title = get_string('pupilmoveschool','block_mou_ege');
					    $strlinkupdate .= "<a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/mou_school/class/question_to_move.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid={$student->id}&amp;sesskey=$USER->sesskey\">";
						$strlinkupdate .= "<img src=\"{$CFG->wwwroot}/blocks/mou_school/i/btn_move.png\" alt=\"$title\" /></a>&nbsp;";
	
	
						// $title = get_string('deleteprofilepupil','block_mou_ege');
						$title = get_string('pupilleaveschool','block_mou_ege');
					    $strlinkupdate .= "<a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/mou_school/class/delpupil.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid={$student->id}&amp;sesskey=$USER->sesskey\">";
						$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
					} else	{
						$strlinkupdate = '-';
						$redirlink = $CFG->wwwroot."/blocks/mou_school/class/pupil.php?mode=4&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid={$student->id}";
					}
	

                    $table->data[] = array (print_user_picture($student->id, 1, $student->picture, false, true),
								    "<div align=left><strong><a href=\"$redirlink\">".fullname($student)."</a></strong></div>",
								    $strsex, $strbd,
									$student->username . '/ '. $student->pswtxt, 
									$stremail, $strlinkupdate);

			}

		}
        return $table;
}
    

/*
function table_classpupils_sortable ($yid, $rid, $sid, $gid, $mode = 1)
{
		global $SITE, $USER, $CFG, $admin_is, $region_operator_is, $rayon_operator_is, $school_operator_is, $rayon;

	    $strnever = get_string('never');
		$tablecolumns = array('picture', 'fullname', 'username', 'pswtxt', 'email',  '');
		$tableheaders = array('', get_string('fullname'), get_string('username'),
		        						get_string('startpassword', 'block_mou_att'),
		        						get_string('email'), get_string('action'));
		$table->class = 'moutable';

	    $baseurl = $CFG->wwwroot."/blocks/mou_school/class/classpupils.php?sid=$sid&amp;rid=$rid&amp;yid=$yid&amp;gid=$gid";

        $table = new flexible_table("user-index-$gid");

	    $table->define_columns($tablecolumns);
        $table->define_headers($tableheaders);
		// $table->column_style_all('align', 'left');
        $table->define_baseurl($baseurl);
        $table->sortable(true, 'lastname');
		// $table->sortable(true, 'lastaccess', SORT_DESC);
        $table->set_attribute('cellspacing', '0');
		// $table->set_attribute('align', 'left');
        $table->set_attribute('id', 'students');
        $table->set_attribute('class', 'generaltable generalbox');
        $table->setup();

	    if($whereclause = $table->get_sql_where()) {
            $whereclause .= ' AND ';
        }
        $studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.maildisplay,
							  u.city, u.country, u.lastlogin as disciplines , u.picture, u.lang,
							  u.timezone as pswtxt, u.timemodified as timeappeal, u.lastaccess, m.classid
                            FROM {$CFG->prefix}user u
                       LEFT JOIN {$CFG->prefix}monit_school_pupil_card m ON m.userid = u.id ";

        $whereclause .= 'classid = '.$gid.' AND ';

	    $studentsql .= 'WHERE '.$whereclause.' u.deleted = 0 AND u.confirmed = 1';

        if($sortclause = $table->get_sql_sort()) {
            $studentsql .= ' ORDER BY '.$sortclause;
        }
		// print_r($studentsql); echo '<hr>';
        $students = get_records_sql($studentsql);

        if(!empty($students)) {

            if ($mode == 6) {
                foreach ($students as $key => $student) {
                    print_user($student, $SITE);
                }
            }
			else {
                foreach ($students as $student) {

                    if ($student->lastaccess) {
                        $lastaccess = format_time(time() - $student->lastaccess);
                    } else {
                        $lastaccess = $strnever;
                    }

				    $pupil = get_record('monit_school_pupil_card', 'userid', $student->id, 'yearid', $yid);

					$title = get_string('editprofilepupil','block_mou_ege');
					$strlinkupdate = "<a href=\"{$CFG->wwwroot}/blocks/mou_ege/pupils/pupilcard.php?mode=4&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid={$student->id}\">";
					$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";

					$title = get_string('pupilleaveschool','block_mou_ege');
				    $strlinkupdate .= "<a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/mou_ege/pupils/leaveschool.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid={$student->id}&amp;sesskey=$USER->sesskey\">";
					$strlinkupdate .= "<img src=\"{$CFG->wwwroot}/blocks/mou_ege/i/leave.png\" alt=\"$title\" /></a>&nbsp;";

					$title = get_string('pupilmoveschool','block_mou_ege');
				    $strlinkupdate .= "<a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/mou_ege/pupils/movepupil.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid={$student->id}&amp;sesskey=$USER->sesskey\">";
					$strlinkupdate .= "<img src=\"{$CFG->wwwroot}/blocks/mou_ege/i/btn_move.png\" alt=\"$title\" /></a>&nbsp;";

					$strcolumn5 = $student->email;

					$title = get_string('deleteprofilepupil','block_mou_ege');
				    $strlinkupdate .= "<a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/mou_ege/pupils/delpupil.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid={$student->id}&amp;sesskey=$USER->sesskey\">";
					$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";

                    $table->add_data(array (print_user_picture($student->id, 1, $student->picture, false, true),
								    "<div align=left><strong><a href=\"{$CFG->wwwroot}/blocks/mou_ege/pupils/pupilcard.php?mode=4&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid={$student->id}\">".fullname($student)."</a></strong></div>",
								    $student->username,
								    $pupil->pswtxt,
                                    $strcolumn5,
                                    $strlinkupdate));

                }
			}

		}
        return $table;
}
*/
?>


