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
 * Remote Learner Update Manager Schedule
 *
 * @package    local_rlsiteadmin
 * @copyright  2012 Remote Learner Inc http://www.remote-learner.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class table_schedule extends table_sql {
    const NOT_STARTED = 0;
    const IN_PROGRESS = 1;
    const COMPLETED   = 2;
    const ERROR       = 3;
    const SKIPPED     = 4;
    const CANCELLED   = 5;

    const READY       = 0;
    const SENT        = 1;
    const NOT_SENT    = 2;

    protected $strings = array(
        self::NOT_STARTED => 'notstarted',
        self::IN_PROGRESS => 'inprogress',
        self::COMPLETED   => 'completed',
        self::ERROR       => 'error',
        self::SKIPPED     => 'skipped',
        self::CANCELLED   => 'cancelled',
    );

    protected $block = 'local_rlsiteadmin';

    /**
     * Format the original date
     *
     * @param array $row A row of data
     */
    function col_originaldate($row) {
        return userdate($row->originaldate);
    }

    /**
     * Format the scheduled date
     *
     * @param array $row A row of data
     */
    function col_scheduleddate($row) {
        return userdate($row->scheduleddate);
    }

    /**
     * Format the scheduled date
     *
     * @param array $row A row of data
     */
    function col_updateddate($row) {
        return userdate($row->updateddate);
    }

    /**
     * Format the scheduled date
     *
     * @param array $row A row of data
     */
    function col_status($row) {
        global $OUTPUT;

        if ($row->status == self::NOT_STARTED) {
            $text = get_string('change', $this->block);

            $url    = new moodle_url('/local/rlsiteadmin/eventedit.php', array('id' => $row->id));
            $action = new popup_action('click', $url, 'change', array('height' => 400, 'width' => 450));
            $col   = $OUTPUT->action_link($url, $text, $action, array('title' => $text));
        } else {
            $col = get_string('status', $this->block).' '.get_string($this->strings[$row->status], $this->block);
        }

        return $col;
    }

    /**
     * Print headers
     *
     * This table uses no headers.
     */
    function print_headers() {
    }

    /**
     * Override row printing to print nice rows
     *
     * $row[0] = scheduled date
     * $row[1] = original date
     * $row[2] = description/title
     * $row[3] = status
     * $row[4] = log
     * $row[5] = updated date
     *
     * @param array  $row       The row to print
     * @param string $classname A class to be applied to the row
     */
    function print_row($row, $classname = '') {
        static $suppress_lastrow = NULL;

        $rowclasses = array();
        if ($classname) {
            $rowclasses[] = $classname;
        }

        echo html_writer::start_tag('tr', array('class' => implode(' ', $rowclasses)));

       // If we have a separator, print it
        if ($row === NULL) {
            $colcount = count($this->columns);
            echo html_writer::tag('td', html_writer::tag('div', '',
                    array('class' => 'tabledivider')), array('colspan' => $colcount));

        } else {
            $content = array();
            $content[] = html_writer::tag('div', $row[2], array('class' => 'title'));

            if ($row[0] != $row[1]) {
                $text = get_string('defaultdate', $this->block);
                $content[] = html_writer::tag('div', $text,   array('class' => 'heading clear'));
                $content[] = html_writer::tag('div', $row[1], array('class' => 'value'));
            }

            $text = get_string('scheduleddate', $this->block);
            $content[] = html_writer::tag('div', $text,   array('class' => 'heading clear'));
            $content[] = html_writer::tag('div', $row[0], array('class' => 'value'));

            $text = get_string('updateddate', $this->block);
            $content[] = html_writer::tag('div', $text,   array('class' => 'heading clear'));
            $content[] = html_writer::tag('div', $row[5], array('class' => 'value'));

            $content[] = html_writer::tag('div', $row[3], array('class' => 'status'));

            if (! empty($row[4])) {
                $content[] = html_writer::tag('div', get_string('log', $this->block), array('class' => 'heading clear'));
                $content[] = html_writer::tag('div', $row[4], array('class' => 'log'));
            }
            $content[] = html_writer::empty_tag('br', array('class' => 'clear'));
            $div  = html_writer::tag('div', implode("\n", $content), array('class' => 'event content'));
            $div  = html_writer::tag('div', $div, array('class' => 'block'));
            echo html_writer::tag('td', $div);
        }

        echo html_writer::end_tag('tr');

        $suppress_enabled = array_sum($this->column_suppress);
        if ($suppress_enabled) {
            $suppress_lastrow = $row;
        }
        $this->currentrow++;
    }
}
