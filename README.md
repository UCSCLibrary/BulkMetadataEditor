Bulk Metadata Editor (plugin for Omeka)
=======================================

[Bulk Metadata Editor] is an [Omeka] plugin intended to expedite the process of
editing metadata in Omeka collections of digital objects by providing tools for
administrators to edit many Items at once based on prespecified rules.

If you use this plugin, please take a moment to submit feedback about your
experience, so we can keep making Omeka better: [User Survey].

The editing options available are:
- Search and replace text
- Add a new metadatum in the selected field
- Prepend text to existing metadata in the selected fields
- Append text to existing metadata in the selected fields
- Remove text from ends of existing metadata in the selected fields
- Convert to uppercase or lowercase existing metadata in the selected fields
- Explode metadata with a separator in multiple elements in the selected fields
- Deduplicate and join metadata in the selected fields
- Deduplicate and remove empty metadata in the selected fields
- Deduplicate files of selected items by hash
- Delete all existing metadata in the selected fields

Installation
------------

Uncompress files and rename plugin folder "BulkMetadataEditor".

Then install it like any other Omeka plugin.

You may have to set the php cli path in `application/config/config.ini`,
according to your server if it is not automatically detected:

```
background.php.path = "/usr/bin/php-cli"
```

In order to get messages about the process, you may have to set the logger:

```
log.errors = true
log.priority = Zend_Log::INFO
```

The log file is `application/logs/errors.log`, that must be writeable.


Notes
-----

- After a successful process, records must be reindexed in order to find them
  via the quick search field.
- The change "*Append text to existing metadata in the selected fields*" appends
  text only if there is already a metadata.
- The preview may fail when there is a lot of fields or changes to prepare.
  The true process will still work fine even with a huge number of items
  and fields, because it is done in the background, without the limit set by the
  server. Nevertheless, it's recommended to avoid too large updates.


Warning
-------

Use it at your own risk.

It's always recommended to backup your files and your databases and to check
your archives regularly so you can roll back if needed.


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
* Copyright Daniel Berthereau, 2015-2017 (see [Daniel-KM] on GitHub)
* Copyright Julian Maurice for BibLibre, 2015 (see [jajm] on GitHub)
* Copyright Daniele Binaghi, 2020-2022 (see [DBinaghi] on GitHub)

[Bulk Metadata Editor]: https://github.com/UCSCLibrary/BulkMetadataEditor
[Omeka]: https://omeka.org
[User Survey]: https://docs.google.com/forms/d/1sfct41zxTelXFlyBwtsT1u33nRl7GGofSTt06d1SDMQ/viewform?usp=send_form
[plugin issues]: https://github.com/UCSCLibrary/BulkMetadataEditor/issues
[GNU/GPL v3]: https://www.gnu.org/licenses/gpl-3.0.html
[Daniel-KM]: https://github.com/Daniel-KM
[jajm]: https://github.com/jajm
[DBinaghi]: https://github.com/DBinaghi
