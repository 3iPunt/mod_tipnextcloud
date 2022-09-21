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
 * Class main_view
 *
 * @package     mod_tipnextcloud
 * @copyright   2022 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tipnextcloud\output;

use coding_exception;
use dml_exception;
use mod_tipnextcloud\tipnextcloud;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * Class main_view
 *
 * @package     mod_tipnextcloud
 * @copyright   2022 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main_view implements renderable, templatable {

    /** @var stdClass Course Module */
    protected $cm;

    /**
     * teacher_view constructor.
     *
     * @param stdClass $cm
     */
    public function __construct(stdClass $cm) {
        $this->cm = $cm;
    }

    /**
     * Export for template
     *
     * @param renderer_base $output
     * @return false|stdClass|string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function export_for_template(renderer_base $output) {
        $tipnextcloud = new tipnextcloud($this->cm);

        $data = new stdClass();
        $data->title = $tipnextcloud->get_title();
        $data->desc = $tipnextcloud->get_description();
        $data->url = $tipnextcloud->get_url();
        $data->typename = $tipnextcloud->get_typename();
        return $data;
    }

}
