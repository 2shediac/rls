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
 * Remote Learner Dashboard - New Relic Browser Use Data
 *
 * @package   local_rlsiteadmin
 * @copyright 2017 Remote Learner Inc http://www.remote-learner.net
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$dir = dirname(__FILE__);
require_once($dir.'/../../../config.php');

require_login(SITEID);

if (!has_capability('moodle/site:config', context_system::instance())) {
    print_error('siteadminonly');
}

$browserstats = \local_rlsiteadmin\lib\widgetbase::instance('browserstats');

// Get the unique sessions for the last week delivered in 30 minute increments.
$query = "SELECT percentage(uniquecount(session), WHERE userAgentName = 'Chrome' AND deviceType = 'Desktop') AS 'Chrome',
            percentage(uniquecount(session), WHERE userAgentName = 'Firefox' AND deviceType = 'Desktop') AS 'FireFox',
            percentage(uniquecount(session), WHERE userAgentName = 'IE' AND deviceType = 'Desktop') AS 'IE',
            percentage(uniquecount(session), WHERE userAgentName = 'Microsoft Edge') AS 'Microsoft Edge',
            percentage(uniquecount(session), WHERE userAgentName = 'Safari' AND deviceType = 'Desktop') AS 'Safari',
            percentage(uniquecount(session), WHERE userAgentName = 'Safari' AND deviceType != 'Desktop') AS 'Mobile Safari',
            percentage(uniquecount(session), WHERE userAgentName = 'Chrome' AND deviceType != 'Desktop') AS 'Mobile Chrome',
            percentage(uniquecount(session), WHERE userAgentName = 'IE' AND deviceType != 'Desktop') AS 'Mobile IE',
            percentage(uniquecount(session), WHERE userAgentName = 'Firefox' AND deviceType != 'Desktop') AS 'Mobile Firefox'
            FROM PageView WHERE appName LIKE '%{rlsiteid}%' SINCE 1 week ago";

$browserdata = $browserstats->get_newrelic_data($query, 'browsers');

// Loop through the data to get it in the appropriate format for the graph.
$formatteddata = array();
header('Content-Type: application/json');
if (empty($browserdata->results)) {
    print(json_encode($formatteddata));
    exit;
}
for ($i=0; $i<count($browserdata->results); $i++) {
    $browserpercentage = $browserdata->results[$i]->result;
    // Only show data if more than .5% of browser share.
    if ($browserpercentage >= .5) {
        $browsername = $browserdata->metadata->contents[$i]->alias;
        $formatteddata[$browsername] = array($browserpercentage);
    }

}

print(json_encode($formatteddata));
