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
 * Remote Learner Update Manager - Moodle Addon Self Service page
 *
 * @package   local_rlsiteadmin
 * @copyright 2014 Remote Learner Inc http://www.remote-learner.net
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__).'/../../../config.php');
require_once(dirname(__FILE__).'/../lib.php');

require_login(SITEID);
if (!has_capability('moodle/site:config', context_system::instance())) {
    print_error('siteadminonly');
}

global $USER;

$onlyshowupdateable = optional_param('updateable', null, PARAM_INT);
$pluginname = get_string('pluginname', 'local_rlsiteadmin');
$pagetitle  = get_string('pagetitle', 'local_rlsiteadmin');
$addontypes = array(
        'tool', 'assignfeedback', 'assignsubmission', 'atto', 'auth', 'availability', 'block', 'cachestore',
        'cachelock', 'calendartype', 'datafield', 'datapreset', 'editor', 'enrol', 'filter', 'format',
        'gradeexport', 'gradeimport', 'gradereport', 'gradingform', 'local', 'ltisource', 'message', 'mod',
        'plagiarism', 'portfolio', 'profilefield', 'qbehaviour', 'qformat', 'qtype', 'quizaccess', 'quiz',
        'report', 'repository', 'scormreport', 'theme', 'tinymce', 'webservice', 'workshopallocation',
        'workshopeval', 'workshopform'
);

$PAGE->set_url('/local/rlsiteadmin/mass');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title($pluginname);
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add($pluginname);
$PAGE->navbar->add($pagetitle);

$PAGE->requires->css('/local/rlsiteadmin/css/font-awesome.min.css');
$PAGE->requires->css('/local/rlsiteadmin/css/mass.css');

if (!in_array('bootstrapbase', $PAGE->theme->parents)) {
    $PAGE->requires->css('/local/rlsiteadmin/css/bootstrap.css');
}

$strings = array(
        'actions_completed_dispatched', 'actions_completed_success', 'actions_completed_failure', 'actions_in_progress', 'add',
        'add_or_update_rating_bold', 'add_or_update_rating_normal', 'ajax_request_failed', 'available_for',
        'average_rating', 'cancel', 'close', 'confirm', 'dependencies', 'dependency_will_be_added',
        'dependency_will_be_removed', 'dispatched', 'failure', 'for_pricing', 'locked', 'no_dependencies',
        'notice', 'plugin_description_not_available', 'plugin_name_not_available', 'plugins_need_help',
        'plugins_require_configuration', 'plugins_will_be_added', 'plugins_will_be_removed', 'plugins_will_be_updated',
        'preparing_actions', 'remove', 'remove_action', 'remove_filter', 'repair', 'success', 'temporarily_unavailable',
        'to_be_added', 'to_be_removed', 'to_be_updated', 'update', 'not_available', 'status_header_waiting',
        'status_header_running', 'updatesettings', 'updatesettings_auto', 'updatesettings_manual'
);

foreach ($addontypes as $type) {
    $strings[] = "title_{$type}";
}
// Not sure why this is used, but JS complains about it being undefined.
$strings[] = "title_module";

$strings[] = 'mass_noresults';

$PAGE->requires->strings_for_js($strings, 'local_rlsiteadmin');
$PAGE->requires->js_call_amd('local_rlsiteadmin/mass', 'init');

// Print header.
print($OUTPUT->header($pagetitle));

// Get filter renderers.
$output = $PAGE->get_renderer('local_rlsiteadmin');

// Eventually we need a way to check whether the staging site's data
// is up to date with the production site. For now, a boolean.
$stale = local_rlsiteadmin_needs_update();
$displayplugins = '';
if ($stale) {
    echo $output->print_update_available($USER);
    $displayplugins = ' style="display: none;"';
}

$addfilters = get_string('btn_addfilters', 'local_rlsiteadmin');

// Updateable filter.
$updateablefilter = \html_writer::start_tag('label', ['class' => 'checkbox']);
$updateablefilterinputattrs = [
    'id' => 'local_rlsiteadmin_mass_filters_updateable',
    'type' => 'checkbox',
    'data-filter-mode' => 'status',
    'data-filter-refine' => 'updateable',
];
$updateablefilter .= \html_writer::empty_tag('input', $updateablefilterinputattrs);;
$updateablefilter .= get_string("title_updateable", 'local_rlsiteadmin');
$updateablefilter .= \html_writer::end_tag('label');

$filters = array(
    'group'  => array('<!-- Plugin group filter loaded by AJAX -->'),
    'status' => array(
            '<!-- status filter elements -->',
            '<label class="checkbox"><input type="checkbox" data-filter-mode="status" data-filter-refine="installed">'.get_string("title_installed", 'local_rlsiteadmin').'</label>',
            '<label class="checkbox"><input type="checkbox" data-filter-mode="status" data-filter-refine="notinstalled">'.get_string("title_not_installed", 'local_rlsiteadmin').'</label>',
            $updateablefilter,
            '<label class="checkbox"><input type="checkbox" data-filter-mode="status" data-filter-refine="repairable">'.get_string("title_repairable", 'local_rlsiteadmin').'</label>',
    ),
    'type'   => array('<!-- plugin type filter elements -->'),
    'display' => array(
             '<label class="checkbox"><input type="radio" id="display-order" name="display-order" value="alpha" checked>'.get_string("alpha", 'local_rlsiteadmin').'</label>',
             '<label class="checkbox"><input type="radio" id="display-order" name="display-order" value="type">'.get_string("plugin_type", 'local_rlsiteadmin').'</label>',
             '<label class="checkbox"><input type="radio" id="display-order" name="display-order" value="release">'.get_string("release", 'local_rlsiteadmin').'</label>',
    ),
);
foreach ($addontypes as $type) {
    $filters['type'][] = '<label class="checkbox"><input type="checkbox" data-filter-mode="type" data-filter-refine="'.$type.'">'.get_string("title_{$type}", 'local_rlsiteadmin').'</label>';
}

// Sort bar
$sortbar = '
    <div class="plugins span9 pull-right">
        <div id="labels-box" class="labels-box row-fluid" style="display: none;">
            <h4>'.get_string('applied_filters', 'local_rlsiteadmin').'</h4>
            <div id="filter-labels" class="labels">
            </div>
        </div>
        <div class="view-apply-filters-box row-fluid">
            <div id="plugin-cart" class="cart btn-group">
                <button class="btn dropdown-toggle plugin-actions disabled" style="width: 100%;" data-toggle="dropdown">
                    <i class="fa fa-check-square-o"></i>
                    '.get_string('selected_plugins_queue', 'local_rlsiteadmin').'
                    <span class="caret"></span>
                </button>
                <ul id="plugin-actions" class="dropdown-menu plugin-actions">
                    <!-- dropdown menu links -->
                </ul>
            </div>
            <button id="go-update-plugins" class="btn btn-success disabled">
                <i class="fa fa-cogs"></i>
                '.get_string('update_selected_plugins', 'local_rlsiteadmin').'
            </button>
        </div>
        <div class="loading-spinner">
            <i class="fa fa-cog fa-3x fa-spin"></i><br>
        </div>
    </div>
    <div id="filter-form" class="span3">
        <div class="block">
            <div class="header">
                <div class="toggle pull-right"><i class="fa fa-chevron-down fa-lg"></i></div>
                <div class="title"><h2>'.get_string('title_filter', 'local_rlsiteadmin').'</h2></div>
            </div>
            <div class="content">
                <input class="" id="plugin-filter" type="text" value="'.get_string('type_filter', 'local_rlsiteadmin').'">
                <label class="checkbox">
                    <input id="trust-filter" type="checkbox" value="1" data-filter-mode="trust" data-filter-refine="trusted" checked="checked"/>
                    '.get_string('trusted_addons_only', 'local_rlsiteadmin').'
                </label>
                <button id="clear-filters" class="btn">
                    <i class="fa fa-times"></i>
                    '.get_string('clear_filters', 'local_rlsiteadmin').'
                </button>
            </div>
        </div>
        <div class="block display">
          <div class="header">
             <div class="toggle pull-right"><i class="fa fa-chevron-down fa-lg"></i></div>
             <div class="title"><h2>'.get_string('display_order','local_rlsiteadmin').'</h2></div>
          </div>
          <div class="content">
          '.implode("\n",$filters['display']).'
          </div>
         </div>
        <div class="block statuses">
            <div class="header">
                <div class="toggle pull-right"><i class="fa fa-chevron-down fa-lg"></i></div>
                <div class="title"><h2>'.get_string('plugin_status', 'local_rlsiteadmin').'</h2></div>
            </div>
            <div class="content">
            '.implode("\n", $filters['status']).'
            </div>
        </div>
        <div class="block types">
            <div class="header">
                <div class="toggle pull-right"><i class="fa fa-chevron-down fa-lg"></i></div>
                <div class="title"><h2>'.get_string('plugin_type', 'local_rlsiteadmin').'</h2></div>
            </div>
            <div class="content">
            '.implode("\n", $filters['type']).'
            </div>
        </div>
        <div class="block groups">
            <div class="header">
                <div class="toggle pull-right"><i class="fa fa-chevron-down fa-lg"></i></div>
                <div class="title"><h2>'.get_string('packages', 'local_rlsiteadmin').'</h2></div>
            </div>
            <div class="content">
                <div class="loading-spinner">
                    <i class="fa fa-cog fa-3x fa-spin"></i><br>
                </div>
                '.implode("\n", $filters['group']).'
            </div>
        </div>
        <div class="block upgradesettings">
            <div class="header">
                <div class="toggle pull-right"><i class="fa fa-chevron-down fa-lg"></i></div>
                <div class="title"><h2>'.get_string('block_upgradesettings', 'local_rlsiteadmin').'</h2></div>
            </div>
            <div class="content">
            <p>'.get_string('block_upgradesettings_instructions', 'local_rlsiteadmin');
$sortbar .= '<button id="upgradesettings_all_auto" type="button" class="btn btn-block btn-install btn-success">';
$sortbar .= get_string('block_upgradesettings_auto', 'local_rlsiteadmin').'</button>';
$sortbar .= '<button id="upgradesettings_all_manual" type="button" class="btn btn-block btn-danger">';
$sortbar .= get_string('block_upgradesettings_manual', 'local_rlsiteadmin').'</button>';
$sortbar .= '</div>
        </div>


    </div>';

$pluginselect = "<div class=\"plugin-select\"{$displayplugins}>{$sortbar}</div>";
print($pluginselect);

$modal = '
  <div class="modal fade" id="manage_actions_modal" tabindex="-1" role="dialog" aria-labelledby="installModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title" id="myModalLabel"></h4>
        </div>
        <div class="modal-body">

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>';

print($modal);

if (!empty($onlyshowupdateable)) {
    $js = '<script>
        document.addEventListener("DOMContentLoaded", function(event) {
            document.getElementById(\'local_rlsiteadmin_mass_filters_updateable\').checked = true;
        });
        </script>';
    print($js);
}

// Print footer.
print($OUTPUT->footer());
