SedMeta (plugin for Omeka)
==========================

Bulk metadata search and replace for the Omeka platform.

This [Omeka] 2.1+ plugin is intended to expedite the process of editing metadata
in Omeka collections of digital objects by providing tools for administrators to
edit many items at once based on prespecified rules.


Installation
------------

Uncompress files and rename plugin folder "SedMeta".

Then install it like any other Omeka plugin.


Notes
-----

- After a successful process, records may need to be reindexed in order to find
them via the quick search field.

- The change "Append text to existing metadata in the selected fields" appends
text only if there is already a metadata.
- A space is automatically added with the change "Append text to existing metadata in the selected fields".


Warning
-------

Use it at your own risk.

It's always recommended to backup your files and database regularly so you can
roll back if needed.


Troubleshooting
---------------

See online issues on the [SedMeta issues] page on GitHub.


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


[Omeka]: https://omeka.org
[SedMeta]: https://github.com/UCSCLibrary/SedMeta
[SedMeta issues]: https://github.com/UCSCLibrary/SedMeta/issues
[GNU/GPL v3]: https://www.gnu.org/licenses/gpl-3.0.html
