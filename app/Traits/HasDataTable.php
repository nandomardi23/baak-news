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
            $field = $request->sort_field;
            
            // Legacy Mapping: id_tahun_akademik -> id_semester
            if ($field === 'id_tahun_akademik') {
                $field = 'id_semester';
            }

            $direction = $request->input('sort_direction', 'asc');
            
            // Safeguard: Check if column exists in the main table to avoid SQL 1054 error
            $tableName = $query->getModel()->getTable();
            $schema = $query->getModel()->getConnection()->getSchemaBuilder();
            
            if ($schema->hasColumn($tableName, $field)) {
                $query->orderBy($field, $direction);
            } else {
                \Illuminate\Support\Facades\Log::warning("HasDataTable: Column '{$field}' not found in table '{$tableName}'. Sorting skipped.");
            }
        }

        // 3. Handling Pagination
        $perPageParam = $request->input('per_page', $perPage);
        return $query->paginate($perPageParam)->withQueryString();
    }

    /**
     * Apply Data Table with allowed sorts, default sort field, and direction.
     *
     * @param Builder $query
     * @param Request $request
     * @param array $allowedSorts Columns allowed for sorting
     * @param string $defaultSortField Default column to sort by
     * @param string $defaultSortDirection Default sort direction (asc/desc)
     * @param int $perPage Items per page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function dataTableQuery(Builder $query, Request $request, array $allowedSorts = [], string $defaultSortField = 'created_at', string $defaultSortDirection = 'desc', int $perPage = 15)
    {
        // 1. Handling Sorting with allowed sorts whitelist
        $sortField = $request->input('sort_field', $defaultSortField);
        $sortDirection = $request->input('sort_direction', $defaultSortDirection);

        if (in_array($sortField, $allowedSorts)) {
            $tableName = $query->getModel()->getTable();
            $schema = $query->getModel()->getConnection()->getSchemaBuilder();

            if ($schema->hasColumn($tableName, $sortField)) {
                $query->orderBy($sortField, $sortDirection);
            } else {
                \Illuminate\Support\Facades\Log::warning("HasDataTable: Column '{$sortField}' not found in table '{$tableName}'. Sorting skipped.");
                $query->orderBy($defaultSortField, $defaultSortDirection);
            }
        } else {
            $query->orderBy($defaultSortField, $defaultSortDirection);
        }

        // 2. Handling Pagination
        $perPageParam = $request->input('per_page', $perPage);
        return $query->paginate($perPageParam)->withQueryString();
    }
}
