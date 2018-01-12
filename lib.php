<?php

defined('MOODLE_INTERNAL') || die();

class enrol_external_plugin extends enrol_plugin {


  public function allow_enrol(stdClass $instance) {
    // Users with enrol cap may unenrol other users manually manually.
    return true;
  }

  public function allow_unenrol(stdClass $instance) {
    // Users with unenrol cap may unenrol other users manually manually.
    return true;
  }

  public function allow_manage(stdClass $instance) {
    // Users with manage cap may tweak period and status.
    return true;
  }

  public function get_instance_name($instance) {
    global $DB;

    if (empty($instance)) {
      $enrol = $this->get_name();
      return get_string('pluginname', 'enrol_'.$enrol);

    } else if (empty($instance->name)) {
      $enrol = $this->get_name();
      return get_string('pluginname', 'enrol_'.$enrol);


    } else {
      return format_string($instance->name, true, array('context'=>context_course::instance($instance->courseid)));
    }
  }


  /**
   * Given a courseid this function returns true if the user is able to enrol or configure cohorts.
   * AND there are cohorts that the user can view.
   *
   * @param int $courseid
   * @return bool
   */
  public function can_add_instance($courseid) {
    //global $CFG;
    //require_once($CFG->dirroot . '/cohort/lib.php');
    //$coursecontext = context_course::instance($courseid);
    //if (!has_capability('moodle/course:enrolconfig', $coursecontext) or !has_capability('enrol/cohort:config', $coursecontext)) {
    //  return false;
    //}
    //return cohort_get_available_cohorts($coursecontext, 0, 0, 1) ? true : false;
    return true;
  }

  /**
   * Is it possible to hide/show enrol instance via standard UI?
   *
   * @param stdClass $instance
   * @return bool
   */
  public function can_hide_show_instance($instance) {
    //$context = context_course::instance($instance->courseid);
    //return has_capability('enrol/cohort:config', $context);
    return true;
  }

  /**
   * Return an array of valid options for the status.
   *
   * @return array
   */
  protected function get_status_options() {
    $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                     ENROL_INSTANCE_DISABLED => get_string('no'));
    return $options;
  }


  public function get_user_enrolment_actions(course_enrolment_manager $manager, $ue) {
    // Get the standard user enrolment actions.
    $actions = parent::get_user_enrolment_actions();

    return $actions;
  }

  public function use_standard_editing_ui() {
    return true;
  }

  /**
   * Add elements to the edit instance form.
   *
   * @param stdClass $instance
   * @param MoodleQuickForm $mform
   * @param context $coursecontext
   * @return bool
   */
  public function edit_instance_form($instance, MoodleQuickForm $mform, $coursecontext) {
    global $DB;

    $mform->addElement('text', 'name', get_string('custominstancename', 'enrol'));
    $mform->setType('name', PARAM_TEXT);

    $options = $this->get_status_options();
    $mform->addElement('select', 'status', get_string('status', 'enrol_cohort'), $options);

//    $options = $this->get_cohort_options($instance, $coursecontext);
//    $mform->addElement('select', 'customint1', get_string('cohort', 'cohort'), $options);
//    if ($instance->id) {
//      $mform->setConstant('customint1', $instance->customint1);
//      $mform->hardFreeze('customint1', $instance->customint1);
//    } else {
//      $mform->addRule('customint1', get_string('required'), 'required', null, 'client');
//    }
//
//    $roles = $this->get_role_options($instance, $coursecontext);
//    $mform->addElement('select', 'roleid', get_string('assignrole', 'enrol_cohort'), $roles);
//    $mform->setDefault('roleid', $this->get_config('roleid'));
//    $groups = $this->get_group_options($coursecontext);
//    $mform->addElement('select', 'customint2', get_string('addgroup', 'enrol_cohort'), $groups);
  }

  /**
   * Perform custom validation of the data used to edit the instance.
   *
   * @param array $data array of ("fieldname" => value) of submitted data
   * @param array $files array of uploaded files "element_name" => tmp_file_path
   * @param object $instance The instance loaded from the DB
   * @param context $context The context of the instance we are editing
   * @return array of "element_name" => "error_description" if there are errors,
   *         or an empty array if everything is OK.
   * @return void
   */
  public function edit_instance_validation($data, $files, $instance, $context) {
    global $DB;
    $errors = array();

    $validstatus = array_keys($this->get_status_options());

    $tovalidate = array(
      'name' => PARAM_TEXT,
      'status' => $validstatus
    );
    $typeerrors = $this->validate_param_types($data, $tovalidate);
    $errors = array_merge($errors, $typeerrors);

    return $errors;
  }

}