# Security Policy

## Reporting a vulnerability

Please do not open public GitHub issues for security vulnerabilities.

Report issues involving fee calculation, gateway-allocation enforcement, invoice item updates, or checkout validation to:

security@peakrack.com

Please include:

- Affected addon version, WHMCS version, and PHP version
- Whether the issue affects invoice refresh, checkout gateway selection, tax handling, or admin import/export
- Description of the issue and reproduction steps
- Potential impact on invoice totals or payment-gateway availability
- Suggested mitigation, if available

## Supported versions

| Version | Supported |
|---|---|
| 1.x | Yes |
| < 1.0 | No |

## Sensitive data

Do not include production gateway fee rules, country-allocation policies, customer billing countries tied to real accounts, invoice IDs, transaction IDs, WHMCS license data, gateway credentials, or server logs containing customer identifiers.

## Public issues

Installation problems, calculation edge cases using sample data, and documentation fixes may be submitted through GitHub Issues.

Security vulnerabilities must be reported privately by email.
