<?php
/*
|--------------------------------------------------------------------------
| 项目-内部通知 模型
|--------------------------------------------------------------------------
*/
namespace App\Http\Model;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Itemnotice extends Model
{
    use SoftDeletes;
    protected $table='item_notice';
    protected $primaryKey='id';
    protected $fillable=['infos','picture'];
    protected $dates=['created_at','updated_at','deleted_at'];
    protected $casts = [
        'picture'=>'array'
    ];
    /* ++++++++++ 数据字段注释 ++++++++++ */
    public $columns=[
        'item_id'=>'项目',
        'cate_id'=>'分类',
        'infos'=>'通知摘要',
        'picture'=>'通知书'
    ];

    /* ++++++++++ 设置添加数据 ++++++++++ */
    public function addOther($request){
        $this->attributes['item_id'] = $request->input('item_id');
        $this->attributes['cate_id'] = $request->input('cate_id');
    }
    /* ++++++++++ 设置修改数据 ++++++++++ */
    public function editOther($request){

    }

    /* ++++++++++ 关联项目 ++++++++++ */
    public function item(){
        return $this->belongsTo('App\Http\Model\Item','item_id','id')->withDefault();
    }
    /* ++++++++++ 关联分类 ++++++++++ */
    public function newscate(){
        return $this->belongsTo('App\Http\Model\Newscate','cate_id','id')->withDefault();
    }
}