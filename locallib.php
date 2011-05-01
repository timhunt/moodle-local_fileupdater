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
 * Library code for file-updater.
 *
 * @package    local
 * @subpackage fileupdater
 * @copyright  2011 Tim Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/tablelib.php');


/**
 * Table of all the files with a particular content hash.
 *
 * @copyright  2011 Tim Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_fileupdater_table extends table_sql {
    public function __construct($filehash) {
        global $DB;

        parent::__construct('local_fileupdater_table');
        $this->set_attribute('id', 'local_fileupdater_table');

        $fields = '
            f.id,
            f.contextid,
            f.component,
            f.filearea,
            f.itemid,
            f.filepath,
            f.filename,
            ' . $DB->sql_concat('f.filepath', 'f.filename') . ' AS filepathname,
            f.userid,
            u.firstname,
            u.lastname,
            f.filesize,
            f.timecreated,
            f.timemodified,
            ctx.contextlevel,
            ctx.instanceid AS contextinstanceid,
            ctx.path AS contextpath,
            ctx.depth AS contextdepth';
        $from = '{files} f
                JOIN {user} u ON u.id = f.userid
                JOIN {context} ctx ON ctx.id = f.contextid';
        $where = 'contenthash = :filehash AND
                (component <> :excludecomponent OR filearea <> :excludearea)';
        $params = array(
            'filehash' => $filehash,
            'excludecomponent' => 'user',
            'excludearea' => 'draft',
        );

        $columns = array(
            'contextpath' => get_string('context', 'role'),
            'component' => get_string('component', 'local_fileupdater'),
            'filearea' => get_string('filearea', 'local_fileupdater'),
            'itemid' => get_string('itemid', 'local_fileupdater'),
            'filepathname' => get_string('filename', 'repository'),
            'filesize' => get_string('size'),
            'timecreated' => get_string('created', 'local_fileupdater'),
            'timemodified' => get_string('modified'),
            'fullname' => get_string('name'),
        );

        $this->set_sql($fields, $from, $where, $params);
        $this->define_columns(array_keys($columns));
        $this->define_headers(array_values($columns));
    }

    /**
     * Format the contents of the contextid column.
     * @param object $file a row retured by the database query.
     * @return string HTML of the cell contents.
     */
    public function col_contextpath($file) {
        $context = new stdClass();
        $context->id = $file->contextid;
        $context->contextlevel = $file->contextlevel;
        $context->instanceid = $file->contextinstanceid;
        $context->path = $file->contextpath;
        $context->depth = $file->contextdepth;
        return html_writer::link(get_context_url($context),
                print_context_name($context, true, true));
    }

    /**
     * Format the contents of the filesize column.
     * @param object $file a row retured by the database query.
     * @return string HTML of the cell contents.
     */
    public function col_filesize($file) {
        return display_size($file->filesize);
    }

    /**
     * Format the contents of the timecreated column.
     * @param object $file a row retured by the database query.
     * @return string HTML of the cell contents.
     */
    public function col_timecreated($file) {
        return userdate($file->timecreated);
    }

    /**
     * Format the contents of the timecreated column.
     * @param object $file a row retured by the database query.
     * @return string HTML of the cell contents.
     */
    public function col_timemodified($file) {
        return userdate($file->timemodified);
    }
}


/**
 * Replace a particular logical file with another file.
 * @param object $filerecord
 * @param string $pathtonewfile path to the file to replace the old file with.
 */
function update_file_contents($filerecord, $pathtonewfile) {
    global $DB;
    $fs = get_file_storage();

    // Warning! I have not acutally tested this code yet, but I am pretty sure it is right.

    $transaction = $DB->start_delegated_transaction();

    $storedfile = $fs->get_file($filerecord->contextid,
            $filerecord->component, $filerecord->filearea, $filerecord->itemid,
            $filerecord->filepath, $filerecord->filename);

    if (!$storedfile) {
        $transaction->rollback(new coding_exception('File not found', $filerecord));
    }

    $storedfile->delete();
    $fs->create_file_from_pathname($filerecord, $pathtonewfile);

    $transaction->allow_commit();
}
