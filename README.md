# Clubhouse JSON export tool

PHP CLI tool to export all your Clubhouse data into a single JSON file

## Requirements

- PHP 7.3+
- JSON extension
- Composer

## Getting started

1. Clone the code with `git`:

  ```bash
  git clone git@github.com:prezly/clubhouse-export-tool.git
  ```
  
2. Install Composer dependencies:

   ```bash
   composer install
   ```
   
3. Put Clubhouse access token into the `.env` file.

   - Go to your Clubhouse dashboard.
   - Navigate to Settings > My Account > API Tokens and generate an environment-specific token.
     See Clubhouse documentation on obtaining a token: 
     https://help.clubhouse.io/hc/en-us/articles/205701199-Clubhouse-API-Tokens
   - Paste the token into `.env` replacing "paste-your-token-here" text.

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

Brought to you with :heart: by [Prezly](https://www.prezly.com/?utm_source=github&utm_campaign=clubhouse-export-tool).
