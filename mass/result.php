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
 * Remote Learner Update Manager - Moodle Addon Self Service addon result page
 *
 * @package   local_rlsiteadmin
 * @copyright 2014 Remote Learner Inc http://www.remote-learner.net
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$dir = dirname(__FILE__);
require_once($dir.'/../../../config.php');

require_login(SITEID);

if (!has_capability('moodle/site:config', context_system::instance())) {
    print_error('siteadminonly');
}

$file = $CFG->dataroot.'/manager/addons/results.txt';

// Default to the error value returned by file()
$rows = false;
if (file_exists($file) && is_readable($file)) {
    $rows = file($file);
}

$result = array();
if (is_array($rows)) {
    foreach ($rows as $row) {
        $result[] = trim($row);
    }
}

print(json_encode($result));
