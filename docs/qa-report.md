# QA Report

**Validation date:** 2026-08-18
**Environment:** Docker Compose, PHP 8.2, MariaDB 10.11, anonymous demo database
**Scope:** functional regression, security hardening, route smoke checks, and documentation screenshots

## Status

The hardened local build passed the final validation gates below. This report is evidence for the local QA environment; it does not claim production deployment readiness.

## Final validation matrix

| Check | Result |
| --- | --- |
| PHP syntax checks | **39/39 passed** |
| JavaScript syntax check (`src/js/main.js`) | **Passed** |
| Authenticated route crawl | **23/23 routes returned HTTP 200** |
| Regression and security assertions | **20/20 passed** |
| Logout security assertions | **7/7 passed** |
| Browser smoke flows | **Passed** |
| Final application-log scan | **0 suspect entries** |

The final log scan found no fatal errors, parse errors, uncaught exceptions, PHP warnings/notices, MySQL errors, or undefined-function errors.

## Regression and security coverage

The `20/20` regression suite verified:

- Public home page responds successfully.
- Login page exposes a CSRF token.
- Valid admin login succeeds with a valid token.
- Admin POST without a CSRF token is rejected.
- Rejected admin POST does not mutate the database.
- QR generation POST without a CSRF token is rejected.
- NIK lookup requires authentication.
- Registration exposes a CSRF token.
- Registration does not expose a public NIK autocomplete endpoint.
- Distribution rejects an animal outside the active qurban period.
- Valid distribution generation succeeds.
- Valid distribution creates the requested package.
- QR/package scan accepts a package once.
- Duplicate QR/package claim is rejected.
- Resident login succeeds with a valid token.
- Invalid fixed payment amount is rejected.
- Invalid payment amount does not mutate the database.
- Valid payment is recorded.
- Payment verification succeeds.
- Payment verification links the payment to the transaction atomically.

The `7/7` logout suite verified:

- Logout form exposes a CSRF token.
- GET logout is rejected.
- GET logout does not destroy the session.
- Valid POST logout is accepted with CSRF.
- The session is denied after logout.

## Route smoke coverage

The authenticated route crawl covered these 23 paths:

```text
/
/index.php
/admin/index.php
/admin/kelola_hewan.php
/admin/kelola_periode.php
/admin/kelola_user.php
/admin/edit_hewan.php?id=1
/admin/edit_periode.php?id=1
/panitia/index.php
/panitia/input_transaksi.php
/panitia/kelola_pembayaran_qurban.php
/panitia/verifikasi_pembayaran.php
/panitia/distribusi_daging.php
/panitia/detail_distribusi.php?id=1
/panitia/scan_qr.php
/panitia/laporan_keuangan.php
/warga/index.php
/warga/bayar_iuran.php
/berqurban/index.php
/berqurban/daftar_qurban.php
/berqurban/bayar_iuran.php
/laporan_keuangan.php
/auth/register.php
```

All returned HTTP 200 and no response contained the checked error markers.

## Browser evidence

A clean Chromium session was used against the same QA stack. The following flows rendered successfully:

1. Login page
2. Admin dashboard
3. Homepage
4. Payment verification
5. Meat distribution

Full-page captures are stored in [`docs/screenshots/`](screenshots/):

- `01-login.png`
- `02-admin-dashboard.png`
- `03-homepage.png`
- `04-payment-verification.png`
- `05-distribution.png`

The screenshots intentionally show anonymous demo data. They are not production records.

## Hardening implemented

- Database credentials are read from environment variables.
- CSRF token generation and validation are centralized in the helper layer.
- State-changing forms and endpoints were audited for CSRF enforcement.
- Logout is POST-only and requires a valid CSRF token.
- The NIK lookup endpoint requires authentication.
- Payment amounts are validated against the application payment schedule.
- Payment verification uses a transaction and links the verification record atomically.
- Distribution generation validates the active period and animal, then persists the package transactionally.
- QR/package claims reject duplicate claims.
- The homepage duplicate `rupiah()` declaration was removed so anonymous and authenticated homepage requests render completely.
- Footer identity and contact metadata use the project owner’s approved public portfolio identity.

## Remaining limitations

This pass does not replace a production security review. The following remain outside this local validation scope:

- Full penetration testing.
- Aggressive concurrent-request and race-condition testing.
- Failure injection for database and external-service outages.
- Production HTTPS, secret rotation, backups, and monitoring.
- Complete review of every file-upload and image-validation path.
- Replacement or self-hosting of the external QR image service.

## Reproduction

Start the stack from the project root:

```bash
APP_PORT=18080 docker compose up --build
```

Then run the project’s local QA scripts in the prepared QA environment. The exact demo credentials and setup notes are documented in the root [README](../README.md).
