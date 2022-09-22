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
 * Plugin strings are defined here 'ca'.
 *
 * @package     mod_tipnextcloud
 * @category    string
 * @copyright   2022 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'TIP NextCloud';
$string['modulename'] = 'TIP NextCloud';
$string['modulenameplural'] = 'Archivos NextCloud';
$string['generalheading'] = 'Configuració General';
$string['generalheadingdesc'] = 'Paràmetres generals del plugin';
$string['missingidandcmid'] = 'Mòdul del curs no trobat';
$string['nonewmodules'] = 'No hi ha nous Mòduls';
$string['pluginadministration'] = 'Administració TIP Nextcloud';
$string['tipnextcloud:addinstance'] = 'Afegir instància TIP Nextcloud';
$string['tipnextcloud:view'] = 'Veure TIP Nextcloud';
$string['type'] = 'Tipus';
$string['type_help'] = 'Seleccionar el tipus de recurs: fitxer o carpeta';
$string['type_file'] = 'Fitxer';
$string['type_folder'] = 'Carpeta';
$string['file_url'] = 'URL del fitxer a NextCloud';
$string['file_url_help'] = "Copieu i enganxeu l'enllaç de la URL del fitxer o carpeta de NextCloud";
$string['file_url_help2'] = "<p>Per copiar l'URL:</p><ul>
<li>Has d'anar al fitxer o carpeta a NextCloud</li>
<li>Fer clic a la icona de compartir (Shared)</li>
<li>Fer clic a internallink</li>
<li>Compartir amb el grup o les persones que consideris</li></ul>";
$string['url_button'] = 'Veure {$a} a NextCloud';
$string['error_url'] = 'La URL no és vàlida, ha de pertànyer al mateix dominio';
$string['host_nextcloud_enabled'] = 'Validació URL';
$string['host_nextcloud_enabled_desc'] = "Si està activat, només funcionaran URL d'un domini";
$string['host_nextcloud'] = 'Domini NextCloud';
$string['host_nextcloud_desc'] = 'Domini del NextCloud permès a la URL. Ex: https://nextcloud.dd.3ip.eu';