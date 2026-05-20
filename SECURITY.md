# Security Policy

## Supported Versions

| Version | Supported |
| --- | --- |
| 1.0.x | Yes |

## Reporting Security Issues

Report security issues privately to the repository owner instead of opening a public issue with exploit details.

## Release Safety

Do not commit:

- WHMCS `configuration.php`
- database dumps
- customer data
- API keys
- licensing secrets
- encoded commercial helper source with readable secrets
- ZIP packages that contain credentials

The public package contains source code for the addon only.
