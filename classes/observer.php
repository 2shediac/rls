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
 * RL Agent observer class
 *
 * @package    local_rlsiteadmin
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2015 Remote Learner.net Inc http://www.remote-learner.net
 */

class local_rlsiteadmin_observer {
    /**
     * Funciton to write to the Add-on Manager log.
     * @param object $event Event passed by core.
     * @param string $action Action performed.
     * @return void.
     */
    public static function write_to_log($event, $action) {
        global $CFG;

        // Create log directories, if they've gone missing.
        local_rlsiteadmin_create_directories();

        // Construct a row for the log.
        $data = $event->get_data();
        $plugins = explode(',', $data['other']['plugins']);
        $row = array($data['other']['fullname'], date('F j, Y, g:i a', $data['timecreated']), $action);

        $fp = fopen($CFG->dataroot.'/manager/addons/mass_log.csv', 'a');

        foreach ($plugins as $plugin) {
            $plugin = trim($plugin);
            array_push($row, $plugin);
            fputcsv($fp, $row);
            array_pop($row);
        }

        fclose($fp);
    }
    /**
     * Observer for when Add-on Manager adds plugin to the install queue.
     *
     * @param event $event Event passed by core
     * @return void
     */
    public static function plugin_install($event) {
        self::write_to_log($event, RLSA_MASS_ADD_ACTION);
    }

    /**
     * Observer for when Add-on Manager adds plugin to the remove queue.
     *
     * @param event $event Event passed by core
     * @return void
     */
    public static function plugin_remove($event) {
        self::write_to_log($event, RLSA_MASS_REMOVE_ACTION);
    }

    /**
     * Observer for when Add-on Manager adds plugin to the update queue
     *
     * @param event $event Event passed by core
     * @return void
     */
    public static function plugin_update($event) {
        self::write_to_log($event, RLSA_MASS_UPDATE_ACTION);
    }
}
