<?php
require_once('ssystems_google_form.php');
require_once('function.php');
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


        $mform = new ssystems_google_form();

        $this->content->text =$mform->render();

        if ($fromform =$mform -> get_data()) {
            $sentence = $mform->text;
        }
        
        $customGoogle = googleSearch($sentence);

        $search = file_get_contents($customGoogle);

        if ($search !== false){   
            $this->content->text = $search;
        } else{
            $this->content->text = "'Something is wrong in your search:'";
        }

        return $this->content;
    }



}
