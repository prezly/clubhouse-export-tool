<?php

use GuzzleHttp\Client;
use Nette\Utils\Json;

$http = require __DIR__ . '/src/bootstrap.php';

assert($http instanceof Client);

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
            $data = Json::decode($response->getBody()->getContents());
            foreach ($data as ['id' => $id]) {
                /*
                 * Because the search endpoint does return shortened story objects
                 * without comments and description, we also have to iterate over all story ids
                 * and fetch full story object with a separate request for every one of them.
                */
                $response = $http->get("https://api.clubhouse.io/api/v3/stories/{$id}");
                yield $response->getBody()->getContents();
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

    if (is_string($section)) {
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

