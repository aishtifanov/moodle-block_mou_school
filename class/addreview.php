<?PHP // $Id: addreview.php,v 1.5 2010/08/31 09:39:03 Shtifanov Exp $

    require_once('../../../config.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_att/lib_att.php');
    require_once($CFG->libdir.'/tablelib.php');
    require_once($CFG->libdir.'/uploadlib.php');
	require_once($CFG->libdir.'/filelib.php');    
    require_once('../../mou_accredit/lib_accredit.php');

    $mode = required_param('mode');    // new, add, edit, update
    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = required_param('sid', PARAM_INT);       // School id
    $yid = required_param('yid', PARAM_INT);       // Year id
    $gid = optional_param('gid', 0, PARAM_INT);      // Class id
    $aid = optional_param('aid', '');      // action id
	$lid = optional_param('lid', '');      // level id
	$plid = optional_param('plid', '');      // place id
    $uid = required_param('uid', PARAM_INT);
	$portid = required_param('portid', PARAM_INT);
    
	$context = get_context_instance(CONTEXT_SCHOOL, $sid);
	if (!has_capability('block/mou_school:editclasslist', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	
    
   	define("MAX_SCAN_COPY_SIZE", 8388608);
	    
	$strclasses = get_string('classes','block_mou_ege');
	$strclass = get_string('class','block_mou_ege');
	$strpupils = get_string('pupils', 'block_mou_ege');
	$strdocoffice = get_string('reviewpupil', 'block_mou_school');	
	
    if ($mode === "new" || $mode === "add" ) 	{
		$strtitle = get_string('addingreview', 'block_mou_school');	
    } else {
		$strtitle = get_string('editingreview', 'block_mou_school');	
    }
	
	
	$breadcrumbs = '<a href="'.$CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('title','block_mou_school').'</a>';
	$breadcrumbs .= " -> <a href=\"classlist.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">$strclasses</a>";
	$breadcrumbs .= " -> <a href=\"classpupils.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid\">$strpupils</a>";
	$breadcrumbs .= " -> <a href=\"review.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid=$uid\">$strdocoffice</a>";	
	$breadcrumbs .= " -> $strtitle";
	print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);
	print_heading($strtitle, "center", 2);

	$rec->schoolid = $sid;
	$rec->rayonid = $rid;
	$rec->classid = $gid;
	$rec->userid = $uid;
	$rec->adress = '';
//	$rec->timeaction = '';
	$actions = get_record_sql("SELECT * FROM {$CFG->prefix}monit_school_actions
							   WHERE nameaction='$aid' and levels='$lid' and place='$plid'");
    switch ($mode)	{
    	case 'new':    
						
			    		$mode='add';
    	break;
	 	case 'add':     $strtitle = get_string('addingaction', 'block_mou_school');	

					 //	$actions = get_record_sql("SELECT * FROM {$CFG->prefix}monit_school_actions
						//							WHERE nameaction='$aid' and levels='$lid' and place='$plid'");
				//
						/*
						if (record_exists('monit_school_portfolio', 'nameactionid', 0,  'userid', $uid)){
							error(get_string('existrecord','block_mou_school'), '../index.php');
						}
						*/
						$rec->schoolid = $sid;
						$rec->rayonid = $rid;
						$rec->classid = $gid;
						$rec->userid = $uid;
						$rec->nameactionid = 0;	
						$rec->adress = required_param('review');
						$rec->timeaction = date("Y-m-d");
						
						if ($newid = insert_record('monit_school_portfolio', $rec))	{
				      		$scancopy = 0;
				      		$frm = data_submitted();
				      		// print_r($frm); print_r($_FILES);      		
							if (!empty($_FILES['newfile']['name']))	{
									$school = get_record("monit_school", 'id', $sid);
					   				$dir = "0/pupils/$rid/{$school->uniqueconstcode}/$uid/$newid";
									$basedir = make_upload_directory($dir);	
									//$files = get_directory_list($dir);				   	
							   		$um = new upload_manager('newfile',true,false, 1, false, MAX_SCAN_COPY_SIZE);
							   		// print_r($um);  echo '<hr>';
							        if ($um->process_file_uploads($dir))  {
							        	$scancopy = 1;
								           //$newfile_name = $um->get_new_filename();
							    	      print_heading(get_string('uploadedfile'), 'center', 4);
							      	} else {
							       	    notify(get_string("uploaderror", "assignment")); //submitting not allowed!
						   			}
					   		}
							set_field('monit_school_portfolio', 'scancopy', $scancopy, 'id', $newid);	   		
					 	    redirect("review.php?mode=4&amp;rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;gid=$gid&amp;uid=$uid&amp;aid=$aid&amp;lid=$lid&amp;plid=$plid", get_string('actionadded', 'block_mou_school'), 2);
						}
					
		break;
		case 'edit':	$prtflio = get_record('monit_school_portfolio','id', $portid);
						$rec->review = 	$prtflio->adress;					
					
						$mode = 'update';
		break;
		case 'update':	$prtflio = get_record('monit_school_portfolio','id', $portid);
						$rec->id = $portid;
						$rec->nameactionid = 0;		
						$rec->adress = required_param('review');
					//	$rec->timeaction = convert_date(required_param('timeaction'), 'ru', 'en');
						update_record('monit_school_portfolio', $rec);     		
							if (!empty($_FILES['newfile']['name']))	{
									$school = get_record("monit_school", 'id', $sid);
					   				$dir = "0/pupils/$rid/{$school->uniqueconstcode}/$uid/$portid";
									$basedir = make_upload_directory($dir);	
									//$files = get_directory_list($dir);				   	
							   		$um = new upload_manager('newfile',true,false, 1, false, MAX_SCAN_COPY_SIZE);
							   		// print_r($um);  echo '<hr>';
							        if ($um->process_file_uploads($dir))  {
							    	      print_heading(get_string('uploadedfile'), 'center', 4);
							      	} else {
							       	    notify(get_string("uploaderror", "assignment")); //submitting not allowed!
						   			}
					   		}	   		
					 	    redirect("review.php?mode=4&amp;rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;gid=$gid&amp;uid=$uid&amp;aid=$aid&amp;lid=$lid&amp;plid=$plid", get_string('actionadded', 'block_mou_school'), 2);
						
		break;
	}
	

     echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	//echo '</table>';
?>
<form enctype="multipart/form-data" name="addform" method="post" action="addreview.php">

	<input type="hidden" name="mode" value="<?php echo $mode ?>" />
	<input type="hidden" name="rid" value="<?php echo $rid ?>" />
	<input type="hidden" name="sid" value="<?php echo $sid ?>" />
	<input type="hidden" name="yid" value="<?php echo $yid ?>" />
	<input type="hidden" name="gid" value="<?php echo $gid ?>" />
	<input type="hidden" name="uid" value="<?php echo $uid ?>" />
	<input type="hidden" name="aid" value="<?php echo $aid ?>" />	
	<input type="hidden" name="lid" value="<?php echo $lid ?>" />
	<input type="hidden" name="plid" value="<?php echo $plid ?>" />
	<input type="hidden" name="portid" value="<?php echo $portid ?>" />
<center>

	<tr valign="top">
    <td align="left"><?php  print_string('review', 'block_mou_school') ?>:</td>
    <td align="left">
    <?php
    	$recreview = '';
    	if (isset($rec->review))  {
    		$recreview = $rec->review;	
    	}
		// <input type="text" id="review" name="review" size="100" value="  " />
		print_textarea(false, 6, 80, 80, 6, 'review', $recreview);
	?>	
    </td>
	</tr>
	
</table>

<?php

		echo '<table cellpadding=10 cellspacing=10 align=center>';
		echo '<tr><td align=center>';
		
	    $CFG->maxbytes = MAX_SCAN_COPY_SIZE; 
	
	    $struploadafile = get_string('loadfiledocs', 'block_mou_att');
	    $strmaxsize = get_string("maxsize", "", display_size($CFG->maxbytes));
	
		echo "<p>$struploadafile($strmaxsize):</p>";
	    upload_print_form_fragment(1,array('newfile'),false,null,0,$CFG->maxbytes,false);
			//$portid = required_param('portid', PARAM_INT);
			$strdelete   = get_string('delete');		
			$school = get_record("monit_school", 'id', $sid);
			$filearea = "0/pupils/$rid/{$school->uniqueconstcode}/$uid/$portid";
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
		                        
		                $delurl  = "delete.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;uid=$uid&amp;gid=$gid&amp;lid=$lid&amp;aid=$aid&amp;plid=$plid&amp;portid=$portid&amp;file=$file";
		                $output .= '<a href="'.$delurl.'">&nbsp;' .'<img title="'.$strdelete.'" src="'.$CFG->pixpath.'/t/delete.gif" class="iconsmall" alt="" /></a><br /> ';	                        
		            }
		        } else {
		        	$output = '<i>' . get_string('isabscent', 'block_mou_att') . '</i>' ;
		        }
		    }
		echo $output;
		
		echo '</td></tr><tr><td align=center>';
		echo '</td></tr></table>'; 	
	
?>
		   <div align="center">
		  <input type="hidden" name="userid" value="<?php p($uid)?>">
		  <input type="submit" name="addfaculty" value="<?php print_string('savechanges')?>">
		  </div>
 </center>
</form>

<?php

	print_footer();

	
function listbox_action($scriptname, $aid)
{
  global $CFG;

  $actionmenu = array();
  $actionmenu[0] = get_string('selectaction', 'block_mou_school').'...';

  if($allactions = get_records_sql("SELECT distinct nameaction FROM {$CFG->prefix}monit_school_actions"))   {
 	 foreach ($allactions as $actions) 	{
      	$actionmenu[$actions->nameaction] = $actions->nameaction;
  	 }
  }

  echo '<tr> <td>'.get_string('actions', 'block_mou_school').': </td><td>';
  popup_form($scriptname, $actionmenu, 'switchaction', $aid, '', '', '', false);
  echo '</td></tr>';
  return 1;
}

function listbox_grade($scriptname, $aid, $lid)
{
  global $CFG;

  $grademenu = array();
  $grademenu[0] = get_string('selectlevel', 'block_mou_school').'...';

  if($allgrade = get_records_sql("SELECT distinct levels FROM {$CFG->prefix}monit_school_actions WHERE levels<>''"))   {
 	 foreach ($allgrade as $grade) 	{
      	$grademenu[$grade->levels] = $grade->levels;
  	 }
  }

  echo '<tr> <td>'.get_string('level', 'block_mou_school').': </td><td>';
  popup_form($scriptname, $grademenu, 'switchlevel', $lid, '', '', '', false);
  echo '</td></tr>';
  return 1;
}

function listbox_place($scriptname, $aid, $lid, $plid)
{
  global $CFG;

  $placemenu = array();
  $placemenu[0] = get_string('selectplace', 'block_mou_school').'...';

  if($allplace = get_records_sql("SELECT distinct place FROM {$CFG->prefix}monit_school_actions WHERE place<>''"))   {
 	 foreach ($allplace as $place) 	{
      	$placemenu[$place->place] = $place->place;
  	 }
  }

  echo '<tr> <td>'.get_string('place', 'block_mou_school').': </td><td>';
  popup_form($scriptname, $placemenu, 'switchplace', $plid, '', '', '', false);
  echo '</td></tr>';
  return 1;
}

?>
