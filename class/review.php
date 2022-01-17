<?php // $Id: review.php,v 1.3 2010/08/31 09:39:03 Shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../../monitoring/lib.php');
    require_once('../lib_school.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once($CFG->dirroot.'/calendar/lib.php');
    require_once($CFG->libdir.'/filelib.php');    
    //require_once('../lib_att.php');
    
	$aid = optional_param('aid', '');      // action id
	$lid = optional_param('lid', '');      // level id
	$plid = optional_param('plid', '');      // place id
	//$portid = optional_param('portid', '');      // portfolio id
    $rid = required_param('rid', PARAM_INT);      // Rayon id
    $sid = required_param('sid', PARAM_INT);      // School id
    $gid = required_param('gid', PARAM_INT);   // Class id
    $yid = required_param('yid', PARAM_INT); // Year id
    $uid = required_param('uid', PARAM_INT);   // Schedule id (jornal id)   

    include('incl_pupil.php');

   	$biguser = (array)$user1 + (array)$pupil;
   	$user = (object)$biguser;

    $currenttab = 'review';
    include('tabspupil.php');


	if (has_capability('block/mou_school:viewclasslist', $context))	{
		$table = table_journal_outpupil($rid, $sid, $yid, $gid, $uid, $lid, $plid, $aid);
		print_color_table($table);

		if (has_capability('block/mou_school:editclasslist', $context))	{		
			$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'gid' => $gid, 'uid' => $uid, 'portid' => '','mode' => 'new');
			echo '<table align="center" border=0><tr><td>';
		    print_single_button("addreview.php", $options, get_string('addreview','block_mou_school'));
			echo '</td></tr></table>';
		}		
	} else {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}				
		
		
    print_footer();


function table_journal_outpupil($rid, $sid, $yid, $gid, $uid, $lid, $plid, $aid)
{
	global $CFG, $context;
	
	$edit_capability = has_capability('block/mou_school:editclasslist', $context);

	$table->head  = array (get_string('reviewpupil', 'block_mou_school'), get_string('scancopy', 'block_mou_school'), get_string('action', 'block_mou_school'));
	$table->align = array ('left','center','center');
	$table->size = array ('45%', '20%', '5%');
	$table->columnwidth = array (45, 20, 5);

    $table->class = 'moutable';
   	$table->width = '70%';
    $table->titles = array();
    $table->titles[] = get_string('actionsandpractics', 'block_mou_school');
    $table->worksheetname = 'actionsandpractics';
	
	
	if($portfolio = get_records_sql("SELECT id, adress FROM {$CFG->prefix}monit_school_portfolio
								WHERE nameactionid = 0 and userid=$uid and classid=$gid")){
		foreach($portfolio as $port){
		$tabledata = array($port->adress);
		$school = get_record("monit_school", 'id', $sid);		
		$filearea = "0/pupils/$rid/{$school->uniqueconstcode}/$uid/{$port->id}";		
	    if ($basedir = make_upload_directory($filearea))   {
	        if ($files = get_directory_list($basedir)) {
	            $output = '';
	            foreach ($files as $key => $file) {
	                $icon = mimeinfo('icon', $file);
	                if ($CFG->slasharguments) {
	                    $ffurl = "$CFG->wwwroot/file.php/$filearea/$file";
	                } else {
	                    $ffurl = "$CFG->wwwroot/file.php?file=/$filearea/$file";
	                }
	
	                $output .= '<img align="middle" src="'.$CFG->pixpath.'/f/'.$icon.'" class="icon" alt="'.$icon.'" />'.
	                        '<a href="'.$ffurl.'" >'.$file.'</a>';
	                        
	               // $delurl  = "delete.php?cat=$category&amp;rid=$rid&amp;sid=$sid&amp;uid=$uid&amp;cid=$cid&amp;lid=$lid&amp;did=$did&amp;yid=$yid&amp;type_ou=$type_ou&amp;file=$file";
	               // $output .= '<img title="'.$strdelete.'" src="'.$CFG->pixpath.'/t/delete.gif" class="iconsmall" alt="" /></a><br /> ';
	                        
	            }
	        } else {
	        	$output = '<i>' . get_string('isabscent', 'block_mou_att') . '</i>' ;
	        }
	    }		
		$tabledata[] = $output;
		
		if ($edit_capability)	{
			$title = get_string('editreview','block_mou_school');
			$strlinkupdate = "<a href=\"{$CFG->wwwroot}/blocks/mou_school/class/addreview.php?mode=edit&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid=$uid&amp;portid={$port->id}\">";
			$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";

			$title = get_string('deletereview','block_mou_school');
		    $strlinkupdate .= "<a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/mou_school/class/delreview.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid=$uid&amp;portid={$port->id}\">";
			$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
		} else {
			$strlinkupdate = '';
		}	
	
		
		$tabledata[] = $strlinkupdate;
		$table->data[] = $tabledata;
		}
					
	}	
	
				
	

    return $table;
}
?>

