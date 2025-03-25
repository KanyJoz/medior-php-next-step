<?php

declare(strict_types=1);

namespace KanyJoz\Tests\Repository;

use KanyJoz\AniMerged\Exception\ModelNotFoundException;
use KanyJoz\AniMerged\Model\Animation;
use KanyJoz\AniMerged\Model\Season;
use KanyJoz\AniMerged\Repository\AnimationRepositoryInterface;

class AnimationStubRepository implements AnimationRepositoryInterface
{
    #[\Override]
    public function insert(Animation $animation): Animation
    {
        // TODO: Implement insert() method.
        return new Animation();
    }

    #[\Override]
    public function get(int $id): Animation
    {
        // This is the method the show() method will call
        $animation = new Animation();

        // Let's simulate non-existent record with ID 4
        if ($id === 4) {
            throw new ModelNotFoundException();
        }

        // Every other ID will return this
        $animation->setTitle('DANDADAN');
        $animation->setYear(2024);
        $animation->setSeason(Season::SP);
        $animation->setGenres(['shonen', 'supernatural']);

        return $animation;
    }

    #[\Override]
    public function update(Animation $animation): Animation
    {
        // TODO: Implement update() method.
        return new Animation();
    }

    #[\Override]
    public function delete(int $id): void
    {
        // TODO: Implement delete() method.
    }
}