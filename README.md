This module connects feeds-imported entities with feeds-imported paragraphs.

This is done by adding a presave event to the entity feed import. Each
entity "feeds item guid" is used to lookup paragraphs with matching feeds item
guids.

The entity feed must map a feed item guid.

The simplest guid could be: [node id]

A more complex guid could take into account imports from multiple sites and be
prefixed with a site key: [site key]-[nid]

Paragraph feeds must map a feed item guid with this specific pattern:
[entity feed item guid]-[field machine name]-[delta]

The entity presave event will loop through every entity field. For each
paragraph field it will query the database for any paragraph guids that match
[entity feed item guid]-[field machine name] sorted by the full guid
(ie. including [delta]) ascending.

These paragraphs are set to the entity paragraph field.

Feed item guid mappings should be set as "unique" so duplicates are not
created.

Paragraph feeds imports should run first so that the paragraphs exist for other
entity feeds to lookup.

The presave event only triggers if feeds detects there are changes. This means
that in some situations the feed setting `Force Update` should be enabled.

## Example

Site has a node content type "page" with a paragraphs field "field_paragraphs".

Site also has a paragraph type with a text field "field_paragraph_title".

Node page feed mapping
- feeds item guid, json source: guid
- title, json source: title

Node page feed json import file
```
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
```
Paragraph feed mapping
- feeds item guid, json source: guid
- field_paragraph_title, json source: title

Paragraph feed json import file
```
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
```

Running the paragraph feeds import will create 3 paragraph entities.

Then running the node page feeds import will create 2 node entities.

During the node page feeds import, the entity presave event will use the node
guid to search the database for any paragraph guids that match the pattern
[node guid]-[paragraph field name]-[delta] and set the node's respective
paragraph field with the discovered paragraph entities.

Page 1 will have paragraph 1a and paragraph 1b attached to field_paragraphs.
Page 2 will have paragraph 2a attached to field_paragraphs.
