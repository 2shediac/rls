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
 * @package local_rlsiteadmin
 * @copyright 2016 onwards Remote-Learner Inc. (http://www.remote-learner.net)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rlsiteadmin\task;

/**
 * Scheduled task to process schedule.
 */
class processschedule extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('task_processschedule', 'local_rlsiteadmin');
    }

    /**
     * Do the job.
     */
    public function execute() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/lib/tablelib.php');
        require_once($CFG->dirroot.'/local/rlsiteadmin/lib/table_schedule.php');

        // Mark old updates as Skipped.
        $query = 'UPDATE {local_rlsiteadmin_schedule}
                     SET notification = '.\table_schedule::NOT_SENT.'
                   WHERE status != '.\table_schedule::NOT_STARTED.'
                         AND notification = '.\table_schedule::READY.'
                         AND rundate < '.(time() - 3600);
        $DB->execute($query);

        $select = 'status NOT IN ('.\table_schedule::NOT_STARTED .', '.\table_schedule::IN_PROGRESS .')'
                .' AND notification='.\table_schedule::READY;
        $records = $DB->get_records_select('local_rlsiteadmin_schedule', $select);

        // There should usually only be one update.
        foreach ($records as $record) {

            if (($record->status == \table_schedule::COMPLETED) && empty($CFG->local_rlsiteadmin_notify_on_success)) {
                $record->notification = \table_schedule::NOT_SENT;
                $record->log .= "\nNotify on success disabled.  Email not sent.";

            } else {

                $data = new stdclass;
                $data->www = $CFG->wwwroot;
                $data->log = $record->log;

                if ($record->status == \table_schedule::ERROR) {
                    if (! empty($CFG->local_rlsiteadmin_error)) {
                        $data->log = get_string($CFG->local_rlsiteadmin_error, $this->blockname);
                    }
                }

                $messages = array(
                    \table_schedule::COMPLETED => 'completed',
                    \table_schedule::ERROR     => 'error',
                    \table_schedule::SKIPPED   => 'skipped',
                );

                $subject = get_string('email_sub_'.  $messages[$record->status], $this->blockname);
                $message = get_string('email_text_'. $messages[$record->status], $this->blockname, $data);
                $html    = get_string('email_html_'. $messages[$record->status], $this->blockname, $data);

                $emails = explode("\n", $CFG->local_rlsiteadmin_recipients);

                $users = $this->get_email_users($emails);

                foreach ($users as $user) {
                    ob_start();
                    $log = $user->email .' at '. userdate(time());
                    if (email_to_user($user, 'RL Update Manager', $subject, $message, $html)) {
                        $log = "\nEmail sent to $log.";
                    } else {
                        $log = "\nFailed to send email to $log:\n". ob_get_contents();
                    }
                    $record->log .= $log;
                    ob_end_flush();
                }

                $record->notification = \table_schedule::SENT;

                if (empty($users)) {
                    $record->notification = \table_schedule::NOT_SENT;
                    $record->log .= "\nNo valid email addresses configured.  Email not sent.";
                }

            }

            $DB->update_record('local_rlsiteadmin_schedule', $record);
        }
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

            if (! $exist) {
                $user = new stdClass();
                $user->id    = 0;
                $user->email = $email;
                $users[] = $user;
            }
        }

        return $users;
    }
}
