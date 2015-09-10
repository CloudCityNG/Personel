<?php 
$capabilities = array(
 
    'block/managebatches:addinstance' => array(
        'riskbitmask' => RISK_XSS,
 
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
           
            'manager' => CAP_ALLOW
        ),
		 
        'clonepermissionsfrom' => 'moodle/site:manageblocks'
    ),
);
?>