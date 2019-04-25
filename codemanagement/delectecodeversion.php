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
$versionid=optional_param('versionid', 0, PARAM_INT);

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

/* $event = \codemanagement\event\course_module_viewed::create(array(
 'objectid' => $moduleinstance->id,
 'context' => $modulecontext
 ));
 $event->add_record_snapshot('course', $course);
 $event->add_record_snapshot('codemanagement', $moduleinstance);
 $event->trigger(); */

$PAGE->set_url('/mod/codemanagement/downloadcode.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
// ，删除项目文件函数
delect_codeversion_from_moodledata($versionid,$reponsitoryid,$id,$c);
echo $OUTPUT->footer();