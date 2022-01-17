<?php // $Id: history.php,v 1.4 2010/08/23 08:47:58 Shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../../mou_att/lib_att.php');

    $rid = required_param('rid', PARAM_INT);       // Rayon id
    $sid = required_param('sid', PARAM_INT);       // School id
    $yid = optional_param('yid', 0, PARAM_INT);  // Year id
    $gid = optional_param('gid', 0, PARAM_INT);   // Class id
    $uid = required_param('uid', PARAM_INT);

    $namepupil = optional_param('namepupil', '');		// student lastname
    $action = optional_param('action', '');

	if ($yid == 0)	{
    	$yid = get_current_edu_year_id();;
    }

	$context = get_context_instance(CONTEXT_SCHOOL, $sid);
	if (!has_capability('block/mou_school:editclasslist', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

    $strtitle = get_string('history','block_mou_school');
    $strmoving = get_string('classes','block_mou_ege');
	$strsearch = get_string("search");
	$searchtext = '';

	$breadcrumbs = '<a href="'.$CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('title','block_mou_school').'</a>';
	$breadcrumbs .= '-> <a href="'.$CFG->wwwroot."/blocks/mou_school/class/movingpupils.php?rid=0&amp;sid=0\">".get_string('movingpupils','block_mou_school').'</a>';
	$breadcrumbs .= "-> $strtitle";
    print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);
	
	$user = get_record('user','id', $uid);
	print_heading($user->lastname.' '.$user->firstname, 'center', 3);

	$table = table_movingpupils ($yid, $rid, $sid, $uid);
	
	print_color_table($table);
		
	print_footer();
    
function table_movingpupils ($yid, $rid, $sid, $uid)
{
		global $SITE, $USER, $CFG, $admin_is, $region_operator_is, $rayon_operator_is, $school_operator_is, $rayon;
									  
		$table->head  = array (get_string('ordernumber', 'block_mou_school'),get_string('dateout', 'block_mou_school'), get_string('rayon', 'block_mou_school'),
								get_string('school', 'block_mou_school'), get_string('class', 'block_mou_school'),
							    get_string('action'));
							   
		$table->align = array ('center','left', 'left', 'left', 'center', 'center');
	    $table->size = array ('7%','20%', '30%', '40%', '7%', '7%');
		$table->columnwidth = array (7,25, 26, 35, 7,7);
	    $table->class = 'moutable';
	   	$table->width = '90%';

	    $table->titles = array();
	    $table->titles[] = get_string('movingpupils', 'block_mou_school');
	    $table->titlesrows = array(30);
	    $table->worksheetname = 'listclass';
	    $table->downloadfilename = 'movingpupils';
		
		if ($pupil_card = get_record_sql("SELECT * FROM {$CFG->prefix}monit_school_pupil_card
												WHERE userid=$uid and yearid=$yid")){
			$title_current = get_string('currentposition','block_mou_school');
			
			$rayon_current = get_record('monit_rayon','id',$pupil_card->rayonid);
				
			$school_current = get_record('monit_school','id', $pupil_card->schoolid);
							
			$class_current = get_record('monit_school_class','id', $pupil_card->classid);
			
			$table->data[] = array ('-', get_string('currentposition','block_mou_school'), $rayon_current->name, 
									"<strong><a title=\"$title_current\" href=\"classlist.php?rid={$rayon_current->id}&amp;sid={$school_current->id}&amp;yid=$yid\">$school_current->name</a></strong>",
									"<strong><a title=\"$title_current\" href=\"classpupils.php?rid={$rayon_current->id}&amp;sid={$school_current->id}&amp;yid=$yid&amp;gid={$class_current->id}\">$class_current->name</a></strong>", '');
			
			if($all_records_for_user = get_records_sql("SELECT * FROM {$CFG->prefix}monit_school_movepupil 
														WHERE userid=$uid
														ORDER BY dateout DESC")){
	 			$i = 1;
				foreach($all_records_for_user as $rec){
					
					if ($rec->rayoninid != 0 && $rec->schoolinid != 0 && $rec->classinid != 0){
						
						$rayon = get_record('monit_rayon','id',$rec->rayoninid);
						$school = get_record('monit_school','id', $rec->schoolinid);
						$class = get_record('monit_school_class','id', $rec->classinid);
						
						$title = get_string('delhistory','block_mou_school');
					    $strlinkupdate = "<a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/mou_school/class/delhistory.php?rid={$rayon->id}&amp;sid={$school->id}&amp;yid=$yid&amp;gid={$class->id}&amp;uid=$uid&amp;id={$rec->id}\">";
						$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
						
						$table->data[] = array ($i++.'.', convert_date($rec->dateout, 'en', 'ru'), $rayon->name, "<strong><a title=\"$title\" href=\"classlist.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">$school->name</a></strong>","<strong><a title=\"$title\" href=\"classpupils.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid={$class->id}\">$class->name</a></strong>",$strlinkupdate);
					} else {
						$title = get_string('delhistory','block_mou_school');
					    $strlinkupdate = "<a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/mou_school/class/delhistory.php?rid=0&amp;sid=0&amp;gid=0&amp;yid=$yid&amp;uid=$uid&amp;id={$rec->id}\">";
						$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
					
						$table->data[] = array ($i++.'.', convert_date($rec->dateout, 'en', 'ru'), $rec->county.'->'.$rec->rayon.'->'.$rec->naspunkt, $rec->school,$rec->class,$strlinkupdate);
					}	
				}
			}else 	{
				
	    	$table->data[] = array ();
	    	}													
		}

        return $table;
}
?>