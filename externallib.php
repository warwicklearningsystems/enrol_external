<?php

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

/**
 * External enrolment external functions.
 *
 * @package   enrol_external
 * @copyright 2018 Russell Boyatt <russell.boyatt@warwick.ac.uk>
 */
class enrol_external_webservices extends external_api {

  /* Manage instances */

  public static function external_add_instance_parameters()
  {
    return new external_function_parameters(

      array(
        'courseid' => new external_value(PARAM_INT, "Course ID"),
        'name' => new external_value(PARAM_TEXT, 'Name of enrolment method')
      )

    );
  }

  public static function external_add_instance_returns() {
    return new external_single_structure(
      array(
        'status' => new external_value(PARAM_BOOL, 'Success')
      )
    );
  }

  public static function external_add_instance($courseid, $name) {

    global $DB;

    $n = new stdClass();
    $n->status = TRUE;

    $params = self::validate_parameters(self::external_add_instance_parameters(),
      array('courseid' => $courseid, 'name' => $name));

    // Find the course
    $course = get_course($params['courseid']);

    // Find the plugin
    $plugin = enrol_get_plugin('external');

    // If both course and plugin are valid, then let's add the default
    // instance of this enrolment method
    if($plugin && $course) {
      $instanceid = $plugin->add_instance($course, array('name' => $name));

      // If we managed to add an instance, let's enable it
      if( !$instanceid ) {
        $n->status = FALSE;
      }

    } else {
      $n->status = FALSE;
    }

    return $n;
  }

  public static function external_remove_instance_parameters()
  {
    return new external_function_parameters(

      array(
        'courseid' => new external_value(PARAM_INT, "Course ID"),
        'instanceid' => new external_value(PARAM_INT, "Enrolment instance ID"),
      )

    );

  }

  public static function external_remove_instance_returns() {
    return new external_single_structure(
      array(
        'status' => new external_value(PARAM_BOOL, 'Success')
      )
    );
  }

  public static function external_remove_instance() {

  }


  /* Enrol users */

  public static function external_enrol_users_parameters() {

    return new external_function_parameters(
      array(
        'enrol' => new external_multiple_structure(
          new external_single_structure(
            array(
              'courseid' => new external_value(PARAM_INT, "Course ID"),
              'instanceid' => new external_value(PARAM_INT, "Enrolment instance ID"),
              'userid' => new external_value(PARAM_INT, 'User ID'),
            )
          )
        )
      )
    );

  }

  public static function external_enrol_users_returns() {
    return new external_single_structure(
      array(
        'status' => new external_value(PARAM_BOOL, 'Success')
      )
    );
  }

  public static function external_enrol_users() {

  }

  /* Unenrol users */

  public static function external_unenrol_users_parameters() {

    return new external_function_parameters(
      array(
        'unenrol' => new external_multiple_structure(
          new external_single_structure(
            array(
              'courseid' => new external_value(PARAM_INT, "Course ID"),
              'userid' => new external_value(PARAM_INT, 'User ID'),
            )
          )
        )
      )
    );

  }

  public static function external_unenrol_users_returns() {
    return new external_single_structure(
      array(
        'status' => new external_value(PARAM_BOOL, 'Success')
      )
    );

  }

  public static function external_unenrol_users() {

  }

  /* List instances in a course */

  public static function external_list_instances_parameters() {
    return new external_function_parameters(
      array(
        'courseid' => new external_value(PARAM_INT, "Course ID")
      )
    );
  }

  public static function external_list_instances_returns() {
    return new external_multiple_structure(
      new external_single_structure(
        array(
          'id' => new external_value(PARAM_INT, "Instance ID"),
          'name' => new external_value(PARAM_TEXT, 'Name of enrolment method')
        )
      )
    );
  }

  public static function external_list_instances($courseid) {

    global $DB;

    $n = array();

    $params = self::validate_parameters(self::external_list_instances_parameters(),
      array('courseid' => $courseid));

    // Find the course
    $course = get_course($params['courseid']);

    // Find the plugin
    $plugin = enrol_get_plugin('external');

    // If both course and plugin are valid, then let's add the default
    // instance of this enrolment method
    if($plugin && $course) {

      $enrolinstances = enrol_get_instances($course->id, true);

      foreach($enrolinstances as $courseenrolinstance) {

        if ($courseenrolinstance->enrol == "external") {
          $i = new stdClass();

          $i->id = $courseenrolinstance->id;
          $i->name = $courseenrolinstance->name;

          $n[] = $i;
        }

      }

    }

    return $n;

  }


}