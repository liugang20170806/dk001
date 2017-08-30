<?php
// Routes

// Render Twig template in route
$app->get('/', function ($request, $response, $args) {
    return $this->view->render($response, 'index.html');
})->setName('index');

$app->post('/login', function ($request, $response, $args) use ($app,$container) {
    $post =  $request->getParsedBody();
    $user = $container->get('db')->table('user');
    $data = $user->where('phone', $post['phone'])->first();
    if($data){
        if(md5($post['password']) == $data['password']){
            $_SESSION['user'] = $data;
            return $response->withRedirect('userinfo');
        }else{
            die('密码错误');
        }
    }else{
        return $response->withRedirect('/error/用户不存在');
    }
});

$app->get('/logout', function ($request, $response, $args) {
    unset($_SESSION['user']);
    return $response->withRedirect('/');
})->setName('find_password');

$app->post('/regist', function ($request, $response, $args) use ($app,$container) {
    $post =  $request->getParsedBody();
    if($post){
        if($_SESSION['smscode'] != $post['code']){
            return $response->withRedirect('/error/手机验证码错误');
        }

        $user =  $container->get('db')->table('user');
        $data = array(
            'phone' => $post['phone'],
            'password' => md5($post['password']),
        );
        if(!$user->where('phone',$post['phone'])->first()) {
            $user->insert($data);
            unset($_SESSION['smscode']);
            return $this->view->render($response, 'userinfo.html');
        }else{
            return $response->withRedirect('/error/用户已存在');
        }
    }else{
        return $response->withRedirect('/');
    }
});

$app->post('/find_password', function ($request, $response, $args) use ($app,$container) {
    $post =  $request->getParsedBody();
    $user = $container->get('db')->table('user');
    if($post){
        if($_SESSION['smscode'] != $post['code']){
            return $response->withRedirect('/error/手机验证码错误');
        }

        $user =  $container->get('db')->table('user');
        $data = array(
            'password' => md5($post['password']),
        );
        if($user->where('phone',$post['phone'])->first()) {
            $user->where('phone', $post['phone'])->update($data);
            return $response->withRedirect('/error/密码修改成功，请重新登录');
        }else{
            return $response->withRedirect('/error/用户不存在');
        }
    }else{
        return $response->withRedirect('/');
    }
});

// $app->group('/auth', function () {
//     $this->map(['GET', 'POST'], '/login', 'Controllers\AuthController:login');
//     $this->map(['GET', 'POST'], '/logout', 'Controllers\AuthController:logout');
//     $this->map(['GET', 'POST'], '/signup', 'Controllers\AuthController:signup');
// });

$app->get('/find_password', function ($request, $response, $args) {
    return $this->view->render($response, 'find_password.html');
})->setName('find_password');

$app->get('/userinfo', function ($request, $response, $args) use ($app,$container) {
    if(empty($_SESSION['user'])){
        return $response->withRedirect('/');
    }else{
        $province = $container->get('db')->table('areas')->where('parent_id',0)->get();

        $data['user_id'] = $_SESSION['user']['id'];
        $info =  $container->get('db')->table('userinfo');
        $userinfo = $info->where('user_id',$data['user_id'])->first();
        $done = ($userinfo) ? 1:0;
        $province_json = array();
        foreach($province as $k => $v){
            $province_json[$k]['name'] = $v['area_name'];
            $province_json[$k]['value'] = $v['area_id'];
            if($v['area_id'] == $userinfo['area_1']) {
                $province_json[$k]['selected'] = true;
            }
        }
        $province_json = json_encode($province_json,JSON_UNESCAPED_UNICODE);
        return $this->view->render($response, 'userinfo.html',['done'=>$done,'info'=>$userinfo,'province'=>$province, 'province_json'=>$province_json]);
    }
})->setName('userinfo');

$app->post('/userinfo/update', function ($request, $response, $args) use ($app,$container) {
    if(empty($_SESSION['user'])){
        return $response->withRedirect('/');
    }else{
        $data = $request->getParsedBody();
        $data['user_id'] = $_SESSION['user']['id'];
        
        $info =  $container->get('db')->table('userinfo');
        if(!$info->where('user_id',$data['user_id'])->first()) $info->insert($data);
        return $response->withRedirect('/userinfo');
    }
})->setName('userinfo');

$app->get('/inquire_progress', function ($request, $response, $args) use ($app,$container) {
    if(empty($_SESSION['user'])){
        return $response->withRedirect('/');
    }else{
        $info =  $container->get('db')->table('userinfo');
        $userinfo = $info->where('user_id',$_SESSION['user']['id'])->first();
        return $this->view->render($response, 'inquire_progress.html',['userinfo'=>$userinfo]);
    }
});




// area generate
$app->get('/area/area_1', function ($request, $response, $args) use ($app,$container) {
    $province = $container->get('db')->table('areas')->where('parent_id',0)->get();
    $area_1 = array();
    foreach ($province as $k => $v) {
        $area_1[$k]['name'] = $v['area_name'];
        $area_1[$k]['text'] = $v['area_name'];
        $area_1[$k]['value'] = $v['area_id'];
    }
    $res = array(
        "success" => true,
        "results" => $area_1
    );
    return json_encode($res,JSON_UNESCAPED_UNICODE);
})->setName('/area/area_1');
$app->get('/area/area_sub[/{parent_id}[/{area_id}]]', function ($request, $response, $args) use ($app,$container)    {
    $city = $container->get('db')->table('areas')->where('parent_id',$args['parent_id'])->get();
    $area_2 = array();
    foreach ($city as $k => $v) {
        $area_2[$k]['name'] = $v['area_name'];
        $area_2[$k]['value'] = $v['area_id'];
        if (isset($args['area_id']) && $args['area_id'] == $v['area_id']) $area_2[$k]['selected'] = true;
    }
    // if (!isset($args['area_id'])) $area_2[1]['selected'] = true; 
 
    return json_encode($area_2,JSON_UNESCAPED_UNICODE);
})->setName('/area/area_sub');



$app->get('/error/[{msg}]', function ($request, $response, $args) {
    // Render index view
    return $this->view->render($response, 'error.html',  [
        'msg' => $args['msg']
    ]);
})->setName('error');


$app->get('/send_code/[{phone}]', function ($request, $response, $args) {
    if($args['phone']){
        $clapi  = new ChuanglanSmsApi();
        $code = mt_rand(1000,9999);
        /**
        * 调用短信方法  
        * 如果需要发送多个手机号码 请用英文逗号","隔开
        * 签名需在平台审核通过后 在短信内容前面添加
        */
        $result = $clapi->sendSMS($args['phone'], '您好，您的验证码是'. $code);
        $result = $clapi->execResult($result);
        if(isset($result[1]) && $result[1] == '0'){
            $_SESSION['smscode'] = $code;
            return json_encode(array('msg'=> 'success'),JSON_UNESCAPED_UNICODE);
        }else{
            return json_encode(array('msg'=>"failed"),JSON_UNESCAPED_UNICODE);
        }
    }
   
  
})->setName('error');
