<?php


namespace app\admin\model;

use app\common\exception\ApiException;
use think\Exception;
use think\Model;
class BaseModel extends Model
{
    public static $formatTime = 'Y-m-d H:i:s';
    //开启时间戳 自动创建
    protected $autoWriteTimestamp = true;

    /**
     *  模型关联
        hasOne：有一个，加上主谓语应该是 ，A 有一个 B
        hasMany：有很多，A 有很多 B
        belongsTo：属于， A 属于 B
        belongsToMany 多对多 belongsToMany('关联模型','中间表','外键','关联键');
     */

    //查询单条记录
    public static function findOne($where,$field="*"){
        // 获取反射表名
        $model = self::getModel();
        $info = $model->where($where)->field($field)->find();
        if($info){
            return $info->toArray();
        }
        return [];
    }

    //查询多条记录
    public static function findAll($where,$field="*",$order="",$limit=''){
        $model = self::getModel();
        $model = $model->where($where)->field($field);
        if($order){
            $model->order($order);
        }
        if($limit){
            $model->limit($limit);
        }
        $list = $model->select();
        if($list){
            return $list->toArray();
        }
        return false;
    }



    //查询记录条数
    public static function findCount($where,$cacheTime=false){
        $model = self::getModel()->where($where);
        if($cacheTime){
            $model = $model->cache(true,$cacheTime);
        }
        return $model->count('*');
    }

    //查询数据统计
    public static function findSum($where,$field,$cacheTime=false){
        $model = self::getModel()->where($where);
        if($cacheTime){
            $model = $model->cache(true,$cacheTime);
        }
        return $model->sum($field);
    }

    //字段追加
    public static function findInc($where,$field,$number=1){
        return self::getModel()->where($where)->inc($field,$number)->update();
    }

    //数据更新
    public static function updates($where,$newData){
        return self::getModel()->where($where)->update($newData);
    }

    //数据删除
    public static function deletes($where){
        return self::getModel()->where($where)->delete();
    }

    //通过模型新增编辑数据
    static function saveData($data){
        $model = self::getModel();
        if(!empty($data['id'])){
            $model = $model::find($data['id']);
        }
        $fieldArr = array_keys($model::$fieldsList);
        foreach ($data as $k=>$v){
            if(!in_array($k,$fieldArr)) continue;
            $model[$k] = $v;
        }
        if($model->save()){
            return $model->id;
        }
        return 0;
    }

    //获取模型表名
    static function getModel($modelName=''){
        if(empty($modelName)){
            $modelName = get_called_class();
            if(is_null($modelName) || !class_exists($modelName)){
                throw new Exception("模型不存在");
            }
        }
        return new $modelName;
    }

}