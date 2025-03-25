<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Utils;

enum StatusCode: int
{
    // 200
    case Ok = 200;
    case Created = 201;

    // 400
    case BadRequest = 400;
    case Unauthorized = 401;
    case Forbidden = 403;
    case NotFound = 404;
    case MethodNotAllowed = 405;
    case Conflict = 409;
    case UnprocessableEntity = 422;
    case TooManyRequests = 429;

    // 500
    case InternalServerError = 500;

    // 200
    public static function OK(): int
    {
        return self::Ok->value;
    }

    public static function CREATED(): int
    {
        return self::Created->value;
    }

    // 400
    public static function BAD_REQUEST(): int
    {
        return self::BadRequest->value;
    }

    public static function UNAUTHORIZED(): int
    {
        return self::Unauthorized->value;
    }

    public static function FORBIDDEN(): int
    {
        return self::Forbidden->value;
    }

    public static function NOT_FOUND(): int
    {
        return self::NotFound->value;
    }

    public static function METHOD_NOT_ALLOWED(): int
    {
        return self::MethodNotAllowed->value;
    }

    public static function CONFLICT(): int
    {
        return self::Conflict->value;
    }

    public static function UNPROCESSABLE_ENTITY(): int
    {
        return self::UnprocessableEntity->value;
    }

    public static function TOO_MANY_REQUESTS(): int
    {
        return self::TooManyRequests->value;
    }

    // 500
    public static function INTERNAL_SERVER_ERROR(): int
    {
        return self::InternalServerError->value;
    }
}