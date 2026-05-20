# PeakRack Gateway Fees for WHMCS

[English](README.md) | [简体中文](README.zh-CN.md)

PeakRack Gateway Fees is a WHMCS addon module for configurable payment gateway fees and country-based gateway allocation.
This directory is the deployable addon folder for WHMCS.

## Current Version

`1.0.1`

## Compatibility

- WHMCS 9.x
- PHP 8.3
- MySQL/MariaDB supported by WHMCS

## Installation

Upload this `peakrack_fees` folder to your WHMCS installation:

```text
modules/addons/peakrack_fees/
```

Then open **System Settings > Addon Modules**, activate **PeakRack Gateway Fees**, and configure the addon from **Addons > PeakRack Gateway Fees**.

## Features

- Adds one managed invoice line item for the selected payment gateway.
- Supports fixed fees, percentage fees, minimum invoice amount rules, and gross-up calculation.
- Refreshes unpaid invoice fees on invoice creation, pre-email generation, gateway changes, admin invoice detail view, and client invoice view.
- Does not modify paid, cancelled, or refunded invoices.
- Can mark the fee invoice item as taxable.
- Can skip tax-exempt clients.
- Supports country-based gateway allocation at checkout.
- Hides unavailable gateway choices in the client UI and validates the selected gateway server-side during checkout.
- Shows optional checkout fee details per gateway.
- Provides English and Chinese admin UI language switching from the header without saving the full settings form.
- Provides English and Chinese invoice item description templates.
- Detects Chinese client invoice language from the WHMCS client language/session and uses the Chinese fee description when appropriate.
- Supports JSON settings import/export.
- Keeps addon logs with configurable retention and WHMCS daily cron cleanup.

## Configuration Summary

### General Controls

- **Enable module** controls all fee and allocation behavior.
- **Mark fee invoice item as taxable** controls the managed invoice item's tax flag.
- **Skip tax-exempt clients** avoids fees for WHMCS tax-exempt clients.
- **Show checkout fee details** displays fee details near checkout payment choices.
- **Refresh fee when invoice is viewed** keeps unpaid invoice totals current.
- **Enable gateway allocation** enables country-based availability rules.
- **Validate allocation at checkout** rejects unavailable methods server-side.
- **Mirror events to WHMCS Activity Log** copies addon log messages into WHMCS Activity Log.

### Gateway Rules

Every enabled WHMCS payment gateway appears automatically after this page is refreshed.
New rules are created with fees disabled by default.

Rule fields include label, enabled fee state, percent, fixed amount, minimum invoice amount, calculation mode, country mode, country list, and checkout notice visibility.

Country codes use ISO-3166 alpha-2 values such as `US`, `CA`, `CN`.

### Invoice Descriptions

English and Chinese invoice item description templates are stored separately.

Available placeholders:

- `{gateway}`: Gateway label.
- `{module}`: WHMCS gateway module name.

## Runtime Hooks

- `InvoiceCreation`
- `InvoiceCreationPreEmail`
- `InvoiceChangeGateway`
- `ViewInvoiceDetailsPage`
- `ClientAreaPageViewInvoice`
- `ShoppingCartValidateCheckout`
- `ShoppingCartCheckoutOutput`
- `ClientAreaFooterOutput`
- `DailyCronJob`

## Database Tables

- `mod_peakrack_fees_settings`
- `mod_peakrack_fees_logs`

Tables are kept when the addon is deactivated.

## Safety Notes

- The addon only manages invoice items with type `PeakRackGatewayFee`.
- Paid, cancelled, and refunded invoices are not modified.
- Test with an unpaid invoice before enabling fees broadly.
- Keep older development addon folders disabled or removed to avoid duplicate hooks.

## Upgrade Notes

See [UPGRADE.md](UPGRADE.md) for release-by-release upgrade details.
