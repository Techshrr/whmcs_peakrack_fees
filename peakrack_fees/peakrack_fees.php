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

function peakrack_fees_config(): array
{
    return [
        'name' => 'PeakRack Gateway Fees',
        'description' => 'Adds configurable payment gateway fees and country-based gateway allocation for WHMCS invoices and checkout.',
        'version' => PRGF_VERSION,
        'author' => 'PeakRack',
        'language' => 'english',
        'fields' => [],
    ];
}

function peakrack_fees_activate(): array
{
    try {
        peakrackGatewayFeesCreateTables();
        peakrackGatewayFeesSaveSettings(peakrackGatewayFeesLoadSettings());

        return [
            'status' => 'success',
            'description' => 'PeakRack Gateway Fees has been activated.',
        ];
    } catch (Throwable $e) {
        return [
            'status' => 'error',
            'description' => 'Activation failed: ' . $e->getMessage(),
        ];
    }
}

function peakrack_fees_deactivate(): array
{
    return [
        'status' => 'success',
        'description' => 'PeakRack Gateway Fees has been deactivated. Configuration and logs were kept.',
    ];
}

function peakrack_fees_upgrade($vars): void
{
    peakrackGatewayFeesCreateTables();
}

function peakrack_fees_output(array $vars): void
{
    $message = '';
    $messageType = 'success';

    try {
        peakrackGatewayFeesCreateTables();
        $settings = peakrackGatewayFeesLoadSettings();
    } catch (Throwable $e) {
        $settings = peakrackGatewayFeesDefaults();
        $message = peakrack_fees_admin_text((string) $settings['adminLanguage'], 'schema_failed') . ' ' . $e->getMessage();
        $messageType = 'danger';
    }

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        if (!peakrack_fees_verify_admin_token()) {
            $language = in_array((string) ($_POST['adminLanguage'] ?? ($settings['adminLanguage'] ?? 'en')), ['en', 'zh'], true)
                ? (string) ($_POST['adminLanguage'] ?? ($settings['adminLanguage'] ?? 'en'))
                : 'en';
            $message = peakrack_fees_admin_text($language, 'token_failed');
            $messageType = 'danger';
        } else {
            $action = (string) ($_POST['prgf_action'] ?? '');
            if ($action === 'save_settings') {
                $settings = peakrack_fees_settings_from_post($settings);
                peakrackGatewayFeesSaveSettings($settings);
                $settings = peakrackGatewayFeesLoadSettings();
                $message = peakrack_fees_admin_text((string) $settings['adminLanguage'], 'saved');
            } elseif ($action === 'import_settings') {
                $import = peakrack_fees_import_settings($settings);
                $settings = $import['settings'];
                $message = $import['message'];
                $messageType = $import['success'] ? 'success' : 'danger';
            }
        }
    }

    echo peakrack_fees_render_admin($settings, $message, $messageType);
}

function peakrack_fees_admin_text(string $language, string $key): string
{
    $texts = peakrack_fees_admin_texts($language);
    return $texts[$key] ?? $key;
}

function peakrack_fees_normalize_admin_language($language): string
{
    return in_array((string) $language, ['zh', 'en'], true) ? (string) $language : '';
}

function peakrack_fees_admin_language(array $settings = []): string
{
    $requested = peakrack_fees_normalize_admin_language($_GET['prgf_admin_lang'] ?? '');
    if ($requested !== '') {
        $_SESSION['peakrack_fees_admin_lang'] = $requested;
        if (!headers_sent()) {
            setcookie('peakrack_fees_admin_lang', $requested, time() + 31536000, '', '', false, true);
        }

        return $requested;
    }

    $sessionLanguage = peakrack_fees_normalize_admin_language($_SESSION['peakrack_fees_admin_lang'] ?? '');
    if ($sessionLanguage !== '') {
        return $sessionLanguage;
    }

    $cookieLanguage = peakrack_fees_normalize_admin_language($_COOKIE['peakrack_fees_admin_lang'] ?? '');
    if ($cookieLanguage !== '') {
        return $cookieLanguage;
    }

    return peakrack_fees_normalize_admin_language($settings['adminLanguage'] ?? '') ?: 'en';
}

function peakrack_fees_admin_url(string $language): string
{
    $params = [
        'module' => 'peakrack_fees',
        'prgf_admin_lang' => peakrack_fees_normalize_admin_language($language) ?: 'en',
    ];

    return 'addonmodules.php?' . http_build_query($params);
}

function peakrack_fees_admin_texts(string $language): array
{
    $dictionary = [
        'en' => [
            'subtitle' => 'WHMCS 9.x / PHP 8.3 addon for gateway fee calculation and country-based gateway allocation.',
            'version' => 'Version ' . PRGF_VERSION,
            'token_failed' => 'Security token validation failed. Refresh the page and try again.',
            'schema_failed' => 'Schema check failed:',
            'saved' => 'Settings saved.',
            'import_empty' => 'Import JSON is empty.',
            'import_invalid' => 'Import JSON is invalid.',
            'imported' => 'Settings imported.',
            'enable_module' => 'Enable module',
            'taxed' => 'Mark fee invoice item as taxable',
            'skip_tax_exempt' => 'Skip tax-exempt clients',
            'checkout_details' => 'Show checkout fee details',
            'invoice_details' => 'Refresh fee when invoice is viewed',
            'allocation_enabled' => 'Enable gateway allocation',
            'checkout_validation' => 'Validate allocation at checkout',
            'activity_log' => 'Mirror events to WHMCS Activity Log',
            'log_retention_days' => 'Log retention days',
            'max_logs' => 'Maximum log rows',
            'admin_language' => 'Admin language',
            'english' => 'English',
            'chinese' => 'Chinese',
            'description_template' => 'Invoice item description template',
            'description_template_en' => 'English invoice item description',
            'description_template_zh' => 'Chinese invoice item description',
            'placeholder_help' => 'Available placeholders: {gateway}, {module}',
            'gateway_rules' => 'Gateway Rules',
            'active_gateway_help' => 'Active WHMCS payment gateways are loaded automatically. After adding or enabling a new gateway in WHMCS, refresh this page and it will appear here with fees disabled by default.',
            'gateway' => 'Gateway',
            'label' => 'Label',
            'fee' => 'Fee',
            'percent' => 'Percent',
            'fixed' => 'Fixed',
            'minimum' => 'Minimum',
            'calc' => 'Calc',
            'country_mode' => 'Country Mode',
            'countries' => 'Countries',
            'notice' => 'Notice',
            'standard' => 'Standard',
            'gross_up' => 'Gross-up',
            'all' => 'All',
            'allow' => 'Allow',
            'block' => 'Block',
            'country_help' => 'Countries use ISO-3166 alpha-2 codes such as US, CA, CN. Allow mode permits only listed countries; block mode hides and rejects listed countries.',
            'save_settings' => 'Save Settings',
            'json_tools' => 'JSON Import / Export',
            'import_json' => 'Import JSON',
            'recent_logs' => 'Recent Logs',
            'no_logs' => 'No events recorded yet.',
            'logs_unavailable' => 'Logs are not available yet.',
            'time' => 'Time',
            'level' => 'Level',
            'invoice' => 'Invoice',
            'message' => 'Message',
        ],
        'zh' => [
            'subtitle' => '适用于 WHMCS 9.x / PHP 8.3 的支付网关手续费与国家/地区网关分配插件。',
            'version' => '版本 ' . PRGF_VERSION,
            'token_failed' => '安全令牌验证失败，请刷新页面后重试。',
            'schema_failed' => '数据库结构检查失败：',
            'saved' => '设置已保存。',
            'import_empty' => '导入 JSON 不能为空。',
            'import_invalid' => '导入 JSON 格式无效。',
            'imported' => '设置已导入。',
            'enable_module' => '启用模块',
            'taxed' => '手续费发票项目计税',
            'skip_tax_exempt' => '跳过免税客户',
            'checkout_details' => '结账页显示手续费说明',
            'invoice_details' => '查看发票时刷新手续费',
            'allocation_enabled' => '启用网关国家/地区分配',
            'checkout_validation' => '结账时校验网关分配',
            'activity_log' => '同步事件到 WHMCS 活动日志',
            'log_retention_days' => '日志保留天数',
            'max_logs' => '最大日志行数',
            'admin_language' => '后台语言',
            'english' => '英文',
            'chinese' => '中文',
            'description_template' => '发票项目描述模板',
            'description_template_en' => '英文发票项目描述',
            'description_template_zh' => '中文发票项目描述',
            'placeholder_help' => '可用占位符：{gateway}、{module}',
            'gateway_rules' => '网关规则',
            'active_gateway_help' => '已启用的 WHMCS 支付网关会自动读取。你在 WHMCS 新增或启用支付方式后，刷新本页就会自动出现在这里，默认不启用手续费。',
            'gateway' => '网关',
            'label' => '显示名称',
            'fee' => '启用费用',
            'percent' => '百分比',
            'fixed' => '固定金额',
            'minimum' => '最低金额',
            'calc' => '算法',
            'country_mode' => '国家/地区模式',
            'countries' => '国家/地区',
            'notice' => '显示说明',
            'standard' => '标准',
            'gross_up' => '倒推补足',
            'all' => '全部',
            'allow' => '允许',
            'block' => '屏蔽',
            'country_help' => '国家/地区使用 ISO-3166 alpha-2 代码，例如 US、CA、CN。允许模式仅允许列表内国家/地区；屏蔽模式会隐藏并拒绝列表内国家/地区。',
            'save_settings' => '保存设置',
            'json_tools' => 'JSON 导入 / 导出',
            'import_json' => '导入 JSON',
            'recent_logs' => '最近日志',
            'no_logs' => '暂无事件记录。',
            'logs_unavailable' => '日志暂不可用。',
            'time' => '时间',
            'level' => '级别',
            'invoice' => '发票',
            'message' => '消息',
        ],
    ];

    return $dictionary[in_array($language, ['en', 'zh'], true) ? $language : 'en'];
}

function peakrack_fees_verify_admin_token(): bool
{
    if (function_exists('check_token')) {
        return (bool) check_token('WHMCS.admin.default');
    }

    return true;
}

function peakrack_fees_admin_token_field(): string
{
    if (function_exists('generate_token')) {
        return '<input type="hidden" name="token" value="' . peakrackGatewayFeesEscape((string) generate_token('plain')) . '">';
    }

    return '';
}

function peakrack_fees_settings_from_post(array $current): array
{
    $settings = $current;
    $settings['enabled'] = peakrackGatewayFeesBool($_POST['enabled'] ?? false);
    $settings['taxed'] = peakrackGatewayFeesBool($_POST['taxed'] ?? false);
    $settings['skipTaxExemptClients'] = peakrackGatewayFeesBool($_POST['skipTaxExemptClients'] ?? false);
    $settings['checkoutDetails'] = peakrackGatewayFeesBool($_POST['checkoutDetails'] ?? false);
    $settings['invoiceDetails'] = peakrackGatewayFeesBool($_POST['invoiceDetails'] ?? false);
    $settings['allocationEnabled'] = peakrackGatewayFeesBool($_POST['allocationEnabled'] ?? false);
    $settings['checkoutAllocationValidation'] = peakrackGatewayFeesBool($_POST['checkoutAllocationValidation'] ?? false);
    $settings['activityLog'] = peakrackGatewayFeesBool($_POST['activityLog'] ?? false);
    $settings['logRetentionDays'] = peakrackGatewayFeesClampInt($_POST['logRetentionDays'] ?? 180, 0, 3650, 180);
    $settings['maxLogs'] = peakrackGatewayFeesClampInt($_POST['maxLogs'] ?? 5000, 0, 1000000, 5000);
    $settings['adminLanguage'] = in_array((string) ($_POST['adminLanguage'] ?? 'en'), ['en', 'zh'], true)
        ? (string) $_POST['adminLanguage']
        : 'en';
    $settings['descriptionTemplate'] = trim((string) ($_POST['descriptionTemplate'] ?? 'Payment Gateway Fee ({gateway})'));
    $settings['descriptionTemplateZh'] = trim((string) ($_POST['descriptionTemplateZh'] ?? '支付网关手续费（{gateway}）'));

    $rules = [];
    $postedRules = isset($_POST['rules']) && is_array($_POST['rules']) ? $_POST['rules'] : [];
    foreach ($postedRules as $gateway => $rule) {
        $gateway = peakrackGatewayFeesNormalizeGateway((string) $gateway);
        if ($gateway === '' || !is_array($rule)) {
            continue;
        }

        $rules[$gateway] = peakrackGatewayFeesNormalizeRule($gateway, [
            'enabled' => $rule['enabled'] ?? false,
            'label' => $rule['label'] ?? $gateway,
            'percent' => $rule['percent'] ?? 0,
            'fixed' => $rule['fixed'] ?? 0,
            'minimum' => $rule['minimum'] ?? 0,
            'calculation' => $rule['calculation'] ?? 'standard',
            'countriesMode' => $rule['countriesMode'] ?? 'all',
            'countries' => $rule['countries'] ?? '',
            'showDetails' => $rule['showDetails'] ?? false,
        ]);
    }

    $settings['rules'] = $rules;
    return peakrackGatewayFeesMergeSettings(peakrackGatewayFeesDefaults(), $settings);
}

function peakrack_fees_import_settings(array $current): array
{
    $json = trim((string) ($_POST['settingsJson'] ?? ''));
    if ($json === '') {
        $language = (string) ($current['adminLanguage'] ?? 'en');
        return [
            'success' => false,
            'message' => peakrack_fees_admin_text($language, 'import_empty'),
            'settings' => $current,
        ];
    }

    $decoded = peakrackGatewayFeesJsonDecode($json, []);
    if (empty($decoded)) {
        $language = (string) ($current['adminLanguage'] ?? 'en');
        return [
            'success' => false,
            'message' => peakrack_fees_admin_text($language, 'import_invalid'),
            'settings' => $current,
        ];
    }

    $settings = peakrackGatewayFeesMergeSettings(peakrackGatewayFeesDefaults(), $decoded);
    peakrackGatewayFeesSaveSettings($settings);

    return [
        'success' => true,
        'message' => peakrack_fees_admin_text((string) $settings['adminLanguage'], 'imported'),
        'settings' => peakrackGatewayFeesLoadSettings(),
    ];
}

function peakrack_fees_render_admin(array $settings, string $message, string $messageType): string
{
    $language = peakrack_fees_admin_language($settings);
    $t = peakrack_fees_admin_texts($language);
    $token = peakrack_fees_admin_token_field();
    $languageInput = peakrackGatewayFeesEscape($language);
    $zhUrl = peakrackGatewayFeesEscape(peakrack_fees_admin_url('zh'));
    $enUrl = peakrackGatewayFeesEscape(peakrack_fees_admin_url('en'));
    $zhActive = $language === 'zh' ? ' active' : '';
    $enActive = $language === 'en' ? ' active' : '';
    $json = peakrackGatewayFeesEscape(peakrackGatewayFeesJsonEncode($settings));
    $logs = peakrack_fees_recent_logs($t);
    $rows = '';

    foreach ($settings['rules'] as $gateway => $rule) {
        $rows .= peakrack_fees_rule_row($gateway, $rule, $t);
    }

    $messageHtml = $message !== ''
        ? '<div class="alert alert-' . peakrackGatewayFeesEscape($messageType) . '">' . peakrackGatewayFeesEscape($message) . '</div>'
        : '';

    $enabledChecked = !empty($settings['enabled']) ? ' checked' : '';
    $taxedChecked = !empty($settings['taxed']) ? ' checked' : '';
    $skipTaxExemptChecked = !empty($settings['skipTaxExemptClients']) ? ' checked' : '';
    $checkoutDetailsChecked = !empty($settings['checkoutDetails']) ? ' checked' : '';
    $invoiceDetailsChecked = !empty($settings['invoiceDetails']) ? ' checked' : '';
    $allocationEnabledChecked = !empty($settings['allocationEnabled']) ? ' checked' : '';
    $checkoutAllocationValidationChecked = !empty($settings['checkoutAllocationValidation']) ? ' checked' : '';
    $activityLogChecked = !empty($settings['activityLog']) ? ' checked' : '';
    $logRetentionDays = peakrackGatewayFeesEscape((string) ($settings['logRetentionDays'] ?? 180));
    $maxLogs = peakrackGatewayFeesEscape((string) ($settings['maxLogs'] ?? 5000));
    $descriptionTemplate = peakrackGatewayFeesEscape((string) ($settings['descriptionTemplate'] ?? 'Payment Gateway Fee ({gateway})'));
    $descriptionTemplateZh = peakrackGatewayFeesEscape((string) ($settings['descriptionTemplateZh'] ?? '支付网关手续费（{gateway}）'));
    $subtitle = peakrackGatewayFeesEscape($t['subtitle']);
    $enableModule = peakrackGatewayFeesEscape($t['enable_module']);
    $taxed = peakrackGatewayFeesEscape($t['taxed']);
    $skipTaxExempt = peakrackGatewayFeesEscape($t['skip_tax_exempt']);
    $checkoutDetails = peakrackGatewayFeesEscape($t['checkout_details']);
    $invoiceDetails = peakrackGatewayFeesEscape($t['invoice_details']);
    $allocationEnabled = peakrackGatewayFeesEscape($t['allocation_enabled']);
    $checkoutValidation = peakrackGatewayFeesEscape($t['checkout_validation']);
    $activityLog = peakrackGatewayFeesEscape($t['activity_log']);
    $logRetentionDaysLabel = peakrackGatewayFeesEscape($t['log_retention_days']);
    $maxLogsLabel = peakrackGatewayFeesEscape($t['max_logs']);
    $english = peakrackGatewayFeesEscape($t['english']);
    $chinese = peakrackGatewayFeesEscape($t['chinese']);
    $version = peakrackGatewayFeesEscape($t['version']);
    $descriptionTemplateLabel = peakrackGatewayFeesEscape($t['description_template']);
    $descriptionTemplateEnLabel = peakrackGatewayFeesEscape($t['description_template_en']);
    $descriptionTemplateZhLabel = peakrackGatewayFeesEscape($t['description_template_zh']);
    $placeholderHelp = peakrackGatewayFeesEscape($t['placeholder_help']);
    $gatewayRules = peakrackGatewayFeesEscape($t['gateway_rules']);
    $activeGatewayHelp = peakrackGatewayFeesEscape($t['active_gateway_help']);
    $gatewayHeader = peakrackGatewayFeesEscape($t['gateway']);
    $labelHeader = peakrackGatewayFeesEscape($t['label']);
    $feeHeader = peakrackGatewayFeesEscape($t['fee']);
    $percentHeader = peakrackGatewayFeesEscape($t['percent']);
    $fixedHeader = peakrackGatewayFeesEscape($t['fixed']);
    $minimumHeader = peakrackGatewayFeesEscape($t['minimum']);
    $calcHeader = peakrackGatewayFeesEscape($t['calc']);
    $countryModeHeader = peakrackGatewayFeesEscape($t['country_mode']);
    $countriesHeader = peakrackGatewayFeesEscape($t['countries']);
    $noticeHeader = peakrackGatewayFeesEscape($t['notice']);
    $countryHelp = peakrackGatewayFeesEscape($t['country_help']);
    $saveSettings = peakrackGatewayFeesEscape($t['save_settings']);
    $jsonTools = peakrackGatewayFeesEscape($t['json_tools']);
    $importJson = peakrackGatewayFeesEscape($t['import_json']);
    $recentLogs = peakrackGatewayFeesEscape($t['recent_logs']);

    return <<<HTML
<style>
.prgf-wrap { max-width: 1320px; }
.prgf-hero { background:#0f172a; color:#fff; border-radius:6px; padding:24px 28px; margin:0 0 18px; display:flex; justify-content:space-between; gap:24px; align-items:flex-start; }
.prgf-hero h2 { margin:0 0 10px; font-size:24px; color:#fff; }
.prgf-hero p { margin:0; color:#dbe7ff; max-width:920px; font-size:15px; line-height:1.55; }
.prgf-hero-actions { display:flex; flex-direction:column; align-items:flex-end; gap:10px; flex:0 0 auto; }
.prgf-badge { border:1px solid rgba(96,165,250,.65); color:#bfdbfe; border-radius:18px; padding:4px 12px; font-weight:600; }
.prgf-lang { display:inline-flex; border:1px solid rgba(255,255,255,.28); border-radius:6px; overflow:hidden; }
.prgf-lang a { color:#dbeafe; padding:10px 18px; min-width:72px; text-align:center; font-weight:600; text-decoration:none; background:rgba(255,255,255,.04); }
.prgf-lang a:hover { color:#fff; text-decoration:none; background:rgba(255,255,255,.12); }
.prgf-lang a.active { background:#2563eb; color:#fff; }
.prgf-card { background:#fff; border:1px solid #d7dce2; border-radius:6px; padding:18px; margin:0 0 18px; }
.prgf-card h2 { margin:0 0 4px; font-size:22px; }
.prgf-card h3 { margin:0 0 14px; font-size:16px; }
.prgf-subtitle { color:#5f6b7a; margin:0 0 18px; }
.prgf-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px 18px; }
.prgf-check { display:flex; align-items:flex-start; gap:8px; font-weight:400; }
.prgf-check input { margin-top:2px; }
.prgf-template-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px 18px; margin-top:14px; }
.prgf-table { table-layout:fixed; min-width:1180px; }
.prgf-table th { white-space:nowrap; vertical-align:middle !important; }
.prgf-table td { vertical-align:middle !important; }
.prgf-table input, .prgf-table select { min-width:0; width:100%; }
.prgf-table input[type="checkbox"] { width:auto; min-width:0; margin:0; }
.prgf-table .prgf-gateway { font-family:monospace; white-space:nowrap; }
.prgf-table .prgf-col-gateway { width:118px; }
.prgf-table .prgf-col-label { width:190px; }
.prgf-table .prgf-col-check { width:82px; text-align:center; }
.prgf-table .prgf-col-number { width:130px; }
.prgf-table .prgf-col-calc { width:120px; }
.prgf-table .prgf-col-country-mode { width:145px; }
.prgf-table .prgf-col-countries { width:170px; }
.prgf-table .prgf-check-cell { text-align:center; }
.prgf-json { min-height:220px; font-family:monospace; }
.prgf-muted { color:#6b7280; font-size:12px; margin-top:4px; }
@media (max-width: 900px) { .prgf-hero { flex-direction:column; } .prgf-hero-actions { align-items:flex-start; } .prgf-grid, .prgf-template-grid { grid-template-columns:1fr; } .prgf-table { display:block; overflow-x:auto; } }
</style>
<div class="prgf-wrap">
    <div class="prgf-hero">
        <div>
            <h2>PeakRack Gateway Fees</h2>
            <p>{$subtitle}</p>
        </div>
        <div class="prgf-hero-actions">
            <span class="prgf-badge">{$version}</span>
            <div class="prgf-lang" aria-label="{$chinese} / {$english}">
                <a class="{$zhActive}" href="{$zhUrl}">中文</a>
                <a class="{$enActive}" href="{$enUrl}">English</a>
            </div>
        </div>
    </div>
    <div class="prgf-card">
        {$messageHtml}
        <form method="post">
            {$token}
            <input type="hidden" name="prgf_action" value="save_settings">
            <input type="hidden" name="adminLanguage" value="{$languageInput}">
            <div class="prgf-grid">
                <label class="prgf-check"><input type="checkbox" name="enabled"{$enabledChecked}> {$enableModule}</label>
                <label class="prgf-check"><input type="checkbox" name="taxed"{$taxedChecked}> {$taxed}</label>
                <label class="prgf-check"><input type="checkbox" name="skipTaxExemptClients"{$skipTaxExemptChecked}> {$skipTaxExempt}</label>
                <label class="prgf-check"><input type="checkbox" name="checkoutDetails"{$checkoutDetailsChecked}> {$checkoutDetails}</label>
                <label class="prgf-check"><input type="checkbox" name="invoiceDetails"{$invoiceDetailsChecked}> {$invoiceDetails}</label>
                <label class="prgf-check"><input type="checkbox" name="allocationEnabled"{$allocationEnabledChecked}> {$allocationEnabled}</label>
                <label class="prgf-check"><input type="checkbox" name="checkoutAllocationValidation"{$checkoutAllocationValidationChecked}> {$checkoutValidation}</label>
                <label class="prgf-check"><input type="checkbox" name="activityLog"{$activityLogChecked}> {$activityLog}</label>
                <label>{$logRetentionDaysLabel}
                    <input class="form-control" name="logRetentionDays" value="{$logRetentionDays}">
                </label>
                <label>{$maxLogsLabel}
                    <input class="form-control" name="maxLogs" value="{$maxLogs}">
                </label>
            </div>
            <div class="prgf-template-grid">
                <label>{$descriptionTemplateEnLabel}
                    <input class="form-control" name="descriptionTemplate" value="{$descriptionTemplate}">
                </label>
                <label>{$descriptionTemplateZhLabel}
                    <input class="form-control" name="descriptionTemplateZh" value="{$descriptionTemplateZh}">
                </label>
            </div>
            <div class="prgf-muted">{$descriptionTemplateLabel} - {$placeholderHelp}</div>
            <hr>
            <h3>{$gatewayRules}</h3>
            <div class="prgf-muted" style="margin-bottom:10px;">{$activeGatewayHelp}</div>
            <table class="table table-striped table-condensed prgf-table">
                <thead>
                    <tr>
                        <th class="prgf-col-gateway">{$gatewayHeader}</th>
                        <th class="prgf-col-label">{$labelHeader}</th>
                        <th class="prgf-col-check">{$feeHeader}</th>
                        <th class="prgf-col-number">{$percentHeader}</th>
                        <th class="prgf-col-number">{$fixedHeader}</th>
                        <th class="prgf-col-number">{$minimumHeader}</th>
                        <th class="prgf-col-calc">{$calcHeader}</th>
                        <th class="prgf-col-country-mode">{$countryModeHeader}</th>
                        <th class="prgf-col-countries">{$countriesHeader}</th>
                        <th class="prgf-col-check">{$noticeHeader}</th>
                    </tr>
                </thead>
                <tbody>{$rows}</tbody>
            </table>
            <div class="prgf-muted">{$countryHelp}</div>
            <button type="submit" class="btn btn-primary" style="margin-top:14px;">{$saveSettings}</button>
        </form>
    </div>

    <div class="prgf-card">
        <h3>{$jsonTools}</h3>
        <form method="post">
            {$token}
            <input type="hidden" name="prgf_action" value="import_settings">
            <input type="hidden" name="adminLanguage" value="{$languageInput}">
            <textarea class="form-control prgf-json" name="settingsJson">{$json}</textarea>
            <button type="submit" class="btn btn-default" style="margin-top:10px;">{$importJson}</button>
        </form>
    </div>

    <div class="prgf-card">
        <h3>{$recentLogs}</h3>
        {$logs}
    </div>
</div>
HTML;
}

function peakrack_fees_rule_row(string $gateway, array $rule, array $t): string
{
    $name = 'rules[' . peakrackGatewayFeesEscape($gateway) . ']';
    $enabled = !empty($rule['enabled']) ? ' checked' : '';
    $showDetails = !empty($rule['showDetails']) ? ' checked' : '';
    $calculation = (string) ($rule['calculation'] ?? 'standard');
    $mode = (string) ($rule['countriesMode'] ?? 'all');
    $countries = implode(', ', $rule['countries'] ?? []);

    $calcOptions = [
        'standard' => $t['standard'],
        'gross_up' => $t['gross_up'],
    ];
    $modeOptions = [
        'all' => $t['all'],
        'allow' => $t['allow'],
        'block' => $t['block'],
    ];

    $calcSelect = '<select class="form-control" name="' . $name . '[calculation]">';
    foreach ($calcOptions as $value => $label) {
        $calcSelect .= '<option value="' . $value . '"' . ($calculation === $value ? ' selected' : '') . '>' . peakrackGatewayFeesEscape($label) . '</option>';
    }
    $calcSelect .= '</select>';

    $modeSelect = '<select class="form-control" name="' . $name . '[countriesMode]">';
    foreach ($modeOptions as $value => $label) {
        $modeSelect .= '<option value="' . $value . '"' . ($mode === $value ? ' selected' : '') . '>' . peakrackGatewayFeesEscape($label) . '</option>';
    }
    $modeSelect .= '</select>';

    return '<tr>'
        . '<td class="prgf-gateway">' . peakrackGatewayFeesEscape($gateway) . '</td>'
        . '<td><input class="form-control" name="' . $name . '[label]" value="' . peakrackGatewayFeesEscape((string) ($rule['label'] ?? $gateway)) . '"></td>'
        . '<td class="prgf-check-cell"><input type="checkbox" name="' . $name . '[enabled]"' . $enabled . '></td>'
        . '<td><input class="form-control" name="' . $name . '[percent]" value="' . peakrackGatewayFeesEscape((string) ($rule['percent'] ?? 0)) . '"></td>'
        . '<td><input class="form-control" name="' . $name . '[fixed]" value="' . peakrackGatewayFeesEscape((string) ($rule['fixed'] ?? 0)) . '"></td>'
        . '<td><input class="form-control" name="' . $name . '[minimum]" value="' . peakrackGatewayFeesEscape((string) ($rule['minimum'] ?? 0)) . '"></td>'
        . '<td>' . $calcSelect . '</td>'
        . '<td>' . $modeSelect . '</td>'
        . '<td><input class="form-control" name="' . $name . '[countries]" value="' . peakrackGatewayFeesEscape($countries) . '"></td>'
        . '<td class="prgf-check-cell"><input type="checkbox" name="' . $name . '[showDetails]"' . $showDetails . '></td>'
        . '</tr>';
}

function peakrack_fees_recent_logs(array $t): string
{
    try {
        $logs = WHMCS\Database\Capsule::table(PRGF_LOGS_TABLE)
            ->orderBy('id', 'desc')
            ->limit(12)
            ->get();
    } catch (Throwable $e) {
        return '<div class="alert alert-warning">' . peakrackGatewayFeesEscape($t['logs_unavailable']) . '</div>';
    }

    if ($logs->isEmpty()) {
        return '<p class="prgf-muted">' . peakrackGatewayFeesEscape($t['no_logs']) . '</p>';
    }

    $html = '<table class="table table-condensed"><thead><tr><th>'
        . peakrackGatewayFeesEscape($t['time'])
        . '</th><th>' . peakrackGatewayFeesEscape($t['level'])
        . '</th><th>' . peakrackGatewayFeesEscape($t['invoice'])
        . '</th><th>' . peakrackGatewayFeesEscape($t['gateway'])
        . '</th><th>' . peakrackGatewayFeesEscape($t['message'])
        . '</th></tr></thead><tbody>';
    foreach ($logs as $log) {
        $html .= '<tr>'
            . '<td>' . peakrackGatewayFeesEscape((string) ($log->created_at ?? '')) . '</td>'
            . '<td>' . peakrackGatewayFeesEscape((string) ($log->level ?? '')) . '</td>'
            . '<td>' . peakrackGatewayFeesEscape((string) ($log->invoice_id ?? '')) . '</td>'
            . '<td>' . peakrackGatewayFeesEscape((string) ($log->gateway ?? '')) . '</td>'
            . '<td>' . peakrackGatewayFeesEscape((string) ($log->message ?? '')) . '</td>'
            . '</tr>';
    }
    $html .= '</tbody></table>';

    return $html;
}

