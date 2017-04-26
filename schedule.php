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

require_once('../../config.php');
require_once($CFG->libdir .'/tablelib.php');
require_once(dirname(__FILE__) .'/lib/table_schedule.php');

require_login(SITEID);

$pluginname = get_string('pluginname', 'local_rlsiteadmin');
$pagetitle  = get_string('scheduledevents', 'local_rlsiteadmin');

$PAGE->set_url('/local/rlsiteadmin/schedule.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title($pluginname);
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add($pluginname);
$PAGE->navbar->add($pagetitle);

if (!has_capability('moodle/site:config', context_system::instance())) {
    print_error('siteadminonly');
}

$fields  = 'id, scheduleddate, originaldate, description, status, log, updateddate';
$from    = $CFG->prefix.'local_rlsiteadmin_schedule';
$columns = array('scheduleddate', 'originaldate', 'description', 'status', 'log', 'updateddate');
$headers = array('Scheduled Date', 'Original Date', 'Description', 'Status', 'Log', 'Last Updated');

$table = new table_schedule('scheduled_update_table');
$table->set_sql($fields, $from, 'true');
$table->define_baseurl($CFG->wwwroot .'/local/rlsiteadmin/schedule.php');
$table->define_columns($columns);
$table->sortable(true, 'scheduleddate', SORT_DESC);

$editurl = new moodle_url('/local/rlsiteadmin/eventedit.php');
$action = new popup_action('click', $editurl, 'change', array('height' => 400, 'width' => 450));
$linktext = get_string('schedule_new_event', 'local_rlsiteadmin');

print($OUTPUT->header($pagetitle));
print($OUTPUT->heading(get_string('scheduledevents', 'local_rlsiteadmin')));
$table->out(10, false);
// NOTE: Temporarily disabled.  Uncomment to allow self-service updates.
// print('<span class="pull-right">'.$OUTPUT->action_link($editurl, $linktext, $action, array('title' => $linktext)).'</span>');
print($OUTPUT->footer());
