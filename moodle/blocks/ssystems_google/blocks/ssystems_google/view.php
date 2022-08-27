<?php

require_once('../../config.php');
require_once('ssystems_google_form.php');

global $DB;

// Check for all required variables.
$courseid = required_param('courseid', PARAM_INT);


if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_ssystems_google', $courseid);
}

require_login($course);

$ssystems_google = new ssystems_google_form();

$ssystems_google->display();
?>