<?php
/**
 * Created by IntelliJ IDEA.
 * User: 1x481n
 * Date: 2022/6/20
 * Time: 4:40 PM
 */

namespace App\Domain\Generic\BPM\Domain\Interface;


use Exception;

interface ShouldValidateInputForm
{
    /**
     * 校验用户输入表单，正确不处理，错误只抛异常
     *
     * @param array $formData 用户输入的表单数据
     * @return void
     * @throws Exception
     */
    public function validateInputForm(array $formData): void;
}
