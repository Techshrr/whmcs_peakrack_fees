# PeakRack Gateway Fees for WHMCS

[English](README.md) | [简体中文](README.zh-CN.md)

PeakRack Gateway Fees 是一个面向 WHMCS 的支付网关手续费与国家/地区网关分配插件。
它会为选定支付网关维护一个受控的发票项目，自动刷新未付款发票总额，并可根据客户账单国家/地区隐藏或拒绝不可用的支付方式。

## 当前版本

`1.0.1`

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
- 可将插件事件镜像到 WHMCS Activity Log。
- 停用插件时保留配置和日志数据。

## 兼容环境

- WHMCS 9.x
- PHP 8.3
- WHMCS 支持的 MySQL/MariaDB

插件使用 WHMCS `Capsule`、Addon Module 生命周期函数、发票辅助函数和标准 Hook 注册方式。

## 文件结构

仓库根目录保持适合 GitHub 浏览的浅层结构。真正用于部署的插件目录是：

```text
peakrack_fees/
```

安装时将 `peakrack_fees` 上传或复制到 WHMCS 的 `modules/addons/peakrack_fees/`。

## 安装方式

1. 将仓库中的插件目录上传到 WHMCS：

   ```text
   peakrack_fees/ -> modules/addons/peakrack_fees/
   ```

   上传后的最终路径应为：

   ```text
   modules/addons/peakrack_fees/
   ```

2. 登录 WHMCS 后台，进入：

   ```text
   System Settings > Addon Modules
   ```

3. 启用 **PeakRack Gateway Fees**。

4. 打开：

   ```text
   Addons > PeakRack Gateway Fees
   ```

5. 在大范围启用手续费前，先检查模块开关、手续费规则、国家/地区分配规则、发票描述和日志保留设置。

## 配置说明

### 基础控制

- **启用模块**：启用或关闭手续费计算和网关分配逻辑。
- **手续费发票项目计税**：将受控的手续费发票项目标记为计税。
- **跳过免税客户**：对 WHMCS 中标记为免税的客户移除或不添加手续费。
- **结账页显示手续费说明**：在结账页支付方式附近显示手续费信息。
- **查看发票时刷新手续费**：打开未付款发票时刷新受控手续费。
- **启用网关国家/地区分配**：应用按国家/地区限制支付方式的规则。
- **结账时校验网关分配**：结账提交时在服务端拒绝不可用支付方式。
- **同步事件到 WHMCS 活动日志**：将插件日志同步到 WHMCS Activity Log。
- **日志保留天数**：每日 Cron 清理早于指定天数的插件日志。填写 `0` 表示不按时间清理。
- **最大日志行数**：只保留最新的指定数量日志。填写 `0` 表示不按数量清理。
- **后台语言**：保存后的默认后台语言。顶部语言按钮可以立即切换当前页面语言，不需要保存整页配置。

### 发票描述

插件分别保存英文和中文发票项目描述模板。

可用占位符：

- `{gateway}`：配置的网关显示名称。
- `{module}`：WHMCS 支付网关模块名。

当客户语言看起来是中文时，插件使用中文模板；其他情况使用英文模板。

### 网关规则

启用的 WHMCS 支付网关会在刷新插件后台后自动出现在规则表中。
新出现的网关规则默认不启用手续费。

规则字段：

- **显示名称**：用于发票描述和结账提示的人类可读网关名称。
- **启用费用**：为该网关启用手续费计算。
- **百分比**：按发票基础金额计算的百分比费用。
- **固定金额**：附加到手续费中的固定金额。
- **最低金额**：发票基础金额达到该值后才收取手续费。
- **算法**：`标准` 或 `倒推补足`。
- **国家/地区模式**：`全部`、`允许` 或 `屏蔽`。
- **国家/地区**：ISO-3166 alpha-2 国家代码，例如 `US`、`CA`、`CN`。
- **显示说明**：控制是否在结账页显示该网关的手续费说明。

算法说明：

- **标准**：`发票基础金额 * 百分比 + 固定金额`。
- **倒推补足**：按最终收款金额倒推手续费，用于覆盖百分比类支付通道成本。

国家/地区分配说明：

- **全部**：该网关对所有国家/地区可用。
- **允许**：该网关仅对列表内国家/地区可用。
- **屏蔽**：该网关会对列表内国家/地区隐藏并拒绝。

## 运行 Hook

插件通过 `hooks.php` 注册以下 WHMCS Hook：

- `InvoiceCreation`：发票创建时添加或刷新网关手续费。
- `InvoiceCreationPreEmail`：生成发票邮件前刷新手续费。
- `InvoiceChangeGateway`：发票支付网关变更后重新计算手续费。
- `ViewInvoiceDetailsPage`：后台查看未付款发票时刷新手续费。
- `ClientAreaPageViewInvoice`：客户查看未付款发票时刷新手续费。
- `ShoppingCartValidateCheckout`：服务端校验选定网关是否可用。
- `ShoppingCartCheckoutOutput`：输出结账页手续费说明和分配数据。
- `ClientAreaFooterOutput`：在发票或支付页面注入前端分配逻辑。
- `DailyCronJob`：按保留策略清理旧插件日志。

## 数据表

插件启用时会创建以下数据表：

- `mod_peakrack_fees_settings`
- `mod_peakrack_fees_logs`

停用插件时不会删除这些数据表，以保留配置和排查记录。

当前公开包固定使用 `mod_peakrack_fees_*` 表名，不迁移早期开发表名，例如 `mod_peakrack_gateway_fees_*`。

## 项目结构

```text
peakrack_fees/
  peakrack_fees.php       插件入口、启用、升级和后台页面
  hooks.php               WHMCS Hook 注册
  README.md               插件目录英文说明
  README.zh-CN.md         插件目录中文说明
  UPGRADE.md              插件目录英文升级说明
  UPGRADE.zh-CN.md        插件目录中文升级说明
  lib/
    Bootstrap.php         默认配置、设置读写、发票手续费、网关分配和日志逻辑
```

## 使用建议

- 插件只管理 `PeakRackGatewayFee` 类型的发票项目。
- 不会修改已付款、已取消或已退款发票。
- 大范围启用前，先用未付款发票完整测试一次。
- 将手续费发票项目设置为计税前，应先确认站点税务规则。
- 只保留一个插件目录处于启用状态，不要同时启用早期开发目录。
- 公开发布包不要包含客户凭据、WHMCS `configuration.php`、数据库导出、商业授权辅助文件或本地编码文件。

## 升级说明

逐版本升级内容见 [UPGRADE.zh-CN.md](UPGRADE.zh-CN.md)。

## 开发检查

打包或发布前建议执行 PHP 语法检查：

```powershell
Get-ChildItem -Path peakrack_fees -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
```

预期结果：所有 PHP 文件均无语法错误。

## 许可协议

MIT License。完整条款见仓库根目录 [LICENSE](LICENSE)。
