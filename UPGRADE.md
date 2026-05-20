# PeakRack Gateway Fees Upgrade Notes

## 1.0.1

Documentation and repository polish release for WHMCS 9.x / PHP 8.3.

### Added

- Repository-level MIT `LICENSE`.
- Full English and Simplified Chinese README files.
- Full English and Simplified Chinese upgrade guides.
- Module-local README and upgrade guide files for packaged distributions.
- Clearer documentation for gateway rules, country allocation, runtime hooks, database tables, and safety notes.

### Changed

- Addon version bumped to `1.0.1`.
- GitHub repository metadata should use the `whmcs`, `peakrack`, and `payment-gateway` topics and the PeakRack homepage.

### Upgrade Notes

- Existing WHMCS installs do not require database changes for this release.
- Copy the updated `peakrack_fees/` directory over `modules/addons/peakrack_fees/`.
- Open **System Settings > Addon Modules** and confirm the addon version shows `1.0.1`.
- Review the new documentation before distributing the package to customers.

## 1.0.0

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

- Invoice totals load WHMCS invoice helpers before calling `updateInvoiceTotal()`.
- Managed invoice item due dates follow the invoice due date.
- Frontend gateway allocation reselects an allowed gateway if the current selection becomes unavailable.
- Paid, cancelled, and refunded invoices are not modified.

### Upgrade Notes

- This release uses the final module name:

  ```text
  peakrack_fees
  ```

- The final WHMCS addon path is:

  ```text
  modules/addons/peakrack_fees
  ```

- The final database tables are:

  ```text
  mod_peakrack_fees_settings
  mod_peakrack_fees_logs
  ```

- If an older development build was installed under `modules/addons/peakrack_gateway_fees`, deactivate and remove that old folder before activating `peakrack_fees`.
- This release intentionally does not migrate old development-table data from `mod_peakrack_gateway_fees_*`.

## Standard Manual Upgrade

1. Back up your WHMCS database.
2. Upload the new `peakrack_fees` folder to `modules/addons/peakrack_fees`.
3. Replace all existing files in that folder.
4. Open **System Settings > Addon Modules** and confirm the module is active.
5. Open **Addons > PeakRack Gateway Fees** and review settings.
6. Test with an unpaid invoice before enabling fees broadly.
