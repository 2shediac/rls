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
 * RL Agent plugin_update event.
 *
 * @package    local_rlsiteadmin
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2015 Remote Learner.net Inc http://www.remote-learner.net
 */
namespace local_rlsiteadmin\event;

defined('MOODLE_INTERNAL') || die();

class plugin_update_auto extends \core\event\base {
    /**
     * This function is overridden from the parent class.
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->context = \context_system::instance();
    }

    /**
     * This function is overridden from the parent class.
     */
    public static function get_name() {
        return get_string('event_plugin_update_auto', 'local_rlsiteadmin');
    }

    /**
     * This function is overridden from the parent class.
     */
    public function get_description() {
        return "The user with id {$this->userid} updated the following plugin(s) {$this->other['plugins']}.";
    }
}
