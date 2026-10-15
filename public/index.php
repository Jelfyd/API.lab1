<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$dataFile = __DIR__ . '/../data/space.json';

$readItems = static function () use ($dataFile): array {
    $contents = file_get_contents($dataFile);
    $items = json_decode($contents === false ? '' : $contents, true);

    if (!is_array($items)) {
        throw new RuntimeException('Не вдалося прочитати файл даних.');
    }

    return $items;
};

$jsonResponse = static function (Response $response, array $payload, int $status = 200): Response {
    $response->getBody()->write((string) json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
    ));

    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus($status);
};

$app->get('/', static function (Request $request, Response $response) use ($jsonResponse): Response {
    return $jsonResponse($response, [
        'name' => 'Space API',
        'description' => 'Навчальний проєкт з фактами про космічні об’єкти.',
        'endpoints' => ['/api/space', '/api/space/{id}'],
    ]);
});

$app->get('/api/space', static function (Request $request, Response $response) use ($readItems, $jsonResponse): Response {
    return $jsonResponse($response, ['data' => $readItems()]);
});

$app->get('/api/space/{id}', static function (Request $request, Response $response, array $args) use ($readItems, $jsonResponse): Response {
    foreach ($readItems() as $item) {
        if ((string) $item['id'] === $args['id']) {
            return $jsonResponse($response, ['data' => $item]);
        }
    }

    return $jsonResponse($response, ['error' => 'Об’єкт не знайдено.'], 404);
});

$app->run();