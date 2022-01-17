<?php  // $Id: edittb_form.php,v 1.1 2011/01/17 07:16:53 shtifanov Exp $

require_once($CFG->libdir.'/formslib.php');

class edittb_form extends moodleform {
    function definition() {

        global $rid, $sid, $yid, $tbid, $catid;

        $mform =& $this->_form;

        $mform->addElement('header','', get_string('edittextbook', 'block_mou_ege'));

        $mform->addElement('text', 'authors', get_string('authors', 'block_mou_ege'), 'maxlength="255" size="70"');
        $mform->addRule('authors', get_string('missingname'), 'required', null, 'client');
        $mform->setType('authors', PARAM_TEXT);

        $mform->addElement('text', 'name',  get_string('textbookname', 'block_mou_ege'), 'maxlength="255" size="70"');
        $mform->addRule('name', get_string('missingname'), 'required', null, 'client');
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('text', 'numclass',  get_string('textbooknumclass', 'block_mou_ege'), 'maxlength="6" size="6"');
        $mform->addRule('numclass', get_string('missingname'), 'required', null, 'client');
        $mform->setType('numclass', PARAM_TEXT);

        $mform->addElement('text', 'publisher',  get_string('publisher', 'block_mou_ege'), 'maxlength="100" size="70"');
        $mform->addRule('publisher', get_string('missingname'), 'required', null, 'client');
        $mform->setType('publisher', PARAM_TEXT);

		$mform->addElement('hidden', 'rid', $rid);    $mform->setType('rid', PARAM_INT);
		$mform->addElement('hidden', 'sid', $sid);    $mform->setType('sid', PARAM_INT);		
		$mform->addElement('hidden', 'yid', $yid);    $mform->setType('yid', PARAM_INT);
		$mform->addElement('hidden', 'tbid', $tbid);  $mform->setType('tbid', PARAM_INT);
		$mform->addElement('hidden', 'catid', $catid);  $mform->setType('catid', PARAM_INT);

        $this->add_action_buttons();
    }

    function validation($data) {
        $errors = array();

        if (0 == count($errors)){
            return true;
        } else {
            return $errors;
        }

    }

}
?>
