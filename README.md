# Clubhouse JSON export tool

PHP CLI tool to export all your Clubhouse data into a single JSON file

## Usage

Export all your Clubhouse data into a single JSON file:

```bash
php export.php > data/clubhouse.json
```

Supported sections:
- `categories`
- `entity-templates`
- `epic-workflow`
- `epics`
- `files`
- `groups`
- `iterations`
- `labels`
- `linked-files`
- `members`
- `milestones`
- `projects`
- `repositories`
- `stories`
- `teams`
- `workflows`

### Exporting only sepcific sections

```bash
php export.php epics > data/epics.json
```


-----------------

Brought to you with :heart: by [Prezly](https://www.prezly.com/?utm_source=github&utm_campaign=slate).
