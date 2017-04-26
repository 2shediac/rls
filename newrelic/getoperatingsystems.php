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
 * Remote Learner Dashboard - New Relic Operating System Use Data
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

$operatingsystemsstats = \local_rlsiteadmin\lib\widgetbase::instance('operatingsystemstats');

// Get the unique sessions for the last week delivered in 30 minute increments.
$query = "SELECT percentage(uniquecount(session), WHERE userAgentOS = 'Mac') AS 'Mac OS',
            percentage(uniquecount(session), WHERE userAgentOS = 'Windows') AS 'Windows',
            percentage(uniquecount(session), WHERE userAgentOS = 'Google Chrome OS') AS 'Google Chrome OS',
            percentage(uniquecount(session), WHERE userAgentOS = 'Linux') AS 'Linux',
            percentage(uniquecount(session), WHERE userAgentOS = 'Android') AS 'Android',
            percentage(uniquecount(session), WHERE userAgentOS = 'iPhone' OR userAgentOS = 'iPad') AS 'iOS',
            percentage(uniquecount(session), WHERE userAgentOS = 'Windows Mobile') AS 'Windows Mobile'
            FROM PageView WHERE appName LIKE '%{rlsiteid}%' SINCE 1 week ago";

$osdata = $operatingsystemsstats->get_newrelic_data($query, 'operatingsystems');

// Loop through the data to get it in the appropriate format for the graph.
$formatteddata = array();
header('Content-Type: application/json');
if (empty($osdata->results)) {
    print(json_encode($formatteddata));
    exit;
}
for ($i=0; $i<count($osdata->results); $i++) {
    $ospercentage = $osdata->results[$i]->result;
    // Only show data if more than .5% of browser share.
    if ($ospercentage >= .5) {
        $osname = $osdata->metadata->contents[$i]->alias;
        $formatteddata[$osname] = array($ospercentage);
    }

}

header('Content-Type: application/json');
print(json_encode($formatteddata));
