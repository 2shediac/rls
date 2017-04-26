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
 * Render markup for Add-on Manager interface.
 *
 * @package   local_rlsiteadmin
 * @copyright 2013 onwards Remote-Learner {@link http://www.remote-learner.net/}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class local_rlsiteadmin_renderer extends plugin_renderer_base {

    /*
     * Output the update available option markup.
     *
     * @param  array $reinoptions Options passed to the format_text(), which displays the widget
     * @param  array $urlparams HTML attributes for <a> tag which opens pop-up with widget markup
     * @return string HTML fragment
     */

    public function print_update_available($USER) {
        global $CFG;

        // Container div.
        $content = html_writer::start_div('site-update');

        // Heading.
        $content .= html_writer::tag('h3', get_string('update_available_heading', 'local_rlsiteadmin'));

        // Info/instructions.
        $content .= html_writer::tag('div', get_string('update_available', 'local_rlsiteadmin'), array('class' => 'instr'));

        // Display update complete email to admin user.
        $content .= html_writer::start_span('notify-email');
        $content .= get_string('notification_email', 'local_rlsiteadmin');
        $content .= html_writer::end_span();
        $content .= html_writer::start_span('useremail');
        $content .= $USER->email;
        $content .= html_writer::end_span();

        // Print buttons.
        $content .= html_writer::start_div('update-controls');
        // Skip button.
        $content .= html_writer::start_tag('button', array('id' => 'skipupdate', 'class' => 'btn btn-warning', 'type' => 'button'));
        $content .= html_writer::tag('i', '', array('class' => 'fa fa-times'));
        $content .= get_string('skipupdate', 'local_rlsiteadmin');
        $content .= html_writer::end_tag('button');
        // Perform update button.
        $content .= html_writer::start_tag('button', array('id' => 'doupdate', 'class' => 'btn btn-success', 'type' => 'button'));
        $content .= html_writer::tag('i', '', array('class' => 'fa fa-check-circle-o'));
        $content .= get_string('syncsite', 'local_rlsiteadmin');
        $content .= html_writer::end_tag('button');
        $content .= html_writer::end_div();

        // Print update spinner.
        $content .= html_writer::start_tag('div', array('class' => 'site-update-spinner', 'style' => 'display: none;'));
        $content .= html_writer::tag('h4', get_string('updatingdata', 'local_rlsiteadmin'));
        $content .= html_writer::tag('i', '', array('class' => 'fa fa-spinner fa-spin fa-2x'));
        $content .= html_writer::tag('div', get_string('update_continue', 'local_rlsiteadmin'), array('class' => 'instr-continue', 'style' => 'display:none;'));
        $content .= html_writer::start_tag('button', array('id' => 'afterupdate', 'class' => 'btn btn-success', 'type' => 'button', 'style' => 'display:none;'));
        $content .= html_writer::tag('i', '', array('class' => 'fa fa-check-circle-o'));
        $content .= get_string('continue', 'local_rlsiteadmin');
        $content .= html_writer::end_tag('button');
        $content .= html_writer::end_tag('div');

        // Close container div.
        $content .=  html_writer::end_div();

        return $content;
    }


    /**
     * Output the markup for the accordion widget in the debug interface.
     *
     * @param  string $widgetname the name of the widget
     * @return string HTML fragment
     */
    public function print_widget($widgetname) {
        global $PAGE;

        // Create the widget instance.
        $widget = \local_rlsiteadmin\lib\widgetbase::instance($widgetname);

        // Add required head JS files.
        $requiredjs = $widget->get_js_dependencies_head();
        if (!empty($requiredjs)) {
            foreach ($requiredjs as $file) {
                $PAGE->requires->js($file, true);
            }
        }

        // Add required JS files.
        $requiredjs = $widget->get_js_dependencies();
        if (!empty($requiredjs)) {
            foreach ($requiredjs as $file) {
                $PAGE->requires->js($file);
            }
        }

        // Add required CSS files.
        $requiredcss = $widget->get_css_dependencies();
        if (!empty($requiredcss)) {
            foreach ($requiredcss as $file) {
                $PAGE->requires->css($file);
            }
        }

        // Retrieve and wrap widget markup.
        $widgetmarkup = $widget->get_html();
        if (empty($widgetmarkup)) {
            return '';
        }
        $widgetinner = html_writer::tag('div', $widgetmarkup, array('class' => 'rl-dashboard-widget-inner '.$widgetname.'-inner'));
        $widgetcontent = html_writer::tag('div', $widgetinner, array('class' => 'block rl-dashboard-widget '.$widgetname));

        return $widgetcontent;
    }

    /**
     * Return the markup for Bootstrap tabs nav element.
     *
     * @param  array $tabsarray array of tabs
     * @param  int $selectedtab index of selected tab
     * @return string HTML fragment
     */
    public function print_tabs($tabsarray, $selectedtab) {
        $content = html_writer::start_tag('ul', array('class' => 'nav nav-tabs', 'role' => 'tablist'));
        // Is there a selected tab? Not 0? If so, get index.
        $selectedindex = 0;
        if ($selectedtab) {
            $selectedtab = intval($selectedtab);
            if ($selectedtab < count($tabsarray)) {
                $selectedindex = $selectedtab;
            }
        }
        // List items for each tab.
        foreach ($tabsarray as $key => $value) {
            $selectedclass = '';
            if ($key === $selectedindex) {
                $selectedclass = 'active';
            }
            $liattribs = array(
                 'role' => 'presentation',
                'class' => $selectedclass);
            $anchattribs = array(
                         'href' => '#'.$value,
                'aria-controls' => $value,
                         'role' => 'tab',
                  'data-toggle' => 'tab');
            $content .= html_writer::start_tag('li', $liattribs);
            $content .= html_writer::tag('a', get_string('navtabs'.$value, 'local_rlsiteadmin'), $anchattribs);
            $content .= html_writer::end_tag('li');
        }
        $content .= html_writer::end_tag('ul');
        return $content;
    }

    /**
     * Return the markup for the tab contents wrapper.
     *
     * @param  wrting $wrapper wrapper to print
     * @param  array $tabsarray array of tabs
     * @param  int $selectedtab index of selected tab
     * @return string HTML fragment
     */
    public function print_tab_wrapper($wrapper, $tabsarray, $selectedtab) {
        $selected = $tabsarray[$selectedtab];
        $activeclass = '';
        if ($wrapper === $selected) {
            $activeclass = 'active';
        }
        $attribs = array(
             'class' => 'tab-pane '.$wrapper.' '.$activeclass,
                'id' => $wrapper);
        $markup = html_writer::start_tag('div', $attribs);
        return $markup;
    }
 }
