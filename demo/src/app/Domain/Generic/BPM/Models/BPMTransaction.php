<?php

namespace App\Domain\Generic\BPM\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Domain\Generic\BPM\Models\BPMTransaction
 *
 * @property int $id
 * @property string $title 来源自定义标题
 * @property string $process_code 流程定义的唯一编码
 * @property int $source_type 来源单类型 dhf_business_type[type=1]
 * @property int $source_id 来源单id
 * @property string $source_no 来源单号
 * @property int $org_level 流程实例归属组织级别：1.总部 2.大区 3.门店
 * @property int $store_id 流程实例所属门店id
 * @property int $area_id 流程实例所属大区id
 * @property int $start_user_id 发起人id
 * @property string $bpm_trace_id bpm跟踪id
 * @property string $transaction_sn 交易流水号
 * @property string $transaction_no 交易编号：对应每次流程运行的唯一实例id
 * @property string $transaction_state 交易状态：pending_start[待发起] pending_continue[发起中|发起异常] processing[已发起] processed[已处理]
 * @property string $transaction_snapshot 交易快照
 * @property string $process_result 流程处理结果 agree:同意，refuse:拒绝, cancel:撤销
 * @property string $source_handler 来源处理类 包含bpm回调通知、可以携带处理表单映射
 * @property string|null $start_at 流程发起时间
 * @property string|null $finish_at 流程完成时间
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @property \Illuminate\Support\Carbon $updated_at 最后更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|BPMTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BPMTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BPMTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|BPMTransaction skipTake($num = 10, $skip = null)
 * @method static \Illuminate\Database\Eloquent\Builder|BPMTransaction whereCallbackSourceHandler($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BPMTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BPMTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BPMTransaction whereProcessCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BPMTransaction whereProcessFinishAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BPMTransaction whereProcessResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BPMTransaction whereProcessStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BPMTransaction whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BPMTransaction whereSourceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BPMTransaction whereSourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BPMTransaction whereStartAreaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BPMTransaction whereStartStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BPMTransaction whereStartUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BPMTransaction whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BPMTransaction whereTransactionNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BPMTransaction whereTransactionState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BPMTransaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BPMTransaction whereAreaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BPMTransaction whereBpmTraceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BPMTransaction whereFinishAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BPMTransaction whereOrgLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BPMTransaction whereStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BPMTransaction whereStoreId($value)
 * @mixin \Eloquent
 */
class BPMTransaction extends Model
{

    protected $table = 'bpm_transaction';

    protected $fillable = [
        'title', //来源自定义标题
        'process_code', //流程定义的唯一编码
        'source_type', //来源单类型 dhf_business_type[type=1]
        'source_id', //来源单id
        'source_no', //来源单号
        'org_level', //流程实例归属组织级别：1.总部 2.大区 3.门店
        'store_id', //流程实例所属门店id
        'area_id', //流程实例所属大区id
        'start_user_id', //发起人id
        'bpm_trace_id', // bpm跟踪id
        'transaction_sn', // 交易流水号
        'transaction_no', //交易编号：对应每次运行的唯一流程实例id（java返）
        'transaction_state',//交易状态：pending_start[待发起] pending_response[已发起未响应] processing[已发起处理中] processed[已处理]
        'transaction_snapshot', //交易快照
        'process_result',//流程处理结果 agree:同意，refuse:拒绝
        'source_handler', //来源处理类 包含bpm回调通知、可以携带处理表单映射
        'start_at', //发起时间
        'finish_at', //完成时间
    ];

    protected $casts = [
        'source_handler' => 'string',
        'transaction_snapshot'=>'array'
    ];

    public function getSourceHandlerAttribute($value)
    {
        return unserialize($value);
    }
}
