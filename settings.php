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
 * Remote Learner Update Manager - Settings page
 *
 * @package   local_rlsiteadmin
 * @copyright 2012 Remote Learner Inc. http://www.remote-learner.net
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

$block = 'local_rlsiteadmin';

if ($hassiteconfig) {
    $settings = new \admin_settingpage('local_rlsiteadmin', new lang_string('pluginname', 'local_rlsiteadmin'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configcheckbox($block .'_enabled', get_string('enable', $block),
                   get_string('disabledesc', $block), true));

    $settings->add(new admin_setting_configtime($block .'_starthour', $block .'_startmin', get_string('updatestart', $block),
                   get_string('updatestartdesc', $block), array('h' => '0', 'm' => '00')));

    $settings->add(new admin_setting_configtime($block .'_endhour', $block .'_endmin', get_string('updateend', $block),
                   get_string('updateenddesc', $block), array('h' => '4', 'm' => '00')));

    $settings->add(new admin_setting_configcheckbox($block .'_notify_on_success', get_string('notifyonsuccess', $block),
                   get_string('notifyonsuccessdesc', $block), false));

    $admins = get_admins();
    $addresses = array();
    foreach ($admins as $admin) {
        $addresses[] = $admin->email;
    }
    $settings->add(new admin_setting_configtextarea($block .'_recipients', get_string('recipients', $block),
                   get_string('recipientsdesc', $block), implode("\n", $addresses)));

    $settings->add(new admin_setting_heading('moodlestats_heading', get_string('moodlestats_heading', $block), ''));

    $settings->add(new admin_setting_configtext($block.'_activecoursedays', get_string('moodlestats_activecoursedays', $block),
                   get_string('moodlestats_activecoursedaysdesc', $block), 7));

    $settings->add(new admin_setting_configtext($block.'_activeusersdays', get_string('moodlestats_activeusersdays', $block),
                   get_string('moodlestats_activeusersdaysdesc', $block), 7));

    $title = get_string('admin_tools_heading', $block);
    $desc = get_string('admin_tools_desc', $block, $CFG);
    $settings->add(new admin_setting_heading('admin_tools_heading', $title, $desc));

}
