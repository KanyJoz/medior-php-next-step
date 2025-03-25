<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Controller;

use Exception;
use KanyJoz\AniMerged\Exception\DatabaseException;
use KanyJoz\AniMerged\Exception\EditConflictException;
use KanyJoz\AniMerged\Exception\ModelNotFoundException;
use KanyJoz\AniMerged\Exception\ReturningException;
use KanyJoz\AniMerged\Model\Animation;
use KanyJoz\AniMerged\Model\Filters;
use KanyJoz\AniMerged\Model\Season;
use KanyJoz\AniMerged\Repository\AnimationRepositoryInterface;
use KanyJoz\AniMerged\Utils\ResponseFormatter;
use KanyJoz\AniMerged\Utils\StatusCode;
use KanyJoz\AniMerged\Utils\UrlBag;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// ...
readonly class AnimationController
{
    public function __construct(
        private UrlBag $urlBag,
        private ResponseFormatter $formatter,
        private AnimationRepositoryInterface $animations,
    ) {}

    // ...
    public function index(
        Request $request,
        Response $response,
        array $args
    ): Response
    {
        // Read query params
        $queryParams = $request->getQueryParams();

        // Parse query params
        $title = $this->urlBag->readString($queryParams, 'title', '');
        $genres = $this->urlBag->readCSV($queryParams, 'genres', []);

        $filters = new Filters();
        $filters->setPage($this->urlBag->readInt($queryParams, 'page', 1));
        $filters->setPageSize($this->urlBag->readInt($queryParams, 'page_size', 10));
        $filters->setSort($this->urlBag->readString($queryParams, 'sort', 'id'));
        $filters->setSortSafelist(
            ['id', 'title', 'year', 'season',
                '-id', '-title', '-year', '-season']);

        // Validate query params
        $validatedFilters = Filters::validate($filters);
        if (!$validatedFilters->valid) {
            return $this->
                formatter->failedValidation($response,
                    $validatedFilters->error);
        }

        // We use the new function to get the data from the database
        try {
            $animations = $this->animations
                ->getAll($title, $genres, $filters);
        } catch (DatabaseException|Exception $e) {
            return $this->
                formatter->serverError($response, $request, $e);
        }

        // We use the 'movies' envelope, and we have the asJson() for Movie object
        // So we use the array_map() to turn array of Movie objects to array of JSON array representations
        return $this->formatter->writeJSON(
            $response,
            array_map(
                function (Animation $animation) {
                    return $animation->asJson();
                },
                $animations
            ),
            envelope: 'animations'
        );
    }

    public function create(
        Request $request,
        Response $response,
        array $args
    ): Response
    {
        // Read json input from Request
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            return $this->formatter
                ->failedParsing($response, 'failed to parse request body as json');
        }

        // Validate the incoming data
        $validatedAnime = Animation::validate($body);
        if (!$validatedAnime->valid) {
            return $this->formatter
                ->failedValidation($response, $validatedAnime->error);
        }

        // Build the Animation DTO
        $animation = Animation::fromRequest($body);

        // We are inserting the animation to the database, then we grab the returned value
        try {
            $animation = $this->animations->insert($animation);
        } catch (DatabaseException|Exception|ReturningException $e) {
            return $this->formatter
                ->serverError($response, $request, $e);
        }

        // Send back the saved Animation in the Response
        return $this->formatter->
            writeJSON($response, $animation->asJson(),
                StatusCode::CREATED(), 'anime');
    }

    // ...
    public function show(
        Request $request,
        Response $response,
        array $args
    ): Response
    {
        // Read ID
        try {
            $id = $this->urlBag->readIdPathParam($args);
        } catch (ModelNotFoundException) {
            return $this->formatter->notFound($response);
        }

        // Find the animation
        try {
            $animation = $this->animations->get($id);
        } catch (ModelNotFoundException) {
            return $this->formatter->notFound($response);
        } catch (DatabaseException|Exception $e) {
            return $this->formatter->serverError($response, $request, $e);
        }

        // Send Response
        return $this->formatter
            ->writeJSON($response, $animation->asJson(), envelope: 'anime');
    }

    public function update(
        Request $request,
        Response $response,
        array $args
    ): Response
    {
        // Read the id of the animation from URL
        try {
            $id = $this->urlBag->readIdPathParam($args);
        } catch (ModelNotFoundException) {
            return $this->formatter->notFound($response);
        }

        // Find the animation
        try {
            $movie = $this->animations->get($id);
        } catch (ModelNotFoundException) {
            return $this->formatter->notFound($response);
        } catch (DatabaseException|Exception $e) {
            return $this->formatter->serverError($response, $request, $e);
        }

        // Read json input from Request
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            return $this->formatter
                ->failedParsing($response, 'failed to parse request body as json');
        }

        // Validate the incoming data, change to partial validation
        $validatedAnimation = Animation::validatePartially($body);
        if (!$validatedAnimation->valid) {
            return $this->formatter
                ->failedValidation($response, $validatedAnimation->error);
        }

        // Conditionally set the data
        if (isset($body['title'])) {
            $movie->setTitle($body['title']);
        }

        if (isset($body['year'])) {
            $movie->setYear($body['year']);
        }

        if (isset($body['season'])) {
            $movie->setSeason(Season::from($body['season']));
        }

        if (isset($body['genres'])) {
            $movie->setGenres($body['genres']);
        }

        // Update the animation
        try {
            $movie = $this->animations->update($movie);
        } catch (EditConflictException) {
            return $this->formatter->editConflict($response);
        } catch (ReturningException|DatabaseException $e) {
            return $this->formatter->serverError($response, $request, $e);
        }

        // Send Response
        return $this->formatter
            ->writeJSON($response, $movie->asJson(), envelope: 'anime');
    }

    // ...
    public function destroy(
        Request $request,
        Response $response,
        array $args
    ): Response
    {
        // Read the id of the animation from URL
        try {
            $id = $this->urlBag->readIdPathParam($args);
        } catch (ModelNotFoundException) {
            return $this->formatter->notFound($response);
        }

        // Delete the animation
        try {
            $this->animations->delete($id);
        } catch (ModelNotFoundException) {
            return $this->formatter->notFound($response);
        } catch (DatabaseException $e) {
            return $this->formatter->serverError($response, $request, $e);
        }

        // Send Response
        return $this->formatter
            ->writeJSON($response, ['message' => 'anime successfully deleted']);
    }
}