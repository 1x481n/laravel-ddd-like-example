<?php
/**
 * Created by IntelliJ IDEA.
 * User: 1x481n
 * Date: 2022/7/10
 * Time: 6:53 PM
 */

namespace App\Domain\Generic\BPM\Services;

use App\Models\UserAdmin;
use App\Utils\UUID;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

class MockClient implements NetworkInterface
{

    /**
     * @var array
     */
    public array $input = [];

    /**
     * @var array
     */
    private array $httpMessage = [];

    /**
     * @var array
     */
    private array $alarmMessage = [];

    /**
     * @var string
     */
    public string $accessToken = 'mock_access_token';

    /**
     * @var string
     */
    public string $requestId;


    public function __construct()
    {
        $this->requestId = UUID::v4() . '-' . round(microtime(true) * 1000);
    }

    /**
     * @return array
     */
    public function getAccessToken(): array
    {
        //TODO:
        return [];
    }

    /**
     * @param string $processDefinitionId
     * @return array
     */
    public function getProcessVars(string $processDefinitionId): array
    {
        //TODO:
        return [];
    }

    /**
     * @param string $processUniqueCode
     * @return array
     */
    public function getProcessStartForm(string $processUniqueCode): array
    {
        //TODO:
        return [];
    }

    /**
     * @param string $taskId
     * @return array
     */
    public function getProcessRuntimeForm(string $taskId): array
    {
        //TODO:
        return [];
    }

    /**
     * @param string $processUniqueCode
     * @return array
     */
    public function getProcessStartVar(string $processUniqueCode): array
    {
        //TODO:
        return [];
    }

    /**
     * @param string $taskId
     * @return array
     */
    public function getProcessRuntimeVar(string $taskId): array
    {
        //TODO:
        return [];
    }

    /**
     * @param string $processUniqueCode
     * @return array
     */
    public function getProcessStartFrom(string $processUniqueCode): array
    {
        //TODO:
        return [];
    }

    /**
     * @param string $processUniqueCode
     * @param array $startUser
     * @param array $formVars
     * @param array $ext
     * @param array $copyIds
     * @param bool $executeFirstNode
     * @return array
     */
    #[Pure] #[ArrayShape(['code' => "int", 'data' => "object", 'message' => "\mixed|string"])]
    public function startProcess(string $processUniqueCode, array $startUser, array $formVars = [], array $ext = [], array $copyIds = [], bool $executeFirstNode = true): array
    {
        return $this->mockResponseData(
            [
                'processInstanceId' => 'mock_processInstanceId_007',
                'processNo' => '2022071014270082379'
            ],
            '流程发起成功'
        );
    }

    /**
     * @param string $taskId
     * @param UserAdmin $startUser
     * @param array $formVars
     * @param string $remark
     * @param array $ext
     * @param array $copyIds
     * @return array
     */
    public function resubmitTodoTask(string $taskId, UserAdmin $startUser, array $formVars = [], string $remark = '', array $ext = [], array $copyIds = []): array
    {
        //TODO:
        return [];
    }

    /**
     * @param string $taskId
     * @param UserAdmin $operator
     * @param string $remark
     * @param array $formVars
     * @param array $ext
     * @param array $copyIds
     * @return array
     */
    public function agreeTodoTask(string $taskId, UserAdmin $operator, string $remark = '', array $formVars = [], array $ext = [], array $copyIds = []): array
    {
        //TODO:
        return [];
    }

    /**
     * @param string $taskId
     * @param UserAdmin $operator
     * @param string $remark
     * @param array $formVars
     * @param array $ext
     * @param array $copyIds
     * @return array
     */
    public function refuseTodoTask(string $taskId, UserAdmin $operator, string $remark = '', array $formVars = [], array $ext = [], array $copyIds = []): array
    {
        //TODO:
        return [];
    }


    /**
     * 暴露当前已缓存的http报文
     *
     * @return array
     */
    public function getHttpMessage(): array
    {
        return $this->httpMessage;
    }


    /**
     * @param array $data
     * @param string $message
     * @return array
     */
    #[ArrayShape(['code' => "int", 'data' => "object", 'message' => "mixed|string"])]
    private function mockResponseData(array $data = [], string $message = ''): array
    {
        return [
            'code' => 0,
            'data' => $data,
            'message' => $message ?? 'mock success!'
        ];
    }

    public function __toString(): string
    {
        return implode(PHP_EOL, $this->httpMessage);
    }
}
