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
 * @package rlsiteadminwidget_browserstats
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2017 onwards Remote-Learner Inc (http://www.remote-learner.net)
 */

namespace rlsiteadminwidget_browserstats;

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
        $html = '<div class="rl-widget-content-inner">';
        $headertext = get_string('dashboard_browserstats_header', 'local_rlsiteadmin');
        $headertext .= '<i class="fa fa-info-circle rlsiteadmin-widget-info-button"';
        $headertext .= 'title="'.get_string('dashboard_widget_info_button_tip', 'local_rlsiteadmin').'"></i>';
        $html .= '<div class="navbar-default widget-header">'.$headertext.'</div>';
        $html .= '<div class="widget-body">';
        $html .= '<div id="browserstats-graph"><div class="loading-spinner"><i class="fa fa-cog fa-spin fa-3x fa-fw"></i>
<span class="sr-only">Loading...</span></div></div>';
        $html .= '</div>';
        $html .= '<div id="sessionstats-info" class="rlsiteadmin-widget-info">';
        $html .= '<i class="fa fa-times-circle rlsiteadmin-widget-info-close-button"';
        $html .= 'title="'.get_string('dashboard_widget_info_close_button_tip', 'local_rlsiteadmin').'"></i>';
        $html .= '<div class="rlsiteadmin-widget-info-inner">';
        $html .= get_string('widget_info', 'rlsiteadminwidget_browserstats');
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
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
