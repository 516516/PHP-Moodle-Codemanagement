<?php

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once(__DIR__.'/classes/installzipfile.php');
require_once(__DIR__.'/classes/create_reponsitory.php');

// Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);
// ... module instance id.
$c  = optional_param('c', 0, PARAM_INT);

if ($id) {
    $cm             = get_coursemodule_from_id('codemanagement', $id, 0, false, MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('codemanagement', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($c) {
    $moduleinstance = $DB->get_record('codemanagement', array('id' => $c), '*', MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm             = get_coursemodule_from_instance('codemanagement', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error(get_string('missingidandcmid', codemanagement));
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$PAGE->set_url('/mod/codemanagement/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
echo $OUTPUT->header();

/*��ʾʵ������*/
show_instance_information($moduleinstance);

if (has_capability('mod/codemanagement:createrepository', $modulecontext)) {
    $reponsitory=mod_codemanagement_classes_create_responsitory::instance();
    $reponsitory->setId($id);
    $reponsitory->setC($c);
    $mform_reponsitory=$reponsitory->get_installfromzip_form();
    if ($mform_reponsitory->is_cancelled()) {
        //�������ֿ�����ȡ����ťʱ�ض���
        redirect($PAGE->url);
    } else if ($fromform = $mform_reponsitory->get_data()) {
        //�������ֿ�form���ύʱ��ȡ�������
        codemanagement_reponsitory_insert($fromform,$moduleinstance);
        echo $OUTPUT->notification(get_string('codemanagementreponsitoryinsert', 'codemanagement'), 'notifysuccess');
        echo $OUTPUT->continue_button(new moodle_url('/mod/codemanagement/view.php', array('id'=>$id,'c'=>$c)));
    } else {
        //�����ú�����ʾ�ֿ��б�
        codemanagement_reponsitory_showtable($id,$c,$moduleinstance,$modulecontext);
        $mform_reponsitory->display();
    }
}else{
    codemanagement_reponsitory_showtable($id,$c,$moduleinstance,$modulecontext);
}

echo $OUTPUT->footer();