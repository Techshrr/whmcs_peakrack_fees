# PeakRack Gateway Fees 升级说明

## 1.0.1

文档和仓库整理版本。

- 插件版本提升到 `1.0.1`。
- 新增模块目录内的英文和简体中文说明文档。
- 不需要数据库结构变更。
- 将当前 `peakrack_fees/` 目录覆盖到现有 `modules/addons/peakrack_fees/`。

## 1.0.0

初始公开版本。

- 最终模块目录：`peakrack_fees`。
- 最终 WHMCS 插件路径：`modules/addons/peakrack_fees`。
- 最终数据表：`mod_peakrack_fees_settings` 和 `mod_peakrack_fees_logs`。
- 早期开发目录，例如 `peakrack_gateway_fees`，应在启用本模块前停用并删除。
- 早期开发表，例如 `mod_peakrack_gateway_fees_*`，不会自动迁移。

## 标准手动升级

1. 备份 WHMCS 数据库。
2. 将当前 `peakrack_fees` 文件夹上传到 `modules/addons/peakrack_fees`。
3. 覆盖该目录中的现有文件。
4. 在 **System Settings > Addon Modules** 确认插件处于启用状态。
5. 从 **Addons > PeakRack Gateway Fees** 检查配置。
6. 大范围启用手续费前，先用未付款发票测试。
