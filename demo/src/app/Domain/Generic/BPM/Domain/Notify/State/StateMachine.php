<?php
/**
 * Created by IntelliJ IDEA.
 * User: 1x481n
 * Date: 2022/7/17
 * Time: 17:05 PM
 */

namespace App\Domain\Generic\BPM\Domain\Notify\State;


use App\Domain\Generic\BPM\Domain\Notify\NotifyHandlerContext;

abstract class StateMachine
{
    protected NotifyHandlerContext $notifyHandlerContext;

    public function setContext(NotifyHandlerContext $notifyHandlerContext)
    {
        $this->notifyHandlerContext = $notifyHandlerContext;
    }

    abstract public function handle();
}
