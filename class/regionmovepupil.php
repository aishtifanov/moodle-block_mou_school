<?php // $Id: regionmovepupil.php,v 1.15 2011/02/04 11:03:46 shtifanov Exp $

    require_once("../../../config.php");
    require_once("$CFG->libdir/gdlib.php");
    require_once('../../monitoring/lib.php');
	require_once('../../mou_ege/lib_ege.php');
    require_once('move_pupil_form.php');
    require_once('../lib_school.php');

    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = required_param('sid', PARAM_INT);       // School id
    $gid = required_param('gid', PARAM_INT);          // Group id
    $uid = required_param('uid', PARAM_INT);       // User id
    $mode = optional_param('mode', 0, PARAM_INT);        // Mode: 0, 1, 2, 3, 4, 9, 99 Can(or can't) show groups
	$action = optional_param('action', '');
	$yid = optional_param('yid', 0, PARAM_INT);

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

    $pupil = get_record('monit_school_pupil_card', 'userid', $uid, 'yearid', $yid);

	$redirlink = "{$CFG->wwwroot}/blocks/mou_school/class/classpupils.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid";

    if (!$user = get_record('user', 'id', $uid) ) {
        error('No such pupil in this class!', '../index.php');
	}

   	$fullname = fullname($user);

  	$schoolout = get_record('monit_school', 'id', ID_SCHOOL_FOR_DELETED);
    	 
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

	
	    $editform = new editmarks_form('regionmovepupil.php');

		$strsqlresults = "SELECT *  FROM {$CFG->prefix}monit_school_movepupil WHERE userid = $uid";
	    $gia_res = get_record_sql($strsqlresults);
	    
	    if (!empty($gia_res)) {
	        $editform->set_data($gia_res);
	    }

	    if ($editform->is_cancelled())	{
            redirect($redirlink, '', 0);
	    } else if ($data = $editform->get_data()) 	{

			// print_r($data); exit(0);
	    	$rec->userid  = $data->uid;
	    	$rec->county  = $data->cc;
	    	$rec->rayon  = $data->rr;
	    	$rec->naspunkt = $data->nn;
	    	$rec->school = $data->ss;
	    	$rec->class = $data->cl;
	    	$rec->rayoninid = $data->rid;
	    	$rec->schoolinid = $data->sid;
	    	$rec->classinid  = $data->gid;
	    	$rec->dateout  = $data->date;

	    	if (!empty($gia_res))	 {
			    $rec->id =  $gia_res->id;
		        if (update_record('monit_school_movepupil', $rec)) {		          
		            redirect($redirlink, get_string('giaresultupdated', 'block_mou_ege', $data->id), 0);
		        } else {
		            error('Error in update pupil record.');
		        }
		    } else {
	    	
	    	
		        if (!$newid = insert_record('monit_school_movepupil', $rec)) {
		            error('Error in insert pupil record.');
		            // redirect($redirlink, get_string('movingdone', 'block_mou_school', $newid), 0);
		        }
		    	
                $strsql = "SELECT id FROM {$CFG->prefix}monit_nsop_pupil where userid=$pupil->userid";
                if ($nsop = get_record_sql($strsql)) {
                    $pupil_card_u->id = $pupil->id;
                    $pupil_card_u->schoolid = 0;
                    $pupil_card_u->classid =  0;
                    $pupil_card_u->nsop =  1;
                    $pupil_card_u->typeemployment = 8;
                    update_record('monit_school_pupil_card', $pupil_card_u);
                    
                    $msg = get_string('leaveandnsop', 'block_mou_school', fullname($user, true));
            	    $msg .= '"' . mb_substr($schoolout->name, 0,  64) . '... "';
                    redirect($redirlink, $msg, 3);
                }
                
				 // find  classid  in ID_SCHOOL_FOR_DELETED school
			     if($class1 = get_record('monit_school_class', 'id', $gid)) {
					
				      $classname = $class1->name;
				      $id_specialschool = ID_SCHOOL_FOR_DELETED;
				      if($classes = get_records_sql("SELECT * FROM {$CFG->prefix}monit_school_class
				                                     WHERE yearid=$yid and schoolid=$id_specialschool")){

				           $pupil_card = get_record('monit_school_pupil_card', 'userid', $uid, 'yearid', $yid);                             	

				           foreach ($classes as $class) {
				              if ($classname == $class->name) {  
	                              $pupil_card_u->id = $pupil_card->id;
	                              $pupil_card_u->rayonid = 25;
	                              $pupil_card_u->schoolid = ID_SCHOOL_FOR_DELETED;
	                              $pupil_card_u->classid = $class->id;
	                              update_record('monit_school_pupil_card', $pupil_card_u);
	                              redirect($redirlink, $msg, 3);  
				              }
				           }	               
			               $rec->rayonid = 25; 
			               $rec->schoolid = ID_SCHOOL_FOR_DELETED;
			               $rec->yearid = $yid;
			               $rec->name = $classname;
			               $rec->parallelnum = $class1->parallelnum;
			               $rec->timecreated = time();
			               $rec->timeadded = time();
			               
						   if(!$newid = insert_record('monit_school_class', $rec)) {
								error('There are some errors in insert new class.', $redirlink);				               			
			               }     
				           $pupil_card_u->id = $pupil_card->id;
				           $pupil_card_u->rayonid = 25;
				           $pupil_card_u->schoolid = ID_SCHOOL_FOR_DELETED;
				           $pupil_card_u->classid = $newid;
				           update_record('monit_school_pupil_card', $pupil_card_u);
                           
                           $msg = get_string('leavededactivity', 'block_mou_school', fullname($user1, true));
                           $msg .= '"' . mb_substr($schoolout->name, 0,  64) . '... "';
	              		   redirect($redirlink, $msg, 3);
				       }
		          }
		    }
	    } else {
	        $editform->display();
	    }

    print_footer();
?>