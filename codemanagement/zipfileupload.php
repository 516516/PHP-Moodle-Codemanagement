<?php

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once(__DIR__.'/classes/installzipfile.php');
require_once(__DIR__.'/classes/create_reponsitory.php');

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

/*
 echo("<script>console.log('course_modle_id:".$id."')</script>");
 echo("<script>console.log('course_modle_id:".$USER->id."')</script>");
 echo("<script>console.log('modle_instance_id:".$c."')</script>");
 echo("<script>console.log('object_cm:".json_encode($cm)."')</script>");
 echo("<script>console.log('object_course:".json_encode($course)."')</script>");
 echo("<script>console.log('object_moduleinstance:".json_encode($moduleinstance)."')</script>");
 */

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);
$context = context_system::instance();

/* $event = \codemanagement\event\course_module_viewed::create(array(
 'objectid' => $moduleinstance->id,
 'context' => $modulecontext
 ));
 $event->add_record_snapshot('course', $course);
 $event->add_record_snapshot('codemanagement', $moduleinstance);
 $event->trigger(); */

$PAGE->set_url('/mod/codemanagement/zipfileupload.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);


$codeversion=mod_codemanagement_classes_installzipfile::instance();
$codeversion->setId($id);
$codeversion->setC($c);
$codeversion->setReponsitoryId($reponsitoryid);

//，上传文件处理---参考插件上传
$pluginman = core_plugin_manager::instance();

$mform=$codeversion->get_installfromzip_form();
if ($mform->is_cancelled()) {
    // ，取消跳转
    redirect(new moodle_url('/mod/codemanagement/view.php', array('id'=>$id,'c'=>$c)));
} else if ($fromform = $mform->get_data()) {
    
    //，moodledata中存储版本的文件夹-每次都会新建一个文件夹。
    //     $storage=make_installfromzip_storage();
    $storage=make_version_storage();
    echo("<script>console.log('storage:".json_encode($storage)."')</script>");
    
    $codezipfilename = $mform->get_new_filename('zipfile');
    echo("<script>console.log('codezipfilename:".json_encode($codezipfilename)."')</script>");
    
    //，保存zip文件到指定的文件夹下。
    $success=$mform->save_file('zipfile', $storage.'/'.$codezipfilename);
    echo("<script>console.log('success:".json_encode($success)."')</script>");
    
    
    if($success){
        // ，版本信息及版本仓库对应信息入库。
        codemanagement_version_and_reponsitory_save($reponsitoryid, $fromform, $storage, $codezipfilename);
    }
    
    //，版本上传成功后自动跳转
    redirect(new moodle_url('/mod/codemanagement/codeversionslist.php', array('id'=>$id,'c'=>$c,'reponsitoryid'=>$reponsitoryid)));
    
} else {
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}