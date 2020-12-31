<?php

use Dotenv\Exception\ValidationException;
use GuzzleHttp\Client;
use Nette\Utils\Json;

if (! is_file(__DIR__ . '/vendor/autoload.php')) {
    throw new LogicException('Please run `composer install`.');
}

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $dotenv->required('CLUBHOUSE_TOKEN');
} catch (ValidationException $exception) {
    error_log($exception->getMessage());
    exit(1);
}

$token = $_ENV['CLUBHOUSE_TOKEN'];

if ($token === 'paste-your-token-here') {
    error_log('Please set `CLUBHOUSE_TOKEN` variable to your Clubhouse Access token in `.env` file.');
    exit(1);
}

if (array_intersect(['help', '--help', '?'], array_slice($argv, 1))) {
    error_log(
        <<<HELP
        Usage:
            php export.php [sections] > clubhouse-export.json
        
        Arguments:
            sections              Optional list of sections to output. 
                                  If `sections` is omitted, all sections will be exported.
                                   
                                  Supported sections:
                                  - categories
                                  - entity-templates
                                  - epic-workflow
                                  - epics
                                  - files
                                  - groups
                                  - iterations
                                  - labels
                                  - linked-files
                                  - members
                                  - milestones
                                  - projects
                                  - repositories
                                  - stories
                                  - teams
                                  - workflows
        
        Help:
            php export.php help   Outputs this message.
        or 
            php export.php --help
        
        HELP,
    );

    exit(0);
}

$http = new Client([
    'headers' => [
        'Clubhouse-Token' => $token,
    ],
]);

$config = [
    'categories'       => 'https://api.clubhouse.io/api/v3/categories',
    'entity-templates' => 'https://api.clubhouse.io/api/v3/entity-templates',
    'epic-workflow'    => 'https://api.clubhouse.io/api/v3/epic-workflow',
    'epics'            => 'https://api.clubhouse.io/api/v3/epics',
    'files'            => 'https://api.clubhouse.io/api/v3/files',
    'groups'           => 'https://api.clubhouse.io/api/v3/groups',
    'iterations'       => 'https://api.clubhouse.io/api/v3/iterations',
    'labels'           => 'https://api.clubhouse.io/api/v3/labels',
    'linked-files'     => 'https://api.clubhouse.io/api/v3/linked-files',
    'members'          => 'https://api.clubhouse.io/api/v3/members',
    'milestones'       => 'https://api.clubhouse.io/api/v3/milestones',
    'projects'         => 'https://api.clubhouse.io/api/v3/projects',
    'repositories'     => 'https://api.clubhouse.io/api/v3/repositories',
    'stories'          => function (Client $http): iterable {
        /*
         * Stuff gets tricky with stories, as there is not endpoint to list all stories.
         * So we have to use `/stories/search` endpoint by searching for every story type separately.
         */
        foreach (['feature', 'bug', 'chore'] as $type) {
            $response = $http->post('https://api.clubhouse.io/api/v3/stories/search', [
                'json' => ['story_type' => $type],
            ]);
            $data = Json::decode($response->getBody()->getContents(), JSON::FORCE_ARRAY);
            foreach ($data as ['id' => $id]) {
                error_log("Exporting story #{$id}");
                /*
                 * Because the search endpoint does return shortened story objects
                 * without comments and description, we also have to iterate over all story ids
                 * and fetch full story object with a separate request for every one of them.
                */
                $response = $http->get("https://api.clubhouse.io/api/v3/stories/{$id}");
                $story = Json::decode($response->getBody()->getContents(), Json::FORCE_ARRAY);
                $response = $http->get("https://api.clubhouse.io/api/v3/stories/{$id}/history");
                $history = Json::decode($response->getBody()->getContents(), Json::FORCE_ARRAY);
                yield Json::encode(array_merge($story, ['history' => $history]));
            }
        }
    },
    'teams'            => 'https://api.clubhouse.io/api/v3/teams',
    'workflows'        => 'https://api.clubhouse.io/api/v3/teams',
];

$sections = array_slice($argv, 1) ?: array_keys($config);

foreach ($sections as $section) {
    if (! isset($config[$section])) {
        throw new InvalidArgumentException("Unsupported section given: `{$section}`. Please check command help.");
    }
}

echo '{', PHP_EOL;

foreach (array_values($sections) as $i => $section) {
    error_log("Exporting {$section}");

    if ($i > 0) {
        echo ',', PHP_EOL;
    }

    $source = $config[$section];

    if (is_string($source)) {
        $response = $http->get($source);
        echo json_encode($section), ':', $response->getBody()->getContents(), PHP_EOL;
    }

    if ($source instanceof Closure) {
        echo json_encode($section), ':[';
        foreach ($source($http) as $j => $content) {
            if ($j > 0) {
                echo ',';
            }
            echo $content;
        }
        echo ']', PHP_EOL;
    }
}

echo '}', PHP_EOL;

error_log('Done!');

