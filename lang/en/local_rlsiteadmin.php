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
 * Strings for component 'local_rlsiteadmin', language 'en'
 *
 * @package    block_rladmin
 * @copyright  2012 Remote Learner Inc http://www.remote-learner.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'RL Site Admin';

$string['actions_in_progress'] = 'Actions in Progress';
$string['actions_completed_dispatched'] = 'The requested actions have been queued. It may take some time for them to complete.';
$string['actions_completed_success'] = 'Congratulations! Your actions completed successfully.';
$string['actions_completed_failure'] = 'Uh oh... Something went wrong. Please <a href="http://support.remote-learner.net/">open a support case</a>.';
$string['add'] = 'Install';
$string['add_or_update_rating_bold'] = 'Add or update your rating';
$string['add_or_update_rating_normal'] = ' for this add on';
$string['alpha'] = 'Alphabetical';
$string['ajax_request_failed'] = 'AJAX request failed.';
$string['applied_filters'] = 'Applied Filters';
$string['available_for'] = 'Available for:';
$string['average_rating'] = 'Average Rating';
$string['block_instructions'] = '<p>The Add-on Manager interface allows you to install, upgrade, and rate add-ons on your sandbox site.</p>';
$string['btn_addfilters'] = 'Add Filters';
$string['btn_install'] = 'Install and Upgrade Add Ons';
$string['btn_rate'] = 'Rate Add Ons';
$string['cachedef_addondata'] = 'Cache for storing Add-on Manager add on data.';
$string['cancel'] = 'Cancel';
$string['cancelled'] = 'Cancelled';
$string['change'] = 'Change';
$string['clear_filters'] = 'Clear Filters';
$string['close'] = 'Close';
$string['communication_error'] = 'There was a communication problem while attempting to fetch remote data.';
$string['completed'] = 'Completed';
$string['confirm'] = 'Confirm';
$string['continue'] = 'Continue';
$string['defaultdate'] = 'Default time:';
$string['dependencies'] = 'Dependencies:';
$string['dependency_will_be_added'] = 'The following add on that {$a->source} depends on will also be added: {$a->target}.';
$string['dependency_will_be_removed'] = 'The following add on that depends on {$a->source} will be also be removed: {$a->target}.';
$string['disabled'] = 'The RL Manager block has been manually disabled, no updates will occur until the block is enabled.';
$string['disabledesc'] = 'Disabling automatic updates will prevent the automatic application of bug fixes and security updates.';
$string['dispatched'] = 'Dispatched:';
$string['status_header_waiting'] = 'Preparing...';
$string['status_header_running'] = 'Processing...';
$string['display_order'] = 'Display Order';
$string['enable'] = 'Enable';
$string['email_html_completed'] = '';
$string['email_html_error'] = '';
$string['email_html_skipped'] = '';
$string['email_sub_completed'] = 'Successful Site Update';
$string['email_sub_error'] = 'Error During Site Update';
$string['email_sub_skipped'] = 'Site Update Skipped';
$string['email_text_completed'] = 'Your Moodle site ({$a->www}) has been updated!';
$string['email_text_error'] = 'An error occurred during the automatic update of your site ({$a->www}):
{$a->log}';
$string['email_text_skipped'] = 'An update to your Moodle site ({$a->www}) was skipped:
{$a->log}';
$string['error'] = 'Error';
$string['error_add_installed'] = 'Add on {$a} is already installed.  Skipping addition.';
$string['error_brokengit'] = 'There was an error while checking your git status.';
$string['error_changedfiles'] = 'Your Moodle files have unapproved changes.';
$string['error_conflicts'] = 'Your Moodle repository has unresolved conflicts.';
$string['error_dependency_cycle'] = 'A dependency cycle was found, so these plugins can not be removed: {$a}';
$string['error_diverged'] = 'Your Moodle repository has unapproved changes.';
$string['error_notadmin'] = 'You must be a site administrator to access this functionality.';
$string['error_remove_notinstalled'] = 'Add on {$a} is not present.  Skipping removal';
$string['error_update_added'] = 'A new version of {$a} will be added, no further update possible.  Skipping update.';
$string['error_update_not_installed'] = 'Add on {$a} is not installed and thus can\'t be updated.  Skipping update.';
$string['error_update_removed'] = 'The {$a} add on will be removed and thus can\'t be updated.  Skipping update.';
$string['error_unable_to_copy_command'] = 'Unable to copy command file to dispatch directory.';
$string['error_unable_to_create_dispatch_dir'] = 'Unable to create dispatch directory: {$a}';
$string['error_unable_to_delete_temp_command_file'] = 'Unable to delete command file from temporary directory.';
$string['error_unable_to_write_temp_command_file'] = 'Unable to write commands to temporary file location.';
$string['error_unknown_addon'] = 'Unknown add on: {$a}.  Skipping.';
$string['error_unknown_addon_type'] = 'Unknown add on type: {$a->type} for add on {$a->name}.  Skipping.';
$string['error_unparseable_name'] = 'Action: {$a->action} - unrecognizable add on name: {$a->subject}.  Skipping.';
$string['error_updatefailed'] = 'An error occurred during the update process.';
$string['event_authtoken_retrievefail'] = 'Failed retrieving Authorization token';
$string['event_plugin_remove'] = 'Add-on Manager plugin removed';
$string['event_plugin_install'] = 'Add-on Manager plugin install';
$string['event_plugin_update'] = 'Add-on Manager plugin update';
$string['event_plugin_update'] = 'Add-on Manager plugin updated automatically';
$string['eventnotfound'] = 'Sorry, that scheduled update could be found in the database';
$string['failure'] = 'Failure';
$string['for_pricing'] = '<a href="http://support.remote-learner.net/">Contact your<br />Account Manager</a><br />for pricing.';
$string['inprogress'] = 'In Progress';
$string['install_instructions'] = 'Configure and test your add ons on your sandbox site, then request that the changes be moved to your production site.';
$string['locked'] = 'This plugin cannot be<br>installed, updated or<br>removed.';
$string['log'] = 'Log:';
$string['manageaddon'] = 'Manage Add Ons';
$string['managearchive'] = 'BackTrack Archives';
$string['mass_task_refreshcache'] = 'Refresh Add-on Manager Cache';
$string['mass_task_upgradeaddons'] = 'Upgrade addons set for automatic upgrades';
$string['name'] = 'Name:';
$string['navcategory'] = 'My RL Admin Tools';
$string['navsad'] = 'Site Dashboard';
$string['navaom'] = 'Add-on Manager';
$string['navbacktrack'] = 'BackTrack Archives';
$string['navtabssupport'] = 'Support';
$string['navtabsreports'] = 'Reports';
$string['navtabsinfo'] = 'Information & News';
$string['newdate'] = 'New time:';
$string['nextupdate'] = 'Your next update is:';
$string['no_dependencies'] = 'No dependencies.';
$string['not_available'] = 'Not Available';
$string['notchanged'] = 'The selected date is identical to the currently scheduled date';
$string['notice'] = 'Notice:';
$string['notification_email'] = 'Notification Email: ';
$string['notifyonsuccess'] = 'Notify on success:';
$string['notifyonsuccessdesc'] = 'Check this option to send an email to the recipient list every time a successful update happens.';
$string['notinrange'] = 'The selected date does not fall within the specified update period';
$string['notstarted'] = 'Not Started';
$string['noupdate'] = 'No update currently scheduled';
$string['packages'] = 'Packages:';
$string['pagetitle'] = 'Install and Upgrade Add Ons';
$string['permission_denied'] = 'Permission to contact add on server was denied.';
$string['plugin_description_not_available'] = 'Add on description not available.';
$string['plugin_name_not_available'] = 'Add on name not available.';
$string['plugin_status'] = 'Plugin status:';
$string['plugin_type'] = 'Plugin type:';
$string['plugins_need_help'] = 'If any of your plugins do not appear to be working as expected after you\'ve entered the necessary settings, please <a href="http://support.remote-learner.net/">open a support case</a>.';
$string['plugins_require_configuration'] = 'The following plugins that you have installed require configuration in their settings pages to work:';
$string['plugins_will_be_added'] = 'The following add ons will be added:';
$string['plugins_will_be_updated'] = 'The following add ons will be updated:';
$string['plugins_will_be_removed'] = 'The following add ons will be removed:';
$string['preparing_actions'] = 'Preparing actions:';
$string['recipients'] = 'Recipients';
$string['recipientsdesc'] = 'List of email addresses to receive notification emails (one per line)';
$string['release'] = 'Release date';
$string['remove'] = 'Uninstall';
$string['remove_action'] = 'Remove action';
$string['remove_filter'] = 'Remove filter';
$string['repair'] = 'Repair';
$string['rlsiteadmin:addinstance'] = 'Add a new RL Manager block';
$string['rlsiteadmin:myaddinstance'] = 'Add a new RL Manager block to My Home';
$string['save'] = 'Save';
$string['schedule'] = 'Schedule';
$string['schedule_new_event'] = 'Schedule a new update';
$string['scheduleddate'] = 'Scheduled time:';
$string['scheduledevents'] = 'Update Schedule';
$string['selected_plugins_queue'] = 'Selected Add Ons Queue';
$string['settings'] = 'Settings';
$string['siteadminonly'] = 'This page is for site administrators only.';
$string['skipped'] = 'Skipped';
$string['skipupdate'] = 'Skip';
$string['status'] = 'Status:';
$string['success'] = 'Success';
$string['syncsite'] = 'Perform Site Sync';
$string['task_processschedule'] = 'Process Schedule';
$string['temporarily_unavailable'] = 'The site will be temporarily unavailable while the changes are being applied.';
$string['title_admintool'] = 'Admin Tool';
$string['title_assignfeedback'] = 'Assignment Feedback';
$string['title_assignsubmission'] = 'Assignment Submission';
$string['title_atto'] = 'Atto Editor';
$string['title_auth'] = 'Authentication';
$string['title_availability'] = 'Availability Condition';
$string['title_block'] = 'Block';
$string['title_cachestore'] = 'Cache Storage';
$string['title_cachelock'] = 'Cache Lock';
$string['title_calendartype'] = 'Calendar type';
$string['title_datafield'] = 'Database Field';
$string['title_datapreset'] = 'Database Preset';
$string['title_editor'] = 'Editor';
$string['title_enrol'] = 'Enrolment';
$string['title_filter'] = 'Filter';
$string['title_format'] = 'Course Format';
$string['title_gradeexport'] = 'Grade Export';
$string['title_gradeimport'] = 'Grade Import';
$string['title_gradereport'] = 'Grade Report';
$string['title_gradingform'] = 'Grading Form';
$string['title_installed'] = 'Installed';
$string['title_local'] = 'Local';
$string['title_ltisource'] = 'LTI Source';
$string['title_message'] = 'Message';
$string['title_mod'] = 'Module';
$string['title_module'] = 'Module';
$string['title_not_installed'] = 'Not Installed';
$string['title_plagiarism'] = 'Plagiarism';
$string['title_portfolio'] = 'Portfolio';
$string['title_profilefield'] = 'Profilefield';
$string['title_qbehaviour'] = 'Question Behaviour';
$string['title_qformat'] = 'Question Format';
$string['title_qtype'] = 'Question Type';
$string['title_quiz'] = 'Quiz';
$string['title_quizaccess'] = 'Quiz Access';
$string['title_repairable'] = 'Repairable';
$string['title_report'] = 'Report';
$string['title_repository'] = 'Repository';
$string['title_scormreport'] = 'SCORM Report';
$string['title_theme'] = 'Theme';
$string['title_tinymce'] = 'TinyMCE';
$string['title_tool'] = 'Admin Tool';
$string['title_updateable'] = 'Updateable';
$string['title_webservice'] = 'Web Service';
$string['title_workshopallocation'] = 'Workshop Allocation';
$string['title_workshopeval'] = 'Workshop Evaluation';
$string['title_workshopform'] = 'Workshop Grading Form';
$string['to_be_added'] = 'To be added';
$string['to_be_removed'] = 'To be removed';
$string['to_be_updated'] = 'To be updated';
$string['trusted_addons_only'] = 'Only show Golden Add Ons';
$string['type'] = 'Type';
$string['type_filter'] = 'Type filter and select Enter';
$string['unknown_addon'] = 'Unknown add on';
$string['update'] = 'Update';
$string['update_available'] = '
    <p>This data for this staging site was last synced with the production site more than 7 days ago. If you wish to add or remove add ons with more current data, select the <strong>Update Data</strong> button below.</p>
    <p>Please note that this site sync will take several minutes. It will rewrite all site data, including this Web page. Your user account will be emailed when the update is complete. If the email below is not correct, please update your user account email before beginning this process.';
$string['update_available_heading'] = 'Update Available';
$string['update_continue'] = '
    <p>This sandbox site will now update from your production site.  This is an automated process that may take a few minutes to complete and you will receive an email when the process finishes.</p>
    <p>Please wait until you receive the confirmation email before using the continue button.  This site will be not be accessible during the update, and any changes you make before the update starts will be overwritten during the update.</p>';
$string['updateddate'] = 'Last updated:';
$string['updatedisabled'] = 'Updates have been disabled';
$string['updateend'] = 'Update Block End';
$string['updateenddesc'] = 'The end of the maintenance period when updates can be performed.';
$string['updateperiod'] = 'Update period:';
$string['updatescheduling'] = 'Update Scheduling';
$string['update_selected_plugins'] = 'Update/Install/Remove Selected Add Ons';
$string['updatespan'] =  '{$a->start} to {$a->end}';
$string['updatestart'] = 'Update Block Start';
$string['updatestartdesc'] = 'The start of the maintenance period when updates can be performed.';
$string['updatingdata'] = 'Updating Site Data';
$string['warning'] = 'Warning:';

$string['archivepagetitle'] = 'BackTrack Archives';
$string['snapshots_header'] = 'Available Snapshots';
$string['archive_expires_in'] = 'Expires in';
$string['archive_available_until'] = '<br />Available until ';
$string['archive_days'] = 'days.';
$string['archives_not_valid'] = 'This site does not appear to be configured to work with the archive manager. Please <a href="http://support.remote-learner.net" target="_blank">contact support</a> to setup the archive manager.';
$string['archives_header'] = 'Archived Snapshots';
$string['archive_status_in_progress'] = 'Snapshot archival in progress.';
$string['archive_request_message'] = 'Your request is being processed. Please note that large sites may take longer to process.';
$string['archive_status_ready'] = 'Archived: click the power button to restore.';
$string['archive_status_restoring'] = 'Restoring: an email will be sent to <span class="adminemail">you</span> when complete.';
$string['archive_status_restored'] = 'Ready: ';
$string['archive_slot_available'] = 'Slot Available';
$string['archive_error_general'] = 'There was an error processing your request. Please try again later.';
$string['archive_error_no_slots_available'] = 'There are no remaining slots available for archival. Remove an existing archive or contact your account manager to increase the number of available archive slots.';
$string['archive_error_restore_in_progress'] = 'This archive is currently restored or is being restored. Refresh this page to get the most accurate status.';
$string['archive_error_api_busy'] = 'The archive system is busy processing other requests submitted from this site. Please try again later.';
$string['archive_error_header'] = 'Error';
$string['archive_discard_confirmation'] = "Are you sure you want to discard this archive? \nThis action cannot be undone.";
$string['archive_error_no_restore_slots_available'] = 'There are currently too many actively restored archives. Power down an existing restored archive by pressing the power button on an archive that is currently restored.';
$string['archive_error_destroy_restore_first'] = 'This archive is currently restored. You must shut down the restore of this archive before you can discard the archive.';
$string['archive_restore_email_subject'] = 'Archive of {{ vm_name }} restored';
$string['archive_restore_email_body'] = "Hello,\n\nThe request you made to restore an archive of {{ vm_name }} is complete. ";
$string['archive_restore_email_body'] .= "The restore can be accessed from the following url: {{ vm_url }} and will be available for {{ restore_duration }} days before being turned off.\n\n";
$string['archive_restore_email_body'] .= "If you have questions or concerns regarding this restored archive, please file a support ticket at http://support.remote-learner.net.\n\n";
$string['archive_restore_email_body'] .= "Kind Regards,\n\n";
$string['archive_restore_email_body'] .= "Remote-Learner Inc\n";
$string['archive_restore_email_body'] .= "http://www.remote-learner.net/";

$string['event_snapshot_archived'] = 'Snapshot archived';
$string['event_archive_discarded'] = 'Archive discarded';
$string['event_archive_restored'] = 'Archive restored';
$string['event_restore_destroyed'] = 'Restore destroyed';

$string['dashboard_overview_page_title'] = 'Overview';

$string['dashboard_moodlestats_header'] = 'Site Details';
$string['moodlestats_heading'] = "Moodlestats widget";
$string['moodlestats_activeusersdays'] = "Users Days Active";
$string['moodlestats_activecoursedays'] = "Course Days Active";
$string['moodlestats_activeusersdaysdesc'] = "Number of days in the past for a user to be active";
$string['moodlestats_activecoursedaysdesc'] = "Number of days in the past for a course to be active";

$string['dashboard_sessionstats_header'] = 'User Sessions';
$string['dashboard_sessionstats_xlabel'] = 'Date and Time';
$string['dashboard_sessionstats_ylabel'] = 'Number of Sessions';

$string['dashboard_browserstats_header'] = 'Browser Statistics';

$string['dashboard_operatingsystems_header'] = 'Operating Systems Statistics';

$string['dashboard'] = 'Dashboard';

$string['dashboard_widget_info_button_tip'] = "Click here for additional information about this widget.";
$string['dashboard_widget_info_close_button_tip'] = "Click here for additional information about this widget.";

$string['mass_noresults'] = 'No plugins found matching your search criteria';
$string['widget_preview_alt'] = 'Widget preview';
$string['widget_preview_notavailable'] = 'No Preview Available';
$string['updatesettings'] = 'Select upgrade method:';
$string['updatesettings_manual'] = 'Manual';
$string['updatesettings_auto'] = 'Automatic';
$string['email_plugin_update_subject'] = 'Plugins for {$a->wwwroot} updated';
$string['email_plugin_update_body'] = 'The following plugins for {$a->wwwroot} have been updated:\n{$a->plugins}';
$string['block_upgradesettings'] = 'Upgrade settings';
$string['block_upgradesettings_instructions'] = 'Set all plugins to:';
$string['block_upgradesettings_auto'] = 'Automatic updates';
$string['block_upgradesettings_manual'] = 'Manual updates';
$string['admin_tools_heading'] = 'Tools';
$string['admin_tools_desc'] = 'If your installed plugins no longer match the plugins listed in the plugins list click this link: <a href="{$a->wwwroot}/local/rlsiteadmin/mass/invalidate_cache.php">Reset addons cache</a>';
$string['admin_tools_invalidate_cache'] = 'The addons cache has been refreshed.';
$string['dashboard_help_info'] = 'We will keep you posted on the latest news and events, including upcoming training opportunities for you and your team as well as site details at-a-glance.';
$string['dashboard_help_reports'] = 'Gain an understanding of the most important operating systems and browser types to support and also view volume of users accessing your site. All statistics represent the previous 7 days.';
$string['dashboard_help_support'] = 'Stay up-to-date on open support tickets, review ticket status, and submit new support tickets.';
