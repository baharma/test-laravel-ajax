<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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

    public function show(Request $request, int|string $id)
    {
        $record = $this->findRecord($id);
        $record->load($this->resolveRelations($request));

        if ($this->usesResourceClass()) {
            return new $this->resourceClass($record);
        }

        return $this->apiSuccess($record, 'Data retrieved successfully');
    }

    public function store(Request $request)
    {
        $record = DB::transaction(function () use ($request) {
            $record = new $this->model();
            $record->fill($this->resolvePayload($request, $record));
            $record->save();

            return $record->fresh();
        });

        $record->load($this->resolveRelations($request));

        if ($this->usesResourceClass()) {
            return new $this->resourceClass($record);
        }

        return $this->apiSuccess($record, 'Data created successfully');
    }

    public function update(Request $request, int|string $id)
    {
        $record = DB::transaction(function () use ($request, $id) {
            $record = $this->findRecord($id);
            $record->fill($this->resolvePayload($request, $record));
            $record->save();

            return $record->fresh();
        });

        $record->load($this->resolveRelations($request));

        if ($this->usesResourceClass()) {
            return new $this->resourceClass($record);
        }

        return $this->apiSuccess($record, 'Data updated successfully');
    }

    public function destroy(int|string $id)
    {
        DB::transaction(function () use ($id) {
            $record = $this->findRecord($id);
            $record->delete();
        });

        return $this->apiSuccess([], 'Data deleted successfully');
    }

    protected function usesResourceClass(): bool
    {
        return filled($this->resourceClass);
    }

    protected function resolveRelations(Request $request): array
    {
        $requestedRelations = $request->get('relations');

        if (!filled($requestedRelations)) {
            return $this->relations;
        }

        return array_values(array_unique([
            ...$this->relations,
            ...array_filter(explode(',', $requestedRelations)),
        ]));
    }

    protected function findRecord(int|string $id): Model
    {
        return $this->model::query()->findOrFail($id);
    }

    protected function resolvePayload(Request $request, ?Model $record = null): array
    {
        $record ??= new $this->model();
        $fillable = $record->getFillable();
        $payload = $request->except(['_token', '_method', 'relations']);

        if (empty($fillable)) {
            return $payload;
        }

        return Arr::only($payload, $fillable);
    }
}
