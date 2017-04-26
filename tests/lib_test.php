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
 * Remote Learner Update Manager - library test
 *
 * @package   local_rlsiteadmin
 * @copyright 2014 Remote Learner Inc http://www.remote-learner.net
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__).'/../lib.php');

defined('MOODLE_INTERNAL') || die();

class local_rlsiteadmin_lib_testcase extends advanced_testcase {
    /**
     * Test the get_branch_number function
     */
    public function test_get_branch_number() {
        global $CFG;
        $branch = local_rlsiteadmin_get_branch_number();
        $this->assertEquals($CFG->branch, $branch);
    }

    /**
     * Test passing an empty array.
     */
    public function test_local_rlsiteadmin_parse_commands_empty_array() {
        $result = local_rlsiteadmin_parse_commands(array());
        $this->assertEmpty($result, 'Result is not empty');
    }

    /**
     * Data provider with invalid commands
     */
    public function invalid_commands() {
        return array(
                array(
                    array('test1 filter_test1'),
                    array('ad filter_test1'),
                    array('a filter_test1'),
                    array('adds filter_test1'),
                    array('u filter_test1'),
                    array('updates filter_test1'),
                    array('r filter_test1'),
                    array('removes filter_test1'),
                    array('filter_test1, add'),
                    array('add, filter_test2'),
                    array('update, filter_test2'),
                    array('remote, filter_test2')
                ),
        );
    }

    /**
     * Testing invalid commands and formats.
     * @dataProvider invalid_commands
     */
    public function test_local_rlsiteadmin_parse_commands_invalid_cmd($data) {
        $result = local_rlsiteadmin_parse_commands($data);
        $this->assertEmpty($result, 'Result is not empty');
    }

    /**
     * Testing for non empty results using valid commands.
     */
    public function test_local_rlsiteadmin_parse_commands_returns_non_empty_array() {
        $data = array(array('add filter_test1'), array('update filter_test2'), array('remove filter_test3'));

        foreach ($data as $d) {
            $result = local_rlsiteadmin_parse_commands($d);
            $this->assertNotEmpty($result, "Array is empty using result {$d[0]}");
            $this->assertCount(1, $result, "Array size is not equal to 1 using result {$d[0]}");
        }

        // Test passing a single command.
        $data = array('add filter_test1');
        $result = local_rlsiteadmin_parse_commands($data);
        $this->assertArrayHasKey('add', $result, "'add' key not found");
        $this->assertEquals('filter_test1', $result['add'], "'add' key value of filter_test1 not found");

        $data = array('update filter_test2');
        $result = local_rlsiteadmin_parse_commands($data);
        $this->assertArrayHasKey('update', $result, "'update' key not found");
        $this->assertEquals('filter_test2', $result['update'], "'update' key value of filter_test2 not found");

        $data = array('remove filter_test3');
        $result = local_rlsiteadmin_parse_commands($data);
        $this->assertArrayHasKey('remove', $result, "'remove' key not found");
        $this->assertEquals('filter_test3', $result['remove'], "'remove' key value of filter_test3 not found");
    }

    /**
     * Testing passing multiple commands.
     */
    public function test_local_rlsiteadmin_parse_commands_passing_multiple_cmds() {
        $data = array('add filter_test1', 'update filter_test2', 'remove filter_test3');

        $result = local_rlsiteadmin_parse_commands($data);
        $this->assertNotEmpty($result, "Array is empty using result {$data[0]}");
        $this->assertCount(3, $result, "Array size is not equal to 3 using result {$data[0]}");

        $this->assertArrayHasKey('add', $result, "'add' key not found");
        $this->assertEquals('filter_test1', $result['add'], "'add' key value of filter_test1 not found");

        $this->assertArrayHasKey('update', $result, "'update' key not found");
        $this->assertEquals('filter_test2', $result['update'], "'update' key value of filter_test2 not found");

        $this->assertArrayHasKey('remove', $result, "'remove' key not found");
        $this->assertEquals('filter_test3', $result['remove'], "'remove' key value of filter_test3 not found");
    }

    /**
     * Testing passing multiple commands using the same action.
     */
    public function test_local_rlsiteadmin_parse_commands_passing_multiple_cmds_same_action() {
        $data = array('add filter_test1', 'add filter_test2', 'add filter_test3');

        $result = local_rlsiteadmin_parse_commands($data);
        $this->assertNotEmpty($result, "Array is empty using result {$data[0]}");
        $this->assertCount(1, $result, "Array size is not equal to 3 using result {$data[0]}");

        $this->assertArrayHasKey('add', $result, "'add' key not found");
        $this->assertEquals('filter_test1, filter_test2, filter_test3', $result['add'], "'add' key value of 'filter_test1, filter_test2, filter_test3' not found");
    }

    /**
     * Test if can update function for scheduled addon updates.
     */
    public function test_local_rlsiteadmin_mass_task_canupdate() {
        global $CFG;
        $this->resetAfterTest();
        require_once($CFG->dirroot.'/local/rlsiteadmin/classes/mass/task/upgradeaddons.php');
        set_config('local_rlsiteadmin_starthour', 0);
        set_config('local_rlsiteadmin_endhour', 4);
        $time = 1485796611;
        $midnight = usergetmidnight($time);
        $currentime = usergetdate($time);
        $year = $currentime['year'];
        $month = $currentime['mon'];
        $day = $currentime['mday'];
        $hours = $currentime['hours'];
        $min = $currentime['minutes'];
        $sec = $currentime['seconds'];
        $now = make_timestamp($year, $month, $day, $hours, $min, $sec);
        for ($i = 0; $i < 24; $i++) {
            $this->assertTrue(\local_rlsiteadmin\mass\task\upgradeaddons::canupdate($midnight + 3600*$i) == ($i >= 0 && $i <= 4));
        }
    }

    /**
     * Test if correct global ini file is returned.
     */
    public function test_local_rlsiteadmin_get_global_ini_file() {
        global $CFG;
        $this->resetAfterTest();
        require_once($CFG->dirroot.'/local/rlsiteadmin/lib.php');
        $this->assertEquals(local_rlsiteadmin_get_global_ini_file(), '/mnt/data/conf/global.ini');
    }
}
