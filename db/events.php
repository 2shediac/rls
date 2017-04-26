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
 * RL Site Admin event handlers.
 *
 * @package local_rlsiteadmin
 * @copyright 2016 onwards Remote-Learner Inc. (http://www.remote-learner.net)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
        [
            'eventname' => '\local_rlsiteadmin\event\plugin_install',
            'callback' => 'local_rlsiteadmin_observer::plugin_install',
            'includefile' => '/local/rlsiteadmin/lib.php'
        ],
        [
            'eventname' => '\local_rlsiteadmin\event\plugin_remove',
            'callback' => 'local_rlsiteadmin_observer::plugin_remove',
            'includefile' => '/local/rlsiteadmin/lib.php'
        ],
        [
            'eventname' => '\local_rlsiteadmin\event\plugin_update',
            'callback' => 'local_rlsiteadmin_observer::plugin_update',
            'includefile' => '/local/rlsiteadmin/lib.php'
        ]
];
