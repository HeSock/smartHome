<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <meta name="renderer" content="webkit|ie-comp|ie-stand">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <link rel="stylesheet" type="text/css" href="http://static.zrzsh5.91xy.com/admin/login/H-ui.css" />
    <link rel="stylesheet" type="text/css" href="http://static.zrzsh5.91xy.com/admin/login/iconfont.css" />
    <link rel="stylesheet" type="text/css" href="http://static.zrzsh5.91xy.com/admin/login/H-ui.login.css" />

    <title>{{ getenv('CHANNEL_NAME') }}</title>
</head>
<body>
<div class="header">
    <h1 style="text-indent: 20px; font-size: 20px; color:#fff;">{{ getenv('CHANNEL_NAME') }}</h1>
</div>
<div class="loginWraper">
    <div id="loginform" class="loginBox">
        <form class="form form-horizontal" method="POST" action="{{ route('login') }}">
            {{ csrf_field() }}
            <div class="row cl">
                <label class="form-label col-xs-3"><i class="Hui-iconfont">账号</i></label>
                <div class="formControls col-xs-8">
                    <input  name="email" type="email" placeholder="账户" value="{{ old('email') }}" class="input-text size-L" style="width:200px;" required autofocus>
                    <span id="check-name" class="c-red check"></span>
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-3"><i class="Hui-iconfont">密码</i></label>
                <div class="formControls col-xs-8">
                    <input  name="password" type="password" placeholder="密码" class="input-text size-L" style="width:200px;" required>
                    <span id="check-pwd" class="c-red check"></span>
                    @if ($errors->has('password'))
                        <span class="help-block">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                </div>

            </div>
            <div class="row cl" >
                <div class="formControls col-xs-8 col-xs-offset-3">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> 记住密码
                        </label>
                    </div>
                </div>
            </div>
            <div class="row cl">
                <div class="formControls col-xs-8 col-xs-offset-3">
                    <input type="submit" id="subtn" class="btn btn-success radius size-L" value="&nbsp;登&nbsp;&nbsp;&nbsp;&nbsp;录&nbsp;">
                    <input  type="reset" class="btn btn-default radius size-L" value="&nbsp;取&nbsp;&nbsp;&nbsp;&nbsp;消&nbsp;">
                </div>
            </div>
        </form>
    </div>
</div>
<div class="footer">bearjoy 游戏后台</div>
</body>
</html>