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
 * Accessory functions for RL Agent block.
 *
 * @package   local_rlsiteadmin
 * @copyright 2014 Amy Groshek for Remote-Learner.net
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
define('RLSA_MASS_ADD_ACTION', 'add');
define('RLSA_MASS_UPDATE_ACTION', 'update');
define('RLSA_MASS_REMOVE_ACTION', 'remove');

require_once(__DIR__.'/lib/archive_api.php');

function local_rlsiteadmin_extend_navigation(\global_navigation $nav) {
    return local_rlsiteadmin_extends_navigation($nav);
}

function local_rlsiteadmin_extends_navigation(global_navigation $nav) {
    if (is_siteadmin() !== true) {
        return false;
    }

    $rltitle = get_string('navcategory', 'local_rlsiteadmin');
    $node = $nav->create($rltitle, null, \navigation_node::TYPE_CATEGORY);

    // Dashboard link.
    $sadtext = get_string('navsad', 'local_rlsiteadmin');
    $sadurl = new \moodle_url('/local/rlsiteadmin/dashboard/');
    $sadpix = new pix_icon('rllogo', 'logo', 'local_rlsiteadmin');
    $node->add($sadtext, $sadurl, \navigation_node::TYPE_CUSTOM, null, null, $sadpix);

    // Add-on Manager link.
    $aomtext = get_string('navaom', 'local_rlsiteadmin');
    $cache = cache::make('local_rlsiteadmin', 'addondata');
    $data = $cache->get('addonlist');
    $numupgrades = (!empty($data['numupgrades'])) ? (int)$data['numupgrades'] : 0;
    if ($numupgrades > 10) {
        $numupgrades = 10;
    }
    if ($numupgrades > 0) {
        $aompix = new \pix_icon('updates'.$numupgrades, 'upgrades', 'local_rlsiteadmin');
        $aomurl = new \moodle_url('/local/rlsiteadmin/mass/', ['updateable' => 1]);
    } else {
        $aompix = $sadpix;
        $aomurl = new \moodle_url('/local/rlsiteadmin/mass/');
    }
    $node->add($aomtext, $aomurl, \navigation_node::TYPE_CUSTOM, null, null, $aompix);

    // BackTrack link.
    $archiveapi = new local_rlsiteadmin_archive_api();
    if (array_key_exists('archive_enabled', $archiveapi->settings) && $archiveapi->settings['archive_enabled']) {
        $archiveurl = new \moodle_url('/local/rlsiteadmin/archive/');
        $archivetext = get_string('managearchive', 'local_rlsiteadmin');
        $node->add($archivetext, $archiveurl, \navigation_node::TYPE_CUSTOM, null, null, $sadpix);
    }

    $node->force_open();

    $nav->add_node($node);
}

/**
 * Create the command directories to write logs to.
 */
function local_rlsiteadmin_create_directories() {
    global $CFG;

    $dirs = array($CFG->dataroot.'/manager/addons', $CFG->dataroot.'/manager/refresh');

    foreach ($dirs as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0770, true);
        }
    }
}

/**
 * Figure out the proper branch number
 *
 * @return int The branch number
 */
function local_rlsiteadmin_get_branch_number() {
    global $CFG;

    // Figure out the branch number.
    $matches = array();
    preg_match('/(\d+)\.(\d+)./', $CFG->release, $matches);
    $branch = $matches[1].$matches[2];

    return $branch;
}

/**
 * Get an ini value from the global.ini file
 *
 * Needs vfsStream for unit testing.
 *
 * @param string $field The field to return
 * @param string $segment The segment to return the field from
 * @return string The field value
 */
function local_rlsiteadmin_get_ini_value($field, $segment) {
    $inifile = local_rlsiteadmin_get_global_ini_file();
    if (file_exists($inifile) && is_readable($inifile)) {
        $ini = parse_ini_file($inifile, true);
        if ((false !== $ini) && isset($ini[$segment][$field])) {
            return $ini[$segment][$field];
        }
    }
    return false;
}

/*
 * If more than 24 hours have elapsed since last sandbox update, return true.
 *
 * @return Boolean True if > 7 days since last sandbox update.
 */
function local_rlsiteadmin_needs_update() {
    global $CFG;

    // Check if this is a sandbox site
    $sandbox = false;
    $sitename = basename($CFG->dirroot);
    if (preg_match('/^moodle_sand([0-9]*|)$/', $sitename)) {
        $sandbox = true;
    } else if (local_rlsiteadmin_get_ini_value('refresh_source', $sitename) !== false) {
        $sandbox = true;
    }

    // If it's a sandbox check how long since the last refresh.
    if ($sandbox) {
        $lastrefresh = 0;
        $path = $CFG->dataroot.'/manager/refresh/last.txt';
        if (file_exists($path) && is_readable($path)) {
            $lastrefresh = intval(file_get_contents($path));
        }

        // Check if the last refresh was more than 7 days ago (7 x 24 x 60 x 60 = 604800).
        if ((time() - $lastrefresh) > 604800) {
            return true;
        }
    }
    return false;
}

/**
 * Write commands to an incron file.
 *
 * Needs vfsStream for unit testing.
 *
 * @param array $commands The commands to write to the command file
 * @param string $prefix The filename prefix to use for the command file
 * @param string $path The directory to write the command file to.
 * @return array $commandreturn The command file and any messages associated while creating the file.
 */
function local_rlsiteadmin_write_incron_commands($commands, $prefix, $path) {
    $messages = array();

    if (!file_exists($path) && !mkdir($path, 0770, true)) {
        $messages[] = get_string('error_unable_to_create_dispatch_dir', 'local_rlsiteadmin', $path);
    } else {
        // Write to a tempfile to make requests atomic.
        $tmpfile = tempnam(sys_get_temp_dir(), $prefix);
        $file = $path.'/'.basename($tmpfile);
        if (file_put_contents($tmpfile, implode("\n", $commands))) {
            if (copy($tmpfile, $file)) {
                if (!unlink($tmpfile)) {
                    $messages[] = get_string('error_unable_to_delete_temp_command_file', 'local_rlsiteadmin');
                }

                // Parse commands array.
                $parse_cmds = local_rlsiteadmin_parse_commands($commands);
                // Trigger events.
                local_rlsiteadmin_trigger_mass_events($parse_cmds);
            } else {
                $messages[] = get_string('error_unable_to_copy_command', 'local_rlsiteadmin');
            }
        } else {
            $messages[] = get_string('error_unable_to_write_temp_command_file', 'local_rlsiteadmin');
        }
    }

    $commandreturn = array();
    $commandreturn['file'] = basename($file);
    $commandreturn['hash'] = md5_file($file);
    $commandreturn['messages'] = $messages;
    return $commandreturn;
}

/**
 * Trigger events based on the mass commands that are to be executed.
 * @param array $parse_cmds An array whose keys are actions and values are a comma delimited string of plugins.
 */
function local_rlsiteadmin_trigger_mass_events($parse_cmds) {
    global $USER;
    foreach ($parse_cmds as $action => $plugins) {
        $data = array(
            'other' => array(
                'plugins' => $plugins,
                'fullname' => $USER->firstname.' '.$USER->lastname
            )
        );

        if (RLSA_MASS_ADD_ACTION == $action) {
            $event = \local_rlsiteadmin\event\plugin_install::create($data);
            $event->trigger();
        }

        if (RLSA_MASS_REMOVE_ACTION == $action) {
            $event = \local_rlsiteadmin\event\plugin_remove::create($data);
            $event->trigger();
        }

        if (RLSA_MASS_UPDATE_ACTION == $action) {
            $event = \local_rlsiteadmin\event\plugin_update::create($data);
            $event->trigger();
        }
    }
}

/**
 * Takes and array of commands, parses the the command into an array with key 'action' and value of 'plugin'
 * and returns the array.
 * @param array $commands The commands that are written to a file.
 * @return array An array whose key is the command 'action' and value is a comma separated list of plugins.
 */
function local_rlsiteadmin_parse_commands($commands) {
    $result = array();
    foreach ($commands as $command) {
        $pos = strpos($command, ' ');
        // Retrieve the command.
        $cmd = substr($command , 0, $pos);
        // Retrieve the plugin.
        $plugin = substr($command, $pos+1);

        if (RLSA_MASS_ADD_ACTION != $cmd && RLSA_MASS_REMOVE_ACTION != $cmd && RLSA_MASS_UPDATE_ACTION != $cmd) {
            continue;
        }

        if (isset($result[$cmd])) {
            $result[$cmd] = $result[$cmd].', '.$plugin;
        } else {
            $result[$cmd] = $plugin;
        }
    }

    return $result;
}

/**
 * Return the location of the global.ini file.
 * @return string Location of global.ini file.
 */
function local_rlsiteadmin_get_global_ini_file() {
    if (defined('BEHAT_TEST')) {
        $inifile = $CFG->dirroot.'/local/rlsiteadmin/tests/fixtures/global.ini';
    } else {
        $inifile = '/mnt/data/conf/global.ini';
    }
    return $inifile;
}
