{{-- 继承布局 --}}
@extends('gov.main')


{{-- 页面内容 --}}
@section('content')

    <p>
        <a class="btn" href="javascript:history.back()">
            <i class="ace-icon fa fa-arrow-left bigger-110"></i>
            返回
        </a>

        <a class="btn" href="{{route('g_houselayoutimg_edit',['id'=>$sdata->id])}}">
            <i class="ace-icon fa fa-pencil-square-o bigger-110"></i>
            修改
        </a>
    </p>


    <div class="profile-user-info profile-user-info-striped">
        <div class="profile-info-row">
            <div class="profile-info-name"> 房源社区： </div>
            <div class="profile-info-value">
                <span class="editable editable-click">{{$sdata->housecommunity->name}}</span>
            </div>
        </div>

        <div class="profile-info-row">
            <div class="profile-info-name"> 房源户型： </div>
            <div class="profile-info-value">
                <span class="editable editable-click">{{$sdata->layout->name}}</span>
            </div>
        </div>

        <div class="profile-info-row">
            <div class="profile-info-name"> 名称： </div>
            <div class="profile-info-value">
                <span class="editable editable-click">{{$sdata->name}}</span>
            </div>
        </div>

        <div class="profile-info-row">
            <div class="profile-info-name"> 户型图： </div>
            <div class="profile-info-value">
                <span class="editable editable-click">
                <ul class="ace-thumbnails clearfix img-content viewer">
                        <li>
                            <div>
                                <img width="120" height="120" src="{!! $sdata->picture !!}" alt="加载失败">
                                <div class="text">
                                    <div class="inner">
                                        <a onclick="preview(this)"><i class="fa fa-search-plus"></i></a>
                                    </div>
                                </div>
                            </div>
                        </li>
                </ul>
                </span>
            </div>
        </div>

        <div class="profile-info-row">
            <div class="profile-info-name"> 创建时间： </div>
            <div class="profile-info-value">
                <span class="editable editable-click">{{$sdata->created_at}}</span>
            </div>
        </div>

        <div class="profile-info-row">
            <div class="profile-info-name"> 更新时间： </div>
            <div class="profile-info-value">
                <span class="editable editable-click">{{$sdata->updated_at}}</span>
            </div>
        </div>

        <div class="profile-info-row">
            <div class="profile-info-name"> 状态： </div>
            <div class="profile-info-value">
                <span class="editable editable-click"> @if($sdata->deleted_at) 已删除 @else 启用中 @endif</span>
            </div>
        </div>

        <div class="profile-info-row">
            <div class="profile-info-name"> 删除时间： </div>
            <div class="profile-info-value">
                <span class="editable editable-click">{{$sdata->deleted_at}}</span>
            </div>
        </div>

    </div>

@endsection

{{-- 样式 --}}
@section('css')

@endsection

{{-- 插件 --}}
@section('js')


@endsection