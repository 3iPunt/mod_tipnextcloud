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
 * Plugin administration pages are defined here.
 *
 * @package     mod_tipnextcloud
 * @copyright   2022 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_heading(
        'tipnextcloud/general',
        get_string('generalheading', 'mod_tipnextcloud'),
        get_string('generalheadingdesc', 'mod_tipnextcloud')));

    $settings->add(new admin_setting_configcheckbox('tipnextcloud/host_nextcloud_enabled',
        get_string('host_nextcloud_enabled', 'mod_tipnextcloud'),
        get_string('host_nextcloud_enabled_desc', 'mod_tipnextcloud'), 0));

    $settings->add(new admin_setting_configtext('tipnextcloud/host_nextcloud',
        get_string('host_nextcloud', 'mod_tipnextcloud'),
        get_string('host_nextcloud_desc', 'mod_tipnextcloud'), 'https://nextcloud.dd.3ip.eu'));

}