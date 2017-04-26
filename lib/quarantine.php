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
 * Remote Learner Update Manager - Version quarantine file
 *
 * This file exists to protect the rest of the plugin from the stupid
 * things that plugin writers do in their version.php files.
 *
 * For example:
 *   - mod_attendance (M27) tries to upgrade from mod_attforblock
 *
 * @package    local_rlsiteadmin
 * @copyright  2015 Remote Learner Inc http://www.remote-learner.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);
$dir = dirname(__FILE__);
require_once($dir.'/../../../config.php');
require_once($CFG->libdir.'/clilib.php');      // cli only functions

// now get cli options
list($options, $unrecognized) = cli_get_params(array('help'=>false),
                                               array('h'=>'help'));

// Moodle would normallly define these classes before reading the version file.
$plugin = new stdClass();
$module = new stdClass();

if (array_key_exists(0, $unrecognized)) {
    eval($unrecognized[0]);
}

if (!empty($plugin->version)) {
    $print = $plugin;
} else if (!empty($module->version)) {
    $print = $module;
}

print(json_encode($print));
