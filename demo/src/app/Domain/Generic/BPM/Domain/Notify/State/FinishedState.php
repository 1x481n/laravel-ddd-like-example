<?php
/**
 * Created by IntelliJ IDEA.
 * User: 1x481n
 * Date: 2022/7/17
 * Time: 17:05 PM
 */

namespace App\Domain\Generic\BPM\Domain\Notify\State;


class FinishedState extends StateMachine
{
    const PROCESSED = 'processed';

    public function handle()
    {
        dump(__CLASS__, 'hasFinished:' . $this->notifyHandlerContext->callbackDTO->hasFinished);

        $bpmTransaction = $this->notifyHandlerContext->callbackDTO->bpmTransaction;
        $bpmTransaction->transaction_state = self::PROCESSED;
        $bpmTransaction->process_result = $this->notifyHandlerContext->callbackDTO->dealResult;
        $bpmTransaction->finish_at = date('Y-m-d H:i:s');
        $bpmTransaction->save();

        $this->notifyHandlerContext->callbackDTO->bpmTransaction = $bpmTransaction->toArray();
    }
}
