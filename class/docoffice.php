<?php // $Id: docoffice.php,v 1.3 2010/08/23 08:47:57 Shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../../monitoring/lib.php');
    require_once('../lib_school.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once($CFG->dirroot.'/calendar/lib.php');
    require_once($CFG->libdir.'/filelib.php');    

    
	$aid = optional_param('aid', '');      // action id
	$lid = optional_param('lid', '');      // level id
	$plid = optional_param('plid', '');      // place id
	//$portid = optional_param('portid', '');      // portfolio id
    $gid = required_param('gid', PARAM_INT);   // Class id
    $uid = required_param('uid', PARAM_INT);   // Schedule id (jornal id)   

    include('incl_pupil.php');

   	$biguser = (array)$user1 + (array)$pupil;
   	$user = (object)$biguser;

    $currenttab = 'officialdocs';
    include('tabspupil.php');

	if (has_capability('block/mou_school:viewclasslist', $context))	{
		$table = table_journal_outpupil($rid, $sid, $yid, $gid, $uid, $lid, $plid, $aid);
		print_color_table($table);
	
		if (has_capability('block/mou_school:editclasslist', $context))	{
			$options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'gid' => $gid, 'uid' => $uid, 'portid' => '','mode' => 'new');
			echo '<table align="center" border=0><tr><td>';
		    print_single_button("addaction.php", $options, get_string('addaction','block_mou_school'));
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

	$table->head  = array (get_string('actionsandpractics', 'block_mou_school')
							, get_string('level', 'block_mou_school'), get_string('place', 'block_mou_school'), get_string('ball', 'block_mou_school')
							, get_string('adress', 'block_mou_school'), get_string('date', 'block_mou_school'), get_string('scancopy', 'block_mou_school'), get_string('action', 'block_mou_school'));
	$table->align = array ('center','center','center','center','center','center','center','center');
	$table->size = array ('15%', '10%', '15%', '5%', '25%', '7%', '18%', '5%');
	$table->columnwidth = array (15, 10, 15, 5, 25, 7, 18, 5);

    $table->class = 'moutable';
   	$table->width = '100%';
    $table->titles = array();
    $table->titles[] = get_string('actionsandpractics', 'block_mou_school');
    $table->worksheetname = 'actionsandpractics';
	
	
	if($act = get_records_sql("SELECT * FROM {$CFG->prefix}monit_school_actions")){
		foreach($act as $a){
			if($portfolio = get_records_sql("SELECT * FROM {$CFG->prefix}monit_school_portfolio
										WHERE nameactionid={$a->id} and userid=$uid and classid=$gid")){
				foreach($portfolio as $port){
				//	$printdate = get_rus_format_date($port->timeaction);

				$tabledata = array($a->nameaction,$a->levels,$a->place,$a->ball,$port->adress,convert_date($port->timeaction, 'en', 'ru'));
				//$tabledata[] = ("<div align=center><strong><a href=\"{$CFG->wwwroot}/blocks/mou_school/class/addscancopy.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid=$uid&amp;portfolioid={$port->id}\">".get_string('add','block_mou_school')."</a></strong></div>");
				
				//$strdelete   = get_string('delete');

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
					$title = get_string('editportfolio','block_mou_school');
					$strlinkupdate = "<a href=\"{$CFG->wwwroot}/blocks/mou_school/class/addaction.php?mode=edit&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid=$uid&amp;portid={$port->id}&amp;aid={$a->nameaction}&amp;lid={$a->levels}&amp;plid={$a->place}\">";
					$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";
	
					$title = get_string('deleteportfolio','block_mou_school');
				    $strlinkupdate .= "<a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/mou_school/class/delaction.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid=$uid&amp;aid={$a->nameaction}&amp;lid={$a->levels}&amp;portid={$port->id}&amp;plid={$a->place}\">";
					$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
				} else {
					$strlinkupdate = '';
				}	
				
				
				$tabledata[] = $strlinkupdate;
				$table->data[] = $tabledata;
				}
							
			}	
	
		}		
	}

    return $table;
}
?>

