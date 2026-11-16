# XPECT FOOTBALL ANALYTICS — ROUTE INVENTORY

Comprehensive inventory of all registered HTTP routes, controllers, middleware, and methods.

| Route Name | URI Pattern | Method | Action / Handler | Middleware | Description |
|---|---|---|---|---|---|
| `dashboard` | `/` | `GET` | `DashboardController@index` | `web` | Main Dashboard: Multi-league fixtures, marquee matchday spotlight, Poisson predictions, SportyBet odds. |
| `matches.sync-live` | `/matches/sync-live` | `POST` | `DashboardController@syncLiveScores` | `web` | Synchronize live in-play and finished scores from API. |
| `matches.predict` | `/matches/{match}/predict` | `POST` | `DashboardController@predict` | `web` | Compute or retrieve Poisson prediction for a match. |
| `predictions.preview` | `/predictions/{prediction}/preview` | `POST` | `DashboardController@generatePreview` | `web` | Trigger AI preview generation with verified DB data. |
| `today` | `/today` | `GET` | `TodayFixturesController@index` | `web` | Today's Fixtures Hub: Grouped matchday feed across all leagues. |
| `today.predict-all` | `/today/predict-all` | `POST` | `TodayFixturesController@predictAll` | `web` | Run batch Poisson predictions for all matches kicking off today. |
| `track-record` | `/track-record` | `GET` | `TrackRecordController@index` | `web` | Calibration curve, Log Loss, Brier score, baseline benchmarks. |
| `ai-reasoning` | `/ai-reasoning` | `GET` | `AiReasoningController@index` | `web` | AI Reasoning Studio & Scenario Permutation Matrix. |
| `ai-reasoning.simulate` | `/ai-reasoning/simulate` | `POST` | `AiReasoningController@simulate` | `web` | Run interactive match scenario simulation. |
| `ai-reasoning.check-guard`| `/ai-reasoning/check-guard` | `POST` | `AiReasoningController@checkGuard` | `web` | Anti-hallucination verification endpoint. |
| `motm` | `/motm` | `GET` | `ManOfTheMatchController@index` | `web` | Man of the Match awards & individual player ratings hub. |
| `motm.evaluate` | `/matches/{match}/motm` | `POST` | `ManOfTheMatchController@evaluate` | `web` | Single-match MOTM evaluation trigger. |
| `motm.batch` | `/motm/evaluate-batch` | `POST` | `ManOfTheMatchController@batchEvaluate` | `web` | Batch evaluation across all unevaluated finished fixtures. |
| `system-health` | `/system-health` | `GET` | `SystemHealthController@index` | `web` | Data integrity audit, coverage timeline, and API call logs. |
| `system-health.run` | `/system-health/run` | `POST` | `SystemHealthController@runAudit` | `web` | Trigger live comprehensive system audit. |
| `settings` | `/settings` | `GET` | `SettingsController@index` | `web` | API credentials, model configuration, cache & maintenance. |
| `settings.keys` | `/settings/keys` | `POST` | `SettingsController@updateKeys` | `web` | Update provider API keys in `.env`. |
| `settings.test-key` | `/settings/test-key` | `POST` | `SettingsController@testKey` | `web` | Live connectivity probe for API keys. |
| `settings.action` | `/settings/action` | `POST` | `SettingsController@runAction` | `web` | Trigger system artisan commands (sync, backfill, backup). |
| `themes` | `/themes` | `GET` | `themes.index` (View) | `web` | Manage and customize local UI page palettes. |
| `changelog` | `/changelog` | `GET` | `ChangelogController@index` | `web` | Model version history, parameters, and walk-forward logs. |
| `docs` | `/docs` | `GET` | `DocsController@index` | `web` | Technical architecture manual, formulas, and user guide. |
| `dev.api.index` | `/dev/api` | `GET` | `ApiInspectorController@index` | `web` | Dev API Inspector: Live call logs and quota usage dashboard. |
| `dev.api.calls` | `/dev/api/calls` | `GET` | `ApiInspectorController@calls` | `web` | JSON endpoint returning logged API calls. |
| `dev.api.call` | `/dev/api/calls/{id}` | `GET` | `ApiInspectorController@call` | `web` | JSON endpoint returning raw request/response body for call. |
| `dev.api.quota` | `/dev/api/quota` | `GET` | `ApiInspectorController@quota` | `web` | JSON endpoint returning real-time quota telemetry. |
| `dev.api.catalog` | `/dev/api/catalog` | `GET` | `ApiInspectorController@catalog` | `web` | JSON endpoint returning active API catalog definition. |
| `dev.api.try` | `/dev/api/try` | `POST` | `ApiInspectorController@tryIt` | `web` | Probe live external API endpoint with key masking. |
| `dev.api.fixture` | `/dev/api/fixture` | `POST` | `ApiInspectorController@saveFixture` | `web` | Save real response as test fixture for offline replay. |
