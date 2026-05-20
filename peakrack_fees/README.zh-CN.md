# PeakRack Gateway Fees for WHMCS

[English](README.md) | [简体中文](README.zh-CN.md)

PeakRack Gateway Fees 是一个面向 WHMCS 的支付网关手续费与国家/地区网关分配插件。
当前目录就是可直接部署到 WHMCS 的插件目录。

## 当前版本

`1.0.1`

## 兼容环境

- WHMCS 9.x
- PHP 8.3
- WHMCS 支持的 MySQL/MariaDB

## 安装方式

将这个 `peakrack_fees` 文件夹上传到 WHMCS：

```text
modules/addons/peakrack_fees/
```

然后打开 **System Settings > Addon Modules**，启用 **PeakRack Gateway Fees**，再从 **Addons > PeakRack Gateway Fees** 进入配置页面。

## 功能特性

- 为选定支付网关增加一个受控的发票项目。
- 支持固定费用、百分比费用、最低发票金额规则和倒推补足算法。
- 在发票创建、发票邮件生成前、切换支付网关、后台查看发票详情、客户查看发票时刷新未付款发票手续费。
- 不修改已付款、已取消或已退款发票。
- 可将手续费发票项目标记为计税。
- 可跳过 WHMCS 中标记为免税的客户。
- 支持结账页按国家/地区分配支付网关。
- 在客户界面隐藏不可用支付方式，并在结账提交时进行服务端校验。
- 可在结账页显示每个网关的手续费说明。
- 支持中英文后台界面，顶部语言切换无需保存整页设置即可立即生效。
- 支持英文和中文发票项目描述模板。
- 可根据 WHMCS 客户语言或当前会话语言识别中文客户，并使用中文手续费描述。
- 支持 JSON 配置导入和导出。
- 保存插件日志，并支持按保留天数和最大行数清理。

## 配置摘要

### 基础控制

- **启用模块**：控制全部手续费和网关分配逻辑。
- **手续费发票项目计税**：控制受控发票项目是否计税。
- **跳过免税客户**：对 WHMCS 免税客户不收取手续费。
- **结账页显示手续费说明**：在结账页支付方式附近显示手续费信息。
- **查看发票时刷新手续费**：保持未付款发票总额最新。
- **启用网关国家/地区分配**：启用按国家/地区限制支付方式的规则。
- **结账时校验网关分配**：在服务端拒绝不可用支付方式。
- **同步事件到 WHMCS 活动日志**：将插件日志同步到 WHMCS Activity Log。

### 网关规则

启用的 WHMCS 支付网关会在刷新插件后台后自动出现在规则表中。
新出现的规则默认不启用手续费。

规则字段包括显示名称、是否启用费用、百分比、固定金额、最低发票金额、算法、国家/地区模式、国家/地区列表和结账页说明开关。

国家代码使用 ISO-3166 alpha-2 格式，例如 `US`、`CA`、`CN`。

### 发票描述

英文和中文发票项目描述模板会分别保存。

可用占位符：

- `{gateway}`：网关显示名称。
- `{module}`：WHMCS 支付网关模块名。

## 运行 Hook

- `InvoiceCreation`
- `InvoiceCreationPreEmail`
- `InvoiceChangeGateway`
- `ViewInvoiceDetailsPage`
- `ClientAreaPageViewInvoice`
- `ShoppingCartValidateCheckout`
- `ShoppingCartCheckoutOutput`
- `ClientAreaFooterOutput`
- `DailyCronJob`

## 数据表

- `mod_peakrack_fees_settings`
- `mod_peakrack_fees_logs`

停用插件时不会删除这些数据表。

## 使用建议

- 插件只管理 `PeakRackGatewayFee` 类型的发票项目。
- 不会修改已付款、已取消或已退款发票。
- 大范围启用手续费前，先用未付款发票测试。
- 停用或删除早期开发插件目录，避免重复 Hook。

## 升级说明

逐版本升级内容见 [UPGRADE.zh-CN.md](UPGRADE.zh-CN.md)。
