<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'treatment' => 'Détartrage et Polissage',
                'activities' => ['Examen parodontal', 'Détartrage supra-gingival', 'Détartrage sous-gingival', 'Polissage des surfaces', 'Aéro-polissage (Airflow)', 'Application de fluor / Vernis'],
            ],
            [
                'treatment' => 'Obturation Composite (Plombage)',
                'activities' => ['Anesthésie locale', 'Éviction de la carie', 'Pose du champ opératoire', 'Mise en place de la matrice', 'Mordançage et Adhésif', 'Pose du composite', 'Polissage et réglage'],
            ],
            [
                'treatment' => 'Traitement Endodontique (Dévitalisation)',
                'activities' => ['Ouverture de la chambre pulpaire', 'Détermination de la longueur', 'Mise en forme des canaux', 'Irrigation et désinfection', 'Obturation canalaire', 'Pansement provisoire'],
            ],
            [
                'treatment' => 'Extraction Simple / Chirurgicale',
                'activities' => ['Anesthésie locale', 'Syndesmotomie', 'Luxation et avulsion', 'Curetage de l\'alvéole', 'Hémostase', 'Suture'],
            ],
            [
                'treatment' => 'Couronne / Bridge',
                'activities' => ['Taille de la dent', 'Prise d\'empreinte', 'Pose d\'une couronne provisoire', 'Essayage de l\'armature', 'Scellement définitif'],
            ],
        ];

        foreach ($data as $item) {
            $treatmentId = DB::table('treatment_catalog')->insertGetId([
                'name' => $item['treatment'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($item['activities'] as $activity) {
                DB::table('activity_catalog')->insert([
                    'treatment_catalog_id' => $treatmentId,
                    'activity_name' => $activity,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
