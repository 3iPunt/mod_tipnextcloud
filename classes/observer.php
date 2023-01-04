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
                    list($course, $cm) = get_course_and_cm_from_instance(
                        $event->other['instanceid'], 'resource');
                    $f = self::get_file_cm($cm);
                    $nc = new nextcloud();
                    $tfncid = self::create_teacherfolder_nc($course, $nc);
                    self::shared_folder($course, $nc);
                    self::create_teacher_folder($course, $nc, $tfncid);
                    $filename = self::upload_file_nc($nc, $f, $cm, $course);
                    self::shared($nc, $filename);
                    $ncid = self::get_ncid($nc, $course->id . '/' . $filename);
                    $url = self::get_url_file_nc($nc, $ncid);
                    self::create_tipnc($course, $cm, $url, $ncid);
                    self::disable_resource($cm);
                }
            } catch (moodle_exception $e) {
                debugging($e->getMessage());
            }
        }

        return true;
    }

    /**
     * Create Teacher Folder.
     *
     * @param stdClass $course
     * @param nextcloud $nc
     * @return int
     * @throws moodle_exception
     */
    protected static function create_teacherfolder_nc(stdClass $course, nextcloud $nc): int {
        $res = $nc->creating_folder(nextcloud::PATH);
        if (!$res) {
            throw new moodle_exception(
                'NEXTCLOUD CREATING FOLDER "CARPETA DEL CURS" [' .
                $res->error->code . ']: ' . $res->error->message);
        }
        $res = $nc->creating_folder(nextcloud::PATH . '/' . $course->id);
        if (!$res) {
            throw new moodle_exception(
                'NEXTCLOUD CREATING COURSE FOLDER "' . $course->id . '" [' .
                $res->error->code . ']: ' . $res->error->message);
        }
        return self::get_ncid($nc, $course->id);
    }

    /**
     * Create Teacher Folder.
     *
     * @param stdClass $course
     * @param nextcloud $nc
     * @param int $ncid
     * @throws coding_exception
     * @throws dml_exception
     */
    protected static function create_teacher_folder(stdClass $course, nextcloud $nc, int $ncid) {
        global $DB;

        $idnumber = 'TEACHER_FOLDER_' . $course->id . '_' . $ncid;

        $teacherfolder = $DB->get_record('course_modules',
            ['course' => $course->id, 'idnumber' => $idnumber]);

        if (!$teacherfolder) {
            $url = self::get_url_file_nc($nc, $ncid);

            self::create_tipnextcloud(
                $course, 'Carpeta del Curs', '', $idnumber, $url,
                0, false, $ncid, false);
        }
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
     * @param stdClass $course
     * @param nextcloud $nc
     * @throws moodle_exception
     */
    protected static function shared_folder(stdClass $course, nextcloud $nc) {
        global $USER;
        $teacher = $USER->username;
        $res = $nc->set_permission('/' . nextcloud::PATH . '/'
            . $course->id, $teacher, nextcloud::PERMISSION_ALL);
        if (!$res) {
            throw new moodle_exception(
                'NEXTCLOUD FOLDER SHARED [' .
                $res->error->code . ']: ' . $res->error->message);
        }
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
                'NEXTCLOUD FILE SHARED [' .
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
     * @param stdClass $course
     * @return string
     * @throws moodle_exception
     */
    protected static function upload_file_nc(nextcloud $nc, stored_file $f, cm_info $cm, stdClass $course): string {
        $filename = $cm->id . '_' . time()  . '_' . str_replace(' ', '', $f->get_filename());
        $res = $nc->upload_file($filename, $f, $course);
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
    protected static function create_tipnc(stdClass $course, cm_info $cm, string $url, int $ncid) {
        global $DB;
        $instance = $DB->get_record(
            'resource', ['id' => $cm->instance], '*' , MUST_EXIST);

        self::create_tipnextcloud(
            $course, $cm->name, $instance->intro, $ncid, $url,
            $cm->sectionnum, $cm->visible, $ncid, $cm->showdescription);
    }

    /**
     * Create TIP NextCloud.
     *
     * @param stdClass $course
     * @param string $name
     * @param string $intro
     * @param string $idnumber
     * @param string $url
     * @param int $sectionnum
     * @param bool $visible
     * @param int|null $ncid
     * @param bool $showdescription
     * @throws coding_exception
     */
    protected static function create_tipnextcloud(
        stdClass $course, string $name, string $intro, string $idnumber, string $url, int $sectionnum,
        bool $visible, int $ncid = null, bool $showdescription = false) {
        $generator = phpunit_util::get_data_generator();
        /** @var mod_tipnextcloud_generator $modgenerator */
        $modgenerator = $generator->get_plugin_generator('mod_tipnextcloud');

        $record = [
            'course' => $course,
            'name' => $name,
            'idnumber' => $idnumber,
            'intro' => !empty($intro) ? $intro : ' ',
            'introformat' => FORMAT_HTML,
            'files' => file_get_unused_draft_itemid(),
            'url' => $url,
            'ncid' => $ncid,
        ];

        $options = [
            'section' => $sectionnum,
            'visible' => $visible,
            'showdescription' => $showdescription
        ];
        $modgenerator->create_instance($record, $options);
    }

}
