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
        'status' => new external_value(PARAM_BOOL, 'Success'),
        'instanceid' => new external_value(PARAM_INT, "Instance ID", VALUE_OPTIONAL)
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
      if( $instanceid ) {
        // Return instance ID if successful
        $n->instanceid = $instanceid;
      } else {
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

  public static function external_remove_instance($courseid, $instanceid) {
    global $DB;

    $n = new stdClass();
    $n->status = TRUE;

    $params = self::validate_parameters(self::external_remove_instance_parameters(),
      array('courseid' => $courseid, 'instanceid' => $instanceid));

    // Find the course
    $course = get_course($params['courseid']);

    // Find the plugin
    $plugin = enrol_get_plugin('external');

    // If both course and plugin are valid, then let's add the default
    // instance of this enrolment method
    if($plugin && $course) {

      $enrolinstances = enrol_get_instances($course->id, true);

      foreach($enrolinstances as $courseenrolinstance) {

        if ($courseenrolinstance->id == $params['instanceid']) {
          $plugin->delete_instance($courseenrolinstance);
        }

      }

    } else {
      $n->status = FALSE;
    }

    return $n;
  }

  /* Remove all instances */

  public static function external_remove_all_instances_parameters()
  {
    return new external_function_parameters(

      array(
        'courseid' => new external_value(PARAM_INT, "Course ID")
      )

    );

  }

  public static function external_remove_all_instances_returns() {
    return new external_single_structure(
      array(
        'status' => new external_value(PARAM_BOOL, 'Success')
      )
    );
  }

  public static function external_remove_all_instances($courseid) {
    global $DB;

    $n = new stdClass();
    $n->status = TRUE;

    $params = self::validate_parameters(self::external_remove_instance_parameters(),
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
          $plugin->delete_instance($courseenrolinstance);
      }

    } else {
      $n->status = FALSE;
    }

    return $n;
  }


  /* Enrol users */

  public static function external_enrol_users_parameters() {

    return new external_function_parameters(
      array(
        'enrolments' => new external_multiple_structure(
          new external_single_structure(
            array(
              'courseid' => new external_value(PARAM_INT, "Course ID"),
              'instanceid' => new external_value(PARAM_INT, "Enrolment instance ID"),
              'userid' => new external_value(PARAM_INT, 'User ID'),
              'roleid' => new external_value(PARAM_INT, 'Role ID'),
              'timestart' => new external_value(PARAM_INT, 'Timestamp when the enrolment start', VALUE_OPTIONAL),
              'timeend' => new external_value(PARAM_INT, 'Timestamp when the enrolment end', VALUE_OPTIONAL),
              'suspend' => new external_value(PARAM_INT, 'set to 1 to suspend the enrolment', VALUE_OPTIONAL)
            )
          )
        )
      )
    );

  }

  public static function external_enrol_users_returns() {
    return null;
  }

  public static function external_enrol_users($enrolments) {

    global $DB, $CFG;

    require_once($CFG->libdir . '/enrollib.php');

    $params = self::validate_parameters(self::external_enrol_users_parameters(),
      array('enrolments' => $enrolments));

    $transaction = $DB->start_delegated_transaction(); // Rollback all enrolment if an error occurs
    // (except if the DB doesn't support it).

    // Retrieve the manual enrolment plugin.
    $enrol = enrol_get_plugin('external');
    // TODO: fix for external
    if (empty($enrol)) {
      throw new moodle_exception('manualpluginnotinstalled', 'enrol_manual');
    }

    foreach ($params['enrolments'] as $enrolment) {
      // Ensure the current user is allowed to run this function in the enrolment context.
      $context = context_course::instance($enrolment['courseid'], IGNORE_MISSING);
      self::validate_context($context);

      // Check that the user has the permission to manual enrol.
      require_capability('enrol/external:enrol', $context);

      // Throw an exception if user is not able to assign the role.
      $roles = get_assignable_roles($context);
      if (!array_key_exists($enrolment['roleid'], $roles)) {
        $errorparams = new stdClass();
        $errorparams->roleid = $enrolment['roleid'];
        $errorparams->courseid = $enrolment['courseid'];
        $errorparams->userid = $enrolment['userid'];
        throw new moodle_exception('wsusercannotassign', 'enrol_manual', '', $errorparams);
      }

      // Check external enrolment plugin instance is enabled/exist.
      $instance = null;
      $enrolinstances = enrol_get_instances($enrolment['courseid'], true);
      foreach ($enrolinstances as $courseenrolinstance) {
        if ($courseenrolinstance->id == $enrolment['instanceid']) {
          $instance = $courseenrolinstance;
          break;
        }
      }

      if (empty($instance)) {
        // TODO: fix for external
        $errorparams = new stdClass();
        $errorparams->courseid = $enrolment['courseid'];
        throw new moodle_exception('wsnoinstance', 'enrol_manual', $errorparams);
      }

      // Check that the plugin accept enrolment (it should always the case, it's hard coded in the plugin).
      if (!$enrol->allow_enrol($instance)) {
        $errorparams = new stdClass();
        $errorparams->roleid = $enrolment['roleid'];
        $errorparams->courseid = $enrolment['courseid'];
        $errorparams->userid = $enrolment['userid'];
        // TODO: fix for external
        throw new moodle_exception('wscannotenrol', 'enrol_manual', '', $errorparams);
      }

      // Finally proceed the enrolment.
      $enrolment['timestart'] = isset($enrolment['timestart']) ? $enrolment['timestart'] : 0;
      $enrolment['timeend'] = isset($enrolment['timeend']) ? $enrolment['timeend'] : 0;
      $enrolment['status'] = (isset($enrolment['suspend']) && !empty($enrolment['suspend'])) ?
        ENROL_USER_SUSPENDED : ENROL_USER_ACTIVE;

      $enrol->enrol_user($instance, $enrolment['userid'], $enrolment['roleid'],
        $enrolment['timestart'], $enrolment['timeend'], $enrolment['status']);

    }

    $transaction->allow_commit();

  }

  /* Unenrol users */

  public static function external_unenrol_users_parameters() {

    return new external_function_parameters(
      array(
        'enrolments' => new external_multiple_structure(
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

  public static function external_unenrol_users_returns() {
    return null;
  }

  public static function external_unenrol_users($enrolments) {
    global $CFG, $DB;
    $params = self::validate_parameters(self::external_unenrol_users_parameters(), array('enrolments' => $enrolments));
    require_once($CFG->libdir . '/enrollib.php');
    $transaction = $DB->start_delegated_transaction(); // Rollback all enrolment if an error occurs.
    $enrol = enrol_get_plugin('external');
    if (empty($enrol)) {
      // TODO: update for external
      throw new moodle_exception('manualpluginnotinstalled', 'enrol_manual');
    }

    foreach ($params['enrolments'] as $enrolment) {
      $context = context_course::instance($enrolment['courseid']);
      self::validate_context($context);
      require_capability('enrol/external:unenrol', $context);
      $instance = $DB->get_record('enrol', array('courseid' => $enrolment['courseid'], 'id' => $enrolment['instanceid'], 'enrol' => 'external'));
      if (!$instance) {
        // TODO: update for external
        throw new moodle_exception('wsnoinstance', 'enrol_manual', $enrolment);
      }
      $user = $DB->get_record('user', array('id' => $enrolment['userid']));
      if (!$user) {
        throw new invalid_parameter_exception('User id not exist: '.$enrolment['userid']);
      }
      if (!$enrol->allow_unenrol($instance)) {
        // TODO: update for external
        throw new moodle_exception('wscannotunenrol', 'enrol_manual', '', $enrolment);
      }
      $enrol->unenrol_user($instance, $enrolment['userid']);
    }
    $transaction->allow_commit();
  }

  /* List instances in a course */

  public static function external_list_instances_parameters() {
    return new external_function_parameters(
      array(
        'courseid' => new external_value(PARAM_INT, "Course ID"),
        'name' => new external_value(PARAM_TEXT, 'Name of enrolment method', VALUE_OPTIONAL)
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

  public static function external_list_instances($courseid, $name = '') {

    global $DB;

    $n = array();

    $params = self::validate_parameters(self::external_list_instances_parameters(),
      array('courseid' => $courseid, 'name' => $name));

    // Find the course
    $course = get_course($params['courseid']);

    // Find the plugin
    $plugin = enrol_get_plugin('external');

    // If both course and plugin are valid, then let's add the default
    // instance of this enrolment method
    if($plugin && $course) {

      $enrolinstances = enrol_get_instances($course->id, true);

      foreach($enrolinstances as $courseenrolinstance) {

        if ($courseenrolinstance->enrol == "external" &&
          ( $params['name'] == "" || ($params['name'] != "" && $params['name'] == $courseenrolinstance->name) )) {
          $i = new stdClass();

          $i->id = $courseenrolinstance->id;
          $i->name = $courseenrolinstance->name;

          $n[] = $i;
        }

      }

    }

    return $n;

  }

  /* SITS enrolments functions */

  public static function external_add_sits_enrolments_parameters() {

    return new external_function_parameters(
      array(
        'courseid' => new external_value(PARAM_INT, "Course ID"),
        'module' => new external_value(PARAM_TEXT, 'Module code, e.g. MD101'),
        'occurrence' => new external_value(PARAM_TEXT, 'Occurrence code, e.g. A'),
        'academicyear' => new external_value(PARAM_TEXT, 'Academic year, e.g. 18/19'),
        'enrolments' => new external_multiple_structure(
          new external_single_structure(
            array(
              'userid' => new external_value(PARAM_INT, 'User ID'),
              'roleid' => new external_value(PARAM_INT, 'Role ID'),
              'timestart' => new external_value(PARAM_INT, 'Timestamp when the enrolment start', VALUE_OPTIONAL),
              'timeend' => new external_value(PARAM_INT, 'Timestamp when the enrolment end', VALUE_OPTIONAL),
              'suspend' => new external_value(PARAM_INT, 'set to 1 to suspend the enrolment', VALUE_OPTIONAL)
            )
          )
        )
      )
    );

  }

  public static function external_add_sits_enrolments_returns() {
    return null;
  }

  public static function external_add_sits_enrolments($courseid, $module, $occurrence, $academicyear, $enrolments) {

    global $DB, $CFG;

    require_once($CFG->libdir . '/enrollib.php');

    $params = self::validate_parameters(self::external_add_sits_enrolments_parameters(),
      array('courseid' => $courseid, 'module' => $module, 'occurrence' => $occurrence,
        'academicyear' => $academicyear, 'enrolments' => $enrolments));

    $transaction = $DB->start_delegated_transaction(); // Rollback all enrolment if an error occurs
    // (except if the DB doesn't support it).

    // Retrieve the manual enrolment plugin.
    $enrol = enrol_get_plugin('external');

    // TODO: fix for external
    if (empty($enrol)) {
      throw new moodle_exception('manualpluginnotinstalled', 'enrol_manual');
    }

    // Is there an instance of the plugin for this module/occurrence/academic year in this course?
    // Ensure the current user is allowed to run this function in the enrolment context.
    $context = context_course::instance($courseid, IGNORE_MISSING);
    self::validate_context($context);

    // Check that the user has the permission to external enrol.
    require_capability('enrol/external:enrol', $context);

    // Check external enrolment plugin instance is enabled/exist.
    $instance = null;
    $enrolinstances = enrol_get_instances($params['courseid'], true);
    foreach ($enrolinstances as $courseenrolinstance) {
      if ($courseenrolinstance->customchar1 == $params['module'] &&
          $courseenrolinstance->customchar2 == $params['occurrence'] &&
          $courseenrolinstance->customchar3 == $params['academicyear']) {
        $instance = $courseenrolinstance;
        break;
      }
    }

    // No existing instance, can we create one instead?
    if (empty($instance)) {

      // Find the course
      $course = get_course($params['courseid']);

      $instanceid = $enrol->add_instance($course, array(
        'customchar1' => $params['module'],
        'customchar2' => $params['occurrence'],
        'customchar3' => $params['academicyear']));

      $enrolinstances = enrol_get_instances($courseid, true);
      foreach ($enrolinstances as $courseenrolinstance) {
        if ($courseenrolinstance->id == $instanceid) {
          $instance = $courseenrolinstance;
          break;
        }
      }

    }

    // Process the enrolments
    foreach ($params['enrolments'] as $enrolment) {

      // Throw an exception if user is not able to assign the role.
      $roles = get_assignable_roles($context);
      if (!array_key_exists($enrolment['roleid'], $roles)) {
        $errorparams = new stdClass();
        $errorparams->roleid = $enrolment['roleid'];
        $errorparams->courseid = $params['courseid'];
        $errorparams->userid = $enrolment['userid'];
        throw new moodle_exception('wsusercannotassign', 'enrol_manual', '', $errorparams);
      }

      // Check that the plugin accept enrolment (it should always the case, it's hard coded in the plugin).
      if (!$enrol->allow_enrol($instance)) {
        $errorparams = new stdClass();
        $errorparams->roleid = $enrolment['roleid'];
        $errorparams->courseid = $params['courseid'];
        $errorparams->userid = $enrolment['userid'];
        // TODO: fix for external
        throw new moodle_exception('wscannotenrol', 'enrol_manual', '', $errorparams);
      }

      // Finally proceed the enrolment.
      $enrolment['timestart'] = isset($enrolment['timestart']) ? $enrolment['timestart'] : 0;
      $enrolment['timeend'] = isset($enrolment['timeend']) ? $enrolment['timeend'] : 0;
      $enrolment['status'] = (isset($enrolment['suspend']) && !empty($enrolment['suspend'])) ?
        ENROL_USER_SUSPENDED : ENROL_USER_ACTIVE;

      $enrol->enrol_user($instance, $enrolment['userid'], $enrolment['roleid'],
        $enrolment['timestart'], $enrolment['timeend'], $enrolment['status']);

    }

    $transaction->allow_commit();

  }


  public static function external_remove_sits_enrolments_parameters() {

    return new external_function_parameters(
      array(
        'courseid' => new external_value(PARAM_INT, "Course ID"),
        'module' => new external_value(PARAM_TEXT, 'Module code, e.g. MD101'),
        'occurrence' => new external_value(PARAM_TEXT, 'Occurrence code, e.g. A'),
        'academicyear' => new external_value(PARAM_TEXT, 'Academic year, e.g. 18/19'),
        'enrolments' => new external_multiple_structure(
          new external_single_structure(
            array(
              'userid' => new external_value(PARAM_INT, 'User ID'),
              'roleid' => new external_value(PARAM_INT, 'Role ID'),
            )
          )
        )
      )
    );

  }

  public static function external_remove_sits_enrolments_returns() {
    return null;
  }

  public static function external_remove_sits_enrolments($courseid, $module, $occurrence, $academicyear, $enrolments) {
    global $CFG, $DB;
    $params = self::validate_parameters(self::external_remove_sits_enrolments_parameters(), array('courseid' => $courseid,
      'module' => $module, 'occurrence' => $occurrence, 'academicyear' => $academicyear, 'enrolments' => $enrolments));
    require_once($CFG->libdir . '/enrollib.php');
    $transaction = $DB->start_delegated_transaction(); // Rollback all enrolment if an error occurs.
    $enrol = enrol_get_plugin('external');
    if (empty($enrol)) {
      // TODO: update for external
      throw new moodle_exception('manualpluginnotinstalled', 'enrol_manual');
    }

    // Find instance for this module, occurence and academic year
    $context = context_course::instance($params['courseid']);
    self::validate_context($context);
    require_capability('enrol/external:unenrol', $context);

    $instance = $DB->get_record('enrol', array('courseid' => $params['courseid'], 'enrol' => 'external',
      'customchar1' => $module, 'customchar2' => $occurrence, 'customchar3' => $academicyear));

    if (!$instance) {
      // TODO: update for external
      throw new moodle_exception('wsnoinstance', 'enrol_manual', $enrolment);
    }

    foreach ($params['enrolments'] as $enrolment) {

      $user = $DB->get_record('user', array('id' => $enrolment['userid']));
      if (!$user) {
        throw new invalid_parameter_exception('User id not exist: '.$enrolment['userid']);
      }
      if (!$enrol->allow_unenrol($instance)) {
        // TODO: update for external
        throw new moodle_exception('wscannotunenrol', 'enrol_manual', '', $enrolment);
      }
      $enrol->unenrol_user($instance, $enrolment['userid']);
    }
    $transaction->allow_commit();
  }


}