# Changelog

All notable changes to `lara-termii` will be documented in this file.

## 2.0.0 - 2026-07-06

Modernized release.

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
- The facade / container binding no longer fatals. v1 instantiated the class
  without the required API key argument.
- WhatsApp media messages now send correctly; the previous implementation built
  the media payload and then immediately discarded it.
- Removed the redundant per-call status re-parsing that decoded the response
  JSON multiple times and masked the real API error body.

### Removed
- Empty stub methods `historyStatus()` and `senderIdStatus()`.
- The non-functional `bool $media` argument on `sendMessage()` (pass `mediaUrl`).

## 1.x

See the git history for the Laravel 6–9 / PHP 7.4 releases.
