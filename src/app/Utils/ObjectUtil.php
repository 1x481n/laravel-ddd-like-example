<?php


namespace App\Utils;



class ObjectUtil
{

    /**
     * 对象浅拷贝,直接clone，无法修改对象的类型。
     * @param Object $source
     * @param Object $target
     * @return bool
     */
    public static function copyProperties(object $source, object $target): bool
    {
        if(!is_object($source) || !is_object($target)){
            throw new \RuntimeException('source and target must be an object type!');
        }
        foreach(get_object_vars($source) as $key=>$val){
            $target->$key = $val;
        }
        return true;
    }

    /**
     * @param array $array
     * @param object $target
     * @return bool
     */
    public static function copyFromArray(array $array,object $target): bool
    {
        if(!is_object($target)){
            throw new \RuntimeException('target must be an object type!');
        }
        foreach($array as $key=>$val){
            $target->$key = $val;
        }
        return true;
    }
}
