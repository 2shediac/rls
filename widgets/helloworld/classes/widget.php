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
 * @package rlsiteadminwidget_helloworld
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2015 onwards Remote-Learner Inc (http://www.remote-learner.net)
 */

namespace rlsiteadminwidget_helloworld;

/**
 * A simple "helloworld" widget that displays a configurable message.
 */
class widget extends \local_rlsiteadmin\lib\widgetbase {
    /**
     * Get HTML to display the widget.
     *
     * @param bool $fullscreen Whether the widget is being displayed full-screen or not.
     * @return string The HTML to display the widget.
     */
    public function get_html($fullscreen = false) {
        return (isset($this->settings['helloworld_text']))
                ? $this->settings['helloworld_text']
                : get_string('helloworld', $this->get_component());
    }

    /**
     * Get an array of CSS files that are needed by the widget.
     *
     * @param bool $fullscreen Whether the widget is being displayed full-screen or not.
     * @return array Array of URLs or \moodle_url objects to require for the widget.
     */
    public function get_css_dependencies($fullscreen = false) {
        return [new \moodle_url($this->get_path().'/css/helloworld.css')];
    }

    /**
     * Add widget's individual settings to the dashboard block's settings form.
     *
     * @param \moodleform &$mform The moodleform object for the dashboard block's settings form.
     */
    public function add_settings(&$mform) {
        $configlabel = get_string('label_texttodisplay', $this->get_component());
        $mform->addElement('text', 'config_'.$this->get_identifier().'_text', $configlabel);
        $mform->setType('config_'.$this->get_identifier().'_text', PARAM_TEXT);
        $defaulttext = get_string('helloworld', $this->get_component());
        $mform->setDefault('config_'.$this->get_identifier().'_text', $defaulttext);
    }
}
