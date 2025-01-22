<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use OpenAI\Client;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });

    $app->post('/generate-ideas', function ($request, $response) {
        // Get the JSON payload from the request
        $data = $request->getParsedBody();

        // Validate the input
        if (!isset($data['niche']) || empty($data['niche'])) {
            return $response->withJson(['error' => 'Please provide a niche'], 400);
        }

        // Initialize the OpenAI client
        $client = \OpenAI::client($_ENV['OPENAI_API_KEY']);

        // Generate ideas using the OpenAI API
        // $prompt = "Generate 5 creative content ideas for the niche: " . $data['niche'];
        $messages = [
            ['role' => 'system', 'content' => 'You are an AI that generates creative content ideas.'],
            ['role' => 'user', 'content' => 'Generate 5 creative content ideas for the niche: ' . $data['niche']],
        ];
        $result = $client->chat()->create([
            'model' => 'gpt-4-turbo',
            'messages' => $messages,
            'max_tokens' => 150,
        ]);

        // Extract the AI-generated text
        $ideas = $result['choices'][0]['message']['content'];

        // Respond with the generated ideas
        $response->getBody()->write(json_encode([
            'niche' => $data['niche'],
            'ideas' => $ideas,
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    });
};
