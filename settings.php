<?php

/**
 * External enrolment method
 *
 * @package    enrol_external
 * @copyright  2017 Russell Boyatt
 * @license    None
 */


defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('enrol_external_settings', '', get_string('pluginname_desc', 'enrol_external')));


    //--- enrol instance defaults ----------------------------------------------------------------------------
    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);

        $settings->add(new admin_setting_configselect('enrol_external/roleid',
            get_string('defaultrole', 'role'), '', $student->id, $options));

        $options = array(
            ENROL_EXT_REMOVED_UNENROL        => get_string('extremovedunenrol', 'enrol'),
            ENROL_EXT_REMOVED_SUSPENDNOROLES => get_string('extremovedsuspendnoroles', 'enrol'));

        $settings->add(
          new admin_setting_configselect('enrol_external/unenrolaction',
            get_string('extremovedaction', 'enrol'),
            get_string('extremovedaction_help', 'enrol'),
            ENROL_EXT_REMOVED_UNENROL,
            $options));
    }
}
