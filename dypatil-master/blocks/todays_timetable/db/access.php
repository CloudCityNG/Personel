<?php 
$capabilities = array(
 
    'block/todays_timetable:addinstance' => array(
        'riskbitmask' => RISK_XSS,
 
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
		 
        'clonepermissionsfrom' => 'moodle/site:manageblocks'
    ),
	 'block/todays_timetable:viewlist' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'student' => CAP_ALLOW,
        )
    )
);
?>