# mscharl/changelog
![Work in Progress](https://img.shields.io/badge/maintenance-WIP-informational)

This package is inspired by the [Gitlab changelog tools](https://docs.gitlab.com/ce/development/changelog.html). It's stripped down to fit the workflow and requirements of most of my projects.

## Creating a changelog entry

A new line for the changelog is created for each file found in your configured `unrelease_dir`.

The file is created like this:
```yaml
type: 'FEATURE'
title: 'We added a new command to dynamically create changelog lines to avoid merge confilcts between feature branches.'
ticket_id: 717
ticket_url: 'https://my-ticket.system/ticket/7f7241a2-b9ce-4218-9187-ddc58782ba37'
merge_request_id: 6615
other:
    - 'POST DEPLOY: Enable the command in the Pipeline for the `develop` branch'
``` 

## Installation

```bash
composer require mscharl/changelog
```

Add the [configuration](#configuration) and [entry template](#entry-template) as required.

## Configuration
To configure the generator you can put a `.changelog.yaml` into you project root.
```yaml
# The path to the changes file that will be generated.
# Should be relative to the project root.
changes_file: 'CHANGES.md'

# The directory path where all unreleased entries are located.
# Should be relative to the project root.
unreleased_dir: 'changelog/unreleased'

# Can be a path to a twig template or a string with placeholders.
entry_template: '<changelog_package>/templates/entry.md.twig'
```

## Entry Template

The Template is rendered by the [Twig](https://twig.symfony.com) template engine. 

In the config file you can configure either a string or path to a twig file. Both methods are handled the same way.

The [entry files](#creating-a-changelog-entry) properties are passed as variables to Twig.

__Note:__ You can't reference any external files within your entry template.   
