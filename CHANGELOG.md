# Changelog

All notable changes to this project are documented in this file.

This project follows Semantic Versioning where practical.

## [1.0.1] - 2026-05-20

### Changed

- Updated repository documentation, bilingual upgrade notes, package metadata, and release files for public distribution.
- Bumped addon version to `1.0.1`.

## [1.0.0] - 2026-05-20

### Added

- Initial release.
- Added WHMCS addon module `peakrack_fees`.
- Added configurable payment gateway fee rules with fixed, percentage, minimum amount, and gross-up calculation modes.
- Added managed invoice item type `PeakRackGatewayFee`.
- Added invoice fee refresh on invoice creation, pre-email generation, gateway changes, admin invoice view, and client invoice view.
- Added checkout country-based gateway allocation with client-side hiding and server-side validation.
- Added English and Chinese admin UI language switching.
- Added English and Chinese invoice item description templates.
- Added JSON import/export for normalized addon settings.
- Added module log table with retention settings and WHMCS daily cron cleanup.

### Fixed

- Loaded WHMCS invoice helpers before updating invoice totals.
- Used invoice due dates for managed invoice items.
- Reselected an allowed gateway in the frontend when the current selection becomes unavailable.
- Avoided modifications to paid, cancelled, and refunded invoices.