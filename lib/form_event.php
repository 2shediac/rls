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
 * Remote Learner Update Manager Schedule
 *
 * @package    local_rlsiteadmin
 * @copyright  2012 Remote Learner Inc http://www.remote-learner.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . '/formslib.php');

/**
 * Form event class for editing an event's date and time
 */
class form_event extends moodleform {
    protected $plugin = 'local_rlsiteadmin';
    /**
     * Define the form
     */
    public function definition() {
        $this->_form->addElement('header', 'eventtime', get_string('updatescheduling', $this->plugin));

        $this->_form->addElement('hidden', 'id');
        $this->_form->addElement('hidden', 'action', 'update');

        $this->_form->addElement('text', 'name', get_string('name', $this->plugin));
        $options = array('update' => get_string('update', $this->plugin));
        $this->_form->addElement('select', 'type', get_string('type', $this->plugin), $options);
        $this->_form->addElement('static', 'originaldate', get_string('defaultdate', $this->plugin));

        $this->_form->addElement('date_time_selector', 'scheduleddate', get_string('newdate', $this->plugin));

        $this->add_action_buttons();
    }
}
