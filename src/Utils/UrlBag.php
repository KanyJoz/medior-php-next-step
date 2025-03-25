<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Utils;

use KanyJoz\AniMerged\Exception\ModelNotFoundException;

// ...

class UrlBag
{
    /**
     * @throws ModelNotFoundException
     */
    public function readIdPathParam(array $args): int
    {
        // Check if the id URL param is a number
        $id = $args['id'];
        if (!is_numeric($id)) {
            throw new ModelNotFoundException('id must be integer');
        }

        // For existing item it should be at least 1
        $id = intval($id);
        if ($id < 1) {
            throw new ModelNotFoundException('id should be positive');
        }

        return $id;
    }

    // ...
    public function readString(
        array $queryParams,
        string $key,
        string $defaultValue
    ): string
    {
        $val = '';

        if (isset($queryParams[$key])) {
            $val = $queryParams[$key];
        }

        if ($val === '') {
            return $defaultValue;
        }

        return $val;
    }

    public function readCSV(
        array $queryParams,
        string $key,
        array $defaultValue
    ): array
    {
        $val = '';

        if (isset($queryParams[$key])) {
            $val = $queryParams[$key];
        }

        if ($val === '') {
            return $defaultValue;
        }

        return explode(',', $val);
    }

    public function readInt(
        array $queryParams,
        string $key,
        int $defaultValue
    ): int
    {
        $val = '';

        if (isset($queryParams[$key])) {
            $val = $queryParams[$key];
        }

        if ($val === '') {
            return $defaultValue;
        }

        $intVal = intval($val);
        if ($intVal === 0) {
            return 0;
        }

        return $intVal;
    }
}