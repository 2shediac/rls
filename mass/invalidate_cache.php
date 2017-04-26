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
 * Remote Learner Update Manager
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

$PAGE->set_url('/local/rlsiteadmin/mass/invalidate_cache.php');

// Print header.
print($OUTPUT->header(get_string('admin_tools_heading', 'local_rlsiteadmin')));

$cache = new local_rlsiteadmin_data_cache();
foreach (['grouplist', 'addonlist'] as $type) {
    $data = $cache->get_data($type);
    $data['timestamp'] = 0;
    $cache->update_data($type, $data);
    $data = $cache->get_data($type);
}

echo \html_writer::tag('h3', get_string('admin_tools_invalidate_cache', 'local_rlsiteadmin'));

// Print footer.
print($OUTPUT->footer());
