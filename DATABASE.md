# DATABASE.md

SQLite for v1 (swappable to MySQL). All timestamps in UTC. Use foreign keys,
unique constraints, and indexes as listed. Migrations are append-only.

Design goals: (1) nothing external is trusted without a stored raw copy, (2) any
row is traceable to its source call, (3) predictions cannot be rewritten after
the fact, (4) backtests can never see the future.

## competitions
| column | type | notes |
|---|---|---|
| id | pk | |
| slug | string, unique | internal slug used as the artisan argument, e.g. `premier-league` |
| name | string | |
| country | string | |
| source_ids | json | per-source competition codes e.g. {"football_data": "PL", "api_football": 39, "csv": "E0"} (verify in Phase 0) |
| is_active | bool | controls which competitions the scheduler syncs |
| timestamps | | |

## teams
| column | type | notes |
|---|---|---|
| id | pk | |
| name | string | canonical name |
| short_name | string null | |
| country | string null | |
| timestamps | | |

## team_aliases  (the name-mapping fix)
| column | type | notes |
|---|---|---|
| id | pk | |
| team_id | fk teams, cascade | |
| source | string | football_data, api_football, understat, csv, elo |
| alias | string | exact string used by that source |
| unique | (source, alias) | |

Only `TeamResolver` reads this table. Never match team names elsewhere.

## matches
| column | type | notes |
|---|---|---|
| id | pk | |
| competition_id | fk | |
| season | smallint | start year, e.g. 2025 for 2025/26 |
| matchday | smallint null | |
| kickoff_at | datetime (UTC) | index |
| home_team_id | fk teams | |
| away_team_id | fk teams | |
| status | string | scheduled, live, finished, postponed, cancelled |
| home_goals | smallint null | full-time |
| away_goals | smallint null | full-time |
| ht_home_goals / ht_away_goals | smallint null | optional |
| football_data_id | string null, unique | |
| api_football_id | string null, unique | |
| understat_id | string null, unique | |
| source_api_call_id | fk api_calls null | provenance of last update |
| timestamps | | |

Indexes: (competition_id, season, kickoff_at), (competition_id, season, status),
(home_team_id, kickoff_at), (away_team_id, kickoff_at), (status, kickoff_at).
Constraint: home_team_id != away_team_id.

## standings_snapshots
Standings as they were AFTER a given matchday (needed to avoid leakage; never use
only "current" standings for backtests). Rebuilt from `matches` by
`standings:rebuild`, so it costs no API calls.
| column | type | notes |
|---|---|---|
| id | pk | |
| competition_id, season | | |
| matchday | smallint | |
| team_id | fk | |
| position, played, won, drawn, lost | smallint | |
| goals_for, goals_against, points | smallint | |
| as_of | datetime (UTC) | date the table is valid for |
| source_api_call_id | fk null | null when computed locally |
| unique | (competition_id, season, matchday, team_id) | |

## team_ratings
| column | type | notes |
|---|---|---|
| id | pk | |
| team_id | fk | |
| rating_type | string | e.g. elo |
| rating | decimal | |
| as_of | date | |
| source_api_call_id | fk null | |
| unique | (team_id, rating_type, as_of) | |

## match_stats  (Phase 6: xG etc. - IMPLEMENTED & VERIFIED)
Migration: `2026_10_28_000012_create_match_stats_table.php`. Stores team-level match statistics (xG created, xG conceded, shots) per match and data source. Matches are unique on `(match_id, team_id, source)`.
| column | type | notes |
|---|---|---|
| id | pk | |
| match_id | fk | |
| team_id | fk | |
| xg | decimal null | |
| xga | decimal null | |
| shots / shots_on_target | smallint null | |
| source | string | |
| source_api_call_id | fk null | |
| unique | (match_id, team_id, source) | |

## api_calls  (REQUIRED: every external call, full copy)
Single table for cache, quota counting, audit, and the inspector.
Append-only: never UPDATE or DELETE.
| column | type | notes |
|---|---|---|
| id | pk | |
| source | string | football_data, api_football, understat, elo, csv, anthropic |
| method | string | GET/POST |
| url | text | without secrets |
| request_params | json null | |
| request_body | longtext null | for LLM calls this is the full prompt payload |
| request_headers | json null | REDACTED (no keys/tokens ever) |
| response_status | smallint null | |
| response_body | longtext null | full raw body, unmodified (NULL on cache hits) |
| response_headers | json null | useful for rate-limit headers |
| duration_ms | integer null | |
| error | text null | includes provider-reported errors found in the body |
| input_tokens / output_tokens | integer null | LLM calls |
| counts_toward_quota | bool | used by QuotaGuard (false on cache hits) |
| served_from_cache | bool | true when answered from an earlier call, no HTTP sent |
| cached_from_call_id | fk api_calls null | the original call whose response was reused |
| cache_key | string | sha256(source + '|' + method + '|' + lowercase_url + '|' + ksort_json(params)), index |
| triggered_by | string | command or job name |
| sync_run_id | fk null | |
| called_at | datetime (UTC) | index |

Indexes: (source, called_at), (cache_key, called_at).
Quota used today = count of rows for the source where `counts_toward_quota` is
true and `called_at` is today (UTC; synchronized with provider rate-limit response
headers via QuotaGuard to prevent timezone reset drift).

## sync_runs
| column | type | notes |
|---|---|---|
| id | pk | |
| command | string | |
| arguments | json null | |
| started_at / finished_at | datetime | |
| status | string | running, success, partial, failed |
| summary | json null | counts, skipped rows, unknown team names, errors |

## predictions  (IMMUTABLE)
| column | type | notes |
|---|---|---|
| id | pk | |
| match_id | fk | |
| model_version | string | bump on ANY model/feature/param change |
| stage | string | `pre_match`, `matchday`, `final` (Phase 7 migration) |
| is_backtest | bool | true when produced by `backtest` |
| as_of | datetime (UTC) | cutoff used for features (must be <= kickoff) |
| p_home / p_draw / p_away | decimal | must sum to about 1 |
| exp_home_goals / exp_away_goals | decimal | |
| top_scorelines | json | |
| features | json | full feature snapshot used (reproducibility) |
| low_confidence | bool | e.g. promoted team, little history |
| created_at | datetime | |
| unique | (match_id, model_version, is_backtest, stage) | |

Application rule: no UPDATE/DELETE. Enforce with a model guard (and a DB trigger
if practical). A match can have an early `pre_match` prediction, a `matchday` prediction (incorporating injuries), and a `final` prediction (with confirmed lineups).

## injuries  (Phase 7: Lineups and injuries)
Migration: `2026_10_28_000014_create_injuries_table.php`.
| column | type | notes |
|---|---|---|
| id | pk | |
| team_id | fk teams | |
| fixture_api_id | string null | |
| player_name | string | |
| player_type | string null | Missing Fixture, Questionable, Suspended |
| reason | string null | injury or suspension description |
| source | string | api_football |
| source_api_call_id | fk api_calls null | |
| as_of | datetime (UTC) | |
Index: `(team_id, as_of)`.

## match_lineups  (Phase 7: Lineups and injuries)
Migration: `2026_10_28_000015_create_match_lineups_table.php`.
| column | type | notes |
|---|---|---|
| id | pk | |
| match_id | fk matches | |
| team_id | fk teams | |
| formation | string null | e.g. '4-3-3', '4-2-3-1' |
| starting_xi | json | 11 starting players (name, number, position) |
| substitutes | json | bench players |
| coach_name | string null | |
| confirmed | bool | true when confirmed ~1h prior to kickoff |
| source | string | api_football |
| source_api_call_id | fk api_calls null | |
Unique: `(match_id, team_id, source)`.

## match_odds  (Phase 8: Benchmarks and extras)
Migration: `2026_10_28_000016_create_match_odds_table.php`. Stores bookmaker odds (Bet365, Market Average, Pinnacle) and normalized implied probabilities (removing margin/overround) as an evaluation benchmark.
| column | type | notes |
|---|---|---|
| id | pk | |
| match_id | fk matches | |
| bookmaker | string | `b365`, `market_avg`, `pinnacle` |
| home_odds / draw_odds / away_odds | decimal | decimal odds (e.g. 1.60, 4.20, 5.25) |
| implied_home_prob / implied_draw_prob / implied_away_prob | decimal | overround-removed probabilities (sum = 1.0) |
| overround | decimal null | sum of raw inverted odds (e.g. 1.0542) |
| source | string | `csv`, `api_football` |
| source_api_call_id | fk api_calls null | |
Unique: `(match_id, bookmaker, source)`.
Index: `(match_id, bookmaker)`.

## prediction_evaluations
| column | type | notes |
|---|---|---|
| id | pk | |
| prediction_id | fk, unique | |
| outcome | string | H, D, A |
| predicted_outcome | string | argmax of probabilities |
| correct | bool | |
| log_loss | decimal | |
| brier | decimal | |
| evaluated_at | datetime | |

## previews
| column | type | notes |
|---|---|---|
| id | pk | |
| prediction_id | fk | |
| api_call_id | fk api_calls | full prompt + response live there |
| model | string | LLM model string used |
| prompt_version | string | |
| text | longtext | final preview shown to the user |
| flagged | bool | number-check guard result |
| created_at | datetime | |

Multiple previews per prediction are allowed (regeneration). Keep all.

## system_audits
Stores automated database timeline verification, zero-leakage, and cleanliness audit runs.
| column | type | notes |
|---|---|---|
| id | pk | |
| triggered_by | string | `cli`, `web`, `schedule` |
| status | string | `clean`, `warning`, `critical` |
| total_checks | integer | total integrity domains audited |
| checks_passed | integer | passed checks count |
| checks_failed | integer | failed checks count |
| checks_warning | integer | warning checks count |
| duration_ms | decimal(8,2) | execution runtime in ms |
| summary | text | human readable audit summary |
| report_json | json | full structured audit payload |
| created_at / updated_at | datetime | index on `created_at` |

## motm_evaluations
Stores objective starting XI player performance ratings, match contributions, and conferred Man of the Match awards.
| column | type | notes |
|---|---|---|
| id | pk | |
| match_id | fk matches, cascade | match evaluated |
| player_name | string | player full name |
| player_number | integer null | jersey shirt number |
| team_id | fk teams, cascade | player club team |
| position | string null | tactical role: `GK`, `D`, `M`, `F` |
| is_starter | bool | true if starting XI player |
| is_motm | bool | true if conferred Man of the Match |
| rating | decimal(3,1) | match rating clamped 5.0 to 10.0 |
| data_rating | decimal(3,1) null | pure Opta/SofaScore statistical match action rating (0-10) |
| decision_score | decimal(3,1) null | panel award decision score with winning team & moment biases (0-10) |
| decisive_actions | json null | structured array of decisive actions (game-winners, clean sheets) |
| decision_rationale | string null | concise narrative explaining the panel decision |
| goals | integer | goals scored in match |
| assists | integer | assists provided in match |
| xg | decimal(4,2) null | individual or team xG context |
| performance_summary | text | objective performance explanation |
| model | string | default `algorithmic-v1` |
| source_api_call_id | fk api_calls null | provenance from lineups call |
| timestamps | datetime | index on `(match_id, is_motm)` |

## Relationships (summary)

competitions 1-n matches; teams 1-n team_aliases; teams 1-n matches (home/away);
matches 1-n predictions; predictions 1-1 prediction_evaluations; predictions 1-n
previews; matches 1-n motm_evaluations; api_calls 1-n (matches, standings_snapshots,
team_ratings, match_stats, previews, motm_evaluations) via `source_api_call_id` / `api_call_id`.

## Seeding

- `competitions`: top 5 leagues (`is_active` only for `premier-league` in v1).
- `teams` + `team_aliases`: seeded from the first real responses, reviewed by
  hand. Unknown names are reported, not auto-created.

## Storage notes

- `api_calls` will be the largest table. Fine for SQLite at this scale. If it
  grows too large, compress `response_body` (gzip) in a later migration; do not
  delete rows.
- Back up the SQLite file with `app:backup`. It contains all raw source data.
