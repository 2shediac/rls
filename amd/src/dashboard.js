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
 * Remote Learner Dashboard
 *
 * @package   local_rlsiteadmin
 * @copyright 2014 Remote Learner Inc http://www.remote-learner.net
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/*globals M */

/**
  * @module local_rlsiteadmin/dashboard
  */
// Workaround to load Masonry.js as though it is from an external site.
// This avoids define call collisions with AMD proper.
M.local_rlsiteadmin = M.local_rlsiteadmin || {};
M.local_rlsiteadmin = {
    masonryjs: ''
};
if (M.cfg) {
    M.local_rlsiteadmin.masonryjs = M.cfg.wwwroot + '/local/rlsiteadmin/js/masonry.pkgd.min.js';
}
define(['jquery', 'core/yui', M.local_rlsiteadmin.masonryjs], function ($, Y, Masonry) {

    return /** @alias module:local_rlsiteadmin/dashboard */ {
        /**
         * init dashboard
         * @access public
         */
        init: function() {
            Y.log('Init function called:');
            if (Y.all(".rl-dashboard-widget").size() > 0) {
                // Trigger masonry layout, each Bootstrap tab separately.
                var options = {
                    itemSelector: '.rl-dashboard-widget',
                    gutter: 0,
                    transitionDuration: '0.2s'
                };
                var supportel = document.querySelector('.tab-pane.support');
                var reportsel = document.querySelector('.tab-pane.reports');
                var infoel = document.querySelector('.tab-pane.info');
                var support_layout = new Masonry(supportel, options);
                var reports_layout = new Masonry(reportsel, options);
                var info_layout = new Masonry(infoel, options);
                // Redo layouts on tabs click.
                Y.all('.nav-tabs li a').on('click', function(e) {
                    var tab = e.currentTarget.get('aria-controls');
                    Y.all('div.rl-dashboard-wells div').each(function (div) {
                        if (div.getAttribute('data-name') !== tab) {
                            div.setAttribute('style', 'display: none');
                        } else {
                            div.setAttribute('style', 'display: block');
                        }
                    });
                    // Delay necessary bc tabs transition must complete first.
                    setTimeout( function(){
                        support_layout.layout();
                        reports_layout.layout();
                        info_layout.layout();
                        window.dispatchEvent(new Event('resize'));
                    }, 350);
                });
            }
            Y.all('.rl-widget-content-inner').each(function(widget) {
                if (widget.one('.rlsiteadmin-widget-info-button')
                        && widget.one('.rlsiteadmin-widget-info-close-button')) {
                    widget.one('.rlsiteadmin-widget-info-button').on('click', function() {
                        widget.one('.rlsiteadmin-widget-info').addClass('show');
                    });
                    widget.one('.rlsiteadmin-widget-info-close-button').on('click', function() {
                        widget.one('.rlsiteadmin-widget-info').removeClass('show');
                    });
                }
            });
        }
    };
});
