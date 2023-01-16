<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Class Observer mod_tipnextcloud
 *
 * @package     mod_tipnextcloud
 * @copyright   2023 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\event\course_created;
use core\event\course_module_created;
use mod_tipnextcloud\api\nextcloud;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/lib/phpunit/classes/util.php');
require_once($CFG->dirroot . '/course/externallib.php');

/**
 * Class Event observer for mod_tipnextcloud_observer.
 *
 * @package     mod_tipnextcloud
 * @copyright   2023 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class mod_tipnextcloud_observer {

    /**
     * Evento que controla la creación del curso.
     *
     * @param course_created $event
     * @return bool
     */
    public static function course_created(course_created $event): bool {
        try {
            $course = get_course($event->courseid);
            $tip = new \mod_tipnextcloud\integration_nc($course);
            $tip->validate_folder_course();
        } catch (moodle_exception $e) {
            debugging($e->getMessage());
        }
        return true;
    }

}
