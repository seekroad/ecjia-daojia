<?php
//
//    ______         ______           __         __         ______
//   /\  ___\       /\  ___\         /\_\       /\_\       /\  __ \
//   \/\  __\       \/\ \____        \/\_\      \/\_\      \/\ \_\ \
//    \/\_____\      \/\_____\     /\_\/\_\      \/\_\      \/\_\ \_\
//     \/_____/       \/_____/     \/__\/_/       \/_/       \/_/ /_/
//
//   上海商创网络科技有限公司
//
//  ---------------------------------------------------------------------------------
//
//   一、协议的许可和权利
//
//    1. 您可以在完全遵守本协议的基础上，将本软件应用于商业用途；
//    2. 您可以在协议规定的约束和限制范围内修改本产品源代码或界面风格以适应您的要求；
//    3. 您拥有使用本产品中的全部内容资料、商品信息及其他信息的所有权，并独立承担与其内容相关的
//       法律义务；
//    4. 获得商业授权之后，您可以将本软件应用于商业用途，自授权时刻起，在技术支持期限内拥有通过
//       指定的方式获得指定范围内的技术支持服务；
//
//   二、协议的约束和限制
//
//    1. 未获商业授权之前，禁止将本软件用于商业用途（包括但不限于企业法人经营的产品、经营性产品
//       以及以盈利为目的或实现盈利产品）；
//    2. 未获商业授权之前，禁止在本产品的整体或在任何部分基础上发展任何派生版本、修改版本或第三
//       方版本用于重新开发；
//    3. 如果您未能遵守本协议的条款，您的授权将被终止，所被许可的权利将被收回并承担相应法律责任；
//
//   三、有限担保和免责声明
//
//    1. 本软件及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的；
//    2. 用户出于自愿而使用本软件，您必须了解使用本软件的风险，在尚未获得商业授权之前，我们不承
//       诺提供任何形式的技术支持、使用担保，也不承担任何因使用本软件而产生问题的相关责任；
//    3. 上海商创网络科技有限公司不对使用本产品构建的商城中的内容信息承担责任，但在不侵犯用户隐
//       私信息的前提下，保留以任何方式获取用户信息及商品信息的权利；
//
//   有关本产品最终用户授权协议、商业授权与技术服务的详细内容，均由上海商创网络科技有限公司独家
//   提供。上海商创网络科技有限公司拥有在不事先通知的情况下，修改授权协议的权力，修改后的协议对
//   改变之日起的新授权用户生效。电子文本形式的授权协议如同双方书面签署的协议一样，具有完全的和
//   等同的法律效力。您一旦开始修改、安装或使用本产品，即被视为完全理解并接受本协议的各项条款，
//   在享有上述条款授予的权力的同时，受到相关的约束和限制。协议许可范围以外的行为，将直接违反本
//   授权协议并构成侵权，我们有权随时终止授权，责令停止损害，并保留追究相关责任的权力。
//
//  ---------------------------------------------------------------------------------
//
RC_Loader::load_app_class('platform_interface', 'platform', false);
class mp_userbind_bind_callback implements platform_interface {
    
    public function action() {
    	$wecaht_user_db = RC_Loader::load_app_model('wecaht_user', 'wechat');
    	$code = $_GET['code'];
    	$uuid = $_GET['uuid'];
    	RC_Loader::load_app_class('wechat_method', 'wechat', false);
    	$wechat = wechat_method::wechat_instance($uuid);
    	
    	$WebAccessToken = $wechat->getWebToken($code);//通过code换取网页授权access_token
    	$openid = $WebAccessToken['openid'];
    	$wechat->refreshWebToken($WebAccessToken);//刷新access_token
    	$wechat->getWebUserInfo($openid, $WebAccessToken);//获取用户信息
    	$wechat->authWebToken($openid, $WebAccessToken);//检验授权凭证（access_token）是否有效
   
    	$wecaht_user_db = RC_Loader::load_app_model('wechat_user_model', 'wechat');
    	$user_db = RC_Loader::load_app_model('users_model', 'user');
    	RC_Loader::load_app_class('platform_account', 'platform', false);
    	$account = platform_account::make($uuid);
    	$wechat_id = $account->getAccountID();
    	$ect_uid  = $wecaht_user_db->where(array('wechat_id' => $wechat_id,'openid' => $openid))->get_field('ect_uid');
    	if (!empty($ect_uid)) {
    		$user_info = $user_db->field('user_name, email, user_id')->find(array('user_id' => $ect_uid));
    	} else {
    		RC_Loader::load_app_class('wechat_user', 'wechat', false);
    		$wechat_user = new wechat_user($wechat_id, $openid);
    		$username  = $wechat_user->getNickname();
    		$password  = wechat_user::generate_password();
    		$email     = wechat_user::generate_email();
    		$sex       = $wechat_user->sex();
    		$reg_time  = RC_Time::gmtime();
    		$user      = RC_Api::api('user', 'init_user');
    		if ($user && $user->check_user($username)) {
    			$username = $username . rc_random(4, 'abcdefghijklmnopqrstuvwxyz0123456789');
    		}
    		$user_info = RC_Api::api('user', 'add_user', array('username' => $username, 'password' => $password, 'email' => $email, 'sex'=>$sex, 'reg_time'=>$reg_time));
    		$user_id = $user_info['user_id'];
    		$wechat_user->setUserId($user_id);
    	}
 
    	$_SESSION['user_id']   = $user_info['user_id'];
    	$_SESSION['user_name'] = $user_info['user_name'];
    	$_SESSION['email']     = $user_info['email'];
    	$_SESSION['last_ip']   = RC_Ip::client_ip();
    	$_SESSION['last_time'] = RC_Time::gmtime();
    	$user_db->where(array('user_id' => $user_info['user_id']))->update(array('last_login' => RC_Time::gmtime(), 'last_ip'=>RC_Ip::client_ip()));
    	$session_id = RC_Session::session_id();
    	header("Location: http://test.b2c.ecjia.com/sites/weshop/index.php?m=touch&c=index&a=init&token=".$session_id);
    	exit;
    }
}

// end