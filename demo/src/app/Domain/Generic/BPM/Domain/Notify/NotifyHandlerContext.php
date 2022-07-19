<?php
/**
 * Created by IntelliJ IDEA.
 * User: 1x481n
 * Date: 2022/7/17
 * Time: 17:05 PM
 */

namespace App\Domain\Generic\BPM\Domain\Notify;

use App\Domain\Generic\BPM\Application\DTO\CallbackDTO;
use App\Domain\Generic\BPM\Domain\Exception\NotifyException;
use App\Domain\Generic\BPM\Domain\Interface\SourceHandler;
use App\Domain\Generic\BPM\Domain\Notify\State\StateMachine;
use Throwable;

class NotifyHandlerContext
{
    /**
     * @var StateMachine 状态机
     */
    private StateMachine $stateMachine;

    /**
     * @var  CallbackDTO bpm回调数据传输对象
     */
    public CallbackDTO $callbackDTO;

    /**
     * @var SourceHandler 源处理程序
     */
    public SourceHandler $sourceHandler;

    const PROCESSED = 'processed';

    public function __construct(StateMachine $stateMachine, SourceHandler $sourceHandler, CallbackDTO $callbackDTO)
    {
        $this->sourceHandler = $sourceHandler;
        $this->callbackDTO = $callbackDTO;
        $this->transitionTo($stateMachine);
    }

    public function transitionTo(StateMachine $stateMachine): StateMachine
    {
        $stateMachine->setContext($this);
        $this->stateMachine = $stateMachine;
        return $this->stateMachine;
    }

    /**
     * @throws Throwable
     */
    public function handle(): bool
    {
        try {
            $this->stateMachine->handle();
            $this->callbackDTO->bpmTransaction = $this->callbackDTO->bpmTransaction->toArray();
            return true;
        } catch (Throwable $e) {
            throw new NotifyException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
