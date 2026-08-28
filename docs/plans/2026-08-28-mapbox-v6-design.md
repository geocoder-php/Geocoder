# Mapbox Geocoding v6 — Design

Issue: https://github.com/geocoder-php/Geocoder/issues/1237
Date: 2026-08-28
Status: validated

## Background

Mapbox released the [Geocoding API v6](https://docs.mapbox.com/api/search/geocoding/) (GA). The v6
API is more precise, adds **Structured Input** (typed address fields instead of free text),
**Smart Address Match** (`match_code`), point `accuracy`, `routable_points`, and new feature
types. The v5 API keeps running; Mapbox does not force a migration.

## Decision

**Upgrade the existing `Geocoder\Provider\Mapbox\Mapbox` provider in place, as a major
version of `geocoder-php/mapbox-provider` (1.x → 2.0.0).** All breaking changes are listed
in the provider CHANGELOG. Users who need v5 pin to `^1.5`.

Rationale: the issue asks for *the* Mapbox provider to support v6; the repo's deprecation
procedure (`docs/procedures/provider-deprecation.md`) only applies once an API stops
responding, which has not happened for v5 — but the provider may move forward in a major
release. (A parallel `MapboxV6` class was considered and rejected in favor of a clean
major bump.)

## Verified v6 API behavior (probed against the live API)

- Forward: `GET https://api.mapbox.com/search/geocode/v6/forward?q={text}` (or structured fields, no `q`)
- Reverse: `GET https://api.mapbox.com/search/geocode/v6/reverse?longitude={lon}&latitude={lat}`
- Invalid token → **401** (v5 used 403). Both map to `InvalidCredentials` in `AbstractHttpProvider`.
- `types=poi` / `types=poi.landmark` → **422** (POI geocoding removed in v6).
- `types` valid values (per the API's own 422 message): `country, region, postcode, district,
  place, locality, neighborhood, street, block, address, secondary_address`. `block` is only
  accepted in Japan context (`country=jp`), `secondary_address` in US context; the docs list
  the 9 globally-safe values.
- Forward `limit=11` → 200 (silently clamped to the max of 10).
- Reverse `limit>1` **requires exactly one** `types` value, else 422.
- `autocomplete` (replaces v5 `fuzzyMatch`, inverted default): prefix-style matching; a typo'd
  query (`wahsington`) returns nothing in v6 — there is no typo tolerance; that is what
  `match_code` confidence is for.
- Structured Input: `q` is dropped in favor of `address_line1`, `address_number`, `street`,
  `block`, `place`, `region`, `postcode`, `locality`, `neighborhood` (plus `country`); Mapbox
  recommends `autocomplete=false` for structured input.
- Response: GeoJSON. `properties` contains `mapbox_id`, `feature_type` (string, formerly
  `place_type` array), `name`, `full_address`, `coordinates{longitude,latitude,accuracy,
  routable_points}`, `match_code{... ,confidence}` (forward only) and `context` as an
  **object keyed by feature type** (v5 was an array of `{id,text,short_code}`).
  `context.country` uses `country_code`, `context.region` uses `region_code`/`region_code_full`,
  `context.address` uses `street_name`/`address_number`.
- `bbox` is documented on non-address features but is **not currently returned** in any of the
  probed responses; the parser maps it defensively when present.

## Public API surface (2.0.0)

### Constructor (breaking)

```php
new Mapbox(ClientInterface $client, string $accessToken, ?string $country = null, bool $permanent = false)
```

The 4th argument changes from `string $geocodingMode` to `bool $permanent` (v6 `permanent=true`
storage, applied to forward and reverse requests).

### Constants

- `FORWARD_ENDPOINT_URL`, `REVERSE_ENDPOINT_URL` (replace `GEOCODE_ENDPOINT_URL_SSL` /
  `REVERSE_ENDPOINT_URL_SSL`).
- Types: `country, region, postcode, district, place, locality, neighborhood, street, address`
  in `TYPES` (adds `TYPE_STREET`; removes `TYPE_POI` / `TYPE_POI_LANDMARK`).
- `TYPE_BLOCK` (Japan) and `TYPE_SECONDARY_ADDRESS` (US) exposed as constants but **not** in
  `TYPES` (conditionally accepted by the API only).
- `STRUCTURED_INPUT_FIELDS`: the data keys that trigger Structured Input.
- Removed: `GEOCODING_MODE_PLACES`, `GEOCODING_MODE_PLACES_PERMANENT`, `GEOCODING_MODES`.

### Query data keys

| Key | v6 param | Note |
|---|---|---|
| `location_type` (string\|array) | `types` | kept from v5; default `address` |
| `autocomplete` (bool) | `autocomplete` | replaces `fuzzy_match` (removed) |
| `country` (string) | `country` | kept; also a structured field |
| `proximity` (`"lon,lat"`\|`"ip"`) | `proximity` | new |
| `worldview` (string) | `worldview` | new |
| `address_line1`, `address_number`, `street`, `block`, `place`, `region`, `postcode`, `locality`, `neighborhood` | same | structured input |
| `bounds` → `bbox`, `locale` → `language`, `limit` → `limit` | | kept |

**Mode selection:** if any structured field is present, `q` is not sent and `autocomplete`
defaults to `false` (unless the user sets it). Otherwise `q` = query text. (The text must
still be non-empty because `GeocodeQuery::create('')` throws; it is simply not sent in
structured mode.)

### Parameter order (deterministic URLs)

- Forward: `q` | structured fields (canonical order) → `bbox` → `types` → `autocomplete` →
  `proximity` → `worldview` → `country` → `language` → `limit` → `permanent` (if true) →
  `access_token`
- Reverse: `longitude` → `latitude` → `types` → `country` → `language` → `limit` →
  `permanent` (if true) → `access_token`

### Response → model mapping

| Model | v6 source |
|---|---|
| `id` | `properties.mapbox_id` (fallback: feature `id`) |
| `streetName` | `context.address.street_name` → `context.street.name` → `properties.name` (keeps v5 semantics: no house number) |
| `streetNumber` | `context.address.address_number` |
| `formattedAddress` | `properties.full_address` |
| `neighborhood` | `context.neighborhood.name` |
| `resultType` | `[properties.feature_type]` (array kept for BC) |
| `postalCode` / `locality` / `country` (+code) / admin levels | `context.postcode.name`, `context.locality.name` + `place.name` (→ locality + level 1), `context.region.name` + `region_code` (→ level 2), `context.country.name` + `country_code` — same precedence rules as v5 (later context entry wins for locality) |
| `bounds` | feature `bbox` (when present) |
| **new** `matchCode` / `matchConfidence` | `properties.match_code` / `.confidence` |
| **new** `accuracy` | `properties.coordinates.accuracy` |

Errors/behavior: IP text → `UnsupportedOperation` (unchanged); 401/403 → `InvalidCredentials`,
429 → `QuotaExceeded`, other ≥300 → `InvalidServerResponse` (base class, unchanged);
`getName()` stays `mapbox`; client-side result limit kept.

## Testing strategy (TDD)

1. **Mocked unit tests** — no network:
   - basics: name, IP rejection ×3, 429, 401, empty reverse response;
   - exact-URL assertions via `getMockedHttpClientCallback` (forward basic / all options /
     structured / explicit autocomplete override / reverse / reverse with types+country);
   - response-parsing tests with inline JSON fixtures (address feature, place feature, street
     feature, feature without context, empty features, invalid JSON).
2. **Integration tests with cached responses** (repo convention): 7 real v6 queries recorded
   with a real key (see implementation plan for the exact URLs); `CachedResponseClient`
   masks the key as `[apikey]` so CI replays the fixtures with the placeholder key.
   v5 fixtures are deleted.
3. `composer test` (full suite), `composer analyse` (PHPStan L6), `composer cs` — per
   `CONTRIBUTING.md`.

## Out of scope (YAGNI)

- Batch geocoding endpoint (`/v6/batch`)
- `entrances` (public preview)
- `routable_points` on the model
- `format=v5` compatibility mode (we parse native v6)
- Deprecation of the v5 API (it still works; no maintainer decision to deprecate)
