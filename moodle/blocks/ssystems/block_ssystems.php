<?php

class block_ssystems extends block_base {
    function init() {
        $this->title = get_string('ssystems', 'block_simplehtml');
        $this->version = 2022082600;
    }

    function get_content() {
        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '¡Dies ist ein Ssystems Test!';
        $this->content->footer = 'Fußzeile...';

        return $this->content;
    }

}
