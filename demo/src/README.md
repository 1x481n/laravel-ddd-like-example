# 工作实践

## [BPM](./app/Domain/Generic/BPM)
BPM（Business Process Manage）业务流程管理
- 敏感数据配置和代码已脱敏。
- 依赖的部分逻辑数据已进行mock处理。
- 来源于工作业务的Lumen框架项目，使用PHP8.1+Laravel9重新适配和编写。
- 逐渐领先于业务代码，使用较好的实现重构部分逻辑。


### 安装

- 初始化 [数据脚本](./app/Domain/Generic/BPM/ddl_dml.sql)

- 参考.env.example 并配置环境变量（若无远程服务，可修改BPM_NETWORK_INTERFACE进行mock）
```commandline
    cp .env.example .env
```

