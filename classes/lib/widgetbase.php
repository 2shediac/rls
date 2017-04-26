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
 * Remote Learner Dashboard - Dashboard Widget Base
 *
 * @package   local_rlsiteadmin
 * @copyright 2015 Remote Learner Inc http://www.remote-learner.net
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rlsiteadmin\lib;

/**
 * An abstract base class implementing some common functionality for dashboard widgets.
 */
abstract class widgetbase implements widgetinterface {
    /** @var array Array of settings from the block instance. */
    protected $settings = [];

    /** @var string Sssotoken */
    static protected $ssotoken = null;

    /** @var string Cached widget identifier, used by get_identifier(). */
    protected $identifier = null;

    /** @var string The path to where the global.ini file is found. */
    protected $globalinipath = '/mnt/data/conf/global.ini';

    /** @var string The url for the New Relic API */
    protected $newrelicapiurl = 'https://insights-api.newrelic.com/v1/accounts/1381294/query?nrql=';

    /** @var array Cached New Relic data */
    protected $newreliccache = null;

    /**
     * Get the human-readable name of the widget.
     *
     * @return string The human-readable name of the widget.
     */
    public function get_name() {
        return get_string('name', $this->get_component());
    }

    /**
     * Get the human-readable description of the widget.
     *
     * @return string The human-readable description of the widget.
     */
    public function get_description() {
        return get_string('description', $this->get_component());
    }

    /**
     * Get the Moodle component identifier of the widget (ex. block_rlagent or rlagentwidget_helloworld in the future).
     *
     * @return string The component identifier of the widget.
     */
    public function get_component() {
        return 'rlsiteadminwidget_'.$this->get_identifier();
    }

    /**
     * Get the path to the widget, for use in URLs
     *
     * @return string The path to the widget, relative to Moodle root.
     */
    public function get_path() {
        return '/local/rlsiteadmin/widgets/'.$this->get_identifier();
    }

    /**
     * Get HTML to display a preview of the widget on the widget selector page.
     *
     * @return string The preview HTML.
     */
    public function get_preview_html() {
        global $CFG;
        $path = $this->get_path().'/preview.png';
        if (file_exists($CFG->dirroot.$path)) {
            $url = new \moodle_url($path);
            $img = \html_writer::img($url, get_string('widget_preview_alt', 'local_rlsiteadmin'));
            return \html_writer::link($url, $img);
        } else {
            return get_string('widget_preview_notavailable', 'local_rlsiteadmin');
        }
    }

    /**
     * Get the unique identifier of the widget. This corresponds to the folder name of the widget (ex. helloworld).
     *
     * @return string The unique identifier of the widget.
     */
    public function get_identifier() {
        if (empty($this->identifier)) {
            $classparts = explode('\\', get_called_class());
            if (isset($classparts[0])) {
                $namespaceparts = explode('_', $classparts[0]);
                if (isset($namespaceparts[1])) {
                    $this->identifier = $namespaceparts[1];
                }
            }
            if (empty($this->identifier)) {
                throw new coding_exception('Invalid namespace specified for class '.get_called_class());
            }
        }
        return $this->identifier;
    }

    /**
     * Get an array of javascript files that are needed by the widget and must be loaded in the head of the page.
     *
     * @param bool $fullscreen Whether the widget is being displayed full-screen or not.
     * @return array Array of URLs or \moodle_url objects to require for the widget.
     */
    public function get_js_dependencies_head($fullscreen = false) {
        return [];
    }

    /**
     * Get an array of javascript files that are needed by the widget.
     *
     * @param bool $fullscreen Whether the widget is being displayed full-screen or not.
     * @return array Array of URLs or \moodle_url objects to require for the widget.
     */
    public function get_js_dependencies($fullscreen = false) {
        return [];
    }

    /**
     * Get an array of CSS files that are needed by the widget.
     *
     * @param bool $fullscreen Whether the widget is being displayed full-screen or not.
     * @return array Array of URLs or \moodle_url objects to require for the widget.
     */
    public function get_css_dependencies($fullscreen = false) {
        return [];
    }

    /**
     * Get a list of capabilities a user must have to be able to add this widget.
     *
     * @return array An array of capabilities the user must have (at the system context) to add this widget.
     */
    public function get_required_capabilities() {
        return [];
    }

    /**
     * Determine whether a user has the required capabilities to add this widget.
     *
     * @return bool Whether the user has the required capabilities to add this widget.
     */
    public function has_required_capabilities() {
        $requiredcaps = $this->get_required_capabilities();
        if (!empty($requiredcaps)) {
            $systemcontext = \context_system::instance();
            foreach ($requiredcaps as $requiredcap) {
                if (has_capability($requiredcap, $systemcontext) !== true) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Get HTML to display the widget.
     *
     * @param bool $fullscreen Whether the widget is being displayed full-screen or not.
     * @return string The HTML to display the widget.
     */
    public function get_html($fullscreen = false) {
        return '';
    }

    /**
     * Add widget's individual settings to the dashboard block's settings form.
     *
     * @param \moodleform &$mform The moodleform object for the dashboard block's settings form.
     */
    public function add_settings(&$mform) {
        // Add settings here...
    }

    /**
     * Set the widget's configured settings.
     *
     * @param \stdClass|array $settings Configured settings.
     */
    public function set_settings($settings = []) {
        if ($settings instanceof \stdClass) {
            $this->settings = (array)$settings;
        } else if (is_array($settings)) {
            $this->settings = $settings;
        } else {
            throw new \coding_exception('Invalid settings data passed to widget get_settings()');
        }
    }

    /**
     * Get the settings for this widget instance.
     *
     * @return array Settings for this widget instance.
     */
    public function get_settings() {
        return $this->settings;
    }

    /**
     * Get an instance of a widget.
     *
     * @param string $widget A unique widget identifier. If empty, will construct the called class.
     * @throws coding_exception If the passed $widget is not in /local/rlsiteadmin/classes/widgets.
     * @return \local_rlsiteadmin\lib\widgetinterface A widget instance.
     */
    public static function instance($widget = '') {
        $widgetclass = '\rlsiteadminwidget_'.$widget.'\widget';
        $widgetclass = str_replace(" ", "", $widgetclass);
        if (!empty($widget) && class_exists($widgetclass)) {
            return new $widgetclass;
        } else {
            throw new \coding_exception('Widget not found '.$widgetclass );
        }
    }

    /**
     * Get site ID from global.ini file
     * @return array The settings array contaning global.ini settings.
     */
    protected function get_site_id() {
        global $CFG;
        $sitename = basename($CFG->dirroot);
        $settings = array();
        $siteid = "";
        $siteid = '08qz8caaa';
//        if (file_exists($this->globalinipath) && is_readable($this->globalinipath)) {
//            $globalini = parse_ini_file($this->globalinipath, true);
//            $siteid = $globalini['webservers']['site_id'];
 //       }
        return $siteid;
    }

    /**
     * Retrieve New Relic query data.
     *
     * @param string $query A NRQL query.
     * @return array The results and code returned by New Relic curl call.
     */
    public function get_newrelic_data($query, $type) {
        global $CFG;
        $this->newrelicdata = \cache::make('local_rlsiteadmin', 'newrelicdata');
        $newrelicdata = $this->newrelicdata->get($type);
        // New relic cache is only valid for 30 minutes.
        $cachetimelimit = 1800;
        if (($newrelicdata === false || $newrelicdata->timestamp < (time() - $cachetimelimit)) && isset($CFG->newrelic_api_key)) {
            $url = $this->newrelicapiurl;
            $siteid = $this->get_site_id();
            $query = implode($siteid, explode('{rlsiteid}', $query));
            $url .= urlencode($query);
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_USERAGENT, 'rlsiteadmin');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $apikey = $CFG->newrelic_api_key;
            $headers[] = 'X-Query-Key:'.$apikey;
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            $result = trim(curl_exec($curl));
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $newrelicdata = json_decode($result);
            $newrelicdata->timestamp = time();
            $this->newrelicdata->set($type, $newrelicdata);
        }
        return $newrelicdata;
    }

    /**
     * Get a token for standalone dashboar widget iframe SSO.
     *
     * @param string &$errstr Optional error string to return in the event of an error.
     * @return string $ssotoken The token returned from the standalone dashboard, empty string on error.
     */
    protected function get_sso_token(&$errstr = null) {
        global $CFG, $USER;
        if (empty(self::$ssotoken)) {

            if (empty($CFG->RL_DASH_MOODLESSO_SECRET) || empty($CFG->RL_DASH_URL)) {
                return '';
            }

            // Get the VM name
            if (isset($CFG->VM_NAME)) {
                $vmname = str_replace(".rlem.net", "", $CFG->VM_NAME);
            } else {
                $vmname = str_replace(".rlem.net", "", gethostname());
            }

            // Get current timestamp
            $ts = time();

            // Sprinkle some salt
            $salt = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);

            // Get user info
            $username = $USER->username;
            $useremail = $USER->email;

            // Generate hash signature
            $msg = $vmname.$ts.$username.$useremail.$salt;
            $sig = hash_hmac("sha256", $msg, $CFG->RL_DASH_MOODLESSO_SECRET);

            // Make SSO url for token request
            $url = $CFG->RL_DASH_URL."/api/auth/moodle_sso_v1";
            $url .= "?vmname=".$vmname;
            $url .= "&ts=".$ts;
            $url .= "&salt=".$salt;
            $url .= "&username=".$username;
            $url .= "&useremail=".$useremail;
            $url .= "&sig=".$sig;
            $postfields['vmname'] = $vmname;
            $postfields['ts'] = $ts;
            $postfields['salt'] = $salt;
            $postfields['username'] = $username;
            $postfields['useremail'] = $useremail;

            // Get SSO token
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_USERAGENT, 'rlagent');
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $result = trim(curl_exec($curl));
            $decodedresult = @json_decode($result);
            if (empty($decodedresult->token)) {
                $errmsg = curl_error($curl);
                if (empty($errmsg)) {
                    if (empty($result)) {
                        $result = 'No Data';
                    }
                    $msgstart = strpos($result, '</title>');
                    // Note: preg_replace() can't match chars+newline ...
                    $errmsg = ($msgstart === false) ? $result : substr($result, $msgstart + strlen('</title>') - 1);
                }
                $eventdata = ['other' => [
                    'widget' => get_class($this),
                    'reason' => $errmsg
                ]];
                $event = \local_rlsiteadmin\event\authtoken_retrievefail::create($eventdata);
                $event->trigger();
                if (!is_null($errstr)) {
                    $errstr = $errmsg;
                }
                return '';
            }
            self::$ssotoken = $decodedresult->token;
        }
        return self::$ssotoken;
    }
}
