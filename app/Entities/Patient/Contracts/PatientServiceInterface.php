<?php

namespace App\Entities\Patient\Contracts;

use App\Entities\Patient\Models\Patient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PatientServiceInterface
{
    public function paginate(?string $search, int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Patient;

    public function findByTelephone(string $telephone): ?Patient;

    public function create(array $data): Patient;

    public function update(int $id, array $data): Patient;

    public function delete(int $id): void;
}
