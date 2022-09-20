<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Prints an instance of mod_tipnextcloud.
 *
 * @package     mod_tipnextcloud
 * @copyright   2022 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_tipnextcloud\event\course_module_viewed;

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

global $DB, $PAGE, $OUTPUT;

// Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);

// ... module instance id.
$t = optional_param('t', 0, PARAM_INT);
$u = optional_param('u', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id(
        'tipnextcloud', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record(
        'course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record(
        'tipnextcloud', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($t) {
    $moduleinstance = $DB->get_record(
        'tipnextcloud', array('id' => $t), '*', MUST_EXIST);
    $course = $DB->get_record(
        'course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance(
        'tipnextcloud', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error(get_string('missingidandcmid', 'mod_tipnextcloud'));
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$event = course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('tipnextcloud', $moduleinstance);
$event->trigger();

$PAGE->set_url('/mod/tipnextcloud/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$PAGE->requires->css('/mod/tipnextcloud/styles.css');


echo $OUTPUT->header();

try {
    $output = $PAGE->get_renderer('mod_tipnextcloud');
    $page = new main_view($cm);
    echo $output->render($page);
} catch (\Exception $e) {
    debugging($e->getMessage());
}


echo $OUTPUT->footer();

