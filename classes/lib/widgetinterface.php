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
 * Remote Learner Dashboard - Dashboard Widget Interface
 *
 * @package   local_rlsiteadmin
 * @copyright 2015 Remote Learner Inc http://www.remote-learner.net
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rlsiteadmin\lib;

/**
 * Interface defining footprint of a dashboard widget subplugin.
 */
interface widgetinterface {
    /**
     * Get the human-readable name of the widget.
     *
     * @return string The human-readable name of the widget.
     */
    public function get_name();

    /**
     * Get the human-readable description of the widget.
     *
     * @return string The human-readable description of the widget.
     */
    public function get_description();

    /**
     * Get the Moodle component identifier of the widget (ex. local_rlsiteadmin or rlsiteadminwidget_helloworld in the future).
     *
     * @return string The component identifier of the widget.
     */
    public function get_component();

    /**
     * Get the unique identifier of the widget. This corresponds to the folder name of the widget (ex. helloworld).
     *
     * @return string The unique identifier of the widget.
     */
    public function get_identifier();

    /**
     * Get an array of javascript files that are needed by the widget and must be loaded in the head of the page.
     *
     * @param bool $fullscreen Whether the widget is being displayed full-screen or not.
     * @return array Array of URLs or \moodle_url objects to require for the widget.
     */
    public function get_js_dependencies_head($fullscreen = false);

    /**
     * Get an array of javascript files that are needed by the widget.
     *
     * @param bool $fullscreen Whether the widget is being displayed full-screen or not.
     * @return array Array of URLs or \moodle_url objects to require for the widget.
     */
    public function get_js_dependencies($fullscreen = false);

    /**
     * Get an array of CSS files that are needed by the widget.
     *
     * @param bool $fullscreen Whether the widget is being displayed full-screen or not.
     * @return array Array of URLs or \moodle_url objects to require for the widget.
     */
    public function get_css_dependencies($fullscreen = false);

    /**
     * Get a list of capabilities a user must have to be able to add this widget.
     *
     * @return array An array of capabilities the user must have (at the system context) to add this widget.
     */
    public function get_required_capabilities();

    /**
     * Determine whether a user has the required capabilities to add this widget.
     *
     * @return bool Whether the user has the required capabilities to add this widget.
     */
    public function has_required_capabilities();

    /**
     * Get HTML to display the widget.
     *
     * @param bool $fullscreen Whether the widget is being displayed full-screen or not.
     * @return string The HTML to display the widget.
     */
    public function get_html($fullscreen = false);

    /**
     * Add widget's individual settings to the dashboard block's settings form.
     *
     * @param \moodleform &$mform The moodleform object for the dashboard block's settings form.
     */
    public function add_settings(&$mform);

    /**
     * Set the widget's configured settings.
     *
     * @param \stdClass|array $settings Configured settings.
     */
    public function set_settings($settings = []);

    /**
     * Get the settings for this widget instance.
     *
     * @return array Settings for this widget instance.
     */
    public function get_settings();

    /**
     * Get an instance of a widget.
     *
     * @param string $widget A unique widget identifier. If empty, will construct the called class.
     * @throws coding_exception If the passed $widget is not in /local/rlsiteadmin/classes/widgets.
     * @return \local_rlsiteadmin\lib\widgetinterface A widget instance.
     */
    public static function instance($widget = '');
}
