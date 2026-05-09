<?php

namespace App\Entities\Patient\Contracts;

use App\Entities\Patient\Models\Patient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PatientServiceInterface
{
    public function paginate(?string $search, string $paymentFilter = 'all', int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Patient;

    public function findByTelephone(string $telephone): ?Patient;

    public function create(array $data): Patient;

    public function update(int $id, array $data): Patient;

    public function delete(int $id): void;

    public function restore(int $id): void;

    public function paginateTrashed(?string $search, int $perPage = 15): LengthAwarePaginator;

    /** @return \Illuminate\Database\Eloquent\Collection<int, Patient> */
    public function all(): \Illuminate\Database\Eloquent\Collection;

    /** @param int[] $patientIds @return array<int, float> */
    public function owedAmountsForPatients(array $patientIds): array;
}
