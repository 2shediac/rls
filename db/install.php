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

require_once(dirname(__FILE__).'/../lib.php');

/**
 * Post install function for RL site admin.
 *
 * @package local_rlsiteadmin
 * @copyright 2016 onwards Remote-Learner Inc. (http://www.remote-learner.net)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function xmldb_local_rlsiteadmin_install() {
    global $DB, $CFG;

    $context = context_course::instance(SITEID);

    local_rlsiteadmin_create_directories();

    $dbman = $DB->get_manager();

    $table = new xmldb_table('user');
    // Define index on username (non-unique) to be added to user table.
    $index = new xmldb_index('username', XMLDB_INDEX_NOTUNIQUE, array('username'));
    // Conditionally launch add index behaviour.
    if (!$dbman->index_exists($table, $index)) {
        $dbman->add_index($table, $index);
    }

    // Add index to prevent full table scans and associated performance problems per HOSSUP-5245
    $table = new xmldb_table('message_read');
    $index = new xmldb_index('mdl_message_read_tmp_idx', XMLDB_INDEX_NOTUNIQUE, array('notification', 'timeread'));
    if (!$dbman->index_exists($table, $index)) {
        $dbman->add_index($table, $index);
    }

    // Switch the order of the fields in the files_reference index, to improve the performance of search_references.
    $table = new xmldb_table('files_reference');
    $index = new xmldb_index('uq_external_file', XMLDB_INDEX_UNIQUE, array('repositoryid', 'referencehash'));
    if ($dbman->index_exists($table, $index)) {
        $dbman->drop_index($table, $index);
    }

    $table = new xmldb_table('files_reference');
    $index = new xmldb_index('uq_external_file', XMLDB_INDEX_UNIQUE, array('referencehash', 'repositoryid'));
    if (!$dbman->index_exists($table, $index)) {
        $dbman->add_index($table, $index);
    }

    // Migrate settings.
    $settings = [
        'activecoursedays',
        'activeusersdays',
        'enabled',
        'endhour',
        'endmin',
        'notify_on_success',
        'recipients',
        'starthour',
        'startmin',
    ];
    foreach ($settings as $setting) {
        $oldkey = 'block_rlagent_'.$setting;
        $newkey = 'local_rlsiteadmin_'.$setting;
        if (isset($CFG->$oldkey)) {
            set_config($newkey, $CFG->$oldkey);
        }
    }
}
