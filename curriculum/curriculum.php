<?php // $Id: curriculum.php,v 1.25 2012/02/21 06:34:41 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_school.php');
	require_once('../authbase.inc.php');


    $pid = optional_param('pid', '0', PARAM_INT);       // Profile id
	
    
   /* 
	if ($action == 'excel') 	{
		$table = print_curriculum_table ($yid, $pid, $sid);
    	// print_r($table);
        print_table_to_excel($table);
        exit();
	}
	*/


  	if ($recs = data_submitted())  {
		// print_r($recs); echo '<hr>';

		if (!has_capability('block/mou_school:editprofiles', $context))	{
			error(get_string('permission', 'block_mou_school'), '../index.php');
		}	

		foreach($recs as $name => $hours)	{
			if ($hours != '')	{
	            $mask = substr($name, 0, 4);
	            if ($mask == 'num_')	{
	            	$ids = explode('_', $name);
	            	$componentid = $ids[1];
	            	$classid = $ids[2];
	            	$discipline = $ids[3];
	            	$strsql = "SELECT id FROM {$CFG->prefix}monit_school_curriculum
							   WHERE componentid=$componentid AND classid=$classid 
							   		AND disciplineid=$discipline AND profileid=$pid"; 	 
	            	if ($curriculum = get_record_sql($strsql))	{
	            			set_field('monit_school_curriculum', 'hours', $hours, 'id', $curriculum->id);
	            	}
	            }
	        }
		}
        notice(get_string('succesavedata','block_monitoring'), "curriculum.php?sid=$sid&amp;rid=$rid&amp;yid=$yid&amp;pid=$pid");
	}



	$currenttab = 'curriculum';
    include('tabsup.php');

    $view_capability = has_capability('block/mou_school:viewprofiles', $context);
    $edit_capability = has_capability('block/mou_school:editprofiles', $context);

	if ($view_capability)	{

		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		listbox_profiles("curriculum.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=",$sid, $pid);
		echo '</table>';
	
	    if ($pid !=0)	{
			echo  '<form name="curriculum" method="post" action="curriculum.php">';
			echo  '<input type="hidden" name="rid" value="' . $rid . '">';
			echo  '<input type="hidden" name="sid" value="' . $sid . '">';
			echo  '<input type="hidden" name="yid" value="' . $yid . '">';
			echo  '<input type="hidden" name="pid" value="' . $pid . '">';
			print_curriculum_table($sid, $yid, $pid);
            if ($edit_capability)	{
    			echo  '<div align="center">';
    			echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '"></div>';
    			echo  '</form>';
            }  else {
                echo  '</form>';
            }
                
			/*
			$options = array('pid' => $pid, 'sid' => $sid, 'yid' => $yid, 'rid' => $rid);
			echo '<table align="center" border=0>';
		    print_single_button("excel.php", $options, get_string("downloadexcel"));
			echo '</table>';
			*/
		}
	}	

    print_footer();



function print_curriculum_table($sid, $yid, $pid)
{
	global $CFG, $rid;

    // ???????????? ?????????????????? ??????????????
    $table->tablealign = 'center';
    $table->cellpadding = '5';
    $table->cellspacing = '1';
    $table->headerstyle = 'header';
    $table->border = '1';
    $table->class = 'moutable';
   	$table->width = '100%';
	
    // ?????????????????? ?????????? ??????????????
    $table->head[1]  = array (get_string ('obrazovatobl','block_mou_school'),
    								 get_string ('predmet','block_mou_school'),
    								 get_string ('parallels','block_mou_school'));
    $table->span[1]  = array ("rowspan=3", "rowspan=3");
	$table->align[1] = array ('center', 'center', 'center');
    // ???????????????????? ?????????? ???????????????????? ???????????????????? ?? ?????????? ?????????????????? ???????????????????????? ?? ?????????????? ??????????
    $pnum = array();
    for ($p = 1; $p <= $CFG->maxparallelnumber; $p++)	{
    	if (record_exists_select('monit_school_curriculum', "parallelnum = $p AND schoolid = $sid AND profileid = $pid")) 	{
    			$pnum[] = $p;
    	}
    }
    if (empty($pnum))	{
    	notice(get_string('notsetdiscipline', 'block_mou_school'), "setdiscipline.php?sid=$sid&amp;rid=$rid&amp;yid=$yid&amp;pid=$pid");
    }

	$clasessidsunsort = array();
	$clasessids = array();
    foreach ($pnum as $pn)	{
    	$table->head[2][$pn]  = $pn;
    	$table->align[2][$pn] = 'center';
    	$strsql = "SELECT DISTINCT classid  FROM {$CFG->prefix}monit_school_curriculum
	  			   WHERE schoolid = $sid AND parallelnum = $pn AND profileid = $pid";
	  	// echo $strsql . '<hr>'; 			
   		if ($clasessidsunsort[$pn] = get_records_sql ($strsql)) {
			$listid = '';
	    	foreach ($clasessidsunsort[$pn] as $clasu)	{
	    		if (record_exists('monit_school_class', 'id', $clasu->classid))	{
	    			$listid .= $clasu->classid . ',';
	    		}	
	    	}
	    	$listid .= '0';
	    	$strsql = "SELECT id as classid, name  FROM {$CFG->prefix}monit_school_class
		  			   WHERE id in ($listid)
				  	   ORDER BY name";
		  	// echo $strsql . '<hr>'; 			
	   		$clasessids[$pn] = get_records_sql ($strsql);
			$table->span[2][$pn] = 'colspan=' . count ($clasessids[$pn]);
			// print_r($clasessidsunsort[$pn]); echo $table->span[2][$pn] . '<hr>';
			// sortiruem
		}
    }
    
    unset($clasessidsunsort);
	// print_r($clasessids); echo '<hr>';//  exit(0);
    $countclass = 0;
    foreach ($clasessids as $clasessid)		{
    	// print_r($clasessid); echo '<hr>';
    	foreach ($clasessid as $class)	{
    		// $class = get_record('monit_school_class', 'id', $clas->classid);
    		$countclass++;
    		$table->head[3][]  = $class->name;
			$table->align[3][] = 'center';
			$table->columnwidth[3][] = 10;
    	}
    }
    $table->span[1][] = 'colspan=' . $countclass;

    //  ?????????????? ?????????? ??????????????
    print_curriculum_table_header($table);

    // ???????????????????????? ???????? ??????????????
    $table->dataplan = array ();
	foreach ($pnum as $pn)	{
	    if ($rec = get_record_sql("SELECT  hourstotal FROM {$CFG->prefix}monit_school_curriculum_totals
						WHERE componentid = 0 and schoolid=$sid AND parallelnum = $pn"))	{
			$is = $rec->hourstotal;
		} else {
			$is = '-';
		}
		$table->dataplan[$pn] = $is;
	}

	$table->datafact = array ();
	foreach ($clasessids as $clasessid)		{
    	foreach ($clasessid as $clas)	{
			$rec = get_record_sql("SELECT sum(hours) as sum FROM {$CFG->prefix}monit_school_curriculum
								   WHERE schoolid=$sid AND profileid = $pid and classid = {$clas->classid}");
			$table->datafact[$clas->classid] = round($rec->sum);
        }
    }
	$table->titlerow = '<b><i>'.get_string('limitnagruzka','block_mou_school').'</b></i>';
	print_component_row($table);

	$components = get_records_sql ("SELECT *  FROM {$CFG->prefix}monit_school_component
								    WHERE schoolid=$sid 
									ORDER BY id");

	if ($components)	{
	    // ?????????????????????????? ????????????????
        $itogplan = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        $itogfact = array();
        foreach ($clasessids as $clasessid)
        	foreach ($clasessid as $clas)
        		$itogfact[$clas->classid] = 0;
        // ???????? ???? ??????????????????????
        foreach ($components as $component) {
            $table->dataplan = array ();
			foreach ($pnum as $pn)	{
			    if ($i = get_record_sql("SELECT  hourstotal FROM {$CFG->prefix}monit_school_curriculum_totals
								WHERE componentid = {$component->id} and schoolid=$sid AND parallelnum = $pn"))	{
					$is = $i->hourstotal;
					$itogplan[$pn] += $is;
				} else {
					$is = '-';
				}
				$table->dataplan[$pn] = $is;
			}
			$table->datafact = array ();
			
			foreach ($clasessids as $clasessid)		{
		    	foreach ($clasessid as $clas)	{
		    		/*
					$rec = get_record_sql("SELECT sum(hours) as sum FROM {$CFG->prefix}monit_school_curriculum
										   WHERE componentid = {$component->id} and schoolid=$sid AND profileid = $pid and classid = {$clas->classid}");
					$table->datafact[$clas->classid] = round($rec->sum);
					$itogfact[$clas->classid] += $rec->sum;
					*/
					$clasdisciplines = get_records_sql("SELECT id, disciplineid, hours FROM {$CFG->prefix}monit_school_curriculum
										   				WHERE componentid = {$component->id} AND classid = {$clas->classid} AND profileid = $pid");
					$datafact=0;
					if ($clasdisciplines)	{
						$droupid = array();
						foreach ($clasdisciplines as $clasdiscipline)	{
							$discipline = get_record_sql("SELECT id, dgroupid FROM {$CFG->prefix}monit_school_discipline
               												WHERE id={$clasdiscipline->disciplineid}");
               				if ($discipline->dgroupid == 0) { 
               					$datafact += $clasdiscipline->hours;
				   			} else {
				   				if (isset($droupid[$discipline->dgroupid]))	{
				   					if ($droupid[$discipline->dgroupid] < $clasdiscipline->hours)	{
				   						$droupid[$discipline->dgroupid] = $clasdiscipline->hours;	
				   					} 	
				   				}  else {
				   					$droupid[$discipline->dgroupid] = $clasdiscipline->hours; 
				   				}
				   			} 								
						}
						foreach ($droupid as $dhour)	{
							$datafact += $dhour;
						}  
					}
					$table->datafact[$clas->classid] = $datafact;
					$itogfact[$clas->classid] += $datafact;
										   
		        }
		    }

			$table->titlerow = '<b><i>'.$component->name.'</b></i>';
			print_component_row($table);

			$discip_domains = get_records_sql ("SELECT id, schoolid, name  FROM {$CFG->prefix}monit_school_discipline_domain
									    		WHERE schoolid=$sid
									    		ORDER BY name");

			if ($discip_domains)	{

                // ???????? ???? ?????????????????????????????? ????????????????
				foreach ($discip_domains as $dsm) {

              		$flag = true;
               		if ($disciplines = get_records_sql("SELECT id, name FROM {$CFG->prefix}monit_school_discipline
               											WHERE schoolid=$sid AND disciplinedomainid={$dsm->id}
               											ORDER BY name"))  {

	               		foreach ($disciplines as $disc) 	{
	               			$datarow = array();
							foreach ($clasessids as $clasessid)		{
						    	foreach ($clasessid as $clas)	{
	                  				if ($curr = get_record_select('monit_school_curriculum', "classid = {$clas->classid} AND disciplineid = {$disc->id} AND profileid = $pid AND componentid = {$component->id} ", 'id, hours')) 	{
				  						$datarow[$clas->classid]  = "<input type=text name=num_{$component->id}_{$clas->classid}_{$disc->id} size=2 MAXLENGTH=4 value={$curr->hours}>";
				  					}
				  				}
			  				}

	                        if (!empty($datarow))	{

			                    if ($flag) {
			                    	$strdsm = $dsm->name;
			                    	$flag = false;
			                    } else {
			                     	$strdsm = '';
			                    }

	                            $table->datarow = array ("<i>$strdsm</i>", $disc->name);
								foreach ($clasessids as $clasessid)		{
							    	foreach ($clasessid as $clas)	{
							    		if (isset($datarow[$clas->classid]))  {
							    			$table->datarow[] = $datarow[$clas->classid];
		                  				} else {
		                  					$table->datarow[] = '';
		                  				}
					  				}
				  				}
				  				print_discipline_row($table);
	  		  				}
						}
					}
				}
			}
			print_curriculum_table_insideheader($table);
		}

	}

	$table->dataplan = array();
	foreach ($pnum as $pn)	{
        $table->dataplan[$pn] =  $itogplan[$pn];
	}
	$table->datafact = $itogfact;
	$table->titlerow = '<b><i>'.get_string('allhoursweek','block_mou_school').'</b></i>';
	print_component_row($table);

    //  ?????????????????? ??????????????
    print_curriculum_table_footer();

    return true;

}

function print_curriculum_table_header($table)
{
	global $CFG;

    print_simple_box_start_old('center', $table->width, '#ffffff', 0);
    echo "\n".'<table width="100%" border=1 align=center ';
    echo " cellpadding=\"$table->cellpadding\" cellspacing=\"$table->cellspacing\" class=\"$table->class\">\n"; //bordercolor=gray

    foreach ($table->head as $index => $tablehead) {

        echo '<tr>'."\n";
        foreach ($tablehead as $key => $heading) {

            if (isset($table->size[$index][$key])) {
                $size[$index][$key] = $table->size[$index][$key];
            } else {
                $size[$index][$key] = '';
            }

            if (isset($table->align[$index][$key])) {
                $align[$index][$key] = $table->align[$index][$key];
            } else {
            	$align[$index][$key] = '';
            }

            if (isset($table->wraphead) && $table->wraphead == 'nowrap') {
            	$headwrap = ' nowrap="nowrap" ';
            } else 	{
            	$headwrap = '';
            }

            if (isset($table->span[$index][$key])) {
            	$span[$index] = $table->span[$index][$key];
            } else 	{
            	$span[$index] = '';
            }

            echo "<th " . $span[$index] . ' '. $align[$index][$key].$size[$index][$key] . $headwrap . " class=\"$table->headerstyle\">". $heading .'</th>'."\n"; // class="header c'.$key.'
			// $output .= '<th style="vertical-align:top;'. $align[$key].$size[$key] .';white-space:nowrap;" class="header c'.$key.'" scope="col">'. $heading .'</th>';
        }
        echo '</tr>'."\n";
    }
}


function print_curriculum_table_footer()
{
	echo '</table>'."\n";
    print_simple_box_end_old();
}


function print_component_row($table)
{
	global $CFG;

     echo "<tr>"."\n";
     echo '<td rowspan=2 align=left '. ' bgcolor="#FFB900"' . '>'. $table->titlerow .'</td>'."\n";
	 echo '<td bgcolor="#FFFF0C" align=center>' . get_string('nagruzkaplan', 'block_mou_school') . '</td>';
	 foreach ($table->dataplan as $key => $item) {
         echo '<td bgcolor="#FFFF0C"' . $table->span[2][$key] . ' align=center>'. $item .'</td>'."\n";
     }
     echo '</tr><tr>'."\n";
 	 echo '<td align=center bgcolor="#1BE4D8">' . get_string('nagruzkafact', 'block_mou_school') . '</td>';
	 foreach ($table->datafact as $key => $item) {
		echo '<td align=center bgcolor="#1BE4D8">'. $item .'</td>'."\n";
    }
    echo '</tr>'."\n";
}

function print_discipline_row($table)
{
	global $CFG;

     echo "<tr>"."\n";
	 foreach ($table->datarow as $key => $item) {
         echo '<td>'. $item .'</td>'."\n";
     }
    echo '</tr>'."\n";
}

function print_curriculum_table_insideheader($table)
{
	global $CFG;

	echo '<tr>'."\n";
	echo "<th class=\"$table->headerstyle\">". '&nbsp;</th>'."\n";
	echo "<th class=\"$table->headerstyle\">". '&nbsp;</th>'."\n";
    foreach ($table->head[3] as $key => $heading) {
            echo "<th align=center class=\"$table->headerstyle\">". $heading .'</th>'."\n";
	}
    echo '</tr>'."\n";
}

?>


