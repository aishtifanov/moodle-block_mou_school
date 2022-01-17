<?php  // $Id: delete.php,v 1.6 2009/12/25 08:59:40 Oleg Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');    
    require_once('../../mou_att/lib_att.php');
    require_once($CFG->libdir.'/tablelib.php');
    require_once($CFG->libdir.'/uploadlib.php');
	require_once($CFG->libdir.'/filelib.php');    
    require_once('../../mou_accredit/lib_accredit.php');
	   
    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = required_param('sid', PARAM_INT);          // School id
	$yid = required_param('yid', PARAM_INT);       // Year id
    $gid = required_param('gid', PARAM_INT);    // Udod id
    $uid = required_param('uid', PARAM_INT);    // DOU id
    $portid = required_param('portid', PARAM_INT);    // DOU id
   // $aid = required_param('aid');      // action id
//	$lid = required_param('lid');      // level id
	//$plid = required_param('plid');      // place id
    $confirm  = optional_param('confirm', 0, PARAM_BOOL);
    $mode  = optional_param('mode', '');
    $file = required_param('file');
   // print $portid;
	require_once('../authall.inc.php');

    //$criteria =  get_record('monit_accr_criteria', 'id', $cid);

    $straccreditation = get_string('accreditation', 'block_monitoring');
    $strtitle = get_string('deleting', 'block_mou_school');
    $strindicator = get_string('onecriteria', 'block_monitoring');
	$school = get_record("monit_school", 'id', $sid);
	$strclass = get_string('class','block_mou_school');
	$strpupils = get_string('pupils', 'block_mou_school');
    $strclasses = get_string('classes','block_mou_school');
		
	$breadcrumbs = '<a href="'.$CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('title','block_mou_school').'</a>';
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/mou_school/class/classlist.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">$strclasses</a>";
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/mou_school/class/classpupils.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid\">$strpupils</a>";
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/mou_school/class/review.php?mode=4&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid=$uid\">$strtitle</a>";
	$breadcrumbs .= " -> $strtitle";
    print_header_mou("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

    $mode = 4;
    $returnurl = "docoffice.php?mode=4&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;uid=$uid&amp;portid=$portid";
    $optionsreturn = array('rid'=>$rid, 'sid'=>$sid, 'gid'=>$gid, 'uid'=>$uid, 'yid'=>$yid, 'mode'=>$mode,
							 'portid' => $portid);

    if (!$confirm) {
        $optionsyes = $optionsreturn;
		$optionsyes['file'] = $file;
		$optionsyes['confirm']=1;
		$optionsyes['sesskey'] = sesskey();
        print_heading(get_string('delete'));
        notice_yesno(get_string('confirmdeletefile', 'assignment', $file), 'delete.php', $returnurl, $optionsyes, $optionsreturn, 'post', 'get');
        print_footer('none');
        die;
    }


    $filepath = $CFG->dataroot."/0/pupils/$rid/{$school->uniqueconstcode}/$uid/$portid/$file";
    //echo $filepath;
    if (file_exists($filepath)) {
        if (@unlink($filepath)) {
            redirect($returnurl, get_string('clamdeletedfile') , 0);
        }
    }

  //   print delete error;
  //  print_header(get_string('delete'));
    notify(get_string('deletefilefailed', 'assignment'));
    print_continue($returnurl);
    print_footer('none');
?>
