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
 * Plugin strings are defined here 'en'.
 *
 * @package     mod_tipnextcloud
 * @category    string
 * @copyright   2022 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Cloud and files';
$string['modulename'] = 'Cloud and files';
$string['modulenameplural'] = 'Cloud and files';
$string['generalheading'] = 'General configuration';
$string['generalheadingdesc'] = 'General plugin parameters';
$string['missingidandcmid'] = 'Course module not found';
$string['nonewmodules'] = 'There are no new Modules';
$string['pluginadministration'] = 'Administration Cloud and files';
$string['tipnextcloud:addinstance'] = 'Add TIP Nextcloud instance';
$string['tipnextcloud:view'] = 'View TIP Nextcloud';
$string['type'] = 'Type';
$string['type_help'] = 'Select the type of resource: file or folder';
$string['type_file'] = 'File';
$string['type_folder'] = 'Folder';
$string['file_url'] = 'File URL in NextCloud';
$string['file_url_help'] = '<p>To copy the URL:</p><ul>
<li>You must go to the file or folder in NextCloud</li>
<li>Click the share icon (Shared)</li>
<li>Click on internallink</li>
<li>Share with the group or people you consider</li></ul>';
$string['url_button'] = 'View {$a} in NextCloud';
$string['error_url'] = 'The URL is not valid, it must belong to the same domain';
$string['host_nextcloud_enabled'] = 'URL validation';
$string['host_nextcloud_enabled_desc'] = 'If enabled, only URLs from one domain will work';
$string['host_nextcloud'] = 'Domain NextCloud';
$string['host_nextcloud_desc'] = 'NextCloud domain, container name recommended. Ex: dd-apps-nextcloud-nginx';
$string['url_nextcloud'] = 'URL NextCloud';
$string['url_nextcloud_desc'] = 'NextCloud URL allowed in the URL. Ex: https://nextcloud.dd.3ip.eu';
$string['user_nextcloud'] = 'User NextCloud';
$string['password_nextcloud'] = 'Password NextCloud';
$string['autocreate_enabled'] = 'Automatic Module Creation in Resource';
$string['autocreate_enabled_desc'] = 'If enabled, a NextCloud TIP module will be created automatically when creating a Moodle Resource module';
$string['uploadfile'] = 'NextCloud Files';
$string['type_upload'] = 'Select the way to link files to NextCloud';
$string['type_upload_help'] = "If you select, with URL, you will have to copy the link in NextCloud, and paste it in the option that will appear below. If you select file upload from your computer, the file will be uploaded to NextCloud and automatically linked to this resource.";
$string['type_upload_url'] = 'Copying the URL of an existing file or folder';
$string['type_upload_upload'] = 'Uploading file from your computer';
$string['ncfile'] = 'Upload File to NextCloud';
$string['ncfile_help'] = 'Find the file on your computer and upload it to the NextCloud cloud';
