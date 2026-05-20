# PeakRack Gateway Fees 升级说明

## 1.0.1

面向 WHMCS 9.x / PHP 8.3 的文档和仓库整理版本。

### 新增

- 仓库根目录 MIT `LICENSE`。
- 完整的英文和简体中文 README。
- 完整的英文和简体中文升级说明。
- 适合随插件目录一起分发的模块内 README 和升级说明。
- 更清晰的网关规则、国家/地区分配、运行 Hook、数据表和安全说明。

### 变更

- 插件版本提升到 `1.0.1`。
- GitHub 仓库元数据应设置 `whmcs`、`peakrack`、`payment-gateway` 等主题，并指向 PeakRack 官网。

### 升级提示

- 现有 WHMCS 安装不需要数据库结构变更。
- 将新的 `peakrack_fees/` 目录覆盖到 `modules/addons/peakrack_fees/`。
- 打开 **System Settings > Addon Modules**，确认插件版本显示为 `1.0.1`。
- 向客户分发前，建议先阅读新的安装、配置和升级说明。

## 1.0.0

初始公开版本。

### 新增

- WHMCS Addon Module：`peakrack_fees`。
- 可配置的支付网关手续费规则。
- 固定金额、百分比、最低发票金额和倒推补足算法。
- 受控发票项目类型 `PeakRackGatewayFee`。
- 发票创建、发票邮件生成前、切换网关、后台查看发票详情、客户查看发票时刷新手续费。
- 结账页按国家/地区分配网关，包含前端隐藏和服务端校验。
- 顶部中英文后台语言切换。
- 英文和中文发票项目描述模板。
- 标准化配置的 JSON 导入/导出。
- 带保留策略的模块日志表和 WHMCS 每日 Cron 清理。
- GitHub Actions PHP 语法检查工作流。

### 加固

- 调用 `updateInvoiceTotal()` 前会先加载 WHMCS 发票辅助函数。
- 受控发票项目到期日跟随发票到期日。
- 当前选择的网关变为不可用时，前端会重新选择一个允许的网关。
- 不修改已付款、已取消或已退款发票。

### 升级提示

- 最终模块目录名为：

  ```text
  peakrack_fees
  ```

- 最终 WHMCS 插件路径为：

  ```text
  modules/addons/peakrack_fees
  ```

- 最终数据表为：

  ```text
  mod_peakrack_fees_settings
  mod_peakrack_fees_logs
  ```

- 如果曾安装早期开发目录 `modules/addons/peakrack_gateway_fees`，应先停用并删除旧目录，再启用 `peakrack_fees`。
- 本版本不会迁移早期开发表 `mod_peakrack_gateway_fees_*` 中的数据。

## 标准手动升级

1. 备份 WHMCS 数据库。
2. 将新的 `peakrack_fees` 文件夹上传到 `modules/addons/peakrack_fees`。
3. 覆盖该目录中的现有文件。
4. 打开 **System Settings > Addon Modules**，确认模块处于启用状态。
5. 打开 **Addons > PeakRack Gateway Fees** 并检查配置。
6. 大范围启用手续费前，先用未付款发票测试。
