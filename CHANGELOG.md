# Changelog

## 1.0.1 - 2026-05-20

Documentation and repository polish release.

### Added

- MIT license file for GitHub license detection.
- Full English and Simplified Chinese README files at the repository root.
- Full English and Simplified Chinese upgrade guides at the repository root.
- Module-local English and Simplified Chinese README and upgrade guides for packaged distributions.
- Clearer installation, configuration, runtime hook, database table, safety, and development-check documentation.

### Changed

- Addon version bumped to `1.0.1`.
- GitHub-facing project documentation now follows the same bilingual release structure used by the PeakRack Risk addon.

## 1.0.0 - 2026-05-20

Initial public release.

### Added

- WHMCS addon module `peakrack_fees`.
- Configurable payment gateway fee rules.
- Fixed fee, percentage fee, minimum invoice amount, and gross-up calculation modes.
- Managed invoice item type `PeakRackGatewayFee`.
- Invoice fee refresh on invoice creation, pre-email generation, gateway changes, admin invoice detail view, and client invoice view.
- Checkout country-based gateway allocation with client-side hiding and server-side validation.
- English and Chinese admin UI language switching from the header.
- English and Chinese invoice item description templates.
- JSON import/export for normalized addon settings.
- Module log table with retention settings and WHMCS daily cron cleanup.
- GitHub Actions workflow for PHP syntax checks.

### Hardened

- Invoice totals now load WHMCS invoice helpers before calling `updateInvoiceTotal()`.
- Managed invoice item due dates now follow the invoice due date instead of always using the current date.
- Frontend gateway allocation now reselects an allowed gateway if the current selection becomes unavailable.
- Paid, cancelled, and refunded invoices are not modified.

### Compatibility

- Targeted for WHMCS 9.x and PHP 8.3.
