<?php // $Id: movingpupils.php,v 1.5 2011/03/10 11:23:46 shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../../mou_att/lib_att.php');

    $rid = required_param('rid', PARAM_INT);       // Rayon id
    $sid = required_param('sid', PARAM_INT);       // School id
    $yid = optional_param('yid', 0, PARAM_INT);  // Year id
    $gid = optional_param('gid', 0, PARAM_INT);   // Class id
    $page    = optional_param('page', 0, PARAM_INT);
    $perpage = optional_param('perpage', 20, PARAM_INT);
    $namepupil = optional_param('namepupil', '');		// student lastname
    $action = optional_param('action', '');

	if ($yid == 0)	{
    	$yid = get_current_edu_year_id();;
    }


	$context = get_context_instance(CONTEXT_REGION, 1);
	 $strsql = "SELECT a.id, roleid, contextid, contextlevel, instanceid, path, depth  
				FROM mdl_role_assignments a	RIGHT JOIN mdl_context ctx ON a.contextid=ctx.id
			   WHERE userid={$USER->id}";
	  // echo $strsql . '<hr>';
	 if ($ctxs = get_records_sql($strsql))	{
	 		// print_r($ctxs);
			foreach($ctxs as $ctx1)	{
				if ($ctx1->contextlevel > 1000 && $ctx1->contextlevel <= CONTEXT_RAYON)	{
					$context = $ctx1;
					break;
				}
			}
	}				

	if (!has_capability('block/mou_school:editclasslist', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	
    
	$action   = optional_param('action', '');
	
    $strtitle = get_string('movingpupils','block_mou_school');
    $strclasses = get_string('classes','block_mou_ege');
	$strsearch = get_string("search");
	$searchtext = '';

	
	$breadcrumbs = '<a href="'.$CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('title','block_mou_school').'</a>';
	$breadcrumbs .= "-> $strtitle";
    print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

	print_heading(get_string('movingpupils','block_mou_school'), 'center', 3);
	
	echo '<div align=right><form name="pupilform" id="pupilform" method="post" action="movingpupils.php?action=lastname">'.
		 get_string('lastname'). '&nbsp&nbsp'.
 		 '<input type="hidden" name="rid" value="' . $rid. '" />'.
		 '<input type="hidden" name="sid" value="' . $sid. '" />'.
 	     '<input type="hidden" name="yid" value="' . $yid. '" />'.
		 '<input type="text" name="namepupil" size="10" value="' . $searchtext. '" />'.
	     '<input name="search" id="search" type="submit" value="' . $strsearch . '" />'.
		 '</form></div>';
	

    if (isset($action) && !empty($action)) 	{


	    if (isset($namepupil) && !empty($namepupil)) 	{
		     $searchpupil = $namepupil;
	         $pupilsql = "SELECT DISTINCT m.userid, u.firstname, u.lastname, u.id
	                       FROM {$CFG->prefix}user u
	                       LEFT JOIN {$CFG->prefix}monit_school_movepupil m ON m.userid = u.id
	                       WHERE u.lastname LIKE '$namepupil%'  AND deleted = 0 AND m.userid = u.id
	                       ORDER BY u.lastname, u.firstname";
	        $pupils = get_records_sql($pupilsql);
	        //print_r($pupils);
	    }
	   

        if(!empty($pupils)) {

	    	$table->head  = array (get_string('ordernumber', 'block_mou_school'),get_string('fullname'), get_string('rayon', 'block_mou_school'),
								get_string('school', 'block_mou_school'), get_string('class', 'block_mou_school'),
							    get_string('action'));
			
			$table->align = array ('center','left', 'left', 'left', 'center', 'center');			    
		    $table->size = array ('7%','27%', '26%', '37%', '7%', '7%');
			$table->columnwidth = array (7,25, 26, 35, 7,7);
	
			
	
	 		$i = $page*$perpage + 1;
	 		
				foreach($pupils as $pupil){
	

					if($pupil_card = get_record_sql("SELECT * FROM {$CFG->prefix}monit_school_pupil_card
													WHERE userid={$pupil->id} and yearid=$yid")){
	
						$rayon = get_record('monit_rayon','id',$pupil_card->rayonid);
						
						$school = get_record('monit_school','id', $pupil_card->schoolid);
						
						$class = get_record('monit_school_class','id', $pupil_card->classid);

						$title = get_string('history','block_mou_school');
					    $strlinkupdate = "<a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/mou_school/class/history.php?rid={$rayon->id}&amp;sid={$school->id}&amp;yid=$yid&amp;gid={$class->id}&amp;uid={$pupil->id}\">";
						$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/history.gif\" alt=\"$title\" /></a>&nbsp;";
	
	                    $table->data[] = array ($i++.'.', $pupil->lastname.' '.$pupil->firstname, 
												$rayon->name, $school->name,$class->name, $strlinkupdate);   
	
					}
					
				}	
			print_table($table);
		}

	} else {
			$all_who_in_movepupil = count_records_sql("SELECT count(distinct userid) FROM {$CFG->prefix}monit_school_movepupil");
		
			$table = table_movingpupils ($yid, $rid, $sid, $page, $perpage);
			
			print_paging_bar($all_who_in_movepupil, $page, $perpage, "movingpupils.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;perpage=$perpage&amp;");
			print_color_table($table);
			print_paging_bar($all_who_in_movepupil, $page, $perpage, "movingpupils.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;perpage=$perpage&amp;");	

	}
	print_footer();
    
    
    
function table_movingpupils ($yid, $rid, $sid, $page = '', $perpage = '')
{
		global $SITE, $USER, $CFG, $admin_is, $region_operator_is, $rayon_operator_is, $school_operator_is, $rayon;
									  
		$table->head  = array (get_string('ordernumber', 'block_mou_school'),get_string('fullname'), get_string('rayon', 'block_mou_school'),
								get_string('school', 'block_mou_school'), get_string('class', 'block_mou_school'),
							    get_string('action'));
							   
		$table->align = array ('center','left', 'left', 'left', 'center', 'center');
	    $table->size = array ('7%','27%', '26%', '37%', '7%', '7%');
		$table->columnwidth = array (7,25, 26, 35, 7,7);
	    $table->class = 'moutable';
	   	$table->width = '90%';

	    $table->titles = array();
	    $table->titles[] = get_string('movingpupils', 'block_mou_school');
	    $table->titlesrows = array(30);
	    $table->worksheetname = 'listclass';
	    $table->downloadfilename = 'movingpupils';
		

		if($all_who_in_movepupil = get_records_sql("SELECT DISTINCT userid FROM {$CFG->prefix}monit_school_movepupil ORDER BY dateout DESC", $page*$perpage, $perpage)){

 		$i = $page*$perpage + 1;
			foreach($all_who_in_movepupil as $who_in_movepupil){

				$user = get_record_sql("SELECT * FROM {$CFG->prefix}user
												WHERE id={$who_in_movepupil->userid} and deleted=0
												ORDER BY lastname");
				if($pupil_card = get_record_sql("SELECT * FROM {$CFG->prefix}monit_school_pupil_card
												WHERE userid={$who_in_movepupil->userid} and yearid=$yid")){

					$rayon = get_record('monit_rayon', 'id', $pupil_card->rayonid);
					
					$school = get_record('monit_school','id', $pupil_card->schoolid);
					
					$class = get_record('monit_school_class', 'id', $pupil_card->classid);

					$title = get_string('history','block_mou_school');
				    $strlinkupdate = "<a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/mou_school/class/history.php?rid={$rayon->id}&amp;sid={$school->id}&amp;yid=$yid&amp;gid={$class->id}&amp;uid={$user->id}\">";
					$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/history.gif\" alt=\"$title\" /></a>&nbsp;";

					$title_current = get_string('currentposition','block_mou_school');
					
	                $table->data[] = array ($i++.'.', $user->lastname.' '.$user->firstname . " (id=$user->id)", 
									$rayon->name,
									"<strong><a title=\"$title_current\" href=\"classlist.php?rid={$rayon->id}&amp;sid={$school->id}&amp;yid=$yid\">$school->name</a></strong>", 
									"<strong><a title=\"$title_current\" href=\"classpupils.php?rid={$rayon->id}&amp;sid={$school->id}&amp;yid=$yid&amp;gid={$class->id}\">$class->name</a></strong>", 
									$strlinkupdate);   

				}
				
			}
		}else 	{
    	$table->data[] = array ();
    }

        return $table;
}
    

?>