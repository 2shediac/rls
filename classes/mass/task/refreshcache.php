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

namespace local_rlsiteadmin\mass\task;

/**
 * Scheduled task to refresh the Add-on Manager cache.
 */
class refreshcache extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('mass_task_refreshcache', 'local_rlsiteadmin');
    }

    /**
     * Attempt refresh.
     */
    public function execute() {
        require_once(__DIR__.'/../../../lib.php');
        require_once(__DIR__.'/../../../lib/data_cache.php');
        $cache = new \local_rlsiteadmin_data_cache();
        $data = $cache->get_data('addonlist');
        return true;
    }
}
