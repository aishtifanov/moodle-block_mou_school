<?php // $Id: holidays.php,v 1.12 2011/08/04 12:32:29 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_school.php');    
	require_once('../authbase.inc.php');
   
	$currenttab = 'holidays';
    include('tabsup.php');

	if (has_capability('block/mou_school:viewtypestudyperiod', $context))	{
		
		$parallel_array = array();
		for ($i = 1; $i <= $CFG->maxparallelnumber; $i++)	{
			$parallel_array[] = $i;
	 	}

		$holidays = get_records_sql ("SELECT id, schoolid, termtypeid, name, datestart, dateend, parallelnum FROM {$CFG->prefix}monit_school_holidays
		 							  WHERE schoolid=$sid or schoolid=0 
									  ORDER BY schoolid DESC, termtypeid, datestart");

		
	
		if ($holidays)	{
			$table->dblhead->head1  = array (get_string('name', 'block_mou_school'), get_string('timestart', 'block_mou_school'),
									get_string('timeend', 'block_mou_school'), get_string('typestudyperiod1', 'block_mou_school'),get_string('parallels', 'block_mou_school'), get_string('action', 'block_mou_school'));
		    $table->dblhead->span1 = array ("rowspan=2", "rowspan=2","rowspan=2","rowspan=2","colspan=$CFG->maxparallelnumber","rowspan=2");
			$table->align = array ("left", "center", "center", "left", "center", "center");
	 	    $table->size = array('10%', '10%', '10%', '5%', '10%', '10%');
	    
			for ($i = 1; $i <= $CFG->maxparallelnumber; $i++)	{
			    $table->dblhead->head2[]  = $i;
				$table->align[] = 'center';
				$table->columnwidth[] = 10;
		    }

		   	$table->width = '90%';
	        $table->class = 'moutable';
					
			foreach ($holidays as $holiday) {
				$explode = explode(',', $holiday->parallelnum);
				
				$list_of_parallels = array();
				foreach($explode as $ids){
					$list_of_parallels[] = $ids;
				}
                if ($holiday->schoolid == 0)    {
                    $termtype->name = '-';
                } else {
				    $termtype = get_record('monit_school_term_type','id', $holiday->termtypeid);
                }    

				$tabledata = array($holiday->name);
				$tabledata[] = convert_date($holiday->datestart, 'en', 'ru');
				$tabledata[] = convert_date($holiday->dateend, 'en', 'ru');	
				$tabledata[] = $termtype->name;

			    foreach($parallel_array as $a=>$b)	{						
					if (in_array($b, $list_of_parallels)) 	{
						$is = '+';
				  	    if ($existholiday = get_record_sql("SELECT DISTINCT parallelnum FROM {$CFG->prefix}monit_school_holidays
															WHERE schoolid = $sid and termtypeid = {$holiday->termtypeid}"))	{
							$insidetable = "<img src=\"{$CFG->pixpath}/t/clear.gif\"/>";
							// $insidetable .= "<input type=hidden name=fld_{$holiday->id}_{$b} value='$is'>";
						}else{
							$insidetable = '-';//"<input type=checkbox checked size=1 name=fld_{$holiday->id}_{$b} value='$is'>";
						}
					}else{
						$is = '-';
						$insidetable = '-';//"<input type=checkbox size=1 name=fld_{$holiday->id}_{$b} value='$is'>";							
					}
                    if ($holiday->schoolid == 0)    {
                        $insidetable = "<img src=\"{$CFG->pixpath}/t/clear.gif\"/>";
                    }    
					$tabledata[] = $insidetable;	
				}	
				
				if (has_capability('block/mou_school:edittypestudyperiod', $context))	{
					$title = get_string('editholidays','block_mou_school');
					$strlinkupdate = "<a title=\"$title\" href=\"addholidays.php?mode=edit&amp;sid=$sid&amp;rid=$rid&amp;yid=$yid&amp;hid={$holiday->id}\">";
					$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";
					$title = get_string('deleteholidays','block_mou_school');
				    $strlinkupdate = $strlinkupdate . "<a title=\"$title\" href=\"delholiday.php?sid=$sid&amp;rid=$rid&amp;yid=$yid&amp;id={$holiday->id}\">";
					$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
				} else {
					$strlinkupdate = '-';
				}
                
                if ($holiday->schoolid == 0)    {
                    $strlinkupdate = '-';
                }
			   $tabledata[] = $strlinkupdate;
			   		    	
			   $table->data[] = $tabledata;
			}
			print_color_table($table);
	
		}	else {
				notify(get_string('notfoundholiday', 'block_mou_school'));
		}
		 
	}else{
		echo 'dflgdlfgjldgf';
	}

	
	
	if (has_capability('block/mou_school:edittypestudyperiod', $context))	{
		?>
		<table align="center">
			<tr>
			<td>
		  <form name="addcurr" method="post" action="addholidays.php">
		     <input type="hidden" name="mode" value="new">
		     <input type="hidden" name="yid" value="<?php echo $yid ?>">
		     <input type="hidden" name="rid" value="<?php echo $rid ?>">
		     <input type="hidden" name="sid" value="<?php echo $sid ?>">
		     <input type="hidden" name="tid" value="<?php echo $tid ?>">
		    <div align="center">
				<input type="submit" name="addcurriculum" value="<?php print_string('addholiday','block_mou_school')?>">
		    </div>
		  </form>
		  </td>
		</tr>
		  </table>
		<?php
	}

    print_footer();

?>



