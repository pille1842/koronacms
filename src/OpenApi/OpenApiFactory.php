<?php

namespace App\OpenApi;

use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Core\OpenApi\OpenApi;
use ApiPlatform\Core\OpenApi\Model;

class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated,
    ) {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);

        $openApi = $openApi->withComponents($openApi->getComponents()->withSchemas(
            new \ArrayObject(array_merge($openApi->getComponents()->getSchemas()->getArrayCopy(), [
                'Login-user.input' => [
                    'type' => 'object',
                    'required' => [
                        'username',
                        'password',
                    ],
                    'properties' => [
                        'username' => [
                            'type' => 'string',
                        ],
                        'password' => [
                            'type' => 'string',
                        ],
                    ],
                ],
            ]))
        ));

        $openApi->getPaths()->addPath('/api/login', new Model\PathItem(
            null,
            null,
            null,
            new Model\Operation(
                null,
                ['Login'],
                [
                    '200' => new Model\Response(
                        'User',
                        new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/User-user.output',
                                ],
                            ],
                        ])
                    ),
                ],
                'Retrieves the currently authenticated user.',
                'Retrieves the currently authenticated user.',
            ),
            null,
            new Model\Operation(
                null,
                ['Login'],
                [
                    '200' => new Model\Response(
                        'User',
                        new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/User-user.output',
                                ],
                            ],
                        ])
                    ),
                    '400' => new Model\Response('Invalid input'),
                    '401' => new Model\Response('Unauthorized'),
                ],
                'Logs in a user.',
                'Logs in a user with username and password credentials.',
                null,
                [],
                new Model\RequestBody('', new \ArrayObject([
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Login-user.input',
                        ],
                        'example' => [
                            'username' => 'johndoe',
                            'password' => 'hunter2',
                        ],
                    ],
                ]))
            )
        ));

        return $openApi;
    }
}
