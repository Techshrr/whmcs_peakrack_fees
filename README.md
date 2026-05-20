# PeakRack Gateway Fees for WHMCS

[English](README.md) | [简体中文](README.zh-CN.md)

PeakRack Gateway Fees is a WHMCS addon module for configurable payment gateway fees and country-based gateway allocation.
It adds a managed invoice line item for gateway surcharges, keeps unpaid invoice totals current, and can hide or reject payment methods that are not available for the client's billing country.

## Current Version

`1.0.1`

## Features

- Adds one managed invoice line item for the selected payment gateway.
- Supports fixed fees, percentage fees, minimum invoice amount rules, and gross-up calculation.
- Refreshes unpaid invoice fees on invoice creation, pre-email generation, gateway changes, admin invoice detail view, and client invoice view.
- Does not modify paid, cancelled, or refunded invoices.
- Can mark the gateway fee invoice item as taxable.
- Can skip tax-exempt clients.
- Supports country-based payment gateway allocation at checkout.
- Hides unavailable gateway choices in the client UI and validates the selected gateway server-side during checkout.
- Shows optional checkout fee details per gateway.
- Provides English and Chinese admin UI language switching from the header without saving the full settings form.
- Provides English and Chinese invoice item description templates.
- Detects Chinese client invoice language from the WHMCS client language/session and uses the Chinese fee description when appropriate.
- Supports JSON settings import/export from the addon admin page.
- Keeps addon logs with configurable retention and WHMCS daily cron cleanup.
- Mirrors addon events to WHMCS Activity Log when enabled.
- Keeps configuration and logs when the addon is deactivated.

## Compatibility

- WHMCS 9.x
- PHP 8.3
- MySQL/MariaDB supported by WHMCS

The addon uses WHMCS `Capsule`, addon module lifecycle functions, invoice helpers, and standard hook registration.

## Package Layout

The repository root is intentionally shallow for GitHub browsing. The deployable addon is the `peakrack_fees` directory:

```text
peakrack_fees/
```

Upload or copy `peakrack_fees` to `modules/addons/peakrack_fees/` in your WHMCS installation.

## Installation

1. From this repository, upload:

   ```text
   peakrack_fees/ -> modules/addons/peakrack_fees/
   ```

   The final addon path should be:

   ```text
   modules/addons/peakrack_fees/
   ```

2. In the WHMCS admin area, go to:

   ```text
   System Settings > Addon Modules
   ```

3. Activate **PeakRack Gateway Fees**.

4. Open:

   ```text
   Addons > PeakRack Gateway Fees
   ```

5. Review module controls, fee rules, country allocation rules, invoice descriptions, and log retention settings before enabling fees broadly.

## Configuration

### General Controls

- **Enable module**: Enables or disables fee calculation and allocation behavior.
- **Mark fee invoice item as taxable**: Marks the managed gateway fee invoice item as taxable.
- **Skip tax-exempt clients**: Removes or avoids the fee for WHMCS clients marked as tax-exempt.
- **Show checkout fee details**: Shows fee details near checkout payment choices.
- **Refresh fee when invoice is viewed**: Refreshes the managed fee when unpaid invoices are opened.
- **Enable gateway allocation**: Applies country-based payment method availability rules.
- **Validate allocation at checkout**: Rejects unavailable payment methods server-side during checkout.
- **Mirror events to WHMCS Activity Log**: Copies addon log messages into WHMCS Activity Log.
- **Log retention days**: Deletes old addon log rows during daily cron. Use `0` to disable age cleanup.
- **Maximum log rows**: Keeps only the newest addon log rows by count. Use `0` to disable count cleanup.
- **Admin language**: Saved default admin language. The header language switch can change the page language immediately without saving.

### Invoice Descriptions

The addon stores separate templates for English and Chinese invoice item descriptions.

Available placeholders:

- `{gateway}`: The configured gateway label.
- `{module}`: The WHMCS gateway module name.

When a client language looks Chinese, the addon uses the Chinese template. Otherwise it uses the English template.

### Gateway Rules

Each active WHMCS payment gateway appears automatically in the rule table after the addon page is refreshed.
New gateway rules are created with fees disabled by default.

Rule fields:

- **Label**: Human-readable gateway name used in invoice descriptions and checkout notices.
- **Fee**: Enables fee calculation for the gateway.
- **Percent**: Percentage fee applied to the invoice base amount.
- **Fixed**: Fixed fee added to the calculated gateway fee.
- **Minimum**: Minimum invoice base amount required before a fee is charged.
- **Calc**: `Standard` or `Gross-up`.
- **Country Mode**: `All`, `Allow`, or `Block`.
- **Countries**: ISO-3166 alpha-2 country codes such as `US`, `CA`, `CN`.
- **Notice**: Controls whether the gateway's fee details are shown at checkout.

Calculation behavior:

- **Standard**: `invoice base * percent + fixed`.
- **Gross-up**: Calculates a fee intended to cover percentage processor cost on the final charged amount.

Country allocation behavior:

- **All**: Gateway is available for every country.
- **Allow**: Gateway is available only for listed countries.
- **Block**: Gateway is hidden and rejected for listed countries.

## Runtime Hooks

The addon registers these WHMCS hooks from `hooks.php`:

- `InvoiceCreation`: Applies or refreshes the gateway fee when an invoice is created.
- `InvoiceCreationPreEmail`: Refreshes the fee before invoice emails are generated.
- `InvoiceChangeGateway`: Recalculates the fee after the invoice payment gateway changes.
- `ViewInvoiceDetailsPage`: Refreshes the fee when an admin views an unpaid invoice.
- `ClientAreaPageViewInvoice`: Refreshes the fee when the client views an unpaid invoice.
- `ShoppingCartValidateCheckout`: Validates selected gateway availability server-side.
- `ShoppingCartCheckoutOutput`: Renders checkout fee details and allocation data.
- `ClientAreaFooterOutput`: Injects client-side allocation behavior for invoice/payment pages.
- `DailyCronJob`: Cleans old addon logs according to retention settings.

## Database Tables

The addon creates these tables during activation:

- `mod_peakrack_fees_settings`
- `mod_peakrack_fees_logs`

Tables are not removed on deactivation so configuration and troubleshooting history are preserved.

This public package intentionally uses `mod_peakrack_fees_*` table names. It does not migrate earlier development-table names such as `mod_peakrack_gateway_fees_*`.

## Project Structure

```text
peakrack_fees/
  peakrack_fees.php       Addon entrypoint, activation, upgrade, and admin page
  hooks.php               WHMCS hook registration
  README.md               Module documentation
  README.zh-CN.md         Simplified Chinese module documentation
  UPGRADE.md              Module-local upgrade notes
  UPGRADE.zh-CN.md        Simplified Chinese module-local upgrade notes
  lib/
    Bootstrap.php         Defaults, settings, invoice fee logic, allocation, logging
```

## Safety Notes

- The addon only manages invoice items with type `PeakRackGatewayFee`.
- Paid, cancelled, and refunded invoices are not modified.
- Test with an unpaid invoice before enabling fees broadly.
- Confirm tax settings before marking gateway fee invoice items as taxable.
- Keep only one copy of the addon enabled; do not leave earlier development folders active.
- Public releases must not include customer credentials, WHMCS `configuration.php`, database dumps, commercial license helpers, or encoded local-only files.

## Upgrade Notes

See [UPGRADE.md](UPGRADE.md) for release-by-release upgrade details.

## Development Checks

Run PHP syntax checks before packaging:

```powershell
Get-ChildItem -Path peakrack_fees -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
```

Expected result: no syntax errors.

## License

MIT License. See the repository [LICENSE](LICENSE) file for the full terms.
