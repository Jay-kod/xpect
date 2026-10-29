<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Team;
use App\Models\TeamAlias;
use Illuminate\Database\Seeder;

class EuropeanLeaguesTeamSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            // ==========================================
            // SPAIN (La Liga) - 27 teams
            // ==========================================
            ['name' => 'Deportivo Alavés', 'short_name' => 'Alavés', 'country' => 'Spain', 'aliases' => ['Alaves', 'Deportivo Alaves']],
            ['name' => 'UD Almería', 'short_name' => 'Almería', 'country' => 'Spain', 'aliases' => ['Almeria', 'UD Almeria']],
            ['name' => 'Athletic Club', 'short_name' => 'Athletic Club', 'country' => 'Spain', 'aliases' => ['Ath Bilbao', 'Athletic Bilbao', 'Athletic']],
            ['name' => 'Club Atlético de Madrid', 'short_name' => 'Atlético Madrid', 'country' => 'Spain', 'aliases' => ['Ath Madrid', 'Atletico Madrid', 'Atletico']],
            ['name' => 'FC Barcelona', 'short_name' => 'Barcelona', 'country' => 'Spain', 'aliases' => ['Barcelona', 'Barca']],
            ['name' => 'Real Betis Balompié', 'short_name' => 'Real Betis', 'country' => 'Spain', 'aliases' => ['Betis', 'Real Betis']],
            ['name' => 'Cádiz CF', 'short_name' => 'Cádiz', 'country' => 'Spain', 'aliases' => ['Cadiz', 'Cadiz CF']],
            ['name' => 'RC Celta de Vigo', 'short_name' => 'Celta Vigo', 'country' => 'Spain', 'aliases' => ['Celta', 'Celta Vigo', 'RC Celta']],
            ['name' => 'SD Eibar', 'short_name' => 'Eibar', 'country' => 'Spain', 'aliases' => ['Eibar']],
            ['name' => 'Elche CF', 'short_name' => 'Elche', 'country' => 'Spain', 'aliases' => ['Elche']],
            ['name' => 'RCD Espanyol de Barcelona', 'short_name' => 'Espanyol', 'country' => 'Spain', 'aliases' => ['Espanol', 'Espanyol', 'RCD Espanyol']],
            ['name' => 'Getafe CF', 'short_name' => 'Getafe', 'country' => 'Spain', 'aliases' => ['Getafe']],
            ['name' => 'Girona FC', 'short_name' => 'Girona', 'country' => 'Spain', 'aliases' => ['Girona']],
            ['name' => 'Granada CF', 'short_name' => 'Granada', 'country' => 'Spain', 'aliases' => ['Granada']],
            ['name' => 'SD Huesca', 'short_name' => 'Huesca', 'country' => 'Spain', 'aliases' => ['Huesca']],
            ['name' => 'UD Las Palmas', 'short_name' => 'Las Palmas', 'country' => 'Spain', 'aliases' => ['Las Palmas']],
            ['name' => 'CD Leganés', 'short_name' => 'Leganés', 'country' => 'Spain', 'aliases' => ['Leganes', 'CD Leganes']],
            ['name' => 'Levante UD', 'short_name' => 'Levante', 'country' => 'Spain', 'aliases' => ['Levante']],
            ['name' => 'RCD Mallorca', 'short_name' => 'Mallorca', 'country' => 'Spain', 'aliases' => ['Mallorca', 'RCD Mallorca']],
            ['name' => 'CA Osasuna', 'short_name' => 'Osasuna', 'country' => 'Spain', 'aliases' => ['Osasuna']],
            ['name' => 'Real Madrid CF', 'short_name' => 'Real Madrid', 'country' => 'Spain', 'aliases' => ['Real Madrid']],
            ['name' => 'Sevilla FC', 'short_name' => 'Sevilla', 'country' => 'Spain', 'aliases' => ['Sevilla']],
            ['name' => 'Real Sociedad de Fútbol', 'short_name' => 'Real Sociedad', 'country' => 'Spain', 'aliases' => ['Sociedad', 'Real Sociedad']],
            ['name' => 'Valencia CF', 'short_name' => 'Valencia', 'country' => 'Spain', 'aliases' => ['Valencia']],
            ['name' => 'Real Valladolid CF', 'short_name' => 'Valladolid', 'country' => 'Spain', 'aliases' => ['Valladolid', 'Real Valladolid']],
            ['name' => 'Rayo Vallecano de Madrid', 'short_name' => 'Rayo Vallecano', 'country' => 'Spain', 'aliases' => ['Vallecano', 'Rayo Vallecano']],
            ['name' => 'Villarreal CF', 'short_name' => 'Villarreal', 'country' => 'Spain', 'aliases' => ['Villarreal']],

            // ==========================================
            // GERMANY (Bundesliga) - 24 teams
            // ==========================================
            ['name' => 'FC Augsburg', 'short_name' => 'Augsburg', 'country' => 'Germany', 'aliases' => ['Augsburg', 'FC Augsburg']],
            ['name' => 'FC Bayern München', 'short_name' => 'Bayern Munich', 'country' => 'Germany', 'aliases' => ['Bayern Munich', 'Bayern Muenchen', 'FC Bayern']],
            ['name' => 'DSC Arminia Bielefeld', 'short_name' => 'Bielefeld', 'country' => 'Germany', 'aliases' => ['Bielefeld', 'Arminia Bielefeld']],
            ['name' => 'VfL Bochum 1848', 'short_name' => 'Bochum', 'country' => 'Germany', 'aliases' => ['Bochum', 'VfL Bochum']],
            ['name' => 'SV Darmstadt 98', 'short_name' => 'Darmstadt', 'country' => 'Germany', 'aliases' => ['Darmstadt', 'SV Darmstadt']],
            ['name' => 'Borussia Dortmund', 'short_name' => 'Dortmund', 'country' => 'Germany', 'aliases' => ['Dortmund', 'BVB']],
            ['name' => 'Eintracht Frankfurt', 'short_name' => 'Eintracht Frankfurt', 'country' => 'Germany', 'aliases' => ['Ein Frankfurt', 'Eintracht Frankfurt', 'Frankfurt']],
            ['name' => '1. FC Köln', 'short_name' => 'Köln', 'country' => 'Germany', 'aliases' => ['FC Koln', 'Koln', 'Cologne', '1. FC Koln']],
            ['name' => 'SC Freiburg', 'short_name' => 'Freiburg', 'country' => 'Germany', 'aliases' => ['Freiburg', 'SC Freiburg']],
            ['name' => 'SpVgg Greuther Fürth', 'short_name' => 'Greuther Fürth', 'country' => 'Germany', 'aliases' => ['Greuther Furth', 'Fuerth', 'SpVgg Greuther Fuerth']],
            ['name' => '1. FC Heidenheim 1846', 'short_name' => 'Heidenheim', 'country' => 'Germany', 'aliases' => ['Heidenheim', '1. FC Heidenheim']],
            ['name' => 'Hertha BSC', 'short_name' => 'Hertha BSC', 'country' => 'Germany', 'aliases' => ['Hertha', 'Hertha Berlin', 'Hertha BSC']],
            ['name' => 'TSG 1899 Hoffenheim', 'short_name' => 'Hoffenheim', 'country' => 'Germany', 'aliases' => ['Hoffenheim', 'TSG Hoffenheim']],
            ['name' => 'Kieler SV Holstein 1900', 'short_name' => 'Holstein Kiel', 'country' => 'Germany', 'aliases' => ['Holstein Kiel', 'Kiel']],
            ['name' => 'Bayer 04 Leverkusen', 'short_name' => 'Leverkusen', 'country' => 'Germany', 'aliases' => ['Leverkusen', 'Bayer Leverkusen']],
            ['name' => 'Borussia Mönchengladbach', 'short_name' => 'Mönchengladbach', 'country' => 'Germany', 'aliases' => ["M'gladbach", 'Monchengladbach', 'Borussia Monchengladbach']],
            ['name' => '1. FSV Mainz 05', 'short_name' => 'Mainz', 'country' => 'Germany', 'aliases' => ['Mainz', 'FSV Mainz', 'Mainz 05']],
            ['name' => 'RB Leipzig', 'short_name' => 'RB Leipzig', 'country' => 'Germany', 'aliases' => ['RB Leipzig', 'Leipzig']],
            ['name' => 'FC Schalke 04', 'short_name' => 'Schalke', 'country' => 'Germany', 'aliases' => ['Schalke 04', 'Schalke', 'FC Schalke']],
            ['name' => 'FC St. Pauli 1910', 'short_name' => 'St. Pauli', 'country' => 'Germany', 'aliases' => ['St Pauli', 'FC St. Pauli', 'St. Pauli']],
            ['name' => 'VfB Stuttgart', 'short_name' => 'Stuttgart', 'country' => 'Germany', 'aliases' => ['Stuttgart', 'VfB Stuttgart']],
            ['name' => '1. FC Union Berlin', 'short_name' => 'Union Berlin', 'country' => 'Germany', 'aliases' => ['Union Berlin', '1. FC Union Berlin']],
            ['name' => 'SV Werder Bremen', 'short_name' => 'Werder Bremen', 'country' => 'Germany', 'aliases' => ['Werder Bremen', 'Bremen']],
            ['name' => 'VfL Wolfsburg', 'short_name' => 'Wolfsburg', 'country' => 'Germany', 'aliases' => ['Wolfsburg', 'VfL Wolfsburg']],

            // ==========================================
            // ITALY (Serie A) - 28 teams
            // ==========================================
            ['name' => 'Atalanta BC', 'short_name' => 'Atalanta', 'country' => 'Italy', 'aliases' => ['Atalanta']],
            ['name' => 'Benevento Calcio', 'short_name' => 'Benevento', 'country' => 'Italy', 'aliases' => ['Benevento']],
            ['name' => 'Bologna FC 1909', 'short_name' => 'Bologna', 'country' => 'Italy', 'aliases' => ['Bologna']],
            ['name' => 'Cagliari Calcio', 'short_name' => 'Cagliari', 'country' => 'Italy', 'aliases' => ['Cagliari']],
            ['name' => 'Como 1907', 'short_name' => 'Como', 'country' => 'Italy', 'aliases' => ['Como']],
            ['name' => 'US Cremonese', 'short_name' => 'Cremonese', 'country' => 'Italy', 'aliases' => ['Cremonese']],
            ['name' => 'FC Crotone', 'short_name' => 'Crotone', 'country' => 'Italy', 'aliases' => ['Crotone']],
            ['name' => 'Empoli FC', 'short_name' => 'Empoli', 'country' => 'Italy', 'aliases' => ['Empoli']],
            ['name' => 'ACF Fiorentina', 'short_name' => 'Fiorentina', 'country' => 'Italy', 'aliases' => ['Fiorentina']],
            ['name' => 'Frosinone Calcio', 'short_name' => 'Frosinone', 'country' => 'Italy', 'aliases' => ['Frosinone']],
            ['name' => 'Genoa CFC', 'short_name' => 'Genoa', 'country' => 'Italy', 'aliases' => ['Genoa']],
            ['name' => 'FC Internazionale Milano', 'short_name' => 'Inter', 'country' => 'Italy', 'aliases' => ['Inter', 'Inter Milan', 'Internazionale']],
            ['name' => 'Juventus FC', 'short_name' => 'Juventus', 'country' => 'Italy', 'aliases' => ['Juventus', 'Juve']],
            ['name' => 'SS Lazio', 'short_name' => 'Lazio', 'country' => 'Italy', 'aliases' => ['Lazio']],
            ['name' => 'US Lecce', 'short_name' => 'Lecce', 'country' => 'Italy', 'aliases' => ['Lecce']],
            ['name' => 'AC Milan', 'short_name' => 'Milan', 'country' => 'Italy', 'aliases' => ['Milan', 'AC Milan']],
            ['name' => 'AC Monza', 'short_name' => 'Monza', 'country' => 'Italy', 'aliases' => ['Monza']],
            ['name' => 'SSC Napoli', 'short_name' => 'Napoli', 'country' => 'Italy', 'aliases' => ['Napoli']],
            ['name' => 'Parma Calcio 1913', 'short_name' => 'Parma', 'country' => 'Italy', 'aliases' => ['Parma']],
            ['name' => 'AS Roma', 'short_name' => 'Roma', 'country' => 'Italy', 'aliases' => ['Roma', 'AS Roma']],
            ['name' => 'US Salernitana 1919', 'short_name' => 'Salernitana', 'country' => 'Italy', 'aliases' => ['Salernitana']],
            ['name' => 'UC Sampdoria', 'short_name' => 'Sampdoria', 'country' => 'Italy', 'aliases' => ['Sampdoria']],
            ['name' => 'US Sassuolo Calcio', 'short_name' => 'Sassuolo', 'country' => 'Italy', 'aliases' => ['Sassuolo']],
            ['name' => 'Spezia Calcio', 'short_name' => 'Spezia', 'country' => 'Italy', 'aliases' => ['Spezia']],
            ['name' => 'Torino FC', 'short_name' => 'Torino', 'country' => 'Italy', 'aliases' => ['Torino']],
            ['name' => 'Udinese Calcio', 'short_name' => 'Udinese', 'country' => 'Italy', 'aliases' => ['Udinese']],
            ['name' => 'Venezia FC', 'short_name' => 'Venezia', 'country' => 'Italy', 'aliases' => ['Venezia']],
            ['name' => 'Hellas Verona FC', 'short_name' => 'Verona', 'country' => 'Italy', 'aliases' => ['Verona', 'Hellas Verona']],

            // ==========================================
            // FRANCE (Ligue 1) - 26 teams
            // ==========================================
            ['name' => 'AC Ajaccio', 'short_name' => 'Ajaccio', 'country' => 'France', 'aliases' => ['Ajaccio']],
            ['name' => 'Angers SCO', 'short_name' => 'Angers', 'country' => 'France', 'aliases' => ['Angers']],
            ['name' => 'AJ Auxerre', 'short_name' => 'Auxerre', 'country' => 'France', 'aliases' => ['Auxerre']],
            ['name' => 'FC Girondins de Bordeaux', 'short_name' => 'Bordeaux', 'country' => 'France', 'aliases' => ['Bordeaux']],
            ['name' => 'Stade Brestois 29', 'short_name' => 'Brest', 'country' => 'France', 'aliases' => ['Brest']],
            ['name' => 'Clermont Foot 63', 'short_name' => 'Clermont', 'country' => 'France', 'aliases' => ['Clermont']],
            ['name' => 'Dijon FCO', 'short_name' => 'Dijon', 'country' => 'France', 'aliases' => ['Dijon']],
            ['name' => 'Le Havre AC', 'short_name' => 'Le Havre', 'country' => 'France', 'aliases' => ['Le Havre']],
            ['name' => 'RC Lens', 'short_name' => 'Lens', 'country' => 'France', 'aliases' => ['Lens']],
            ['name' => 'Lille OSC', 'short_name' => 'Lille', 'country' => 'France', 'aliases' => ['Lille', 'LOSC Lille']],
            ['name' => 'FC Lorient', 'short_name' => 'Lorient', 'country' => 'France', 'aliases' => ['Lorient']],
            ['name' => 'Olympique Lyonnais', 'short_name' => 'Lyon', 'country' => 'France', 'aliases' => ['Lyon', 'OL']],
            ['name' => 'Olympique de Marseille', 'short_name' => 'Marseille', 'country' => 'France', 'aliases' => ['Marseille', 'OM']],
            ['name' => 'FC Metz', 'short_name' => 'Metz', 'country' => 'France', 'aliases' => ['Metz']],
            ['name' => 'AS Monaco FC', 'short_name' => 'Monaco', 'country' => 'France', 'aliases' => ['Monaco', 'AS Monaco']],
            ['name' => 'Montpellier HSC', 'short_name' => 'Montpellier', 'country' => 'France', 'aliases' => ['Montpellier']],
            ['name' => 'FC Nantes', 'short_name' => 'Nantes', 'country' => 'France', 'aliases' => ['Nantes']],
            ['name' => 'OGC Nice', 'short_name' => 'Nice', 'country' => 'France', 'aliases' => ['Nice', 'OGC Nice']],
            ['name' => 'Nîmes Olympique', 'short_name' => 'Nîmes', 'country' => 'France', 'aliases' => ['Nimes', 'Nimes Olympique']],
            ['name' => 'Paris Saint-Germain FC', 'short_name' => 'PSG', 'country' => 'France', 'aliases' => ['Paris SG', 'PSG', 'Paris Saint-Germain']],
            ['name' => 'Stade de Reims', 'short_name' => 'Reims', 'country' => 'France', 'aliases' => ['Reims']],
            ['name' => 'Stade Rennais FC 1901', 'short_name' => 'Rennes', 'country' => 'France', 'aliases' => ['Rennes', 'Stade Rennais']],
            ['name' => 'AS Saint-Étienne', 'short_name' => 'Saint-Étienne', 'country' => 'France', 'aliases' => ['St Etienne', 'Saint-Etienne', 'ASSE']],
            ['name' => 'RC Strasbourg Alsace', 'short_name' => 'Strasbourg', 'country' => 'France', 'aliases' => ['Strasbourg']],
            ['name' => 'Toulouse FC', 'short_name' => 'Toulouse', 'country' => 'France', 'aliases' => ['Toulouse']],
            ['name' => 'ES Troyes AC', 'short_name' => 'Troyes', 'country' => 'France', 'aliases' => ['Troyes']],
        ];

        foreach ($teams as $teamData) {
            $aliases = $teamData['aliases'] ?? [];
            unset($teamData['aliases']);

            $team = Team::updateOrCreate(
                ['name' => $teamData['name']],
                $teamData
            );

            // Add the name and short name itself as aliases
            $allAliases = array_unique(array_merge([$team->name, $team->short_name], $aliases));

            foreach ($allAliases as $alias) {
                // Register for all sources (csv, football_data, api_football)
                foreach (['csv', 'football_data', 'api_football'] as $source) {
                    TeamAlias::updateOrCreate(
                        [
                            'source' => $source,
                            'alias' => $alias,
                        ],
                        [
                            'team_id' => $team->id,
                        ]
                    );
                }
            }
        }
    }
}

