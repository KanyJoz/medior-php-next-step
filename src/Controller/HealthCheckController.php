<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Controller;

use KanyJoz\AniMerged\Configuration;
use KanyJoz\AniMerged\Utils\ResponseFormatter;
use KanyJoz\AniMerged\Utils\StatusCode;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

readonly class HealthCheckController
{
    public function __construct(
        private Configuration $config,
        private ResponseFormatter $formatter,
    ) { }

    public function health(Request $request, Response $response, array $args): Response
    {
        return $this->formatter->writeJSON(
            $response,
            [ 'environment' => $this->config->get('app.environment') ],
            StatusCode::OK()
        );
    }
}