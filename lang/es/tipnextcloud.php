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
 * Plugin strings are defined here 'es'.
 *
 * @package     mod_tipnextcloud
 * @category    string
 * @copyright   2022 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Nube y archivos';
$string['modulename'] = 'Nube y archivos';
$string['modulenameplural'] = 'Nube y archivos';
$string['generalheading'] = 'Configuración General';
$string['generalheadingdesc'] = 'Parámetros generales del plugin';
$string['missingidandcmid'] = 'Módulo del curso no encontrado';
$string['nonewmodules'] = 'No existen nuevos Módulos';
$string['pluginadministration'] = 'Administración Nube y archivos';
$string['tipnextcloud:addinstance'] = 'Añadir instancia TIP Nextcloud';
$string['tipnextcloud:view'] = 'Ver Nube y archivos';
$string['type'] = 'Tipo';
$string['type_help'] = 'Seleccionar el tipo de recurso: archivo o carpeta';
$string['type_file'] = 'Archivo';
$string['type_folder'] = 'Carpeta';
$string['file_url'] = 'URL del archivo en NextCloud';
$string['file_url_help'] = 'Copie y pegue el enlace de la URL del archivo o carpeta de NextCloud.';
$string['file_url_help2'] = '<p>Para copiar la URL:</p><ul>
<li>Debes ir al archivo o carpeta en NextCloud</li>
<li>Hacer clic en el icono de compartir (Shared)</li>
<li>Hacer clic en internallink</li>
<li>Compartir con el grupo o personas que consideres</li></ul>';
$string['url_button'] = 'Ver {$a} en NextCloud';
$string['error_url'] = 'La URL no es válida, debe pertenecer al mismo dominio';
$string['host_nextcloud_enabled'] = 'Validación URL';
$string['host_nextcloud_enabled_desc'] = 'Si está activado, solo funcionarán URL de un dominio';
$string['host_nextcloud'] = 'Dominio NextCloud';
$string['host_nextcloud_desc'] = 'Dominio del NextCloud permitido en la URL. Ej: https://nextcloud.dd.3ip.eu';