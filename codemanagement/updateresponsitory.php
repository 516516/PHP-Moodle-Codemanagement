<?php

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once(__DIR__.'/classes/installzipfile.php');
require_once(__DIR__.'/classes/update_reponsitory.php');

// Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);
// ... module instance id.
$c  = optional_param('c', 0, PARAM_INT);
$reponsitoryid  = optional_param('reponsitoryid', 0, PARAM_INT);

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

$PAGE->set_url('/mod/codemanagement/updateresponsitory.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);


$updateresponsitory=mod_codemanagement_classes_update_responsitory::instance();
$updateresponsitory->setId($id);
$updateresponsitory->setC($c);
$updateresponsitory->setResponsitory($reponsitoryid);
$updateresponsitory->setReName(get_re_name($reponsitoryid));
$updateresponsitory->setReDescrition(get_re_reponsitory($reponsitoryid));


$update_responsitory_form=$updateresponsitory->get_installfromzip_form();
if ($update_responsitory_form->is_cancelled()) {
    //、创建仓库表单点击取消按钮时重定向
    redirect(new moodle_url('/mod/codemanagement/view.php', array('id'=>$id,'c'=>$c)));
} else if ($fromform = $update_responsitory_form->get_data()) {
    //、创建仓库form表单提交时获取数据入库
    codemanagement_reponsitory_update($fromform,$reponsitoryid);
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('codemanagementreponsitoryupdate', 'codemanagement'), 'notifysuccess');
    echo $OUTPUT->continue_button(new moodle_url('/mod/codemanagement/view.php', array('id'=>$id,'c'=>$c)));
    echo $OUTPUT->footer();
} else {
    //、调用函数显示仓库列表
    echo $OUTPUT->header();
    $update_responsitory_form->display();
    echo $OUTPUT->footer();
}

