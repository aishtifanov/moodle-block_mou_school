<?php // $Id: performance.php,v 1.2 2014/06/03 07:51:03 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_school.php');
   	require_once('../authbase.inc.php');
    require_once('lib_report.php');
   	
    $rpid 	= optional_param('rpid', 0, PARAM_INT);       // Report id
    $termid = optional_param('termid', 0, PARAM_INT);		//Term id
    
    $scriptname = basename($_SERVER['PHP_SELF']);	// echo '<hr>'.basename(me());

    if ($action == 'excel') 	{
        $table = table_rayon_performance($yid, $rid);
        print_table_to_excel($table);
        exit();
    }
    
    $currenttab = 'rperformance';
    include('tabsreports.php');
    
    // notice(get_string('vstadii', 'block_mou_att'), "../index.php?rid=$rid&yid=$yid&sid=$sid");

	if ($edit_capability_rayon) {
		$table = table_rayon_performance($yid, $rid);
		print_color_table($table);
        $options = array('action' => 'excel', 'yid' => $yid, 'rid' => $rid, 'sid' => $sid);
		echo '<p></p><table align="center" border=0><tr><td>';
		print_single_button("$scriptname", $options, get_string("downloadexcel"));
	   	echo '</td><td></table>';			
	} else {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}

    print_footer();


function table_rayon_performance($yid, $rid)
{
	global $CFG;

    // $starttermid = $termid;
    $yearname = get_field_select('monit_years', 'name', "id=$yid"); 
    
    $table->dblhead->head1  = array ('№ п/п', get_string ('class','block_mou_school'), 
                                     "Количество на начало<br /> $yearname уч.года",
                                     'Успеваемость',  'Из них',
                                     "Количество на конец<br /> $yearname уч.года", 
                                     'Качество<br /> знаний,  %',  'Успеваемость, %'); // 8
    $table->dblhead->span1  = array ("rowspan=2", "rowspan=2", "rowspan=2", "colspan=4", "colspan=3", "rowspan=2", "rowspan=2", "rowspan=2"); // 8
	$table->align = array ('center', 'center', 'center', 
                           'center', 'center', 'center', 'center', 'center', 'center', 'center',
                           'center', 'center', 'center');
	$table->columnwidth = array (7, 7, 14, 
                                 4, 8, 5, 5, 5, 8, 8, 
                                 17, 11, 17);
	//$table->dblhead->size = array ('25%','25%','15%','15%','15%','15%','15%','15%');
	$table->wraphead = 'nowrap';

    // $table->dblhead->head2 = array('На "5"', 'На "4" и "5"', 'На "3"', 'На "2"', 'Из них <br />с одной "3"', 'Из них <br />с одной "4"');
    $table->dblhead->head2 = array('На "5"', 'На "4" и "5"', 'На "3"', 'На "2"', 'с одной "2"', 'с одной "3"', 'с одной "4"');
    /*
    for ($i=1; $i<=7; $i++)  {
		$table->align[] = 'center';
		$table->size[] = '5%';
		// $table->columnwidth[] = 10;
    }
    */
    
    $table->class = 'moutable';
   	// $table->width = '95%';

	$table->titles = array();
    $table->titles[] = 'Успеваемость по району';
    $table->titlesrows = array(30);
    $table->worksheetname = $yid;
    $table->downloadfilename = 'rayon_performance'.$rid.'.doc'; 
    
    $prevyid = $yid - 1;
    $sql = "SELECT parallelnum, group_concat(id) as classids FROM mdl_monit_school_class
            where yearid=$prevyid and rayonid=$rid
            group by parallelnum";
    $prevclassids = get_records_sql_menu($sql);        
                
    $sql = "SELECT parallelnum, group_concat(id) as classids FROM mdl_monit_school_class
            where yearid=$yid and rayonid=$rid
            group by parallelnum";
    $currclassids = get_records_sql_menu($sql);        

    $sql = "SELECT distinct parallelnum FROM mdl_monit_school_class
            where yearid=$yid and rayonid=$rid
            order by parallelnum";               
    if ($parallels = get_records_sql($sql)) {
        $pp = 0;
        foreach ($parallels as $parallel)   {
            $pp++;
            $sql = "create temporary table mdl_temp_marks
                    SELECT m.userid, m.mark
                    FROM mdl_monit_school_pupil_card pc
                    inner join mdl_monit_school_class sc on sc.id=pc.classid
                    inner join mdl_monit_school_marks_totals_term m on pc.userid=m.userid
                    where pc.yearid=$yid and sc.yearid=$yid and sc.rayonid=$rid and sc.parallelnum={$parallel->parallelnum}";
            // print $sql . '<br />';                
            execute_sql($sql, false);        
    
            $sql = "create temporary table mdl_temp_min_marks 
                    select userid, min(mark) as minmark from mdl_temp_marks group by userid";
            // print $sql . '<br />';                
            execute_sql($sql, false);                
            
            if ($parallel->parallelnum > 1 && $parallel->parallelnum < 12) {
                $ids = $prevclassids[$parallel->parallelnum-1];        
                $sql = "SELECT count(userid) as cnt FROM mdl_monit_school_pupil_card where classid in ($ids)";    
                $countpupilstart = get_field_sql($sql);
            } else {
                $countpupilstart = '-';
            }    
            
	        $tabledata = array ($pp, $parallel->parallelnum, $countpupilstart);    
            $kolmarks = array(0,0,0,0,0,0); 
            $sql = "select minmark, count(minmark) as cnt from mdl_temp_min_marks group by minmark";
	        if ($marks = get_records_sql_menu($sql)) {
                for ($i=5; $i>=2; $i--)  {
                    if (isset($marks[$i])) { 
                        $tabledata[] = $marks[$i];
                        $kolmarks[$i] = $marks[$i];
                    } else {
                        $tabledata[] = '-';
                    }    
                }
            } else {
                $tabledata[] = '-';
                $tabledata[] = '-';
                $tabledata[] = '-';
                $tabledata[] = '-';
            }   
            
            for ($i=2; $i<=4; $i++)  {     
                $sql = "select userid, count(mark) as cnt
                        from mdl_temp_marks tm
                        inner join mdl_temp_min_marks tmm using(userid)
                        where tm.mark=$i and tmm.minmark=$i
                        group by userid
                        having count(mark)=1";
                if ($marks = get_records_sql_menu($sql)) {
                    $tabledata[] = count($marks);
                } else {
                    $tabledata[] = '-';
                }    
            }    
            /*
            $sql = "select count(userid) as countpupil from mdl_temp_min_marks";
            $countpupils = get_field_sql($sql);
            */
            $ids = $currclassids[$parallel->parallelnum];        
            $sql = "SELECT count(userid) as cnt FROM mdl_monit_school_pupil_card where classid in ($ids)";    
            $countpupils = get_field_sql($sql);
            
            $tabledata[] = $countpupils;     

            if ($countpupils > 0)   {
                // % кач. зн. по предмету
                $kachestvo = calculate_kachestvo($kolmarks, $countpupils);
                $tabledata[] = str_replace('.', ',', $kachestvo);
                    
                // СОУ (%)                                        
                $SOU = calculate_SOU($kolmarks, $countpupils);
                $tabledata[] = str_replace('.', ',', $SOU);
             } else {
                $tabledata[] = '-';
                $tabledata[] = '-';
             }   
            
            $table->data[] = $tabledata;
            
            $sql = "drop temporary table mdl_temp_marks";
            execute_sql($sql, false);

            $sql = "drop temporary table mdl_temp_min_marks";
            execute_sql($sql, false);
         }
    }        
                
    return $table;
}


?>