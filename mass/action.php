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
 * Remote Learner Update Manager - Moodle Addon Self Service action page
 *
 * @package   local_rlsiteadmin
 * @copyright 2014 Remote Learner Inc http://www.remote-learner.net
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$dir = dirname(__FILE__);
require_once($dir.'/../../../config.php');
require_once($dir.'/../lib.php');
require_once($dir.'/../lib/xmlrpc_dashboard_client.php');
require_once($dir.'/../lib/data_cache.php');

require_login(SITEID);
global $USER;

if (!has_capability('moodle/site:config', context_system::instance())) {
    print_error('siteadminonly');
}

// The order in the actions array determines the order of operations in the request file.
// Don't change it unless you know what you are dong.
$actions = array('remove', 'add', 'update');

$types = core_component::get_plugin_types();

$cache = new local_rlsiteadmin_data_cache();
$addonlist = $cache->get_data('addonlist');

$messages = array();
$skipped = array();

// Check actions for bad requests.
$addons = array();
foreach ($actions as $action) {
    $skipped[$action] = array();
    $addons[$action] = array();
    $items = optional_param_array($action, array(), PARAM_ALPHANUMEXT);

    foreach ($items as $item) {
        list($type, $name) = explode('_', $item, 2);
        $skip = false;
        if (empty($name)) {
            $values = new stdClass();
            $values->action = $action;
            $values->subject = $item;
            $messages[] = get_string('error_unparseable_name', 'local_rlsiteadmin', $values);
            $skip = true;
        } else if (!array_key_exists($type, $types)) {
            $values = new stdClass();
            $values->name = $item;
            $values->type = $type;
            $messages[] = get_string('error_unknown_addon_type', 'local_rlsiteadmin', $values);
            $skip = true;
        } else if (!array_key_exists($item, $addonlist['data'])) {
            $messages[] = get_string('error_unknown_addon', 'local_rlsiteadmin', $item);
            $skip = true;
        }
        if ($skip) {
            $skipped[$action][$item] = $item;
            continue;
        }
        $addons[$action][$item] = array('type' => $type, 'name' => $name);
    }
}

// Check for useless removals.
foreach ($addons['remove'] as $name => $plugin) {
    $list = core_component::get_plugin_list($plugin['type']);
    if (!array_key_exists($plugin['name'], $list)) {
        unset($addons['remove'][$name]);
        $messages[] = get_string('error_remove_notinstalled', 'local_rlsiteadmin', $name);
        $skipped['remove'][$name] = $name;
    }
}

// Fix the order of removals so that plugins that depend on others are remove first.
// Using Kahn's algorithm from "Topological sorting of large networks"
// Step 1: Create an empty list to contain the sorted plugins
$list = array();
// Step 2: Create a lookup table for dependencies
$lookup = array();
foreach ($addons['remove'] as $name => $plugin) {
    $dependencies = $addonlist['data'][$name]['dependencies'];
    foreach ($dependencies as $depended => $version) {
        if (array_key_exists($depended, $addons['remove'])) {
            if (!array_key_exists($depended, $lookup)) {
                $lookup[$depended] = array();
            }
            $lookup[$depended][$name] = $version;
        }
    }
}
// Step 3: Create a list of plugins with no dependencies
$set = array();
foreach ($addons['remove'] as $name => $plugin) {
    if (!array_key_exists($name, $lookup)) {
        $set[$name] = $plugin;
    }
}
// Step 4: Create the ordered list
while (count($set) > 0) {
    $plugin = array_pop($set);
    $name = $plugin['type'].'_'.$plugin['name'];
    $list[$name] = $plugin;
    $dependencies = $addonlist['data'][$name]['dependencies'];
    foreach ($dependencies as $depended => $version) {
        if (array_key_exists($depended, $lookup)) {
            unset($lookup[$depended][$name]);
            if (count($lookup[$depended]) == 0) {
                $set[$depended] = $addons['remove'][$depended];
                unset($lookup[$depended]);
            }
        }
    }
}
// Step 5: Check for dependency cycles.
if (count($lookup) > 0) {
    $names = implode(', ', array_keys($lookup));
    $messages[] = get_string('error_dependency_cycle', 'local_rlsiteadmin', $names);
    foreach ($lookup as $name => $dependencies) {
        $skipped['remove'][$name] = $name;
    }
}
// Step 6: Use the sorted list (we won't try to uninstall plugins with cyclical dependencies)
$addons['remove'] = $list;

// Settings for upgrade method.
$addonsettings = json_decode(get_config('local_rlsiteadmin', 'plugins_upgrademethod'), true);

// Flag if cache has been updated.
$cacheupdated = false;

// Check for useless adds.
foreach ($addons['add'] as $name => $add) {
    $list = core_component::get_plugin_list($add['type']);
    $skip = false;
    $exists = array_key_exists($add['name'], $list);
    $removed = array_key_exists($name, $addons['remove']);
    $missing = $addonlist['data'][$name]['missing'];
    if ($exists && !$removed && !$missing) {
        unset($addons['add'][$name]);
        $messages[] = get_string('error_add_installed', 'local_rlsiteadmin', $name);
        $skipped['add'][$name] = $name;
    } else {
        // No previously installed. Set to auto update by default for new plugins.
        $addon = $add['type'].'_'.$add['name'];
        $addonlist['data'][$addon]['upgrademethod'] = 'auto';
        $addonsettings[$addon] = 'auto';
        $cacheupdated = true;
    }
}

if ($cacheupdated) {
    $cache->update_data('addonlist', $addonlist);
    set_config('plugins_upgrademethod', json_encode($addonsettings), 'local_rlsiteadmin');
}

// Check for useless updates.
foreach ($addons['update'] as $name => $update) {
    $list = core_component::get_plugin_list($update['type']);
    $skip = false;
    if (array_key_exists($name, $addons['add'])) {
        $messages[] = get_string('error_update_added', 'local_rlsiteadmin', $name);
        $skip = true;
    } else if (array_key_exists($name, $addons['remove'])) {
        $messages[] = get_string('error_update_removed'. 'local_rlsiteadmin', $name);
        $skip = true;
    } else if (!array_key_exists($update['name'], $list)) {
        $messages[] = get_string('error_update_not_installed', 'local_rlsiteadmin', $name);
        $skip = true;
    }
    if ($skip) {
        unset($addons['update'][$name]);
        $skipped['update'][$name] = $name;
    }
}

$contents = array("site $CFG->dirroot");
foreach ($addons as $action => $items) {
    foreach ($items as $name => $addon) {
        $contents[] = "$action $name";
    }
    if (count($skipped[$action]) > 0) {
        $messages[] = "Skipping $action for the following addons: ".implode(', ', $skipped[$action]);
    }
}

$command = [];

if (count($contents) > 1) {
    $command = local_rlsiteadmin_write_incron_commands($contents, 'addon_', '/var/run/mass');
}

// Add messages after the command execution, so that skipped ad-ons still return a message.
if (array_key_exists('messages', $command)) {
    $command['messages'] = array_merge($messages, $command['messages']);
} else {
    $command['messages'] = $messages;
}

$return = json_encode($command);
print($return);
