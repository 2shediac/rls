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
 * @package rlsiteadminwidget_otherservices
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2015 onwards Remote-Learner Inc (http://www.remote-learner.net)
 */

namespace rlsiteadminwidget_otherservices;

/**
 * A widget that displays hello world and a pie chart graphic.
 */
class widget extends \local_rlsiteadmin\lib\widgetbase {

    /**
     * Get HTML to display the widget.
     *
     * @param bool $fullscreen Whether the widget is being displayed full-screen or not.
     * @return string The HTML to display the widget.
     */
    public function get_html($fullscreen = false) {
        global $CFG;
        // Collect current services from global.ini.
        $globalini = $this->get_ini_settings();
        $currentservices = [];
        if (array_key_exists('deliverables', $globalini)) {
            // Check for ELIS.
            if (array_key_exists('deliverable_software', $globalini['deliverables'])) {
                $software = $globalini['deliverables']['deliverable_software'];
                if (strtolower($software) == 'elis') {
                    $currentservices[] = 'elis';
                }
            }
            // Check for additional add-ons.
            foreach ($globalini['deliverables'] as $key => $value) {
                // Look for addon_* entries.
                if (substr($key, 0, 6) == "addon_" && $value == true) {
                    // Add everything after addon_ to the currentservices array.
                    $currentservices[] = substr($key, 6);
                }
            }
        }
        // Check for backtrack/archive subscription.
        $sitename = basename($CFG->dirroot);
        if (array_key_exists($sitename, $globalini) && array_key_exists('archive_enabled', $globalini[$sitename])) {
            $archive = $globalini[$sitename]['archive_enabled'];
            if ($archive == true) {
                $currentservices[] = 'backtrack';
            }
        }
        if (!empty($CFG->behat_wwwroot) && $CFG->wwwroot == $CFG->behat_wwwroot) {
            return '<div class="behattest learningspaces">otherservices</div>';
        }
        // Print the iframe with url query vars for vm and current services.
        $html = '<iframe width="100%" height="380" src="https://widgetadmin.remote-learner.net/other-services?currentservices=';
        $html .= implode(",", $currentservices).'" frameborder="0"></iframe>';
        return $html;
    }

    /**
     * Get an global.ini array.
     *
     * @return array The settings array contaning global.ini settings.
     */
    protected function get_ini_settings() {
        // Collect current services from global.ini.
        $inifile = local_rlsiteadmin_get_global_ini_file();
        $globalini = array();
        if (file_exists($inifile) && is_readable($inifile)) {
            $globalini = parse_ini_file($inifile, true);
        }
        return $globalini;
    }

    /**
     * Get an array of CSS files that are needed by the widget.
     *
     * @param bool $fullscreen Whether the widget is being displayed full-screen or not.
     * @return array Array of URLs or \moodle_url objects to require for the widget.
     */
    public function get_css_dependencies($fullscreen = false) {
        return [
            new \moodle_url($this->get_path().'/css/widget.css'),
        ];
    }


    /**
     * Get an array of js files that are needed by the widget.
     *
     * @param bool $fullscreen Whether the widget is being displayed full-screen or not.
     * @return array Array of URLs or \moodle_url objects to require for the widget.
     */
    public function get_js_dependencies_head($fullscreen = false) {
        return [
            new \moodle_url($this->get_path().'/js/widget-header.js'),
        ];
    }
    /**
     * Get an array of js files that are needed by the widget.
     *
     * @param bool $fullscreen Whether the widget is being displayed full-screen or not.
     * @return array Array of URLs or \moodle_url objects to require for the widget.
     */
    public function get_js_dependencies($fullscreen = false) {
        return [
            new \moodle_url($this->get_path().'/js/widget-footer.js'),
        ];
    }
}
