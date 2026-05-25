# PeakRack Gateway Fees for WHMCS

> Official repository: https://github.com/Techshrr/whmcs_peakrack_fees
> License: Apache License 2.0

PeakRack Gateway Fees is a WHMCS addon for configurable payment gateway fees and country-based gateway allocation.

## Overview

The addon adds one managed invoice line item for the selected payment gateway. It can calculate fixed and percentage fees, refresh unpaid invoices during common WHMCS invoice flows, and restrict payment gateway choices by billing country.

Settings and logs are stored in module tables. Deactivation keeps configuration and logs.

## Features

- Adds a managed invoice item with type `PeakRackGatewayFee`.
- Supports fixed fees, percentage fees, minimum invoice amount rules, and gross-up calculation.
- Refreshes unpaid invoice fees on invoice creation, pre-email generation, gateway changes, admin invoice view, and client invoice view.
- Does not modify paid, cancelled, or refunded invoices.
- Supports optional taxable fee items and tax-exempt client skipping.
- Supports country-based payment gateway allocation at checkout.
- Hides unavailable gateway choices in the client UI and validates the selected gateway server-side.
- Provides English and Chinese admin UI language switching.
- Provides English and Chinese invoice item description templates.
- Supports JSON settings import and export.
- Keeps module logs with configurable retention and optional WHMCS Activity Log mirroring.

## Requirements

- WHMCS 9.0.x
- PHP 8.3 or later
- MySQL 5.7 / 8.0

## Installation

1. Download the latest release from the official repository.
2. Upload the addon directory to:

   `modules/addons/peakrack_fees/`

3. Log in to the WHMCS admin area.
4. Go to **System Settings > Addon Modules** and activate **PeakRack Gateway Fees**.
5. Open **Addons > PeakRack Gateway Fees** and review rules before enabling fees broadly.

## Configuration

| Option | Description | Default |
|---|---|---|
| Enable module | Enables fee calculation and allocation behavior | Enabled |
| Mark fee invoice item as taxable | Marks managed fee invoice items as taxable | Disabled |
| Skip tax-exempt clients | Avoids fees for tax-exempt clients | Enabled |
| Show checkout fee details | Shows fee details near checkout payment choices | Enabled |
| Refresh fee when invoice is viewed | Refreshes unpaid invoices when viewed | Enabled |
| Enable gateway allocation | Applies country-based gateway rules | Enabled |
| Validate allocation at checkout | Rejects unavailable gateways during checkout | Enabled |
| Mirror events to WHMCS Activity Log | Copies module events to WHMCS Activity Log | Enabled |
| Log retention days | Age-based module log cleanup | 180 |
| Maximum log rows | Count-based module log cleanup | 5000 |
| Admin language | Saved default admin UI language | en |
| English invoice item description | Description template for non-Chinese clients | Payment Gateway Fee ({gateway}) |
| Chinese invoice item description | Description template for Chinese clients | 支付网关手续费（{gateway}） |
| Gateway rule fields | Per-gateway label, fee toggle, percent, fixed amount, minimum, calculation mode, country mode, country list, and checkout notice | Disabled per gateway |

## Usage

The administrator defines rules for active WHMCS payment gateways. Each rule can enable a fee, set the calculation method, set country availability, and decide whether checkout fee details are shown.

When an unpaid invoice is created or refreshed, the addon updates its managed gateway fee line item for the selected gateway. At checkout, unavailable gateways can be hidden and rejected server-side based on the client's billing country.

## Database Tables

- `mod_peakrack_fees_settings`
- `mod_peakrack_fees_logs`

## Upgrade

See [UPGRADE.md](UPGRADE.md).

## Chinese Documentation

See [README.zh-CN.md](README.zh-CN.md).

## Security

Do not commit production credentials, API keys, database passwords, payment secrets, WHMCS license data, customer data, identity documents, or private signing keys.

To report a security issue, see [SECURITY.md](SECURITY.md).

## License

This project is licensed under the Apache License 2.0. See [LICENSE](LICENSE) for details.

Additional project notices are available in [NOTICE](NOTICE).
