<?php // $Id: pupilcard.php,v 1.8 2012/02/10 08:52:14 shtifanov Exp $


    include('incl_pupil.php');
    
	if (!$edit_capability && !$edit_capability_class)	{
		redirect($CFG->wwwroot."/blocks/mou_school/class/pupil.php?mode=4&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid=$uid", '', 0);
	}	
    

   	$biguser = (array)$user1 + (array)$pupil;
   	$user = (object)$biguser;
	    // $user->staffid = $pupil->id;

    if (isadmin()) {             // Current user is an admin
         if ($mainadmin = get_admin()) {
               if ($user->id == $mainadmin->id) {  // Can't edit primary admin
                  print_error('adminprimarynoedit');
             }
         }
    }

    if (isguest()) {
        error("The guest user cannot edit their profile.");
   	}

    if (isguest($user->id)) {
   	    error("Sorry, the guest user cannot be edited.");
    }

   	
	$auth = "manual";    

/// If data submitted, then process and store.

    if ($usernew = data_submitted()) {

        // print_r($usernew); echo '<hr>';
        /*
        if (($USER->id <> $usernew->id) && !isadmin()) {
            error("You can only edit your own information");
        }
       */
        $redirlink = "$CFG->wwwroot/blocks/mou_school/class/classpupils.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid";

        if (isset($usernew->password)) {
            unset($usernew->password);
        }

        // data cleanup
        // username is validated in find_regform_errors
        $usernew->country = 'RU';
        $usernew->lang    = 'ru_utf8';
        // $usernew->url     = clean_param($usernew->url,     PARAM_URL);
        // $usernew->icq     = clean_param($usernew->icq,     PARAM_INT);
        /*
        if (!$usernew->icq) {
            $usernew->icq = '';
        }
        */
        $usernew->icq = '';
        $usernew->skype   = '';
        $usernew->yahoo   = '';
        // $usernew->aim   = clean_param($usernew->aim,   PARAM_CLEAN);
        $usernew->msn   = '';

        $usernew->mnethostid    = $CFG->mnet_localhost_id;
        $usernew->maildisplay   = 1;
        $usernew->mailformat    = 1;
        $usernew->maildigest    = 0;
        $usernew->autosubscribe = 1;
        $usernew->htmleditor    = 1;
        $usernew->emailstop     = 0;
        $usernew->trackforums   = 1;

        if (isset($usernew->timezone)) {
            if ($CFG->forcetimezone != 99) { // Don't allow changing this in any way
                unset($usernew->timezone);
            } else { // Clean up the data a bit, just in case of injections
                $usernew->timezone = str_replace(';', '',  $usernew->timezone);
                $usernew->timezone = str_replace('\'', '', $usernew->timezone);
            }
        }

		if (!get_magic_quotes_gpc()) {
	        foreach ($usernew as $key => $data) {
	            $usernew->$key = addslashes(clean_text(stripslashes(trim($usernew->$key)), FORMAT_MOODLE));
	        }
	    } else {
	        foreach ($usernew as $key => $data) {
	            $usernew->$key = clean_text(trim($usernew->$key), FORMAT_MOODLE);
	        }
	    }

        $usernew->lastname    = strip_tags($usernew->lastname);
        $usernew->firstname   = strip_tags($usernew->firstname);
        $usernew->secondname  = strip_tags($usernew->secondname);

        if (isset($usernew->username)) {
            $usernew->username = moodle_strtolower($usernew->username);
        }

        require_once($CFG->dirroot.'/lib/uploadlib.php');
        $um = new upload_manager('imagefile',false,false,null,false,0,true,true);


        if (find_regform_errors($user, $usernew, $err, $um, $profile)) {
            if (empty($err['imagefile']) && $usernew->picture = save_profile_image($user->id, $um)) {
                set_field('user', 'picture', $usernew->picture, 'id', $user->id);  /// Note picture in DB
            } else {
                if (!empty($usernew->deletepicture)) {
                    set_field('user', 'picture', 0, 'id', $user->id);  /// Delete picture
                    $usernew->picture = 0;
                }
            }

            $usernew->auth = $user->auth;
            $usernew->deleted = $user->deleted;
            $user = $usernew;

        } else {

			$i = 0;
			foreach ($profile->fields as $pf)  {
  				if (isset($usernew->{$pf}))  {
  					$pupil->{$pf} = $usernew->{$pf};

					switch ($profile->type[$i]) {
						case 'text': case 'int':
						break;
						case 'date':
						    if ($usernew->{$pf} != '-' && !empty($usernew->{$pf})) {
							    $pupil->{$pf} = convert_date($usernew->{$pf}, 'ru', 'en');
							} else {
							    $pupil->{$pf} = NULL;
							}
						break;
					}

				} else {
					switch ($profile->type[$i]) {
						case 'text': $pupil->{$pf} = '-';
						break;
						case 'int': $pupil->{$pf} = 0;
						break;
					}
				}


				$i++;
			}

			$pupil->listegeids = '';
			$pupil->listdatesids = '';


       	    if (!empty($usernew->newpassword))  {
	            $pupil->pswtxt = $usernew->newpassword;
	        }

			foreach ($pupil as $keyt => $atrib)	{
				if (isset($pupil->{$keyt}) && !empty($pupil->{$keyt})) 	{
					$pupil1->{$keyt} = $pupil->{$keyt};
				}
			}


            $pupil1->timemodified = time();

	        if (!update_monit_record('monit_school_pupil_card', $pupil1))	{
	            print_r($pupil1); echo '<hr>';
				error(get_string('errorinupdateprofilepupil','block_mou_ege'), "{$CFG->wwwroot}/blocks/mou_school/class/pupil.php?mode=4&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid=$uid");
 		    }

            $timenow = time();

            if (!$usernew->picture = save_profile_image($user->id,$um)) {
                if (!empty($usernew->deletepicture)) {
                    set_field('user', 'picture', 0, 'id', $user->id);  /// Delete picture
                    $usernew->picture = 0;
                } else {
                    $usernew->picture = $user->picture;
                }
            }

            $usernew->timemodified = time();

            if (!empty($usernew->newpassword))  {

                $usernew->password = md5($usernew->newpassword);

                // update external passwords
                if (!empty($CFG->{'auth_'. $user->auth.'_stdchangepassword'})) {
                    if (function_exists('auth_user_update_password')){
                        if (!auth_user_update_password($user->username, $usernew->newpassword)){
                            error('Failed to update password on external auth: ' . $user->auth .
                                    '. See the server logs for more details.');
                        }
                    } else {
                        error('Your external authentication module is misconfigued!');
                    }
                }
            }
            // store forcepasswordchange in user's preferences
            if (!empty($usernew->forcepasswordchange)){
                set_user_preference('auth_forcepasswordchange', 1, $user->id);
            } else {
                unset_user_preference('auth_forcepasswordchange', $user->id);
            }
            /*
            if ($usernew->url and !(substr($usernew->url, 0, 4) == "http")) {
                $usernew->url = "http://".$usernew->url;
            }
            */
            $userold = get_record('user', 'id', $usernew->id);
            $usernew->firstname  .= ' ' . $usernew->secondname;

            if (update_record("user", $usernew)) {
                if (function_exists("auth_user_update")){
                    // pass a true $userold here
                    auth_user_update($userold, $usernew);
                };

                 if ($userold->email != $usernew->email) {
                    set_bounce_count($usernew,true);
                    set_send_count($usernew,true);
                }

                add_to_log(1, 'mou_ege', "user update", 'registrationcard.php', $USER->lastname.' '.$USER->firstname);

                if ($user->id == $USER->id) {
                    // Copy data into $USER session variable
                    $usernew = (array)$usernew;
                    foreach ($usernew as $variable => $value) {
                        $USER->$variable = stripslashes($value);
                    }
                    if (isset($USER->newadminuser)) {
                        unset($USER->newadminuser);
                        redirect("$CFG->wwwroot/", get_string('changessaved'));
                    }
                    if (!empty($SESSION->wantsurl)) {  // User may have been forced to edit account, so let's
                                                       // send them to where they wanted to go originally
                        $wantsurl = $SESSION->wantsurl;
                        $SESSION->wantsurl = '';       // In case unset doesn't work as expected
                        unset($SESSION->wantsurl);
                        redirect($wantsurl, get_string('changessaved'));
                       
                    } else {
                        redirect($redirlink, get_string("changessaved"), 0);
                    }
                } else {
                    redirect($redirlink, get_string("changessaved"), 0);
                }
            } else {
                error("Could not update the user record ($user->id: $fullname)");
            }
        }
    }

		/// Otherwise fill and print the form.
       	$fullname = fullname($user);
	    $personalprofile = get_string("personalprofile");
	    $participants = get_string("participants");

	    if ($user->deleted) {
	        print_heading(get_string("userdeleted"));
	    }

	    $currenttab = 'pupilcard';
	    include('tabspupil.php');

/*
		if (!$admin_is && !$region_operator_is) {
	        error(get_string('accesstemporarylock', 'block_mou_ege'));
		}
*/
	    $streditmyprofile = get_string("editmyprofile");
	    $strparticipants = get_string("participants");
	    $strnewuser = get_string("newuser");

	    print_simple_box_start("center", '70%', 'white');

	    if (!empty($err)) {
    	    echo "<center>";
        	notify(get_string("someerrorswerefound"));
	        echo "</center>";
	    }

	include("pupilcardedit.html");

   	print_simple_box_end();

    print_footer();

	exit;

/// FUNCTIONS ////////////////////

function find_regform_errors(&$user, &$usernew, &$err, &$um, &$profile)
{
    global $CFG, $sid, $lid; //$admin_is, $region_operator_is, $rayon_operator_is, $school_operator_is;

//     print_r($usernew); echo '<hr>';
//     print_r($user); echo '<hr>';
//	if ($admin_is || $region_operator_is || $rayon_operator_is || $school_operator_is) {
        if (empty($usernew->username)) {
            $err["username"] = get_string("missingusername");

        } else if ($usernew->username == 'pupil'.$sid.$lid)  {
	        $err["username"] = get_string('errorinusernamename', 'block_mou_att');

        }  else {
            if (empty($CFG->extendedusernamechars)) {
                $string = eregi_replace("[^(-\.[:alnum:])]", "", $usernew->username);
                if (strcmp($usernew->username, $string)) {
                    $err["username"] = get_string("alphanumerical");
                }
            }
        }

        if (strtolower($usernew->username) != strtolower($user->username))    {
        	if (record_exists("user", "username", $usernew->username)) {
	            $err["username"] = get_string("usernameexists");
	        }
	    }

        /*
        if (empty($usernew->newpassword) and empty($user->password))
            $err["newpassword"] = get_string("missingpassword");

        if (($usernew->newpassword == "admin") or ($user->password == md5("admin") and empty($usernew->newpassword)) ) {
            $err["newpassword"] = get_string("unsafepassword");
        }
        */
  //  }

    if (empty($usernew->email))
        $err["email"] = get_string("missingemail");

    if (over_bounce_threshold($user) && $user->email == $usernew->email)
        $err['email'] = get_string('toomanybounces');
  /*
    if (empty($usernew->description) and !isadmin())
        $err["description"] = get_string("missingdescription");
  */
    if (empty($usernew->city))
        $err["city"] = get_string("missingcity");

    if (empty($usernew->firstname))
        $err["firstname"] = get_string("missingfirstname");

    if (empty($usernew->lastname))
        $err["lastname"] = get_string("missinglastname");

    if (empty($usernew->country))
        $err["country"] = get_string("missingcountry");

    if (!validate_email($usernew->email)) {
        $err["email"] = get_string("invalidemail");

    } else if ($otherusers = get_records("user", "email", $usernew->email)) {
    	if (count($otherusers)>1)  {
            $err["email"] = get_string("emailexists");
    	} else {
    	   foreach ($otherusers as $otheruser) 	{
		        if ($otheruser->id <> $user->id) {
  			          $err["email"] = get_string("emailexists");
		        }
		   }
		}
    }

    if (empty($err["email"]) and !isadmin()) {
        if ($error = email_is_not_allowed($usernew->email)) {
            $err["email"] = $error;
        }
    }

    if (!$um->preprocess_files()) {
        $err['imagefile'] = $um->notify;
    }

    $user->email = $usernew->email;
    /*
	$i = 0;

	foreach ($profile->fields as $pf)  {
		if (in_array($pf, $profile->numericfield)) {
		     if (isset($usernew->{$pf}))  { // && !empty($usernew->{$pf}))	{
		   		if ($usernew->{$pf} == '-') {
		   			$usernew->{$pf} = 0;
		   		}
		   	    if (!is_numeric($usernew->{$pf})) {
 		      		$err[$pf] = get_string('errorinputdata', 'block_mou_att');;
  		     	}
   			 }
		} else {
			if (empty($usernew->{$pf}))  {
	  			$err[$pf] = get_string('missingname');
			} else {
				switch ($profile->type[$i]) {
					case 'date':
						if ($usernew->{$pf} != '-' && !is_date($usernew->{$pf})) {
			 	      		$err[$pf] = get_string('missingdate', 'block_mou_att');
			  	     	}
					break;
				}
	 		}
 		}
		$i++;
	}
    */


    return count($err);
}


?>

