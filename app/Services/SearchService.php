<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SearchService
{
    /**
     * Parameter request yang dicadangkan dan bukan untuk filter 'where'.
     *
     * @var array
     */
    protected $reservedParams = [
        'relations',
        'orderBy',
        'orderDirection',
        'per_page',
        'page',
        'search',
    ];

    protected Builder $query;
    protected Request $request;
    protected array $tableColumns = [];
    protected array $searchableColumns = [];

    /**
     * Menerapkan semua logika search, filter, sort, dan paginasi ke query.
     *
     * @param Builder $query Query Eloquent yang akan dimodifikasi.
     * @param Request $request Request HTTP saat ini.
     * @param array $defaultRelations Relasi default dari controller.
     * @param array $searchableColumns Kolom yang diizinkan untuk pencarian keyword.
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection
     */
    public function handleSearch(
        Builder $query,
        Request $request,
        array $defaultRelations = [],
        array $searchableColumns = [],
    )
    {
        $this->query = $query;
        $this->request = $request;
        $this->tableColumns = Schema::getColumnListing($this->query->getModel()->getTable());
        $this->searchableColumns = $this->resolveSearchableColumns($searchableColumns);

        $this->applyRelations($defaultRelations);
        $this->applySearch();
        $this->applyFilters();
        $this->applySorting();

        // Mengembalikan hasil (Paginated atau Collection)
        return $this->getResults();
    }

    /**
     * Memuat relasi berdasarkan request 'relations' dan relasi default.
     *
     * @param array $defaultRelations
     */
    protected function applyRelations(array $defaultRelations)
    {
        $requestedRelations = $this->request->get('relations');
        $relationsToLoad = $defaultRelations;

        if ($requestedRelations) {
            $relationsToLoad = array_merge(
                $relationsToLoad,
                array_filter(explode(',', $requestedRelations))
            );
        }

        if (!empty($relationsToLoad)) {
            $this->query->with(array_unique($relationsToLoad));
        }
    }

    protected function applySearch(): void
    {
        $keyword = trim((string) $this->request->get('search', ''));

        if ($keyword === '' || empty($this->searchableColumns)) {
            return;
        }

        $this->query->where(function (Builder $builder) use ($keyword) {
            foreach ($this->searchableColumns as $index => $column) {
                $method = $index === 0 ? 'where' : 'orWhere';
                $builder->{$method}($column, 'like', "%{$keyword}%");
            }
        });
    }

    protected function applySorting()
    {
        $orderBy = $this->request->get('orderBy', 'id');
        $orderDirection = $this->request->get('orderDirection', 'desc');

        if (!in_array($orderBy, $this->tableColumns, true)) {
            $orderBy = 'id';
        }

        if (!in_array(strtolower($orderDirection), ['asc', 'desc'])) {
            $orderDirection = 'desc';
        }

        $this->query->orderBy($orderBy, $orderDirection);
    }

    /**
     * Menerapkan filter 'where' sederhana berdasarkan parameter request.
     */
    protected function applyFilters()
    {
        $filters = $this->request->except($this->reservedParams);

        if (empty($filters)) {
            return;
        }

        foreach ($filters as $field => $value) {
            if (in_array($field, $this->tableColumns, true) && $value !== null && $value !== '') {
                if (is_array($value)) {
                    $this->query->whereIn($field, $value);
                } else {
                    $this->query->where($field, $this->normalizeFilterValue($value));
                }
            }
        }
    }

    /**
     * Mendapatkan hasil akhir, baik paginasi atau semua data.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection
     */
    protected function getResults()
    {
        $perPage = $this->request->get('per_page', 15);

        if ($perPage == '-1' || strtolower((string) $perPage) == 'all') {
            return $this->query->get();
        }

        return $this->query->paginate((int) $perPage);
    }

    protected function resolveSearchableColumns(array $searchableColumns): array
    {
        $columns = empty($searchableColumns) ? $this->tableColumns : $searchableColumns;

        return array_values(array_intersect($columns, $this->tableColumns));
    }

    protected function normalizeFilterValue(mixed $value): mixed
    {
        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            if ($normalized === 'true') {
                return true;
            }

            if ($normalized === 'false') {
                return false;
            }
        }

        return $value;
    }
}
