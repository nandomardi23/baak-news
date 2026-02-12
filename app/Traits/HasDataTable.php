<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait HasDataTable
{
    /**
     * Apply Data Table filters (Search, Sort, Pagination)
     *
     * @param Builder $query
     * @param Request $request
     * @param array $searchableFields
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function applyDataTable(Builder $query, Request $request, array $searchableFields = [], int $perPage = 10)
    {
        // 1. Handling Search
        if ($request->filled('search') && !empty($searchableFields)) {
            $search = $request->search;
            $query->where(function ($q) use ($search, $searchableFields) {
                foreach ($searchableFields as $field) {
                    if (str_contains($field, '.')) {
                        $parts = explode('.', $field);
                        $col = array_pop($parts);
                        $rel = implode('.', $parts);

                        // If the first part is the model's table, it's just a table prefix, use orWhere
                        if ($rel === $q->getModel()->getTable()) {
                            $q->orWhere($field, 'like', "%{$search}%");
                        } else {
                            // Assume it's a relationship
                            $q->orWhereHas($rel, function ($q) use ($col, $search) {
                                $q->where($col, 'like', "%{$search}%");
                            });
                        }
                    } else {
                        $q->orWhere($field, 'like', "%{$search}%");
                    }
                }
            });
        }

        // 2. Handling Sorting
        if ($request->filled('sort_field')) {
            $direction = $request->input('sort_direction', 'asc');
            // Basic SQL order, can be extended for related models if needed
            $query->orderBy($request->sort_field, $direction);
        } else {
            // Default sort if provided or standard fallback
            // $query->latest(); // Default behavior can be overridden
        }

        // 3. Handling Pagination
        $perPageParam = $request->input('per_page', $perPage);
        return $query->paginate($perPageParam)->withQueryString();
    }
}
