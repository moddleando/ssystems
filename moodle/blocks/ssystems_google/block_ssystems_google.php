<?php
class block_ssystems_google extends block_base {

    public function init() {
        $this->title = get_string('ssystems_google', 'block_ssystems_google'); //App Name
    }

     
    public function get_content() {
        global $COURSE;
        include 'globalVariable.php'; //
        if ($this->content !== null) {
            return $this->content;
        }
        
        $this->content  =  new stdClass;

        //$google = 'https://www.googleapis.com/customsearch/v1?key='.$API_GOOGLE.'&cx='.$ID_SEARCHENGINE.'&q=Moodle+Blocks';

        $customGoogle = "https://cse.google.com/cse?cx=459d75ded43e048c9";

        $search = file_get_contents($customGoogle);

        if ($search !== false){   
            $this->content->text = $customGoogle;
        } else{
            $this->content->text = "'Something is wrong in your search:'";
        }

        //$this -> content -> footer = "Footer here...";

        $url = new moodle_url('/blocks/ssystems_google/view.php', array('blockid' => $this->instance->id, 'courseid' => $COURSE->id));
        $this->content->footer = html_writer::link($url, get_string('addpage', 'block_ssystems_google'));

        return $this->content;
    }



}
