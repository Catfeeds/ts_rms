<?php
/*
|--------------------------------------------------------------------------
| 项目通知分类
|--------------------------------------------------------------------------
*/
namespace App\Http\Controllers\System;
use App\Http\Model\Itemnoticecate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemnoticecateController extends BaseController
{
    /* ++++++++++ 初始化 ++++++++++ */
    public function __construct()
    {

    }

    /* ++++++++++ 首页 ++++++++++ */
    public function index(Request $request){
        $select = ['id','name','infos','deleted_at'];
        /* ********** 查询条件 ********** */
        $where=[];
        /* ++++++++++ 名称 ++++++++++ */
        $name=trim($request->input('name'));
        if($name){
            $where[]=['name','like','%'.$name.'%'];
            $infos['name']=$name;
        }
        /* ********** 排序 ********** */
        $ordername=$request->input('ordername');
        $ordername=$ordername?$ordername:'id';
        $infos['ordername']=$ordername;

        $orderby=$request->input('orderby');
        $orderby=$orderby?$orderby:'asc';
        $infos['orderby']=$orderby;
        /* ********** 每页条数 ********** */
        $nums=[15,30,50,100,200];
        $infos['nums']=$nums;
        $displaynum=$request->input('displaynum');
        $displaynum=$displaynum?$displaynum:15;
        $infos['displaynum']=$displaynum;
        /* ********** 是否删除 ********** */
        $deleted=$request->input('deleted');

        $model=new Itemnoticecate();
        if(is_numeric($deleted) && in_array($deleted,[0,1])){
            $infos['deleted']=$deleted;
            if($deleted){
                $model=$model->onlyTrashed();
            }
        }else{
            $model=$model->withTrashed();
        }

        /* ********** 查询 ********** */
        DB::beginTransaction();
        try{
            $itemnoticecates=$model->where($where)->select($select)->orderBy($ordername,$orderby)->sharedLock()->paginate($displaynum);
            if(blank($itemnoticecates)){
                throw new \Exception('没有符合条件的数据',404404);
            }

            $code='success';
            $msg='查询成功';
            $data=$itemnoticecates;
            $url='';
        }catch (\Exception $exception){
            $itemnoticecates=collect();
            $code='error';
            $msg=$exception->getCode()==404404?$exception->getMessage():'网络异常';
            $data=$itemnoticecates;
            $url='';
        }
        DB::commit();
        $infos['itemnoticecates']=$itemnoticecates;
        $infos[$code]=$msg;

        /* ********** 结果 ********** */
        if($request->ajax()){
            return response()->json(['code'=>$code,'message'=>$msg,'sdata'=>$data,'edata'=>'','url'=>$url]);
        }else{
            return view('system.itemnoticecate.index',$infos);
        }
    }

    /* ========== 添加 ========== */
    public function add(Request $request){
        $model=new Itemnoticecate();
        /* ********** 保存 ********** */
        if($request->isMethod('post')){
            /* ++++++++++ 表单验证 ++++++++++ */
            $rules=[
                'name'=>'required|unique:a_item_notice_cate',
            ];
            $messages=[
                'required'=>':attribute 为必须项',
                'unique'=>':attribute 已存在'
            ];

            $this->validate($request,$rules,$messages,$model->columns);

            /* ++++++++++ 新增 ++++++++++ */
            DB::beginTransaction();
            try{
                /* ++++++++++ 批量赋值 ++++++++++ */
                $itemnoticecate=$model;
                $itemnoticecate->fill($request->input());
                $itemnoticecate->setOther($request);
                $itemnoticecate->save();
                if(blank($itemnoticecate)){
                    throw new \Exception('添加失败',404404);
                }
                $code='success';
                $msg='添加成功';
                $data=$itemnoticecate;
                $url='';
                DB::commit();
            }catch (\Exception $exception){
                $code='error';
                $msg=$exception->getCode()==404404?$exception->getMessage():'添加失败';
                $data=[];
                $url='';
                DB::rollBack();
            }
            /* ++++++++++ 结果 ++++++++++ */
            if($request->ajax()){
                return response()->json(['code'=>$code,'message'=>$msg,'sdata'=>$data,'edata'=>'','url'=>$url]);
            }else{
                return redirect()->back()->withInput()->with($code,$msg);
            }
        }
        /* ********** 视图 ********** */
        else{
            /* ++++++++++ 输出视图 ++++++++++ */
            return view('system.itemnoticecate.add');
        }
    }

    /* ========== 详情 ========== */
    public function info(Request $request){
        $id = $request->input('id');
        if(!$id){
            $code='warning';
            $msg='请选择一条数据';
            return response()->json(['code'=>$code,'message'=>$msg,'sdata'=>'','edata'=>'','url'=>'']);
        }
        /* ********** 当前数据 ********** */
        DB::beginTransaction();
        $itemnoticecate=Itemnoticecate::withTrashed()
            ->sharedLock()
            ->find($id);

        DB::commit();
        /* ++++++++++ 数据不存在 ++++++++++ */
        if(blank($itemnoticecate)){
            $code='warning';
            $msg='数据不存在';
            $data=[];
            $url='';
        }else{
            $code='success';
            $msg='获取成功';
            $data=$itemnoticecate;
            $url='';
        }
        $infos=[

            'code'=>$code,
            'msg'=>$msg,
            'sdata'=>$data,
            'edata'=>'',
            'url'=>$url,
        ];

        /* ********** 输出视图 ********** */
        return view('system.itemnoticecate.info',$infos);
    }

    /* ========== 修改 ========== */
    public function edit(Request $request){
        $id = $request->input('id');
        if(!$id){
            $code='warning';
            $msg='请选择一条数据';
            return response()->json(['code'=>$code,'message'=>$msg,'sdata'=>'','edata'=>'','url'=>'']);
        }
        $model=new Itemnoticecate();
        if($request->isMethod('post')){
            /* ********** 表单验证 ********** */
            $rules=[
                'name'=>'required|unique:a_item_notice_cate,name,'.$id.',id'
            ];
            $messages=[
                'required'=>':attribute 为必须项',
                'unique'=>':attribute 已存在'
            ];
            $this->validate($request,$rules,$messages,$model->columns);

            /* ********** 更新 ********** */
            DB::beginTransaction();
            try{
                /* ++++++++++ 锁定数据模型 ++++++++++ */
                $itemnoticecate=Itemnoticecate::withTrashed()
                    ->lockForUpdate()
                    ->find($id);

                if(blank($itemnoticecate)){
                    throw new \Exception('指定数据项不存在',404404);
                }
                /* ++++++++++ 处理其他数据 ++++++++++ */
                $itemnoticecate->fill($request->input());
                $itemnoticecate->setOther($request);
                $itemnoticecate->save();
                if(blank($itemnoticecate)){
                    throw new \Exception('修改失败',404404);
                }
                $code='success';
                $msg='修改成功';
                $data=$itemnoticecate;
                $url='';
                DB::commit();
            }catch (\Exception $exception){

                $code='error';
                $msg=$exception->getCode()==404404?$exception->getMessage():'网络异常';
                $data=[];
                $url='';
                DB::rollBack();
            }
            /* ********** 结果 ********** */
            if($request->ajax()){
                return response()->json(['code'=>$code,'message'=>$msg,'sdata'=>$data,'edata'=>'','url'=>$url]);
            }else{
                return redirect()->back()->withInput()->with($code,$msg);
            }
        }else{
            /* ********** 当前数据 ********** */
            DB::beginTransaction();
            $itemnoticecate=Itemnoticecate::withTrashed()
                ->sharedLock()
                ->find($id);

            DB::commit();
            /* ++++++++++ 数据不存在 ++++++++++ */
            if(blank($itemnoticecate)){
                $code='warning';
                $msg='数据不存在';
                $data=[];
                $url='';
            }else{
                $code='success';
                $msg='获取成功';
                $data=$itemnoticecate;
                $url='';
            }
            $infos=[
                'code'=>$code,
                'msg'=>$msg,
                'sdata'=>$data,
                'edata'=>'',
                'url'=>$url
            ];

            /* ********** 输出视图 ********** */
            return view('system.itemnoticecate.edit',$infos);
        }

    }
}