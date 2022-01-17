<?php // $Id: importclasses.php,v 1.9 2010/08/23 08:47:58 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
  	require_once($CFG->dirroot.'/lib/uploadlib.php');
   	require_once('../../mou_att/lib_att.php');
    require_once('../../mou_ege/lib_ege.php');

	define('ROLE_PUPIL', 5);
	
    require_once('../lib_school.php');

    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = required_param('sid', PARAM_INT);      // School id
    $yid = optional_param('yid', '0', PARAM_INT);     // Year id
    $gid = optional_param('gid', 0, PARAM_INT);   // Class id
    
    $breadcrumbs[0]->name = get_string('classes','block_mou_school');
    $breadcrumbs[0]->link = "{$CFG->wwwroot}/blocks/mou_school/class/classlist.php?rid=$rid&amp;sid=$sid&amp;yid=$yid";
	    
	require_once('../authbase.inc.php');

	$struser = get_string('user');
    $strtitle = get_string('importclass', 'block_mou_school');

    $csv_delimiter = ';';
    $usersnew = 0;
	$userserrors  = 0;
    $linenum = 1; // since header is line 1

	/// If a file has been uploaded, then process it

//	if (!empty($frm) ) {

	if (has_capability('block/mou_school:editclasslist', $context))	{
	
		$um = new upload_manager('userfile',false,false,null,false,0);
		$f = 0;
		if ($um->preprocess_files()) {
			$filename = $um->files['userfile']['tmp_name'];

		    @set_time_limit(0);
		    @raise_memory_limit("192M");
		    if (function_exists('apache_child_terminate')) {
		        @apache_child_terminate();
		    }
			$redirlink = "importclasses.php?sid=$sid&amp;rid=$rid&amp;yid=$yid";
			
			$text = file($filename);
			if($text == FALSE)	{
				error(get_string('errorfile', 'block_monitoring'), $redirlink);
			}
			$size = sizeof($text);

			$textlib = textlib_get_instance();
  			for($i=0; $i < $size; $i++)  {
				$text[$i] = $textlib->convert($text[$i], 'win1251');
            }

		    $required = array("name" => 1, "lastname" => 1, "firstname" => 1);

            // --- get and check header (field names) ---
            $header = split($csv_delimiter, $text[0]);

			$string_rus['name']='класс';             //1
			$string_rus['lastname']='фамилия';       //2
			$string_rus['firstname']='имя отчество'; //3
			$string_rus['pol']='пол'; 				//4
			$string_rus['birthday']='дата рождения';//5			

			$string_lat['name']='name';
			$string_lat['lastname']='lastname';
			$string_lat['firstname']='firstname';
			$string_lat['pol']='pol'; 				//4
			$string_lat['birthday']='birthday';//5			

		    foreach ($header as $i => $h) {
				$h = trim($h);
				$flag = true;
				foreach ($string_rus as $j => $strrus) {
		       		if ($strrus == $h)  {
		       			$header[$i] = $string_lat[$j];
						$flag = false;
		       			break;
		       		}
		       	}
		       	if ($flag)  {
			         print_r($header); echo '<hr>';
					 error(get_string('errorinnamefield', 'block_mou_ege', $string_rus[$header[$i]]), "importclasses.php?sid=$sid&amp;rid=$rid&amp;yid=$yid");
		       	}
		    }

	        $fullnames = array();

			$strsql = "SELECT id, schoolid FROM {$CFG->prefix}monit_school_class
				       WHERE schoolid=$sid AND yearid=$yid";
		 	if ($classes = get_records_sql($strsql))	{
		        $classesarray = array();
			    foreach ($classes as $ca)  {
			        $classesarray[] = $ca->id;
			    }
	 			$classeslist = implode(',', $classesarray);

				$strsql = "SELECT id, classid, userid FROM {$CFG->prefix}monit_school_pupil_card
				 		   WHERE classid in ($classeslist)";
			    if ($pupils = get_records_sql($strsql)) 	{
			        $pupilsarray = array();
				    foreach ($pupils as $pp)  {
				       $pupilsarray[] = $pp->userid;
				    }
				    $pupilslist = implode(',', $pupilsarray);

					$strsql = "SELECT id, lastname, firstname FROM {$CFG->prefix}user
					 		   WHERE id in ($pupilslist)";
				    if ($upupils = get_records_sql($strsql)) 	{
					    foreach ($upupils as $upp)  {
					       $fullnames[] = $upp->lastname . ' ' . $upp->firstname;
					    }
					}
	            }
	        }
            // print_r($fullnames);
            // exit();

			echo 'login;password;lastname;firstname;email<br>';

  			for($i=1; $i < $size; $i++)  {


	            $line = split($csv_delimiter, $text[$i]);
 	  	        foreach ($line as $key => $value) {
  	                $record[$header[$key]] = trim($value);
   	 	        }

                $linenum++;
                if ($linenum > 500)	 {
					error(get_string('verybiglinenum', 'block_mou_ege'), $redirlink);
                }
                
                // print_r($record);
                // add fields to object $user
                foreach ($record as $name => $value) {
                    // check for required values
                    if (isset($required[$name]) and !$value) {
                        error(get_string('missingfield', 'error', $string_rus[$name]). " ".
                              get_string('erroronline', 'error', $linenum),
                              "importclasses.php?sid=$sid&amp;rid=$rid&amp;yid=$yid");
                    }
                    // normal entry
                    else {
                        if ($name == 'lastname' || $name == 'firstname') {
                        	$user->{$name} = addslashes($value);
                        } else {
                        	$pupil->{$name} = addslashes($value);
                        }
                    }
                }

                $fullnames_check = $user->lastname . ' ' . $user->firstname;

                if (in_array($fullnames_check, $fullnames))	 {
                    notify(get_string('pupilnotaddedregistered', 'block_mou_ege', $fullnames_check));
                    $userserrors++;
                    continue;
                }
               // echo $pupil->when_hands.':';


			  $pupil->name = $textlib->strtoupper($pupil->name);
			  $pupil->pol  = $textlib->strtoupper($pupil->pol);
			  if ($pupil->pol == 'М')	{
			  		$pupil->pol = 1; 
			  } else if ($pupil->pol == 'Ж')	{
			  		$pupil->pol = 2;
			  }	
			  
			  $pupil->birthday = convert_date($pupil->birthday);

			  $pupil->name = translit_english_utf8($pupil->name);

			  $symbols = array (' ', '\"', "\'", "`", '-', '#', '*', '+', '_', '=');
			  foreach ($symbols as $sym)	{
				  $pupil->name = str_replace($sym, '', $pupil->name);
			  }

				if(!$class = get_record('monit_school_class', 'schoolid', $sid, 'yearid', $yid, 'name', $pupil->name)) 	 {
						$rec->rayonid = $rid;
						$rec->schoolid = $sid;
						$rec->yearid = $yid;
						$rec->curriculumid = 0;
						$rec->name = $pupil->name;
						$rec->parallelnum = (int) $pupil->name;
						$rec->description = "";
						$rec->timecreated = time();

						if ($idnew = insert_record('monit_school_class', $rec))	{
							$class = get_record('monit_school_class', 'id', $idnew);
		                    notify(get_string('classadded','block_mou_ege', $rec->name), 'green', 'left');
						} else {
						    print_r($rec); echo '<hr>';
							error(get_string('errorinaddingclass','block_mou_ege'), "$CFG->wwwroot/blocks/mou_school/class/importclasses.php?sid=$sid&amp;rid=$rid&amp;yid=$yid");
						}
                }


			    $code = get_pupil_username($rid, $sid, $class);

				$user->username = $code;

				 if ($olduser = get_record('user', 'username', $user->username))		{
				      if (($olduser->lastname == $user->lastname) && ($olduser->firstname == $user->firstname))	{
                           //Record not added - user is already registered
                           //In this case, output userid from previous registration
                           //This can be used to obtain a list of userids for existing users
                           notify("$olduser->id ".get_string('usernotaddedregistered', 'error', $user->username . ' '. $user->lastname. ' '.  $user->firstname));
                           $userserrors++;
                           continue;
                      }
                 }

/*
				 if ($olduser = get_record('user', 'username', $user->username, 'lastname', $user->lastname, 'firstname', $user->firstname))  {
                           //Full tezka
                           notify("FULL TEZKA: $olduser->id ".get_string('usernotaddedregistered', 'error', $user->username . ' '. $user->lastname. ' '.  $user->firstname));
                           $userserrors++;
                           continue;
                 }
*/

                $j = 1;
                $makecontinue = false;
				while (record_exists('user', 'username', $user->username))  {
					$user->username = $code.$j;
			 		if ($olduser = get_record('user', 'username', $user->username))		{
					    if ($olduser->firstname == $user->firstname)	{
                           notify("$olduser->id ".get_string('usernotaddedregistered', 'error', $user->username . ' '. $user->lastname. ' '.  $user->firstname));
	                       $userserrors++;
	                       $makecontinue = true;
	                       break;
	                    }
	                }
					if ($j++ > 1000) break;
				}

				if ($makecontinue) continue;


				$user->email = $user->username . '@temp.ru';

                $pupil->pswtxt = generate_password2(6);
                // $txtl->convert($strvalue, 'utf-8', 'windows-1251');
                $user->password = hash_internal_user_password($pupil->pswtxt);


		    	$rayon = get_record('monit_rayon', 'id', $rid);
		   	    $user->city = $rayon->name;

                $user->mnethostid = $CFG->mnet_localhost_id;
                $user->confirmed = 1;
                $user->timemodified = time();
                $user->country = 'RU';
                $user->lang = 'ru_utf8';

		    	$school = get_record('monit_school', 'id', $sid);

                $user->description = '';

                // echo '<hr>';
                // print_r($user);
                // print_r($pupil);


                if ($newid = insert_record("user", $user)) {
                    echo "$user->username; $pupil->pswtxt; $user->lastname; $user->firstname; $user->email<br>";
                    $usersnew++;
                    $pupil->userid = $newid;
                } else {
                    // Record not added -- possibly some other error
                    notify(get_string('usernotaddederror', 'error', $user->username));
                    $userserrors++;
                    continue;
	            }
                /*
                $coursecontext = get_context_instance(CONTEXT_COURSE, 1);
                if (!user_can_assign($coursecontext, ROLE_PUPIL)) {
                    notify("--> Can not assign role: $newid = $user->username ($user->lastname $user->firstname)"); //TODO: localize
                }
                $ret = role_assign(ROLE_PUPIL, $newid, 0, $coursecontext->id);
                */
                $pupil->classid 	 = $class->id;
                $pupil->rayonid  	 = $rid;
                $pupil->schoolid	 = $sid;
                $pupil->yearid 	 	 = $yid;
                $pupil->timemodified = time();

			    if (record_exists('user', 'id', $pupil->userid))	 {
					if (insert_record('monit_school_pupil_card', $pupil))	{
  					    $fullnames[] = $user->lastname . ' ' . $user->firstname;
						// add_to_log(1, 'monitoring', 'operator added', '/blocks/monitoring/users/operators.php?level=$levelmonit&amp;sid=$sid&amp;rid=$rid', $USER->lastname.' '.$USER->firstname);
					} else  {
					    print_r($pupil); echo '<hr>';
						error(get_string('errorinaddingpupil','block_mou_ege'), "classlist.php?rid=$rid&amp;yid=$yid&amp;sid=$sid");
	                	// error("--> Can not add <b>teacher</b> in staff: $user->username ($user->lastname $user->firstname)"); //TODO: localize
					}
			    }

                unset($user);
                unset($pupil);
            }
		    $strusersnew = get_string("usersnew");
    	    notify("$strusersnew: $usersnew", 'green', 'center');
            notify(get_string('errors', 'block_mou_ege') . ": $userserrors");
	        echo '<hr />';
       }

	    $currenttab = 'importclasses';
 	    include('tabsclasses.php');

    	$school = get_record('monit_school', 'id', $sid);
   	    $strschool = $school->name;
	    $struploadusers = get_string('importclasspupil', 'block_mou_ege', $strschool);
	    print_heading_with_help($struploadusers, 'importclasspol', 'mou');

		/// Print the form
   	    $struploadusers = get_string('importclassload', 'block_mou_school');
	    $maxuploadsize  = get_max_upload_file_size();
		$strchoose = ''; // get_string("choose"). ':';

	    echo '<center>';
	    echo '<form method="post" enctype="multipart/form-data" action="importclasses.php">'.
	         $strchoose.'<input type="hidden" name="MAX_FILE_SIZE" value="'.$maxuploadsize.'">'.
	         '<input type="hidden" name="rid" value="'.$rid.'">'.
	         '<input type="hidden" name="sid" value="'.$sid.'">'.
	         '<input type="hidden" name="yid" value="'.$yid.'">'.
	         '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'">';


	    echo '<input type="file" name="userfile" size="50">'.
	         '<br><input type="submit" value="'.$struploadusers.'">'.
	         '</form><p>';
	    $output = helpbutton('importclasspol', 'Как загрузить списки учеников нескольких классов', 'mou', true, true, '', true);
	    echo $output;
	    echo '</center>';

	}
 
	    

?>
<p><strong>Пример файла импорта в формате CSV (кодировка Windows-1251):</strong> </p>
<p><font size="1" face="Courier New, Courier, mono"></font>
класс;фамилия;имя отчество;пол;дата рождения<br>
2А;Иванова;Евгения Геннадьевна;ж;01.09.2001<br>
2А;Петрова;Светлана Евгеньевна;Ж;12.12.2001<br>
2Б;Сидоров;Владислав Сергеевич;м;10.08.2001<br>
</font></p>
<?php
	    
    print_footer();




?>
