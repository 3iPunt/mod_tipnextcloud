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
 * Class nextcloud
 *
 * @package     mod_tipnextcloud
 * @copyright   2023 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tipnextcloud\api;

use curl;
use dml_exception;
use stdClass;
use stored_file;

/**
 * Class nextcloud
 *
 * @package     mod_tipnextcloud
 * @copyright   2023 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class nextcloud {

    const PATH = 'CarpetaDelCurs';

    const SHARE_TYPE_USER = 0;
    const PERMISSION_READ = 1;
    const PERMISSION_EDITION = 2;
    const PERMISSION_UPDATE = 4;
    const PERMISSION_ALL = 31;

    const TIMEOUT = 10;

    /** @var string Host */
    public $host;

    /** @var string URL */
    public $url;

    /** @var string User */
    protected $user;

    /** @var string Password */
    protected $password;

    /**
     * constructor.
     *
     * @throws dml_exception
     */
    public function __construct() {
        $this->host = get_config('tipnextcloud', 'host_nextcloud');
        $this->url = get_config('tipnextcloud', 'url_nextcloud');
        $this->user = get_config('tipnextcloud', 'user_nextcloud');
        $this->password = get_config('tipnextcloud', 'password_nextcloud');
    }

    /**
     * Creating Folder.
     *
     * @param string $foldername
     * @return response
     */
    public function creating_folder(string $foldername): response {
        $curl = new curl();
        $url = $this->host . '/remote.php/dav/files/' . $this->user . '/' . $foldername . '?format=json';
        $headers = array();
        $headers[] = "Content-type: application/json";
        $headers[] = "OCS-APIRequest: true";
        $curl->setHeader($headers);
        $params = [];
        try {
            $curl->post($url, $params, $this->get_options_curl('MKCOL'));
            $response = $curl->getResponse();
            if ($response['HTTP/1.1'] === '201 Created' || $response['HTTP/1.1'] === '405 Method Not Allowed') {
                $response = new response(true, '');
            } else {
                if (!empty($response['HTTP/1.1'])) {
                    $response = new response(false, null, new error('0101', $response['HTTP/1.1']));
                } else {
                    $response = new response(false, null, new error('0102',
                        json_encode($response, JSON_PRETTY_PRINT)));
                }
            }
        } catch (\Exception $e) {
            $response = new response(false, null,
                new error('0100', $e->getMessage()));
        }
        return $response;
    }

    /**
     * Upload File.
     *
     * @param string $filename
     * @param stored_file $f
     * @param stdClass $course
     * @return response
     */
    public function upload_file(string $filename, stored_file $f, stdClass $course): response {
        $curl = new curl();
        $url = $this->host . '/remote.php/dav/files/' . $this->user .
            '/' . self::PATH . '/' . $course->id .'/'. $filename . '?format=json';
        $headers = array();
        $headers[] = "Content-type: application/json";
        $headers[] = "OCS-APIRequest: true";
        $curl->setHeader($headers);
        $params = $f->get_content();
        try {
            $curl->post($url, $params, $this->get_options_curl('PUT'));
            $response = $curl->getResponse();
            if ($response['HTTP/1.1'] === '201 Created' || $response['HTTP/1.1'] === '204 No Content') {
                $response = new response(true, '');
            } else {
                if (!empty($response['HTTP/1.1'])) {
                    $response = new response(false, null, new error('0201', $response['HTTP/1.1']));
                } else {
                    $response = new response(false, null, new error('0202',
                        json_encode($response, JSON_PRETTY_PRINT)));
                }
            }
        } catch (\Exception $e) {
            $response = new response(false, null,
                new error('0200', $e->getMessage()));
        }
        return $response;
    }

    /**
     * Upload File.
     *
     * @param string $filename
     * @param string $content
     * @return response
     */
    public function upload_file_content(string $filename, string $content): response {
        $curl = new curl();
        $url = $this->host . '/remote.php/dav/files/' . $this->user .
            '/' . self::PATH . '/' . $filename . '?format=json';
        $headers = array();
        $headers[] = "Content-type: application/json";
        $headers[] = "OCS-APIRequest: true";
        $curl->setHeader($headers);
        $params = $content;
        try {
            $curl->post($url, $params, $this->get_options_curl('PUT'));
            $response = $curl->getResponse();
            if ($response['HTTP/1.1'] === '201 Created' || $response['HTTP/1.1'] === '204 No Content') {
                $response = new response(true, '');
            } else {
                if (!empty($response['HTTP/1.1'])) {
                    $response = new response(false, null, new error('0201', $response['HTTP/1.1']));
                } else {
                    $response = new response(false, null, new error('0202',
                        json_encode($response, JSON_PRETTY_PRINT)));
                }
            }
        } catch (\Exception $e) {
            $response = new response(false, null,
                new error('0200', $e->getMessage()));
        }
        return $response;
    }

    /**
     * Listing.
     *
     * @param string $file
     * @return response
     */
    public function listing(string $file): response {
        $url = $this->host . '/remote.php/dav/files/' . $this->user . '/' . $file;
        $headers = array();
        $headers[] = "Content-type: application/xml";
        $headers[] = "OCS-APIRequest: true";
        $headers[] = 'Authorization: Basic '. base64_encode($this->user .':' . $this->password);
        $params = '<d:propfind  xmlns:d="DAV:" xmlns:oc="http://owncloud.org/ns" xmlns:nc="http://nextcloud.org/ns">
                    <d:prop>
                        <d:getlastmodified />
                        <d:getetag />
                        <d:getcontenttype />
                        <d:resourcetype />
                        <oc:fileid />
                        <oc:permissions />
                        <oc:size />
                        <d:getcontentlength />
                        <nc:has-preview />
                        <oc:favorite />
                        <oc:comments-unread />
                        <oc:owner-display-name />
                        <oc:share-types />
                    </d:prop>
                   </d:propfind>';
        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'PROPFIND',
                CURLOPT_POSTFIELDS => $params,
                CURLOPT_HTTPHEADER => $headers,
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $xml = str_replace('d:', '', $response);
            $xml = str_replace('oc:', '', $xml);
            $xml = str_replace('nc:', '', $xml);
            $xml = simplexml_load_string($xml);
            if ($xml === false) {
                $response = new response(
                    false, null, new error('0302', 'XML has errors'));
                return $response;
            }
            if (isset($xml->response->propstat->prop->fileid)) {
                $fileid = current($xml->response->propstat->prop->fileid);
                $response = new response(true, $fileid);
            } else {
                $response = new response(
                    false, null, new error('0301', 'The FileID could not be retrieved'));
            }
        } catch (\Exception $e) {
            $response = new response(false, null,
                new error('0300', $e->getMessage()));
        }
        return $response;
    }

    /**
     * Set Permission.
     *
     * @param string $file
     * @param string $username
     * @param int $permission
     * @return response
     */
    public function set_permission(string $file, string $username, int $permission): response {
        $curl = new curl();
        $url = $this->host . '/ocs/v2.php/apps/files_sharing/api/v1/shares?format=json';
        $headers = array();
        $headers[] = "Content-type: application/json";
        $headers[] = "OCS-APIRequest: true";
        $curl->setHeader($headers);
        $params = new stdClass();
        $params->path = $file;
        $params->shareType = self::SHARE_TYPE_USER;
        $params->permissions = $permission;
        $params->shareWith = $username;
        try {
            $res = $curl->post($url, json_encode($params), $this->get_options_curl('POST'));
            $res = json_decode($res, true);
            $response = $curl->getResponse();
            if (isset($response['HTTP/1.1'])) {
                if ($response['HTTP/1.1'] === '200 OK') {
                    if (isset($res['ocs']['data']['id'])) {
                        $response = new response(true, $res['ocs']['data']['id']);
                    } else {
                        $response = new response(false, null, new error('0402', 'Respuesta no esperada'));
                    }
                } else {
                    if (!empty($response['HTTP/1.1'])) {
                        $response = new response(false, null, new error('0401', $response['HTTP/1.1']));
                    } else {
                        $response = new response(false, null, new error('0403',
                            json_encode($response, JSON_PRETTY_PRINT)));
                    }
                }
            } else {
                $response = new response(true, null, new error('0404',
                    json_encode($response, JSON_PRETTY_PRINT)));
            }
        } catch (\Exception $e) {
            $response = new response(false, null,
                new error('0400', $e->getMessage()));
        }
        return $response;
    }

    /**
     * Get Options CURL.
     *
     * @param string $method
     * @return array
     */
    private function get_options_curl(string $method): array {
        return [
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_TIMEOUT' => self::TIMEOUT,
            'CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
            'CURLOPT_CUSTOMREQUEST' => $method,
            'CURLOPT_SSLVERSION' => CURL_SSLVERSION_TLSv1_2,
            'CURLOPT_USERPWD' => "{$this->user}:{$this->password}"
        ];
    }
}
