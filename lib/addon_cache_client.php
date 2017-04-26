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
 * Remote Learner Update Manager - Local addon cache client
 *
 * @package    local_rlsiteadmin
 * @copyright  2014 Remote Learner Inc http://www.remote-learner.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_rlsiteadmin_addon_cache_client {
    /** @var string The directory where addon reference copies are stored. */
    protected $referencedir = '/mnt/cluster/git/moodle_addons';

    /**
     *
     */
    public function __construct() {
        global $CFG;

        if (!empty($CFG->local_rlsiteadmin_mass_referencedir)) {
            $this->referencedir = $CFG->local_rlsiteadmin_mass_referencedir;
        }
    }

    /**
     * Check if the version file exists
     *
     * @param string $branch The branch to check.
     * @return bool True when exists.
     */
    protected function check_version_file_exists($branch) {
        $notexists = true;
        $output = '';
        // Check if the reference repository has a version file (tinymce plugins don't).
        exec("git rev-parse --verify {$branch}", $output, $notexists);
        return !$notexists;
    }

    /**
     * Get addon data from the cache
     *
     * Note: Not unit testable because of exec calls
     *
     * @param string $addon The name of the addon to get data from
     */
    public function get_addon_data($addon) {
        $branchnum = local_rlsiteadmin_get_branch_number();
        $branch = "MOODLE_{$branchnum}_STABLE";
        $path = "{$this->referencedir}/moodle-{$addon}";

        // Check if the reference directory exists.
        if (file_exists($path)) {
            $current = getcwd();

            if (chdir($path)) {
                $origin = '';
                if (!$this->is_bare($path)) {
                    // If it's not-bare we need to look at the version file from the remote repository because the local
                    // version could be modified or badly out-of-date.
                    $origin = 'origin/';
                }

                if ($this->check_version_file_exists("{$origin}{$branch}")) {
                    $plugin = $this->get_plugin_info($origin, $branch);
                }
                chdir($current);
            }
        }

        if (!empty($plugin->version)) {
            $plugin->path = $path;
            return $plugin;
        }

        // Nothing found?
        return new stdClass();
    }

    /**
     * Check if a repository is bare
     *
     * @param string $origin The origin for the branch
     * @param string $branch The branch name
     * @return object A plugin information object.
     */
    protected function get_plugin_info($origin, $branch) {
        $output = '';
        $file = array();
        $plugin = new stdClass();

        exec("git cat-file blob {$origin}{$branch}:version.php", $file);
        $code = escapeshellarg(str_ireplace('<?php', '', implode("\n", $file)));
        $dir = dirname(__FILE__);
        $status = 0;
        exec("php $dir/quarantine.php $code", $output, $status);
        if (!$status && !empty($output)) {
            foreach ($output as $line) {
                if (preg_match('/^{"/', $line)) {
                    $plugin = json_decode($line);
                    break;
                }
            }
            // Get the git commit hash of the cached repo to check against the commit hash of the currently
            // installed plugin.
            $commitid = array();
            exec("git rev-parse --verify {$branch}", $commitid);
            if (!empty($plugin->version)) {
                $plugin->commitid = $commitid[0];
            }
        }
        return $plugin;
    }

    /**
     * Check if a repository is bare
     *
     * @return bool True when bare.
     */
    protected function is_bare() {
        $bare = true;
        $output = '';
        exec("git rev-parse --is-bare-repository | grep false", $output, $bare);
        return $bare;
    }
}
