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
require_once(dirname(__FILE__).'/../../lib/data_cache.php');

defined('MOODLE_INTERNAL') || die();

class local_rlsiteadmin_data_cache_testcase extends advanced_testcase {
    /** @var object The test version of the dasta cache */
    private $cache;
    /**
     * Do common setup tasks
     *
     * Reset the cache before each test.
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    public function setUp() {
        parent::setUp();
        $cache = $this->getMockBuilder('local_rlsiteadmin_data_cache')
                      ->setMethods(array('get_moodle_data', 'get_xmlrpc_data', 'get_rlcache_data'))
                      ->getMock();
        $cache->expects($this->any())
               ->method('get_moodle_data')
               ->will($this->returnValue(true));
        $cache->expects($this->any())
              ->method('get_xmlrpc_data')
              ->will($this->returnValue(true));
        $cache->expects($this->any())
              ->method('get_rlcache_data')
              ->will($this->returnValue(true));

        $this->cache = $cache;

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
     * Test the get_addon_data method
     */
    public function test_get_data() {
        $expected = array('result' => 'fail');

        $result = $this->cache->get_data('addonlist');
        $this->assertEquals($expected, $result);

        $result = $this->cache->get_data('grouplist');
        $this->assertEquals($expected, $result);

        $result = $this->cache->get_data('shoelist');
        $this->assertEquals(false, $result);
    }
}
