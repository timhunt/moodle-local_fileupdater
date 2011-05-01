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
 * Proof of concept for updating a file in Moodle 2.0 everywhere it is used.
 *
 * @package    local
 * @subpackage fileupdater
 * @copyright  2011 Tim Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/fileupdater/locallib.php');


$file = required_param('file', PARAM_FILE);

admin_externalpage_setup('local_fileupdater', '', array('file' => $file));
require_capability('moodle/site:config', get_system_context());

$filepath = $CFG->dataroot . '/local_fileupdater/' . $file;
if (!is_readable($filepath)) {
    throw new moodle_exception('filenotfound', 'local_fileupdater', '', $filepath);
}

$table = new local_fileupdater_table(sha1_file($filepath));
$table->define_baseurl($PAGE->url);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('allplaces', 'local_fileupdater', $file));
$table->out(100, false);
echo $OUTPUT->footer();
