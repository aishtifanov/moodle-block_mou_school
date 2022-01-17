<?php // $Id: profiles.php,v 1.26 2012/02/21 06:34:41 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_school.php');
	require_once('../authbase.inc.php');

    if ($recs = data_submitted())  {
		// print_r($recs); echo '<hr>';
        // notice(get_string('succesavedata','block_monitoring'), "points.php?rid=0&amp;yid=$yid");

		if (!has_capability('block/mou_school:editprofiles', $context))	{
			error(get_string('permission', 'block_mou_school'), '../index.php');
		}	

		$redirlink = "profiles.php?sid=$sid&amp;yid=$yid&amp;rid=$rid";

		
        if (isset($recs->setstandart))	{
	 		if (!record_exists('monit_school_profiles_curriculum', 'schoolid', $sid))	{
	    		$newrec->schoolid = $sid;
	    		$newrec->name = get_string('standartprofile','block_mou_school');
	    		$newrec->profilenumlist = '0,1,2,3,4,5,6,7,8,9,10,11,12,0';
		        if (!$lastprofileid = insert_record('monit_school_profiles_curriculum', $newrec))	{
					error(get_string('errorinaddingprofile','block_mou_school'), $redirlink);
			    }
	 		} else {
	 			notice(get_string('profilelalreadyexists','block_mou_school'), $redirlink);	
	 		}	
		} else {
			
	        $plists = array();
	        $flagnum = false;
			foreach($recs as $fieldname => $profilename)	{
	
				if ($profilename != '')	{
		            $mask = substr($fieldname, 0, 4);
		            if ($mask == 'fld_')	{
		            	$ids = explode('_', $fieldname);
		            	$profileid = $ids[1];
		            	$class = $ids[2];
		            	if (isset($plists[$profileid]))	{
		            		$plists[$profileid] .= $class . ',';
		            	} else {
		            		$plists[$profileid] = $class . ',';
		          		}
		            } else if ($mask == 'num_')	{
		            	$flagnum = true;
		            	$ids = explode('_', $fieldname);
		            	$profileid = $ids[1];
		            	if (record_exists('monit_school_profiles_curriculum', 'id', $profileid, 'schoolid', $sid))	{
		           			set_field('monit_school_profiles_curriculum', 'name', $profilename, 'id', $profileid, 'schoolid', $sid);
		            	} else {
		            		$newrec->schoolid = $sid;
		            		$newrec->name = $profilename;
		            		$newrec->profilenumlist = 0;
					        if (!$lastprofileid = insert_record('monit_school_profiles_curriculum', $newrec))	{
								error(get_string('errorinaddingprofile','block_mou_school'), $redirlink);
						    }
							// print_r($newrec);  echo '<hr>';
		            	}
		            }
		        }
		    }
		    
		    if ($flagnum)	{
		    	// print_r ($plists); echo '<hr>';
		    	
			    foreach ($plists as $key => $pl)	{
			    	$plists[$key] .= '0';
			    	if ($key == 0)	{
			    		set_field('monit_school_profiles_curriculum', 'profilenumlist', '0,' . $plists[$key], 'id', $lastprofileid, 'schoolid', $sid);
			    	} else {
			    		set_field('monit_school_profiles_curriculum', 'profilenumlist', '0,' . $plists[$key], 'id', $key, 'schoolid', $sid);
			    	}
			    }
			    // print_r ($plists); echo '<hr>';	
		
		        // notice(get_string('succesavedata','block_mou_school'), $redirlink);
				redirect($redirlink, get_string('succesavedata','block_monitoring'), 0);
			} else {
				notify(get_string('notifynotname', 'block_mou_school'));
			}
		}		
	}


  	$currenttab = 'profiles';
    include('tabsup.php');

    $view_capability = has_capability('block/mou_school:viewprofiles', $context);
    $edit_capability = has_capability('block/mou_school:editprofiles', $context);

	if ($view_capability)	{
		echo  '<form name="profiles" method="post" action="profiles.php">';
		echo  '<input type="hidden" name="rid" value="' . $rid . '">';
		echo  '<input type="hidden" name="sid" value="' . $sid . '">';
		echo  '<input type="hidden" name="yid" value="' . $yid . '">';
		$table = table_profiles ($yid, $rid, $sid);
		print_color_table($table);
        if ($edit_capability)	{
    		echo  '<div align="center">';
    		echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '">';
    		echo  '<input type="submit" name="setstandart" value="'. get_string('setstandartvalue', 'block_mou_school') . '">';	
    		echo  '</div></form>';
        }  else {
            echo  '</form>';
        }  
    
	}	

    print_footer();


function table_profiles ($yid, $rid, $sid)
{
	global $CFG;
	
    $table->dblhead->head1  = array (get_string ('profiles','block_mou_school'), get_string ('parallels','block_mou_school'),
											      get_string('action', 'block_mou_school'));
    $table->dblhead->span1  = array ("rowspan=2", "colspan=$CFG->maxparallelnumber", "rowspan=2");
	$table->align = array ('left', 'center', 'center');
	$table->columnwidth = array (20);

    for ($i = 1; $i <= $CFG->maxparallelnumber; $i++)	{
	    $table->dblhead->head2[]  = $i;
		$table->align[] = 'center';
		$table->columnwidth[] = 10;
    }
    $table->class = 'moutable';
   	$table->width = '70%';

	$table->titles = array();
    $table->titles[] = get_string('profiles','block_mou_school');
    $table->worksheetname = $yid;

	$profiles = get_records_sql ("SELECT * FROM {$CFG->prefix}monit_school_profiles_curriculum
								  WHERE schoolid=$sid ORDER BY name");
	if ($profiles)	{

        foreach ($profiles as $profile) {

			$tabledata = array ("<input type=text  name=num_{$profile->id} size=45 value=\"$profile->name\">");

			$profilenumlist = explode(',', $profile->profilenumlist);

			for ($p = 1; $p <= $CFG->maxparallelnumber; $p++)	{
				$insidetable = '';
			  	if (in_array($p, $profilenumlist)) 	{
			  		$is = '+';
			  	    if ($existprofile = get_record_sql("SELECT DISTINCT parallelnum FROM {$CFG->prefix}monit_school_curriculum
														WHERE schoolid = $sid AND parallelnum = $p AND profileid = {$profile->id} "))	{
						$insidetable = "<img src=\"{$CFG->pixpath}/t/clear.gif\"/>";
						$insidetable .= "<input type=hidden name=fld_{$profile->id}_{$p} value='$is'>";
					} else{
						$insidetable = "<input type=checkbox checked size=1 name=fld_{$profile->id}_{$p} value='$is'>";
					}
				} else {
					$is = '-';
					$insidetable = "<input type=checkbox size=1 name=fld_{$profile->id}_{$p} value='$is'>";

				}
	           $tabledata[] = $insidetable;
			}

            $title = get_string('deleteprofile','block_mou_school');
			$strlinkupdate = "<a title=\"$title\" href=\"delcurriculum.php?part=p&amp;sid=$sid&amp;id={$profile->id}&amp;rid=$rid&amp;yid=$yid\">";
			$tabledata[] = $strlinkupdate . "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";

			$table->data[] = $tabledata;
        }
	}

	$tabledata = array ("<input type=text name=num_0 size=45 value=''>");

	for ($p = 1; $p <= $CFG->maxparallelnumber; $p++)	{
		$tabledata[] = "<input type=checkbox size=1 name=fld_0_{$p} value='-'>";
	}
	$tabledata[] = '';
	$table->data[] = $tabledata;

    return $table;
}

/*

function table_profiles ($yid, $rid, $sid)
{
	global $CFG;
	
    $table->dblhead->head1  = array (get_string ('profiles','block_mou_school'), 
									 get_string ('parallelnums','block_mou_school'),
									 get_string('action', 'block_mou_school'));

	$strsql = "SELECT id, name FROM {$CFG->prefix}monit_school_class 
			   WHERE schoolid=$sid AND yearid=$yid 
			   ORDER BY parallelnum, name";
	$countclass = 0;
	if ($classes = get_records_sql ($strsql))	{
		$countclass = count($classes);
	}	
											      
    $table->dblhead->span1  = array ("rowspan=2", "colspan=$countclass", "rowspan=2");
	$table->align = array ('left', 'center', 'center');
	$table->columnwidth = array (20);

    foreach ($classes  as $class)		{
	    $table->dblhead->head2[] = $class->name;
		$table->align[] = 'center';
		$table->columnwidth[] = 10;
	}	

    $table->class = 'moutable';
   	$table->width = '70%';

	$table->titles = array();
    $table->titles[] = get_string('parallelnums','block_mou_school');
    $table->worksheetname = $yid;

	$profiles = get_records_sql ("SELECT * FROM {$CFG->prefix}monit_school_profiles_curriculum
								  WHERE schoolid=$sid ORDER BY name");
	if ($profiles)	{

        foreach ($profiles as $profile) {

			$tabledata = array ("<input type=text  name=num_{$profile->id} size=45 value=\"$profile->name\">");

			$profilenumlist = explode(',', $profile->profilenumlist);
			
			$classlist = array();
			if (!empty($profile->classlist))	{
				$parals = explode (';', $profile->classlist); 
				foreach ($parals as $paral)	{
					$paral2 = explode (':', $paral);
					$parallelnum_index = $paral2[0];
					$classids = explode (',', $paral2[1]);
					foreach ($classids as $classid)	{
						$classlist[$parallelnum_index][$classid] = 1;			
					}
				}
			}

			for ($p = 1; $p <= $CFG->maxparallelnumber; $p++)	{
				$insidetable = '';
				$strsql = "SELECT id FROM {$CFG->prefix}monit_school_class
	 						WHERE schoolid=$sid AND yearid=$yid AND parallelnum=$p
							 ORDER BY name";
	 			
				// $is_many_classes = false; 			
				if ($classes = get_records_sql ($strsql))	{
					foreach ($classes as $class)	{
						if (isset($classlist[$p][$class->id]))	{
							if (record_exists("monit_school_curriculum", "schoolid = $sid and yearid = $yid AND profileid = {$profile->id} AND classid = $class->id"))	{
								$insidetable = "<img src=\"{$CFG->pixpath}/t/clear.gif\"/>";
								$insidetable .= "<input type=hidden name=fld_{$profile->id}_{$p}_{$class->id} value='+'>";
							}	else {
								$insidetable = "<input type=checkbox checked size=1 name=fld_{$profile->id}_{$p}_{$class->id} value='+'>";	
							}
							
						} else {
						  	if (in_array($p, $profilenumlist)) 	{
						  		$is = '+';
						  	    if ($existprofile = get_record_sql("SELECT DISTINCT parallelnum FROM {$CFG->prefix}monit_school_curriculum
																	WHERE schoolid = $sid and yearid = $yid AND
																	profileid = {$profile->id} AND parallelnum = $p"))	{
									$insidetable = "<img src=\"{$CFG->pixpath}/t/clear.gif\"/>";
									$insidetable .= "<input type=hidden name=fld_{$profile->id}_{$p} value='$is'>";
								} else{
									$insidetable = "<input type=checkbox checked size=1 name=fld_{$profile->id}_{$p} value='$is'>";
								}
							} else {
								$is = '-';
								$insidetable = "<input type=checkbox size=1 name=fld_{$profile->id}_{$p} value='$is'>";
			
							}

							// $insidetable = "<input type=checkbox size=1 name=fld_{$profile->id}_{$p}__{$class->id} value='-'>";
						}
						
						$tabledata[] = $insidetable;
					}
				}	
	           
			}

            $title = get_string('deleteprofile','block_mou_school');
			$strlinkupdate = "<a title=\"$title\" href=\"delcurriculum.php?part=p&amp;sid=$sid&amp;id={$profile->id}&amp;rid=$rid&amp;yid=$yid\">";
			$tabledata[] = $strlinkupdate . "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";

			$table->data[] = $tabledata;
        }
	}

	$tabledata = array ("<input type=text name=num_0 size=45 value=''>");

	for ($p = 1; $p <= $CFG->maxparallelnumber; $p++)	{
		$strsql = "SELECT id FROM {$CFG->prefix}monit_school_class
					WHERE schoolid=$sid AND yearid=$yid AND parallelnum=$p
					 ORDER BY name";
		
		// $is_many_classes = false; 			
		if ($classes = get_records_sql ($strsql))	{
			foreach ($classes as $class)	{
				$tabledata[] = "<input type=checkbox size=1 name=fld_0_{$p}_{$class->id} value='-'>";
			}
		}		
	}
	$tabledata[] = '';
	$table->data[] = $tabledata;

    return $table;
}
*/


?>