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
 * Class tipnextcloud
 *
 * @package     mod_tipnextcloud
 * @copyright   2022 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tipnextcloud;

use coding_exception;
use dml_exception;
use stdClass;

/**
 * Class tipnextcloud
 *
 * @package     mod_tipnextcloud
 * @copyright   2022 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tipnextcloud  {

    /** @var stdClass Course Module */
    protected $cm;

    /** @var stdClass Instance */
    protected $instance;

    /**
     * teacher_view constructor.
     *
     * @param stdClass $cm
     */
    public function __construct(stdClass $cm) {
        $this->cm = $cm;
        $this->set_instance();
    }

    /**
     * Get Title.
     *
     * @return string
     */
    public function get_title(): string {
        return $this->cm->name;
    }

    /**
     * Set Instance.
     *
     * @throws dml_exception
     */
    protected function set_instance() {
        global $DB;
        $this->instance = $DB->get_record($this->cm->modname, ['id' => $this->cm->instance]);
    }

    /**
     * Get Description.
     *
     * @return string
     */
    public function get_description(): string {
        if (isset($this->instance)) {
            return $this->instance->intro;
        } else {
            return '';
        }
    }

    /**
     * Get URL.
     *
     * @return string
     */
    public function get_url(): string {
        if (isset($this->instance)) {
            return $this->instance->url;
        } else {
            return '';
        }
    }

    /**
     * Get Type Name.
     *
     * @return string
     * @throws coding_exception
     */
    public function get_typename(): string {
        if (isset($this->instance)) {
            $type = $this->instance->type;
            switch ($type) {
                case 1:
                    return get_string('type_folder', 'mod_tipnextcloud');
                default:
                    return get_string('type_file', 'mod_tipnextcloud');
            }
        } else {
            return '';
        }
    }

}
