<?php
// SPDX-License-Identifier: Apache-2.0

/**
 * PeakRack Gateway Fees for WHMCS
 *
 * Official repository:
 * https://github.com/Techshrr/whmcs_peakrack_fees
 *
 * Copyright 2026 PeakRack.
 * Licensed under the Apache License, Version 2.0.
 * See the LICENSE and NOTICE files for details.
 */

if (!defined('WHMCS')) {
    die('No direct access');
}

require_once __DIR__ . '/lib/Bootstrap.php';

add_hook('InvoiceCreation', 1, static function (array $vars): void {
    peakrackGatewayFeesApplyToInvoice((int) ($vars['invoiceid'] ?? 0), 'invoice creation');
});

add_hook('InvoiceCreationPreEmail', 1, static function (array $vars): void {
    peakrackGatewayFeesApplyToInvoice((int) ($vars['invoiceid'] ?? 0), 'invoice pre-email');
});

add_hook('InvoiceChangeGateway', 1, static function (array $vars): void {
    peakrackGatewayFeesApplyToInvoice((int) ($vars['invoiceid'] ?? 0), 'gateway change');
});

add_hook('ViewInvoiceDetailsPage', 1, static function (array $vars): void {
    peakrackGatewayFeesApplyToInvoice((int) ($vars['invoiceid'] ?? 0), 'invoice details');
});

add_hook('ClientAreaPageViewInvoice', 1, static function (array $vars): array {
    peakrackGatewayFeesApplyToInvoice((int) ($vars['invoiceid'] ?? ($_GET['id'] ?? 0)), 'client invoice view');
    return [];
});

add_hook('ShoppingCartValidateCheckout', 1, static function (array $vars): array {
    return peakrackGatewayFeesCheckoutValidation($vars);
});

add_hook('ShoppingCartCheckoutOutput', 1, static function (array $vars): string {
    return peakrackGatewayFeesCheckoutOutput($vars);
});

add_hook('ClientAreaFooterOutput', 1, static function (array $vars): string {
    $filename = strtolower((string) ($vars['filename'] ?? ''));

    if ($filename === 'viewinvoice') {
        return peakrackGatewayFeesInvoiceOutput($vars);
    }

    return '';
});

add_hook('DailyCronJob', 1, static function (array $vars): void {
    try {
        peakrackGatewayFeesCleanupLogs(peakrackGatewayFeesLoadSettings());
    } catch (Throwable $e) {
        if (function_exists('logActivity')) {
            logActivity('PeakRack Gateway Fees: log cleanup failed: ' . $e->getMessage());
        }
    }
});
