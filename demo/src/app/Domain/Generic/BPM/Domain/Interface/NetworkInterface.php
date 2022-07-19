<?php
/**
 * Created by IntelliJ IDEA.
 * User: 1x481n
 * Date: 2022/7/10
 * Time: 2:44 PM
 */

namespace App\Domain\Generic\BPM\Domain\Interface;

use App\Models\UserAdmin;

interface NetworkInterface
{
    public function getAccessToken(): array;

    public function getProcessVars(string $processDefinitionId): array;

    public function getProcessStartForm(string $processUniqueCode): array;

    public function getProcessRuntimeForm(string $taskId): array;

    public function getProcessStartVar(string $processUniqueCode): array;

    public function getProcessRuntimeVar(string $taskId): array;

    public function getProcessStartFrom(string $processUniqueCode): array;

    public function startProcess(
        string $processUniqueCode,
        array  $startUser,
        array  $formVars = [],
        array  $ext = [],
        array  $copyIds = [],
        bool   $executeFirstNode = true
    ): array;

    public function resubmitTodoTask(
        string    $taskId,
        UserAdmin $startUser,
        array     $formVars = [],
        string    $remark = '',
        array     $ext = [],
        array     $copyIds = []
    ): array;

    public function agreeTodoTask(
        string    $taskId,
        UserAdmin $operator,
        string    $remark = '',
        array     $formVars = [],
        array     $ext = [],
        array     $copyIds = []
    ): array;

    public function refuseTodoTask(
        string    $taskId,
        UserAdmin $operator,
        string    $remark = '',
        array     $formVars = [],
        array     $ext = [],
        array     $copyIds = []
    ): array;


    public function getHttpMessage(): array;

}
