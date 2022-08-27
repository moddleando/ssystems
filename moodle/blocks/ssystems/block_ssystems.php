<?php
class block_ssystems extends block_base {

    public function init() {
        $this->title = get_string('ssystems', 'block_systems'); //App Name
    }

     
    public function get_content() {
        include 'globalVariable.php'; //
        if ($this->content !== null) {
            return $this->content;
        }
        
        $this->content         =  new stdClass;

        $google = 'https://www.googleapis.com/customsearch/v1?key='.$API_GOOGLE.'&cx='.$ID_SEARCHENGINE.'&q=Moodle+Blocks';

        $search = file_get_contents($google);

        if ($search !== false){   
            $this->content->text = $search;
        } else{
            $this->content->text = "'Something is wrong in your search:'.$API_GOOGLE.' or '.$ID_SEARCHENGINE";
        }

        return $this->content;
    }



}
