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
 * Class integration_nc
 *
 * @package     mod_tipnextcloud
 * @copyright   2022 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tipnextcloud;

defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once($CFG->dirroot . '/lib/phpunit/classes/util.php');
require_once($CFG->dirroot . '/course/externallib.php');
require_once($CFG->dirroot . '/course/lib.php');

use coding_exception;
use dml_exception;
use mod_tipnextcloud\api\nextcloud;
use mod_tipnextcloud_generator;
use moodle_exception;
use phpunit_util;
use stdClass;

/**
 * Class integration_nc
 *
 * @package     mod_tipnextcloud
 * @copyright   2022 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class integration_nc {

    /** @var nextcloud NextCloud API */
    protected $nc;

    /** @var stdClass Course */
    protected $course;

    /** @var string Course Folder */
    protected $coursefolder;

    /** @var string Teacher Username */
    protected $teacher;

    /**
     * constructor.
     *
     * @param stdClass $course
     * @throws moodle_exception
     */
    public function __construct(stdClass $course) {
        global $USER;
        $this->nc = new nextcloud();
        $this->course = $course;
        $this->coursefolder = str_replace(' ', '-', $course->shortname);
        $this->teacher = $USER->username;
    }

    /**
     * Validate folder Course.
     *
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function validate_folder_course() {
        $tfncid = $this->create_teacherfolder_nc();
        $this->shared_folder();
        $this->create_teacher_folder($tfncid);
    }

    /**
     * Upload File.
     *
     * @param string $filename
     * @param string $content
     * @return string[]
     * @throws moodle_exception
     */
    public function upload_file(string $filename, string $content): array {
        $filename = str_replace(' ', '-', $filename);
        $path = $this->coursefolder . '/' . $filename;
        $res = $this->nc->upload_file_content($path, $content);
        if ($res->success) {
            $this->shared($path);
            $ncid = $this->get_ncid($path);
            return [
                'url' => $this->get_url_file_nc($ncid),
                'ncid' => $ncid
            ];
        } else {
            throw new moodle_exception($res->error->message);
        }
    }

    /**
     * Shared.
     *
     * @param string $filename
     * @throws moodle_exception
     */
    protected function shared(string $filename) {
        $res = $this->nc->set_permission('/' . nextcloud::PATH . '/' . $filename,
            $this->teacher, nextcloud::PERMISSION_ALL);
        if (!$res) {
            throw new moodle_exception(
                'NEXTCLOUD FILE SHARED [' .
                $res->error->code . ']: ' . $res->error->message);
        }
    }

    /**
     * Create Teacher Folder.
     *
     * @return int
     * @throws moodle_exception
     */
    protected function create_teacherfolder_nc(): int {
        $res = $this->nc->creating_folder(nextcloud::PATH);
        if (!$res) {
            throw new moodle_exception(
                'NEXTCLOUD CREATING FOLDER "CARPETA DEL CURS" [' .
                $res->error->code . ']: ' . $res->error->message);
        }
        $res = $this->nc->creating_folder(nextcloud::PATH . '/' . $this->coursefolder);
        if (!$res) {
            throw new moodle_exception(
                'NEXTCLOUD CREATING COURSE FOLDER "' . $this->coursefolder . '" [' .
                $res->error->code . ']: ' . $res->error->message);
        }
        return $this->get_ncid($this->coursefolder);
    }

    /**
     * Get NC ID.
     *
     * @param string $filename
     * @return int
     * @throws moodle_exception
     */
    protected function get_ncid(string $filename): int {
        $res = $this->nc->listing(nextcloud::PATH . '/' . $filename);
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
     * Shared.
     *
     * @throws moodle_exception
     */
    protected function shared_folder() {
        $res = $this->nc->set_permission('/' . nextcloud::PATH . '/' . $this->coursefolder,
            $this->teacher, nextcloud::PERMISSION_ALL);
        if (!$res) {
            throw new moodle_exception(
                'NEXTCLOUD FOLDER SHARED [' .
                $res->error->code . ']: ' . $res->error->message);
        }
    }

    /**
     * Create Teacher Folder.
     *
     * @param int $ncid
     * @throws coding_exception
     * @throws dml_exception
     */
    public function create_teacher_folder(int $ncid) {
        global $DB;

        $idnumber = 'TEACHER_FOLDER_' . $this->course->id . '_' . $ncid;

        $teacherfolder = $DB->get_record('course_modules',
            ['course' => $this->course->id, 'idnumber' => $idnumber]);

        if (!$teacherfolder) {
            $url = $this->get_url_file_nc($ncid);

            $this->create_tipnextcloud('Carpeta del Curs', '', $idnumber, $url,
                0, false, $ncid, false);
        }
    }

    /**
     * Get URL file NC.
     *
     * @param int $ncid
     * @return string
     */
    protected function get_url_file_nc(int $ncid): string {
        return $this->nc->url . '/f/' . $ncid;
    }

    /**
     * Create TIP NextCloud.
     *
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
    protected function create_tipnextcloud(string $name, string $intro, string $idnumber, string $url, int $sectionnum,
        bool $visible, int $ncid = null, bool $showdescription = false) {
        $generator = phpunit_util::get_data_generator();
        /** @var mod_tipnextcloud_generator $modgenerator */
        $modgenerator = $generator->get_plugin_generator('mod_tipnextcloud');

        $record = [
            'course' => $this->course,
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

