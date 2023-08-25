This module allows paragraphs to be attached to nodes during a feed import.

It does this by adding an entity presave event to the import process and then
matching paragraph feed item guid with entity feed item guid.

Only the node entity type is supported currently.

Node feeds must map a feed item guid.
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

Paragraph feed imports should run before node feed imports.

Node imports may skip saving if it deems that a node has no changes. This means
that in some situations the node feed setting `Force Update` should be enabled.
