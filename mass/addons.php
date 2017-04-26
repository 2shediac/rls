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
 * Remote Learner Update Manager - Plugin data provider page
 *
 * @package   local_rlsiteadmin
 * @copyright 2014 Remote Learner Inc http://www.remote-learner.net
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

$type = optional_param('type', 'list', PARAM_ALPHA);

$types = array('addonlist' => 'addons', 'grouplist' => 'groups');
if (!array_key_exists($type, $types)) {
    print_error('Unknown type');
}

$cache = new local_rlsiteadmin_data_cache();
$data = $cache->get_data($type);

print(json_encode(array($types[$type] => $data['data'])));
