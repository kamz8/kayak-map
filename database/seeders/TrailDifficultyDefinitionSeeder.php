<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Enums\Difficulty;

class TrailDifficultyDefinitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('trail_difficulty_definition')->insert([
            [
                'accessibility' => 'Dostępne dla początkujących',
                'conditions' => 'Małe zbiorniki o wodzie stojącej, na których nie występują duże fale. Rzeki o słabym prądzie. Przeszkody i zjawiska wpływające na trudność szlaku w zasadzie nie występują.',
                'label' => 'bardzo łatwo',
                'code' => 'ZWA',
                'difficulty_level' => Difficulty::EASY->value
            ],
            [
                'accessibility' => 'Dostępne dla początkujących',
                'conditions' => 'Średniej wielkości zbiorniki wodne. Rzeki o dość silnym prądzie, występują przeszkody i zjawiska lekko podwyższające trudność.',
                'label' => 'łatwo',
                'code' => 'ZWB',
                'difficulty_level' => Difficulty::EASY->value
            ],
            [
                'accessibility' => 'Dostępne dla początkujących pod opieką wprawnych',
                'conditions' => 'Duże zbiorniki wodne. Rzeki o szybkim nurcie. Małe regularne bystrza, ostre zakręty, mielizny i ławice. Duża ilość przeszkód wymagających dokładnego manewrowania.',
                'label' => 'nieco trudno',
                'code' => 'ZWC',
                'difficulty_level' => Difficulty::MODERATE->value
            ],
            [
                'accessibility' => 'Dostępne dla początkujących pod opieką wprawnych',
                'conditions' => 'Rzeki górskie o znacznym prądzie, rzadko występujące duże kamienie, bystrza o szerokich przejściach, fale regularne, częste przemiały. Trudności wynikające z charakteru koryta rzeki są łatwe do rozpoznania.',
                'label' => 'umiarkowanie trudno',
                'code' => 'WW I',
                'difficulty_level' => Difficulty::MODERATE->value
            ],
            [
                'accessibility' => 'Dostępne tylko dla wprawnych',
                'conditions' => 'Rzeki górskie o szybkim prądzie z podwodnymi skałami i głazami, pojawiają się małe progi z regularnymi odwojami i duże fale.',
                'label' => 'dość trudno',
                'code' => 'WW II',
                'difficulty_level' => Difficulty::HARD->value
            ],
            [
                'accessibility' => 'Dostępne tylko dla wprawnych',
                'conditions' => 'Rzeki górskie o silnym nurcie z dużymi falami, częste małe odwoje, małe progi, nieduże szachownice. Wprawni mogą płynąć z marszu bez asekuracji z lądu.',
                'label' => 'trudno',
                'code' => 'WW III',
                'difficulty_level' => Difficulty::HARD->value
            ],
            [
                'accessibility' => 'Dostępne tylko dla wprawnych',
                'conditions' => 'Rzeki górskie o bardzo silnym nurcie, duże, często nieregularne fale, występują nieregularne i skośne odwoje, szachownice z dużych kamieni przesłaniają dalszą trasę. Trudności z zatrzymaniem się. Wymagane na pewnych odcinkach rozpoznanie i asekuracja z brzegu.',
                'label' => 'bardzo trudno',
                'code' => 'WW IV',
                'difficulty_level' => Difficulty::HARD->value
            ],
            [
                'accessibility' => 'Dostępne tylko dla bardzo wprawnych',
                'conditions' => 'Rzeki górskie o bardzo szybkim nurcie, duże progi, silne nieregularne odwoje, wysokie nieregularne fale. Bardzo duże spadki, bardzo duże trudności z zatrzymaniem się. Spływ niebezpieczny. Rozpoznanie z lądu i asekuracja konieczne.',
                'label' => 'nadzwyczaj trudno',
                'code' => 'WW V',
                'difficulty_level' => Difficulty::HARD->value
            ],
            [
                'accessibility' => 'Dostępne tylko dla bardzo wprawnych',
                'conditions' => 'Przepłynięcie na granicy ludzkich możliwości nawet przez kajakarzy posiadających bardzo wysokie możliwości techniczne.',
                'label' => 'WW VI - skrajnie trudno',
                'code' => 'WW VI',
                'difficulty_level' => Difficulty::HARD->value
            ]
        ]);
    }
}
