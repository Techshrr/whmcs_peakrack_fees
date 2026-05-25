# Upgrade Guide

This guide explains how to upgrade this module from an older version.

## Before upgrading

1. Back up the WHMCS files.
2. Back up the WHMCS database.
3. Make a copy of `modules/addons/peakrack_fees/`.
4. Review [CHANGELOG.md](CHANGELOG.md).
5. Check whether the upgrade includes database changes.

## Upgrade steps

1. Download the latest release from the official repository:

   https://github.com/Techshrr/whmcs_peakrack_fees

2. Replace the addon files in:

   `modules/addons/peakrack_fees/`

3. Keep existing WHMCS module settings.
4. Log in to the WHMCS admin area.
5. Open **Addons > PeakRack Gateway Fees** and verify all options and gateway rules.
6. Clear the WHMCS template cache if invoice or checkout output does not update.

## Database migrations

This version does not require manual database migration.

The addon creates its settings and log tables during activation or admin access.

## Version-specific notes

### Upgrade from 1.0.0 to 1.0.1

- No breaking changes.
- Existing settings are preserved.
- No manual database changes are required.

## Rollback

To roll back:

1. Restore the previous `modules/addons/peakrack_fees/` directory.
2. Restore the database backup if the upgrade changed module tables.
3. Clear the WHMCS template cache.
4. Check the WHMCS activity log and module logs for errors.

## Notes

Do not overwrite production credentials, local configuration files, custom templates, callback secrets, or payment credentials unless the upgrade notes explicitly require it.