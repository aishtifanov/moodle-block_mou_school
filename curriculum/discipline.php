<?php // $Id: discipline.php,v 1.27 2011/01/19 11:11:11 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_school.php');
	require_once('../authbase.inc.php');
    
    switch ($action)	{
    	case 'excel': $table = table_discipline ($yid, $rid, $sid);
    				 // print_r($table);
        			print_table_to_excel_merge($table, 0, 1);
        			exit();
        			
		case 'setstandart': 

					if (!has_capability('block/mou_school:editdiscipline', $context))	{
						error(get_string('permission', 'block_mou_school'), '../index.php');
					}	
					
					$redirlink = "discipline.php?sid=$sid&amp;yid=$yid&amp;rid=$rid";
					// get standart oblasti
					$domainstvalues = get_records_sql("SELECT * FROM {$CFG->prefix}monit_school_datadir_domain");
					if ($domainstvalues )	{
						// po kajdoi oblasti
						foreach ($domainstvalues as $domainstvalue)	{
							// school domain
							if (!$schdiscdomain = get_record('monit_school_discipline_domain', 'name', $domainstvalue->dirname, 'schoolid', $sid))	{
			            		$newrec->schoolid = $sid;
			            		$newrec->name = $domainstvalue->dirname;
						        if (!insert_record('monit_school_discipline_domain', $newrec))	{
									error(get_string('errorinaddingeduarea','block_mou_school'), $redirlink);
							    }
							    unset($newrec);
							}    
							
							if ($schdiscdomain = get_record('monit_school_discipline_domain', 'name', $domainstvalue->dirname, 'schoolid', $sid))	{					
								// print_r($schdiscdomain); echo '<br>';
								// get standart predmets
								if ($discipstvalues = get_records_sql("SELECT * FROM {$CFG->prefix}monit_school_datadir_discipline
																		WHERE domdatadirid={$domainstvalue->id}"))	{
									// print_r($discipstvalues); echo '<br>';																
							      	foreach ($discipstvalues as $std)	{
										if (!record_exists('monit_school_discipline', 'name', $std->disciplinename, 'schoolid', $sid, 'disciplinedomainid', $schdiscdomain->id))	{
			            					$newrec->name = $std->disciplinename;
			            					$newrec->shortname = $std->discipabbreviature; 
			            					$newrec->schoolid = $sid;
			            					$newrec->disciplinedomainid = $schdiscdomain->id;
											$newrec->dgroupid = $std->dgroupdatadirid; 
											if (!insert_record('monit_school_discipline', $newrec))	{
												error(get_string('errorinaddingschooldiscipline','block_mou_school'), $redirlink);
											}													
									    }	
									}
									
								}
							} 
						}
						notice(get_string('succesavedata','block_mou_school'), $redirlink);
						// redirect($redirlink, get_string('succesavedata','block_monitoring'), 0);
					}
					break;
	}		

	$currenttab = 'discipline';
    include('tabsdis.php');
	
	if (has_capability('block/mou_school:viewdiscipline', $context))	{
		$table = table_discipline ($yid, $rid, $sid);
	    print_color_table($table);

		if (has_capability('block/mou_school:editdiscipline', $context))	{
			$options = array('mode' => 'new' , 'rid' => $rid, 'sid' => $sid, 'yid' => $yid);
			echo '<table align="center" border=0><tr><td>';
		    print_single_button("adddiscip.php", $options, get_string('adddiscip','block_mou_school'));
			echo '</td><td>';
			$options = array('mode' => 'new' , 'rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'action' => 'setstandart');	
		 	print_single_button("discipline.php", $options, get_string('setstandartvalue', 'block_mou_school'));
			echo '</td></tr><tr><td align=center colspan=2>';
			$options = array('mode' => 'new' , 'rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'action' => 'excel');	 	
		    print_single_button("discipline.php", $options, get_string("downloadexcel"));
			echo '</td></tr></table>';
		}	
	} else {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}
	

    print_footer();



function table_discipline ($yid, $rid, $sid)
{
	global $CFG, $context;
	
	$edit_capability = has_capability('block/mou_school:editdiscipline', $context);

	$table->head  = array (	get_string('obrazovatobl', 'block_mou_school'), 'N', get_string('predmet', 'block_mou_school'),
							get_string('shortname', 'block_mou_school'),
							get_string('disciplinesubgroups', 'block_mou_school'), get_string('action', 'block_mou_school'));
    $table->align = array ("left", "center", "left", "center", "left", "center");
	$table->size = array ('20%', '3%', '30%', '10%', '5%','5%');
	$table->columnwidth = array (20, 5, 35, 15, 15);    
	$table->class = 'moutable';
   	$table->width = '80%';

	$table->titles = array();
    $table->titles[] = get_string('discipline', 'block_mou_school');
    $table->titlesrows = array(20);
    $table->worksheetname = 'discipline';
	$table->downloadfilename = 'disciplines';
	
	$discip_domains = get_records_sql ("SELECT *  FROM {$CFG->prefix}monit_school_discipline_domain
									    WHERE schoolid=$sid ORDER BY name");
    $i=0;
	if ($discip_domains)	{

		foreach ($discip_domains as $dsm) {

               $flag = true;
               if ($disciplines = get_records_sql("SELECT * FROM {$CFG->prefix}monit_school_discipline 
			   										WHERE schoolid=$sid AND disciplinedomainid={$dsm->id} 
												   ORDER BY name"))  {

	               foreach ($disciplines as $discipline) {
	                   $strnamedisc = $discipline->name;
	                   if ($discipline->dgroupid != 0)	{
 	                  		$dgroup = get_record('monit_school_discipline_group', 'id', $discipline->dgroupid);
                   			$strnamedisc = $dgroup->name . ': ' . $strnamedisc;
                   		}

	                    $strsubgroups = '';
	                  	$subgroups = get_records_sql("SELECT * FROM {$CFG->prefix}monit_school_subgroup WHERE schoolid=$sid AND disciplineid={$discipline->id}");
		                if ($subgroups) {
		                 	foreach ($subgroups as $subgroup){
		                		$strsubgroups .= '* '. $subgroup->name . '<br>';
		                 	}
	                    }
	                    
	                    if ($edit_capability)	{
							$title = get_string('editdiscipline','block_mou_school');
							$strlinkupdate = "<a title=\"$title\" href=\"adddiscip.php?mode=edit&amp;sid=$sid&amp;rid=$rid&amp;yid=$yid&amp;did={$discipline->id}&amp;ddid={$dsm->id}\">";
							$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";
							$title = get_string('deletediscipline','block_mou_school');
					   		$strlinkupdate .= "<a title=\"$title\" href=\"delcurriculum.php?part=dis&amp;sid=$sid&amp;id={$discipline->id}&amp;yid=$yid&amp;rid=$rid\">";
							$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
							$title = get_string('disciplines','block_mou_school');
							$strdsm = "<strong><a title=\"$title\" href=\"educationareas.php?sid=$sid&amp;rid=$rid\">$dsm->name</a></strong>";
						} else	{
							$strlinkupdate = '-';
							$strdsm = $dsm->name;
						}
							

	                    if (!$flag) $strdsm = '';

						$table->data[] = array ($strdsm, ++$i.'.', $strnamedisc, $discipline->shortname, $strsubgroups, $strlinkupdate);

						$flag = false;
					}
				}	else {
					$table->data[] = array ($dsm->name, '', '', '', '');
				}
		}

	}
    return $table;
}


function set_standart_value($yid, $rid, $sid)
{
	global $CFG;
	
	$domains = get_records_sql ("SELECT * FROM {$CFG->prefix}monit_school_discipline_domain
								  WHERE schoolid=$sid ORDER BY name");

	if ($domains)	{

			foreach ($domains as $domain) {

				$strdiscipline = $domain->name;
			}
	} else {
		notice(get_string('notfounddisciplinedomain', 'block_mou_school'), "discipline.php?rid=$rid&amp;sid=$sid&amp;yid=$yid");
	}			
}
?>