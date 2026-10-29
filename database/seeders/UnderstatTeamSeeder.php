<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Team;
use App\Models\TeamAlias;
use Illuminate\Database\Seeder;

class UnderstatTeamSeeder extends Seeder
{
    public function run(): void
    {
        // Canonical Name => Understat Aliases
        $mappings = [
            // Premier League (England)
            'Arsenal FC' => ['Arsenal'],
            'Aston Villa FC' => ['Aston Villa'],
            'AFC Bournemouth' => ['Bournemouth'],
            'Brentford FC' => ['Brentford'],
            'Brighton & Hove Albion FC' => ['Brighton'],
            'Burnley FC' => ['Burnley'],
            'Chelsea FC' => ['Chelsea'],
            'Crystal Palace FC' => ['Crystal Palace'],
            'Everton FC' => ['Everton'],
            'Fulham FC' => ['Fulham'],
            'Ipswich Town FC' => ['Ipswich'],
            'Leeds United FC' => ['Leeds'],
            'Leicester City FC' => ['Leicester'],
            'Liverpool FC' => ['Liverpool'],
            'Luton Town FC' => ['Luton'],
            'Manchester City FC' => ['Manchester City'],
            'Manchester United FC' => ['Manchester United'],
            'Newcastle United FC' => ['Newcastle United'],
            'Norwich City FC' => ['Norwich'],
            'Nottingham Forest FC' => ['Nottingham Forest'],
            'Sheffield United FC' => ['Sheffield United'],
            'Southampton FC' => ['Southampton'],
            'Tottenham Hotspur FC' => ['Tottenham'],
            'Watford FC' => ['Watford'],
            'West Bromwich Albion FC' => ['West Bromwich Albion', 'West Brom'],
            'West Ham United FC' => ['West Ham'],
            'Wolverhampton Wanderers FC' => ['Wolverhampton Wanderers'],

            // La Liga (Spain)
            'Real Madrid CF' => ['Real Madrid'],
            'FC Barcelona' => ['Barcelona'],
            'Club Atlético de Madrid' => ['Atletico Madrid', 'Atlético Madrid'],
            'Real Sociedad de Fútbol' => ['Real Sociedad'],
            'Villarreal CF' => ['Villarreal'],
            'Real Betis Balompié' => ['Real Betis'],
            'Sevilla FC' => ['Sevilla'],
            'Athletic Club' => ['Athletic Club', 'Athletic Bilbao'],
            'Valencia CF' => ['Valencia'],
            'RC Celta de Vigo' => ['Celta Vigo', 'Celta de Vigo'],
            'CA Osasuna' => ['Osasuna'],
            'Getafe CF' => ['Getafe'],
            'Girona FC' => ['Girona'],
            'RCD Mallorca' => ['Mallorca'],
            'Rayo Vallecano de Madrid' => ['Rayo Vallecano'],
            'Deportivo Alavés' => ['Alaves', 'Alavés'],
            'Granada CF' => ['Granada'],
            'UD Las Palmas' => ['Las Palmas'],
            'Cádiz CF' => ['Cadiz', 'Cádiz'],
            'UD Almería' => ['Almeria', 'Almería'],
            'RCD Espanyol de Barcelona' => ['Espanyol'],
            'Elche CF' => ['Elche'],
            'Real Valladolid CF' => ['Real Valladolid', 'Valladolid'],
            'SD Eibar' => ['Eibar'],
            'SD Huesca' => ['Huesca'],
            'Levante UD' => ['Levante'],
            'CD Leganés' => ['Leganes', 'Leganés'],

            // Bundesliga (Germany)
            'FC Bayern München' => ['Bayern Munich'],
            'Borussia Dortmund' => ['Borussia Dortmund', 'Dortmund'],
            'RB Leipzig' => ['RasenBallsport Leipzig', 'RB Leipzig'],
            'Bayer 04 Leverkusen' => ['Bayer Leverkusen'],
            'Eintracht Frankfurt' => ['Eintracht Frankfurt'],
            'VfB Stuttgart' => ['VfB Stuttgart', 'Stuttgart'],
            'VfL Wolfsburg' => ['Wolfsburg', 'VfL Wolfsburg'],
            'Borussia Mönchengladbach' => ['Borussia M.Gladbach', 'Borussia Monchengladbach'],
            'Sport-Club Freiburg' => ['Freiburg', 'SC Freiburg'],
            '1. FSV Mainz 05' => ['Mainz 05', 'Mainz'],
            '1. FC Union Berlin' => ['Union Berlin'],
            'SV Werder Bremen' => ['Werder Bremen'],
            'TSG 1899 Hoffenheim' => ['Hoffenheim'],
            'FC Augsburg' => ['Augsburg', 'FC Augsburg'],
            '1. FC Heidenheim 1846' => ['FC Heidenheim', 'Heidenheim', '1. FC Heidenheim 1846'],
            'FC St. Pauli 1910' => ['St. Pauli', 'FC St. Pauli'],
            'VfL Bochum 1848' => ['Bochum', 'VfL Bochum'],
            'Holstein Kiel' => ['Holstein Kiel'],
            '1. FC Köln' => ['FC Cologne', 'FC Koln', 'FC Köln', 'FC Koeln'],
            'SV Darmstadt 98' => ['Darmstadt', 'SV Darmstadt 98'],
            'FC Schalke 04' => ['Schalke 04', 'Schalke'],
            'Hertha BSC' => ['Hertha Berlin', 'Hertha BSC'],
            'DSC Arminia Bielefeld' => ['Arminia Bielefeld', 'Bielefeld'],
            'SpVgg Greuther Fürth' => ['Greuther Fuerth', 'Greuther Fürth'],

            // Serie A (Italy)
            'FC Internazionale Milano' => ['Inter'],
            'AC Milan' => ['AC Milan', 'Milan'],
            'Juventus FC' => ['Juventus'],
            'SSC Napoli' => ['Napoli'],
            'Atalanta BC' => ['Atalanta'],
            'AS Roma' => ['Roma'],
            'SS Lazio' => ['Lazio'],
            'ACF Fiorentina' => ['Fiorentina'],
            'Bologna FC 1909' => ['Bologna'],
            'Torino FC' => ['Torino'],
            'AC Monza' => ['Monza'],
            'Genoa CFC' => ['Genoa'],
            'US Lecce' => ['Lecce'],
            'Udinese Calcio' => ['Udinese'],
            'Cagliari Calcio' => ['Cagliari'],
            'Empoli FC' => ['Empoli'],
            'Hellas Verona FC' => ['Verona', 'Hellas Verona'],
            'Parma Calcio 1913' => ['Parma Calcio 1913', 'Parma'],
            'Como 1907' => ['Como'],
            'Venezia FC' => ['Venezia'],
            'Frosinone Calcio' => ['Frosinone'],
            'US Sassuolo Calcio' => ['Sassuolo'],
            'US Salernitana 1919' => ['Salernitana'],
            'Spezia Calcio' => ['Spezia'],
            'UC Sampdoria' => ['Sampdoria'],
            'US Cremonese' => ['Cremonese'],
            'Benevento Calcio' => ['Benevento'],
            'FC Crotone' => ['Crotone'],

            // Ligue 1 (France)
            'Paris Saint-Germain FC' => ['Paris Saint Germain', 'Paris Saint-Germain'],
            'Olympique de Marseille' => ['Marseille'],
            'AS Monaco FC' => ['Monaco'],
            'Lille OSC' => ['Lille'],
            'Olympique Lyonnais' => ['Lyon'],
            'Stade Rennais FC 1901' => ['Rennes', 'Stade Rennais'],
            'OGC Nice' => ['Nice'],
            'Racing Club de Lens' => ['Lens'],
            'Stade de Reims' => ['Reims', 'Stade de Reims'],
            'Stade Brestois 29' => ['Brest', 'Stade Brestois 29'],
            'Montpellier HSC' => ['Montpellier'],
            'RC Strasbourg Alsace' => ['Strasbourg'],
            'Toulouse FC' => ['Toulouse'],
            'FC Nantes' => ['Nantes'],
            'Le Havre AC' => ['Le Havre'],
            'AJ Auxerre' => ['Auxerre'],
            'Angers SCO' => ['Angers'],
            'AS Saint-Étienne' => ['Saint-Etienne', 'Saint-Étienne'],
            'FC Lorient' => ['Lorient'],
            'FC Metz' => ['Metz'],
            'Clermont Foot 63' => ['Clermont Foot', 'Clermont'],
            'ES Troyes AC' => ['Troyes'],
            'AC Ajaccio' => ['Ajaccio'],
            'FC Girondins de Bordeaux' => ['Bordeaux'],
            'Nîmes Olympique' => ['Nimes', 'Nîmes'],
            'Dijon FCO' => ['Dijon'],
        ];

        foreach ($mappings as $canonicalName => $aliases) {
            $team = Team::where('name', $canonicalName)->first();
            if (!$team) {
                continue;
            }

            foreach ($aliases as $alias) {
                TeamAlias::firstOrCreate([
                    'team_id' => $team->id,
                    'source' => 'understat',
                    'alias' => $alias,
                ]);
            }
        }
    }
}
