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
 * @package rlsiteadminwidget_learningsolutions
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2015 onwards Remote-Learner Inc (http://www.remote-learner.net)
 */

namespace rlsiteadminwidget_learningsolutions;

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
        if (!empty($CFG->behat_wwwroot) && $CFG->wwwroot == $CFG->behat_wwwroot) {
            return '<div class="behattest learningsolutions">learningsolutions</div>';
        }
        if (isset($CFG->VM_NAME)) {
            $vmname = str_replace(".rlem.net", "", $CFG->VM_NAME);
        } else {
            $vmname = str_replace(".rlem.net", "", gethostname());
        }
        $html = '<iframe width="100%" height="380" src="https://widgetadmin.remote-learner.net/learning-solutions?vm='.$vmname.'" frameborder="0"></iframe>';
        return $html;
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
