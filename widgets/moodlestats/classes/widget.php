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
 * @package rlsiteadminwidget_moodlestats
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2015 onwards Remote-Learner Inc (http://www.remote-learner.net)
 */

namespace rlsiteadminwidget_moodlestats;

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

        $support = [
            27 => '2017-12',
            28 => '2016-12',
            29 => '2017-06',
            30 => '2017-12',
            31 => '2019-12',
            32 => '2018-12',
        ];
        $endym = (isset($CFG->branch) && isset($support[$CFG->branch])) ? $support[$CFG->branch] : '1969-01';
        $curym = date('Y-m');

        $stats = $this->get_moodlestats();
        $html = '<div class="rl-widget-content-inner">';
        $html .= '<div class="navbar-default widget-header">'.get_string('dashboard_moodlestats_header', 'local_rlsiteadmin').'</div>';
        $html .= '<div class="widget-body">';
        $html .= '<div class="moodlestats-table">
            <table class="table table-hover table-striped" >
                <tbody>
                    <tr>
                        <th>Release</th>
                        <td>'.$this->get_version_support_alert_box($CFG->release, $curym, $endym).'</td>
                    </tr>
                    <tr>
                        <th>Version</th>
                        <td>'.$CFG->version.'</td>
                    </tr>';
        if ($stats['activecourses'] != null) {
            $html .= '<tr class="activecourses">
                        <th>Active courses</th>
                        <td>'.$stats['activecourses'].'</td>
                      </tr>';
        }
        $html .= '  <tr class="totalcourses">
                        <th>Total courses</th>
                        <td>'.$stats['totalcourses'].'</td>
                    </tr>
                    <tr class="activeusers">
                        <th>Active users</th>
                        <td>'.$stats['activeusers'].'</td>
                    </tr>
                    <tr class="totalusers">
                        <th>Total users</th>
                        <td>'.$stats['totalusers'].'</td>
                    </tr>
                    <tr class="totalplugins">
                        <th>Total Plugins</th>
                        <td><a href="'.$CFG->wwwroot.'/local/rlsiteadmin/mass/">'.$stats['totalplugins'].'</a></td>
                    </tr>
                </tbody>
            </table>
        </div>';
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    /**
     * Get Moodle statistics to display in the widget.
     *
     * @return array Moodle statistics.
     */
    public function get_moodlestats() {
        global $DB, $CFG;
        $stats = array();
        $stats['vmname'] = preg_replace("/.rlem.net/", "", gethostname());
        $enabledstores = preg_split("/,/", get_config('tool_log', 'enabled_stores'));
        $courseslimit = 7;
        if (!empty($CFG->local_rlsiteadmin_activecoursedays)) {
            $courseslimit = $CFG->local_rlsiteadmin_activecoursedays;
        }
        $userslimit = 365;
        if (!empty($CFG->local_rlsiteadmin_activeusersdays)) {
            $userslimit = $CFG->local_rlsiteadmin_activeusersdays;
        }
        $time = time() - $courseslimit * 86400;
        if (in_array('logstore_standard', $enabledstores)) {
            $results = $DB->get_record_sql("SELECT COUNT(DISTINCT courseid) c
                                              FROM {logstore_standard_log} log, {course} course
                                             WHERE contextlevel = 70
                                                   AND courseid = course.id
                                                   AND course.visible = 1
                                                   AND courseid != 1
                                                   AND log.timecreated > $time");
            $stats['activecourses'] = $results->c;
        } else if (in_array('logstore_legacy', $enabledstores) && get_config('logstore_legacy', 'loglegacy')) {
            $results = $DB->get_record_sql("SELECT COUNT(DISTINCT course) c
                                              FROM {log} log, {course} course
                                             WHERE time > $time
                                                   AND module != 'user'
                                                   AND course != 1
                                                   AND course = course.id
                                                   AND course.visible = 1
                                                   AND action != 'view'");
            $stats['activecourses'] = $results->c;
        } else {
            $stats['activecourses'] = null;
        }

        $result = $DB->get_record_sql('SELECT COUNT(*) c FROM {course} WHERE visible = 1 AND id != 1');
        $stats['totalcourses'] = $result->c;

        $results = $DB->get_record_sql("SELECT COUNT(*) c
                                          FROM {user}
                                         WHERE id != 1
                                               AND suspended = 0
                                               AND mnethostid = ?
                                               AND lastaccess > UNIX_TIMESTAMP()-$userslimit*86400",
                array($CFG->mnet_localhost_id));
        $stats['activeusers'] = $results->c;
        $results = $DB->get_record_sql("SELECT COUNT(*) c
                                          FROM {user}
                                         WHERE id != 1
                                               AND suspended = 0
                                               AND mnethostid = ?",
                array($CFG->mnet_localhost_id));
        $stats['totalusers'] = $results->c;

        $pluginman = \core_plugin_manager::instance();
        $plugins = $pluginman->get_plugins();
        $total = 0;
        foreach ($plugins as $type => $plugins) {
            $total += count($plugins);
        }
        $stats['totalplugins'] = $total;
        return $stats;
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

    /**
     * Get the version support alert box for a given release, current date, and end date.
     *
     * @param string $currelease The current release. Only used in language string.
     * @param string $curym A date string for the current year/month. In the form "YYYY-MM"
     * @param string $endym A date string for the end of support. In the form "YYYY-MM"
     */
    public function get_version_support_alert_box($currelease, $curym, $endym) {
        $curdate = date_create($curym);
        $enddate = date_create($endym);
        $diff = date_diff($curdate, $enddate);

        $totalmonths = ($diff->format('%r%y') * 12) + $diff->format('%r%m');
        $class = 'alert ';
        $string = $currelease.'<br />';
        if ($totalmonths > 6) {
            // Supported for > 6 months.
            $class .= 'alert-success';
            $string .= get_string('versionsupported', 'rlsiteadminwidget_moodlestats', $endym);
        } else if ($totalmonths > 0 && $totalmonths <= 6) {
            // Warning.
            $string .= get_string('versionwarning', 'rlsiteadminwidget_moodlestats', $endym);
        } else {
            // Not supported.
            $class .= 'alert-error';
            $string .= get_string('versionnotsupported', 'rlsiteadminwidget_moodlestats', $endym);
        }
        return \html_writer::tag('div', $string, ['class' => $class]);
    }
}
