<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{
    /**
     * Get all records.
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Get paginated records.
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    /**
     * Find record by ID.
     */
    public function find(int $id, array $columns = ['*']): ?Model;

    /**
     * Find record by ID or fail.
     */
    public function findOrFail(int $id, array $columns = ['*']): Model;

    /**
     * Find record by specific attribute.
     */
    public function findBy(string $field, mixed $value, array $columns = ['*']): ?Model;

    /**
     * Create a new record.
     */
    public function create(array $data): Model;

    /**
     * Update existing record.
     */
    public function update(int $id, array $data): Model;

    /**
     * Delete record by ID.
     */
    public function delete(int $id): bool;

    /**
     * Eager load relationships.
     */
    public function with(array $relations): static;

    /**
     * Filter by conditions.
     */
    public function where(string $column, mixed $operator = null, mixed $value = null): static;

    /**
     * Order records by column.
     */
    public function orderBy(string $column, string $direction = 'asc'): static;
}
