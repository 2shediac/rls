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

namespace local_rlsiteadmin\mass\task;

/**
 * Scheduled task to refresh the Add-on Manager cache.
 */
class upgradeaddons extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('mass_task_upgradeaddons', 'local_rlsiteadmin');
    }


    /**
     * Detimine updates can happen automatically.
     *
     * @param $time Optional parameter for unix timestamp to check.
     * @return True if can execute automate updates.
     */
    public static function canupdate($time = null) {
        global $CFG;
        // Ensure updates only happen during scheduled time.
        $starthour = $CFG->local_rlsiteadmin_starthour;
        $endhour = $CFG->local_rlsiteadmin_endhour;
        if ($time == null) {
            $time = time();
        }
        $midnight = usergetmidnight($time);
        $currentime = usergetdate($time);
        $year = $currentime['year'];
        $month = $currentime['mon'];
        $day = $currentime['mday'];
        $hours = $currentime['hours'];
        $min = $currentime['minutes'];
        $sec = $currentime['seconds'];
        $now = make_timestamp($year, $month, $day, $hours, $min, $sec);
        if (!(($midnight + 3600 * $starthour) <= $now && ($midnight + 3600 * $endhour) >= $now)) {
            return false;
        }
        return true;
    }

    /**
     * Attempt refresh.
     */
    public function execute() {
        global $CFG;

        require_once(__DIR__.'/../../../lib.php');
        require_once(__DIR__.'/../../../lib/data_cache.php');
        $cache = new \local_rlsiteadmin_data_cache();
        $data = $cache->get_data('addonlist');
        $addonsettings = json_decode(get_config('local_rlsiteadmin', 'plugins_upgrademethod'), true);
        if (empty($addonsettings['updates'])) {
            $addonsettings['updates'] = [];
        }

        // Check if auto updates can be done.
        if (!defined('BEHAT_TEST') && !self::canupdate()) {
            $start = userdate($midnight + 3600 * $starthour);
            $end = userdate($midnight + 3600 * $endhour);
            mtrace("Allowed updates are from {$start} to {$end}\n");
            return true;
        }

        $contents = ["site $CFG->dirroot"];
        $time = time() - 86400;
        $plugins = [];
        foreach ($data['data'] as $name => $addon) {
            $addon['upgradeable'] = 1;
            if ($addon['upgradeable']) {
                // Check if update method is set to auto. A empty value and manual is manual upgrade.
                if (!empty($addonsettings[$name]) && $addonsettings[$name] == 'auto') {
                    // Upgrade plugin by sending mass command.
                    // Prevent from second command from being sent.
                    if (!empty($addonsettings['updates'][$name]) && $addonsettings['updates'][$name] > $time) {
                        continue;
                    }
                    $plugins[] = $addon['name'];
                    $contents[] = "update $name";
                    $addonsettings['updates'][$name] = time();
                }
            }
        }

        // Save settings.
        set_config('plugins_upgrademethod', json_encode($addonsettings), 'local_rlsiteadmin');

        if (count($contents) > 1) {
            // If behat test change dispatch directory.
            if (defined('BEHAT_TEST')) {
                $massdir = $CFG->behat_dataroot.'/temp';
            } else {
                $massdir = '/var/run/mass';
            }
            $command = local_rlsiteadmin_write_incron_commands($contents, 'addon_', $massdir);
            // Make log entry for upgrade.
            $data = [
                'other' => [
                    'plugins' => $plugins,
                ]
            ];
            $event = \local_rlsiteadmin\event\plugin_update_auto::create($data);
            $event->trigger();

            $emails = explode("\n", $CFG->local_rlsiteadmin_recipients);
            $users = $this->get_email_users($emails);

            $data = new \stdClass();
            $data->wwwroot = $CFG->wwwroot;
            $data->plugins = '';

            foreach ($plugins as $plugin) {
                $data->plugins .= $plugin;
            }

            $subject = get_string('email_plugin_update_subject', 'local_rlsiteadmin', $data);
            $message = get_string('email_plugin_update_body', 'local_rlsiteadmin', $data);

            foreach ($users as $user) {
                ob_start();
                if (!defined('BEHAT_TEST')) {
                    email_to_user($user, 'RL Update Manager', $subject, $message, '');
                }
                ob_end_flush();
            }
        }
        return true;
    }

    /**
     * Get or make the user objects for the provided email addresses
     */
    protected function get_email_users($emails) {
        global $DB;

        $found = array();

        foreach ($emails as $key => $email) {
            $email = trim($email);
            $emails[$key] = $email;
            $found[$email] = false;
        }

        $users  = $DB->get_records_list('user', 'email', $emails);

        foreach ($users as $user) {
            $found[$user->email] = true;
        }

        foreach ($found as $email => $exist) {

            if (!$exist) {
                $user = new \stdClass();
                $user->id    = 0;
                $user->email = $email;
                $users[] = $user;
            }
        }

        return $users;
    }
}
