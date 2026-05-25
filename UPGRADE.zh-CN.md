# 升级说明

本文档用于说明如何从旧版本升级本模块。

## 升级前准备

1. 备份 WHMCS 文件。
2. 备份 WHMCS 数据库。
3. 复制一份 `modules/addons/peakrack_fees/`。
4. 升级前阅读 [CHANGELOG.md](CHANGELOG.md)。
5. 确认本次升级是否涉及数据库变更。

## 升级步骤

1. 从官方仓库下载最新版本：

   https://github.com/Techshrr/whmcs_peakrack_fees

2. 将插件文件替换到：

   `modules/addons/peakrack_fees/`

3. 保留现有 WHMCS 模块设置。
4. 登录 WHMCS 后台。
5. 打开 **Addons > PeakRack Gateway Fees**，检查所有配置项和网关规则。
6. 如果发票或结账页面显示没有更新，请清理 WHMCS 模板缓存。

## 数据库迁移

本版本不需要手动执行数据库迁移。

插件会在启用或后台访问时创建设置表和日志表。

## 版本升级说明

### 从 1.0.0 升级到 1.0.1

- 无破坏性变更。
- 原有设置会保留。
- 不需要手动执行数据库变更。

## 回滚方法

如需回滚：

1. 恢复旧版本 `modules/addons/peakrack_fees/` 目录。
2. 如果升级修改过模块表，恢复数据库备份。
3. 清理 WHMCS 模板缓存。
4. 检查 WHMCS 活动日志和模块日志是否有错误。

## 注意事项

不要覆盖生产环境密钥、本地配置文件、自定义模板、回调密钥或支付凭据，除非升级说明明确要求。