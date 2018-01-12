<?php

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");


class enrol_external_external extends external_api {

  /**
   * Returns description of method parameters.
   *
   * @return external_function_parameters
   * @since Moodle 2.2
   */
  public static function enrol_users_parameters() {
    return new external_function_parameters(
      array(
        'enrolments' => new external_multiple_structure(
          new external_single_structure(
            array(
              'roleid' => new external_value(PARAM_INT, 'Role to assign to the user'),
              'userid' => new external_value(PARAM_INT, 'The user that is going to be enrolled'),
              'courseid' => new external_value(PARAM_INT, 'The course to enrol the user role in'),
              'timestart' => new external_value(PARAM_INT, 'Timestamp when the enrolment start', VALUE_OPTIONAL),
              'timeend' => new external_value(PARAM_INT, 'Timestamp when the enrolment end', VALUE_OPTIONAL),
              'suspend' => new external_value(PARAM_INT, 'set to 1 to suspend the enrolment', VALUE_OPTIONAL)
            )
          )
        )
      )
    );
  }

  /**
   * Enrolment of users.
   *
   * Function throw an exception at the first error encountered.
   * @param array $enrolments  An array of user enrolment
   * @since Moodle 2.2
   */
  public static function enrol_users($enrolments) {
    global $DB, $CFG;

    require_once($CFG->libdir . '/enrollib.php');

    $params = self::validate_parameters(self::enrol_users_parameters(),
      array('enrolments' => $enrolments));

    $transaction = $DB->start_delegated_transaction(); // Rollback all enrolment if an error occurs
    // (except if the DB doesn't support it).

    // Retrieve the external enrolment plugin.
    $enrol = enrol_get_plugin('external');
    if (empty($enrol)) {
      throw new moodle_exception('externalpluginnotinstalled', 'enrol_external');
    }

    foreach ($params['enrolments'] as $enrolment) {
      // Ensure the current user is allowed to run this function in the enrolment context.
      $context = context_course::instance($enrolment['courseid'], IGNORE_MISSING);
      self::validate_context($context);

      // Check that the user has the permission to external enrol.
      require_capability('enrol/external:enrol', $context);

      // Throw an exception if user is not able to assign the role.
      $roles = get_assignable_roles($context);
      if (!array_key_exists($enrolment['roleid'], $roles)) {
        $errorparams = new stdClass();
        $errorparams->roleid = $enrolment['roleid'];
        $errorparams->courseid = $enrolment['courseid'];
        $errorparams->userid = $enrolment['userid'];
        throw new moodle_exception('wsusercannotassign', 'enrol_external', '', $errorparams);
      }

      // Check external enrolment plugin instance is enabled/exist.
      $instance = null;
      $enrolinstances = enrol_get_instances($enrolment['courseid'], true);
      foreach ($enrolinstances as $courseenrolinstance) {
        if ($courseenrolinstance->enrol == "external") {
          $instance = $courseenrolinstance;
          break;
        }
      }
      if (empty($instance)) {
        $errorparams = new stdClass();
        $errorparams->courseid = $enrolment['courseid'];
        throw new moodle_exception('wsnoinstance', 'enrol_external', $errorparams);
      }

      // Check that the plugin accept enrolment (it should always the case, it's hard coded in the plugin).
      if (!$enrol->allow_enrol($instance)) {
        $errorparams = new stdClass();
        $errorparams->roleid = $enrolment['roleid'];
        $errorparams->courseid = $enrolment['courseid'];
        $errorparams->userid = $enrolment['userid'];
        throw new moodle_exception('wscannotenrol', 'enrol_external', '', $errorparams);
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

  /**
   * Returns description of method result value.
   *
   * @return null
   * @since Moodle 2.2
   */
  public static function enrol_users_returns() {
    return null;
  }

  /**
   * Returns description of method parameters.
   *
   * @return external_function_parameters
   */
  public static function unenrol_users_parameters() {
    return new external_function_parameters(array(
      'enrolments' => new external_multiple_structure(
        new external_single_structure(
          array(
            'userid' => new external_value(PARAM_INT, 'The user that is going to be unenrolled'),
            'courseid' => new external_value(PARAM_INT, 'The course to unenrol the user from'),
            'roleid' => new external_value(PARAM_INT, 'The user role', VALUE_OPTIONAL),
          )
        )
      )
    ));
  }

  /**
   * Unenrolment of users.
   *
   * @param array $enrolments an array of course user and role ids
   * @throws coding_exception
   * @throws dml_transaction_exception
   * @throws invalid_parameter_exception
   * @throws moodle_exception
   * @throws required_capability_exception
   * @throws restricted_context_exception
   */
  public static function unenrol_users($enrolments) {
    global $CFG, $DB;
    $params = self::validate_parameters(self::unenrol_users_parameters(), array('enrolments' => $enrolments));
    require_once($CFG->libdir . '/enrollib.php');
    $transaction = $DB->start_delegated_transaction(); // Rollback all enrolment if an error occurs.
    $enrol = enrol_get_plugin('external');
    if (empty($enrol)) {
      throw new moodle_exception('externalpluginnotinstalled', 'enrol_external');
    }

    foreach ($params['enrolments'] as $enrolment) {
      $context = context_course::instance($enrolment['courseid']);
      self::validate_context($context);
      require_capability('enrol/external:unenrol', $context);
      $instance = $DB->get_record('enrol', array('courseid' => $enrolment['courseid'], 'enrol' => 'external'));
      if (!$instance) {
        throw new moodle_exception('wsnoinstance', 'enrol_external', $enrolment);
      }
      $user = $DB->get_record('user', array('id' => $enrolment['userid']));
      if (!$user) {
        throw new invalid_parameter_exception('User id not exist: '.$enrolment['userid']);
      }
      if (!$enrol->allow_unenrol($instance)) {
        throw new moodle_exception('wscannotunenrol', 'enrol_external', '', $enrolment);
      }
      $enrol->unenrol_user($instance, $enrolment['userid']);
    }
    $transaction->allow_commit();
  }

  /**
   * Returns description of method result value.
   *
   * @return null
   */
  public static function unenrol_users_returns() {
    return null;
  }



}