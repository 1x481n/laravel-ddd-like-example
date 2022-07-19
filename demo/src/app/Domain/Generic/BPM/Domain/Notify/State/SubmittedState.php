<?php
/**
 * Created by IntelliJ IDEA.
 * User: 1x481n
 * Date: 2022/7/17
 * Time: 17:05 PM
 */

namespace App\Domain\Generic\BPM\Domain\Notify\State;


class SubmittedState extends StateMachine
{
    public function handle()
    {
        dump(__CLASS__, 'hasFinished:' . $this->notifyHandlerContext->callbackDTO->hasFinished);

        if ($this->notifyHandlerContext->callbackDTO->hasFinished) {
            $this->notifyHandlerContext->transitionTo(app(FinishedState::class));
            $this->notifyHandlerContext->sourceHandler->submittedAndFinished();
        } else {
            //TODO:
        }
    }
}
