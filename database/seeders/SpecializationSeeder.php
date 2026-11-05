<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Specialization;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class SpecializationSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the starting list of medical specializations; safe to run again.
     */
    public function run(): void
    {
        $names = [
            'Alergologia',
            'Anestezjologia i intensywna terapia',
            'Angiologia',
            'Audiologia i foniatria',
            'Balneologia i medycyna fizykalna',
            'Chirurgia dziecięca',
            'Chirurgia klatki piersiowej',
            'Chirurgia naczyniowa',
            'Chirurgia ogólna',
            'Chirurgia onkologiczna',
            'Chirurgia plastyczna',
            'Chirurgia szczękowo-twarzowa',
            'Choroby płuc',
            'Choroby wewnętrzne',
            'Choroby zakaźne',
            'Dermatologia i wenerologia',
            'Diabetologia',
            'Diagnostyka laboratoryjna',
            'Endokrynologia',
            'Gastroenterologia',
            'Genetyka kliniczna',
            'Geriatria',
            'Ginekologia onkologiczna',
            'Hematologia',
            'Immunologia kliniczna',
            'Kardiochirurgia',
            'Kardiologia',
            'Kardiologia dziecięca',
            'Medycyna paliatywna',
            'Medycyna pracy',
            'Medycyna ratunkowa',
            'Medycyna rodzinna',
            'Medycyna sportowa',
            'Nefrologia',
            'Neonatologia',
            'Neurochirurgia',
            'Neurologia',
            'Neurologia dziecięca',
            'Okulistyka',
            'Onkologia i hematologia dziecięca',
            'Onkologia kliniczna',
            'Ortopedia i traumatologia narządu ruchu',
            'Otorynolaryngologia',
            'Patomorfologia',
            'Pediatria',
            'Położnictwo i ginekologia',
            'Psychiatria',
            'Psychiatria dzieci i młodzieży',
            'Radiologia i diagnostyka obrazowa',
            'Radioterapia onkologiczna',
            'Rehabilitacja medyczna',
            'Reumatologia',
            'Urologia',
        ];

        foreach ($names as $name) {
            Specialization::firstOrCreate(['name' => $name]);
        }
    }
}
