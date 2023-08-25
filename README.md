This module allows paragraphs to be attached to entities during a feed import.

It does this by adding an entity presave event to the import process and then
matching paragraph feed item guid with entity feed item guid.

The entity feed must map a feed item guid.

The simplest guid could be: [node id]

A more complex guid could take into account imports from multiple sites and
be prefixed with a site key: [site key]-[nid]

Paragraph feeds must map a feed item guid with this specific pattern:
[node feed item guid]-[field machine name]-[delta]

The entity presave event will loop through every field. For each paragraph
field it will query the database for any paragraph feed items that match
[node feed item guid]-[field machine name] sorted by the full guid (ie.
including [delta]) ascending.

The resulting paragraphs are set to the entity paragraph field.

Paragraph feeds imports should run beforehand so that the paragraphs exist for
other feeds to lookup.

The presave event only triggers if feeds detects there are changes. This means
that in some situations the feed setting `Force Update` should be enabled.
