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
 * Remote Learner Update Manager - Moodle Addon Self Service upgrade setting endpoint.
 *
 * @package   local_rlsiteadmin
 * @copyright 2017 Remote Learner Inc http://www.remote-learner.net
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

$cache = new local_rlsiteadmin_data_cache();
$addons = $cache->get_data('addonlist');

$addon = required_param('addon', PARAM_TEXT);
// Options are manual - for only manual update by mass interface.
//               auto - Nightly update down with in upgrade time schedule.
$method = required_param('upgrademethod', PARAM_TEXT);
if (!in_array($method, ['manual', 'auto'])) {
    print_error('invalid setting');
}

$addonsexist = true;
$addonstoupdate = explode(',', $addon);
foreach ($addonstoupdate as $addon) {
    if (!empty($addon)&&!array_key_exists($addon, $addons['data'])) {
        // This plugin does not exist in the cache.
        $addonsexist = false;
    }
}

if ($addons['result'] == 'OK' && $addonsexist) {
    // Get current configuration.
    $addonsettings = json_decode(get_config('local_rlsiteadmin', 'plugins_upgrademethod'), true);
    if (!is_array($addonsettings)) {
        $addonsettings = [];
    }

    $update = false;
    foreach ($addonstoupdate as $addon) {
        if (!empty($addon)) {
            $addonsettings[$addon] = $method;
            $addons['data'][$addon]['upgrademethod'] = $method;
            $update = true;
        }
    }

    if ($update) {
        set_config('plugins_upgrademethod', json_encode($addonsettings), 'local_rlsiteadmin');
        $cache->update_data('addonlist', $addons);
    }

    print(json_encode(['result' => 'OK']));
} else if ($addons['result'] == 'OK') {
    $result = array('result' => 'Failed', 'error' => get_string('unknown_addon', 'local_rlsiteadmin'));
} else {
    $result = array('result' => 'Failed', 'error' => get_string('communication_error', 'local_rlsiteadmin'));
}