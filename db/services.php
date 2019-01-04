<?php


$functions = array(

  // === manage instances === //

  'enrol_external_add_instance' => array(
    'classname'   => 'enrol_external_webservices',
    'methodname'  => 'external_add_instance',
    'classpath'   => 'enrol/external/externallib.php',
    'description' => 'Add enrolment methods instance to a course',
    'capabilities'=> 'enrol/external:config',
    'type'        => 'write',
  ),

  'enrol_external_remove_instance' => array(
    'classname'   => 'enrol_external_webservices',
    'methodname'  => 'external_remove_instance',
    'classpath'   => 'enrol/external/externallib.php',
    'description' => 'Remove enrolment methods instance from a course',
    'capabilities'=> 'enrol/external:config',
    'type'        => 'write',
  ),

  // === query functions === //

  'enrol_external_list_instances' => array(
    'classname'   => 'enrol_external_webservices',
    'methodname'  => 'external_list_instances',
    'classpath'   => 'enrol/external/externallib.php',
    'description' => 'List external instances in a course',
    'capabilities'=> 'enrol/external:config',
    'type'        => 'read',
  ),

  // === enrol related functions ===
  'enrol_external_enrol_users' => array(
    'classname'   => 'enrol_external_webservices',
    'methodname'  => 'external_enrol_users',
    'classpath'   => 'enrol/external/externallib.php',
    'description' => 'Enrol users',
    'capabilities'=> 'enrol/external:enrol',
    'type'        => 'write',
  ),

  'enrol_external_unenrol_users' => array(
    'classname'   => 'enrol_external_webservices',
    'methodname'  => 'external_unenrol_users',
    'classpath'   => 'enrol/external/externallib.php',
    'description' => 'Unenrol users',
    'capabilities'=> 'enrol/external:unenrol',
    'type'        => 'write',
  ),

  'enrol_external_add_sits_enrolments' => array(
    'classname'   => 'enrol_external_webservices',
    'methodname'  => 'external_add_sits_enrolments',
    'classpath'   => 'enrol/external/externallib.php',
    'description' => 'Add enrolments for SITS module',
    'capabilities'=> 'enrol/external:enrol',
    'type'        => 'write',
  ),

  'enrol_external_remove_sits_enrolments' => array(
    'classname'   => 'enrol_external_webservices',
    'methodname'  => 'external_remove_sits_enrolments',
    'classpath'   => 'enrol/external/externallib.php',
    'description' => 'Remove enrolments for SITS module',
    'capabilities'=> 'enrol/external:enrol',
    'type'        => 'write',
  ),


);
