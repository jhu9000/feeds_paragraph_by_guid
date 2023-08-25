This module allows paragraphs to be attached to entities during a feed import.

It does this by adding an entity presave event to the import process and then
matching paragraph feed item guid with entity feed item guid.

The entity feed must map a feed item guid.

The simplest guid could be: [node id]

A more complex guid could take into account imports from multiple sites and
be prefixed with a site key: [site key]-[nid]

Paragraph feeds must map a feed item guid with this specific pattern:
[entity feed item guid]-[field machine name]-[delta]

The entity presave event will loop through every field. For each paragraph
field it will query the database for any paragraph feed items that match
[entity feed item guid]-[field machine name] sorted by the full guid (ie.
including [delta]) ascending.

The resulting paragraphs are set to the entity paragraph field.

Paragraph feeds imports should run beforehand so that the paragraphs exist for
other feeds to lookup.

The presave event only triggers if feeds detects there are changes. This means
that in some situations the feed setting `Force Update` should be enabled.

## Example

The site has a node content type "page" with a paragraphs field "field_paragraphs".

The site also has a paragraph type with a text field "field_paragraph_title".

Node page feed mapping
- feeds item guid, json source: guid
- title, json source: title

Node page feed json import file
{
  "0": {
    "guid": "nid-1",
    "title": "page 1"
  },
  "1": {
    "guid": "nid-2",
    "title": "page 2"
  }
}

Paragraph feed mapping
- feeds item guid, json source: guid
- field_paragraph_title, json source: title

Paragraph feed json import file
{
  "0": {
    "guid": "nid-1-field_paragraphs-0",
    "title": "paragraph 1a"
  },
  "1": {
    "guid": "nid-1-field_paragraphs-1",
    "title": "paragraph 1b"
  },
  "2": {
    "guid": "nid-2-field_paragraphs-0",
    "title": "paragraph 2a"
  },
}

Running the paragraph feeds import will create 3 paragraph entities.

Then running the node page feeds import will create 2 node entities.

During the node page feeds import, the entity presave event will use the node guid to search the database for any paragraph guids that match the pattern [node guid]-[paragraph field name]-[delta] and set the node's respective paragraph field to the discovered paragraph entities.
