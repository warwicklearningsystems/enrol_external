<?php


$functions = array(

  // === enrol related functions ===
  'enrol_external_enrol_users' => array(
    'classname'   => 'enrol_external_external',
    'methodname'  => 'enrol_users',
    'classpath'   => 'enrol/external/externallib.php',
    'description' => 'Enrol users',
    'capabilities'=> 'enrol/external:enrol',
    'type'        => 'write',
  ),

  'enrol_external_unenrol_users' => array(
    'classname'   => 'enrol_external_external',
    'methodname'  => 'unenrol_users',
    'classpath'   => 'enrol/external/externallib.php',
    'description' => 'Unenrol users',
    'capabilities'=> 'enrol/external:unenrol',
    'type'        => 'write',
  ),

  'enrol_external_list_instances' => array(
    'classname'   => 'enrol_external_external',
    'methodname'  => 'list_instances',
    'classpath'   => 'enrol/external/externallib.php',
    'description' => 'List instances',
    'capabilities'=> 'enrol/external:listinstances',
    'type'        => 'read',
  ),

  'enrol_external_add_sits_enrolments' => array(
    'classname'   => 'enrol_external_external',
    'methodname'  => 'add_sits_enrolments',
    'classpath'   => 'enrol/external/externallib.php',
    'description' => 'Add enrolments for SITS module',
    'capabilities'=> 'enrol/external:listinstances',
    'type'        => 'write',
  ),

);
