<?php

/**
 * Shared runtime helpers for PeakRack Gateway Fees.
 *
 * Target runtime: WHMCS 9.x / PHP 8.3.
 */

use WHMCS\Database\Capsule;

if (!defined('WHMCS')) {
    die('No direct access');
}

const PRGF_MODULE = 'peakrack_fees';
const PRGF_VERSION = '1.0.0';
const PRGF_SETTING_KEY = 'config';
const PRGF_SETTINGS_TABLE = 'mod_peakrack_fees_settings';
const PRGF_LOGS_TABLE = 'mod_peakrack_fees_logs';
const PRGF_FEE_TYPE = 'PeakRackGatewayFee';

if (!function_exists('peakrackGatewayFeesDefaults')) {
    function peakrackGatewayFeesDefaults(): array
    {
        return [
            'enabled' => true,
            'adminLanguage' => 'en',
            'taxed' => false,
            'skipTaxExemptClients' => true,
            'checkoutDetails' => true,
            'invoiceDetails' => true,
            'allocationEnabled' => true,
            'checkoutAllocationValidation' => true,
            'activityLog' => true,
            'logRetentionDays' => 180,
            'maxLogs' => 5000,
            'descriptionTemplate' => 'Payment Gateway Fee ({gateway})',
            'descriptionTemplateZh' => '支付网关手续费（{gateway}）',
            'rules' => [],
        ];
    }
}

if (!function_exists('peakrackGatewayFeesCreateTables')) {
    function peakrackGatewayFeesCreateTables(): void
    {
        $schema = Capsule::schema();

        if (!$schema->hasTable(PRGF_SETTINGS_TABLE)) {
            $schema->create(PRGF_SETTINGS_TABLE, static function ($table): void {
                $table->increments('id');
                $table->string('setting', 100)->unique();
                $table->longText('value');
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (!$schema->hasTable(PRGF_LOGS_TABLE)) {
            $schema->create(PRGF_LOGS_TABLE, static function ($table): void {
                $table->increments('id');
                $table->unsignedInteger('invoice_id')->nullable()->index();
                $table->unsignedInteger('client_id')->nullable()->index();
                $table->string('gateway', 80)->nullable()->index();
                $table->string('level', 20)->index();
                $table->string('message', 255);
                $table->longText('context')->nullable();
                $table->timestamp('created_at')->nullable()->index();
            });
        }
    }
}

if (!function_exists('peakrackGatewayFeesLoadSettings')) {
    function peakrackGatewayFeesLoadSettings(): array
    {
        try {
            peakrackGatewayFeesCreateTables();
            $row = Capsule::table(PRGF_SETTINGS_TABLE)
                ->where('setting', PRGF_SETTING_KEY)
                ->first();
            $stored = $row ? peakrackGatewayFeesJsonDecode((string) $row->value, []) : [];
        } catch (Throwable $e) {
            $stored = [];
        }

        return peakrackGatewayFeesMergeSettings(peakrackGatewayFeesDefaults(), $stored);
    }
}

if (!function_exists('peakrackGatewayFeesSaveSettings')) {
    function peakrackGatewayFeesSaveSettings(array $settings): void
    {
        peakrackGatewayFeesCreateTables();
        $settings = peakrackGatewayFeesMergeSettings(peakrackGatewayFeesDefaults(), $settings);
        $payload = [
            'setting' => PRGF_SETTING_KEY,
            'value' => peakrackGatewayFeesJsonEncode($settings),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $exists = Capsule::table(PRGF_SETTINGS_TABLE)
            ->where('setting', PRGF_SETTING_KEY)
            ->exists();

        if ($exists) {
            Capsule::table(PRGF_SETTINGS_TABLE)
                ->where('setting', PRGF_SETTING_KEY)
                ->update($payload);
            return;
        }

        Capsule::table(PRGF_SETTINGS_TABLE)->insert($payload);
    }
}

if (!function_exists('peakrackGatewayFeesMergeSettings')) {
    function peakrackGatewayFeesMergeSettings(array $defaults, array $stored): array
    {
        $settings = array_replace($defaults, $stored);
        $settings['enabled'] = peakrackGatewayFeesBool($settings['enabled'] ?? $defaults['enabled']);
        $settings['taxed'] = peakrackGatewayFeesBool($settings['taxed'] ?? $defaults['taxed']);
        $settings['skipTaxExemptClients'] = peakrackGatewayFeesBool($settings['skipTaxExemptClients'] ?? $defaults['skipTaxExemptClients']);
        $settings['checkoutDetails'] = peakrackGatewayFeesBool($settings['checkoutDetails'] ?? $defaults['checkoutDetails']);
        $settings['invoiceDetails'] = peakrackGatewayFeesBool($settings['invoiceDetails'] ?? $defaults['invoiceDetails']);
        $settings['allocationEnabled'] = peakrackGatewayFeesBool($settings['allocationEnabled'] ?? $defaults['allocationEnabled']);
        $settings['checkoutAllocationValidation'] = peakrackGatewayFeesBool($settings['checkoutAllocationValidation'] ?? $defaults['checkoutAllocationValidation']);
        $settings['activityLog'] = peakrackGatewayFeesBool($settings['activityLog'] ?? $defaults['activityLog']);
        $settings['logRetentionDays'] = peakrackGatewayFeesClampInt($settings['logRetentionDays'] ?? $defaults['logRetentionDays'], 0, 3650, (int) $defaults['logRetentionDays']);
        $settings['maxLogs'] = peakrackGatewayFeesClampInt($settings['maxLogs'] ?? $defaults['maxLogs'], 0, 1000000, (int) $defaults['maxLogs']);
        $settings['adminLanguage'] = in_array((string) ($settings['adminLanguage'] ?? 'en'), ['en', 'zh'], true)
            ? (string) $settings['adminLanguage']
            : 'en';
        $settings['descriptionTemplate'] = trim((string) ($settings['descriptionTemplate'] ?? $defaults['descriptionTemplate']));
        if ($settings['descriptionTemplate'] === '') {
            $settings['descriptionTemplate'] = $defaults['descriptionTemplate'];
        }
        $settings['descriptionTemplateZh'] = trim((string) ($settings['descriptionTemplateZh'] ?? $defaults['descriptionTemplateZh']));
        if ($settings['descriptionTemplateZh'] === '') {
            $settings['descriptionTemplateZh'] = $defaults['descriptionTemplateZh'];
        }

        $rules = [];
        $storedRules = isset($stored['rules']) && is_array($stored['rules']) ? $stored['rules'] : [];
        foreach ($storedRules as $gateway => $rule) {
            $gateway = peakrackGatewayFeesNormalizeGateway((string) $gateway);
            if ($gateway === '' || !is_array($rule)) {
                continue;
            }

            $rules[$gateway] = peakrackGatewayFeesNormalizeRule($gateway, $rule);
        }

        foreach (peakrackGatewayFeesActiveGateways() as $gateway => $gatewayInfo) {
            if (!isset($rules[$gateway])) {
                $rules[$gateway] = peakrackGatewayFeesNormalizeRule($gateway, [
                    'label' => $gatewayInfo['label'] ?: $gateway,
                    'enabled' => false,
                ]);
            } elseif (($rules[$gateway]['label'] ?? '') === '') {
                $rules[$gateway]['label'] = $gatewayInfo['label'] ?: $gateway;
            }
        }

        ksort($rules);
        $settings['rules'] = $rules;

        return $settings;
    }
}

if (!function_exists('peakrackGatewayFeesNormalizeRule')) {
    function peakrackGatewayFeesNormalizeRule(string $gateway, array $rule): array
    {
        $mode = (string) ($rule['countriesMode'] ?? 'all');
        if (!in_array($mode, ['all', 'allow', 'block'], true)) {
            $mode = 'all';
        }

        $calculation = (string) ($rule['calculation'] ?? 'standard');
        if (!in_array($calculation, ['standard', 'gross_up'], true)) {
            $calculation = 'standard';
        }

        return [
            'enabled' => peakrackGatewayFeesBool($rule['enabled'] ?? false),
            'label' => trim((string) ($rule['label'] ?? $gateway)),
            'percent' => peakrackGatewayFeesClampFloat($rule['percent'] ?? 0, 0, 99.99, 0),
            'fixed' => peakrackGatewayFeesClampFloat($rule['fixed'] ?? 0, 0, 1000000, 0),
            'minimum' => peakrackGatewayFeesClampFloat($rule['minimum'] ?? 0, 0, 1000000, 0),
            'calculation' => $calculation,
            'countriesMode' => $mode,
            'countries' => peakrackGatewayFeesNormalizeList($rule['countries'] ?? '', true),
            'showDetails' => peakrackGatewayFeesBool($rule['showDetails'] ?? true),
        ];
    }
}

if (!function_exists('peakrackGatewayFeesActiveGateways')) {
    function peakrackGatewayFeesActiveGateways(): array
    {
        try {
            $rows = Capsule::table('tblpaymentgateways')
                ->select(['gateway', 'setting', 'value'])
                ->orderBy('gateway', 'asc')
                ->get();
        } catch (Throwable $e) {
            return [];
        }

        $gateways = [];
        foreach ($rows as $row) {
            $gateway = peakrackGatewayFeesNormalizeGateway((string) ($row->gateway ?? ''));
            if ($gateway === '') {
                continue;
            }

            if (!isset($gateways[$gateway])) {
                $gateways[$gateway] = [
                    'gateway' => $gateway,
                    'label' => '',
                ];
            }

            $setting = strtolower((string) ($row->setting ?? ''));
            if (in_array($setting, ['name', 'displayname', 'friendlyname', 'visiblename'], true)) {
                $label = trim((string) ($row->value ?? ''));
                if ($label !== '') {
                    $gateways[$gateway]['label'] = $label;
                }
            }
        }

        return $gateways;
    }
}

if (!function_exists('peakrackGatewayFeesApplyToInvoice')) {
    function peakrackGatewayFeesApplyToInvoice(int $invoiceId, string $reason = 'hook'): void
    {
        static $running = [];

        if ($invoiceId <= 0 || isset($running[$invoiceId])) {
            return;
        }

        $running[$invoiceId] = true;

        try {
            $settings = peakrackGatewayFeesLoadSettings();
            if (!$settings['enabled']) {
                unset($running[$invoiceId]);
                return;
            }

            $invoice = Capsule::table('tblinvoices')->where('id', $invoiceId)->first();
            if (!$invoice) {
                unset($running[$invoiceId]);
                return;
            }

            $status = strtolower((string) ($invoice->status ?? ''));
            if (!in_array($status, ['unpaid', 'payment pending'], true)) {
                unset($running[$invoiceId]);
                return;
            }

            $gateway = peakrackGatewayFeesNormalizeGateway((string) ($invoice->paymentmethod ?? ''));
            $rule = peakrackGatewayFeesRuleForGateway($settings, $gateway);
            $client = peakrackGatewayFeesClient((int) ($invoice->userid ?? 0));

            if (!$rule || !$rule['enabled']) {
                peakrackGatewayFeesRemoveInvoiceFee($invoiceId, true);
                unset($running[$invoiceId]);
                return;
            }

            if ($settings['skipTaxExemptClients'] && !empty($client) && !empty($client['taxexempt'])) {
                peakrackGatewayFeesRemoveInvoiceFee($invoiceId, true);
                peakrackGatewayFeesLog('info', 'Skipped gateway fee for tax-exempt client', $invoiceId, (int) ($invoice->userid ?? 0), $gateway);
                unset($running[$invoiceId]);
                return;
            }

            $base = peakrackGatewayFeesInvoiceBaseAmount($invoiceId);
            $fee = peakrackGatewayFeesCalculateFee($base, $rule);
            if ($fee <= 0) {
                peakrackGatewayFeesRemoveInvoiceFee($invoiceId, true);
                unset($running[$invoiceId]);
                return;
            }

            peakrackGatewayFeesUpsertInvoiceFee($invoice, $gateway, $rule, $settings, $fee);
            peakrackGatewayFeesUpdateInvoiceTotal($invoiceId);
            peakrackGatewayFeesLog('info', 'Applied gateway fee during ' . $reason, $invoiceId, (int) ($invoice->userid ?? 0), $gateway, [
                'base' => $base,
                'fee' => $fee,
            ]);
        } catch (Throwable $e) {
            peakrackGatewayFeesLog('error', 'Gateway fee application failed: ' . $e->getMessage(), $invoiceId);
        }

        unset($running[$invoiceId]);
    }
}

if (!function_exists('peakrackGatewayFeesInvoiceBaseAmount')) {
    function peakrackGatewayFeesInvoiceBaseAmount(int $invoiceId): float
    {
        $items = Capsule::table('tblinvoiceitems')
            ->where('invoiceid', $invoiceId)
            ->where(function ($query): void {
                $query->whereNull('type')
                    ->orWhere('type', '!=', PRGF_FEE_TYPE);
            })
            ->get();

        $base = 0.0;
        foreach ($items as $item) {
            $base += (float) ($item->amount ?? 0);
        }

        return round(max(0.0, $base), 2);
    }
}

if (!function_exists('peakrackGatewayFeesCalculateFee')) {
    function peakrackGatewayFeesCalculateFee(float $base, array $rule): float
    {
        if ($base <= 0 || $base < (float) $rule['minimum']) {
            return 0.0;
        }

        $percent = (float) $rule['percent'];
        $fixed = (float) $rule['fixed'];

        if ($percent <= 0 && $fixed <= 0) {
            return 0.0;
        }

        if (($rule['calculation'] ?? 'standard') === 'gross_up' && $percent > 0 && $percent < 100) {
            $fee = (($base + $fixed) / (1 - ($percent / 100))) - $base;
        } else {
            $fee = ($base * ($percent / 100)) + $fixed;
        }

        return round(max(0.0, $fee), 2);
    }
}

if (!function_exists('peakrackGatewayFeesUpsertInvoiceFee')) {
    function peakrackGatewayFeesUpsertInvoiceFee(object $invoice, string $gateway, array $rule, array $settings, float $fee): void
    {
        $invoiceId = (int) $invoice->id;
        $clientId = (int) ($invoice->userid ?? 0);
        $description = peakrackGatewayFeesFeeDescription($gateway, $rule, $settings, $clientId);
        $taxed = !empty($settings['taxed']) ? 1 : 0;
        $existing = Capsule::table('tblinvoiceitems')
            ->where('invoiceid', $invoiceId)
            ->where('type', PRGF_FEE_TYPE)
            ->first();

        $payload = [
            'userid' => $clientId,
            'invoiceid' => $invoiceId,
            'type' => PRGF_FEE_TYPE,
            'relid' => 0,
            'description' => $description,
            'amount' => $fee,
            'taxed' => $taxed,
            'duedate' => peakrackGatewayFeesInvoiceItemDueDate($invoice),
            'paymentmethod' => $gateway,
        ];

        if ($existing) {
            Capsule::table('tblinvoiceitems')->where('id', (int) $existing->id)->update($payload);
            return;
        }

        Capsule::table('tblinvoiceitems')->insert($payload);
    }
}

if (!function_exists('peakrackGatewayFeesRemoveInvoiceFee')) {
    function peakrackGatewayFeesRemoveInvoiceFee(int $invoiceId, bool $updateTotal = false): void
    {
        if ($invoiceId <= 0) {
            return;
        }

        $deleted = Capsule::table('tblinvoiceitems')
            ->where('invoiceid', $invoiceId)
            ->where('type', PRGF_FEE_TYPE)
            ->delete();

        if ($deleted && $updateTotal) {
            peakrackGatewayFeesUpdateInvoiceTotal($invoiceId);
        }
    }
}

if (!function_exists('peakrackGatewayFeesUpdateInvoiceTotal')) {
    function peakrackGatewayFeesUpdateInvoiceTotal(int $invoiceId): void
    {
        if ($invoiceId <= 0) {
            return;
        }

        peakrackGatewayFeesEnsureInvoiceFunctions();
        if (function_exists('updateInvoiceTotal')) {
            updateInvoiceTotal($invoiceId);
        }
    }
}

if (!function_exists('peakrackGatewayFeesEnsureInvoiceFunctions')) {
    function peakrackGatewayFeesEnsureInvoiceFunctions(): void
    {
        if (function_exists('updateInvoiceTotal')) {
            return;
        }

        if (!defined('ROOTDIR')) {
            return;
        }

        $file = rtrim((string) ROOTDIR, '/\\') . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'invoicefunctions.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
}

if (!function_exists('peakrackGatewayFeesInvoiceItemDueDate')) {
    function peakrackGatewayFeesInvoiceItemDueDate(object $invoice): string
    {
        $dueDate = trim((string) ($invoice->duedate ?? ''));
        if ($dueDate !== '' && $dueDate !== '0000-00-00') {
            return substr($dueDate, 0, 10);
        }

        return date('Y-m-d');
    }
}

if (!function_exists('peakrackGatewayFeesRuleForGateway')) {
    function peakrackGatewayFeesRuleForGateway(array $settings, string $gateway): ?array
    {
        $gateway = peakrackGatewayFeesNormalizeGateway($gateway);
        if ($gateway === '' || empty($settings['rules'][$gateway]) || !is_array($settings['rules'][$gateway])) {
            return null;
        }

        return $settings['rules'][$gateway];
    }
}

if (!function_exists('peakrackGatewayFeesFeeDescription')) {
    function peakrackGatewayFeesFeeDescription(string $gateway, array $rule, array $settings, int $clientId = 0): string
    {
        $label = trim((string) ($rule['label'] ?? $gateway));
        if ($label === '') {
            $label = $gateway;
        }

        $template = peakrackGatewayFeesClientPrefersChinese($clientId)
            ? (string) ($settings['descriptionTemplateZh'] ?? '')
            : (string) ($settings['descriptionTemplate'] ?? '');
        if (trim($template) === '') {
            $template = 'Payment Gateway Fee ({gateway})';
        }

        $description = str_replace(
            ['{gateway}', '{module}'],
            [$label, $gateway],
            $template
        );

        return trim($description) !== '' ? trim($description) : 'Payment Gateway Fee';
    }
}

if (!function_exists('peakrackGatewayFeesClientPrefersChinese')) {
    function peakrackGatewayFeesClientPrefersChinese(int $clientId): bool
    {
        $language = '';

        if ($clientId > 0) {
            try {
                $language = (string) Capsule::table('tblclients')->where('id', $clientId)->value('language');
            } catch (Throwable $e) {
                $language = '';
            }
        }

        if ($language === '' && !empty($_SESSION['Language'])) {
            $language = (string) $_SESSION['Language'];
        }

        $language = strtolower(trim($language));
        return $language !== '' && (
            strpos($language, 'chinese') !== false
            || strpos($language, 'zh') !== false
            || strpos($language, 'cn') !== false
        );
    }
}

if (!function_exists('peakrackGatewayFeesClient')) {
    function peakrackGatewayFeesClient(int $clientId): array
    {
        if ($clientId <= 0) {
            return [];
        }

        try {
            $client = Capsule::table('tblclients')->where('id', $clientId)->first();
        } catch (Throwable $e) {
            return [];
        }

        if (!$client) {
            return [];
        }

        return [
            'id' => (int) ($client->id ?? 0),
            'country' => strtoupper((string) ($client->country ?? '')),
            'taxexempt' => !empty($client->taxexempt),
        ];
    }
}

if (!function_exists('peakrackGatewayFeesCheckoutValidation')) {
    function peakrackGatewayFeesCheckoutValidation(array $vars): array
    {
        $settings = peakrackGatewayFeesLoadSettings();
        if (!$settings['enabled'] || !$settings['allocationEnabled'] || !$settings['checkoutAllocationValidation']) {
            return [];
        }

        $gateway = peakrackGatewayFeesNormalizeGateway((string) ($vars['paymentmethod'] ?? ($_POST['paymentmethod'] ?? '')));
        $country = peakrackGatewayFeesCheckoutCountry($vars);
        if ($gateway === '' || peakrackGatewayFeesIsGatewayAllowed($settings, $gateway, $country)) {
            return [];
        }

        $clientId = (int) ($vars['clientId'] ?? ($_SESSION['uid'] ?? 0));
        $rule = peakrackGatewayFeesRuleForGateway($settings, $gateway);
        $label = $rule ? (string) ($rule['label'] ?? $gateway) : $gateway;
        if (peakrackGatewayFeesClientPrefersChinese($clientId)) {
            return ['所选支付方式不适用于你的账单国家/地区：' . $label . '。'];
        }

        return ['The selected payment method is not available for your billing country: ' . $label . '.'];
    }
}

if (!function_exists('peakrackGatewayFeesCheckoutCountry')) {
    function peakrackGatewayFeesCheckoutCountry(array $vars): string
    {
        $country = strtoupper(trim((string) ($vars['country'] ?? ($_POST['country'] ?? ''))));
        if ($country !== '') {
            return $country;
        }

        $clientId = (int) ($vars['clientId'] ?? ($_SESSION['uid'] ?? 0));
        $client = peakrackGatewayFeesClient($clientId);
        return strtoupper((string) ($client['country'] ?? ''));
    }
}

if (!function_exists('peakrackGatewayFeesIsGatewayAllowed')) {
    function peakrackGatewayFeesIsGatewayAllowed(array $settings, string $gateway, string $country): bool
    {
        if (!$settings['allocationEnabled']) {
            return true;
        }

        $rule = peakrackGatewayFeesRuleForGateway($settings, $gateway);
        if (!$rule) {
            return true;
        }

        $mode = (string) ($rule['countriesMode'] ?? 'all');
        $countries = $rule['countries'] ?? [];
        $country = strtoupper(trim($country));

        if ($mode === 'all' || empty($countries) || $country === '') {
            return true;
        }

        $listed = in_array($country, $countries, true);
        return $mode === 'allow' ? $listed : !$listed;
    }
}

if (!function_exists('peakrackGatewayFeesCheckoutOutput')) {
    function peakrackGatewayFeesCheckoutOutput(array $vars): string
    {
        $settings = peakrackGatewayFeesLoadSettings();
        if (!$settings['enabled'] || (!$settings['checkoutDetails'] && !$settings['allocationEnabled'])) {
            return '';
        }

        return peakrackGatewayFeesNoticeHtml($settings, peakrackGatewayFeesCheckoutCountry($vars), true);
    }
}

if (!function_exists('peakrackGatewayFeesInvoiceOutput')) {
    function peakrackGatewayFeesInvoiceOutput(array $vars): string
    {
        $settings = peakrackGatewayFeesLoadSettings();
        if (!$settings['enabled'] || (!$settings['invoiceDetails'] && !$settings['allocationEnabled'])) {
            return '';
        }

        $invoiceId = (int) ($_GET['id'] ?? ($vars['invoiceid'] ?? 0));
        if ($invoiceId > 0) {
            peakrackGatewayFeesApplyToInvoice($invoiceId, 'invoice view');
        }

        $clientId = (int) ($_SESSION['uid'] ?? 0);
        $client = peakrackGatewayFeesClient($clientId);
        return peakrackGatewayFeesNoticeHtml($settings, (string) ($client['country'] ?? ''), false);
    }
}

if (!function_exists('peakrackGatewayFeesNoticeHtml')) {
    function peakrackGatewayFeesNoticeHtml(array $settings, string $country, bool $checkout): string
    {
        $rules = [];
        foreach ($settings['rules'] as $gateway => $rule) {
            if (empty($rule['enabled']) && ($rule['countriesMode'] ?? 'all') === 'all') {
                continue;
            }

            $rules[$gateway] = [
                'label' => (string) ($rule['label'] ?? $gateway),
                'enabled' => !empty($rule['enabled']),
                'percent' => (float) ($rule['percent'] ?? 0),
                'fixed' => (float) ($rule['fixed'] ?? 0),
                'minimum' => (float) ($rule['minimum'] ?? 0),
                'calculation' => (string) ($rule['calculation'] ?? 'standard'),
                'countriesMode' => (string) ($rule['countriesMode'] ?? 'all'),
                'countries' => array_values($rule['countries'] ?? []),
                'showDetails' => !empty($rule['showDetails']),
            ];
        }

        if (empty($rules)) {
            return '';
        }

        $lines = [];
        if ($checkout && $settings['checkoutDetails']) {
            foreach ($rules as $gateway => $rule) {
                if (!$rule['enabled'] || !$rule['showDetails']) {
                    continue;
                }

                $parts = [];
                if ($rule['percent'] > 0) {
                    $parts[] = rtrim(rtrim(number_format((float) $rule['percent'], 2, '.', ''), '0'), '.') . '%';
                }
                if ($rule['fixed'] > 0) {
                    $parts[] = number_format((float) $rule['fixed'], 2, '.', '');
                }
                if (empty($parts)) {
                    continue;
                }

                $lines[] = peakrackGatewayFeesEscape($rule['label']) . ': ' . peakrackGatewayFeesEscape(implode(' + ', $parts));
            }
        }

        $data = [
            'country' => strtoupper($country),
            'allocationEnabled' => !empty($settings['allocationEnabled']),
            'rules' => $rules,
        ];

        $html = '';
        if (!empty($lines)) {
            $html .= '<div class="alert alert-info peakrack-gateway-fees-notice" style="margin-top:12px;">';
            $heading = peakrackGatewayFeesClientPrefersChinese((int) ($_SESSION['uid'] ?? 0)) ? '支付方式手续费' : 'Payment method fees';
            $html .= '<strong>' . peakrackGatewayFeesEscape($heading) . '</strong><br>' . implode('<br>', $lines);
            $html .= '</div>';
        }

        $html .= '<script>window.PeakRackGatewayFees=' . peakrackGatewayFeesJsonEncode($data) . ';';
        $html .= peakrackGatewayFeesAllocatorScript();
        $html .= '</script>';

        return $html;
    }
}

if (!function_exists('peakrackGatewayFeesAllocatorScript')) {
    function peakrackGatewayFeesAllocatorScript(): string
    {
        return <<<'JS'
(function () {
    var config = window.PeakRackGatewayFees || {};
    if (!config.allocationEnabled || !config.rules) {
        return;
    }
    function normalize(value) {
        return String(value || '').toLowerCase().replace(/[^a-z0-9_]/g, '');
    }
    function country() {
        var field = document.querySelector('[name="country"]');
        return String((field && field.value) || config.country || '').toUpperCase();
    }
    function allowed(gateway, currentCountry) {
        var rule = config.rules[gateway] || config.rules[normalize(gateway)];
        if (!rule) {
            return true;
        }
        if (!rule.countriesMode || rule.countriesMode === 'all' || !rule.countries || !rule.countries.length || !currentCountry) {
            return true;
        }
        var listed = rule.countries.indexOf(currentCountry) !== -1;
        return rule.countriesMode === 'allow' ? listed : !listed;
    }
    function wrapperFor(input) {
        return input.closest('.payment-method, .gateway, .form-check, .radio, label, li, tr, .panel') || input.parentElement;
    }
    function apply() {
        var currentCountry = country();
        var fields = Array.prototype.slice.call(document.querySelectorAll('input[name="paymentmethod"], input[name="gateway"]'));
        var firstAllowed = null;
        var disallowedChecked = null;
        fields.forEach(function (field) {
            var gateway = normalize(field.value);
            var ok = allowed(gateway, currentCountry);
            var wrapper = wrapperFor(field);
            if (wrapper) {
                wrapper.style.display = ok ? '' : 'none';
            }
            field.disabled = !ok;
            if (ok && !firstAllowed) {
                firstAllowed = field;
            }
            if (!ok && field.checked) {
                disallowedChecked = field;
            }
        });
        if (disallowedChecked && firstAllowed) {
            firstAllowed.disabled = false;
            firstAllowed.checked = true;
            firstAllowed.dispatchEvent(new Event('change', { bubbles: true }));
        }
        Array.prototype.slice.call(document.querySelectorAll('select[name="paymentmethod"], select[name="gateway"]')).forEach(function (select) {
            var firstAllowedValue = '';
            Array.prototype.slice.call(select.options).forEach(function (option) {
                var ok = allowed(normalize(option.value), currentCountry);
                option.hidden = !ok;
                option.disabled = !ok;
                if (ok && !firstAllowedValue) {
                    firstAllowedValue = option.value;
                }
            });
            if (!allowed(normalize(select.value), currentCountry) && firstAllowedValue) {
                select.value = firstAllowedValue;
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    }
    document.addEventListener('change', function (event) {
        if (event.target && event.target.name === 'country') {
            apply();
        }
    });
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', apply);
    } else {
        apply();
    }
})();
JS;
    }
}

if (!function_exists('peakrackGatewayFeesLog')) {
    function peakrackGatewayFeesLog(string $level, string $message, int $invoiceId = 0, int $clientId = 0, string $gateway = '', array $context = []): void
    {
        try {
            Capsule::table(PRGF_LOGS_TABLE)->insert([
                'invoice_id' => $invoiceId > 0 ? $invoiceId : null,
                'client_id' => $clientId > 0 ? $clientId : null,
                'gateway' => $gateway !== '' ? $gateway : null,
                'level' => $level,
                'message' => substr($message, 0, 255),
                'context' => !empty($context) ? peakrackGatewayFeesJsonEncode($context) : null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (Throwable $e) {
            // Logging must never block checkout or invoice rendering.
        }

        $settings = peakrackGatewayFeesLoadSettings();
        if (!empty($settings['activityLog']) && function_exists('logActivity')) {
            logActivity('PeakRack Gateway Fees: ' . $message);
        }
    }
}

if (!function_exists('peakrackGatewayFeesCleanupLogs')) {
    function peakrackGatewayFeesCleanupLogs(array $settings): array
    {
        $deleted = [
            'age' => 0,
            'count' => 0,
        ];

        $retentionDays = peakrackGatewayFeesClampInt($settings['logRetentionDays'] ?? 180, 0, 3650, 180);
        if ($retentionDays > 0) {
            $cutoff = date('Y-m-d H:i:s', time() - ($retentionDays * 86400));
            $deleted['age'] = (int) Capsule::table(PRGF_LOGS_TABLE)
                ->where('created_at', '<', $cutoff)
                ->delete();
        }

        $maxLogs = peakrackGatewayFeesClampInt($settings['maxLogs'] ?? 5000, 0, 1000000, 5000);
        if ($maxLogs > 0) {
            $threshold = Capsule::table(PRGF_LOGS_TABLE)
                ->orderBy('id', 'desc')
                ->skip($maxLogs)
                ->take(1)
                ->value('id');

            if ($threshold) {
                $deleted['count'] = (int) Capsule::table(PRGF_LOGS_TABLE)
                    ->where('id', '<=', (int) $threshold)
                    ->delete();
            }
        }

        return $deleted;
    }
}

if (!function_exists('peakrackGatewayFeesNormalizeGateway')) {
    function peakrackGatewayFeesNormalizeGateway(string $gateway): string
    {
        $gateway = strtolower(trim($gateway));
        return preg_replace('/[^a-z0-9_]/', '', $gateway) ?: '';
    }
}

if (!function_exists('peakrackGatewayFeesNormalizeList')) {
    function peakrackGatewayFeesNormalizeList($value, bool $uppercase = false): array
    {
        if (is_array($value)) {
            $parts = $value;
        } else {
            $parts = preg_split('/[\s,;]+/', (string) $value) ?: [];
        }

        $items = [];
        foreach ($parts as $part) {
            $item = trim((string) $part);
            if ($uppercase) {
                $item = strtoupper($item);
            } else {
                $item = strtolower($item);
            }
            $item = preg_replace($uppercase ? '/[^A-Z0-9_\-]/' : '/[^a-z0-9_\-]/', '', $item) ?: '';
            if ($item !== '' && !in_array($item, $items, true)) {
                $items[] = $item;
            }
        }

        return $items;
    }
}

if (!function_exists('peakrackGatewayFeesBool')) {
    function peakrackGatewayFeesBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }
}

if (!function_exists('peakrackGatewayFeesClampFloat')) {
    function peakrackGatewayFeesClampFloat($value, float $min, float $max, float $fallback): float
    {
        if (!is_numeric($value)) {
            return $fallback;
        }

        return max($min, min($max, (float) $value));
    }
}

if (!function_exists('peakrackGatewayFeesClampInt')) {
    function peakrackGatewayFeesClampInt($value, int $min, int $max, int $fallback): int
    {
        if (!is_numeric($value)) {
            return $fallback;
        }

        return max($min, min($max, (int) $value));
    }
}

if (!function_exists('peakrackGatewayFeesJsonEncode')) {
    function peakrackGatewayFeesJsonEncode(array $data): string
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return is_string($json) ? $json : '{}';
    }
}

if (!function_exists('peakrackGatewayFeesJsonDecode')) {
    function peakrackGatewayFeesJsonDecode(?string $json, array $fallback = []): array
    {
        if (!is_string($json) || trim($json) === '') {
            return $fallback;
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : $fallback;
    }
}

if (!function_exists('peakrackGatewayFeesEscape')) {
    function peakrackGatewayFeesEscape($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

