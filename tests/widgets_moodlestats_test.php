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
 * Remote Learner Update Manager - Data cache test
 *
 * @package   local_rlsiteadmin
 * @copyright 2015 Remote Learner Inc http://www.remote-learner.net
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__).'/../widgets/moodlestats/classes/widget.php');

defined('MOODLE_INTERNAL') || die();

class local_rlsiteadmin_widgets_moodlestats extends advanced_testcase {
     /**
     * Do common setup tasks
     *
     * Reset the cache before each test.
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    public function setUp() {
        global $CFG, $DB;
        $this->resetAfterTest();
        $user1 = $this->getDataGenerator()->create_user(array('email'=>'user1@example.com', 'username'=>'user1'));
        $user2 = $this->getDataGenerator()->create_user(array('email'=>'user2@example.com', 'username'=>'user2'));
        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        set_config('local_rlsiteadmin_activecoursedays', 7);
        set_config('local_rlsiteadmin_activeusersdays', 7);
        set_config('enabled_stores', 'logstore_standard' ,'tool_log');
        $coursecontext = context_course::instance($course1->id);
        $log = new stdClass();
        $log->courseid = $course1->id;
        // Two days ago.
        $log->timecreated = time() - 172800;
        $log->eventname = 'test';
        $log->component = 'quiz';
        $log->action = 'c';
        $log->target = 'target';
        $log->edulevel = 1;
        $log->contextid = $coursecontext->id;
        $log->contextinstanceid = $coursecontext->id;
        $log->contextlevel = CONTEXT_MODULE;
        $log->userid = $user1->id;
        $DB->insert_record('logstore_standard_log', $log);
        // Ten days ago.
        $log->timecreated = time() - 864000;
        $log->courseid = $course2->id;
        $DB->insert_record('logstore_standard_log', $log);

        $log = new stdClass();
        $log->time = time() - 172800;
        $log->userid = $user1->id;
        $log->ip = '127.0.0.1';
        $log->course = $course1->id;
        $log->module = 'module';
        $log->cmid = $coursecontext->id;
        $log->action = 'c';
        $log->url = 'testurl';
        $log->info = 'extra info';
        $DB->insert_record('log', $log);

        $log->time = time() - 864000;
        $log->course = $course2->id;
        $DB->insert_record('log', $log);
    }

    /**
     * Test stats for users.
     */
    public function test_moodlestats_users() {
        global $DB;
        $block = new \rlsiteadminwidget_moodlestats\widget();
        $stats = $block->get_moodlestats();
        $this->assertEquals(0, $stats['activeusers']);
        $user = $DB->get_record('user', array('username' => 'user1'));
        $user->lastaccess = time();
        $DB->update_record('user', $user);
        $stats = $block->get_moodlestats();
        $this->assertEquals(1, $stats['activeusers']);
        $user = $DB->get_record('user', array('username' => 'user2'));
        $user->lastaccess = time() - 259200;
        $DB->update_record('user', $user);
        $stats = $block->get_moodlestats();
        $this->assertEquals(2, $stats['activeusers']);
        set_config('local_rlsiteadmin_activeusersdays', 1);
        $stats = $block->get_moodlestats();
        $this->assertEquals(1, $stats['activeusers']);
        $this->assertEquals(3, $stats['totalusers']);
    }

    /**
     * Test stats for courses.
     */
    public function test_moodlestats_courses() {
        global $DB;
        $block = new \rlsiteadminwidget_moodlestats\widget();
        $stats = $block->get_moodlestats();
        $this->assertEquals(1, $stats['activecourses']);
        set_config('local_rlsiteadmin_activecoursedays', 1);
        $stats = $block->get_moodlestats();
        $this->assertEquals(0, $stats['activecourses']);
        set_config('local_rlsiteadmin_activecoursedays', 30);
        $stats = $block->get_moodlestats();
        $this->assertEquals(2, $stats['activecourses']);
        set_config('enabled_stores', '' ,'tool_log');
        $stats = $block->get_moodlestats();
        $this->assertEquals(null, $stats['activecourses']);
        // Test legacy log.
        set_config('enabled_stores', 'logstore_legacy' ,'tool_log');
        set_config('loglegacy', 1, 'logstore_legacy');
        set_config('local_rlsiteadmin_activecoursedays', 7);
        $stats = $block->get_moodlestats();
        $this->assertEquals(1, $stats['activecourses']);
        set_config('local_rlsiteadmin_activecoursedays', 1);
        $stats = $block->get_moodlestats();
        $this->assertEquals(0, $stats['activecourses']);
        set_config('local_rlsiteadmin_activecoursedays', 30);
        $stats = $block->get_moodlestats();
        $this->assertEquals(2, $stats['activecourses']);
        $this->assertEquals(2, $stats['totalcourses']);
    }

    /**
     * Test get html.
     */
    public function test_moodlestats_get_html() {
        global $CFG;
        $block = new \rlsiteadminwidget_moodlestats\widget();
        $html = $block->get_html();
        // Release has () + which don't play nice with preg_match expressions.
        $this->assertTrue(strpos($html, $CFG->release) > 0);
        $this->assertEquals(1, preg_match('/<th>Total courses<\/th>/', $html));
        // Matches the stat for total users of 3.
        $this->assertEquals(1, preg_match('/>3</', $html));
    }
}
