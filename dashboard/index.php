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
 * Remote Learner Dashboard - Dashboard Widget Page
 *
 * @package   local_rlsiteadmin
 * @copyright 2015 Remote Learner Inc http://www.remote-learner.net
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$dir = dirname(__FILE__);
require_once($dir.'/../../../config.php');
require_once($dir.'/../lib.php');
require_once($dir.'/../lib/archive_api.php');

require_login(SITEID);

$has_admin = has_capability('moodle/site:config', context_system::instance());

$pluginname = get_string('pluginname', 'local_rlsiteadmin');
$pagetitle  = get_string('dashboard_overview_page_title', 'local_rlsiteadmin');

$PAGE->set_url('/local/rlsiteadmin/dashboard/index.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title($pluginname);
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add($pluginname);
$PAGE->navbar->add($pagetitle);
$PAGE->add_body_class("rl-dashboard-overview");

$contents = '';
$contents .= html_writer::tag('h2', get_string('navsad', 'local_rlsiteadmin'));

if (!$has_admin) {
    $contents .= html_writer::tag('p', get_string('error_notadmin', 'local_rlsiteadmin'));
} else {
    // User is admin, so we present all the widgets.
    // Load all requires.
    $PAGE->requires->css('/local/rlsiteadmin/css/font-awesome.min.css');
    if (!in_array('bootstrapbase', $PAGE->theme->parents)) {
        $PAGE->requires->css('/local/rlsiteadmin/css/bootstrap.css');
    }
    $PAGE->requires->css(new \moodle_url('/local/rlsiteadmin/css/dashboard.css'));
    $PAGE->requires->jquery();
    $PAGE->requires->js(new \moodle_url('/local/rlsiteadmin/js/bootstrap.min.js'));

    // Array of tabs.
    $tabsarray = ['info', 'support', 'reports'];

    $strings = array('dashboard_sessionstats_xlabel', 'dashboard_sessionstats_ylabel');
    $PAGE->requires->strings_for_js($strings, 'local_rlsiteadmin');
    $PAGE->requires->js_call_amd('local_rlsiteadmin/dashboard', 'init');

    // Get block renderers.
    $output = $PAGE->get_renderer('local_rlsiteadmin');

    // Get selected tab param if provided.
    $selectedtab = optional_param('tab', null, PARAM_TEXT);
    if ($selectedtab) {
        $selectedtab = intval($selectedtab);
    } else {
        $selectedtab = 0;
    }

    // Print page tabs.
    $contents .= $output->print_tabs($tabsarray, $selectedtab);

    // Wrapper for widgets.
    $contents .= html_writer::start_tag('div',
        array('class' => 'rl-dashboard-widget-wrapper rl-dashboard widgets tab-content'));
    $contents .= html_writer::start_tag('div', ['class' => "rl-dashboard-wells"]);
    foreach ($tabsarray as $tab) {
        $attr = ['data-name' => $tab, 'class' => "rl-dashboard-{$tab}-well well"];
        $contents .= html_writer::tag('div', get_string("dashboard_help_$tab", 'local_rlsiteadmin'), $attr);
    }
    $contents .= html_writer::end_tag('div');

    // Wrapper for support widgets.
    $contents .= $output->print_tab_wrapper('support', $tabsarray, $selectedtab);
    // Add Support Cases widget.
    $contents .= $output->print_widget("supportcases");
    // Close support widget wrapper.
    $contents .= html_writer::end_tag('div');

    // Wrapper for reports widgets.
    $contents .= $output->print_tab_wrapper('reports', $tabsarray, $selectedtab);
    // Add session statistic widget.
    $contents .= $output->print_widget("sessionstats");
    // Add browser statistic widget.
    $contents .= $output->print_widget("browserstats");
    // Add operating systems statistic widget.
    $contents .= $output->print_widget("operatingsystemstats");
    // Close reports widget wrapper.
    $contents .= html_writer::end_tag('div');

    // Wrapper for info widgets.
    $contents .= $output->print_tab_wrapper('info', $tabsarray, $selectedtab);
    // Add announcements widget.
    $contents .= $output->print_widget("announcements");
    // Add Learning Spaces widgets.
    $contents .= $output->print_widget("learningspaces");
    // Add Product News widget.
    $contents .= $output->print_widget("learningsolutions");
    // Add Moodle Statistic widget.
    $contents .= $output->print_widget("moodlestats");
    // Close reports widget wrapper.
    $contents .= html_writer::end_tag('div');

    // Close widgets wrapper.
    $contents .= html_writer::end_tag('div');
}

// Write the contents.
echo $OUTPUT->header($pagetitle);
echo "<link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>";
echo $contents;
// Print footer.
echo $OUTPUT->footer();


