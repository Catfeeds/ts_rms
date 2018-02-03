<?php
/*
|--------------------------------------------------------------------------
| 人员管理
|--------------------------------------------------------------------------
*/
namespace App\Http\Controllers\Gov;

use App\Http\Model\Dept;
use App\Http\Model\Role;
use App\Http\Model\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends BaseController
{
    /* ++++++++++ 初始化 ++++++++++ */
    public function __construct()
    {

    }

    /* ++++++++++ 列表 ++++++++++ */
    public function index(Request $request)
    {
        /* ********** 查询条件 ********** */
        $select=['id','dept_id','role_id','username','name','phone','email','infos','login_at','login_ip','action_at','created_at','updated_at','deleted_at'];
        /* ********** 查询 ********** */
        DB::beginTransaction();
        try{
            $users=User::withTrashed()
                ->with(['dept'=>function($query){
                    $query->withTrashed()->select(['id','name']);
                },'role'=>function($query){
                    $query->withTrashed()->select(['id','name']);
                }])
                ->select($select)
                ->sharedLock()
                ->paginate();

            if(blank($users)){
                throw new \Exception('没有符合条件的数据',404404);
            }
            $code='success';
            $msg='查询成功';
            $sdata=$users;
            $edata=null;
            $url=null;
        }catch (\Exception $exception){
            $code='error';
            $msg=$exception->getCode()==404404?$exception->getMessage():'网络异常';
            $sdata=null;
            $edata=null;
            $url=null;
        }
        DB::commit();

        /* ++++++++++ 结果 ++++++++++ */
        $result=['code'=>$code,'message'=>$msg,'sdata'=>$sdata,'edata'=>$edata,'url'=>$url];
        if($request->ajax()){
            return response()->json($result);
        }else{
            return view('gov.user.index')->with($result);
        }
    }

    /* ========== 添加 ========== */
    public function add(Request $request){
        $model=new User();

        if($request->isMethod('get')){
            $depts=Dept::select(['id','name'])->get();
            $roles=Role::select(['id','name'])->get();

            $result=['code'=>'success','message'=>'请求成功','sdata'=>['depts'=>$depts,'roles'=>$roles],'edata'=>$model,'url'=>null];
            if($request->ajax()){
                return response()->json($result);
            }else{
                return view('gov.user.add')->with($result);
            }

        }
        /* ++++++++++ 保存 ++++++++++ */
        else{
            /* ++++++++++ 表单验证 ++++++++++ */
            $rules=[
                'dept_id'=>['required','regex:/^[0-9]+$/'],
                'role_id'=>['required','regex:/^[0-9]+$/'],
                'username'=>'required|unique:user',
                'type'=>'required|boolean'
            ];
            $messages=[
                'required'=>':attribute 为必须项',
                'parent_id.regex'=>'错误操作',
                'unique'=>':attribute 已存在',
                'boolean'=>'错误操作',
            ];
            $validator = Validator::make($request->all(),$rules,$messages,$model->columns);
            if($validator->fails()){
                $result=['code'=>'error','message'=>$validator->errors()->first(),'sdata'=>null,'edata'=>null,'url'=>null];
                return response()->json($result);
            }

            /* ++++++++++ 新增 ++++++++++ */
            DB::beginTransaction();
            try{
                /* ++++++++++ 批量赋值 ++++++++++ */
                $user=$model;
                $user->fill($request->input());
                $user->addOther($request);
                $user->save();
                if(blank($user)){
                    throw new \Exception('保存失败',404404);
                }

                $code='success';
                $msg='保存成功';
                $sdata=$user;
                $edata=null;
                $url=route('g_user');

                DB::commit();
            }catch (\Exception $exception){
                $code='error';
                $msg=$exception->getCode()==404404?$exception->getMessage():'保存失败';
                $sdata=null;
                $edata=null;
                $url=null;

                DB::rollBack();
            }
            /* ++++++++++ 结果 ++++++++++ */
            $result=['code'=>$code,'message'=>$msg,'sdata'=>$sdata,'edata'=>$edata,'url'=>$url];
            return response()->json($result);
        }
    }

    /* ========== 详情 ========== */
    public function info(Request $request){
        $id=$request->input('id');
        if(!$id){
            $result=['code'=>'error','message'=>'请先选择数据','sdata'=>null,'edata'=>null,'url'=>null];
            if($request->ajax()){
                return response()->json($result);
            }else{
                return view('gov.error')->with($result);
            }
        }
        /* ********** 获取数据 ********** */
        DB::beginTransaction();
        $user=User::withTrashed()
            ->with(['father'=>function($query){
                $query->withTrashed()->select(['id','name']);
            }])
            ->sharedLock()
            ->find($id);
        DB::commit();
        /* ++++++++++ 数据不存在 ++++++++++ */
        if(blank($user)){
            $code='error';
            $msg='数据不存在';
            $sdata=null;
            $edata=null;
            $url=null;

            $view='gov.error';
        }else{
            $code='success';
            $msg='查询成功';
            $sdata=$user;
            $edata=new User();
            $url=null;

            $view='gov.user.info';
        }
        $result=['code'=>$code,'message'=>$msg,'sdata'=>$sdata,'edata'=>$edata,'url'=>$url];
        if($request->ajax()){
            return response()->json($result);
        }else{
            return view($view)->with($result);
        }
    }

    /* ========== 修改 ========== */
    public function edit(Request $request){
        $id=$request->input('id');
        if(!$id){
            $result=['code'=>'error','message'=>'请先选择数据','sdata'=>null,'edata'=>null,'url'=>null];
            if($request->ajax()){
                return response()->json($result);
            }else{
                return view('gov.error')->with($result);
            }
        }

        if($request->isMethod('get')){
            /* ********** 获取数据 ********** */
            DB::beginTransaction();
            $user=User::withTrashed()
                ->with(['father'=>function($query){
                    $query->withTrashed()->select(['id','name']);
                }])
                ->sharedLock()
                ->find($id);
            DB::commit();
            /* ++++++++++ 数据不存在 ++++++++++ */
            if(blank($user)){
                $code='error';
                $msg='数据不存在';
                $sdata=null;
                $edata=null;
                $url=null;

                $view='gov.error';
            }else{
                $code='success';
                $msg='查询成功';
                $sdata=$user;
                $edata=new User();
                $url=null;

                $view='gov.user.edit';
            }
            $result=['code'=>$code,'message'=>$msg,'sdata'=>$sdata,'edata'=>$edata,'url'=>$url];
            if($request->ajax()){
                return response()->json($result);
            }else{
                return view($view)->with($result);
            }
        }
        /* ********** 保存 ********** */
        else{
            /* ********** 表单验证 ********** */
            $model=new User();
            $rules=[
                'name'=>'required|unique:user,name,'.$id.',id',
                'type'=>'required|boolean'
            ];
            $messages=[
                'required'=>':attribute 为必须项',
                'unique'=>':attribute 已存在',
                'boolean'=>'错误操作',
            ];
            $validator = Validator::make($request->all(),$rules,$messages,$model->columns);
            if($validator->fails()){
                $result=['code'=>'error','message'=>$validator->errors()->first(),'sdata'=>null,'edata'=>null,'url'=>null];
                return response()->json($result);
            }
            /* ********** 更新 ********** */
            DB::beginTransaction();
            try{
                /* ++++++++++ 锁定数据模型 ++++++++++ */
                $user=User::withTrashed()->lockForUpdate()->find($id);
                if(blank($user)){
                    throw new \Exception('数据不存在',404404);
                }
                /* ++++++++++ 处理其他数据 ++++++++++ */
                $user->fill($request->input());
                $user->editOther($request);
                $user->save();
                if(blank($user)){
                    throw new \Exception('修改失败',404404);
                }

                $code='success';
                $msg='保存成功';
                $sdata=$user;
                $edata=null;
                $url=route('g_user');

                DB::commit();
            }catch (\Exception $exception){
                $code='error';
                $msg=$exception->getCode()==404404?$exception->getMessage():'保存失败';
                $sdata=null;
                $edata=$user;
                $url=null;

                DB::rollBack();
            }
            /* ********** 结果 ********** */
            $result=['code'=>$code,'message'=>$msg,'sdata'=>$sdata,'edata'=>$edata,'url'=>$url];
            return response()->json($result);
        }
    }

}