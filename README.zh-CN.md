# PeakRack Gateway Fees for WHMCS

> 官方仓库：https://github.com/Techshrr/whmcs_peakrack_fees
> 许可证：Apache License 2.0

PeakRack Gateway Fees 是一个 WHMCS 插件，用于配置支付网关手续费和按国家/地区分配支付网关。

## 项目说明

插件会为所选支付网关添加一个受管理的发票项目。它可以计算固定金额和百分比手续费，在常见 WHMCS 发票流程中刷新未付款发票，并按客户账单国家/地区限制可用支付网关。

设置和日志保存到专用模块表中。停用插件不会删除配置和日志。

## 功能特性

- 添加类型为 `PeakRackGatewayFee` 的受管理发票项目。
- 支持固定手续费、百分比手续费、最低发票金额规则和倒推补足算法。
- 在发票创建、发票邮件发送前、切换网关、后台查看发票和客户查看发票时刷新未付款发票费用。
- 不修改已付款、已取消或已退款发票。
- 支持将手续费发票项目标记为计税，并可跳过免税客户。
- 支持结账时按国家/地区分配支付网关。
- 在客户界面隐藏不可用网关，并在结账时进行服务端校验。
- 后台界面支持英文和中文切换。
- 支持英文和中文发票项目描述模板。
- 支持 JSON 设置导入和导出。
- 保留模块日志，支持日志保留设置和可选 WHMCS Activity Log 同步。

## 环境要求

- WHMCS 9.0.x
- PHP 8.3 或更高版本
- MySQL 5.7 / 8.0

## 安装方法

1. 从官方仓库下载最新版本。
2. 将插件目录上传到：

   `modules/addons/peakrack_fees/`

3. 登录 WHMCS 后台。
4. 进入 **System Settings > Addon Modules** 并启用 **PeakRack Gateway Fees**。
5. 打开 **Addons > PeakRack Gateway Fees**，在大范围启用手续费前检查规则。

## 配置说明

| 配置项 | 说明 | 默认值 |
|---|---|---|
| Enable module | 是否启用手续费和网关分配逻辑 | 开启 |
| Mark fee invoice item as taxable | 是否将手续费发票项目标记为计税 | 关闭 |
| Skip tax-exempt clients | 是否跳过免税客户 | 开启 |
| Show checkout fee details | 是否在结账支付方式附近显示手续费说明 | 开启 |
| Refresh fee when invoice is viewed | 查看未付款发票时是否刷新手续费 | 开启 |
| Enable gateway allocation | 是否启用国家/地区网关规则 | 开启 |
| Validate allocation at checkout | 结账时是否拒绝不可用网关 | 开启 |
| Mirror events to WHMCS Activity Log | 是否同步事件到 WHMCS 活动日志 | 开启 |
| Log retention days | 按时间清理模块日志 | 180 |
| Maximum log rows | 按数量清理模块日志 | 5000 |
| Admin language | 后台默认语言 | en |
| English invoice item description | 非中文客户使用的发票项目描述模板 | Payment Gateway Fee ({gateway}) |
| Chinese invoice item description | 中文客户使用的发票项目描述模板 | 支付网关手续费（{gateway}） |
| Gateway rule fields | 每个网关的显示名称、费用开关、百分比、固定金额、最低金额、算法、国家/地区模式、国家列表和结账说明 | 每个网关默认关闭费用 |

## 使用说明

管理员为已启用的 WHMCS 支付网关配置规则。每条规则可以启用手续费、设置计算方式、设置国家/地区可用性，并决定是否在结账页显示手续费说明。

当未付款发票创建或刷新时，插件会根据当前支付网关更新受管理的手续费发票项目。结账时，插件可以根据客户账单国家/地区隐藏不可用网关，并在服务端拒绝不可用选择。

## 数据库表

- `mod_peakrack_fees_settings`
- `mod_peakrack_fees_logs`

## 升级说明

请查看 [UPGRADE.zh-CN.md](UPGRADE.zh-CN.md)。

## 英文文档

请查看 [README.md](README.md)。

## 安全说明

请勿提交生产环境凭据、API Key、数据库密码、支付密钥、WHMCS 授权信息、客户数据、身份证件或私有签名密钥。

安全问题报告方式请查看 [SECURITY.md](SECURITY.md)。

## 许可证

本项目基于 Apache License 2.0 发布。完整许可证请查看 [LICENSE](LICENSE)。

其他项目声明请查看 [NOTICE](NOTICE)。
