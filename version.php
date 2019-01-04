<?php


/**
 * External enrolment method
 *
 * @package    enrol_external
 * @copyright  2017 Russell Boyatt
 * @license    None
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2019010306;        // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires  = 2016112900;        // Requires this Moodle version
$plugin->component = 'enrol_external';    // Full name of the plugin (used for diagnostics)
$plugin->cron      = 60*60;             // run cron every hour by default, it is not out-of-sync often
