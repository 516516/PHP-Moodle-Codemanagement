<?php

defined('MOODLE_INTERNAL') || die();
//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class mod_codemanagement_classes_installzipfile_form extends moodleform{
    
    public function definition()
    {
        $mform=$this->_form;
        $installer=$this->_customdata["installer"];
        
        $mform->addElement('header', 'general', get_string('installfromzip', 'codemanagement'));
        $mform->addHelpButton('general', 'installfromzip', 'codemanagement');
        
        $mform->addElement('filepicker', 'zipfile', get_string('installfromzipfile', 'codemanagement'),null, array('accepted_types' => '.zip'));
        $mform->addHelpButton('zipfile', 'installfromzipfile', 'codemanagement');
        $mform->addRule('zipfile', null, 'required', null, 'client');
        
        $mform->addElement('textarea', 'description', get_string('versiondescription', 'codemanagement'),'wrap="virtual" rows="5" cols="66"' );
        $mform->setType('description', PARAM_TEXT);
        $mform->addRule('description', get_string('required'), 'required', null, 'client');
        $mform->addRule('description', get_string('maximumchars', '', 255), 'maxlength', 255, 'client' );
        $mform->addHelpButton('description', 'versiondescription', 'codemanagement');
        
        //测试--文件下载遇到问题了添加文件管理
        //$mform->addElement('filemanager', 'attachments', format_string('File Manager Example'),null, $this->get_filemanager_options_array());
        
        $this->add_action_buttons(true, get_string('installfromzipsubmit', 'codemanagement'));
    }
    
    function get_filemanager_options_array () {
        return array(
            'subdirs' => 0,
            'maxbytes' => 0,
            'maxfiles' => 4,
            'accepted_types' => array('*')
            
        );
    }
}