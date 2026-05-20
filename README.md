# PeakRack Gateway Fees for WHMCS

PeakRack Gateway Fees is a WHMCS addon module for configurable payment gateway fees and country-based gateway availability rules.

## Version

Current release: `1.0.0`

## Compatibility

- WHMCS 9.x
- PHP 8.3
- MySQL/MariaDB supported by WHMCS

## Features

- Adds one managed invoice item for the selected payment gateway.
- Supports percentage fees, fixed fees, minimum invoice amount rules, and gross-up calculation.
- Refreshes unpaid invoice fees on invoice creation, gateway change, admin invoice detail view, and client invoice view.
- Does not modify paid, cancelled, or refunded invoices.
- Supports taxable or non-taxable gateway fee invoice items.
- Can skip tax-exempt clients.
- Supports country-based payment gateway allocation at checkout.
- Hides unavailable gateway choices in the client UI and validates them server-side during checkout.
- Provides English and Chinese admin UI language switching without saving settings.
- Provides English and Chinese invoice item description templates.
- Keeps module logs with configurable retention and daily cron cleanup.

## Installation

Upload the module folder:

```text
peakrack_fees
```

to:

```text
modules/addons/peakrack_fees
```

Then activate **PeakRack Gateway Fees** in:

```text
System Settings > Addon Modules
```

Open the addon page from:

```text
Addons > PeakRack Gateway Fees
```

## Database Tables

The addon creates and uses:

- `mod_peakrack_fees_settings`
- `mod_peakrack_fees_logs`

## Notes

- New WHMCS payment gateways appear automatically after they are enabled in WHMCS and the addon page is refreshed.
- New gateway rules are created with fees disabled by default.
- The addon only manages invoice items with type `PeakRackGatewayFee`.
- Public releases must not include encoded commercial license helpers, customer credentials, or WHMCS `configuration.php`.

## 中文说明

这是适用于 WHMCS 9.x / PHP 8.3 的支付网关手续费插件。安装时只需要上传 `peakrack_fees` 文件夹到 `modules/addons/peakrack_fees`，然后在 WHMCS 后台启用插件。

插件会自动读取 WHMCS 已启用的支付方式。新增支付方式后刷新插件后台即可看到新规则，默认不会启用手续费。
