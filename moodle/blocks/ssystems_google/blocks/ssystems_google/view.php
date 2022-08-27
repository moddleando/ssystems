<?php

require_once('../../config.php');
require_once('simplehtml_form.php');

global $DB;

// Check for all required variables.
$courseid = required_param('courseid', PARAM_INT);


if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$simplehtml = new simplehtml_form();

$simplehtml->display();
?>