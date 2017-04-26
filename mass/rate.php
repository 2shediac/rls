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
 * Remote Learner Update Manager - Moodle Addon Self Service addon rating page
 *
 * @package   local_rlsiteadmin
 * @copyright 2014 Remote Learner Inc http://www.remote-learner.net
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$dir = dirname(__FILE__);
require_once($dir.'/../../../config.php');
require_once($dir.'/../lib.php');
require_once($dir.'/../lib/xmlrpc_dashboard_client.php');
require_once($dir.'/../lib/data_cache.php');

require_login(SITEID);

if (!has_capability('moodle/site:config', context_system::instance())) {
    print_error('siteadminonly');
}

$cache = new local_rlsiteadmin_data_cache();
$addons = $cache->get_data('addonlist');

$addon = required_param('addon', PARAM_ALPHANUMEXT);
$rating = required_param('rating', PARAM_INT);

if (($addons['result'] == 'OK') && (array_key_exists($addon, $addons['data']))) {
    $client = new local_rlsiteadmin_xmlrpc_dashboard_client();
    $result = $client->rate_addon($addon, $rating);
    if (is_array($result) && array_key_exists('result', $result) && ($result['result'] == 'OK')) {
        $row = new stdClass();
        $row->userid = $USER->id;
        $row->plugin = $addon;
        $row->rating = $rating;
        $DB->insert_record('local_rlsiteadmin_rating', $row);

        // Update cache
        $addons['data'][$addon]['myrating'] = $rating;
        $cache->update_data('addonlist', $addons);
    }
} else if ($addons['result'] == 'OK') {
    $result = array('result' => 'Failed', 'error' => get_string('unknown_addon', 'local_rlsiteadmin'));
} else {
    $result = array('result' => 'Failed', 'error' => get_string('communication_error', 'local_rlsiteadmin'));
}
print(json_encode($result));
