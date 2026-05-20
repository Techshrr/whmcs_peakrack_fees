# PeakRack Gateway Fees Upgrade Notes

## 1.0.1

Documentation and repository polish release.

- Addon version bumped to `1.0.1`.
- Added module-local English and Simplified Chinese documentation.
- No database changes are required.
- Replace the existing `modules/addons/peakrack_fees/` files with this updated folder.

## 1.0.0

Initial public release.

- Final module directory: `peakrack_fees`.
- Final WHMCS addon path: `modules/addons/peakrack_fees`.
- Final database tables: `mod_peakrack_fees_settings` and `mod_peakrack_fees_logs`.
- Earlier development folders such as `peakrack_gateway_fees` should be disabled and removed before activating this module.
- Earlier development tables such as `mod_peakrack_gateway_fees_*` are not migrated.

## Standard Manual Upgrade

1. Back up your WHMCS database.
2. Upload this `peakrack_fees` folder to `modules/addons/peakrack_fees`.
3. Replace all existing files in that folder.
4. Confirm the addon is active in **System Settings > Addon Modules**.
5. Review settings from **Addons > PeakRack Gateway Fees**.
6. Test with an unpaid invoice before enabling fees broadly.
