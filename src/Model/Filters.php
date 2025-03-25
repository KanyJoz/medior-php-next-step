<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Model;

use KanyJoz\AniMerged\DTO\Validated;
use KanyJoz\AniMerged\Validator\Validator;
use RuntimeException;

class Filters
{
    // Properties
    private int $page;
    private int $pageSize;
    private string $sort;
    private array $sortSafelist; // New field

    // Default values
    public function __construct()
    {
        $this->page = 0;
        $this->pageSize = 0;
        $this->sort = '';
        $this->sortSafelist = []; // New default value
    }

    public static function validate(Filters $filters): Validated
    {
        $v = new Validator();

        $v->check($v->gt($filters->getPage(), 0),
            'page', 'must be greater than zero');
        $v->check($v->le($filters->getPage(), 10000),
            'page', 'must be a maximum of 10 million');

        $v->check($v->gt($filters->getPageSize(), 0),
            'page_size', 'must be greater than zero');
        $v->check($v->le($filters->getPageSize(), 100),
            'page_size', 'must be a maximum of 100');

        $v->check($v->permitted($filters->getSort(),
            $filters->getSortSafelist()), 'sort', 'invalid sort value');

        return new Validated($v->valid(), $v->firstError());
    }

    // ...
    // Pagination
    public function limit(): int
    {
        return $this->pageSize;
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->pageSize;
    }
    // ...

    // Sorting
    public function sortColumn(): string
    {
        if (in_array($this->sort, $this->sortSafelist, true)) {
            if (str_starts_with($this->sort, '+') || str_starts_with($this->sort, '-')) {
                return substr(trim($this->sort), 1);
            }

            return trim($this->sort);
        }

        throw new RuntimeException('unsafe sort parameter: ' . $this->sort);
    }

    public function sortDirection(): string
    {
        if (str_starts_with(trim($this->sort), '-')) {
            return 'DESC';
        }

        return 'ASC';
    }

    // Setters and Getters
    // ...
    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): Filters
    {
        $this->page = $page;
        return $this;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function setPageSize(int $pageSize): Filters
    {
        $this->pageSize = $pageSize;
        return $this;
    }

    public function getSort(): string
    {
        return $this->sort;
    }

    public function setSort(string $sort): Filters
    {
        $this->sort = $sort;
        return $this;
    }

    public function getSortSafelist(): array
    {
        return $this->sortSafelist;
    }

    public function setSortSafelist(array $sortSafelist): Filters
    {
        $this->sortSafelist = $sortSafelist;
        return $this;
    }
}