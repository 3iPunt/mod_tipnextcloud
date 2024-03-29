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
 * Library of interface functions and constants.
 *
 * @package     mod_tipnextcloud
 * @copyright   2022 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_tipnextcloud\api\nextcloud;

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return int True if the feature is supported, null otherwise.
 */
function tipnextcloud_supports(string $feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:             return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:                    return false;
        case FEATURE_GROUPINGS:                 return false;
        case FEATURE_MOD_INTRO:                 return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:   return true;
        case FEATURE_COMPLETION_HAS_RULES:      return false;
        case FEATURE_GRADE_HAS_GRADE:           return false;
        case FEATURE_GRADE_OUTCOMES:            return false;
        case FEATURE_ADVANCED_GRADING:          return false;
        case FEATURE_BACKUP_MOODLE2:            return true;
        case FEATURE_SHOW_DESCRIPTION:          return true;
        case FEATURE_CONTROLS_GRADE_VISIBILITY: return false;
        case FEATURE_USES_QUESTIONS:            return false;
        case FEATURE_PLAGIARISM:                return false;
        case FEATURE_COMMENT:                   return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_tipnextcloud into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_tipnextcloud_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 * @throws dml_exception|moodle_exception
 */
function tipnextcloud_add_instance(object $moduleinstance, $mform = null): int {
    global $DB, $COURSE;
    $moduleinstance->timecreated = time();

    try {
        if (!is_null($mform)) {
            if (!empty($mform->get_file_content('ncfile'))) {
                $filename = empty($mform->get_new_filename('ncfile')) ?
                    'file_' . $moduleinstance->id : $mform->get_new_filename('ncfile');
                $int = new \mod_tipnextcloud\integration_nc($COURSE);
                $int->validate_folder_course();
                $res = $int->upload_file($filename, $mform->get_file_content('ncfile'));
                $moduleinstance->url = $res['url'];
                $moduleinstance->ncid = $res['ncid'];
            }
        }
    } catch (\Exception $e) {
        debugging($e->getMessage());
    }

    $moduleinstance->id = $DB->insert_record('tipnextcloud', $moduleinstance);

    return $moduleinstance->id;

}

/**
 * Updates an instance of the mod_tipnextcloud in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_tipnextcloud_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 * @throws dml_exception
 */
function tipnextcloud_update_instance(object $moduleinstance, $mform = null): bool {
    global $DB, $COURSE;
    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    try {
        if (!is_null($mform)) {
            if (!empty($mform->get_file_content('ncfile'))) {
                $filename = empty($mform->get_new_filename('ncfile')) ?
                    'file_' . $moduleinstance->id : $mform->get_new_filename('ncfile');
                $int = new \mod_tipnextcloud\integration_nc($COURSE);
                $int->validate_folder_course();
                $res = $int->upload_file($filename, $mform->get_file_content('ncfile'));
                $moduleinstance->url = $res['url'];
                $moduleinstance->ncid = $res['ncid'];
            }
        }
    } catch (\Exception $e) {
        debugging($e->getMessage());
    }

    $DB->update_record('tipnextcloud', $moduleinstance);
    return true;
}

/**
 * Removes an instance of the mod_tipnextcloud from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 * @throws dml_exception
 */
function tipnextcloud_delete_instance(int $id): bool {
    global $DB;
    $exists = $DB->get_record('tipnextcloud', array( 'id' => $id ));
    if (!$exists) {
        return false;
    }
    $DB->delete_records('tipnextcloud', array( 'id' => $id ));
    return true;
}

/**
 * Running addtional permission check on plugin, for example, plugins
 * may have switch to turn on/off comments option, this callback will
 * affect UI display, not like pluginname_comment_validate only throw
 * exceptions.
 * Capability check has been done in comment->check_permissions(), we
 * don't need to do it again here.
 *
 * @package  mod_tipnextcloud
 * @category comment
 *
 * @param stdClass $comment_param {
 *              context     => context the context object
 *              courseid    => int course id
 *              cm          => stdClass course module object
 *              commentarea => string comment area
 *              itemid      => int itemid
 * }
 * @return array
 */
function tipnextcloud_comment_permissions(stdClass $comment_param): array {
    return array( 'post' => true, 'view' => true );
}

/**
 * Validate comment parameter before perform other comments actions
 *
 * @package  mod_tipnextcloud
 * @category comment
 *
 * @param stdClass $comment_param {
 *              context     => context the context object
 *              courseid    => int course id
 *              cm          => stdClass course module object
 *              commentarea => string comment area
 *              itemid      => int itemid
 * }
 * @return boolean
 */
function tipnextcloud_comment_validate(stdClass $comment_param): bool {
    return true;
}


/**
 * Serves the resource files.
 *
 * @package  mod_resource
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function tipnextcloud_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG;
    require_once("$CFG->libdir/resourcelib.php");

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_course_login($course, true, $cm);

    if (!has_capability('mod/tipnextcloud:view', $context)) {
        return false;
    }

    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_tipnextcloud/intro/0/$relativepath";

    $fs = get_file_storage();
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    // finally send the file
    send_stored_file($file, 0, 0, $forcedownload, $options);
}

