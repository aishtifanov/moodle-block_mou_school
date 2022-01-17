<?php // $Id: setdiscipline.php,v 1.12 2012/02/21 06:34:41 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_school.php');
	require_once('../authbase.inc.php');

   
    $pid = optional_param('pid', 0, PARAM_INT);       // Profile id
    $cid = optional_param('cid', 0, PARAM_INT);       // Component id


    if ($recs = data_submitted())  {
		//print_r($recs); echo '<hr>';
        // notice(get_string('succesavedata','block_monitoring'), "points.php?rid=0&amp;yid=$yid");
        $redirlink = "setdiscipline.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=$pid&amp;cid=$cid";

		$disciplines = get_records_sql ("SELECT id, name FROM {$CFG->prefix}monit_school_discipline
										  WHERE schoolid=$sid ORDER BY name");
    	
        if (isset($recs->setdefaults))	{
        	$examples = get_example_data_setsicipline();
        	if ($disciplines)	{
 				foreach ($disciplines as $discipline) {
	            	$did = $discipline->id;
	            	if (isset($examples[$discipline->name]))	{
	            		$did_pnum = $examples[$discipline->name];
	            	} else {
	            		continue;
	            	}
					for ($p = 1; $p <= $CFG->maxparallelnumber; $p++)	{
						if ($did_pnum[$p-1] == 1)	{
	  	            		$classes = get_records_sql ("SELECT id FROM {$CFG->prefix}monit_school_class
										  		 		WHERE schoolid=$sid AND yearid=$yid AND parallelnum = $p");
							 // print_r($classes); echo '<hr>';
							if ($classes)	{
								foreach ($classes as $class) {
	                        		if (!record_exists_select('monit_school_curriculum', "classid = {$class->id} AND disciplineid = $did  AND profileid = $pid  AND componentid = $cid AND parallelnum = $p")) 	{
						          		$newrec->parallelnum = $p;
						          		$newrec->yearid 	 = $yid;
						          		$newrec->schoolid 	 = $sid;
						          		$newrec->classid 	 = $class->id;
						          		$newrec->componentid = $cid;
						          		$newrec->profileid 	 = $pid;
					            		$newrec->disciplineid = $did;
					            		$newrec->hours = 2;
	                                    // print_r($newrec); echo '<hr>';
								        if (!insert_record('monit_school_curriculum', $newrec))	{
											error(get_string('errorinaddingcurriculum','block_mou_school'), $redirlink);
									    }
		            				}
		            			}
		            		}
						}
					}
				}
			}				
		}	else {	
	        $did_pnum = array();
			foreach($recs as $fieldname => $value)	{
				if ($value != '')	{
		            $mask = substr($fieldname, 0, 4);
		            if ($mask == 'fld_')	{
		            	$ids = explode('_', $fieldname);
		            	$did_pnum[$ids[1]][$ids[2]] = 1;
		            }
		        }
		    }
	        // print_r($did_pnum); echo '<hr>';
			if ($disciplines)	{
	            foreach ($disciplines as $discipline) {
	            	$did = $discipline->id;
	            	// echo "};<br>#example[] ={";
					for ($p = 1; $p <= $CFG->maxparallelnumber; $p++)	{
						// echo "[$p] ";
						if (isset($did_pnum[$did][$p]))	{
							// echo $did_pnum[$did][$p] . ',';
	  	            		$classes = get_records_sql ("SELECT id FROM {$CFG->prefix}monit_school_class
										  		 		WHERE schoolid=$sid AND yearid=$yid AND parallelnum = $p");
							 // print_r($classes); echo '<hr>';
							if ($classes)	{
								foreach ($classes as $class) {
	                        		if (!record_exists_select('monit_school_curriculum', "classid = {$class->id} AND disciplineid = $did  AND profileid = $pid  AND componentid = $cid AND parallelnum = $p")) 	{
						          		$newrec->parallelnum = $p;
						          		$newrec->yearid 	 = $yid;
						          		$newrec->schoolid 	 = $sid;
						          		$newrec->classid 	 = $class->id;
						          		$newrec->componentid = $cid;
						          		$newrec->profileid 	 = $pid;
					            		$newrec->disciplineid = $did;
					            		$newrec->hours = 2;
	                                    // print_r($newrec); echo '<hr>';
								        if (!insert_record('monit_school_curriculum', $newrec))	{
											error(get_string('errorinaddingcurriculum','block_mou_school'), $redirlink);
									    }
		            				}
		            			}
		            		}
		            		
		            	} else {
		            		// echo '0,';
		            		delete_records_select ('monit_school_curriculum', "parallelnum = $p AND schoolid = $sid AND profileid = $pid AND componentid = $cid AND disciplineid = $did");
		            	}
	
		       		}
		       	}
		    }
		}		
        notice(get_string('succesavedata','block_mou_school'), $redirlink);
		// redirect($redirlink, get_string('succesavedata','block_monitoring'), 0);
	}



	$currenttab = 'setdiscipline';
    include('tabsup.php');

    $view_capability = has_capability('block/mou_school:viewprofiles', $context);
    $edit_capability = has_capability('block/mou_school:editprofiles', $context);

	if ($view_capability)	{

		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		listbox_profiles("setdiscipline.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=",$sid, $pid);
		listbox_components("setdiscipline.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=$pid&amp;cid=", $sid, $cid);
		echo '</table>';
	
	    if ($pid !=0 && $cid != 0)	{
			echo  '<form name="setdiscipline" method="post" action="setdiscipline.php">';
			echo  '<input type="hidden" name="rid" value="' . $rid . '">';
			echo  '<input type="hidden" name="sid" value="' . $sid . '">';
			echo  '<input type="hidden" name="yid" value="' . $yid . '">';
			echo  '<input type="hidden" name="pid" value="' . $pid . '">';
			echo  '<input type="hidden" name="cid" value="' . $cid . '">';
			$table = table_setdiscipline($sid, $pid, $cid);
			print_color_table($table);
            if ($edit_capability)	{
    			echo  '<div align="center">';
    	        echo '<a href="javascript:select_all_in(\'TABLE\',null,\'setdiscipline\');">'.get_string('selectall', 'quiz').'</a> / ';
    	        echo '<a href="javascript:deselect_all_in(\'TABLE\',null,\'setdiscipline\');">'.get_string('selectnone', 'quiz').'</a><br>';
    			echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '">';
    			echo  '<input type="submit" name="setdefaults" value="'. get_string('autocreatexample', 'block_mou_school') . '"></div>';
    			echo  '</form>';
            }  else {
                echo  '</form>';
            }
        }      

		print_string ('notifysetdiscipline', 'block_mou_school');
	}
		
    print_footer();



function table_setdiscipline($sid, $pid, $cid)
{
	global $CFG, $maxparallel;

	$table->id = 'setdiscipline'; 
    $table->dblhead->head1  = array (get_string ('discipline','block_mou_school'), get_string ('parallels','block_mou_school'));
    $table->dblhead->span1  = array ("rowspan=2", "colspan=$CFG->maxparallelnumber");
	$table->align = array ('left', 'center');
	$table->columnwidth = array (20);

    for ($i = 1; $i <= $CFG->maxparallelnumber; $i++)	{
	    $table->dblhead->head2[]  = $i;
		$table->align[] = 'center';
		$table->columnwidth[] = 10;
    }
    $table->class = 'moutable';
   	$table->width = '70%';

	$table->titles = array();
    $table->titles[] = get_string('discipline','block_mou_school');
    $table->worksheetname = $sid;

	$disciplines = get_records_sql ("SELECT * FROM {$CFG->prefix}monit_school_discipline
								  WHERE schoolid=$sid ORDER BY name");
	if ($disciplines)	{

        foreach ($disciplines as $discipline) {
            $did = $discipline->id;
			$tabledata = array ($discipline->name);

			for ($p = 1; $p <= $CFG->maxparallelnumber; $p++)	{
				$insidetable = '';
			  	if (record_exists_select('monit_school_curriculum', "schoolid = $sid AND parallelnum = $p AND  profileid = $pid AND componentid = $cid AND disciplineid = $did")) 	{
			  		$is = '+';
					$insidetable = "<input type=checkbox checked size=1 name=fld_{$did}_{$p} value='$is'>";
				} else {
					$is = '-';
					$insidetable = "<input type=checkbox size=1 name=fld_{$did}_{$p} value='$is'>";

				}
	           $tabledata[] = $insidetable;
			}

			$table->data[] = $tabledata;
        }
	}

    return $table;
}

function get_example_data_setsicipline()
{
	/*
$example[] = array (0,0,0,0,0,0,1,1,1,1,1,1); // Алгебра
$example[] = array (1,1,1,1,1,1,1,1,1,1,1,1); // Английский язык
$example[] = array (0,0,0,0,0,1,1,1,1,1,1,1); // Биология
$example[] = array (0,0,0,0,0,0,0,0,1,0,0,0); // Всеобщая история
$example[] = array (0,0,0,0,0,1,1,1,1,1,1,1); // География
$example[] = array (0,0,0,0,0,0,1,1,1,1,1,1); // Геометрия
$example[] = array (1,1,1,1,1,1,1,1,0,0,0,0); // Изобразительное искусство
$example[] = array (0,1,1,1,1,1,1,1,1,1,1,1); // Иностранный язык (английский)
$example[] = array (0,0,1,1,0,0,0,1,1,1,1,1); // Информатика и ИКТ
$example[] = array (0,0,0,0,0,0,0,0,1,0,0,0); // Искусство
$example[] = array (0,0,0,0,1,1,1,1,0,0,0,0); // История
$example[] = array (0,0,0,0,0,0,0,0,1,1,1,1); // История России
$example[] = array (0,0,0,0,0,0,0,0,0,0,0,0); // Краеведение
$example[] = array (0,0,0,0,1,1,1,1,1,1,1,1); // Литература
$example[] = array (1,1,1,1,0,0,0,0,0,0,0,0); // Литературное чтение
$example[] = array (1,1,1,1,1,1,0,0,0,0,0,0); // Математика
$example[] = array (1,1,1,1,1,1,1,0,0,0,0,0); // Музыка
$example[] = array (0,0,0,0,0,1,1,1,1,1,1,1); // Обществознание	
$example[] = array (1,1,1,1,0,0,0,0,0,0,0,0); // Окружающий мир
$example[] = array (0,0,0,0,0,0,0,1,1,1,1,1); // Основы безопасности жизнедеятельности
$example[] = array (0,0,0,0,0,0,0,0,0,0,0,0); // Православная культура
$example[] = array (0,0,0,0,1,0,0,0,0,0,0,0); // Природоведение
$example[] = array (1,1,1,1,1,1,1,1,1,1,1,1); // Русский язык
$example[] = array (1,1,1,1,1,1,1,1,1,1,1,1); // Технология
$example[] = array (0,0,0,0,0,0,1,1,1,1,1,1); // Физика
$example[] = array (1,1,1,1,1,1,1,1,1,1,1,1); // Физическая культура
$example[] = array (0,0,0,0,0,0,0,1,1,1,1,1); // Химия
$example[] = array (0,0,0,0,0,0,0,0,0,0,1,1); // Экология
*/


$example['Алгебра'] = array (0,0,0,0,0,0,1,1,1,1,1,1); // 
$example['Английский язык'] = array (1,1,1,1,1,1,1,1,1,1,1,1); // 
$example['Биология'] = array (0,0,0,0,0,1,1,1,1,1,1,1); // 
$example['Всеобщая история'] = array (0,0,0,0,0,0,0,0,1,0,0,0); // 
$example['География'] = array (0,0,0,0,0,1,1,1,1,1,1,1); // 
$example['Геометрия'] = array (0,0,0,0,0,0,1,1,1,1,1,1); // 
$example['Изобразительное искусство'] = array (1,1,1,1,1,1,1,1,0,0,0,0); // 
$example['Немецкий язык'] = array (0,1,1,1,1,1,1,1,1,1,1,1); // 
$example['Информатика и ИКТ'] = array (0,0,1,1,0,0,0,1,1,1,1,1); // 
$example['Искусство'] = array (0,0,0,0,0,0,0,0,1,0,0,0); // 
$example['История'] = array (0,0,0,0,1,1,1,1,0,0,0,0); // 
$example['История России'] = array (0,0,0,0,0,0,0,0,1,1,1,1); // 
$example['Краеведение'] = array (0,0,0,0,0,0,0,0,0,0,0,0); // 
$example['Литература'] = array (0,0,0,0,1,1,1,1,1,1,1,1); // 
$example['Литературное чтение'] = array (1,1,1,1,0,0,0,0,0,0,0,0); // 
$example['Математика'] = array (1,1,1,1,1,1,0,0,0,0,0,0); // 
$example['Музыка'] = array (1,1,1,1,1,1,1,0,0,0,0,0); // 
$example['Обществознание'] = array (0,0,0,0,0,1,1,1,1,1,1,1); // 	
$example['Окружающий мир'] = array (1,1,1,1,0,0,0,0,0,0,0,0); // 
$example['Основы безопасности жизнедеятельности'] = array (0,0,0,0,0,0,0,1,1,1,1,1); // 
$example['Православная культура'] = array (0,0,0,0,0,0,0,0,0,0,0,0); // 
$example['Природоведение'] = array (0,0,0,0,1,0,0,0,0,0,0,0); // 
$example['Русский язык'] = array (1,1,1,1,1,1,1,1,1,1,1,1); // 
$example['Технология'] = array (1,1,1,1,1,1,1,1,1,1,1,1); // 
$example['Физика'] = array (0,0,0,0,0,0,1,1,1,1,1,1); // 
$example['Физическая культура'] = array (1,1,1,1,1,1,1,1,1,1,1,1); //
$example['Французский язык'] = array (0,1,1,1,1,1,1,1,1,1,1,1); // 
$example['Химия'] = array (0,0,0,0,0,0,0,1,1,1,1,1); // 
$example['Экология'] = array (0,0,0,0,0,0,0,0,0,0,1,1); // 

return $example;
}

?>

