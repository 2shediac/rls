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
 * Remote Learner Update Manager - Plugin action status provider
 *
 * @package   local_rlsiteadmin
 * @copyright 2016 Remote Learner Inc http://www.remote-learner.net
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$dir = dirname(__FILE__);
require_once($dir.'/../../../config.php');
require_once($dir.'/../lib.php');
require_once($dir.'/../lib/data_cache.php');

require_login(SITEID);
if (!has_capability('moodle/site:config', context_system::instance())) {
    print_error('siteadminonly');
}

$commandfile = required_param('addoncommand', PARAM_ALPHANUMEXT);
$commandhash = required_param('hash', PARAM_ALPHANUMEXT);

// Store command file name.
$data = array();
$data['command'] = $commandfile;

// Check if dispatch command for the addon file is currently running.
exec("ps aux | grep -i '{$commandfile}' | grep -v 'grep' | wc -l", $output, $isrunning);
$data['running'] = intval($output[0]);

// Read command file contents.
$commandfilepath = "/var/run/mass/".$commandfile;
if (file_exists($commandfilepath) && is_readable($commandfilepath) && $commandhash == md5_file($commandfilepath)) {
    $data['fileexists'] = true;
} else {
    $data['fileexists'] = false;
}

// Read results.txt file.
$resultsfile = $CFG->dataroot.'/manager/addons/results.txt';
if (file_exists($resultsfile) && is_readable($resultsfile)) {
    $results = file_get_contents($resultsfile);
    $data['results'] = $results;
}

print(json_encode($data));
