<?php

namespace App\Models\Business;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Model\Business\BusinessType
 *
 * @property int $id
 * @property string $name 类型名称（业务线或其他）
 * @property string $code 模型
 * @property string $bpm_code 关联java bpm流程定义ID
 * @property int $value 值
 * @property int $type 类型 enum 1:审核:EXAMINE,2:任务:TASK
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Business\BusinessType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Business\BusinessType newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Model\Business\BusinessType onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Business\BusinessType query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Business\BusinessType skipTake($num = 10, $skip = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Business\BusinessType whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Business\BusinessType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Business\BusinessType whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Business\BusinessType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Business\BusinessType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Business\BusinessType whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Business\BusinessType whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Business\BusinessType whereValue($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\Business\BusinessType withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Model\Business\BusinessType withoutTrashed()
 * @mixin \Eloquent
 */
class BusinessType extends Model
{
    use SoftDeletes;

    protected $table = 'business_type';

    protected $fillable = [
        'name', //类型名称（业务线或其他）
        'code', //编码
        'value', //值
        'type', //类型 enum 1:审核:EXAMINE,2:任务:TASK
        'created_at', //
    ];

    protected $hidden = [
        'deleted_at'
    ];
}
