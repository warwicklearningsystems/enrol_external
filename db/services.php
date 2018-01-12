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

);
