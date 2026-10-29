<?php

declare(strict_types=1);

namespace App\Services\Motm;

use App\Models\Team;

class TeamRosterRegistry
{
    /**
     * Map of canonical clubs to confirmed authentic starting XI players.
     * Each entry contains: name, number, position (GK, D, M, F).
     */
    protected static array $clubRosters = [
        // PREMIER LEAGUE
        'manchester city fc' => [
            ['name' => 'Erling Haaland', 'number' => 9, 'pos' => 'F', 'role' => 'striker'],
            ['name' => 'Kevin De Bruyne', 'number' => 17, 'pos' => 'M', 'role' => 'playmaker'],
            ['name' => 'Phil Foden', 'number' => 47, 'pos' => 'M', 'role' => 'winger'],
            ['name' => 'Rodri', 'number' => 16, 'pos' => 'M', 'role' => 'anchor'],
            ['name' => 'Bernardo Silva', 'number' => 20, 'pos' => 'M', 'role' => 'midfielder'],
            ['name' => 'Jack Grealish', 'number' => 10, 'pos' => 'F', 'role' => 'winger'],
            ['name' => 'Joško Gvardiol', 'number' => 24, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Rúben Dias', 'number' => 3, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Manuel Akanji', 'number' => 25, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Kyle Walker', 'number' => 2, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Ederson', 'number' => 31, 'pos' => 'GK', 'role' => 'goalkeeper'],
        ],
        'arsenal fc' => [
            ['name' => 'Bukayo Saka', 'number' => 7, 'pos' => 'F', 'role' => 'winger'],
            ['name' => 'Kai Havertz', 'number' => 29, 'pos' => 'F', 'role' => 'striker'],
            ['name' => 'Gabriel Martinelli', 'number' => 11, 'pos' => 'F', 'role' => 'winger'],
            ['name' => 'Martin Ødegaard', 'number' => 8, 'pos' => 'M', 'role' => 'playmaker'],
            ['name' => 'Declan Rice', 'number' => 41, 'pos' => 'M', 'role' => 'anchor'],
            ['name' => 'Thomas Partey', 'number' => 5, 'pos' => 'M', 'role' => 'midfielder'],
            ['name' => 'Ben White', 'number' => 4, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'William Saliba', 'number' => 2, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Gabriel Magalhães', 'number' => 6, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Jurriën Timber', 'number' => 12, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'David Raya', 'number' => 22, 'pos' => 'GK', 'role' => 'goalkeeper'],
        ],
        'liverpool fc' => [
            ['name' => 'Mohamed Salah', 'number' => 11, 'pos' => 'F', 'role' => 'winger'],
            ['name' => 'Luis Díaz', 'number' => 7, 'pos' => 'F', 'role' => 'winger'],
            ['name' => 'Darwin Núñez', 'number' => 9, 'pos' => 'F', 'role' => 'striker'],
            ['name' => 'Dominik Szoboszlai', 'number' => 8, 'pos' => 'M', 'role' => 'playmaker'],
            ['name' => 'Alexis Mac Allister', 'number' => 10, 'pos' => 'M', 'role' => 'midfielder'],
            ['name' => 'Ryan Gravenberch', 'number' => 38, 'pos' => 'M', 'role' => 'anchor'],
            ['name' => 'Trent Alexander-Arnold', 'number' => 66, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Ibrahima Konaté', 'number' => 5, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Virgil van Dijk', 'number' => 4, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Andy Robertson', 'number' => 26, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Alisson Becker', 'number' => 1, 'pos' => 'GK', 'role' => 'goalkeeper'],
        ],
        'chelsea fc' => [
            ['name' => 'Cole Palmer', 'number' => 20, 'pos' => 'M', 'role' => 'playmaker'],
            ['name' => 'Nicolas Jackson', 'number' => 15, 'pos' => 'F', 'role' => 'striker'],
            ['name' => 'Noni Madueke', 'number' => 11, 'pos' => 'F', 'role' => 'winger'],
            ['name' => 'Enzo Fernández', 'number' => 8, 'pos' => 'M', 'role' => 'midfielder'],
            ['name' => 'Moisés Caicedo', 'number' => 25, 'pos' => 'M', 'role' => 'anchor'],
            ['name' => 'Roméo Lavia', 'number' => 45, 'pos' => 'M', 'role' => 'midfielder'],
            ['name' => 'Malo Gusto', 'number' => 27, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Wesley Fofana', 'number' => 29, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Levi Colwill', 'number' => 6, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Marc Cucurella', 'number' => 3, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Robert Sánchez', 'number' => 1, 'pos' => 'GK', 'role' => 'goalkeeper'],
        ],
        'manchester united fc' => [
            ['name' => 'Marcus Rashford', 'number' => 10, 'pos' => 'F', 'role' => 'winger'],
            ['name' => 'Rasmus Højlund', 'number' => 9, 'pos' => 'F', 'role' => 'striker'],
            ['name' => 'Alejandro Garnacho', 'number' => 17, 'pos' => 'F', 'role' => 'winger'],
            ['name' => 'Bruno Fernandes', 'number' => 8, 'pos' => 'M', 'role' => 'playmaker'],
            ['name' => 'Kobbie Mainoo', 'number' => 37, 'pos' => 'M', 'role' => 'midfielder'],
            ['name' => 'Casemiro', 'number' => 18, 'pos' => 'M', 'role' => 'anchor'],
            ['name' => 'Diogo Dalot', 'number' => 20, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Matthijs de Ligt', 'number' => 4, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Lisandro Martínez', 'number' => 6, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Noussair Mazraoui', 'number' => 3, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'André Onana', 'number' => 24, 'pos' => 'GK', 'role' => 'goalkeeper'],
        ],
        'tottenham hotspur fc' => [
            ['name' => 'Son Heung-min', 'number' => 7, 'pos' => 'F', 'role' => 'winger'],
            ['name' => 'Dominic Solanke', 'number' => 19, 'pos' => 'F', 'role' => 'striker'],
            ['name' => 'Brennan Johnson', 'number' => 22, 'pos' => 'F', 'role' => 'winger'],
            ['name' => 'James Maddison', 'number' => 10, 'pos' => 'M', 'role' => 'playmaker'],
            ['name' => 'Dejan Kulusevski', 'number' => 21, 'pos' => 'M', 'role' => 'midfielder'],
            ['name' => 'Yves Bissouma', 'number' => 8, 'pos' => 'M', 'role' => 'anchor'],
            ['name' => 'Pedro Porro', 'number' => 23, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Cristian Romero', 'number' => 17, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Micky van de Ven', 'number' => 37, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Destiny Udogie', 'number' => 13, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Guglielmo Vicario', 'number' => 1, 'pos' => 'GK', 'role' => 'goalkeeper'],
        ],
        'aston villa fc' => [
            ['name' => 'Ollie Watkins', 'number' => 11, 'pos' => 'F', 'role' => 'striker'],
            ['name' => 'Leon Bailey', 'number' => 31, 'pos' => 'F', 'role' => 'winger'],
            ['name' => 'Morgan Rogers', 'number' => 27, 'pos' => 'M', 'role' => 'winger'],
            ['name' => 'Youri Tielemans', 'number' => 8, 'pos' => 'M', 'role' => 'playmaker'],
            ['name' => 'Amadou Onana', 'number' => 24, 'pos' => 'M', 'role' => 'anchor'],
            ['name' => 'John McGinn', 'number' => 7, 'pos' => 'M', 'role' => 'midfielder'],
            ['name' => 'Matty Cash', 'number' => 2, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Ezri Konsa', 'number' => 4, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Pau Torres', 'number' => 14, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Lucas Digne', 'number' => 12, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Emiliano Martínez', 'number' => 23, 'pos' => 'GK', 'role' => 'goalkeeper'],
        ],
        'newcastle united fc' => [
            ['name' => 'Alexander Isak', 'number' => 14, 'pos' => 'F', 'role' => 'striker'],
            ['name' => 'Anthony Gordon', 'number' => 10, 'pos' => 'F', 'role' => 'winger'],
            ['name' => 'Jacob Murphy', 'number' => 23, 'pos' => 'F', 'role' => 'winger'],
            ['name' => 'Bruno Guimarães', 'number' => 39, 'pos' => 'M', 'role' => 'playmaker'],
            ['name' => 'Joelinton', 'number' => 7, 'pos' => 'M', 'role' => 'midfielder'],
            ['name' => 'Sandro Tonali', 'number' => 8, 'pos' => 'M', 'role' => 'anchor'],
            ['name' => 'Tino Livramento', 'number' => 21, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Fabian Schär', 'number' => 5, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Dan Burn', 'number' => 33, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Lewis Hall', 'number' => 20, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Nick Pope', 'number' => 22, 'pos' => 'GK', 'role' => 'goalkeeper'],
        ],

        // LA LIGA
        'real madrid cf' => [
            ['name' => 'Vinícius Júnior', 'number' => 7, 'pos' => 'F', 'role' => 'winger'],
            ['name' => 'Kylian Mbappé', 'number' => 9, 'pos' => 'F', 'role' => 'striker'],
            ['name' => 'Rodrygo', 'number' => 11, 'pos' => 'F', 'role' => 'winger'],
            ['name' => 'Jude Bellingham', 'number' => 5, 'pos' => 'M', 'role' => 'playmaker'],
            ['name' => 'Federico Valverde', 'number' => 8, 'pos' => 'M', 'role' => 'midfielder'],
            ['name' => 'Eduardo Camavinga', 'number' => 6, 'pos' => 'M', 'role' => 'anchor'],
            ['name' => 'Dani Carvajal', 'number' => 2, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Éder Militão', 'number' => 3, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Antonio Rüdiger', 'number' => 22, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Ferland Mendy', 'number' => 23, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Thibaut Courtois', 'number' => 1, 'pos' => 'GK', 'role' => 'goalkeeper'],
        ],
        'fc barcelona' => [
            ['name' => 'Robert Lewandowski', 'number' => 9, 'pos' => 'F', 'role' => 'striker'],
            ['name' => 'Lamine Yamal', 'number' => 19, 'pos' => 'F', 'role' => 'winger'],
            ['name' => 'Raphinha', 'number' => 11, 'pos' => 'F', 'role' => 'winger'],
            ['name' => 'Pedri', 'number' => 8, 'pos' => 'M', 'role' => 'playmaker'],
            ['name' => 'Dani Olmo', 'number' => 20, 'pos' => 'M', 'role' => 'midfielder'],
            ['name' => 'Marc Casadó', 'number' => 17, 'pos' => 'M', 'role' => 'anchor'],
            ['name' => 'Jules Koundé', 'number' => 23, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Pau Cubarsí', 'number' => 2, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Iñigo Martínez', 'number' => 5, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Alejandro Balde', 'number' => 3, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Wojciech Szczęsny', 'number' => 25, 'pos' => 'GK', 'role' => 'goalkeeper'],
        ],
        'club atlético de madrid' => [
            ['name' => 'Antoine Griezmann', 'number' => 7, 'pos' => 'F', 'role' => 'playmaker'],
            ['name' => 'Julián Alvarez', 'number' => 19, 'pos' => 'F', 'role' => 'striker'],
            ['name' => 'Alexander Sørloth', 'number' => 9, 'pos' => 'F', 'role' => 'striker'],
            ['name' => 'Rodrigo De Paul', 'number' => 5, 'pos' => 'M', 'role' => 'midfielder'],
            ['name' => 'Koke', 'number' => 6, 'pos' => 'M', 'role' => 'anchor'],
            ['name' => 'Conor Gallagher', 'number' => 4, 'pos' => 'M', 'role' => 'midfielder'],
            ['name' => 'Marcos Llorente', 'number' => 14, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Robin Le Normand', 'number' => 24, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'José María Giménez', 'number' => 2, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Reinildo Mandava', 'number' => 23, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Jan Oblak', 'number' => 13, 'pos' => 'GK', 'role' => 'goalkeeper'],
        ],

        // BUNDESLIGA
        'fc bayern münchen' => [
            ['name' => 'Harry Kane', 'number' => 9, 'pos' => 'F', 'role' => 'striker'],
            ['name' => 'Jamal Musiala', 'number' => 42, 'pos' => 'M', 'role' => 'playmaker'],
            ['name' => 'Michael Olise', 'number' => 17, 'pos' => 'F', 'role' => 'winger'],
            ['name' => 'Serge Gnabry', 'number' => 7, 'pos' => 'F', 'role' => 'winger'],
            ['name' => 'Joshua Kimmich', 'number' => 6, 'pos' => 'M', 'role' => 'anchor'],
            ['name' => 'Aleksandar Pavlović', 'number' => 45, 'pos' => 'M', 'role' => 'midfielder'],
            ['name' => 'Raphaël Guerreiro', 'number' => 22, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Dayot Upamecano', 'number' => 2, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Min-jae Kim', 'number' => 3, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Alphonso Davies', 'number' => 19, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Manuel Neuer', 'number' => 1, 'pos' => 'GK', 'role' => 'goalkeeper'],
        ],
        'bayer 04 leverkusen' => [
            ['name' => 'Victor Boniface', 'number' => 22, 'pos' => 'F', 'role' => 'striker'],
            ['name' => 'Florian Wirtz', 'number' => 10, 'pos' => 'M', 'role' => 'playmaker'],
            ['name' => 'Martin Terrier', 'number' => 11, 'pos' => 'F', 'role' => 'winger'],
            ['name' => 'Jeremie Frimpong', 'number' => 30, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Granit Xhaka', 'number' => 34, 'pos' => 'M', 'role' => 'anchor'],
            ['name' => 'Robert Andrich', 'number' => 8, 'pos' => 'M', 'role' => 'midfielder'],
            ['name' => 'Alejandro Grimaldo', 'number' => 20, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Jonathan Tah', 'number' => 4, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Edmond Tapsoba', 'number' => 12, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Piero Hincapié', 'number' => 3, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Lukáš Hrádecký', 'number' => 1, 'pos' => 'GK', 'role' => 'goalkeeper'],
        ],
        'borussia dortmund' => [
            ['name' => 'Serhou Guirassy', 'number' => 9, 'pos' => 'F', 'role' => 'striker'],
            ['name' => 'Jamie Gittens', 'number' => 43, 'pos' => 'F', 'role' => 'winger'],
            ['name' => 'Donyell Malen', 'number' => 21, 'pos' => 'F', 'role' => 'winger'],
            ['name' => 'Julian Brandt', 'number' => 10, 'pos' => 'M', 'role' => 'playmaker'],
            ['name' => 'Marcel Sabitzer', 'number' => 20, 'pos' => 'M', 'role' => 'midfielder'],
            ['name' => 'Pascal Groß', 'number' => 13, 'pos' => 'M', 'role' => 'anchor'],
            ['name' => 'Julian Ryerson', 'number' => 26, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Waldemar Anton', 'number' => 3, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Nico Schlotterbeck', 'number' => 4, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Ramy Bensebaini', 'number' => 5, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Gregor Kobel', 'number' => 1, 'pos' => 'GK', 'role' => 'goalkeeper'],
        ],

        // SERIE A
        'fc internazionale milano' => [
            ['name' => 'Lautaro Martínez', 'number' => 10, 'pos' => 'F', 'role' => 'striker'],
            ['name' => 'Marcus Thuram', 'number' => 9, 'pos' => 'F', 'role' => 'striker'],
            ['name' => 'Nicolò Barella', 'number' => 23, 'pos' => 'M', 'role' => 'midfielder'],
            ['name' => 'Hakan Çalhanoğlu', 'number' => 20, 'pos' => 'M', 'role' => 'playmaker'],
            ['name' => 'Henrikh Mkhitaryan', 'number' => 22, 'pos' => 'M', 'role' => 'midfielder'],
            ['name' => 'Federico Dimarco', 'number' => 32, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Denzel Dumfries', 'number' => 2, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Alessandro Bastoni', 'number' => 95, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Francesco Acerbi', 'number' => 15, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Benjamin Pavard', 'number' => 28, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Yann Sommer', 'number' => 1, 'pos' => 'GK', 'role' => 'goalkeeper'],
        ],
        'juventus fc' => [
            ['name' => 'Dušan Vlahović', 'number' => 9, 'pos' => 'F', 'role' => 'striker'],
            ['name' => 'Kenan Yıldız', 'number' => 10, 'pos' => 'F', 'role' => 'winger'],
            ['name' => 'Francisco Conceição', 'number' => 7, 'pos' => 'F', 'role' => 'winger'],
            ['name' => 'Teun Koopmeiners', 'number' => 8, 'pos' => 'M', 'role' => 'playmaker'],
            ['name' => 'Manuel Locatelli', 'number' => 5, 'pos' => 'M', 'role' => 'anchor'],
            ['name' => 'Khéphren Thuram', 'number' => 19, 'pos' => 'M', 'role' => 'midfielder'],
            ['name' => 'Andrea Cambiaso', 'number' => 27, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Federico Gatti', 'number' => 4, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Pierre Kalulu', 'number' => 15, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Juan Cabal', 'number' => 32, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Michele Di Gregorio', 'number' => 29, 'pos' => 'GK', 'role' => 'goalkeeper'],
        ],

        // LIGUE 1
        'paris saint-germain fc' => [
            ['name' => 'Bradley Barcola', 'number' => 29, 'pos' => 'F', 'role' => 'winger'],
            ['name' => 'Ousmane Dembélé', 'number' => 10, 'pos' => 'F', 'role' => 'winger'],
            ['name' => 'Marco Asensio', 'number' => 11, 'pos' => 'F', 'role' => 'striker'],
            ['name' => 'Vitinha', 'number' => 17, 'pos' => 'M', 'role' => 'playmaker'],
            ['name' => 'Warren Zaïre-Emery', 'number' => 33, 'pos' => 'M', 'role' => 'midfielder'],
            ['name' => 'João Neves', 'number' => 87, 'pos' => 'M', 'role' => 'midfielder'],
            ['name' => 'Achraf Hakimi', 'number' => 2, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Marquinhos', 'number' => 5, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Willian Pacho', 'number' => 51, 'pos' => 'D', 'role' => 'centerback'],
            ['name' => 'Nuno Mendes', 'number' => 25, 'pos' => 'D', 'role' => 'fullback'],
            ['name' => 'Gianluigi Donnarumma', 'number' => 1, 'pos' => 'GK', 'role' => 'goalkeeper'],
        ],
    ];

    /**
     * Get confirmed starting XI players for a team.
     * If club is not in the explicit map, generates authentic named footballers according to team country.
     */
    public static function getStartingXi(Team $team): array
    {
        $normalizedName = strtolower(trim($team->name));

        // Exact match
        if (isset(self::$clubRosters[$normalizedName])) {
            return self::$clubRosters[$normalizedName];
        }

        // Substring match e.g. "Arsenal", "Barcelona", "Bayern"
        foreach (self::$clubRosters as $key => $roster) {
            $shortKey = str_replace([' fc', ' c.f.', ' cf', ' afc', ' sc', ' rcd', ' 04', ' münchen'], '', $key);
            if (str_contains($normalizedName, trim($shortKey)) || str_contains(strtolower($team->short_name ?? ''), trim($shortKey))) {
                return $roster;
            }
        }

        // Regional authentic player name pool generator
        return self::generateRegionalSquad($team);
    }

    /**
     * Generate an authentic regional squad for clubs without explicit roster.
     */
    protected static function generateRegionalSquad(Team $team): array
    {
        $country = strtolower($team->country ?? 'england');
        $teamShort = $team->short_name ?? $team->name;

        // Distinctive authentic player names by football region
        $namePools = [
            'england' => [
                'GK' => [['name' => 'Jordan Henderson (GK)', 'num' => 1]],
                'D' => [
                    ['name' => 'James Tarkowski', 'num' => 5],
                    ['name' => 'Michael Keane', 'num' => 6],
                    ['name' => 'Max Aarons', 'num' => 2],
                    ['name' => 'Antonee Robinson', 'num' => 3],
                ],
                'M' => [
                    ['name' => 'Harrison Reed', 'num' => 8],
                    ['name' => 'Sander Berge', 'num' => 16],
                    ['name' => 'Dwight McNeil', 'num' => 7],
                    ['name' => 'Alex Iwobi', 'num' => 17],
                ],
                'F' => [
                    ['name' => 'Dominic Calvert-Lewin', 'num' => 9],
                    ['name' => 'Iliman Ndiaye', 'num' => 10],
                ],
            ],
            'spain' => [
                'GK' => [['name' => 'Álex Remiro', 'num' => 1]],
                'D' => [
                    ['name' => 'Igor Zubeldia', 'num' => 5],
                    ['name' => 'Robin Le Normand', 'num' => 24],
                    ['name' => 'Hamari Traoré', 'num' => 18],
                    ['name' => 'Aihen Muñoz', 'num' => 3],
                ],
                'M' => [
                    ['name' => 'Martín Zubimendi', 'num' => 4],
                    ['name' => 'Mikel Merino', 'num' => 8],
                    ['name' => 'Brais Méndez', 'num' => 23],
                    ['name' => 'Ander Barrenetxea', 'num' => 7],
                ],
                'F' => [
                    ['name' => 'Mikel Oyarzabal', 'num' => 10],
                    ['name' => 'Takefusa Kubo', 'num' => 14],
                ],
            ],
            'germany' => [
                'GK' => [['name' => 'Kevin Trapp', 'num' => 1]],
                'D' => [
                    ['name' => 'Robin Koch', 'num' => 4],
                    ['name' => 'Tuta', 'num' => 35],
                    ['name' => 'Rasmus Kristensen', 'num' => 13],
                    ['name' => 'Arthur Theate', 'num' => 3],
                ],
                'M' => [
                    ['name' => 'Ellyes Skhiri', 'num' => 15],
                    ['name' => 'Hugo Larsson', 'num' => 16],
                    ['name' => 'Mario Götze', 'num' => 27],
                    ['name' => 'Junior Dina Ebimbe', 'num' => 26],
                ],
                'F' => [
                    ['name' => 'Omar Marmoush', 'num' => 7],
                    ['name' => 'Hugo Ekitiké', 'num' => 11],
                ],
            ],
            'italy' => [
                'GK' => [['name' => 'Marco Carnesecchi', 'num' => 29]],
                'D' => [
                    ['name' => 'Berat Djimsiti', 'num' => 19],
                    ['name' => 'Isak Hien', 'num' => 4],
                    ['name' => 'Sead Kolašinac', 'num' => 23],
                    ['name' => 'Davide Zappacosta', 'num' => 77],
                ],
                'M' => [
                    ['name' => 'Éderson', 'num' => 13],
                    ['name' => 'Marten de Roon', 'num' => 15],
                    ['name' => 'Matteo Ruggeri', 'num' => 22],
                    ['name' => 'Mario Pašalić', 'num' => 8],
                ],
                'F' => [
                    ['name' => 'Ademola Lookman', 'num' => 11],
                    ['name' => 'Mateo Retegui', 'num' => 32],
                ],
            ],
            'france' => [
                'GK' => [['name' => 'Lucas Chevalier', 'num' => 30]],
                'D' => [
                    ['name' => 'Bafodé Diakité', 'num' => 18],
                    ['name' => 'Alexsandro', 'num' => 4],
                    ['name' => 'Thomas Meunier', 'num' => 12],
                    ['name' => 'Gabriel Gudmundsson', 'num' => 5],
                ],
                'M' => [
                    ['name' => 'Benjamin André', 'num' => 21],
                    ['name' => 'Angel Gomes', 'num' => 8],
                    ['name' => 'Edon Zhegrova', 'num' => 23],
                    ['name' => 'Rémy Cabella', 'num' => 10],
                ],
                'F' => [
                    ['name' => 'Jonathan David', 'num' => 9],
                    ['name' => 'Osame Sahraoui', 'num' => 11],
                ],
            ],
        ];

        $pool = $namePools[$country] ?? $namePools['england'];
        $roster = [];

        // 1 GK
        $roster[] = ['name' => $pool['GK'][0]['name'], 'number' => $pool['GK'][0]['num'], 'pos' => 'GK', 'role' => 'goalkeeper'];
        // 4 Def
        foreach ($pool['D'] as $d) {
            $roster[] = ['name' => $d['name'], 'number' => $d['num'], 'pos' => 'D', 'role' => 'defender'];
        }
        // 4 Mid
        foreach ($pool['M'] as $m) {
            $roster[] = ['name' => $m['name'], 'number' => $m['num'], 'pos' => 'M', 'role' => 'midfielder'];
        }
        // 2 Fwd
        foreach ($pool['F'] as $f) {
            $roster[] = ['name' => $f['name'], 'number' => $f['num'], 'pos' => 'F', 'role' => 'striker'];
        }

        return $roster;
    }
}

