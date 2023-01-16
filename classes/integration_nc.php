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
        $filename = str_replace(',', '', $filename);
        $filename = str_replace('?', '', $filename);
        $filename = str_replace('¿', '', $filename);
        $filename = str_replace('&', '', $filename);
        $filename = $this->remove_accents($filename);
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

    /**
     * Remove Accents.
     *
     * @param $string
     * @return string
     */
    protected function remove_accents($string): string {
        if ( !preg_match('/[\x80-\xff]/', $string)) {
            return $string;
        }
        $chars = array(
            // Decompositions for Latin-1 Supplement.
            chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
            chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
            chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
            chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
            chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
            chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
            chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
            chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
            chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
            chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
            chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
            chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
            chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
            chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
            chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
            chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
            chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
            chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
            chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
            chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
            chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
            chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
            chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
            chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
            chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
            chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
            chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
            chr(195).chr(191) => 'y',
            // Decompositions for Latin Extended-A.
            chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
            chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
            chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
            chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
            chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
            chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
            chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
            chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
            chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
            chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
            chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
            chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
            chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
            chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
            chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
            chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
            chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
            chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
            chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
            chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
            chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
            chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
            chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
            chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
            chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
            chr(196).chr(178) => 'IJ', chr(196).chr(179) => 'ij',
            chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
            chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
            chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
            chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
            chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
            chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
            chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
            chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
            chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
            chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
            chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
            chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
            chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
            chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
            chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
            chr(197).chr(146) => 'OE', chr(197).chr(147) => 'oe',
            chr(197).chr(148) => 'R', chr(197).chr(149) => 'r',
            chr(197).chr(150) => 'R', chr(197).chr(151) => 'r',
            chr(197).chr(152) => 'R', chr(197).chr(153) => 'r',
            chr(197).chr(154) => 'S', chr(197).chr(155) => 's',
            chr(197).chr(156) => 'S', chr(197).chr(157) => 's',
            chr(197).chr(158) => 'S', chr(197).chr(159) => 's',
            chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
            chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
            chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
            chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
            chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
            chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
            chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
            chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
            chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
            chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
            chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
            chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
            chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
            chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
            chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
            chr(197).chr(190) => 'z', chr(197).chr(191) => 's'
        );

        $string = strtr($string, $chars);

        return $string;
    }

}

