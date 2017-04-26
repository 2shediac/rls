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
$dir = dirname(__FILE__);
require_once($dir.'/../../../config.php');
require_once($dir.'/../lib.php');
require_once($dir.'/../lib/archive_api.php');

// Create Archive API object.
$archiveapi = new local_rlsiteadmin_archive_api();

require_login(SITEID);
if (!has_capability('moodle/site:config', context_system::instance())) {
    print_error('siteadminonly');
}

$pluginname = get_string('pluginname', 'local_rlsiteadmin');
$pagetitle  = get_string('archivepagetitle', 'local_rlsiteadmin');

$PAGE->set_url('/local/rlsiteadmin/archive/index.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title($pluginname);
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add($pluginname);
$PAGE->navbar->add($pagetitle);
$PAGE->add_body_class("archive-manager");

$PAGE->requires->css('/local/rlsiteadmin/css/font-awesome.min.css');
if (!in_array('bootstrapbase', $PAGE->theme->parents)) {
    $PAGE->requires->css('/local/rlsiteadmin/css/bootstrap.css');
}

$PAGE->requires->yui_module('moodle-local_rlsiteadmin-archive', 'M.local_rlsiteadmin.init');

$strings = array(
    'ajax_request_failed', 'archive_status_in_progress', 'archive_status_ready', 'archive_status_restoring', 'archive_status_restored',
    'archive_available_until', 'close', 'archive_request_message', 'archive_error_general', 'archive_error_no_slots_available',
    'archive_error_restore_in_progress', 'archive_error_api_busy', 'archive_error_header', 'archive_discard_confirmation',
    'archive_error_no_restore_slots_available', 'archive_error_destroy_restore_first'
);

$PAGE->requires->strings_for_js($strings, 'local_rlsiteadmin');

// Check if site is properly setup for the archive manager.
$archive_validated = get_config('local_rlsiteadmin', 'archive_validated');
if (empty($archiveapi->settings['archive_enabled'])) {
    set_config('archive_validated', 0, 'local_rlsiteadmin');
    $archive_validated = false;
} else if (!$archive_validated) {
    if ($archiveapi->has_archive()) {
        set_config('archive_validated', 1, 'local_rlsiteadmin');
        $archive_validated = true;
    } else {
        set_config('archive_validated', 0, 'local_rlsiteadmin');
        $archive_validated = false;
    }
}

// Print header.
print($OUTPUT->header($pagetitle));

// Get filter renderers.
$output = $PAGE->get_renderer('local_rlsiteadmin');
$maxarchives = (isset($archiveapi->settings['max_archived_snapshots']))
        ? $archiveapi->settings['max_archived_snapshots']
        : 0;
if ($archive_validated) {
$archivelayout = '
    <div class="archive-manager">
        <div class="row-fluid row">
            <!-- start snapshots -->
            <div id="rlsnapshots" class="span6">
                <h3>'.get_string('snapshots_header', 'local_rlsiteadmin').'</h3>
                <div class="spinner">
                    <i class="fa fa-cog fa-3x fa-spin"></i>
                </div>
                <div class="list">
                </div>
            </div>
            <!-- end snapshots -->
            <!-- start archives -->
            <div id="rlarchives" class="span6">
                <div class="clearfix">
                    <h3>
                        <div class="count">
                            <span id="numused"></span>
                            /
                            <span id="numtotal">'.$maxarchives.'</span>
                        </div>
                        '.get_string('archives_header', 'local_rlsiteadmin').'
                    </h3>
                </div>
                <div class="spinner">
                    <i class="fa fa-cog fa-3x fa-spin"></i>
                </div>
                <div class="list">
                </div>
            </div>
            <!-- end archives -->
        </div>
        <div class="templates">
            <div class="snapshot block fadedout">
                <div class="clearfix">
                    <div class="icon">
                        <i class="fa fa-cloud"></i>
                    </div>
                    <div class="date">
                    </div>
                    <div class="button save">
                        <i class="fa fa-save fa-2x"></i>
                    </div>
                    <div class="expiration">
                    '.get_string('archive_expires_in', 'local_rlsiteadmin').'
                    <span class="expiration-date"></span>
                    '.get_string('archive_days', 'local_rlsiteadmin').'
                    </div>
                    <div style="clear:both;"></div>
                </div>
            </div>
            <div class="slot slot-open block fadedout">
                <div class="clearfix">
                    <div class="icon">
                        <i class="fa fa-cloud"></i>
                    </div>
                    <div class="info">
                    '.get_string('archive_slot_available', 'local_rlsiteadmin').'
                    </div>
                    <div class="spinner">
                        <i class="fa fa-cog fa-3x fa-spin"></i>
                    </div>
                </div>
            </div>
            <div class="archive block fadedout">
                <div class="clearfix">
                    <div class="icon">
                          <i class="fa fa-cloud"></i>
                          <i class="fa fa-gear fa-spin"></i>
                          <i class="fa fa-wifi"></i>
                    </div>
                    <div class="controls">
                        <div class="button power">
                            <i class="fa fa-power-off fa-lg"></i>
                        </div>
                        <div class="button discard">
                            <i class="fa fa-trash fa-lg"></i>
                        </div>
                    </div>
                    <div class="info">
                        <div class="date">
                        </div>
                        <div class="status">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="useremail">'.$USER->email.'</div>
    </div>';
} else {
    $archivelayout = '
        <div class="block archive-error">
            <i class="fa fa-exclamation-triangle"></i>
            '.get_string('archives_not_valid', 'local_rlsiteadmin').'
        </div>';
}
print($archivelayout);

// Print footer.
print($OUTPUT->footer());
