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
 * Remote Learner Archive Product
 *
 * @package   local_rlsiteadmin
 * @copyright 2014 Remote Learner Inc http://www.remote-learner.net
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.local_rlsiteadmin = M.local_rlsiteadmin || {};
M.local_rlsiteadmin = {

    /* @var object Contains the list of snapshots that the user is allowed to archive. */
    data_snapshots: {},

    /* @var object Contains the list of archives that the user is allowed to discard or restore. */
    data_archives: {},

    /* @var object Contains the list of restored archives. */
    data_restored: {},

    /* @var number The total number of slots available */
    total_slots: Number(Y.one('#rlarchives #numtotal').get("innerHTML")),

    /* @var number The actaul number of slots available */
    available_slots: Number,

    /* @var number The number of slots used */
    used_slots: Number,

    /* @var object The array used to check statuses of ajax calls */
    ajax_status: {
        'snapshots': false,
        'archives': false
    },

    /* @var object The modal that displays the confirmation message for actions */
    error_modal: null,

    /* @var object The listener reference object */
    bindings: {},

    /**
     * Fetch the snapshots from the archive api
     */
    snapshots_fetch: function() {
        Y.log('snapshots_fetch');
        YUI().use("io-base", function(Y) {

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
                Y.log('snapshots_fetch success');
                var response = null;
                // Filter out any PHP warnings before or after the output.
                var match = o.responseText.match(/\{"archiveapi":{.*\}}/m);
                if (match === null) {
                    Y.log("Unable to load snapshots!");
                    return;
                }
                YUI().use('json-parse', function (Y) {
                    try {
                        response = Y.JSON.parse(match[0]);
                    }
                    catch (e) {
                        Y.log("Parsing failed.");
                    }
                });
                M.local_rlsiteadmin.data_snapshots = response.archiveapi.snapshots;
                Y.log(M.local_rlsiteadmin.data_snapshots);
                M.local_rlsiteadmin.ajax_status['snapshots'] = true;
                M.local_rlsiteadmin.snapshots_write();
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
            var url = M.cfg.wwwroot+'/local/rlsiteadmin/archive/apicall.php?request=listsnapshots';
            Y.log(url);
            Y.io(url);
        });
    },

    /**
     * Write the snapshots to #rlsnapshots .list.
     */
    snapshots_write: function() {
        // Inject addon HTML into page.
        var counter = 0;
        Y.Object.each(M.local_rlsiteadmin.data_snapshots, function(value, key) {
            // Only print a snapshot if it has an expiration of >0 days.
            if (value.expiration > 0) {
                // The list where the snapshot will be added
                var $snapshostlist = Y.one('#rlsnapshots .list');
                // The template dom object
                var $snapshottemplate = Y.one('.archive-manager .templates .snapshot');
                // Append the template to the list.
                var $snapshot = $snapshottemplate.cloneNode(true).appendTo($snapshostlist);
                // Set snapshot data-snapshotid attribute.
                $snapshot.setAttribute('data-snapshotid', value.id);
                // Update the .date div in the snapshot.
                $snapshot.one('.date').setHTML(value.date);
                // Update the .expiration-date
                $snapshot.one('.expiration-date').setHTML(value.expiration);
                // Show the snapshot. Delay the removal of .fadedout for a gradual reveal effect.
                // Animation transiton are in the styles.css of this block.
                counter++;
                setTimeout(function() {
                    $snapshot.removeClass('fadedout');
                }, 70 * counter);
            }
        });

        // Hide the gear spinner that shows before snapshots are printed to the page.
        $spinner = Y.one('#rlsnapshots .spinner');
        $spinner.hide();

        // Check if all ajax calls are done.
        if (M.local_rlsiteadmin.ajax_status['snapshots'] && M.local_rlsiteadmin.ajax_status['archives']) {
            // Assign drag/drop and saving functionality.
            M.local_rlsiteadmin.drag_and_drop_init();
        }
    },

    /**
     * Fetch the archives from the archive api
     */
    archives_fetch: function() {
        Y.log('archives_fetch');
        YUI().use("io-base", function(Y) {

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
                Y.log('archives_fetch success');
                var response = null;
                // Filter out any PHP warnings before or after the output.
                var match = o.responseText.match(/\{"archiveapi":{.*\}}/m);
                if (match === null) {
                    Y.log("Unable to load archives!");
                    return;
                }
                YUI().use('json-parse', function (Y) {
                    try {
                        response = Y.JSON.parse(match[0]);
                    }
                    catch (e) {
                        Y.log("Parsing failed.");
                    }
                });
                M.local_rlsiteadmin.data_archives = response.archiveapi.archives;
                Y.log(M.local_rlsiteadmin.data_archives);
                M.local_rlsiteadmin.ajax_status['archives'] = true;
                M.local_rlsiteadmin.archives_write();
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
            var url = M.cfg.wwwroot+'/local/rlsiteadmin/archive/apicall.php?request=listarchives';
            Y.log(url);
            Y.io(url);
        });
    },

    /**
     * Write the archives to #rlarchives .list.
     */
    archives_write: function() {
        var counter = 0;

        // First show the done archives.
        Y.Object.each(M.local_rlsiteadmin.data_archives.done, function(value, key) {
            // The list where the archive will be added
            var $archivelist = Y.one('#rlarchives .list');
            // The template dom object
            var $archivetemplate = Y.one('.archive-manager .templates .archive');
            // Append the template to the list.
            var $archive = $archivetemplate.cloneNode(true).appendTo($archivelist);
            // Set archive data-archiveid attribute.
            $archive.setAttribute('data-archiveid', value.id);
            // Update the .date div in the snapshot.
            $archive.one('.date').setHTML(value.date);
            // Update the status.
            $archive.one('.status').setHTML(M.util.get_string('archive_status_ready', 'local_rlsiteadmin'));
        });

        // Now show the in-progress archives.
        Y.Object.each(M.local_rlsiteadmin.data_archives['in-progress'], function(value, key) {
            // The list where the archive will be added
            var $archivelist = Y.one('#rlarchives .list');
            // The template dom object
            var $archivetemplate = Y.one('.archive-manager .templates .archive');
            // Append the template to the list.
            var $archive = $archivetemplate.cloneNode(true).appendTo($archivelist);
            $archive.addClass("in-progress");
            // Set archive data-archiveid attribute.
            $archive.setAttribute('data-archiveid', value.id);
            // Update the .date div in the snapshot.
            $archive.one('.date').setHTML(value.date);
            // Update the status.
            $archive.one('.status').setHTML(M.util.get_string('archive_status_in_progress', 'local_rlsiteadmin'));
        });

        // Calculate and add available archive slots.
        M.local_rlsiteadmin.used_slots = M.local_rlsiteadmin.data_archives.done.length + M.local_rlsiteadmin.data_archives["in-progress"].length;
        M.local_rlsiteadmin.available_slots = M.local_rlsiteadmin.total_slots - M.local_rlsiteadmin.used_slots;
        Y.one('#rlarchives #numused').setHTML(M.local_rlsiteadmin.used_slots);
        var slots = Array();
        for (var i=0; i<M.local_rlsiteadmin.available_slots; i++) {
            slots.push(i);
        }
        Y.Object.each(slots, function(value, key) {
            // The list where the archive will be added
            var $archivelist = Y.one('#rlarchives .list');
            // The template dom object
            var $slottemplate = Y.one('.archive-manager .templates .slot');
            // Append the template to the list.
            var $slot = $slottemplate.cloneNode(true).appendTo($archivelist);
            // Show the snapshot. Delay the removal of .fadedout for a gradual reveal effect.
            // Animation transiton are in the styles.css of this block.
            /*
            counter++;
            setTimeout(function() {
                $slot.removeClass('fadedout');
            }, 70 * counter);
            */
        });

        // Assign discard archive functionality.
        M.local_rlsiteadmin.discard_archive_init();

        // Check if all ajax calls are done.
        if (M.local_rlsiteadmin.ajax_status['snapshots'] && M.local_rlsiteadmin.ajax_status['archives']) {
            // Assign drag/drop and saving functionality.
            M.local_rlsiteadmin.drag_and_drop_init();
        }

        // Now fetch the restored VMs.
        M.local_rlsiteadmin.restored_fetch();
    },

    /**
     * Assign drag and drop functionality to snapshots and archive slots.
     */
    drag_and_drop_init: function() {
        // Since all archives may be slots, add the slot class to each.
        $archives = Y.all('#rlarchives .archive');
        Y.each($archives, function(v, k) {
            v.addClass("slot-open");
        });

        YUI().use('dd-drop', 'dd-proxy', 'dd-constrain', function(Y) {

            // Assign drop functionality to .slot.
            var $slots = Y.one('#rlarchives .list').all('.slot-open');
            Y.each($slots, function(v, k) {
                var drop = new Y.DD.Drop({
                    node: v
                });
            });

            // Assign drag functionality to .snapshot
            var $snapshots = Y.one('#rlsnapshots .list').all('.snapshot');
            Y.each($snapshots, function(v, k) {
                var drag = new Y.DD.Drag({
                    node: v
                }).plug(Y.Plugin.DDProxy, {
                    moveOnEnd: false
                }).plug(Y.Plugin.DDConstrained, {
                    constrain2node: '.archive-manager'
                });
                drag.detach();
                drag.on('drag:start', function() {
                    var p = this.get('dragNode'),
                        n = this.get('node');
                        // Fade the original node back.
                        n.setStyle('opacity', .25);
                    // Copy the node in to the dragging object.
                    p.set('innerHTML', n.getHTML());
                    p.addClass('block snapshot');
                    p.setStyle('opacity', .65);
                });
                drag.on('drag:end', function() {
                    var n = this.get('node');
                    n.setStyle('opacity', '1');
                });
                drag.on('drag:drophit', function(e) {
                    // Pass $snapshot and $slot to setup function.
                    var $snapshot = e.drag.get('node');
                    var $slot = e.drop.get('node');
                    M.local_rlsiteadmin.setup_archive($snapshot, $slot);
                });
                drag.on('drag:dropmiss', function(e) {
                    // Nothing to do here.
                });
            });
        });

        // Remove .slot-open from all .archive
        // 3 second delay to get around issue where YUI doesn't assign functionality before the class is removed.
        setTimeout(function() {
            Y.all('#rlarchives .archive').removeClass('slot-open');
        }, 3000);

        // Save button fallback.
        function save_snapshot() {
            if (Y.all('#rlarchives .slot-open').size() > 0) {
                // Pass $snapshot and $slot to setup function.
                var $snapshot = this.ancestor('.snapshot');
                var $slot = Y.one('#rlarchives .slot-open');
                M.local_rlsiteadmin.setup_archive($snapshot, $slot);
            } else {
                // Show error if no slots are available.
                M.local_rlsiteadmin.show_error('archive_error_no_slots_available');
            }
        }
        Y.one("#rlsnapshots .list").delegate('click', save_snapshot, '.snapshot .save');
    },

    /**
     * Assign discard archive functionality.
     */
    discard_archive_init: function() {
        function discard_archive() {
            var $archive = this.ancestor('.archive');
            // Check if restored.
            if ($archive.hasClass("restore-in-progress") || $archive.hasClass("restore-ready")) {
                M.local_rlsiteadmin.show_error('archive_error_destroy_restore_first');
                return;
            }
            if ($archive.hasClass("processing")) {
                return;
            }
            var remove = confirm(M.util.get_string('archive_discard_confirmation', 'local_rlsiteadmin'));
            if (remove) {
                var archiveid = $archive.getAttribute('data-archiveid');

                YUI().use("io-base", "node", function(Y) {

                    /**
                     * Handler for returned "complete" status
                     */
                    function complete() {
                        $archive.removeClass("processing");
                        Y.log('complete');
                    }

                    /**
                     * Handler for returned "success" status
                     *
                     * @param string id Not used
                     * @param object o A YUI object containing the response text
                     */
                    function success(id, o) {
                        $archive.removeClass("processing");
                        Y.log('archives_fetch success');
                        var response = null;
                        // Filter out any PHP warnings before or after the output.
                        var match = o.responseText.match(/\{"archiveapi":{.*\}}/m);
                        if (match === null) {
                            // Show general error.
                            M.local_rlsiteadmin.show_error('archive_error_general');
                            Y.log("Unable to discard archive!");
                            return;
                        }
                        YUI().use('json-parse', function (Y) {
                            try {
                                response = Y.JSON.parse(match[0]);
                            }
                            catch (e) {
                                Y.log("Parsing failed.");
                            }
                        });
                        if (response.archiveapi.error) {
                            // Show archive currently being restored error.
                            M.local_rlsiteadmin.show_error('archive_error_restore_in_progress');
                        } else if (response.archiveapi.httpcode == 503) {
                            // Show archive busy error.
                            M.local_rlsiteadmin.show_error('archive_error_api_busy');
                        } else {
                            // Convert archive to open slot.
                            $archive.setAttribute('data-archiveid', '');
                            $archive.removeClass('archive');
                            $archive.addClass('slot');
                            $archive.addClass('slot-open');
                            var slothtml = Y.one('.archive-manager .templates .slot').getHTML();
                            $archive.set('innerHTML', slothtml);
                            // Update the used slot counter.
                            M.local_rlsiteadmin.used_slots--;
                            Y.one('#rlarchives #numused').setHTML(M.local_rlsiteadmin.used_slots);
                        }
                    }

                    /**
                     * Handler for returned "failure" status
                     *
                     * @param array args Arguments passed to the failure function
                     */
                    function failure(args) {
                        $archive.removeClass("processing");
                        Y.log('Failure: '+args[0]);
                    }
                    Y.on('io:complete', complete, Y, []);
                    Y.on('io:success', success, Y, []);
                    Y.on('io:failure', failure, Y, [M.util.get_string('ajax_request_failed', 'local_rlsiteadmin')]);
                    var url = M.cfg.wwwroot+'/local/rlsiteadmin/archive/apicall.php?request=discardarchive&archiveid='+archiveid;
                    Y.log(url);
                    Y.io(url);
                });
            }

        }
        if (M.local_rlsiteadmin.bindings.discard) {
            M.local_rlsiteadmin.bindings.discard.detach();
        }
        M.local_rlsiteadmin.bindings.discard = Y.one("#rlarchives .list").delegate('click', discard_archive, '.archive .controls .discard');

    },

    /**
     * Setup archive from snapshot.
     */
    setup_archive: function($snapshot, $slot) {
        // Don't proceed if the slot is already in use and its .slot class has been removed.
        if (!$slot.hasClass("slot-open")) {
            return;
        }

        // Get the snapshotid from the snapshot.
        var snapshotid = $snapshot.getAttribute("data-snapshotid");

        // Hide .info and show .spinner on .slot
        $slot.one(".info").hide();
        $slot.removeClass("slot");
        $slot.removeClass("slot-open");
        $slot.addClass("archive");

        // Hide the snapshot.
        $snapshot.hide();

        YUI().use('io-base', 'node', function(Y) {

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
                Y.log('addons_fetch success');
                var response = null;
                // Filter out any PHP warnings before or after the output.
                var match = o.responseText.match(/\{"archiveapi":{.*\}}/m);
                if (match === null) {
                    // Show general error.
                    M.local_rlsiteadmin.show_error('archive_error_general');
                    $slot.one(".info").show();
                    $slot.addClass("slot");
                    $slot.removeClass("archive");
                    $snapshot.show();
                    Y.log("Unable to create archive!");
                    return;
                }
                YUI().use('json-parse', function (Y) {
                    try {
                        response = Y.JSON.parse(match[0]);
                    }
                    catch (e) {
                        Y.log("Parsing failed.");
                    }
                });
                if (response.archiveapi.error) {
                    // Show no remaining slots error.
                    M.local_rlsiteadmin.show_error('archive_error_no_slots_available');
                    $slot.one(".info").show();
                    $slot.addClass("slot");
                    $slot.addClass("slot-open");
                    $slot.removeClass("archive");
                    $snapshot.show();
                } else if (response.archiveapi.httpcode == 503) {
                    // Show archive busy error.
                    M.local_rlsiteadmin.show_error('archive_error_api_busy');
                    $slot.one(".info").show();
                    $slot.addClass("slot");
                    $slot.addClass("slot-open");
                    $slot.removeClass("archive");
                    $snapshot.show();
                } else {
                    // Update the slot to be an archive with archive html.
                    $slot.setAttribute("data-archiveid", snapshotid);
                    var archivehtml = Y.one('.archive-manager .templates .archive').getHTML();
                    $slot.set('innerHTML', archivehtml);
                    $slot.one('.date').setHTML($snapshot.one('.date').getHTML());
                    $slot.one('.status').setHTML(M.util.get_string('archive_status_in_progress', 'local_rlsiteadmin'));
                    $slot.addClass('in-progress');
                    M.local_rlsiteadmin.used_slots++;
                    Y.one('#rlarchives #numused').setHTML(M.local_rlsiteadmin.used_slots);
                    $snapshot.remove();
                    // Assign discard archive functionality.
                    M.local_rlsiteadmin.discard_archive_init();
                    // Assign restore archive functionality.
                    M.local_rlsiteadmin.restore_archive_init();
                    // Show message indicating that large sites take longer.
                    M.local_rlsiteadmin.show_info('archive_request_message');
                }
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
            var url = M.cfg.wwwroot+'/local/rlsiteadmin/archive/apicall.php?request=archivesnapshot&snapshotid='+snapshotid;
            Y.log(url);
            Y.io(url);
        });
    },

    /**
     * Fetch the restored archives from the archive api
     */
    restored_fetch: function() {
        YUI().use("io-base", function(Y) {

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
                Y.log('snapshots_fetch success');
                var response = null;
                // Filter out any PHP warnings before or after the output.
                var match = o.responseText.match(/\{"archiveapi":{.*\}}/m);
                if (match === null) {
                    Y.log("Unable to load snapshots!");
                    return;
                }
                YUI().use('json-parse', function (Y) {
                    try {
                        response = Y.JSON.parse(match[0]);
                    }
                    catch (e) {
                        Y.log("Parsing failed.");
                    }
                });
                M.local_rlsiteadmin.data_restored = response.archiveapi.restored;
                Y.log(M.local_rlsiteadmin.data_restored);
                M.local_rlsiteadmin.restored_write();
                M.local_rlsiteadmin.restore_archive_init();
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
            var url = M.cfg.wwwroot+'/local/rlsiteadmin/archive/apicall.php?request=listrestored';
            Y.log(url);
            Y.io(url);
        });
    },

    restored_write: function() {
        var $archives = Y.all('#rlarchives .archive');
        Y.each($archives, function($archive, k) {
            var archiveid = $archive.getAttribute('data-archiveid');
            Y.Object.each(M.local_rlsiteadmin.data_restored['done'], function(value, key) {
                if (archiveid == value.id) {
                    $archive.addClass("restore-ready");
                    var link = '<a href="'+value.url+'">'+value.url+"</a>";
                    var shutdowndate = value['expiration'];
                    var status = M.util.get_string('archive_status_restored', 'local_rlsiteadmin')+link;
                        status += M.util.get_string('archive_available_until', 'local_rlsiteadmin')+shutdowndate;
                    $archive.one('.status').setHTML(status);
                }
            });
            Y.Object.each(M.local_rlsiteadmin.data_restored['in-progress'], function(value, key) {
                if (archiveid == value.id) {
                    $archive.addClass("restore-in-progress");
                    $archive.one('.status').setHTML(M.util.get_string('archive_status_restoring', 'local_rlsiteadmin'));
                    $archive.one('.status .adminemail').setHTML(Y.one('#useremail').getHTML());
                }
            });
        });

        // Show the counter
        Y.one('#rlarchives .count').addClass('show');

        // Hide the gear spinner that shows before archives are printed to the page.
        $spinner = Y.one('#rlarchives .spinner');
        $spinner.hide();

        // Reveal archives
        var counter = 0;
        var $archivecolitems = Y.all('#rlarchives .block');
        Y.each($archivecolitems, function($block, k) {
            counter++;
            setTimeout(function() {
                $block.removeClass('fadedout');
            }, 70 * counter);
        });
    },

    /**
     * Assign restore archive functionality.
     */
    restore_archive_init: function() {

        // Check the status of an archive. Destroy or restore accordingly.
        function update_restore_status() {
            var $archive = this.ancestor('.archive');
            if ($archive.hasClass("processing")) {
                return;
            }
            $archive.addClass("processing");
            var archiveid = $archive.getAttribute('data-archiveid');
            if ($archive.hasClass("restore-in-progress") || $archive.hasClass("restore-ready")) {
                destroy_restore(archiveid, $archive);
            } else {
                restore_archive(archiveid, $archive);
            }
        }

        // Restore an archive.
        function restore_archive(archiveid, $archive) {
            YUI().use("io-base", "node", function(Y) {

                /**
                 * Handler for returned "complete" status
                 */
                function complete() {
                    $archive.removeClass("processing");
                    Y.log('complete');
                }

                /**
                 * Handler for returned "success" status
                 *
                 * @param string id Not used
                 * @param object o A YUI object containing the response text
                 */
                function success(id, o) {
                    $archive.removeClass("processing");
                    Y.log('archives_fetch success');
                    var response = null;
                    // Filter out any PHP warnings before or after the output.
                    var match = o.responseText.match(/\{"archiveapi":{.*\}}/m);
                    if (match === null) {
                        // Show general error.
                        M.local_rlsiteadmin.show_error('archive_error_general');
                        Y.log("Unable to restore archive!");
                        return;
                    }
                    YUI().use('json-parse', function (Y) {
                        try {
                            response = Y.JSON.parse(match[0]);
                        }
                        catch (e) {
                            Y.log("Parsing failed.");
                        }
                    });
                    if (response.archiveapi.error) {
                        // Show no remaining restore slots error.
                        M.local_rlsiteadmin.show_error('archive_error_no_restore_slots_available');
                    } else if (response.archiveapi.httpcode == 503) {
                        // Show archive busy error.
                        M.local_rlsiteadmin.show_error('archive_error_api_busy');
                    } else {
                        $archive.addClass("restore-in-progress");
                        $archive.one('.status').setHTML(M.util.get_string('archive_status_restoring', 'local_rlsiteadmin'));
                        $archive.one('.status .adminemail').setHTML(Y.one('#useremail').getHTML());
                        // Show message indicating that large sites take longer.
                        M.local_rlsiteadmin.show_info('archive_request_message');
                    }
                }

                /**
                 * Handler for returned "failure" status
                 *
                 * @param array args Arguments passed to the failure function
                 */
                function failure(args) {
                    $archive.removeClass("processing");
                    Y.log('Failure: '+args[0]);
                }
                Y.on('io:complete', complete, Y, []);
                Y.on('io:success', success, Y, []);
                Y.on('io:failure', failure, Y, [M.util.get_string('ajax_request_failed', 'local_rlsiteadmin')]);
                var url = M.cfg.wwwroot+'/local/rlsiteadmin/archive/apicall.php?request=restorearchive&archiveid='+archiveid;
                Y.log(url);
                Y.io(url);
            });
        }

        function destroy_restore(archiveid, $archive) {
            YUI().use("io-base", "node", function(Y) {

                /**
                 * Handler for returned "complete" status
                 */
                function complete() {
                    $archive.removeClass("processing");
                    Y.log('complete');
                }

                /**
                 * Handler for returned "success" status
                 *
                 * @param string id Not used
                 * @param object o A YUI object containing the response text
                 */
                function success(id, o) {
                    $archive.removeClass("processing");
                    Y.log('archives_fetch success');
                    var response = null;
                    // Filter out any PHP warnings before or after the output.
                    var match = o.responseText.match(/\{"archiveapi":{.*\}}/m);
                    if (match === null) {
                        // Show general error.
                        M.local_rlsiteadmin.show_error('archive_error_general');
                        Y.log("Unable to destroy restore!");
                        return;
                    }
                    YUI().use('json-parse', function (Y) {
                        try {
                            response = Y.JSON.parse(match[0]);
                        }
                        catch (e) {
                            Y.log("Parsing failed.");
                        }
                    });
                    if (response.archiveapi.error) {
                        // Show no remaining restore slots error.
                        M.local_rlsiteadmin.show_error('archive_error_no_restore_slots_available');
                    } else if (response.archiveapi.httpcode == 503) {
                        // Show archive busy error.
                        M.local_rlsiteadmin.show_error('archive_error_api_busy');
                    } else {
                        $archive.removeClass("restore-in-progress");
                        $archive.removeClass("restore-ready");
                        $archive.one('.status').setHTML(M.util.get_string('archive_status_ready', 'local_rlsiteadmin'));
                    }
                }

                /**
                 * Handler for returned "failure" status
                 *
                 * @param array args Arguments passed to the failure function
                 */
                function failure(args) {
                    $archive.removeClass("processing");
                    Y.log('Failure: '+args[0]);
                }
                Y.on('io:complete', complete, Y, []);
                Y.on('io:success', success, Y, []);
                Y.on('io:failure', failure, Y, [M.util.get_string('ajax_request_failed', 'local_rlsiteadmin')]);
                var url = M.cfg.wwwroot+'/local/rlsiteadmin/archive/apicall.php?request=destroyrestore&restoreid='+archiveid;
                Y.log(url);
                Y.io(url);
            });
        }

        if (M.local_rlsiteadmin.bindings.restore) {
            M.local_rlsiteadmin.bindings.restore.detach();
        }
        M.local_rlsiteadmin.bindings.restore = Y.one("#rlarchives .list").delegate('click', update_restore_status, '.archive .controls .power');
    },

    /**
     * Show error modal.
     *
     * @param string errorstring The string for the error
     */
    show_error: function(errorstring) {
        YUI().use("panel", function (Y) {
            M.local_rlsiteadmin.error_modal = new Y.Panel({
                srcNode: '<div></div>',
                id: 'error-modal',
                headerContent: M.util.get_string('archive_error_header', 'local_rlsiteadmin'),
                bodyContent: M.util.get_string(errorstring, 'local_rlsiteadmin'),
                buttons: [
                    {
                        id: 'modal-confirm-button',
                        label: M.util.get_string('close', 'local_rlsiteadmin'),
                        section: 'footer',
                        action: function(e) {
                            e.preventDefault();
                            M.local_rlsiteadmin.error_modal.hide();
                            M.local_rlsiteadmin.error_modal.destroy();
                        },
                        classNames: 'modal-confirm-button',
                        disabled: false
                    },
                ],
                classNames: 'error-modal',
                width: 400,
                height: 200,
                zIndex: 10000,
                centered: true,
                modal: true,
                visible: true,
                render: true
            });
        });
    },

    /**
     * Show info modal.
     *
     * @param string infostring The string for the message
     */
    show_info: function(infostring) {
        YUI().use("panel", function (Y) {
            M.local_rlsiteadmin.error_modal = new Y.Panel({
                srcNode: '<div></div>',
                id: 'info-modal',
                headerContent: ' ',
                bodyContent: M.util.get_string(infostring, 'local_rlsiteadmin'),
                buttons: [
                    {
                        id: 'modal-confirm-button',
                        label: M.util.get_string('close', 'local_rlsiteadmin'),
                        section: 'footer',
                        action: function(e) {
                            e.preventDefault();
                            M.local_rlsiteadmin.error_modal.hide();
                            M.local_rlsiteadmin.error_modal.destroy();
                        },
                        classNames: 'modal-confirm-button',
                        disabled: false
                    },
                ],
                classNames: 'info-modal',
                width: 400,
                height: 200,
                zIndex: 10000,
                centered: true,
                modal: true,
                visible: true,
                render: true
            });
        });
    },

    /**
     * Initialization function.
     */
    init: function() {
        Y.log('Init function called:');
        if (Y.all(".archive-error").size() == 0) {
            M.local_rlsiteadmin.snapshots_fetch();
            M.local_rlsiteadmin.archives_fetch();
        }
    }
};
