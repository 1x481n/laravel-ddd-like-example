<?php
/**
 * Created by IntelliJ IDEA.
 * User: 1x481n
 * Date: 2022/6/17
 * Time: 4:19 PM
 */

namespace App\Domain\Generic\BPM\Domain\Interface;


use App\Domain\Generic\BPM\Models\BPMTransaction;

interface WithContextFormMap
{
    /**
     * 根据中台表单配置，需要上下文自己映射为表单数组
     * @desc ps:各表单实现类编写时已经限定在某个流程code的语境内，不同节点可配置不同的表单属性，为区分不同节点，定制不同表单属性时，需要 $nodeId
     *
     * @param string $nodeId 流程的节点id
     * @param BPMTransaction $bpmTransaction 本地bpm交易数据模型，所有动态的表单字都可以基于交易的快照数据进行回查
     * @return array 上下文表单map数组 ['filed1'=>'val1',...]
     */
    public function mapFormFields(string $nodeId, BPMTransaction $bpmTransaction): array;

}
