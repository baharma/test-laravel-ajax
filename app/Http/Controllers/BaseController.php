<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    use ApiResponse;

    /**
     * @param string $model Model class
     * @param string|null $resourceClass Resource class
     * @param array $relations Default relations to load
     * @param string|null $createAction Custom action/service class for create
     * @param string|null $updateAction Custom action/service class for update
     * @param string|null $deleteAction Custom action/service class for delete
     */
    public function __construct(
        protected string $model,
        protected ?string $resourceClass = null,
        protected array $relations = [],
        protected array $searchable = [],
        protected ?string $createAction = null,
        protected ?string $updateAction = null,
        protected ?string $deleteAction = null,
    ) {}

    /**
     * Menampilkan daftar resource dengan filter, relasi, dan paginasi.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request, SearchService $searchService)
    {
        $query = $this->model::query();
        $results = $searchService->handleSearch(
            $query,
            $request,
            $this->relations,
            $this->searchable,
        );

        if ($this->usesResourceClass()) {
            return $this->resourceClass::collection($results);
        }

        return $this->apiSuccess($results, 'Data retrieved successfully');
    }

    protected function requestRelations()
    {
        $relations = request()->get('relations');

        if ($relations) {
            $this->relations = explode(',', $relations);
        }
    }

    protected function usesResourceClass(): bool
    {
        return filled($this->resourceClass);
    }
}
