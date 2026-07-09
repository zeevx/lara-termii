# Changelog

All notable changes to `lara-termii` will be documented in this file.

## 1.2.0 - 2026-07-09

Audited every wrapped endpoint against the current Termii docs
(https://developers.termii.com/). Paths, verbs, payload keys and API key
placement were confirmed correct; the changes below cover the differences
that were found.

### Added
- eSIM (Sotel) support via a new `$termii->esim()` sub-client
  (`LaraTermiiEsim`). It shares the configured API key and base URL, handles
  the eSIM API's bearer-token authentication automatically, and wraps all nine
  endpoints: `dataPlans()`, `createEsim()`, `purchasePlan()`, `qrCode()`,
  `profile()`, `usage()`, `planStatus()`, `esims()` and `countries()`.
- `allSenderId()` now accepts optional `name` and `status` filters.
- `history()` now accepts an optional `message_id` to fetch a single report.
- `sendCampaign()` now exposes `campaign_type` and `schedule_sms_status`
  (both documented as required by Termii) as explicit parameters, defaulting
  to `regular`.

### Changed
- **BREAKING (minor):** `sendCampaign()`'s `$options` array moved from the
  7th to the 9th positional parameter. Calls passing `$options` positionally
  must switch to named arguments or add the two new arguments; values passed
  via `$options` still override the new defaults.
- The constructor fallback, README and tests now consistently use the
  `https://v4.api.termii.com` default base URL that the config file already
  used.
- Internal: `LaraTermii` and `LaraTermiiEsim` were split into per-area traits
  under `Zeevx\LaraTermii\Concerns\Engage` (account, sender IDs, messaging,
  OTP, phonebooks, contacts, campaigns) and `Zeevx\LaraTermii\Concerns\Sotel`
  (auth, eSIMs, plans). The public API is unchanged.

### Fixed
- `addContactsFromContents()` (and therefore `addContactsFromFile()`) now
  sends the bulk contact CSV upload in the format the current docs specify: a
  `file` part plus a single JSON `contact` part containing `pid`,
  `country_code` and `api_key`, instead of flat form fields.

## 1.1.0 - 2026-07-06

Adds wrappers for the remaining documented Termii endpoints. All changes are
additive and backward compatible.

### Added
- **Messaging:** `sendBulkMessage()` (up to 100 recipients).
- **Token:** `sendEmailOTP()` (note: email OTPs cannot be verified via
  `verifyOTP()`).
- **Templates:** `sendTemplate()` and `sendTemplateWithMedia()` for WhatsApp
  device templates.
- **Phonebooks:** `phonebooks()`, `createPhonebook()`, `updatePhonebook()`,
  `deletePhonebook()`.
- **Contacts:** `contacts()`, `addContact()`, `addContactsFromFile()` (CSV
  upload from the local filesystem or any Laravel Storage disk via the `$disk`
  argument), `addContactsFromContents()` (upload raw CSV contents, e.g. an
  uploaded file), `deleteContact()`.
- **Campaigns:** `sendCampaign()`, `campaigns()`, `campaignHistory()`,
  `retryCampaign()`.
- Internal `patch()` / `delete()` request helpers.

### Notes
- Termii's docs do not specify how a contact is identified when deleting, so
  `deleteContact()` sends the contact id in the request body against the
  documented `phonebooks/{id}/contacts` path.

## 1.0.0 - 2026-07-06

First stable release. A full modernization of the package (previous releases
were 0.1.x). The changes below are relative to the 0.1.x line.

### Added
- Publishable `config/termii.php` with `api_key`, `base_url`, `sender_id`,
  `channel`, `timeout` and `throw` options.
- Container binding and facade now work out of the box, driven by config.
- `new LaraTermii()` resolves credentials from config; the constructor accepts
  optional overrides for API key, base URL, sender ID, channel, timeout and
  throw behaviour.
- Optional `throw`-on-failure mode.
- Pest test suite using `Http::fake()`.
- Laravel Pint for code style (`composer lint` / `composer format`) with a
  dedicated CI job. Both are dev-only tools and do not change the package's
  runtime requirements.
- `TermiiException` for clearer configuration errors.

### Changed
- **BREAKING:** every method now returns `Illuminate\Http\Client\Response`
  instead of a raw JSON string. Use `->json()` / `->body()`.
- **BREAKING:** requires PHP 8.1+ and Laravel 9 through 13.
- Phone numbers are now typed as `string` instead of `int` so international and
  `+`-prefixed numbers are preserved.
- `from` on `sendMessage()` and `sendOTP()` now accepts `null` to fall back to
  the configured Sender ID.
- **BREAKING:** `sendMessage()` dropped the old (broken) `bool $media` flag. The
  first four positional arguments (`to`, `from`, `sms`, `channel`) are unchanged,
  but calls that passed the media flag/URL positionally must drop the boolean,
  e.g. `sendMessage($to, $from, $sms, 'whatsapp', $url, $caption)`. A media
  request now sends `media` without the `sms` field, per Termii's docs.
- `sendOTP()` now sends `pin_type` (required by the send-token endpoint),
  defaulting to the given `message_type`; an optional `$pinType` argument was
  added at the end of the signature.
- `submitSenderId()` now sends both `use_case` and `usecase` because Termii's
  docs disagree on the field name.
- The base URL is now configurable (Termii issues account-specific base URLs)
  and defaults to `https://v3.api.termii.com`.

### Fixed
- The facade / container binding no longer fatals. The 0.1.x line instantiated
  the class without the required API key argument.
- WhatsApp media messages now send correctly; the previous implementation built
  the media payload and then immediately discarded it.
- Removed the redundant per-call status re-parsing that decoded the response
  JSON multiple times and masked the real API error body.

### Removed
- Empty stub methods `historyStatus()` and `senderIdStatus()`.
- The non-functional `bool $media` argument on `sendMessage()` (pass `mediaUrl`).

## 0.1.x

See the git history for the earlier Laravel 6–9 / PHP 7.4 releases.
