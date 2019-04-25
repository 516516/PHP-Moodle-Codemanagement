<?php

defined('MOODLE_INTERNAL') || die();
//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class mod_codemanagement_classes_create_responsitory_form extends moodleform{
    public function definition()
    {
        $mform=$this->_form;
        
        $mform->addElement('header', 'general', get_string('createreponsitory', 'codemanagement'));
        $mform->addHelpButton('general', 'installfromzip', 'codemanagement');
        
        $mform->addElement('text', 'name', get_string('reponsitoryname', 'codemanagement'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'reponsitoryname', 'codemanagement');
        $mform->setDefault('name','XXXreponsitory');
        
        $mform->addElement('textarea', 'description', get_string('intro', 'codemanagement'),'wrap="virtual" rows="5" cols="66"' );
        $mform->setType('description', PARAM_TEXT);
        $mform->addRule('description', get_string('required'), 'required', null, 'client');
        $mform->addRule('description', get_string('maximumchars', '', 255), 'maxlength', 255, 'client' );
        $mform->addHelpButton('description', 'intro', 'codemanagement');
        
        $options = array();
        $options[1] = 'private';
        $options[2] = 'public';
        
        $mform->addElement('select', 'private_public_id', get_string('responsitoryprivateorpoublic', 'codemanagement'), $options);
        $mform->addRule('private_public_id', null, 'required', null, 'client');
        $mform->addHelpButton('private_public_id', 'responsitoryprivateorpoublic', 'codemanagement');
        
        $this->add_action_buttons(true, get_string('responsitorysubmit', 'codemanagement'));
    }
}