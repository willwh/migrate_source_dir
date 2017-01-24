# migrate_source_dir

Source definitions look something like this:

```
source:
  plugin: dir
  path: /some/path/to/search
  file_ext: mp3
  recurse: true
```

A full example of migrating mp3 files might look like so:

```
langcode: en
status: true
dependencies: {  }
id: mp3_migration
migration_tags: mp3
migration_group: null
label: 'mp3 id3 migration'
source:
  constants:
    uri_file: 'public://audio'
  plugin: dir
  track_changes: true
  path: /media/audio
  file_ext: mp3
  recurse: true
process:
  source_full_path: pathname
  uri_file:
    -
      plugin: concat
      delimiter: /
      source:
        - constants/uri_file
        - filename
    -
      plugin: urlencode
  filename: filename
  uri:
    plugin: file_copy
    source:
      - '@source_full_path'
      - '@uri_file'
destination:
  plugin: 'entity:file'
migration_dependencies:
  required: {  }
  optional: {  }
```
