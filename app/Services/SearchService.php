<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
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

    /**
     * Menerapkan semua logika search, filter, sort, dan paginasi ke query.
     *
     * @param Builder $query Query Eloquent yang akan dimodifikasi.
     * @param Request $request Request HTTP saat ini.
     * @param array $defaultRelations Relasi default dari controller.
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection
     */
    public function handleSearch(Builder $query, Request $request, array $defaultRelations = [])
    {
        $this->query = $query;
        $this->request = $request;

        $this->applyRelations($defaultRelations);
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

    protected function applySorting()
    {
        $orderBy = $this->request->get('orderBy', 'id');
        $orderDirection = $this->request->get('orderDirection', 'desc');

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


        $tableColumns = Schema::getColumnListing($this->query->getModel()->getTable());

        foreach ($filters as $field => $value) {
            if (in_array($field, $tableColumns) && $value !== null && $value !== '') {
                if (is_array($value)) {
                    $this->query->whereIn($field, $value);
                } else {
                    $this->query->where($field, $value);
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
        if ($perPage == '-1' || strtolower($perPage) == 'all') {
            return $this->query->get();
        }
        return $this->query->paginate((int)$perPage);
    }
}
