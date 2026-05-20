# Upgrade Guide

## From a development build using `peakrack_gateway_fees`

The final module name is:

```text
peakrack_fees
```

The final WHMCS addon module path is:

```text
modules/addons/peakrack_fees
```

The final database tables are:

```text
mod_peakrack_fees_settings
mod_peakrack_fees_logs
```

If an older development build was installed under `modules/addons/peakrack_gateway_fees`, deactivate and remove that old folder before activating `peakrack_fees`.

This release intentionally does not migrate old development-table data from `mod_peakrack_gateway_fees_*`.

## Standard Upgrade

1. Back up your WHMCS database.
2. Upload the new `peakrack_fees` folder to `modules/addons/peakrack_fees`.
3. Replace all existing files in that folder.
4. Open **System Settings > Addon Modules** and confirm the module is active.
5. Open **Addons > PeakRack Gateway Fees** and review settings.
6. Test with an unpaid invoice before enabling fees broadly.
