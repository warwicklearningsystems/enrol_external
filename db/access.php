<?php

/**
 * Capabilities for cohort access plugin.
 *
 * @package    enrol_external
 * @copyright  2017 Russell Boyatt
 *
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

  'enrol/external:config' => array(

    'captype' => 'write',
    'contextlevel' => CONTEXT_COURSE,
    'archetypes' => array(
      'editingteacher' => CAP_ALLOW,
      'manager' => CAP_ALLOW,
    )
  ),

  'enrol/external:enrol' => array(

    'captype' => 'write',
    'contextlevel' => CONTEXT_COURSE,
    'archetypes' => array(
      'manager' => CAP_ALLOW,
    )
  ),

  /* This is used only when sync suspends users instead of full unenrolment. */
  'enrol/external:unenrol' => array(

    'captype' => 'write',
    'contextlevel' => CONTEXT_COURSE,
    'archetypes' => array(
      'manager' => CAP_ALLOW,
    )
  ),

  'enrol/external:managelocal' => array(

    'captype' => 'write',
    'contextlevel' => CONTEXT_COURSE,
    'archetypes' => array()
  ),

  'enrol/external:unenrollocal' => array(

    'captype' => 'write',
    'contextlevel' => CONTEXT_COURSE,
    'archetypes' => array()
  ),

  'enrol/external:manage' => array(

    'captype' => 'write',
    'contextlevel' => CONTEXT_COURSE,
    'archetypes' => array()
  ),

);
