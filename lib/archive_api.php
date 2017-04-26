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
 * Remote Learner Update Manager - Local addon cache client
 *
 * @package    local_rlsiteadmin
 * @copyright  2014 Remote Learner Inc http://www.remote-learner.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_rlsiteadmin_archive_api {
    /** @var string The name of the VM. */
    protected $vmname;

    /** @var string The name of the cluster. */
    protected $cluster;

    /** @var string The version of the API. */
    protected $apiversion;

    /** @var string The api url. */
    protected $apiurl;

    /** @var string The path to where the global.ini file is found. */
    protected $globalinipath = '/mnt/data/conf/global.ini';

    /** @var array The settings contained in global.ini. */
    public $settings;

    /**
     * Constructor
     */
    public function __construct() {
        // Get FQDN of host.
        $fqdn = gethostname();
        // Get host-only portion of FQDN.
        $shorthost = explode('.', $fqdn)[0];
        // Get cluster from host-only portion to correctly set 'aws' in apiurl.
        $this->cluster = $this->get_cluster($shorthost);
        // Trim 'aws-' prefix from any hostname if it's present.
        $this->vmname = str_replace('aws-', '', $shorthost);

        if (strpos($fqdn, '.rlem.net') !== false) {
            // Handle older, internally hosted VMs like rl01-3-v2907.rlem.net.
            $domain = '.rlem.net';
            $this->apiversion = "1.0";
        } else if (strpos($fqdn, '.rl-public.net') !== false) {
            // Handle newer, externally hosted VMs like aws-0kjkhqaab.rl-public.net.
            $domain = '.rl-public.net';
            $this->apiversion = "2.0";
        }
        // TODO: Add detection of region for specifying US-vs-CA mach servers on AWS.
        $this->apiurl = "http://".$this->cluster."-mach.rlem.net/".$this->apiversion."/";
        $this->settings = $this->get_settings();
        if (isset($this->settings['site_id'])) {
            $this->apiversion = "2.0";
        } else {
            $this->apiversion = "1.0";
            $this->settings['site_id'] = $this->vmname;
        }
        $this->apiurl = "http://".$this->cluster."-mach.rlem.net/".$this->apiversion."/";
    }

    /**
     * Get cluster name.
     * @param string The vmname
     * @return string The name of the cluster.
     */
    public function get_cluster($vmname) {
        $vmnameparts = explode("-", $vmname);
        if ($vmnameparts[0] == "rl01") {
            if ($vmnameparts[1] == "3") {
                $cluster = "rl01-3";
            } else {
                $cluster = "rl01-2";
            }
        } else {
            $cluster = $vmnameparts[0];
        }
        return $cluster;
    }

    /**
     * Handle curl calls.
     * @return array The settings array contaning global.ini settings.
     */
    protected function get_settings() {
        global $CFG;
        $sitename = basename($CFG->dirroot);
        $settings = array();
        $this->globalinipath = local_rlsiteadmin_get_global_ini_file();
        if (file_exists($this->globalinipath) && is_readable($this->globalinipath)) {
            $globalini = parse_ini_file($this->globalinipath, true);
            if (array_key_exists($sitename, $globalini)) {
                $settings = $globalini[$sitename];
            }
            $settings['site_id'] = $globalini['webservers']['site_id'];
        }
        if (empty($settings["snapshot_expiration"])) {
            $settings["snapshot_expiration"] = 14; // Snapshots are stored for 14 days unless otherwise specified in global.ini
        }
        if (!empty($CFG->behat_wwwroot) && $CFG->wwwroot == $CFG->behat_wwwroot) {
            $settings['archive_enabled'] = 1;
            $settings['site_id'] = 'add3jjaac';
            $settings['max_archived_snapshots'] = 5;
        }
        return $settings;
    }

    /**
     * Handle curl calls.
     *
     * @param string $url The url to use for the API call
     * @param string $method The request method to use for the API call
     * @param array $postfields The request post fileds to use for POST API calls
     * @return array The json encoded response from the API and HTTP code of the curl call
     */
    protected function handle_curl($url, $method, $postfields=array()) {
        // Check if archive is enabled.
        if (empty($this->settings['archive_enabled'])) {
            $code = 402;
            $result = '{"error":"Not enabled"}';
            return array('return' => $result, 'code' => $code);
        }
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'rlagent');
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if ($method == 'GET') {
            // Nothing special here
        } else if ($method == 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);
        } else {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        }
        $result = trim(curl_exec($curl));
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        return array('return' => $result, 'code' => $code);
    }

    /**
     * Beautify time from unix time source array.
     *
     * @param array $unixtimes The array of unix times
     * @param boolean $expiration Flag to add expiration
     * @return array $beautified The beautified time array containing original time (id),
     * human readable date (date), and number of days before expiration for each.
     */
    protected function beautify_time($unixtimes, $expiration = false) {
        if (empty($unixtimes) || !is_array($unixtimes)) {
            return [];
        }
        arsort($unixtimes);
        $beautified = [];
        foreach($unixtimes as $ut) {
            $item = [];
            $item['id'] = $ut;
            $item['date'] = userdate($ut);

            // Calculate expiration.
            if ($expiration) {
                $now = usertime(time());
                // Get difference between now and snapshottime. Round to the lowest day.
                $timedifference = floor(($now - usertime($ut)) / 24 / 60 / 60);
                $expirationdays = $this->settings["snapshot_expiration"] - $timedifference;
                $item['expiration'] = $expirationdays;
            }
            $beautified[] = $item;
        }
        return $beautified;
    }

    /**
     * Check if setup to use archive.
     *
     * @return string The json encoded response from the API
     */
    public function has_archive() {
        $apipath = "/has_archive";
        $result = $this->handle_curl($this->apiurl.$this->settings['site_id'].$apipath, 'GET');
        return $result;
    }

    /**
     * List snapshots available for archival.
     *
     * @return string The json encoded response from the API
     */
    public function list_snapshots() {
        $apipath = "/snapshots/list";
        $result = $this->handle_curl($this->apiurl.$this->settings['site_id'].$apipath, 'GET');
        $snapshotsjson = json_decode($result['return'], true);
        $snapshotsjson['snapshots'] = $this->beautify_time($snapshotsjson['snapshots'], true);
        $result['return'] = json_encode($snapshotsjson);
        return $result;
    }

    /**
     * List archived snapshots.
     *
     * @return string The json encoded response from the API
     */
    public function list_archives() {
        $apipath = "/archives/list";
        $result = $this->handle_curl($this->apiurl.$this->settings['site_id'].$apipath, 'GET');
        $archivesjson = json_decode($result['return'], true);
        $archivesjson['archives']['done'] = $this->beautify_time($archivesjson['archives']['done']);
        $archivesjson['archives']['in-progress'] = $this->beautify_time($archivesjson['archives']['in-progress']);
        $result['return'] = json_encode($archivesjson);
        return $result;
    }

    /**
     * Schedule snapshot for archival.
     *
     * @param string $snapshotid The ID of the snapshot to schedule for archival
     * @return string The json encoded response from the API
     */
    public function archive_snapshot($snapshotid) {
        // First check the current count of archives and compare agains the max allowed in globla.ini
        $archives = $this->list_archives();
        $archives = json_decode($archives['return'], true);
        $numarchives = count($archives['archives']['done']) + count($archives['archives']['in-progress']);
        if ($numarchives >= $this->settings['max_archived_snapshots']) {
            $result = array();
            $result['code'] = 409; // Conflict
            $result['return'] = '{"error":"Not allowed: archive max limit reached"}';
        } else {
            $apipath = "/snapshots/".$snapshotid."/archive";
            $result = $this->handle_curl($this->apiurl.$this->settings['site_id'].$apipath, 'POST');
        }
        return $result;
    }

    /**
     * Restore archived snapshots.
     *
     * @param string $archiveid The ID of the archive to restore
     * @return string The json encoded response from the API
     */
    public function restore_archive($archiveid) {
        global $USER;
        $restores = $this->list_restored();
        $restores = json_decode($restores['return'], true);
        $numrestores = count($restores['restored']['done']) + count($restores['restored']['in-progress']);
        if ($numrestores >= $this->settings['max_restored_snapshots']) {
            $result = array();
            $result['code'] = 409; // Conflict
            $result['return'] = '{"error":"Not allowed: restored max limit reached"}';
        } else {
            $apipath = "/archives/".$archiveid."/scratch_restore";
            $params = array();
            $params["restore_duration"] = $this->settings['restore_duration'];
            $params["email"] = $USER->email;
            $params["subject"] = get_string('archive_restore_email_subject', 'local_rlsiteadmin');
            $params["body"] = get_string('archive_restore_email_body', 'local_rlsiteadmin');
            $result = $this->handle_curl($this->apiurl.$this->settings['site_id'].$apipath, 'POST', $params);
        }
        return $result;
    }

    /**
     * List restored snapshots.
     *
     * @return string The json encoded response from the API
     */
    public function list_restored() {
        $apipath = "/restored/list";
        $result = $this->handle_curl($this->apiurl.$this->settings['site_id'].$apipath, 'GET');
        $restoredjson = json_decode($result['return'], true);
        for ($i=0; $i<count($restoredjson['restored']['done']); $i++) {
            $remaining = intval($restoredjson['restored']['done'][$i]['remaining-seconds']);
            $restoredjson['restored']['done'][$i]['expiration'] = userdate(time() + $remaining);
        }
        $result['return'] = json_encode($restoredjson);
        return $result;
    }

    /**
     * Discard an archived snapshot.
     *
     * @param string $archiveid The ID of the archive to discard
     * @return string The json encoded response from the API
     */
    public function discard_archive($archiveid) {
        // Look for a match in current restored archives before discarding.
        $restores = $this->list_restored();
        $restores = json_decode($restores['return'], true);
        $allrestores = array_merge($restores['restored']['done'], $restores['restored']['in-progress']);
        foreach($allrestores as $restore) {
            if ($archiveid == $restore['id']) {
                // Match found! Return an error.
                $result = array();
                $result['code'] = 409; // Conflict
                $result['return'] = '{"error":"Not allowed: restored in progress on archive '.$archiveid.'"}';
                return $result;
            }
        }
        $apipath = "/archives/".$archiveid;
        $result = $this->handle_curl($this->apiurl.$this->settings['site_id'].$apipath, 'DELETE');
        return $result;
    }

   /**
     * Destroy a scratch VM.
     *
     * @param string $restoreid The ID of the archive restore to destroy
     * @return string The json encoded response from the API
     */
    public function destroy_restore($restoreid) {
        $apipath = "/archives/".$restoreid."/scratch_destroy";
        $result = $this->handle_curl($this->apiurl.$this->settings['site_id'].$apipath, 'DELETE');
        return $result;
    }

   /**
     * List available tasks for a given VM.
     *
     * @return string The json encoded response from the API
     */
    public function list_tasks() {
        $apipath = "/tasks/list";
        $result = $this->handle_curl($this->apiurl.$this->settings['site_id'].$apipath, 'GET');
        return $result;
    }

   /**
     * Check task status for a given task.
     *
     * @param string $taskid The ID of the task to restore.
     * @return string The json encoded response from the API
     */
    public function task_status($taskid) {
        $apipath = "/tasks/".$taskid;
        $result = $this->handle_curl($this->apiurl.$this->settings['site_id'].$apipath, 'GET');
        return $result;
    }
}
