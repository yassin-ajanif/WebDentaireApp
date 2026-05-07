<?php

namespace App\Entities\Patient\Services;

use App\Entities\Appointment\Contracts\PatientLookupInterface;
use App\Entities\Patient\Contracts\PatientServiceInterface;
use App\Entities\Patient\Models\Patient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PatientService implements PatientLookupInterface, PatientServiceInterface
{
    public function exists(int $patientId): bool
    {
        return Patient::query()->whereKey($patientId)->exists();
    }

    public function paginate(?string $search, string $paymentFilter = 'all', int $perPage = 15): LengthAwarePaginator
    {
        $q = Patient::query()->orderByDesc('id');

        if ($search !== null && $search !== '') {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $like = '%'.mb_strtolower($escaped).'%';
            $q->where(function ($inner) use ($like) {
                $inner->whereRaw('LOWER(first_name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(last_name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(telephone) LIKE ?', [$like]);
            });
        }

        if ($paymentFilter === 'unpaid') {
            $q->whereHas('treatmentInfos', function ($inner): void {
                $inner->where('remaining_amount', '>', 0);
            });
        } elseif ($paymentFilter === 'paid') {
            $q->whereHas('treatmentInfos')
                ->whereDoesntHave('treatmentInfos', function ($inner): void {
                    $inner->where('remaining_amount', '>', 0);
                });
        }

        return $q->paginate($perPage);
    }

    public function find(int $id): ?Patient
    {
        return Patient::query()->find($id);
    }

    public function findByTelephone(string $telephone): ?Patient
    {
        return Patient::query()->where('telephone', trim($telephone))->first();
    }

    public function create(array $data): Patient
    {
        $telephone = trim($data['telephone']);

        if (Patient::query()->where('telephone', $telephone)->exists()) {
            throw ValidationException::withMessages([
                'telephone' => __('A patient with this telephone already exists.'),
            ]);
        }

        return Patient::query()->create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'telephone' => $telephone,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function update(int $id, array $data): Patient
    {
        $patient = Patient::query()->findOrFail($id);
        $telephone = trim($data['telephone']);

        if (Patient::query()->where('telephone', $telephone)->whereKeyNot($id)->exists()) {
            throw ValidationException::withMessages([
                'telephone' => __('A patient with this telephone already exists.'),
            ]);
        }

        $patient->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'telephone' => $telephone,
            'notes' => $data['notes'] ?? null,
        ]);

        return $patient->fresh();
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            Patient::query()->whereKey($id)->delete();
        });
    }
}
