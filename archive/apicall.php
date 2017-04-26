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
 * Remote Learner Update Manager - Plugin data provider page
 *
 * @package   local_rlsiteadmin
 * @copyright 2014 Remote Learner Inc http://www.remote-learner.net
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$dir = dirname(__FILE__);
require_once($dir.'/../../../config.php');
require_once($dir.'/../lib.php');
require_once($dir.'/../lib/archive_api.php');

require_login(SITEID);
if (!has_capability('moodle/site:config', context_system::instance())) {
    print_error('siteadminonly');
}

use local_rlsiteadmin\event; // use keyword for using the namespace

// Get request parameter.
$request = optional_param('request', array(), PARAM_ALPHANUMEXT);

// Create Archive API object.
$archiveapi = new local_rlsiteadmin_archive_api();

// Switch supported request types.
switch ($request) {

    // List snapshots available for archival.
    case "listsnapshots" :
        $result = $archiveapi->list_snapshots();
        break;

    // Schedule snapshot for archival.
    case "archivesnapshot" :
        $snapshotid = optional_param('snapshotid', array(), PARAM_ALPHANUMEXT);
        $result = $archiveapi->archive_snapshot($snapshotid);
        // Log to moodle logs
        if ($result['code'] == 200) {
            $context = context_system::instance();
            $event = \local_rlsiteadmin\event\snapshot_archived::create(array('context' => $context, 'objectid' => $snapshotid));
            $event->trigger();
        }
        break;

    // List archived snapshots.
    case "listarchives" :
        $result = $archiveapi->list_archives();
        break;

    // Restore an archived snapshot.
    case "restorearchive" :
        $archiveid = optional_param('archiveid', array(), PARAM_ALPHANUMEXT);
        $result = $archiveapi->restore_archive($archiveid);
        // Log to moodle logs
        if ($result['code'] == 200) {
            $context = context_system::instance();
            $event = \local_rlsiteadmin\event\archive_restored::create(array('context' => $context, 'objectid' => $archiveid));
            $event->trigger();
        }
        break;

    // List restored snapshots for a given VM.
    case "listrestored" :
        $result = $archiveapi->list_restored();
        break;

    // Discard an archived snapshot.
    case "discardarchive" :
        $archiveid = optional_param('archiveid', array(), PARAM_ALPHANUMEXT);
        $result = $archiveapi->discard_archive($archiveid);
        // Log to moodle logs
        if ($result['code'] == 200) {
            $context = context_system::instance();
            $event = \local_rlsiteadmin\event\archive_discarded::create(array('context' => $context, 'objectid' => $archiveid));
            $event->trigger();
        }
        break;

    // Destroy a scratch VM.
    case "destroyrestore" :
        $restoreid = optional_param('restoreid', array(), PARAM_ALPHANUMEXT);
        $result = $archiveapi->destroy_restore($restoreid);
        // Log to moodle logs
        if ($result['code'] == 200) {
            $context = context_system::instance();
            $event = \local_rlsiteadmin\event\restore_destroyed::create(array('context' => $context, 'objectid' => $restoreid));
            $event->trigger();
        }
        break;

    // List available tasks for a given VM.
    case "listtasks" :
        $result = $archiveapi->list_tasks();
        break;

    // Get task status.
    case "taskstatus" :
        $taskid = optional_param('taskid', array(), PARAM_ALPHANUMEXT);
        $result = $archiveapi->task_status($taskid);
        break;

    // Invalid request
    default :
        $result = array();
        $result['code'] = 400; // Bad Request
        $result['return'] = '{"error":"Invalid request"}';

}

// Define http_reponse_code for PHP 5.3
if (!function_exists('http_response_code')) {
    function http_response_code($newcode = NULL) {
        static $code = 200;
        if($newcode !== NULL)
        {
            header('X-PHP-Response-Code: '.$newcode, true, $newcode);
            if(!headers_sent())
                $code = $newcode;
        }
        return $code;
    }
}

// Print response
http_response_code(200);
$decodedreturn = json_decode($result['return']);
$decodedreturn->httpcode = $result['code'];
print(json_encode(array('archiveapi' => $decodedreturn)));

