<?php
require_once("$CFG->libdir/formslib.php");

class ssystems_google_form extends moodleform {
    
    protected function definition() {
        global $CFG;
        
        $mform =& $this->_form;

        $mform->addElement('text','_text',get_string('sentence'));
        $mform->setDefault('_text', PARAM_NOTAGS);
        $mform->setDefault('_text', 'Please, introduce a sentence or word to search');

        $radioarray = array();
        $radioarray[] = $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $radioarray[] = $mform->createElement('reset', 'resetbutton', get_string('revert'));
        $radioarray[] = $mform->createElement('cancel');
        $mform -> addGroup($radioarray, 'buttonar', '', ' ',false);

    }
}
