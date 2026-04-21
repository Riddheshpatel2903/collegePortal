<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

abstract class BaseApiController extends Controller
{
    use ApiResponseTrait;

    abstract protected function modelClass(): string;

    protected function withRelations(): array
    {
        return [];
    }

    public function index(Request $request)
    {
        $query = $this->modelClass()::query();

        if ($request->filled('search')) {
            if (method_exists($this->modelClass(), 'scopeSearch')) {
                $query->search($request->input('search'));
            }
        }

        $data = $query->with($this->withRelations())
            ->select($this->modelClass()::query()->getModel()->getTable().'.*')
            ->paginate($request->input('per_page', 15));

        return $this->success($data);
    }

    public function show($id)
    {
        try {
            $item = $this->modelClass()::with($this->withRelations())->findOrFail($id);

            return $this->success($item);
        } catch (ModelNotFoundException $e) {
            return $this->error('Resource not found', 404);
        }
    }

    protected function formRequestClass(string $action): ?string
    {
        $modelClass = $this->modelClass();
        $modelBase = class_basename($modelClass);
        $class = "App\\Http\\Requests\\{$action}{$modelBase}Request";

        return class_exists($class) ? $class : null;
    }

    public function store(Request $request)
    {
        $requestClass = $this->formRequestClass('Store');
        if ($requestClass) {
            $request = app($requestClass);
        }

        $model = $this->modelClass();
        $validated = method_exists($request, 'validated') ? $request->validated() : $request->all();
        $resource = $model::create($validated);

        return $this->success($resource, 'Created', 201);
    }

    public function update(Request $request, $id)
    {
        try {
            $model = $this->modelClass()::findOrFail($id);

            $requestClass = $this->formRequestClass('Update');
            if ($requestClass) {
                $request = app($requestClass);
            }

            $validated = method_exists($request, 'validated') ? $request->validated() : $request->all();
            $model->update($validated);

            return $this->success($model, 'Updated');
        } catch (ModelNotFoundException $e) {
            return $this->error('Resource not found', 404);
        }
    }

    public function destroy($id)
    {
        try {
            $model = $this->modelClass()::findOrFail($id);
            $model->delete();

            return $this->success(null, 'Deleted');
        } catch (ModelNotFoundException $e) {
            return $this->error('Resource not found', 404);
        }
    }
}
