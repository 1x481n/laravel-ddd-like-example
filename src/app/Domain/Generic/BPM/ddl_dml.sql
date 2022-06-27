create table business_type
(
    id                      bigint unsigned auto_increment
        primary key,
    name                    varchar(30)   default '' not null comment '类型名称（业务线或其他）',
    code                    varchar(255)             not null comment '模型',
    value                   int           default 0  not null comment '值',
    type                    tinyint       default 1  not null comment '类型 enum 1:审核:EXAMINE,2:任务:TASK',
    bpm_code                varchar(255)  default '' not null comment '关联bpm流程模板code',
    created_at              timestamp                null,
    updated_at              timestamp                null,
    deleted_at              timestamp                null
)
    comment '审核类型表' collate = utf8mb4_unicode_ci;


create table bpm_transaction
(
    id                   bigint unsigned auto_increment
        primary key,
    title                varchar(100)  default ''                not null comment '来源自定义标题',
    process_code         varchar(30)   default ''                not null comment '流程定义的唯一编码',
    source_type          int           default 0                 not null comment '来源单类型 dhf_business_type[type=1]',
    source_id            bigint        default 0                 not null comment '来源单id',
    source_no            varchar(100)  default ''                not null comment '来源单号',
    org_level            tinyint       default 0                 not null comment '流程实例归属组织级别：1.总部 2.大区 3.门店',
    store_id             int           default 0                 not null comment '流程实例所属门店id',
    area_id              int           default 0                 not null comment '流程实例所属大区id',
    start_user_id        int           default 0                 not null comment '发起人id',
    bpm_trace_id         varchar(100)  default ''                not null comment 'bpm跟踪id',
    transaction_sn       varchar(100)  default ''                not null comment '交易流水号',
    transaction_no       varchar(30)   default ''                not null comment '交易编号：对应每次运行的唯一流程实例id',
    transaction_state    varchar(20)   default 'pending_start'   not null comment '交易状态：pending_start[待发起] pending_continue[发起中|发起异常] processing[已发起] processed[已处理]',
    transaction_snapshot varchar(2000) default ''                not null comment '交易快照',
    process_result       varchar(20)   default ''                not null comment '流程处理结果 agree:同意，refuse:拒绝, cancel:撤销',
    source_handler       varchar(1000) default ''                not null comment '来源处理类 包含bpm回调通知、可以携带处理表单映射',
    start_at             timestamp                               null comment '流程发起时间',
    finish_at            timestamp                               null comment '流程完成时间',
    created_at           timestamp     default CURRENT_TIMESTAMP not null comment '创建时间',
    updated_at           timestamp     default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP comment '最后更新时间'
)
    comment 'bpm业务流交易表' collate = utf8mb4_unicode_ci;

create unique index idx_trad_sn
    on bpm_transaction (transaction_sn);

create index idx_trade_no
    on bpm_transaction (transaction_no);

create index idx_src
    on bpm_transaction (source_id, source_type);

create index idx_trace_id
    on bpm_transaction (bpm_trace_id);

create index idx_src_no
    on bpm_transaction (source_no);

create index idx_user_type
    on bpm_transaction (start_user_id, source_type);



