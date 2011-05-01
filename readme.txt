At the moment this is just a proof of concept showing that it would be possible
to build a UI for updating all the places where a single file is used in a
Moodle site.

To install using git, type this command in the root of your Moodle install
    git clone git://github.com/timhunt/moodle-local_fileupdater.git local/fileupdater
Then add /local/codechecker to your git ignore.

Alternatively, download the zip from
    https://github.com/timhunt/moodle-local_fileupdater/zipball/master
unzip it into the local folder, and then rename the new folder to fileupdater.

To use (I did say this was a rough proof of concept):

Put the original of the file you want to update in
    {$CFG->dataroot}/local_fileupdater/
(you will need to create that folder. Suppose the original file is called example.doc
Then go to the URL
    {$CFG->wwwroot}/local/fileupdater/index.php?file=example.doc

Tim Hunt. May 2011.


Todo:

1. Needs a much better UI.
2. Security. We need to restrict it so that people can only update the files
   they are allowed to edit.
3. Needs to be linked into the navigation/settings block in an appropriate place.