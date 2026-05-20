# PeakRack Gateway Fees for WHMCS

PeakRack Gateway Fees is a WHMCS addon module for configurable payment method fees and country-based gateway allocation.

Version: `1.0.0`

## Compatibility

- WHMCS 9.x
- PHP 8.3
- MySQL/MariaDB supported by WHMCS

## Features

- Adds one managed invoice line item for the selected payment gateway.
- Supports fixed fees, percentage fees, and gross-up calculation.
- Supports minimum invoice amount per gateway.
- Can mark the fee invoice item as taxable.
- Can skip tax-exempt clients.
- Refreshes unpaid invoice fees on invoice creation, gateway change, and invoice view.
- Hides unavailable gateways on checkout and invoice pages based on billing country.
- Validates gateway allocation during checkout before the order is created.
- Shows checkout fee details per gateway.
- Keeps a recent event log in the addon admin page.
- Cleans old log rows during the WHMCS daily cron according to configured retention settings.
- Stores settings in the dedicated `mod_peakrack_fees_*` tables.

## Package Layout

Upload this folder to:

```text
modules/addons/peakrack_fees
```

The source package follows the PeakRack underscore style:

```text
whmcs_peakrack_fees/peakrack_fees
```

## Installation

1. Upload `peakrack_fees` to `modules/addons/`.
2. In WHMCS admin, open `System Settings > Addon Modules`.
3. Activate `PeakRack Gateway Fees`.
4. Open `Addons > PeakRack Gateway Fees`.
5. Enable fee rules for the gateway module names you want to charge.

## Rule Notes

- `Standard` calculation: `invoice base * percent + fixed`.
- `Gross-up` calculation: calculates a fee intended to cover percentage processor cost on the final charged amount.
- Country codes should be ISO-3166 alpha-2 values such as `US`, `CA`, `CN`.
- `Allow` mode permits only listed countries.
- `Block` mode hides and rejects listed countries.

## Runtime Hooks

- `InvoiceCreation`
- `InvoiceCreationPreEmail`
- `InvoiceChangeGateway`
- `ViewInvoiceDetailsPage`
- `ClientAreaPageViewInvoice`
- `ShoppingCartValidateCheckout`
- `ShoppingCartCheckoutOutput`
- `ClientAreaFooterOutput`

## Safety

The module only manages invoice items with type `PeakRackGatewayFee`. It removes or updates that managed item while an invoice is unpaid when a selected gateway has no active rule or when a client is tax-exempt and skipping is enabled. Paid, cancelled, and refunded invoices are not modified.

