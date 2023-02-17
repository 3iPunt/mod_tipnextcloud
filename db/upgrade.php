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
 * tip nextcloudu module upgrade code
 *
 * This file keeps track of upgrades to
 * the resource module
 *
 * Sometimes, changes between versions involve
 * alterations to database structures and other
 * major things that may break installations.
 *
 * The upgrade function in this file will attempt
 * to perform all the necessary actions to upgrade
 * your older installation to the current version.
 *
 * If there's something it cannot do itself, it
 * will tell you what you need to do.
 *
 * The commands in here will all be database-neutral,
 * using the methods of database_manager class
 *
 * Please do not forget to use upgrade_set_timeout()
 * before any action that may take longer time to finish.
 *
 * @package     mod_tipnextcloud
 * @copyright   2023 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * UPGRADE.
 *
 * @param $oldversion
 * @return bool
 * @throws ddl_exception
 */
function xmldb_tipnextcloud_upgrade($oldversion): bool {
    global $DB;

    if ($oldversion < 2023010207) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('tipnextcloud');
        $field = new xmldb_field(
            'ncid',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            null,
            null,
            null,
            'type');

        // Conditionally launch add field sensitivedatareasons.
        if (!$dbman->field_exists($table, $field)) {
            try {
                $dbman->add_field($table, $field);
            } catch (moodle_exception $e) {
                debugging($e->getMessage());
            }
        }

    }

    return true;
}
