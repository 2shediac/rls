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
 * @package rlsiteadminwidget_sessionstats
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2017 onwards Remote-Learner Inc (http://www.remote-learner.net)
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Session stats widget';
$string['name'] = 'Session stats';
$string['description'] = 'Displays sessions stats';
$string['widget_info'] = 'This displays the number of user sessions within the past 7 days.
Sessions expire when the user closes the browser. When the user opens a new browser window, a new session is created.';
