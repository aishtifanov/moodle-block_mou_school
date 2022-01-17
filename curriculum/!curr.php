<?php // $Id: curriculum.php,v 1.20 2010/08/27 09:38:08 Shtifanov Exp $

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

	if (has_capability('block/mou_school:editprofiles', $context))	{
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
			echo  '<div align="center">';
			echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '"></div>';
			echo  '</form>';
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

    // задаем параметры таблицы
    $table->tablealign = 'center';
    $table->cellpadding = '5';
    $table->cellspacing = '1';
    $table->headerstyle = 'header';
    $table->border = '1';
    $table->class = 'moutable';
   	$table->width = '100%';
	
    // формируем шапку таблицы
    $table->head[1]  = array (get_string ('obrazovatobl','block_mou_school'),
    								 get_string ('predmet','block_mou_school'),
    								 get_string ('parallels','block_mou_school'));
    $table->span[1]  = array ("rowspan=3", "rowspan=3");
	$table->align[1] = array ('center', 'center', 'center');
    // определяем какое количество параллелей и какие параллели присутствуют в учебном плане
    $pnum = array();
    for ($p = 1; $p <= $CFG->maxparallelnumber; $p++)	{
    	if (record_exists_select('monit_school_curriculum', "parallelnum = $p AND schoolid = $sid AND profileid = $pid AND yearid = $yid")) 	{
    			$pnum[] = $p;
    	}
    }
    if (empty($pnum))	{
    	notice(get_string('notsetdiscipline', 'block_mou_school'), "setdiscipline.php?sid=$sid&amp;rid=$rid&amp;yid=$yid&amp;pid=$pid");
    }

	$clasessids = array();
    foreach ($pnum as $pn)	{
    	$table->head[2][$pn]  = $pn;
    	$table->align[2][$pn] = 'center';
    	$strsql = "SELECT DISTINCT classid  FROM {$CFG->prefix}monit_school_curriculum
	  				WHERE parallelnum = $pn AND schoolid = $sid AND profileid = $pid AND yearid = $yid";
	  	// echo $strsql . '<hr>'; 			
   		if ($clasessids[$pn] = get_records_sql ($strsql)) {
			$table->span[2][$pn] = 'colspan=' . count ($clasessids[$pn]);
		}
    }
	// print_r($clasessids);
    $countclass = 0;
    foreach ($clasessids as $clasessid)		{
    	foreach ($clasessid as $clas)	{
    		$class = get_record('monit_school_class', 'id', $clas->classid);
    		$countclass++;
    		$table->head[3][]  = $class->name;
			$table->align[3][] = 'center';
			$table->columnwidth[3][] = 10;
    	}
    }
    $table->span[1][] = 'colspan=' . $countclass;

    //  выводим шапку таблицы
    print_curriculum_table_header($table);

    // формирование тела таблицы
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
	    // инициализация массивов
        $itogplan = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        $itogfact = array();
        foreach ($clasessids as $clasessid)
        	foreach ($clasessid as $clas)
        		$itogfact[$clas->classid] = 0;
        // цикл по компонентам
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
										   				WHERE componentid = {$component->id} and schoolid=$sid AND profileid = $pid and classid = {$clas->classid}");
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

                // цикл по образовательным областям
				foreach ($discip_domains as $dsm) {

              		$flag = true;
               		if ($disciplines = get_records_sql("SELECT id, name FROM {$CFG->prefix}monit_school_discipline
               											WHERE schoolid=$sid AND disciplinedomainid={$dsm->id}
               											ORDER BY name"))  {

	               		foreach ($disciplines as $disc) 	{
	               			$datarow = array();
							foreach ($clasessids as $clasessid)		{
						    	foreach ($clasessid as $clas)	{
	                  				if ($curr = get_record_select('monit_school_curriculum', "classid = {$clas->classid} AND schoolid = $sid AND profileid = $pid AND componentid = {$component->id} AND disciplineid = {$disc->id}")) 	{
	                  					
	                  					
										  // =========== CLEAR DUBL LINES IN CURRICULUM 
										  if ($curr_two_s = get_records_select('monit_school_curriculum', "classid = {$clas->classid} AND schoolid = $sid AND profileid = $pid AND componentid = {$component->id} AND disciplineid = {$disc->id}")) 	{
	                  						if (count($curr_two_s) > 1)	{
	                  							echo '!-->'.$curr->id.'<hr>';
	                  							foreach ($curr_two_s as $curr_two)	{
	                  								if ($curr->id == $curr_two->id)	 continue;
	                  								print_r($curr_two); echo '<br>';
	                  								delete_records('monit_school_curriculum', 'id', $curr_two->id);	                  								
	                  							}
	                  							echo '<hr>';
	                  						}
	                  					}	
	                  					// =========== CLEAR DUBL LINES IN CURRICULUM
	                  						
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

    //  закрываем таблицу
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


