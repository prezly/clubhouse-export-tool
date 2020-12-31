<?php

use Dotenv\Exception\ValidationException;
use GuzzleHttp\Client;

if (! is_file(__DIR__ . '/../vendor/autoload.php')) {
    throw new LogicException('Please run `composer install`.');
}

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../', '.env');
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

return new Client([
    'headers' => [
        'Clubhouse-Token' => $token,
    ],
]);


