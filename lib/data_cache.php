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
$dir = dirname(__FILE__);
require_once($dir.'/addon_cache_client.php');
require_once($dir.'/xmlrpc_dashboard_client.php');

/**
 * Remote Learner Update Manager - Data Cache class
 *
 * @package    local_rlsiteadmin
 * @copyright  2014 Remote Learner Inc http://www.remote-learner.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_rlsiteadmin_data_cache {
    /** @var object The Moodle cache object */
    protected $cache = null;

    /** @var object The addons data */
    protected $data = array();

    /** @var object The RL addon cache client object */
    protected $rlcache = null;

    /** @var array An array of type => function mappings for data retrieval */
    protected $types = array('addonlist' => 'get_addon_data', 'grouplist' => 'get_group_data');

    /** @var object The XMLRPC client object */
    protected $xmlrpc = null;

    /**
     * Constructor
     */
    public function __construct() {
        global $CFG;
        $this->addonsettings = json_decode(get_config('local_rlsiteadmin', 'plugins_upgrademethod'), true);
        if (empty($this->addonsettings)) {
            $this->addonsettings = [];
        }
        $this->cache = cache::make('local_rlsiteadmin', 'addondata');
        $this->rlcache = new local_rlsiteadmin_addon_cache_client();
        $this->xmlrpc = new local_rlsiteadmin_xmlrpc_dashboard_client();
    }

    /**
     * Get data from the cache (fetching it if it's missing)
     *
     * The returned array is an array with timestamp and data entries.
     *
     * @param string $type The type of data to fetch
     * @return array|bool The data from the cache.  False on failure to get data.
     */
    public function get_data($type) {

        if (!array_key_exists($type, $this->types)) {
            return false;
        }

        $this->data = $this->cache->get($type);

        // Fetch new data if we don't have it or it's more than 23 hours old.
        if (($this->data === false) || ($this->data['timestamp'] < (time() - 82800))) {
            $this->data = array('result' => 'fail');
            $this->get_xmlrpc_data($type);
            $this->get_moodle_data($type);
            $this->get_rlcache_data($type);

            if ($this->data['result'] == 'OK') {
                $this->cache->set($type, $this->data);
            }
        }

        return $this->data;
    }

    /**
     * Set upgrade settings for an addon.
     *
     * @param object $addon Addon definition.
     * @return object Addon with updated settings.
     */
    public function setupgradesettings($addon) {
        global $CFG;
        // Not a plugin addon.
        if (empty($addon['type']) || empty($addon['name'])) {
            return $addon;
        }
        $plugin = $addon['type'].'_'.$addon['name'];
        // Check if plugin upgrade settings can be changed.
        $addon['upgrademethodchangeable'] = true;
        $addon['upgrademethod'] = 'manual';
        if (!empty($this->addonsettings[$plugin])) {
            $addon['upgrademethod'] = $this->addonsettings[$plugin];
        }
        // Force plugin upgrade to manual only.
        if (!empty($CFG->local_rlsiteadmin_autoupgradeplugins)) {
            $autoupgradeplugins = explode(",", $CFG->local_rlsiteadmin_autoupgradeplugins);
            // Force plugin ugprade to automatic only ie (rlsiteadmin, rlauth).
            if (in_array($plugin, $autoupgradeplugins)) {
                $addon['upgrademethod'] = 'auto';
                $addon['upgrademethodchangeable'] = false;
            }
        }
        if (!empty($CFG->local_rlsiteadmin_manualupgradeplugins)) {
            $manualupgradeplugins = explode(",", $CFG->local_rlsiteadmin_manualupgradeplugins);
            if (in_array($plugin, $manualupgradeplugins)) {
                $addon['upgrademethod'] = 'manual';
                $addon['upgrademethodchangeable'] = false;
            }
        }
        return $addon;
    }

    /**
     * Get data from Moodle
     *
     * We need $this->data to pre-populated with target addons.
     *
     * @param string $type The type of data to fetch
     */
    protected function get_moodle_data($type) {
        global $DB, $USER, $CFG;

        $this->addonsettings = json_decode(get_config('local_rlsiteadmin', 'plugins_upgrademethod'), true);
        switch ($type) {
            case 'addonlist':
                $this->addonsettings = json_decode(get_config('local_rlsiteadmin', 'plugins_upgrademethod'), true);
                $list = core_plugin_manager::instance()->get_plugins();
                // Prevent warnings when data is not retrieved.
                if (!empty($this->data['data']['result']) && $this->data['data']['result'] == 'Failed') {
                    return;
                }
                foreach ($this->data['data'] as $key => $addon) {
                    if (empty($addon['type']) || empty($addon['name'])) {
                        continue;
                    }
                    $type = $addon['type'];
                    $name = $addon['name'];
                    $addon['installed'] = false;
                    $addon['locked'] = false;
                    $addon['versiondisk'] = 0;
                    $addon['versiondb'] = 0;
                    $addon['commitid'] = 0;
                    $addon['dependencies'] = array();
                    $addon['release'] = '';
                    $addon['myrating'] = 0;
                    $addon['missing'] = false;
                    $addon = $this->setupgradesettings($addon);
                    if (array_key_exists($type, $list) && array_key_exists($name, $list[$type])) {
                        $moodle = $list[$type][$name];
                        $addon['installed'] = true;
                        $addon['missing'] = true;
                        if ($moodle->versiondisk != null) {
                            $addon['missing'] = false;
                            $addon['versiondisk'] = $moodle->versiondisk;
                        }
                        $addon['versiondb'] = $moodle->versiondb;
                        // Get the git commit hash of the currently installed plugin to check against the commit hash
                        // of the cached repo.
                        $path = $CFG->dirroot.'/'.$addon['path'].'/';
                        if (file_exists($path.'.git')) {
                            $current = getcwd();
                            if (chdir($path)) {
                                $commitid = array();
                                exec("git rev-parse --verify HEAD", $commitid);
                                $addon['commitid'] = $commitid[0];
                                chdir($current);
                            }
                        } else if (file_exists($path)) {
                            // If the directory exists and there is no .git folder, set locked status
                            $addon['locked'] = true;
                        }
                        if (!empty($moodle->dependencies)) {
                            $addon['dependencies'] = $moodle->dependencies;
                        }
                        if (!empty($moodle->release)) {
                            $addon['release'] = $moodle->release;
                        }
                    }
                    $this->data['data'][$key] = $addon;
                }
                $ratings = $DB->get_records('local_rlsiteadmin_rating', array ('userid' => $USER->id));
                foreach ($ratings as $rating) {
                    if (array_key_exists($rating->plugin, $this->data['data'])) {
                        $this->data['data'][$rating->plugin]['myrating'] = $rating->rating;
                    }
                }
                break;
            default:
                break;
        }
    }

    /**
     * Get data from the rlcache client
     *
     * We need $this->data to pre-populated with target addons.
     *
     * @param string $type The type of data to fetch
     */
    protected function get_rlcache_data($type) {
        switch ($type) {
            case 'addonlist':
                $this->addonsettings = json_decode(get_config('local_rlsiteadmin', 'plugins_upgrademethod'), true);
                $software = local_rlsiteadmin_get_ini_value('deliverable_software', 'deliverables');
                $elis = false;
                if (strtolower($software) == 'elis') {
                    $elis = true;
                }

                $pay = array();
                // Turtles all the way down.
                $cache = new local_rlsiteadmin_data_cache();
                $groups = $cache->get_data('grouplist');
                unset($cache);
                if ($elis !== true && is_array($groups) && isset($groups['data']) && is_array($groups['data'])
                        && isset($groups['data']['elis']) && is_array($groups['data']['elis'])) {
                    $pay = array_fill_keys($groups['data']['elis']['plugins'], 1);
                }
                $curbranch = local_rlsiteadmin_get_branch_number();
                $numupgrades = 0;
                // Prevent warnings when data is not retrieved.
                if (!empty($this->data['data']['result']) && $this->data['data']['result'] == 'Failed') {
                    return;
                }
                foreach ($this->data['data'] as $name => $addon) {
                    if (!is_array($addon)) {
                        continue;
                    }
                    $cached = $this->rlcache->get_addon_data($name);
                    $addon['cached'] = false;
                    $addon['upgradeable'] = false;
                    $addon['cache'] = array();
                    $addon['paid'] = false;
                    $addon['curbranch'] = $curbranch;
                    if (!empty($cached->commitid)) {
                        $addon['cached'] = true;
                        $addon['cache']['dependencies'] = array();
                        if (!empty($cached->dependencies)) {
                            $addon['cache']['dependencies'] = $cached->dependencies;
                        }
                        $addon['cache']['version'] = $cached->version;
                        $addon['cache']['commitid'] = $cached->commitid;
                        if ($addon['installed'] && !$addon['missing'] &&  ($addon['commitid'] !== 0) &&
                                ($addon['cache']['commitid'] !== $addon['commitid'])) {
                            $current = getcwd();
                            if (chdir($cached->path)) {
                                $curcommit = $addon['commitid'];
                                $cachecommit = $addon['cache']['commitid'];
                                $mergebase = array();
                                exec("git merge-base {$curcommit} {$cachecommit}", $mergebase);
                                if (array_key_exists(0, $mergebase) && ($mergebase[0] == $curcommit)) {
                                    $addon['upgradeable'] = true;
                                    $numupgrades++;
                                }
                                chdir($current);
                            }
                        } else if (($addon['installed'] == true) && ($addon['versiondisk'] < $cached->version)) {
                            $addon['upgradeable'] = true;
                            $numupgrades++;
                        }
                    }
                    if (array_key_exists($name, $pay)) {
                        $addon['paid'] = true;
                    }
                    // Fix moodleversions before sending to javascript.
                    $addon['moodleversions'] = $this->fix_moodleversions_array($addon['moodleversions'], true);
                    $addon = $this->setupgradesettings($addon);
                    $this->data['data'][$name] = $addon;
                    $this->data['upgrades'] = (!empty($numupgrades)) ? true : false;
                    $this->data['numupgrades'] = $numupgrades;
                }
                break;
            default:
                break;
        }
    }

    /**
     * Get data from the xmlrpc client
     *
     * We will overwrite $this->data with data from the Dashboard
     *
     * @param string $type The type of data to fetch
     */
    protected function get_xmlrpc_data($type) {
        $method = $this->types[$type];
        $response = $this->xmlrpc->$method();
        if (is_array($response)) {
            if (array_key_exists(0, $response) && array_key_exists('result', $response[0])) {
                // Future improved format
                $this->data = ksort($response[0]);
                if ($type == 'addonlist') {
                    $this->addonsettings = json_decode(get_config('local_rlsiteadmin', 'plugins_upgrademethod'), true);
                    foreach ($this->data['data'] as $name => $addon) {
                        $this->data['data'][$name] = $this->setupgradesettings($addon);
                    }
                }
            } else {
                // Current broken format
                $this->data = array('result' => 'OK', 'data' => $response);
            }
        } else {
            $this->data['result'] = 'FAILED';
            $this->data['data'] = array();
        }
        $this->data['timestamp'] = time();
    }

    /**
     * Update the cache data
     *
     * @param string $type The type of data to set
     * @param array $data The data to set in the cache
     */
    public function update_data($type, $data) {
        if ($type == 'addonlist') {
            $this->addonsettings = json_decode(get_config('local_rlsiteadmin', 'plugins_upgrademethod'), true);
            foreach ($data['data'] as $name => $addon) {
                $data['data'][$name] = $this->setupgradesettings($addon);
            }
        }
        $this->cache->set($type, $data);
    }

    /**
     * Massage the moodleversions string into an array that is simple to parse and only has
     * future versions or may also contain current version if specified.
     *
     * @param string $moodleversions A delimited string of Moodle versions and whether this plugin is available
     * @param bool $includecurrent optionally return availability of current branch version (default false).
     * @return array
     */
    protected function fix_moodleversions_array($moodleversions, $includecurrent = false) {
        // Get the current version of this site.
        $branchnum = local_rlsiteadmin_get_branch_number();

        // Explode versions string on comma.
        $versionsarray = explode(",", $moodleversions);
        $hashedversions = array();
        foreach ($versionsarray as $key => $value) {
            // Explode version and value on colon.
            $versions = explode(":", $value);
            if ($versions[0] > $branchnum || ($includecurrent && $versions[0] == $branchnum)) {
                $hashedversions[$versions[0]] = $versions[1];
            }
        }
        return $hashedversions;
    }
}
