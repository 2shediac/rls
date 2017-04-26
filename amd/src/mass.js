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
 * Remote Learner Update Manager - Moodle Addon Self Service User Interface code
 *
 * @package   local_rlsiteadmin
 * @copyright 2014 Remote Learner Inc http://www.remote-learner.net
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/yui'], function ($, Y) {
    M.local_rlsiteadmin = M.local_rlsiteadmin || {};
    M.local_rlsiteadmin = {
        /* @var object Contains the actions the user has selected. */
        data_actions: {
            "add": {},
            "remove": {},
            "update": {}
        },

        /* @var object Contains the list of addons that the user is allowed to manage. */
        data_addons: {},

        /* @var object Contains the list of filters for the addons. */
        data_filters: {
            'group': {},
            'status': {},
            'string': {},
            'trust': {},
            'type': {},
        },

        /* @var object Contains the list of addon groups that the user is allowed to select. */
        data_groups: {},

        /* @var object The modal that displays the confirmation message for actions. */
        plugin_modal: null,

        /* @var object Placeholder for last clicked node.  Used to deduplicate rate events */
        plugin_rate_node: null,

        /* @var object Contains the list of plugin types that are current supported with icons. */
        plugin_types: {
            'auth': {'type': 'auth', 'title': M.util.get_string('title_auth', 'local_rlsiteadmin'), 'icon': 'fa-key'},
            'block': {'type': 'block', 'title': M.util.get_string('title_block', 'local_rlsiteadmin'), 'icon': 'fa-cube'},
            'calendartype': {'type': 'calendartype', 'title': M.util.get_string('title_calendartype', 'local_rlsiteadmin'), 'icon': 'fa-calendar'},
            'enrol': {'type': 'enrol', 'title': M.util.get_string('title_enrol', 'local_rlsiteadmin'), 'icon': 'fa-group'},
            'filter': {'type': 'filter', 'title': M.util.get_string('title_filter', 'local_rlsiteadmin'), 'icon': 'fa-filter'},
            'format': {'type': 'format', 'title': M.util.get_string('title_format', 'local_rlsiteadmin'), 'icon': 'fa-columns'},
            'gradeexport': {'type': 'gradeexport', 'title': M.util.get_string('title_gradeexport', 'local_rlsiteadmin'), 'icon': 'fa-download'},
            'local': {'type': 'local', 'title': M.util.get_string('title_local', 'local_rlsiteadmin'), 'icon': 'fa-home'},
            'mod': {'type': 'mod', 'title': M.util.get_string('title_module', 'local_rlsiteadmin'), 'icon': 'fa-puzzle-piece'},
            'plagiarism': {'type': 'plagiarism', 'title': M.util.get_string('title_plagiarism', 'local_rlsiteadmin'), 'icon': 'fa-eye'},
            'qtype': {'type': 'qtype', 'title': M.util.get_string('title_qtype', 'local_rlsiteadmin'), 'icon': 'fa-question'},
            'report': {'type': 'qtype', 'title': M.util.get_string('title_report', 'local_rlsiteadmin'), 'icon': 'fa-file-text-o'},
            'repository': {'type': 'repository', 'title': M.util.get_string('title_repository', 'local_rlsiteadmin'), 'icon': 'fa-folder-open'},
            'theme': {'type': 'theme', 'title': M.util.get_string('title_theme', 'local_rlsiteadmin'), 'icon': 'fa-picture-o'},
            'tinymce': {'type': 'tinymce', 'title': M.util.get_string('title_tinymce', 'local_rlsiteadmin'), 'icon': 'fa-text-height'}
        },

        /**
         * Update dropdown listing plugin actions.
         */
        action_dropdown_update: function() {
            Y.log('update_dropdown');

            var $ul = Y.one('ul#plugin-actions'),
                $dropbtn = Y.one('button.plugin-actions'),
                $updatebtn = Y.one('#go-update-plugins'),
                $items;

            Y.log($ul);

            /**
             * Remove an action from the action list.
             */
            function remove_action(e) {
                e.preventDefault();
                var $this = e.target,
                    // Fetch key.
                    $key = $this.ancestor('li').getAttribute('data-key'),
                    // Fetch action.
                    $action = $this.ancestor('li').getAttribute('data-action'),
                    $btn = Y.one('.plugin.well[data-key="' + $key + '"] button[data-action="' + $action + '"]');

                Y.log('remove_action');
                Y.log(e.target);
                // Remove action from object.
                delete M.local_rlsiteadmin.data_actions[$action][$key];

                Y.log('attempted to delete relevant action node, displaying new data_actions object below');
                Y.log(M.local_rlsiteadmin.data_actions);

                // If no actions, disable buttons.
                if (Object.keys(M.local_rlsiteadmin.data_actions.add).length === 0 &&
                    Object.keys(M.local_rlsiteadmin.data_actions.remove).length === 0 &&
                    Object.keys(M.local_rlsiteadmin.data_actions.update).length === 0 ) {

                    Y.one('ul#plugin-actions').setStyle('display', 'none');
                    if (!$dropbtn.hasClass('disabled')) {
                        $dropbtn.addClass('disabled');
                    }
                    if (!$updatebtn.hasClass('disabled')) {
                        $updatebtn.addClass('disabled');
                    }
                }

                // Remove list item (hide menu first to hide weird popup contortions).
                $this.ancestor('li').remove(true);

                // Reactivate plugin button pertaining to action.
                if ($btn.hasClass('disabled')) {
                    $btn.removeClass('disabled');
                }
            }

            // Remove all click listeners in the list as they will have to be redone
            // and we don't want duplicates.
            $ul.detach('click');

            // If the data_actions object is empty, remove list items and disable buttons.
            if (Object.keys(M.local_rlsiteadmin.data_actions.add).length === 0 &&
                Object.keys(M.local_rlsiteadmin.data_actions.remove).length === 0 &&
                Object.keys(M.local_rlsiteadmin.data_actions.update).length === 0 ) {

                // Then remove all list items.
                $items = $ul.all('li');
                if ($items.size()) {
                    $ul.empty();
                }

                // And disable buttons.
                if (!$dropbtn.hasClass('disabled')) {
                    $dropbtn.addClass('disabled');
                }
                if (!$updatebtn.hasClass('disabled')) {
                    $updatebtn.addClass('disabled');
                }

                return false;
            }

            // Address all add actions.
            if (Object.keys(M.local_rlsiteadmin.data_actions.add).length > 0) {
                // Empty all add plugins
                $ul.all('[data-action="add"]').remove(true);
                // Insert new addon plugins
                Y.Object.each(M.local_rlsiteadmin.data_actions.add, function($value, $key) {
                    // Build markup for individual list item.
                    var $markup;
                    $markup = '<li data-action="add" ';
                    $markup += 'data-key="' + $key + '">';
                    $markup += '<i class="fa fa-plus" alt="'+M.util.get_string('to_be_added', 'local_rlsiteadmin')+'"></i>';
                    $markup += M.local_rlsiteadmin.data_addons[$key].display_name; // Addon name
                    $markup += '<i class="fa fa-times-circle rm-action" alt="'+M.util.get_string('remove_action', 'local_rlsiteadmin')+'"></i>';
                    $markup += '</li>';
                    // Insert markup.
                    $ul.append($markup);
                });

                // If buttons are disabled, enable them.
                if ($dropbtn.hasClass('disabled')) {
                    Y.log('dropbtn has disabled class');
                    $dropbtn.removeClass('disabled');
                }
                if ($updatebtn.hasClass('disabled')) {
                    Y.log('updatebtn has disabled class');
                    $updatebtn.removeClass('disabled');
                }
            }

            // Address all remove actions.
            if (Object.keys(M.local_rlsiteadmin.data_actions.remove).length > 0) {
                // Empty all add plugins
                $ul.all('[data-action="remove"]').remove(true);
                // Insert new addon plugins
                Y.Object.each(M.local_rlsiteadmin.data_actions.remove, function($value, $key) {
                    // Build markup for individual list item.
                    var $markup;
                    $markup = '<li data-action="remove" ';
                    $markup += 'data-key="' + $key + '">';
                    $markup += '<i class="fa fa-times" alt="'+M.util.get_string('to_be_added', 'local_rlsiteadmin')+'"></i>';
                    $markup += M.local_rlsiteadmin.data_addons[$key].display_name; // Addon name
                    $markup += '<i class="fa fa-times-circle rm-action" alt="'+M.util.get_string('remove_action', 'local_rlsiteadmin')+'"></i>';
                    $markup += '</li>';
                    // Insert markup.
                    $ul.append($markup);
                });

                // If buttons are disabled, enable them.
                if ($dropbtn.hasClass('disabled')) {
                    Y.log('dropbtn has disabled class');
                    $dropbtn.removeClass('disabled');
                }
                if ($updatebtn.hasClass('disabled')) {
                    Y.log('updatebtn has disabled class');
                    $updatebtn.removeClass('disabled');
                }
            }

            // Address all update actions.
            if (Object.keys(M.local_rlsiteadmin.data_actions.update).length > 0) {
                // Empty all add plugins
                $ul.all('[data-action="update"]').remove(true);
                // Insert new addon plugins
                Y.Object.each(M.local_rlsiteadmin.data_actions.update, function($value, $key) {
                    // Build markup for individual list item.
                    var $markup;
                    $markup = '<li data-action="update" ';
                    $markup += 'data-key="' + $key + '">';
                    $markup += '<i class="fa fa-level-up" alt="'+M.util.get_string('to_be_updated', 'local_rlsiteadmin')+'"></i>';
                    $markup += M.local_rlsiteadmin.data_addons[$key].display_name; // Addon name
                    $markup += '<i class="fa fa-times-circle rm-action" alt="'+M.util.get_string('remove_action', 'local_rlsiteadmin')+'"></i>';
                    $markup += '</li>';
                    // Insert markup.
                    $ul.append($markup);
                });

                // If buttons are disabled, enable them.
                if ($dropbtn.hasClass('disabled')) {
                    Y.log('dropbtn has disabled class');
                    $dropbtn.removeClass('disabled');
                }
                if ($updatebtn.hasClass('disabled')) {
                    Y.log('updatebtn has disabled class');
                    $updatebtn.removeClass('disabled');
                }
            }

            // Redo all click listeners in the list.
            $ul.delegate('click', remove_action, '.rm-action');

            Y.log('add length');
            Y.log(Object.keys(M.local_rlsiteadmin.data_actions.add).length);
        },

        /**
         * Handle the clicking of plugin buttons
         */
        addons_button_init: function() {
            /**
             * Handle the clicking of the install button
             *
             * @param event e The event triggered by clicking the install button
             */
            function button_click(e) {
                e.preventDefault();
                var $this = e.target,
                    name = $this.ancestor('.plugin.well').getAttribute('data-key'), // Get addon name.
                    action = $this.getAttribute('data-action');
                if ($this.hasClass('disabled')) {
                    return;
                }
                Y.log($this);
                // Get the addon object key to add to action list.

                Y.log('Button '+action+' started for '+name);
                // Add addon to actions array.
                if (action in M.local_rlsiteadmin.data_actions) {
                    M.local_rlsiteadmin.data_actions[action][name] = name;
                    Y.log(M.local_rlsiteadmin.data_actions);
                } else {
                    Y.log('Unknown action requested: '+action);
                }

                // Disable button for this plugin.
                $this.addClass('disabled');
                M.local_rlsiteadmin.action_dropdown_update();
            }

            /**
             * Handle the clicking of the do-the-actions button
             *
             * @param event e The event triggered by clicking the button
             */
            function confirm_actions(e) {
                e.preventDefault();

                var $confirm = '<ul>'+M.util.get_string('plugins_will_be_added', 'local_rlsiteadmin'),
                    $bodycontent,
                    plugin,
                    $status,
                    $report = '',
                    $dependency = {},
                    button;

                for (plugin in M.local_rlsiteadmin.data_actions.add) {
                    $confirm += '<li>'+plugin+'</li>';
                }
                $confirm += '</ul>';

                $confirm += '<ul>'+M.util.get_string('plugins_will_be_updated', 'local_rlsiteadmin');
                for (plugin in M.local_rlsiteadmin.data_actions.update) {
                    $confirm += '<li>'+plugin+'</li>';
                }
                $confirm += '</ul>';

                $confirm += '<ul>'+M.util.get_string('plugins_will_be_removed', 'local_rlsiteadmin');
                for (plugin in M.local_rlsiteadmin.data_actions.remove) {
                    $confirm += '<li>'+plugin+'</li>';
                }
                $confirm += '</ul>';

                Y.log($confirm);

                // Rebuild the modal each time, since the content of the modal is
                // so unique, and enabling and disabling the button would have to be
                // redone anyway.
                $bodycontent = '<div id="modal-content">';
                $bodycontent += '<h4>'+M.util.get_string('preparing_actions', 'local_rlsiteadmin')+'</h4>';
                $bodycontent += '<p>' + $confirm + '</p>';
                $bodycontent += '<div id="action-results"><i class="fa fa-spinner fa-spin fa-4"></i></div>';
                $bodycontent += '</div>';

                YUI().use("panel", function (Y) {
                    M.local_rlsiteadmin.plugin_modal = new Y.Panel({
                        srcNode: '<div></div>',
                        id: 'manage-actions-modal',
                        headerContent: M.util.get_string('actions_in_progress', 'local_rlsiteadmin'),
                        bodyContent: $bodycontent,
                        buttons: [
                            {
                                id: 'modal-confirm-button',
                                label: M.util.get_string('confirm', 'local_rlsiteadmin'),
                                section: 'footer',
                                action: function(e) {
                                    e.preventDefault();
                                    e.target.hide();
                                    Y.one('#manage-actions-modal .modal-cancel-button').set('label', M.util.get_string('close', 'local_rlsiteadmin'));
                                    do_actions(e);
                                },
                                classNames: 'modal-confirm-button',
                                disabled: true
                            },
                            {
                                id: 'modal-cancel-button',
                                label: M.util.get_string('cancel', 'local_rlsiteadmin'),
                                section: 'footer',
                                action: function(e) {
                                    e.preventDefault();
                                    M.local_rlsiteadmin.plugin_modal.hide();
                                    M.local_rlsiteadmin.plugin_modal.destroy();
                                },
                                classNames: 'modal-cancel-button',
                                disabled: true
                            },
                        ],
                        classNames: 'manage-actions-modal',
                        width: '60%',
                        height: 500,
                        zIndex: 10000,
                        centered: true,
                        modal: true,
                        visible: true,
                        render: true
                    });
                });

                // Check dependencies for each add action, and add that action and each dependency not yet installed.
                Y.Object.each(M.local_rlsiteadmin.data_actions.add, function($value, $key) {
                    Y.log('Loop through add actions.');

                    // For each dependency, if it's not installed in the addons object, add the dependency.
                    if ((typeof M.local_rlsiteadmin.data_addons[$key].cache === 'object') && ('dependencies' in M.local_rlsiteadmin.data_addons[$key].cache)) {
                        Y.Object.each(M.local_rlsiteadmin.data_addons[$key].cache.dependencies, function($dvalue, $dkey) {
                            if (($dkey in M.local_rlsiteadmin.data_addons) && !(M.local_rlsiteadmin.data_addons[$dkey].installed || ($dkey in M.local_rlsiteadmin.data_actions.add))) {
                                M.local_rlsiteadmin.data_actions.add[$dkey] = $dkey;
                                $dependency = {'source': $key, 'target': $dkey};
                                $report += '<li>'+M.util.get_string('dependency_will_be_added', 'local_rlsiteadmin', $dependency)+'</li>';
                            }
                        });
                    }
                });

                // Check dependencies for each update action, and add that action and each dependency not yet installed.
                Y.Object.each(M.local_rlsiteadmin.data_actions.update, function($value, $key) {
                    Y.log('Loop through update actions.');

                    // For each dependency, if it's not installed in the addons object, add.
                    if ((typeof M.local_rlsiteadmin.data_addons[$key].cache === 'object') && ('dependencies' in M.local_rlsiteadmin.data_addons[$key].cache)) {
                        Y.Object.each(M.local_rlsiteadmin.data_addons[$key].cache.dependencies, function($dvalue, $dkey) {
                            if (($dkey in M.local_rlsiteadmin.data_addons) && !(M.local_rlsiteadmin.data_addons[$dkey].installed || ($dkey in M.local_rlsiteadmin.data_actions.add))) {
                                M.local_rlsiteadmin.data_actions.update[$dkey] = $dkey;
                                $dependency = {'source': $key, 'target': $dkey};
                                $report += '<li>'+M.util.get_string('dependency_will_be_added', 'local_rlsiteadmin', $dependency)+'</li>';
                            }
                        });
                    }
                });

                // Loop through all remove actions.
                // Check for plugins that depend on the plugins being removed.
                Y.Object.each(M.local_rlsiteadmin.data_actions.remove, function($value, $key) {
                    Y.log('Loop through remove actions.');

                    // For every installed plugin, if it depends on the removed plugin also remove it.
                    Y.Object.each(M.local_rlsiteadmin.data_addons, function($rvalue, $rkey) {
                        if ((typeof $rvalue.dependencies !== 'undefined') && ($rvalue.dependencies[$key])) {
                            // Only alert if we're not already uninstalling the plugin.
                            if (!($rkey in M.local_rlsiteadmin.data_actions.remove)) {
                                M.local_rlsiteadmin.data_actions.remove[$rkey] = $rkey;
                                $dependency = {'source': $key, 'target': $rkey};
                                $report += '<li>'+M.util.get_string('dependency_will_be_removed', 'local_rlsiteadmin', $dependency)+'</li>';
                            }
                        }
                    });
                });

                if ($report.length <= 0) {
                    $report = '<li>'+M.util.get_string('no_dependencies', 'local_rlsiteadmin')+'</li>';
                }
                Y.log($report);
                $report = '<h4>'+M.util.get_string('dependencies', 'local_rlsiteadmin')+'</h4>\n<ul>'+$report+'</ul>';
                Y.one('#action-results').insert($report, 'before');

                M.local_rlsiteadmin.action_dropdown_update();

                Y.one('#modal-content .fa-spinner').hide();

                button = Y.one('#manage-actions-modal .modal-confirm-button');
                button.set('disabled', false);
                // Don't know why this isn't done automatically.  YUI bug?
                button.removeClass('yui3-button-disabled');

                button = Y.one('#manage-actions-modal .modal-cancel-button');
                button.set('disabled', false);
                // Don't know why this isn't done automatically.  YUI bug?
                button.removeClass('yui3-button-disabled');

                // Create status placeholder
                $status = '<h4 id="status-header"></h4>\n';
                $status += '<div id="status-bar" class="progress"><div class="bar" style="width: 0%;"></div></div>\n';
                Y.one('#action-results').insert($status, 'before');
                Y.one('#status-bar').hide();
            }

            /**
             * Handle the clicking of the confirm button for the do-the-actions display
             *
             * @param event e The event triggered by clicking the button
             */
            function do_actions(e) {
                e.preventDefault();

                /**
                 * Update the modal
                 *
                 * @param boolean $status The curent status.
                 */
                var percentagedone = 0;
                function update_modal(status) {
                    Y.log('update_modal');
                    var header = M.util.get_string('status_header_'+status, 'local_rlsiteadmin'),
                        minpercentage = 0,
                        maxpercentage = 2;

                    Y.one('#status-bar').show();

                    switch(status) {
                        case "waiting":
                            minpercentage = 2;
                            maxpercentage = 10;
                            break;

                        case "running":
                            minpercentage = 0;
                            maxpercentage = 90;
                            break;
                    }

                    if (percentagedone < minpercentage) {
                        percentagedone = minpercentage;
                    }
                    percentagedone = minpercentage + percentagedone + (maxpercentage - minpercentage - percentagedone) / 10;
                    Y.one('#status-bar .bar').setStyle("width", percentagedone+"%");

                    Y.one('#status-header').setHTML(header, 'before');
                    // Hide the spinner.

                    /*
                    // Enable the close button.
                    $button = Y.one('#manage-actions-modal button');
                    $button.set('disabled', false);
                    // Don't know why this isn't done automatically.  YUI bug?
                    $button.removeClass('yui3-button-disabled');
                    */
                }

                /**
                 * Finish off the modal
                 *
                 * @param boolean $success Whether the action dispatching script reported success.
                 * @param array $messages A list of messages to be printed with the result message.
                 */
                function finish_modal(success, messages) {
                    Y.log('finish_modal');
                    Y.log(success);
                    var result,
                        length,
                        msg = '',
                        warn = '',
                        i;

                    if (success) {
                        result = 'success';
                    } else {
                        result = 'failure';
                    }

                    // Remove status header and bar.
                    Y.one('#status-header').remove();
                    Y.one('#status-bar').remove();

                    // Display messages if there are any.
                    length = messages.length;
                    if (length > 0) {
                        for (i = 0; i < length; i += 1) {
                            msg += '<li>'+messages[i]+"</li>\n";
                        }
                        msg = '<ul>'+msg+"</ul>\n";
                    }

                    length = configs.length;
                    if (length > 0) {
                        for (i = 0; i < length; i += 1) {
                            warn += '<li>'+configs[i]+"</li>\n";
                        }
                        warn = '<ul>'+M.util.get_string('plugins_require_configuration', 'local_rlsiteadmin')+"</ul>\n"+
                               '<ul>'+warn+"</ul>\n"+
                               '<ul>'+M.util.get_string('plugins_need_help', 'local_rlsiteadmin')+"</ul>\n";
                    }

                    msg = '<h4>'+M.util.get_string(result, 'local_rlsiteadmin')+"</h4>\n"+
                          '<ul>'+M.util.get_string('actions_completed_'+result, 'local_rlsiteadmin')+"</ul>\n"+
                          msg+warn;
                    Y.one('#action-results').insert(msg, 'before');
                    // Hide the spinner.
                    Y.one('#modal-content .fa-spinner').hide();
                    // Enable the close button.
                    $button = Y.one('#manage-actions-modal button');
                    $button.set('disabled', false);
                    // Don't know why this isn't done automatically.  YUI bug?
                    $button.removeClass('yui3-button-disabled');
                }

                // Formulate AJAX request, make request, and write results to modal.
                // Build the query string sent in POST request.
                var data = '';
                var configs = [];

                // Loop through all add actions.
                Y.log('Loop through add actions.');
                Y.Object.each(M.local_rlsiteadmin.data_actions.add, function(value, key) {
                    Y.log(key+' -> '+value);
                    if (data.length > 0) {
                        data += '&';
                    }
                    data += 'add[]='+key;

                    // Check for GAO+ (5) plugins
                    if (5 === M.local_rlsiteadmin.data_addons[key].source) {
                        configs[configs.length] = key;
                    }
                });

                // Loop through all update actions.
                Y.log('Loop through update actions.');
                Y.Object.each(M.local_rlsiteadmin.data_actions.update, function(value, key) {
                    if (data.length > 0) {
                        data += '&';
                    }
                    data += 'update[]='+key;
                });

                // Loop through all remove actions.
                Y.log('Loop through remove actions.');
                Y.Object.each(M.local_rlsiteadmin.data_actions.remove, function(value, key) {
                    if (data.length > 0) {
                        data += '&';
                    }
                    data += 'remove[]='+key;
                });

                Y.log('data = ' + data);

                // AJAX to send actions.
                YUI().use("io-base", function(Y) {
                    var url = M.cfg.wwwroot+'/local/rlsiteadmin/mass/action.php';
                    var cfg = {
                        method: 'POST',
                        data: data,
                        on: {
                            success: function(id, o) {
                                Y.log('do_actions success');

                                var $response = null;
                                YUI().use('json-parse', function (Y) {
                                    try {
                                        $response = Y.JSON.parse(o.responseText);
                                    }
                                    catch (e) {
                                        Y.log("Parsing failed.");
                                    }
                                });

                                // AJAX to send actions.
                                function check_status() {
                                    YUI().use("io-base", function(Y) {
                                        var url = M.cfg.wwwroot+'/local/rlsiteadmin/mass/addonstatus.php?addoncommand='+$response.file+'&hash='+$response.hash;
                                        var cfg = {
                                            method: 'GET',
                                            on: {
                                                success: function(id, o) {
                                                    var $status = null;
                                                    YUI().use('json-parse', function (Y) {
                                                        try {
                                                            $status = Y.JSON.parse(o.responseText);
                                                            var status = "waiting";
                                                            if ($status.running) {
                                                                status = "running";
                                                            } else if (!$status.running && $status.fileexists === false && $status.results) {
                                                                status = "completed";
                                                            }
                                                            if (status != "completed") {
                                                                update_modal(status);
                                                                setTimeout(function() {
                                                                    check_status();
                                                                }, 2000);
                                                            } else {
                                                                $response.results = $status.results;
                                                                complete_feedback();
                                                            }
                                                        }
                                                        catch (e) {
                                                            Y.log("Parsing failed.");
                                                        }
                                                    });
                                                },
                                                failure: function(c, o) {
                                                    if (o.status == '503') {
                                                        // Ignore failures caused by this site is upgrading messages.
                                                        setTimeout(function() {
                                                            check_status();
                                                        }, 2000);
                                                        return;
                                                    }
                                                    try {
                                                        $status = Y.JSON.parse(o.responseText);
                                                        complete_feedback();
                                                    } catch (e) {
                                                        // Some other error has occured. The progress bar will now stop moving.
                                                        Y.log("Parsing failed.");
                                                    }
                                                }
                                            }
                                        };
                                        $addons = Y.io(url, cfg);
                                    });
                                }
                                check_status();

                                function complete_feedback() {
                                    Y.log($response);
                                    var $success = false;
                                    // No all results have a $response.results returned. If messages exist generate one.
                                    if (typeof $response.results === 'undefined') {
                                        $response.results = '';
                                    }
                                    if (typeof $response.messages === 'undefined') {
                                        $response.messages = [];
                                    }
                                    if (typeof $response.results === '' && typeof $response.messages[0] != 'undefined') {
                                        $response.results = '';
                                        for (var i = 0; i < $response.messsages.length; i++) {
                                            $response.results += $response.messsages[i]+'<br>';
                                        };
                                    }
                                    $response.results = $response.results.toLowerCase();
                                    if (!$response.messages[0] &&
                                                ($response.results === undefined ||
                                                ($response.results.indexOf("unable") == -1 && $response.results.indexOf("failed") == -1))) {
                                        $success = true;
                                        Y.log('JSON response is success.');
                                    } else {
                                        Y.log('JSON response reports a failure.');
                                    }

                                    // Empty all plugin actions
                                    if (Object.keys(M.local_rlsiteadmin.data_actions.add).length > 0) {
                                        Y.Object.each(M.local_rlsiteadmin.data_actions.add, function($value, $key) {
                                            delete M.local_rlsiteadmin.data_actions.add[$key];
                                        });
                                    }
                                    if (Object.keys(M.local_rlsiteadmin.data_actions.remove).length > 0) {
                                        Y.Object.each(M.local_rlsiteadmin.data_actions.remove, function($value, $key) {
                                            delete M.local_rlsiteadmin.data_actions.remove[$key];
                                        });
                                    }
                                    if (Object.keys(M.local_rlsiteadmin.data_actions.update).length > 0) {
                                        Y.Object.each(M.local_rlsiteadmin.data_actions.update, function($value, $key) {
                                            delete M.local_rlsiteadmin.data_actions.update[$key];
                                        });
                                    }
                                    M.local_rlsiteadmin.action_dropdown_update();

                                    finish_modal($success, $response);
                                }
                            },
                            failure: function() {
                                Y.log('Action failure.');
                                // TODO: Inject a failure message into the panel.
                                // Enable the close button.
                                Y.one('#manage-actions-modal button').set('disabled', false);
                                // Hide the spinner.
                                Y.one('#modal-content fa-spinner').hide();
                            }
                        }
                    };
                    $addons = Y.io(url, cfg);
                });
            }

            /**
             * Handle the clicking of the plugin queue button
             *
             * @param event e The event triggered by clicking the button
             */
            function toggle_actions(e) {
                Y.log('Toggle the actions!');
                var $actions = Y.one('ul#plugin-actions');

                if (!Y.one('button.plugin-actions').hasClass('disabled') && $actions.getStyle('display') === 'none') {
                    $actions.setStyle('display', 'block');
                } else {
                    $actions.setStyle('display', 'none');
                }
                $actions.get('parentNode').toggleClass('open');
            }

            var $plugins = Y.one('.plugins');
            $plugins.delegate('click', button_click, 'button.btn-install');
            $plugins.delegate('click', button_click, 'button.btn-remove');
            $plugins.delegate('click', button_click, 'button.btn-update');
            Y.one('#go-update-plugins').on('click', confirm_actions);
            Y.one('button.plugin-actions').on('click', toggle_actions);
            Y.one('ul#plugin-actions').on('mouseleave', toggle_actions);
        },

        /**
         * Handle the clicking of upgrade radio buttons.
         */
        addons_upgrademethod_init: function() {
            var update = '';
            Y.Object.each(M.local_rlsiteadmin.data_addons, function($value, $key) {
                var name = String($value.name).replace(' ', '_'),
                    types = ['manual', 'auto'],
                    pluginname = $value.type + '_' + name,
                    id,
                    i;
                for (i = 0; i < types.length; i++) {
                    id = Y.one('#'+pluginname+'_'+types[i]);
                    if (id) {
                        Y.log('select event for ' + pluginname+'_'+types[i]);
                        id.delegate('click', function (e) {
                            var value = e.target.get('value'),
                                pluginname = e.target.get('id').replace(/_manual$|_auto$/, '');
                            Y.log('upgrade method set to ' + value + ' for ' + pluginname);
                            M.local_rlsiteadmin.addons_upgrademethod_send(pluginname, value);
                        }, 'input[type=radio]');
                    }
                }
            });
            Y.one("#upgradesettings_all_auto").on('click', function (e) {
                var update = '';
                Y.all('.pluginupgradesetting .upgradeauto input').each(function (e) {
                    if (!e.get('checked')) {
                        update = update + ',' + e.get('id').replace(/_manual$|_auto$/, '');
                    }
                });
                Y.all('.pluginupgradesetting .upgradeauto input').each(function (e) {
                    if (!e.get('checked')) {
                        e.set('checked', 'true');
                    }
                });
                M.local_rlsiteadmin.addons_upgrademethod_send(update, 'auto');
            });
            Y.one("#upgradesettings_all_manual").on('click', function () {
                var update = '';
                Y.all('.pluginupgradesetting .upgrademanual input').each(function (e) {
                    if (!e.get('checked')) {
                        update = update + ',' + e.get('id').replace(/_manual$|_auto$/, '');
                    }
                });
                Y.all('.pluginupgradesetting .upgrademanual input').each(function (e) {
                    if (!e.get('checked')) {
                        e.set('checked', 'true');
                    }
                });
                M.local_rlsiteadmin.addons_upgrademethod_send(update, 'manual');
            });
        },

        /**
         * Fetch the addons from the addons script
         */
        addons_fetch: function() {
            Y.log('addons_fetch');
            M.local_rlsiteadmin.ajax_fetch('addon', M.local_rlsiteadmin.addons_update);
        },

        /**
         * Update the addons
         */
        addons_update: function(response) {
            Y.log('addons_update');
            M.local_rlsiteadmin.data_addons = response;
            disp = $('input[name="display-order"]:checked').val();
            M.local_rlsiteadmin.addons_sort(disp);
            M.local_rlsiteadmin.addons_write();
            M.local_rlsiteadmin.filter_plugins();
        },

        /**
         * Handle the rating of plugins
         */
        addons_rate: function() {
            /**
             * Handle the event when someone clicks on a star
             */
            function starclick(e) {
                // Y.log('starclick function');
                e.preventDefault();
                $this = e.target;

                // Required because for whatever reason the click event is calling the callback
                // function hundreds of times for each click.
                if (!M.local_rlsiteadmin.plugin_rate_node || $this !== M.local_rlsiteadmin.plugin_rate_node) {
                    // If no existing node, or $this is a new node, start over.
                    Y.log('M.local_rlsiteadmin.plugin_rate_node is null');
                    // Y.log($this);
                    // Y.log(M.local_rlsiteadmin.plugin_rate_node);
                    M.local_rlsiteadmin.plugin_rate_node = $this;
                    // Y.log('M.local_rlsiteadmin.plugin_rate_node after saved $this into it:');
                    // Y.log(M.local_rlsiteadmin.plugin_rate_node);

                    // Get full set of clicked star plus siblings.
                    $starset = M.local_rlsiteadmin.plugin_rate_node.get('parentNode').get('children').filter('.fa');

                    // Get star index.
                    $clickedindex = $starset.indexOf(M.local_rlsiteadmin.plugin_rate_node);

                    // Fill star and all stars of index below it.
                    $fillstars = $starset.slice(0, $clickedindex + 1);

                    // Empty all stars with index above it.
                    $emptystars = $starset.slice($clickedindex + 1, $starset.size() + 1);

                    // Rating is 1-based: 1-5.
                    var $rating = $clickedindex + 1;
                    // Get the addon object key to send with rating call.
                    var $addon = M.local_rlsiteadmin.plugin_rate_node.ancestor('.plugin.well').getAttribute('data-key'); // Get addon name.

                    $starset.each(function($star) {
                        // Remove existing classes if present.
                        if($star.hasClass('fa-star-o')) {
                            $star.removeClass('fa-star-o');
                        }
                        if($star.hasClass('fa-star')) {
                            $star.removeClass('fa-star');
                        }

                        // Apply appropriate classes and call send rating.
                        if ($starset.indexOf($star) === $starset.size() - 1) {
                            $emptystars.addClass('fa-star-o');
                            $fillstars.addClass('fa-star');
                            M.local_rlsiteadmin.addons_rating_send($addon, $rating);
                        }
                    });
                } else {
                    // If node has already been acted upon, exit.
                    Y.log('Conditions not met. M.local_rlsiteadmin.plugin_rate_node not null or matches existing e.target.');
                    return false;
                }
            }

            Y.one('.plugins').delegate('click', starclick, '.rate-plugin .fa-star-o, .rate-plugin .fa-star');
        },

        /**
         * Send plugin ratings to the plugin rating script.
         *
         * @param string $addon The addon name
         * @param int $rating The numberical rating value (1-5)
         */
        addons_rating_send: function($addon, $rating) {
            Y.log('addon = '+$addon);
            Y.log('rating = '+$rating);

            // AJAX to send rating.
            YUI().use("io-base", function(Y) {
                var url = M.cfg.wwwroot+'/local/rlsiteadmin/mass/rate.php';
                var cfg = {
                    method: 'POST',
                    data: 'addon='+$addon+'&rating='+$rating,
                    on: {
                        success: function(id, o) {
                            Y.log('success');
                            var $response = null;
                            YUI().use('json-parse', function (Y) {
                                try {
                                    $response = Y.JSON.parse(o.responseText);
                                }
                                catch (e) {
                                    Y.log("Parsing failed.");
                                }
                            });
                            Y.log('$response = ');
                            Y.log($response);
                        },
                        failure: function() {
                            Y.log('Ratings failure.');
                        }
                    }
                };
                $addons = Y.io(url, cfg);
            });
        },
        /**
         * Send plugin update settings.
         *
         * @param string $addon The addon name
         * @param int $rating The numberical rating value (1-5)
         */
        addons_upgrademethod_send: function($addon, $method) {
            Y.log('addon = '+$addon);
            Y.log('method = '+$method);

            // AJAX to send rating.
            YUI().use("io-base", function(Y) {
                var url = M.cfg.wwwroot+'/local/rlsiteadmin/mass/pluginsettings.php';
                var cfg = {
                    method: 'POST',
                    data: 'addon='+$addon+'&upgrademethod='+$method,
                    on: {
                        success: function(id, o) {
                            Y.log('success');
                            var $response = null;
                            YUI().use('json-parse', function (Y) {
                                try {
                                    $response = Y.JSON.parse(o.responseText);
                                } catch (e) {
                                    Y.log("Parsing failed. $response = ");
                                    Y.log($response);
                                }
                            });
                        },
                        failure: function() {
                            Y.log('Update settings failure.');
                        }
                    }
                };
                $addons = Y.io(url, cfg);
            });
        },
        addons_sort: function (disp) {
            var arrayLength,
                i;
            /**
             * Handle sorting the display of the plugins
             */
            function moveObjectElement(currentKey, afterKey, obj) {
                var result = {},
                    val = obj[currentKey],
                    next = -1,
                    i = 0;
                delete obj[currentKey];
                if(typeof afterKey === 'undefined' || afterKey == null) {
                    afterKey = '';
                }
                $.each(obj, function(k, v) {
                    if((afterKey === '' && i === 0) || next === 1) {
                        result[currentKey] = val;
                        next = 0;
                    }
                    if(k === afterKey) { next = 1; }
                        result[k] = v;
                        ++i;
                    }
                );
                if(next === 1) {
                    result[currentKey] = val;
                }
                if(next !== -1) {
                    return result;
                } else {
                    return obj;
                }
            }
            Y.log('addons_sort');
            /**
             *  Get the key from the Javascript object
             */

            var keys=[];
            switch(disp){
                case 'alpha':
                    Y.Object.each(M.local_rlsiteadmin.data_addons, function($value, $key) {
                        $k = $key;
                        $disp = $value.display_name;
                        $disp = M.local_rlsiteadmin.capitalise_firstletter($disp);
                        keys.push([$k,$disp]);
                    });
                    break;
                case 'type':
                    Y.Object.each(M.local_rlsiteadmin.data_addons, function($value, $key) {
                        $k = $key;
                        $disp = $value.type;
                        keys.push([$k,$disp]);
                    });
                    break;
                case 'release':
                    Y.Object.each(M.local_rlsiteadmin.data_addons, function($value, $key) {
                        $k = $key;
                        $disp = $value.cache.version ? $value.cache.version : 0;
                        keys.push([$k,$disp]);
                    });
                    break;
           }
           if (keys.length > 0) {
               /**
                * Sort based on key
                */
               if (disp === 'release') {
                   keys.sort(function(a,b) { return (a[1] > b[1] ? -1 : (a[1] < b[1] ? 1 : 0)); });
               } else {
                   keys.sort(function(a,b) { return (a[1] < b[1] ? -1 : (a[1] > b[1] ? 1 : 0)); });
               }

               /**
                * Re-order Javascript object
                */
               prev = keys[0][0];
               M.local_rlsiteadmin.data_addons = moveObjectElement(prev, '', M.local_rlsiteadmin.data_addons);
               arrayLength = keys.length;
               for (i = 1; i < arrayLength; i++) {
                   newkey = keys[i][0];
                   M.local_rlsiteadmin.data_addons = moveObjectElement(newkey, prev, M.local_rlsiteadmin.data_addons);
                   prev = newkey;
               }
           }
       },
        /**
         * Write the addons to the list array of the page.
         */
        addons_write: function() {
            // Hide spinner
            $tothtml='';
            Y.one(".plugins .loading-spinner").hide();
            $newhtml = '<div class="display-plugins">';
            Y.one('.plugin-select .plugins').insert($newhtml);
            Y.Object.each(M.local_rlsiteadmin.data_addons, function($value, $key) {
                // Plugin statuses
                var installed = $value.installed ? $value.installed : false,
                    missing = $value.missing ? $value.missing : false,
                    paid = $value.paid ? $value.paid : false,
                    updateable = $value.upgradeable ? $value.upgradeable : false,
                    cached = $value.cached ? $value.cached : false,
                    locked = $value.locked ? $value.locked : false,
                    $displayname = $value.display_name ? $value.display_name : M.util.get_string('plugin_name_not_available', 'local_rlsiteadmin'),
                    $description = $value.description ? $value.description :  M.util.get_string('plugin_description_not_available', 'local_rlsiteadmin'),
                    $myrating = $value.myrating ? $value.myrating : 0,
                    $rating = $value.rating ? $value.rating : 0,
                    versions = $value.moodleversions ? $value.moodleversions : '',
                    $type = $value.type ? $value.type : '1',
                    $typeclass = ' type-'+$type, // M.local_rlsiteadmin.plugin_types[$type].type;
                    $nameclass = ' name-'+String($value.name).replace(' ', '_'),
                    $datakey = 'data-key="'+String($key).replace(' ', '_')+'" ',
                    $datainstalled = 'data-installed="'+String(installed).replace(' ', '_')+'" ',
                    $dataupdateable = 'data-updateable="'+String(updateable).replace(' ', '_')+'" ',
                    $datacached = 'data-cached="'+String(cached).replace(' ', '_')+'" ',
                    $datatype = 'data-type="'+String($type).replace(' ', '_')+'" ',
                    buttons = ['install'],
                    buttonmarkups = [],
                    markup = '',
                    icon = ['fa', 'fa-plus'],
                    key,
                    i;

                Y.log($key+' install (default)');
                if (locked) {
                    Y.log('locked');
                    buttons[0] = 'locked';
                } else if (missing) {
                    buttons[0] = 'repair';
                    buttons[1] = 'uninstall';
                    Y.log('missing');
                } else if (installed) {
                    buttons[0] = 'uninstall';
                    if (updateable) {
                        buttons[1] = 'update';
                    }
                    Y.log('installed');
                } else if (paid) {
                    Y.log('paid');
                    buttons[0] = 'pricing';
                }

                for (i = 0; i < buttons.length; i += 1) {
                    var string = 'add';
                    var buttonclass = ['btn', 'btn-block', 'btn-install', 'btn-success'];
                    var action = 'add';
                    var type = 'button';

                    switch (buttons[i]) {
                        case 'install':
                            break;
                        case 'repair':
                            string = 'repair';
                            icon[1] = 'fa-wrench';
                            buttonclass[3] = 'btn-primary';
                            break;
                        case 'locked':
                            // This is not a button, it's an indicator that this plugin has issues.
                            string = 'locked';
                            action = '';
                            type = 'div';
                            // Remove unneeded classes
                            buttonclass = ['text-center'];
                            icon[1] = 'fa-lock';
                            break;
                        case 'uninstall':
                            string = 'remove';
                            buttonclass[2] = 'btn-remove';
                            buttonclass[3] = 'btn-danger';
                            action = 'remove';
                            icon[1] = 'fa-times';
                            break;
                        case 'update':
                            string = 'update';
                            buttonclass[2] = 'btn-update';
                            buttonclass[3] = 'btn-primary';
                            action = 'update';
                            icon[1] = 'fa-level-up';
                            break;
                        case 'pricing':
                            // This is not really a button so doesn't do anything.
                            string = 'for_pricing';
                            action = '';
                            type = 'div';
                            // Remove unneeded classes
                            buttonclass = ['text-center'];
                            icon = [];
                            break;
                        default:
                            // install
                            break;
                    }
                    var text = M.util.get_string(string, 'local_rlsiteadmin');

                    markup = '<'+type+' type="button" class="'+buttonclass.join(' ')+'" data-action="'+action+'">';
                    markup += '<i class="'+icon.join(' ')+'"></i>';
                    markup += text;
                    markup += '</'+type+'>';
                    buttonmarkups[buttonmarkups.length] = markup;
                }

                // Build markup for plugin average rating.
                $ratingmarkup = '<div class="avg-rating"><h5>'+M.util.get_string('average_rating', 'local_rlsiteadmin')+'</h5>';
                for (i = 1; i < 6; i++) {
                    if (i <= $rating) {
                        $ratingmarkup += '<i class="fa fa-star"></i>';
                    } else {
                        $ratingmarkup += '<i class="fa fa-star-o"></i>';
                    }
                }
                $ratingmarkup += '</div>';

                icon = 'fa-plug';
                if ($type in M.local_rlsiteadmin.plugin_types) {
                    icon = M.local_rlsiteadmin.plugin_types[$type].icon;
                }

                // Build markup for Available versions.
                versionmarkup = '';
                for (key in versions) {
                    versionmarkup += '<span class="available-version">';
                    if (versions[key] === "Y") {
                        versionkey = key;
                    } else {
                        versionkey = '&nbsp;&nbsp;&nbsp;';
                    }
                    versionmarkup += versionkey+'</span>';
                }
                availablemarkup = '<div class="available"><h5>'+M.util.get_string('available_for', 'local_rlsiteadmin')+'</h5>';
                availablemarkup += versionmarkup;
                availablemarkup += '</div>';

                // Build markup for plugin title and description.
                $itemmarkup = '<ul class="media-list"><li class="media">';
                $itemmarkup += '<i class="pull-left plugin-type fa '+icon+'"></i>';
                $itemmarkup += '<div class="media-body">';
                $itemmarkup += '<h3 class="media-heading">'+$displayname+'</h3>';
                $itemmarkup += '<p>'+$description+'</p>';
                $itemmarkup += '<div class="rate-plugin">';
                $itemmarkup += '<p><strong>'+M.util.get_string('add_or_update_rating_bold', 'local_rlsiteadmin')+
                               '</strong>'+M.util.get_string('add_or_update_rating_normal', 'local_rlsiteadmin')+'</p>';
                for (i = 1; i < 6; i++) {
                    if (i <= $myrating) {
                        $itemmarkup += '<i class="fa fa-star" title="'+i+' stars"></i>';
                    } else {
                        $itemmarkup += '<i class="fa fa-star-o" title="'+i+' stars"></i>';
                    }
                }
                if ($value.installed) {
                    $value.upgrademethod = $value.upgrademethod ? $value.upgrademethod : 'manual';
                } else {
                    $value.upgrademethod = 'auto';
                }
                var name = String($value.name).replace(' ', '_');
                var pluginname = $value.type + '_' + name;
                if ($value.upgrademethodchangeable && $value.installed) {
                    pluginame = 'addonsetting_'+pluginname;
                    $itemmarkup += '<p class="pluginupgradesetting"><strong>'+M.util.get_string('updatesettings', 'local_rlsiteadmin')+
                                   '</strong>';
                    var auto = '';
                    var manual = '';
                    if ($value.upgrademethod == 'manual') {
                        manual = ' checked="checked" ';
                    } else {
                        auto = ' checked="checked" ';
                    }
                    $itemmarkup += '&nbsp;<label class="upgrademanual" for="'+pluginname +'_manual"><input class="upgrademanual" type="radio" name="upgrademethod_' +pluginname+'" id="'+pluginname+'_manual" value="manual" '+manual+'/>';
                    $itemmarkup += M.util.get_string('updatesettings_manual', 'local_rlsiteadmin')+'</label>';
                    $itemmarkup += '&nbsp;<label class="upgradeauto" for="'+pluginname+'_auto"><input class="upgradeauto" type="radio" name="upgrademethod_'+pluginname+'" id="'+pluginname+'_auto" value="auto" '+auto+'/>';
                    $itemmarkup += M.util.get_string('updatesettings_auto', 'local_rlsiteadmin')+'</label>';
                }

                $itemmarkup += '</div>';
                $itemmarkup += '</div>';
                $itemmarkup += '</li></ul>';


                $html = '<div class="clearfix plugin well'+$typeclass+$nameclass+'" '+$datakey+
                        $datainstalled+$dataupdateable+$datacached+$datatype+' style="display: none;" hidden="hidden">';
                $html += '<div class="choose">';
                $html += buttonmarkups.join(' ');
                $html += $ratingmarkup;
                $html += availablemarkup;
                $html += '</div>';

                $html += $itemmarkup;

                $html += '</div>';
                $tothtml += $html;
                Y.one('.plugin-select .plugins').insert($html);

            });

            $newhtml = "</div>";
            Y.one('.plugin-select .plugins').insert($newhtml);
            // Init rate plugins *after* the plugins are printed to the page.
            M.local_rlsiteadmin.addons_rate();
            // Init action buttons *after* the plugins are printed to the page.
            M.local_rlsiteadmin.addons_button_init();
            // Init radio buttons for upgrade method.
            M.local_rlsiteadmin.addons_upgrademethod_init();
        },

        /**
         * Fetch the addons from the addons script
         */
        ajax_fetch: function(type, callback) {
            Y.log('ajax_fetch');
            YUI().use("io-base", function(Y) {
                $url = M.cfg.wwwroot+'/local/rlsiteadmin/mass/addons.php?type='+type+'list';
                Y.log($url);

                /**
                 * Handler for returned "complete" status
                 */
                function complete() {
                    Y.log('complete');
                }

                /**
                 * Handler for returned "success" status
                 *
                 * @param string id Not used
                 * @param object o A YUI object containing the response text
                 */
                function success(id, o) {
                    Y.log('ajax_fetch success');
                    var response = null;
                    // Filter out any PHP warnings before or after the output.
                    var match = o.responseText.match(/\{"(addons|groups)":[\[{].*[\]}]}/m);
                    if (match === null) {
                        Y.log("Unable to load data from "+$url+": "+o.responseText);
                        return;
                    }
                    Y.log(match);
                    YUI().use('json-parse', function (Y) {
                        try {
                            response = Y.JSON.parse(match[0]);
                        }
                        catch (e) {
                            Y.log("Parsing failed.");
                            Y.log(e);
                        }
                    });
                    callback(response['addons']);
                }

                /**
                 * Handler for returned "failure" status
                 *
                 * @param array args Arguments passed to the failure function
                 */
                function failure(args) {
                    Y.log('Failure: '+args[0]);
                }
                Y.on('io:complete', complete, Y, []);
                Y.on('io:success', success, Y, []);
                Y.on('io:failure', failure, Y, [M.util.get_string('ajax_request_failed', 'local_rlsiteadmin')]);
                $addons = Y.io($url);
            });
        },

        /**
         * Capitalise the first letter of the string.
         *
         * @param string The string that needs it's first letter capitalised.
         */
        capitalise_firstletter: function(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        },

        /**
         * Initialize the filtering and rating systems.
         */
        filter_init: function() {
            // Inits functions for filtering and rating plugins.
            // Called when the plugins list and filter controls are first shown.
            M.local_rlsiteadmin.filter_clear();
            M.local_rlsiteadmin.filter_text();
            M.local_rlsiteadmin.addons_fetch();
            M.local_rlsiteadmin.groups_fetch();
        },

        /**
         * Add a filter to the UI.
         *
         * @param string $filterstring The filter string
         * @param string $mode The filter mode
         * @param string $refine The refine value
         */
        filter_add: function(filterstring, mode, refine) {
            // Called when a filter is added by any means.
            // If filters list not displayed, display it.
            $labelsbox = Y.one('#labels-box');
            if ($labelsbox.getStyle('display') === "none") {
                $labelsbox.setStyle('display', 'block');
            }
            // Get all of the displayed filters.
            $filters = Y.all('#filter-labels span.badge');
            filterlist = $filters.get('text');
            // Gather all displayed filter labels into an array.
            var labelarr = [];
            Y.Array.each(filterlist, function(value) {
                labelarr.push(value.trim());
            });
            prefix = M.local_rlsiteadmin.capitalise_firstletter(mode) + ' | ';
            // If the filter is not yet displayed, display it.
            if (labelarr.indexOf(prefix + filterstring) <= -1) {
                labelmarkup = '<span class="badge" data-filter-mode="'+mode+'" data-filter-refine="'+refine+'">'+
                    prefix+filterstring+
                    '<i class="fa fa-times" alt="'+M.util.get_string('remove_filter', 'local_rlsiteadmin')+'"></i></span>';
                if ($filters.length >= 1) {
                    // If there are other filters, add after the last one.
                    $item = $filters.item(filterlist.length -1).insert(labelmarkup, 'after');
                } else {
                    // Otherwise, add inside the parent container.
                    $item = Y.one('#filter-labels').insert(labelmarkup);
                }
                M.local_rlsiteadmin.filter_plugins();
                M.local_rlsiteadmin.filter_remove();
                M.local_rlsiteadmin.filter_block_show();
                return $item;
            } else {
                return false;
            }
        },

        /**
         * Code that is called when a filter is clicked on.
         */
        filter_column: function() {
            Y.log('Setting up filter column.');
            // Called when filter in filter checkbox is clicked.
            Y.all('#filter-form input[type="checkbox"]').on('change', function(e) {
                Y.log('Checkbox change');
                // Capture target.
                var $target = Y.one(e.currentTarget);
                // Capture the settings of the checkbox.
                var mode = $target.getAttribute('data-filter-mode');
                var refine = $target.getAttribute('data-filter-refine');
                var value = false;
                if ($target.get('checked')) {
                    value = true;
                }
                Y.log('$mode = '+mode+' $refine = '+refine+' value = '+value);
                M.local_rlsiteadmin.data_filters[mode][refine] = value;
                M.local_rlsiteadmin.filter_plugins();
            });

            Y.all('#filter-form input[type="radio"]').on('change', function(e) {
            // Called when radio button for sorting is clicked.
                var $target = Y.one(e.currentTarget);
                var display = $target.getAttribute('value');
                M.local_rlsiteadmin.addons_fetch();
                M.local_rlsiteadmin.addons_sort(display);
                M.local_rlsiteadmin.addons_write();
                $tothtml='<div class="display-plugins">'+ $tothtml+ '</div>';
                $("div.display-plugins").replaceWith($tothtml);
            });
            Y.all('#filter-form div.toggle').on('click', function(e) {
                e.preventDefault();
                var $target = Y.one(e.currentTarget);
                var $icon = $target.one('.fa');
                $icon.toggleClass('fa-chevron-down');
                $icon.toggleClass('fa-chevron-right');
                var $parent = $target.ancestor(".block");
                var $content = $parent.one("> .content");
                $content.toggleView();
            });
        },

        /**
         * Clear the added filters.
         */
        filter_clear: function() {
            $clearbutton = Y.one('button#clear-filters');
            $clearbutton.on('click', function() {
                Y.all('#filter-labels span.badge').remove(true);
                M.local_rlsiteadmin.filter_plugins();
                M.local_rlsiteadmin.filter_block_hide();
            });
        },

        /**
         * Displays the filter block if there are filters.
         */
        filter_block_show: function() {
            if (Y.all('#filter-labels span.badge').size() >= 1) {
                Y.one('#labels-box').show(true);
            }
        },

        /**
         * If there are no filters, hide the filter block.
         */
        filter_block_hide: function() {
            if (Y.all('#filter-labels span.badge').size() <= 0) {
                Y.one('#labels-box').hide(true);
                // Also re-display all plugins.
                M.local_rlsiteadmin.filter_plugins();
            }
        },

        /**
         * Filter the plugins based on the selected filters.
         */
        filter_plugins: function() {
            Y.log('filter_plugins');
            // Hide addons.
            Y.all('.plugins .plugin.well').hide(true);
            Y.log(M.local_rlsiteadmin.data_filters);
            // Addons object to manipulate.
            var addons = JSON.parse(JSON.stringify(M.local_rlsiteadmin.data_addons));
            Y.Object.each(addons, function(addon, key) {
                addon.filtered = false;
            });
            Y.log('Start filtering');
            Y.log(addons);

            /**
             * Get the list of filters of the specified type
             *
             * @param string type The type of checkbox filters to collect
             * @returns array All of the filters that enabled of the specified type.
             */
            function get_checkbox_filters(type) {
                var filters = {};
                var boxes = Y.all('#filter-form .'+type+' input[type=checkbox]');
                Y.log('Getting starting '+type+' filters');
                boxes.each(function(box) {
                    var name = box.getData('filter-refine');
                    var checked = box.get('checked');
                    Y.log('Checking filter '+name+'.');
                    if (checked) {
                        Y.log('Filter '+name+' is active!');
                        filters[name] = checked;
                    }
                });
                return filters;
            }

            /**
             *  Handle trust-level filters.
             */
            function filter_trust() {
                // If there are no type filters, move on to the
                // next set of filters.
                var trust = Y.one('#trust-filter').get('checked');
                if (!trust) {
                    Y.log('Trust filter disabled, moving on to type.');
                    return false;
                }
                Y.log('Trust filter enabled:');
                var hide;
                // Additive filtering. If match, add to object.
                Y.Object.each(addons, function(addon, key) {
                    hide = true;
                    if ((addon.source === 4) || (addon.source === 5)) {
                        hide = false;
                    }
                    if (hide) {
                        addon.filtered = true;
                    }
                });
                return true;
            }

            /**
             * Handle plugin group filters.
             */
            function filter_group() {
                var filters = get_checkbox_filters('groups');

                // If there are no group filters, move on to the next set of filters.
                if (Object.keys(filters).length <= 0) {
                    Y.log('Group filter list is empty, moving on to next.');
                    return false;
                }

                var filtered = {};
                Y.Object.each(addons, function(addon, key) {
                    filtered[key] = true;
                });

                var i = 0;
                // Only loop through the filters that are actually set
                Y.Object.each(filters, function(value, index) {
                    if (index in M.local_rlsiteadmin.data_groups) {
                        for (i = 0; i < M.local_rlsiteadmin.data_groups[index].plugins.length; i += 1) {
                            filtered[M.local_rlsiteadmin.data_groups[index].plugins[i]] = false;
                        }
                    }
                });

                Y.Object.each(filtered, function(value, key) {
                    if (value && !addons[key].filtered) {
                        addons[key].filtered = true;
                    }
                });

                return true;
            }

            /**
             * Handle plugin-status filters.
             */
            function filter_status() {
                var filters = get_checkbox_filters('statuses');

                // If there are no status filters, move on to the next set of filters.
                if (Object.keys(filters).length <= 0) {
                    Y.log('Status filter list is empty, moving on to next.');
                    return false;
                }

                Y.log('Status filters:');
                Y.log(filters);
                // Now flag plugins that do not match any of the selected statuses.
                var hide;
                Y.Object.each(addons, function(addon, key) {
                    hide = true;
                    // Only loop through the filters that are actually set
                    Y.Object.each(filters, function(value, index) {
                        if ((index === 'installed') && value && addon.installed) {
                            Y.log('type matches = '+value);
                            hide = false;
                        } else if ((index === 'notinstalled') && value && !addon.installed) {
                            Y.log('type matches = '+value);
                            hide = false;
                        } else if ((index === 'updateable') && value && addon.installed && addon.upgradeable) {
                            Y.log('type matches = '+value);
                            hide = false;
                        } else if ((index === 'repairable') && value && addon.installed && addon.missing) {
                            Y.log('type matches = '+value);
                            hide = false;
                        }
                    });
                    Y.log("Status filter for "+key+": "+hide);
                    if (hide) {
                        addon.filtered = true;
                    }
                });
                return true;
            }

            /**
             *  Handle plugin-type filters.
             */
            function filter_type() {
                var filters = get_checkbox_filters('types');

                // If there are no type filters, move on to the next set of filters.
                if (Object.keys(filters).length <= 0) {
                    Y.log('Type filter disabled, moving on to status.');
                    return false;
                }

                Y.log('Type filters enabled:');
                var hide;
                // Additive filtering. If match, add to object.
                Y.Object.each(addons, function(addon, key) {
                    hide = true;
                    Y.Object.each(filters, function(value, index) {
                        Y.log(addon.type+' === '+index);
                        if (value && (addon.type === index)) {
                            hide = false;
                        }
                    });
                    Y.log("Type filter for "+key+": "+hide);
                    if (hide) {
                        addon.filtered = true;
                    }
                });
                return true;
            }

            /**
             *  Handle plugin-string filters.
             */
            function filter_string() {
                // Get all of the displayed filters.
                var filters = Y.all('#filter-labels span.badge');
                M.local_rlsiteadmin.data_filters['string'] = {};
                filters.filter('[data-filter-mode="string"]').each(function(node) {
                    var refine = Y.one(node).getAttribute('data-filter-refine');
                    M.local_rlsiteadmin.data_filters['string'][refine] = true;
                });

                if (Object.keys(M.local_rlsiteadmin.data_filters.string).length <= 0) {
                    Y.log('String filter list is empty, done.');
                    return false;
                }
                Y.log('String filter enabled:');
                // Now remove all plugins not matching string from status object.
                var hide;
                Y.Object.each(addons, function(addon, key) {
                    // Establish the lowercase strings to search.
                    heading = String(addon.display_name).toLowerCase();
                    description = String(addon.description).toLowerCase();
                    name = String(key).toLowerCase();
                    // For each string, test for matches.
                    hide = false;
                    Y.Object.each(M.local_rlsiteadmin.data_filters.string, function(value, index) {
                        // Get lowercaps value of string
                        stringlowcaps = String(index).toLowerCase();
                        // Determine whether each contains the search string.
                        isinheading = heading.indexOf(stringlowcaps);
                        isindesc = description.indexOf(stringlowcaps);
                        isinname = name.indexOf(stringlowcaps);

                        if ((isinheading < 0) && (isindesc < 0) && (isinname < 0)) {
                            hide = true;
                        } else {
                            Y.log('string matches.');
                        }
                    });
                    Y.log("String filter for "+key+": "+hide);
                    if (hide) {
                        addon.filtered = true;
                    }
                });
                return true;
            }
            filter_trust();
            filter_group();
            filter_status();
            filter_type();
            filter_string();

            Y.Object.each(addons, function(value, key) {
                // Show addons not filtered out.
                newkey = String(key).replace(' ', '_');
                selector = '.plugin[data-key="'+newkey+'"]';
                var $obj = Y.one(selector);
                // This truthiness check is to ignore errors in JSON object.
                if ($obj) {
                    if (!value.filtered) {
                        // Show addon.
                        $obj.show(true);
                    }
                }
            });
        },

        /**
         * Remove a single filter.
         */
        filter_remove: function() {
            $removebutton = Y.all('#filter-labels i.fa-times');
            $removebutton.on('click', function(e) {
                var parent = e.target.get('parentNode');
                $target = parent.remove(true);
                M.local_rlsiteadmin.filter_plugins();
                M.local_rlsiteadmin.filter_block_hide();
            });
        },

        /**
         * Scripts the behavior of the filter input field.
         */
        filter_text: function() {
            $input = Y.one('input#plugin-filter');

            // Enter key adds the input contents to filter list.
            function enter_key_press() {
                value = Y.one('input#plugin-filter').get('value');
                if (value.length >= 1) {
                    M.local_rlsiteadmin.filter_add(value, 'string', value);
                    $input.set('value', '');
                }
                M.local_rlsiteadmin.filter_block_show();
            }

            // Select of input clears default input contents.
            $input.on('click', function(e) {
                var $input = e.target;

                $input.set('value', '');
                $input.on('key', enter_key_press, 'enter');
            });
        },

        /**
         * Fetch the groups from the addons script
         */
        groups_fetch: function() {
            Y.log('groups_fetch');
            M.local_rlsiteadmin.ajax_fetch('group', M.local_rlsiteadmin.groups_update);
            M.local_rlsiteadmin.groups_write();
        },

        /**
         * Update the groups from the addons script
         */
        groups_update: function(response) {
            Y.log('groups_fetch');
            M.local_rlsiteadmin.data_groups = response;
            M.local_rlsiteadmin.groups_write();
            // Once the group filters have been written, hook them up.
            M.local_rlsiteadmin.filter_column();
        },

        /**
         * Write the addons to the list array of the page.
         */
        groups_write: function() {
            // Hide spinner
            Y.one('#filter-form .groups .loading-spinner').hide();
            // Inject addon HTML into page.
            Y.Object.each(M.local_rlsiteadmin.data_groups, function(value, key) {
                var displayname = value.display_name ? value.display_name : M.util.get_string('group_name_not_available', 'local_rlsiteadmin'),
                    html = '<label class="checkbox"><input type="checkbox" data-filter-mode="group" data-filter-refine="'+value.name+'">'+displayname+'</label>';
                Y.one('#filter-form .groups .content').insert(html);
            });
        },

        /**
         * Perform the actions required when the user skips a site update.
         */
        update_continue_init: function() {
            // Actions performed if user skips site update.
            Y.one('#afterupdate').on('click', function() {
                Y.one('.site-update').hide(true, function() {
                    Y.one('.plugin-select').setStyle('display', 'block').hide().show(true);
                });
                M.local_rlsiteadmin.filter_init();
            });
        },

        /**
         * Perform the actions required when the user choose to update the site.
         */
        update_site_init: function() {
            // Actions performed if user chooses to update site.
            Y.one('#doupdate').on('click', function(e) {
                Y.log("Perform site sync clicked");
                // Disable buttons.
                e.target.setAttribute('disabled');
                Y.one('#skipupdate').setAttribute('disabled');

                // Show spinner.
                Y.one('.site-update-spinner').show(true);

                // Assign the items we need to manipulate later to variables.
                var $spinner = Y.one('.site-update-spinner .fa-spinner'),
                    $continue = Y.one('div.instr-continue'),
                    $after = Y.one('#afterupdate');

                // Call site update function
                YUI().use("io-base", function(Y) {
                    var url = M.cfg.wwwroot+'/local/rlsiteadmin/mass/refresh.php',
                        cfg = {
                            method: 'POST',
                            data: '',
                            on: {
                                success: function(id, o) {
                                    Y.log('Refresh ordered.');
                                    var $response = null;
                                    YUI().use('json-parse', function (Y) {
                                        try {
                                            $response = Y.JSON.parse(o.responseText);
                                        }
                                        catch (e) {
                                            Y.log("Parsing failed.");
                                            Y.log(e);
                                        }
                                    });
                                    Y.log('$response = ');
                                    Y.log($response);
                                    $spinner.hide(true);

                                    $continue.show(true);
                                    $after.show(true);
                                },
                                failure: function() {
                                    Y.log('Refresh failed.');
                                    $spinner.hide(true);
                                }
                            }
                    };
                    Y.io(url, cfg);
                });
                Y.log('Resume operation.');
            });
        },

        /**
         * Perform the actions required when the user skips a site update.
         */
        update_skip_init: function() {
            // Actions performed if user skips site update.
            Y.one('#skipupdate').on('click', function() {
                Y.one('.site-update').hide(true, function() {
                    Y.one('.plugin-select').setStyle('display', 'block').hide().show(true);
                });
                M.local_rlsiteadmin.filter_init();
            });
        },

        /**
         * Initialization function.
         */
        init: function() {
            Y.log('Init function called:');
            $update = Y.one('.site-update');
            if ($update) {
                Y.log('Update found.');
                // If plugin update available, display that interface
                M.local_rlsiteadmin.update_continue_init();
                M.local_rlsiteadmin.update_skip_init();
                M.local_rlsiteadmin.update_site_init();
            } else {
                // Display the plugin selection interface
                M.local_rlsiteadmin.filter_init();
            }
        }
    };

    return {
        /**
         * Initialization function
         */
        init: function() {
            M.local_rlsiteadmin.init();
        }
    };
});
