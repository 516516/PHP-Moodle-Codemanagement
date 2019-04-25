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
$compare = optional_param('compare',-1, PARAM_INT);
$comparewith = optional_param('comparewith',-1, PARAM_INT);

//持有变化文件所属版本id
$versionid = optional_param('versionid',-1, PARAM_INT);
$versionid_file_index = optional_param('versionid_file_index',-1, PARAM_INT);

//被对比文件所属版本id
$versionid_comparewith = optional_param('versionid_comparewith',-1, PARAM_INT);
$versionid_comparewith_file_index = optional_param('versionid_comparewith_file_index',-1, PARAM_INT);

$is_add_or_delete = optional_param('is_add_or_delete',-1, PARAM_INT);

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

/* $event = \codemanagement\event\course_module_viewed::create(array(
 'objectid' => $moduleinstance->id,
 'context' => $modulecontext
 ));
 $event->add_record_snapshot('course', $course);
 $event->add_record_snapshot('codemanagement', $moduleinstance);
 $event->trigger(); */
$PAGE->requires->js('/mod/codemanagement/codemirror.js');
$PAGE->requires->js('/mod/codemanagement/prism.js');
$PAGE->requires->js('/mod/codemanagement/differ_patch.js');

$PAGE->set_url('/mod/codemanagement/viewdifferencebetweentwoversions.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
echo $OUTPUT->header();
get_different_files_form_two_versions($reponsitoryid,$id,$c,$compare,$comparewith,$versionid,$versionid_file_index,$versionid_comparewith,$versionid_comparewith_file_index,$is_add_or_delete);
echo $OUTPUT->footer();