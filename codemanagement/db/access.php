<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'mod/codemanagement:addinstance' => array(
        'riskbitmask' => RISK_SPAM,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager'=>CAP_ALLOW,
            'coursecreator'=>CAP_ALLOW,
            'teacher'=>CAP_ALLOW,
            'student'=>CAP_ALLOW,
            'guest'=>CAP_ALLOW,
            'user'=>CAP_ALLOW,
            'frontpage'=>CAP_ALLOW
        )
    ),
    
    'mod/codemanagement:viewallreponsitory' => array(
        'riskbitmask' => RISK_SPAM,
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager'=>CAP_ALLOW,
            'coursecreator'=>CAP_ALLOW,
            'teacher'=>CAP_ALLOW,
            'student'=>CAP_PREVENT,
            'guest'=>CAP_PREVENT,
            'user'=>CAP_PREVENT,
            'frontpage'=>CAP_PREVENT
        )
    ),
    
    'mod/codemanagement:createrepository'=>array(
        'riskbitmask' => RISK_SPAM,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_PREVENT,
            'manager'=>CAP_PREVENT,
            'coursecreator'=>CAP_PREVENT,
            'teacher'=>CAP_PREVENT,
            'student'=>CAP_ALLOW,
            'guest'=>CAP_PREVENT,
            'user'=>CAP_PREVENT,
            'frontpage'=>CAP_PREVENT
        )
    ),
    
);
?>