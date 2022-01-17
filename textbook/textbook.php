<?PHP // $Id: textbook.php,v 1.2 2011/10/24 07:57:57 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');

    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = required_param('sid', PARAM_INT);       // School id
    $yid = optional_param('yid', 0, PARAM_INT);       // Year id
	$catid  = optional_param('catid', 0, PARAM_INT);       // Category textbook
    $tbid  = optional_param('tbid', 0, PARAM_INT);       // Textbook ID

    if ($yid == 0)	{
    	$yid = get_current_edu_year_id();
    }

	require_login();

	$context = get_context_instance(CONTEXT_REGION, 1);
	$strsql = "SELECT a.id, roleid, contextid, contextlevel, instanceid, path, depth  
				FROM mdl_role_assignments a	RIGHT JOIN mdl_context ctx ON a.contextid=ctx.id
			   WHERE userid={$USER->id}";
	  // echo $strsql . '<hr>';
	if ($ctxs = get_records_sql($strsql))	{
	 		// print_r($ctxs);
			foreach($ctxs as $ctx1)	{
				if ($ctx1->contextlevel > 1000 && $ctx1->contextlevel <= CONTEXT_RAYON)	{
					$context = $ctx1;
					break;
				}
			}
	}				

	if (!has_capability('block/mou_school:editclasslist', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

	$action   = optional_param('action', '');
    if ($action == 'excel') {
        $table = table_textbook($rid, $sid, $yid, $catid);
        print_table_to_excel($table, 1);
        exit();
	} else if ($action == 'clear') {
	    delete_records('monit_textbook', 'id', $tbid); 
    }   

    $strtextbook = get_string('textbooks', 'block_mou_ege');

	$breadcrumbs = '<a href="'.$CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('title','block_mou_school').'</a>';
	$breadcrumbs .= "-> $strtextbook";
	print_header_mou("$SITE->shortname: $strtextbook", $SITE->fullname, $breadcrumbs);

	// print_tabs_years($yid, "textbook.php?yid=");

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_textbook("textbook.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;catid=", $yid, $catid);
	echo '</table>';


    if ($yid  != 0 &&  $catid != 0)		{

		// echo "<hr />";
		// print_heading($strdisciplines, "center", 4);
		// print_heading(get_string("disciplinesterm","block_mou_ege"), "center", 4);
	    print_heading($strtextbook, "center", 4);
	    
	    $table = table_textbook($rid, $sid, $yid, $catid);

	    print_color_table($table);

		// if 	($admin_is || $region_operator_is || $rayon_operator_is)	 {
			?>
			<table align="center">
				<tr>
				<td>
			  <form name="adddiscipl" method="post" action="edittextbook.php">
					<input type="hidden" name="rid" value="<?php echo $rid ?>">
					<input type="hidden" name="sid" value="<?php echo $sid ?>">
					<input type="hidden" name="yid" value="<?php echo $yid ?>">
					<input type="hidden" name="catid" value="<?php echo $catid ?>">
					<input type="hidden" name="tbid" value="0">
			  	    <div align="center">
					<input type="submit" name="adddiscipline" value="<?php print_string('addtextbook','block_mou_ege')?>">
				    </div>
			  </form>
			  </td>
				<td>
				<form name="download" method="post" action="textbook.php">
				    <div align="center">
					<input type="hidden" name="rid" value="<?php echo $rid ?>">
					<input type="hidden" name="sid" value="<?php echo $sid ?>">
					<input type="hidden" name="yid" value="<?php echo $yid ?>">
					<input type="hidden" name="catid" value="<?php echo $catid ?>">
				    <input type="hidden" name="action" value="excel">
					<input type="submit" name="downloadexcel" value="<?php print_string("downloadexcel")?>">
				    </div>
			  </form>
				</td>
				</tr>
			  </table>
			<?php

		// }
    }
    print_footer();


function table_textbook($rid, $sid, $yid, $catid)
{
	global $CFG;
	
    $table->head  = array ('№', get_string('authors','block_mou_ege'), get_string('textbookname','block_mou_ege'),
						    get_string('textbooknumclass','block_mou_ege'), get_string('publisher','block_mou_ege'),
						    get_string("action","block_mou_ege"));

    $table->align = array ('center', 'left', 'left', 'center', 'left', 'center');
    $table->class = 'moutable';
  	$table->width = '80%';
    $table->size = array ('5%', '30%', '20%', '10%', '10%', '5%');
    $table->columnwidth = array (5, 24, 62, 10, 25, 8);
	
   	$table->titlesrows = array(30);
    $table->titles = array();

	$catbook  = get_record_select ('monit_textbook_cat', "id = $catid", 'id, name');
    
    $table->titles[] = $catbook->name;
    $table->downloadfilename = "textbooks_".$catid;
    $table->worksheetname = $table->downloadfilename;

	$textbooks =  get_records_select('monit_textbook', "categoryid = $catid", 'authors', 'id, categoryid, authors, name, numclass, publisher');

	if ($textbooks)	{
	    $i = 0;
		foreach ($textbooks as $textbook) {
			$title = get_string('edittextbook','block_mou_ege');
			$strlinkupdate = "<a title=\"$title\" href=\"edittextbook.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;catid=$catid&amp;tbid={$textbook->id}\">";
			$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";

			// if 	($admin_is || $region_operator_is)	 {
				$title = get_string('deletetextbook','block_mou_ege');
		  	 	$strlinkupdate = $strlinkupdate . "<a title=\"$title\" href=\"textbook.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;catid=$catid&amp;tbid={$textbook->id}&amp;action=clear\">";
				$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
			// }

			$table->data[] = array (++$i.'.', $textbook->authors, $textbook->name, $textbook->numclass, $textbook->publisher, $strlinkupdate);
		}
	}
	
	return $table;

}	

?>