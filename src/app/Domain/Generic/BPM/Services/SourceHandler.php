<?php
/**
 * Created by IntelliJ IDEA.
 * User: 1x481n
 * Date: 2022/6/8
 * Time: 4:25 PM
 */

namespace App\Domain\Generic\BPM\Services;

use App\Domain\Generic\BPM\Application\DTO\CallbackDTO;

/**
 * 所有审批业务的调用侧，如果需要基于审批回调处理自己的业务逻辑，在发起审批时候，必须传入当前抽象类的子类，按需重写方法
 */
abstract class SourceHandler
{
    /**
     * @var  CallbackDTO bpm回调数据传输对象
     */
    public CallbackDTO $callbackDTO;

    /**
     * 已同意且未完成
     *
     * @return void
     */
    public function agreedAndUnfinished()
    {

    }

    /**
     * 已驳回且未完成
     *
     * @return void
     */
    public function disagreeAndUnfinished()
    {

    }

    /**
     * 已同意且已完成
     *
     * @return void
     */
    public function agreedAndFinished()
    {

    }

    /**
     * 已拒绝且已完成
     *
     * @return void
     */
    public function refusedAndFinished()
    {

    }

    /**
     * 已撤销且已完成
     *
     * @return void
     */
    public function canceledAndFinished()
    {

    }

    public function __toString()
    {
        return serialize($this);
    }

}
