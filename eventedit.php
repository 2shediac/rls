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
 * @package   local_rlsiteadmin
 * @copyright (c) 2012 Remote Learner Inc http://www.remote-learner.net
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../config.php');
require('lib/form_event.php');

require_login(SITEID);

$PAGE->set_url('/local/rlsiteadmin/schedule.php');
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title(get_string('pluginname', 'local_rlsiteadmin'));
$PAGE->set_pagelayout('popup');

if (!has_capability('moodle/site:config', context_system::instance())) {
    print_error('siteadminonly');
}

$done   = false;
$id     = optional_param('id', 0, PARAM_INT);

$event = $DB->get_record('local_rlsiteadmin_schedule', array('id' => $id));

$defaults = array('id' => $id, 'name' => '', 'scheduleddate' => userdate(time()), 'originaldate' => userdate(time()));
if (!empty($event)) {
    $defaults['name'] = $event->description;
    $defaults['scheduleddate'] = $event->scheduleddate;
    $defaults['originaldate']  = userdate($event->originaldate);
} else {
    $event = new stdClass();
    $event->id = 0;
    $event->eventkey = md5($CFG->wwwroot.microtime());
    $event->log = '';
}

$event->updateddate = time();
$form = new form_event();
$form->set_data($defaults);

$data = $form->get_data();

if (!empty($data) && ($data->action = 'update')) {
    $event->description = $data->name;
    $event->type = $data->type;
    $event->scheduleddate = $data->scheduleddate;
    if ($id !== 0) {
        $DB->update_record('local_rlsiteadmin_schedule', $event);
    } else {
        $event->originaldate = $data->scheduleddate;
        $DB->insert_record('local_rlsiteadmin_schedule', $event);
    }
    $done = true;
} else  if ($form->is_cancelled()) {
    $done = true;
}

print($OUTPUT->header());

if (! $done) {
    $form->display();
} else {
    print('<div class="close"><a href="Javascript:self.close();">'
        . get_string('closewindow') .'</a></div>');
}

print($OUTPUT->footer());
