<?php // $Id: classdisciplines.php,v 1.23 2012/02/13 10:32:23 shtifanov Exp $
//
    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../../mou_att/lib_att.php');
    require_once('../lib_school.php');
	require_once('../authbase.inc.php');    

    $gid = optional_param('gid', 0, PARAM_INT);   // Class id
	$did = optional_param('did', 0, PARAM_INT);		//Discipline id

	if ($gid != 0)  {
		$context_class = get_context_instance(CONTEXT_CLASS, $gid);
		$edit_capability_class = has_capability('block/mou_school:editclasslist', $context_class);
	}		

	$edit_capability = has_capability('block/mou_school:editclasslist', $context);

    switch ($action)  {
    	case 'excel': $table = table_classdisciplines ($rid, $sid, $yid, $gid , $action);
        			  print_table_to_excel($table, 1);
        			  exit();
		case 'chkcrt':
					  checkandcreate_classdisciplines ($rid, $sid, $yid, $gid);
					  break;
	}		

    if ($recs = data_submitted())  {
   		if (!$edit_capability && !$edit_capability_class)	{
			error(get_string('permission', 'block_mou_school'), '../index.php');
		}		
		// print_r($recs); echo '<hr>';
        // notice(get_string('succesavedata','block_monitoring'), "points.php?rid=0&amp;yid=$yid");
		$redirlink = "classdisciplines.php?rid=$rid&amp;yid=$yid&amp;gid=$gid&amp;sid=$sid";
		$role_predmetnik= get_record('role', 'shortname', 'predmetnik');
		
		foreach($recs as $fieldname => $teacherid)	{
			if ($teacherid != '')	{
   				$mask = substr($fieldname, 0, 2);
		        if ($mask == 't_')	{
         			$ids = explode('_', $fieldname);
					$uprec->id = $ids[1];
					$uprec->teacherid = $teacherid;
					
					$predmet = get_record('monit_school_class_discipline', 'id', $uprec->id);
					$ctx = get_context_instance(CONTEXT_DISCIPLINE, $predmet->id);
					if ($uprec->teacherid != $predmet->teacherid) { 
			     		if (!role_unassign_mou($role_predmetnik->id, $predmet->teacherid, $ctx->id))	{
	    					notify("Not unassigned PREDMETNIK {$predmet->teacherid}.");
			    		}

		     			if (!role_assign_mou($role_predmetnik->id, $uprec->teacherid, $ctx->id))	{
							notify("Not assigned PREDMETNIK {$uprec->teacherid}.");
				    	}
				    	
    					$role_sotrudnik = get_record('role', 'shortname', 'sotrudnik');
					    $ctx = get_context_instance(CONTEXT_SCHOOL, $sid);
	    		 		if (!role_assign_mou($role_sotrudnik->id, $uprec->teacherid, $ctx->id))	{
							notify("Not assigned SOTRUDNIK {$uprec->teacherid}.");
			    		}

					}
					
					if (update_record('monit_school_class_discipline', $uprec))	{
							 // add_to_log(1, 'school', 'discipline update', $redirlink, $USER->lastname.' '.$USER->firstname);
							 // notice(get_string('curriculumupdate','block_school'), $redirlink);
					} else {
						    print_r($uprec);
							error(get_string('errorinupdatingcurr','block_mou_school'), $redirlink);
					}
				}	
			}	
		}	
	}


    $currenttab = 'classdisciplines';
    include('tabsclasses.php');

	if (has_capability('block/mou_school:viewclasslist', $context))	{
		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		listbox_class("classdisciplines.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=", $rid, $sid, $yid, $gid);
		echo '</table>';

		if ($gid != 0)  {
			
			$context_class = get_context_instance(CONTEXT_CLASS, $gid);
			$edit_capability_class = has_capability('block/mou_school:editclasslist', $context_class);

			/* $table = table_classdisciplines_curriculum ($rid, $sid, $yid, $gid);
			print_color_table($table); */
			// if ($admin_is || $region_operator_is || ($rayon_operator_is == $rayon->id)) 	{
		   	
			echo  '<form name="classdisciplines" method="post" action="classdisciplines.php">';
			echo  '<input type="hidden" name="rid" value="' . $rid . '">';
			echo  '<input type="hidden" name="sid" value="' . $sid . '">';
			echo  '<input type="hidden" name="yid" value="' . $yid . '">';
			echo  '<input type="hidden" name="gid" value="' . $gid . '">';
			$table = table_classdisciplines ($rid, $sid, $yid, $gid);
			print_color_table($table);
			echo  '<div align="center">';
 			if ($edit_capability || $edit_capability_class)	{
				echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '">';
			}	
			echo  '</div></form><hr>';

			if ($edit_capability || $edit_capability_class)	{
		   		$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'gid' => $gid, 'action' => 'excel');
				echo '<table align="center" border=0><tr><td>';
				print_single_button("classdisciplines.php", $options, get_string("downloadexcel"));
			   	echo '</td><td>';
		   		$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'gid' => $gid, 'action' => 'chkcrt');
				print_single_button("classdisciplines.php", $options, get_string('checkandcreatdisc', 'block_mou_school'));
			   	echo '</td></tr></table>';
			}   	
		}   	
	   	
    }

    print_footer();


function table_classdisciplines ($rid, $sid, $yid, $gid, $action = '')
{
	global $CFG, $edit_capability, $edit_capability_class;

	$table->head  = array (get_string("ordernumber","block_mou_school"),
							get_string("predmet","block_mou_school"), get_string("teacher","block_mou_school"),
						   get_string("hours","block_mou_school"), get_string("action","block_mou_school"));
	$table->align = array ('center', 'left', 'left', 'center', 'center');
    $table->size = array ('5%', '20%', '15%', '7%', '5%');
	$table->columnwidth = array (7, 40, 30, 7, 7);
    $table->class = 'moutable';
   	$table->width = '90%';
    $table->titles = array();
    $table->titles[] = get_string('class', 'block_mou_school');
    $table->titlesrows = array(30);
    $table->downloadfilename = 'class_'. $sid . '_' . $gid;
    $table->worksheetname = $table->downloadfilename;

	$class = get_record ('monit_school_class', 'id', $gid);
	// print_heading(get_string('class', 'block_mou_ege') . ': '. $classes->name, "center", 3);
	if ($class)	{

		$droupid = array();
		$disciplinehours = array();

		$strsql = "SELECT id, schoolid, classid, schoolsubgroupid, disciplineid, teacherid, name 
                   FROM {$CFG->prefix}monit_school_class_discipline
				   WHERE  classid=$gid
				   ORDER BY name";
		// echo $strsql; 							
									
		if ($classdisciplines = get_records_sql ($strsql))	{
			$num = 1;
			foreach ($classdisciplines as $classdiscipline) {
				$strdiscipline = $strteacher = '-';  
				$strhours = 0;
				if ($discipline = get_record_select ('monit_school_discipline', "id = $classdiscipline->disciplineid", 'id, schoolid, disciplinedomainid, dgroupid, name, shortname'))	{
					$strdiscipline = $discipline->name;
					
					if ($action == 'excel') { 
						
						if (!empty($classdiscipline->teacherid))	{
							$teacher = get_record_select ('user', "id = $classdiscipline->teacherid", 'id, lastname, firstname');
							$strteacher	= fullname ($teacher);
						}
						
					} else {	
						$teachermenu = array();
				  	 	$teachers = get_records_sql("SELECT id, schoolid, teacherid, disciplineid
                                                     FROM {$CFG->prefix}monit_school_teacher
			   	 	 								 WHERE schoolid=$sid AND disciplineid={$discipline->id}");
			        	if ($teachers)  {
		        	  	  $teachermenu[0] = get_string('selectateacher', 'block_mou_school') . ' .......................................................................................';
			              foreach ($teachers as $teach)  {
			            	    $user=get_record_sql("SELECT id, lastname, firstname, email FROM {$CFG->prefix}user
			              							  WHERE id={$teach->teacherid}");
				           		$teachermenu[$teach->teacherid] = fullname($user) . " ($user->email)";
			              }
			              $strteacher = choose_from_menu ($teachermenu, "t_{$classdiscipline->id}", $classdiscipline->teacherid, '', "", "", true);
			            } else {
			           	  $strteacher = '<a href = "' . $CFG->wwwroot."/blocks/mou_school/curriculum/discipteachers.php?rid=$rid&amp;yid=$yid&amp;sid=$sid" . '">' . get_string('notassignteacher', 'block_mou_school') . '</a>';
			            }
						/*	  
			        	} else {
							$teacher = get_record ('user', 'id', $classdiscipline->teacherid);
					    	$strteacher	= fullname ($teacher);
				        }
				        */
				     }   
					
					
					if ($curriculums = get_records_select('monit_school_curriculum', "(classid = $gid) AND (disciplineid = {$discipline->id})"))  {
						foreach($curriculums as $curriculum)	{
							$strhours += $curriculum->hours;						
						}
					}
				} else {
					// print_r($classdiscipline); echo '<hr>';
					notify(get_string('discnotfoundinschool', 'block_mou_school', $num . '. ' . $classdiscipline->name));
					// continue; 
				}
					
//				if ($admin_is || $region_operator_is) 	{
					/*$title = get_string('editdiscipline','block_mou_school');
					$strlinkupdate = "<a title=\"$title\" href=\"editdiscipline.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;cdid={$classdiscipline->id}&amp;did={$discipline->id}\">";
					$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";
					*/
				$strlinkupdate = '';	
				if ($edit_capability)	{	
					if ($ctx = get_record('context', 'contextlevel', CONTEXT_DISCIPLINE, 'instanceid', $classdiscipline->id)) {
						$title = get_string('assignroles','role');
					    $strlinkupdate .= "<a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/mou_school/roles/assign.php?contextid={$ctx->id}\">";
						$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/roles.gif\" alt=\"$title\" /></a>&nbsp;";
					}	

					$title = get_string('deletediscipline','block_mou_school');
				    $strlinkupdate .= "<a title=\"$title\" href=\"deldiscipline.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;cdid={$classdiscipline->id}&amp;did={$discipline->id}\">";
					$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
				} else {
					$strlinkupdate = '-';
					if (!$edit_capability_class)	{
		           		if (!empty($classdiscipline->teacherid))	{
							$teacher = get_record_select ('user', "id = $classdiscipline->teacherid", 'id, lastname, firstname');
						    $strteacher	= fullname ($teacher);
					    } else {
							$strteacher = '-';				    	
					    }
					}	 	
				}	

/*				}
					// $strgroup = "<strong><a href=$CFG->wwwroot/blocks/school/curr_courses.php?fid=$fid&amp;sid=$sid&amp;cid={$curr->id}>$curr->name</a></strong>";
				else	{
					$strlinkupdate = '-';
				}
*/				
				if ($discipline->dgroupid == 0) { 
					$disciplinehours[$discipline->id] = $strhours;
	   			} else {
	   				if (isset($droupid[$discipline->dgroupid]))	{
	   					if ($droupid[$discipline->dgroupid] < $strhours)	{
	   						$droupid[$discipline->dgroupid] = $strhours;	
	   					} 	
	   				}  else {
	   					$droupid[$discipline->dgroupid] = $strhours; 
	   				}
	   			} 												
				
					
				$table->data[] = array ($num, $classdiscipline->name, $strteacher, $strhours, $strlinkupdate);

				$num++;
			}
			
			$sumhours = 0;
			foreach ($disciplinehours as $dhour)	{
				$sumhours += $dhour;
			} 
			foreach ($droupid as $dhour)	{
				$sumhours += $dhour;
			}  
			$table->data[] = array ('', '<b>'.get_string("allhoursweek","block_mou_school").'</b>', '', '<b>'.$sumhours.'</b>', '');
		}

    }

    return $table;
}

function table_classdisciplines_curriculum ($rid, $sid, $yid, $gid)
{
	global $CFG, $admin_is, $region_operator_is, $rayon_operator_is, $school_operator_is;


	$table->head  = array (get_string("ordernumber","block_mou_school"),
							get_string("predmet","block_mou_school"), get_string("teacher","block_mou_school"),
						   get_string("profile","block_mou_school"), get_string("components","block_mou_school"),
						   get_string("hours","block_mou_school"),
						   get_string("action","block_mou_school"));
	$table->align = array ('center', 'left', 'left', 'left', 'left', 'left', 'center');
    $table->size = array ('5%', '10%', '20%', '15%', '12%', '7%', '10%',);
	$table->columnwidth = array (5, 10, 10, 15, 15, 10, 9);
    // $table->datatype = array ('char', 'char');
    $table->class = 'moutable';
   	$table->width = '90%';
    // $table->size = array ('10%', '10%');
    $table->titles = array();
    $table->titles[] = get_string('class', 'block_mou_school');
    $table->worksheetname = 'class';


	$class = get_record ('monit_school_class', 'id', $gid);
	// print_heading(get_string('class', 'block_mou_ege') . ': '. $classes->name, "center", 3);
	if ($class)	{
			
		$sumhours = 0;

		$curriculums = get_records_sql ("SELECT id, parallelnum, yearid, schoolid, classid, componentid, profileid, disciplineid, hours
                                         FROM {$CFG->prefix}monit_school_curriculum
									     WHERE classid=$gid and yearid=$yid");
		if ($curriculums)	{
			$num = 0;
			foreach ($curriculums as $curriculum) {
				$strdiscipline = $strprofile = $strteacher = $strcomponent = '-';
				if ($discipline = get_record ('monit_school_discipline', 'id', $curriculum->disciplineid))	{
					$strdiscipline = $discipline->name;
				}
				if ($profile = get_record ('monit_school_profiles_curriculum', 'id', $curriculum->profileid))		{
					$strprofile = $profile->name;
				}
				if($component = get_record ('monit_school_component', 'id', $curriculum->componentid))	{
					$strcomponent = $component->name;
				}
				if ($idteacher = get_record ('monit_school_teacher', 'id', $curriculum->disciplineid))	{
					$teacher = get_record ('user', 'id', $idteacher->teacherid);
					$strteacher	= fullname ($teacher);
				}
				
				if ($admin_is || $region_operator_is) 	{
					$title = get_string('editdiscipline','block_mou_school');
					$strlinkupdate = "<a title=\"$title\" href=\"editdiscipline.php?mode=edit&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid\">";
					$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";
					$title = get_string('deletediscipline','block_mou_school');
				    $strlinkupdate .= "<a title=\"$title\" href=\"deldiscipline.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid\">";
					$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
				}
					// $strgroup = "<strong><a href=$CFG->wwwroot/blocks/school/curr_courses.php?fid=$fid&amp;sid=$sid&amp;cid={$curr->id}>$curr->name</a></strong>";
				else	{
					$strlinkupdate = '-';
				}
				$sumhours += $curriculum->hours;
				$num++;

				$table->data[] = array ($num, $discipline->name, $strteacher, $strprofile, $strcomponent, $curriculum->hours, $strlinkupdate);
			}
			$table->data[] = array ('', '<b>'.get_string("allhoursweek","block_mou_school").'</b>', '', '', '', '<b>'.$sumhours.'</b>', '');
		}

    }

    return $table;
}


function checkandcreate_classdisciplines ($rid, $sid, $yid, $gid)
{
	global $CFG, $admin_is, $region_operator_is, $rayon_operator_is, $school_operator_is;
/*
	if (record_exists('monit_school_class_discipline', 'classid', $gid, 'schoolid', $sid))	{
		return false;
	} else {
*/		
		$curriculums = get_records_select ('monit_school_curriculum', "yearid = $yid AND classid= $gid AND schoolid = $sid");
		$classdisciplinesname = $classdisciplinesid = $classdisciplinessubid = array();
		foreach ($curriculums as $curriculum)	{
			if ($discipline = get_record('monit_school_discipline', 'id', $curriculum->disciplineid))  {
                   $strnamedisc = $discipline->name;
                   if ($discipline->dgroupid != 0)	{
                   		$dgroup = get_record('monit_school_discipline_group', 'id', $discipline->dgroupid);
                   		$strnamedisc = $dgroup->name . ':' . $strnamedisc;
                   }

                   if ($subgroups = get_records_select('monit_school_subgroup', "schoolid=$sid AND disciplineid={$discipline->id}"))   {
                   		foreach ($subgroups as $subgroup){
		                	$classdisciplinesname[] = $strnamedisc . '>' . $subgroup->name;
		                	$classdisciplinesid[] = $discipline->id;
		                	$classdisciplinessubid[] =  $subgroup->id;
		               	}
		           }  else {
		                	$classdisciplinesname[] = $strnamedisc;
		                	$classdisciplinesid[] = $discipline->id;
		                	$classdisciplinessubid[] = 0;
		           }
		    }
		}

		foreach ($classdisciplinesname as $key => $name)	{
			
			if ($exist_class_discipline = get_record_select('monit_school_class_discipline', "classid=$gid AND schoolsubgroupid = {$classdisciplinessubid[$key]} AND disciplineid = {$classdisciplinesid[$key]}"))	{
				if ($name !== $exist_class_discipline->name)	{
					set_field('monit_school_class_discipline', 'name', $name, 'id', $exist_class_discipline->id);
				}
			} else  {
				$newrec->schoolid = $sid;
				$newrec->classid = $gid;
				$newrec->schoolsubgroupid = $classdisciplinessubid[$key];
				$newrec->disciplineid = $classdisciplinesid[$key];
				$newrec->name = $name;
				$newrec->descriptions = '';
				$newrec->teacherid = 0;
				if (count_records_select('monit_school_teacher', "schoolid=$sid AND disciplineid={$newrec->disciplineid}") == 1)	{
					$teacher = get_record_select('monit_school_teacher', "schoolid=$sid AND disciplineid={$newrec->disciplineid}");
					$newrec->teacherid = $teacher->teacherid;
				}
				
		        if (!$lastid = insert_record('monit_school_class_discipline', $newrec))	{
					error(get_string('errorinaddingclassdiscipline','block_mou_school'), $CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid");
				}
				
				if ($newrec->teacherid > 0)	{
			
					$role_predmetnik= get_record('role', 'shortname', 'predmetnik');
					$ctx = get_context_instance(CONTEXT_DISCIPLINE, $lastid);
	     			if (!role_assign_mou($role_predmetnik->id, $newrec->teacherid, $ctx->id))	{
						notify("Not assigned PREDMETNIK {$newrec->teacherid}.");
			    	}
				    	
    				$role_sotrudnik = get_record('role', 'shortname', 'sotrudnik');
					$ctx = get_context_instance(CONTEXT_SCHOOL, $sid);
		 			if (!role_assign_mou($role_sotrudnik->id, $newrec->teacherid, $ctx->id))	{
						notify("Not assigned SOTRUDNIK {$newrec->teacherid}.");
			    	}
					
				}
			}	
		}
	return true;
//	}
}
?>
