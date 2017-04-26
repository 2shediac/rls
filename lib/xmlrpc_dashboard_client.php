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
 * Remote Learner Update Manager - Dashboard XML RPC client
 *
 * @package    local_rlsiteadmin
 * @copyright  2014 Remote Learner Inc http://www.remote-learner.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_rlsiteadmin_xmlrpc_dashboard_client {
    /** @var array An array with the required identity information for the server */
    protected $identity = array();

    /** @var string Server url */
    protected $serverurl = 'https://rlscripts.remote-learner.net';

    /** @var string RL Scritps directory */
    protected $rlscriptsdir = '/rlscripts';

    /**
     * Constructor
     */
    public function __construct() {
        $this->get_webservices_config();
    }

    /**
     * Format the request data
     *
     * @param mixed The data to be sent
     * @return array The data in proper format for encoding
     */
    protected function format_request($data) {
        $formatted = $this->identity;
        $formatted['data'] = array('0' => $data);
        return $formatted;
    }

    /**
     * Get the list of addons from the Dashboard
     */
    public function get_addon_data() {
        global $CFG;
        $branch = local_rlsiteadmin_get_branch_number();
        // Bitmask for all (UI will filter).
        $level = 7;
        // Requirement: Only display private plugins if they belong to this site.
        $private = 1;
        $data = array('branchnum' => $branch, 'level' => $level, 'private' => $private, 'url' => $CFG->wwwroot);

        $response = $this->send_request('get_moodle_plugins', $data);
        return $response;
    }

    /**
     * Get the list of addons from the Dashboard
     */
    public function get_group_data() {
        global $CFG;
        $branch = local_rlsiteadmin_get_branch_number();
        $private = 1;
        $data = array('branchnum' => $branch, 'private' => $private, 'url' => $CFG->wwwroot);
        $response = $this->send_request('get_moodle_plugin_groups', $data);
        return $response;
    }


    /**
     * Get the webservices config information from the local RL Scripts
     */
    protected function get_webservices_config() {
        $file = $this->rlscriptsdir.'/automation/rlscripts/webservices_config';
        if (file_exists($file)) {
            $config = json_decode(trim(shell_exec($file)));
            if (($config != null) && array_key_exists('server', $config)) {
                $this->serverurl = $config->server;
            }
            if (($config != null) && array_key_exists('identity', $config)) {
                $this->identity = (array) $config->identity;
            }
        }
    }

    /**
     * Assign a rating to an addon
     *
     * @param int $userid
     * @param string $addon
     * @param int $rating
     */
    public function rate_addon($addon, $rating) {
        global $CFG, $USER;
        $data = array('plugin_name' => $addon, 'url' => $CFG->wwwroot, 'user_id' => $USER->id, 'rating' => $rating);

        $response = $this->send_request('rate_moodle_plugin', $data);
        return $response;
    }

    /**
     * Send the request to the dashboard.
     *
     * @param string $type The request type
     * @param array $data The formatted data request.
     * @return array The decode result.
     */
    protected function send_request($type, $data = array()) {
        // Create request.
        $request = xmlrpc_encode_request($type, $this->format_request($data));
        $postfields = array('data' => $request);

        // Send request.
        $curl = curl_init($this->serverurl.'/webservices/xmlrpc.php');
        curl_setopt($curl, CURLOPT_USERAGENT, 'rlscripts');
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $response = trim(curl_exec($curl));

        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($code == 403) {
            $decoded = get_string('permission_denied', 'local_rlsiteadmin');
        } else if ($code != 0) {
            $decoded = xmlrpc_decode($response);
        } else {
            $decoded = curl_error($curl);
        }
        return $decoded;
    }

}
