<?php // $Id: excel.php,v 1.3 2012/02/21 06:34:41 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_school.php');

    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = optional_param('sid', '0', PARAM_INT);       // School id
    $yid = optional_param('yid', '0', PARAM_INT);       // Year id
    $pid = optional_param('pid', '0', PARAM_INT);       // Profile id
	
    if ($yid == 0)	{
    	$yid = get_current_edu_year_id();
    }

    require_once('../authall.inc.php');
	
	$action   = optional_param('action', '');    
   /* 
	if ($action == 'excel') 	{
		$table = print_curriculum_table ($yid, $pid, $sid);
    	// print_r($table);
        print_table_to_excel($table);
        exit();
	}
	*/

    $downloadfilename = $table->downloadfilename;

    header("Content-type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"{$downloadfilename}.xls\"");
    header("Expires: 0");
    header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
    header("Pragma: public");

    $workbook = new Workbook("-");
    $txtl = new textlib();

	$strwin1251 =  $txtl->convert($table->worksheetname, 'utf-8', 'windows-1251');
    $myxls =&$workbook->add_worksheet($strwin1251);

	$numcolumn = count ($table->columnwidth) - $lastcols;
    $i=0;
    foreach ($table->columnwidth as $width)	{
		$myxls->set_column($i, $i, $width);
		$i++;
	}

	$formath1 =& $workbook->add_format();
	$formath1->set_size(12);
    $formath1->set_align('center');
    $formath1->set_align('vcenter');
	$formath1->set_color('black');
	$formath1->set_bold(1);
	// $formath1->set_italic();
	$formath1->set_text_wrap();
	// $formath1->set_border(2);

    $i = $ii = 0;
   
    foreach ($table->titles as $key => $title)	{
		$myxls->set_row($i, $table->titlesrows[$key]);
		$strwin1251 =  $txtl->convert($title, 'utf-8', 'windows-1251');
	    $myxls->write_string($i, 0, $strwin1251, $formath1);
		$myxls->merge_cells($i, 0, $i, $numcolumn-1);
		$i++;
    }

	$formath2 =& $workbook->add_format();
	$formath2->set_size(11);
    $formath2->set_align('center');
    $formath2->set_align('vcenter');
	$formath2->set_color('black');
	$formath2->set_bold(1);
	//$formath2->set_italic();
	$formath2->set_border(2);
	$formath2->set_text_wrap();

    if (!empty($table->head)) {
    	$formatp = array();
    	$numcolumn = count ($table->head) - $lastcols;
        foreach ($table->head as $key => $heading) {
        	if ($key >= $numcolumn) continue;
	   		$strwin1251 =  $txtl->convert(strip_tags($heading), 'utf-8', 'windows-1251');
	        $myxls->write_string($i, $key,  $strwin1251, $formath2);

			$formatp[$key] =& $workbook->add_format();
			$formatp[$key]->set_size(10);
		    $formatp[$key]->set_align($table->align[$key]);
		    $formatp[$key]->set_align('vcenter');
			$formatp[$key]->set_color('black');
			$formatp[$key]->set_bold(0);
			$formatp[$key]->set_border(1);
			$formatp[$key]->set_text_wrap();
        }
        $i++;
        $ii = $i;
    }

    if (isset($table->data)) foreach ($table->data as $keyrow => $row) {
      	$numcolumn = count ($row) - $lastcols;
        foreach ($row as $keycol => $item) 	{
           	if ($keycol >= $numcolumn) continue;
        	$clearitem = strip_tags($item);
        	switch ($clearitem)	{
        		case '&raquo;': $clearitem = '>>'; break;
        		case '&laquo;': $clearitem = '<<'; break;
        	}
 			$strwin1251 =  $txtl->convert($clearitem, 'utf-8', 'windows-1251');
			$myxls->write($i + $keyrow, $keycol,  $strwin1251, $formatp[$keycol]);
			$ii = $i + $keyrow;
		}
    }
    
    if (!empty($table2)) {
    	$i = $ii + 2;
    	
    	$formatp = array();
    	$numcolumn = count ($table2->head) - $lastcols;
        foreach ($table2->head as $key => $heading) {
        	if ($key >= $numcolumn) continue;
	   		$strwin1251 =  $txtl->convert(strip_tags($heading), 'utf-8', 'windows-1251');
	        $myxls->write_string($i, $key,  $strwin1251, $formath2);

			$formatp[$key] =& $workbook->add_format();
			$formatp[$key]->set_size(10);
		    $formatp[$key]->set_align($table2->align[$key]);
		    $formatp[$key]->set_align('vcenter');
			$formatp[$key]->set_color('black');
			$formatp[$key]->set_bold(0);
			$formatp[$key]->set_border(1);
			$formatp[$key]->set_text_wrap();
        }
        $i++;
    }

    if (isset($table2->data)) foreach ($table2->data as $keyrow => $row) {
      	$numcolumn = count ($row) - $lastcols;
        foreach ($row as $keycol => $item) 	{
           	if ($keycol >= $numcolumn) continue;
        	$clearitem = strip_tags($item);
        	switch ($clearitem)	{
        		case '&raquo;': $clearitem = '>>'; break;
        		case '&laquo;': $clearitem = '<<'; break;
        	}
 			$strwin1251 =  $txtl->convert($clearitem, 'utf-8', 'windows-1251');
			$myxls->write($i + $keyrow, $keycol,  $strwin1251, $formatp[$keycol]);
			$ii = $i + $keyrow;
		}
    }
      

    $workbook->close();


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
	
	$table->titles = array();
    $table->titles[] = get_string('discipline', 'block_mou_school');
    $table->titlesrows = array(20);
    $table->worksheetname = 'discipline';
	$table->downloadfilename = 'disciplines';
	
    // определяем какое количество параллелей и какие параллели присутствуют в учебном плане
    $pnum = array();
    for ($p = 1; $p <= $CFG->maxparallelnumber; $p++)	{
    	if (record_exists_select('monit_school_curriculum', "parallelnum = $p AND schoolid = $sid AND profileid = $pid")) 	{
    			$pnum[] = $p;
    	}
    }
    if (empty($pnum))	{
    	notice(get_string('notsetdiscipline', 'block_mou_school'), "setdiscipline.php?sid=$sid&amp;rid=$rid&amp;yid=$yid");
    }

	$clasessids = array();
    foreach ($pnum as $pn)	{
    	$table->head[2][$pn]  = $pn;
    	$table->align[2][$pn] = 'center';
   		if ($clasessids[$pn] = get_records_sql ("SELECT DISTINCT classid  FROM {$CFG->prefix}monit_school_curriculum
								  		WHERE parallelnum = $pn AND schoolid = $sid AND profileid = $pid ")) {
			$table->span[2][$pn] = 'colspan=' . count ($clasessids[$pn]);
		}
    }

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
								   WHERE classid = {$clas->classid} AND profileid = $pid ");
			$table->datafact[$clas->classid] = $rec->sum;
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
					$table->datafact[$clas->classid] = $rec->sum;
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
	                  				if ($curr = get_record_select('monit_school_curriculum', "classid = {$clas->classid} AND profileid = $pid AND disciplineid = {$disc->id}  AND componentid = {$component->id} ")) 	{
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


