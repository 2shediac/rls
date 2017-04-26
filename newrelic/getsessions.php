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
 * Remote Learner Dashboard - New Relic 7 Day Session Data
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

$sessionstats = \local_rlsiteadmin\lib\widgetbase::instance('sessionstats');

// Get the unique sessions for the last week delivered in 30 minute increments.
$query = "SELECT uniqueCount(session) FROM PageView TIMESERIES 30 minutes WHERE appName LIKE '%{rlsiteid}%' SINCE 1 week ago";
$sessiondata = $sessionstats->get_newrelic_data($query, 'sessions');

// Loop through the data to get it in the appropriate format for the graph.
$formatteddata = array();
header('Content-Type: application/json');
if (empty($sessiondata->timeSeries)) {
    print(json_encode($formatteddata));
    exit;
}
foreach ($sessiondata->timeSeries as $ts) {
    $datapoint = array();
    // Javascript uses miliseconds. Take the average of the start and end times.
    $datapoint['time'] = 1000*($ts->beginTimeSeconds+$ts->endTimeSeconds)/2;
    $datapoint['sessions'] = $ts->results[0]->uniqueCount;
    $formatteddata[] = $datapoint;

}

print(json_encode($formatteddata));
