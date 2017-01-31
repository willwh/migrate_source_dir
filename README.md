# migrate_source_dir

A Migrate Source Plugin to import files from a directory path in to Drupal Files.

Plugin Definition:
```
source:
  constants:
    uri_file: 'public://' #required
  plugin: directory
  track_changes: true
  urls:
    - /path/to/files/for/import
  file_extensions:
    - mp3
    - m4a
    - wav
  recurse_level: -1
```

This plugin provides the following fields:

```
path - Directory path to file
url - Full path to file, used as the ID for incoming sources
filename - The name of file
```

Use the plugin like so to import files in to Drupal:

```
langcode: en
status: true
dependencies: {  }
id: directory_mp3
migration_tags: directory
migration_group: null
label: 'mp3 id3 migration'
source:
  constants:
    uri_file: 'public://'
  plugin: directory
  track_changes: true
  urls:
    - /path/to/files/for/import
  file_extensions:
    - mp3
    - m4a
    - wav
  recurse_level: -1
process:
  source_full_path: url
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

To reference these files in other migrations, use the source property `url`.

In the follow example, I am referencing my previous migration `directory_mp3`, and passing to a file field called `your_file_field`.

```
Process:
  your_file_field:
    -
      plugin: migration
      migration: directory_mp3
      source: url
```
