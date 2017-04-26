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
 * @copyright 2014 Remote Learner Inc http://www.remote-learner.net
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__).'/../../lib/archive_api.php');

defined('MOODLE_INTERNAL') || die();

class local_rlsiteadmin_archive_api_testcase extends advanced_testcase {
    /** @var object The test version of the archive api */
    private $archiveapi;

    /**
     * Do common setup tasks
     *
     * Reset the cache before each test.
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    public function setUp() {
        parent::setUp();

        $archiveapi = $this->getMockBuilder('local_rlsiteadmin_archive_api')
                    ->disableOriginalConstructor()
                    ->setMethods(array('handle_curl','list_archives','list_restored'))
                    ->getMock();

        $archiveapireflection = new ReflectionClass($archiveapi);
        $settings = $archiveapireflection->getProperty('settings');
        $settings->setAccessible(true);
        $settings->setValue($archiveapi, array(
                'site_id' => 'rlsite',
                'archive_enabled' => true,
                'max_archived_snapshots' => 5,
                'max_restored_snapshots' => 1,
                'manages_alternate_hosts' => false,
                'restore_duration' => 7
            ));

        $this->archiveapi = $archiveapi;

        cache_factory::reset();
        cache_config_phpunittest::create_default_configuration();
    }

    /**
     * Reset the cache after testing to purge testing data.
     */
    public static function tearDownAfterClass() {
        parent::tearDownAfterClass();
        cache_factory::reset();
    }

    /**
     * Test the get_cluster method.
     */
    public function test_get_cluster() {
        // Test 0
        // rl01-vXXXX format
        $expected = "rl01-2";
        $this->assertEquals($expected, $this->archiveapi->get_cluster("rl01-v1111"));
        // Test 1
        // rl01-3-vXXXX format
        $expected = "rl01-3";
        $this->assertEquals($expected, $this->archiveapi->get_cluster("rl01-3-v1111"));
        // Test 2
        // rl02-vXXXX format
        $expected = "rl02";
        $this->assertEquals($expected, $this->archiveapi->get_cluster("rl02-v1111"));
        // Test 3
        // rl03-vXXXX format
        $expected = "rl03";
        $this->assertEquals($expected, $this->archiveapi->get_cluster("rl03-v1111"));
        // Test 4
        // rl04-vXXXX format
        $expected = "rl04";
        $this->assertEquals($expected, $this->archiveapi->get_cluster("rl04-v1111"));
    }

    /**
     * Test the archive_snapshot method.
     */
    public function test_archive_snapshot() {

        // Mock list_archives return.
        $list_archives_return = array(
            'code' => 200,
            'return' => '{ "archives": { "done": [ 111, 222, 333 ], "in-progress": [] }, "host": "test-vm" }'
        );
        $list_archives_return_full = array(
            'code' => 200,
            'return' => '{ "archives": { "done": [ 111, 222, 333, 444, 555 ], "in-progress": [] }, "host": "test-vm" }'
        );
        $this->archiveapi->expects($this->any())
             ->method('list_archives')
             ->will($this->onConsecutiveCalls($list_archives_return, $list_archives_return_full));

        // Mock handle_curl return.
        $archive_snapshot_return = array(
            'code' => 200,
            'return' => '{ "host": "test-vm", "task_id": "ttt" }'
        );
        $this->archiveapi->expects($this->any())
             ->method('handle_curl')
             ->will($this->returnValue(
                $archive_snapshot_return)
            );

        // Test 0
        // Successful archive request.
        $expected = array(
            'code' => 200,
            'return' => '{ "host": "test-vm", "task_id": "ttt" }'
        );
        $this->assertEquals($expected, $this->archiveapi->archive_snapshot(111));

        // Test 1
        // Limit reached.
        //$this->archiveapi->settings['max_archived_snapshots'] = 2;
        $expected = array(
            'code' => 409,
            'return' => '{"error":"Not allowed: archive max limit reached"}'
        );
        $this->assertEquals($expected, $this->archiveapi->archive_snapshot(111));
    }

    /**
     * Test the restore_archive method.
     */
    public function test_restore_archive() {

        // Create the global user.
        global $USER;
        $USER->email = 'foo@bar.com';

        // Mock list_restord return.
        $list_restored_return_empty = array(
            'code' => 200,
            'return' => '{ "host": "test-vm", "restored": { "done": [], "in-progress": [] } }'
        );
        $list_restored_return_notempty = array(
            'code' => 200,
            'return' => '{ "host": "test-vm", "restored": { "done": [ { "id": 111, "url": "https://rl0x-proxy.rlem.net/test-archive-vm-111" }], "in-progress": [] } }'
        );
        $this->archiveapi->expects($this->any())
             ->method('list_restored')
             ->will($this->onConsecutiveCalls($list_restored_return_empty, $list_restored_return_notempty));

        // Mock handle_curl return.
        $restore_archive_return = array(
            'code' => 200,
            'return' => '{ "host": "test-vm", "task_id": "ttt" }'
        );
        $this->archiveapi->expects($this->any())
             ->method('handle_curl')
             ->will($this->returnValue(
                $restore_archive_return)
            );

        // Test 0
        // Successful archive request.
        $expected = array(
            'code' => 200,
            'return' => '{ "host": "test-vm", "task_id": "ttt" }'
        );
        $this->assertEquals($expected, $this->archiveapi->restore_archive(111));

        // Test 1
        // Limit reached.
        $expected = array(
            'code' => 409,
            'return' => '{"error":"Not allowed: restored max limit reached"}'
        );
        $this->assertEquals($expected, $this->archiveapi->restore_archive(111));
    }
}
