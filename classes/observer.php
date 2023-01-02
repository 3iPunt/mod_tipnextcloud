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
     * @param course_module_created $event
     * @return bool
     * @throws dml_exception
     */
    public static function course_module_created(course_module_created $event): bool {
        if (get_config('tipnextcloud', 'autocreate_enabled') == true) {
            try {
                if ($event->other['modulename'] === 'resource') {
                    list($course, $cm) = get_course_and_cm_from_instance($event->other['instanceid'], 'resource');
                    $f = self::get_file_cm($cm);
                    $nc = new nextcloud();
                    $filename = self::upload_file_nc($nc, $f, $cm);
                    self::shared($nc, $filename);
                    $ncid = self::get_ncid($nc, $filename);
                    $url = self::get_url_file_nc($nc, $ncid);
                    self::create_tipnextcloud($course, $cm, $url, $ncid);
                    self::disable_resource($cm);
                }
            } catch (moodle_exception $e) {
                debugging($e->getMessage());
            }
        }

        return true;
    }

    /**
     * Disable Resource.
     *
     * @param cm_info $cm
     * @throws coding_exception
     * @throws moodle_exception
     */
    protected static function disable_resource(cm_info $cm) {
        core_course_external::edit_module('hide', $cm->id);
    }

    /**
     * Shared.
     *
     * @param nextcloud $nc
     * @param string $filename
     * @throws moodle_exception
     */
    protected static function shared(nextcloud $nc, string $filename) {
        global $USER;
        $teacher = $USER->username;
        $res = $nc->set_permission('/' . nextcloud::PATH . '/'
            . $filename, $teacher, nextcloud::PERMISSION_ALL);
        if (!$res) {
            throw new moodle_exception(
                'NEXTCLOUD SHARED [' .
                $res->error->code . ']: ' . $res->error->message);
        }
    }

    /**
     * Get URL file NC.
     *
     * @param nextcloud $nc
     * @param int $ncid
     * @return string
     */
    protected static function get_url_file_nc(nextcloud $nc, int $ncid): string {
        return $nc->host . '/f/' . $ncid;
    }

    /**
     * Get NC ID.
     *
     * @param nextcloud $nc
     * @param string $filename
     * @return int
     * @throws moodle_exception
     */
    protected static function get_ncid(nextcloud $nc, string $filename): int {
        $res = $nc->listing(nextcloud::PATH . '/' . $filename);
        if ($res->success) {
            if ((int)$res->data > 0) {
                return (int)$res->data;
            } else {
                throw new moodle_exception(
                    'NEXTCLOUD FILE ERROR = ' . $res->data);
            }
        } else {
            throw new moodle_exception(
                'NEXTCLOUD FILE ID NOT FOUND [' .
                $res->error->code . ']: ' . $res->error->message);
        }
    }

    /**
     * Get file in CM.
     *
     * @param cm_info $cm
     * @return stored_file
     * @throws coding_exception|moodle_exception
     */
    protected static function get_file_cm(cm_info $cm): stored_file {
        $fs = get_file_storage();
        $files = $fs->get_area_files($cm->context->id, 'mod_resource', 'content', 0);
        foreach ($files as $f) {
            if ($f->get_filename() !== '.') {
                return $f;
            }
        }
        throw new moodle_exception('NOT FILE FOUND IN RESOURCE');
    }

    /**
     * Upload File to NextCloud.
     * @param nextcloud $nc
     * @param stored_file $f
     * @param cm_info $cm
     * @throws moodle_exception
     */
    protected static function upload_file_nc(nextcloud $nc, stored_file $f, cm_info $cm): string {
        $filename = $cm->id . '_' . time()  . '_' . str_replace(' ', '', $f->get_filename());
        $res = $nc->upload_file($filename, $f);
        if ($res->success) {
            return $filename;
        } else {
            throw new moodle_exception($res->error->message);
        }
    }

    /**
     * Create TIP NextCloud.
     *
     * @param stdClass $course
     * @param cm_info $cm
     * @param string $url
     * @param int $ncid
     * @throws coding_exception
     * @throws dml_exception
     */
    protected static function create_tipnextcloud(stdClass $course, cm_info $cm, string $url, int $ncid) {
        global $DB;
        $instance = $DB->get_record(
            'resource', ['id' => $cm->instance], '*' , MUST_EXIST);

        $generator = phpunit_util::get_data_generator();
        /** @var mod_tipnextcloud_generator $modgenerator */
        $modgenerator = $generator->get_plugin_generator('mod_tipnextcloud');

        $record = [
            'course' => $course,
            'name' => $cm->name,
            'intro' => !empty($instance->intro) ? $instance->intro : ' ',
            'introformat' => FORMAT_HTML,
            'files' => file_get_unused_draft_itemid(),
            'url' => $url,
            'ncid' => $ncid,
        ];

        $options = [
            'section' => $cm->sectionnum,
            'visible' => $cm->visible,
            'showdescription' => $cm->showdescription
        ];
        $res = $modgenerator->create_instance($record, $options);
    }

}
