Bulk Metadata Editor (plugin for Omeka)
=======================================

[Bulk Metadata Editor] is an [Omeka] plugin intended to expedite the process of
editing metadata in Omeka collections of digital objects by providing tools for
administrators to edit many items at once based on prespecified rules.

If you use this plugin, please take a moment to submit feedback about your
experience, so we can keep making Omeka better: [User Survey].


Installation
------------

Uncompress files and rename plugin folder "BulkMetadataEditor".

Then install it like any other Omeka plugin.


Notes
-----

- After a successful process, records must be reindexed in order to find them
  via the quick search field.
- The change "Append text to existing metadata in the selected fields" appends
  text only if there is already a metadata.
- A space is automatically added with the change "Append text to existing
  metadata in the selected fields".
- The preview may fail when there is a lot of fields or changes to prepare.
  Nevermind, the true process will work fine even with a huge number of items
  and fields, because it is done in the background, without the limit set by the
  server. Nevertheless, it's recommended to avoid too large updates.


Warning
-------

Use it at your own risk.

It's always recommended to backup your files and database regularly so you can
roll back if needed.


Troubleshooting
---------------

See online issues on the [plugin issues] page on GitHub.


License
-------

This plugin is published under [GNU/GPL v3].

This program is free software; you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation; either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
details.

You should have received a copy of the GNU General Public License along with
this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.


Copyright
---------

* Copyright 2014 UCSC Library Digital Initiatives
* Copyright Daniel Berthereau, 2015-2016 (see [Daniel-KM] on GitHub)
* Copyright Julian Maurice for BibLibre, 2015 (see [jajm] on GitHub)


[Bulk Metadata Editor]: https://github.com/UCSCLibrary/BulkMetadataEditor
[Omeka]: http://omeka.org
[User Survey]: https://docs.google.com/forms/d/1sfct41zxTelXFlyBwtsT1u33nRl7GGofSTt06d1SDMQ/viewform?usp=send_form
[plugin issues]: https://github.com/UCSCLibrary/BulkMetadataEditor/issues
[GNU/GPL v3]: https://www.gnu.org/licenses/gpl-3.0.html
[Daniel-KM]: https://github.com/Daniel-KM
[jajm]: https://github.com/jajm
