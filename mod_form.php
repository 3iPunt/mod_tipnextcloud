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
 * The main mod_tipnextcloud configuration form.
 *
 * @package     mod_tipnextcloud
 * @copyright   2022 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package     mod_tipnextcloud
 * @copyright   2022 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_tipnextcloud_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     *
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function definition() {
        global $CFG, $USER;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('name'), array('size' => '64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string(
            'maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        $typeoptions = [
            0 => get_string('type_file', 'mod_tipnextcloud'),
            1 => get_string('type_folder', 'mod_tipnextcloud'),
        ];
        $mform->addElement('select', 'type', get_string('type', 'mod_tipnextcloud'), $typeoptions);
        $mform->addHelpButton('type', 'type', 'mod_tipnextcloud');

        $mform->addElement('static', 'helpurl', get_string('file_url_help2', 'mod_tipnextcloud'));

        $mform->addElement('url', 'url', get_string('file_url', 'mod_tipnextcloud'),
            array('size' => '60'), array('usefilepicker' => false));
        $mform->addHelpButton('url', 'file_url', 'mod_tipnextcloud');
        $mform->setType('url', PARAM_RAW_TRIMMED);
        $mform->addRule('url', null, 'required', null, 'client');

        if (get_config('tipnextcloud', 'host_nextcloud_enabled')) {
            $validateurl = function($val) {
                $host = get_config('tipnextcloud', 'host_nextcloud');
                return strpos($val, $host) !== false;
            };
            $mform->addRule('url',
                get_string('error_url', 'mod_tipnextcloud'),
                'callback', $validateurl, 'server');
        }

        $mform->addElement('hidden', 'userid', $USER->id);
        $mform->setType('userid', PARAM_INT);

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }
}
