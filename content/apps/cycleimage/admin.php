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
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * ECJia轮播图管理控制器
 * @author songqian
 */
class admin extends ecjia_admin {
	private $cycle;
	public function __construct() {
		parent::__construct();

		$this->cycle = RC_Loader::load_app_class('cycleimage_method');

		if (!ecjia::config(cycleimage_method::STORAGEKEY_cycleimage_data, ecjia::CONFIG_CHECK)) {
			ecjia_config::instance()->insert_config('hidden', cycleimage_method::STORAGEKEY_cycleimage_data, serialize(array()), array('type' => 'hidden'));
		}
		if (!ecjia::config(cycleimage_method::STORAGEKEY_cycleimage_style, ecjia::CONFIG_CHECK)) {
		    ecjia_config::instance()->insert_config('hidden', cycleimage_method::STORAGEKEY_cycleimage_style, '', array('type' => 'hidden'));
		}

		RC_Loader::load_app_func('global');
		assign_adminlog_content();

		RC_Script::enqueue_script('jquery-form');
		RC_Script::enqueue_script('jquery-validate');
		RC_Script::enqueue_script('smoke');
		RC_Script::enqueue_script('bootstrap-placeholder');
		RC_Script::enqueue_script('bootstrap-editable.min', RC_Uri::admin_url('statics/lib/x-editable/bootstrap-editable/js/bootstrap-editable.min.js'));
		RC_Style::enqueue_style('bootstrap-editable', RC_Uri::admin_url('statics/lib/x-editable/bootstrap-editable/css/bootstrap-editable.css'));
		RC_Script::enqueue_script('cycleimage', RC_App::apps_url('statics/js/cycleimage.js', __FILE__), array(), false, false);
		
		RC_Script::localize_script('cycleimage', 'js_lang', RC_Lang::get('cycleimage::flashplay.js_lang'));
		
		ecjia_screen::$current_screen->add_nav_here(new admin_nav_here(RC_Lang::get('cycleimage::flashplay.cycle_image_list'), RC_Uri::url('cycleimage/admin/init')));
	}

	/**
	 * 列表页
	 */
	public function init () {
		$this->admin_priv('flash_manage');
		
		ecjia_screen::$current_screen->remove_last_nav_here();
		ecjia_screen::$current_screen->add_nav_here(new admin_nav_here(RC_Lang::get('cycleimage::flashplay.cycle_image_list')));
		ecjia_screen::get_current_screen()->add_help_tab(array(
			'id'		=> 'overview',
			'title'		=> RC_Lang::get('cycleimage::flashplay.overview'),
			'content'	=> '<p>' . RC_Lang::get('cycleimage::flashplay.clcyeimage_list_help') . '</p>'
		));

		ecjia_screen::get_current_screen()->set_help_sidebar(
			'<p><strong>' . RC_Lang::get('cycleimage::flashplay.more_info') . '</strong></p>' .
			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:轮播图管理" target="_blank">'.RC_Lang::get('cycleimage::flashplay.about_cycleimage_list').'</a>') . '</p>'
		);
		
		$this->assign('ur_here', RC_Lang::get('cycleimage::flashplay.cycle_image_list'));
		$this->assign('action_link_special', array('text' => RC_Lang::get('cycleimage::flashplay.add_cycle_image'), 'href' => RC_Uri::url('cycleimage/admin/add')));
		
		$playerdb  = $this->cycle->player_data(true);
		$flashtpls = $this->cycle->cycle_list();
		$this->assign('flashtpls', $flashtpls);
		$this->assign('playerdb', $playerdb);
		
		$this->assign('uri', RC_Uri::site_url());
		$this->assign('current_flashtpl', ecjia::config(cycleimage_method::STORAGEKEY_cycleimage_style));
		
		$this->display('cycleimage_list.dwt');
	}

	/**
	 * 添加及提交处理
	 */
	public function add() {
		if (empty($_POST['step'])) {
		    $this->admin_priv('flash_update');
		    
			ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('cycleimage::flashplay.add_cycle_image')));
			ecjia_screen::get_current_screen()->add_help_tab(array(
				'id'		=> 'overview',
				'title'		=> RC_Lang::get('cycleimage::flashplay.overview'),
				'content'	=> '<p>' . RC_Lang::get('cycleimage::flashplay.add_cycleimage_help') . '</p>'
			));

			ecjia_screen::get_current_screen()->set_help_sidebar(
				'<p><strong>' . RC_Lang::get('cycleimage::flashplay.more_info') . '</strong></p>' .
				'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:轮播图管理" target="_blank">'.RC_Lang::get('cycleimage::flashplay.about_add_cycleimage').'</a>') . '</p>'
			);
			
			$this->assign('ur_here', RC_Lang::get('cycleimage::flashplay.add_cycle_image'));
			$this->assign('action_link', array('text' => RC_Lang::get('cycleimage::flashplay.cycle_image_list'), 'href' => RC_Uri::url('cycleimage/admin/init')));
			
			$url  = isset($_GET['url']) ? $_GET['url'] : 'http://';
			$src  = isset($_GET['src']) ? $_GET['src'] : '';
			$sort = 0;
			$rt   = array(
				'img_url'	=> $url,
				'img_src'	=> $src,
				'img_sort'	=> $sort
			);
			$this->assign('rt', $rt);
			
			$this->assign('form_action', RC_Uri::url('cycleimage/admin/add'));
			
			$this->display('cycleimage_edit.dwt');
		}
		// 提交表单处理
		elseif ($_POST['step'] == 2) {
		    $this->admin_priv('flash_update', ecjia::MSGTYPE_JSON);
		    
			if (!empty($_FILES['img_file_src']['name'])) {
				$upload = RC_Upload::uploader('image', array('save_path' => 'data/afficheimg', 'auto_sub_dirs' => false));
				$info = $upload->upload($_FILES['img_file_src']);
				if (!empty($info)) {
					$src = $upload->get_position($info);
				} else {
					return $this->showmessage($upload->error(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
				}
			}
			// 直接填写图片地址
			elseif (!empty($_POST['img_src'])) {
				$src = $_POST['img_src'];
				if (strstr($src, 'http') && !strstr($src, $_SERVER['SERVER_NAME'])) {
					$src = get_url_image($src);
				}
			}
			// 图片地址不能为空
			else {
				return $this->showmessage(RC_Lang::get('cycleimage::flashplay.pls_upload_image'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}

			if (empty($_POST['img_url'])) {
				return $this->showmessage(RC_Lang::get('cycleimage::flashplay.link_empty'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
			// 获取flash播放器数据
			$flashdb = $this->cycle->player_data();
			// 插入新数据
			$cycid = array_unshift($flashdb, array('src'=>$src, 'url'=>$_POST['img_url'], 'text'=>$_POST['img_text'], 'sort'=>$_POST['img_sort']));
			// 实现排序
			$flashdb_sort   = array();
			$_flashdb       = array();
			
			foreach ($flashdb as $key => $value) {
				$flashdb_sort[$key] = $value['sort'];
			}
			asort($flashdb_sort, SORT_NUMERIC);

			foreach ($flashdb_sort as $key => $value) {
				$_flashdb[] = $flashdb[$key];
			}
			unset($flashdb, $flashdb_sort);

			$_flashdb = serialize($_flashdb);
			ecjia_config::instance()->write_config(cycleimage_method::STORAGEKEY_cycleimage_data, $_flashdb);

			$links[] = array('text' => RC_Lang::get('cycleimage::flashplay.cycle_image_list'), 'href' => RC_Uri::url('cycleimage/admin/init'));

			ecjia_admin::admin_log($_POST['img_text'], 'add', 'cycleimage');
			return $this->showmessage(RC_Lang::get('cycleimage::flashplay.add_success'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('links' => $links, 'pjaxurl' => RC_Uri::url('cycleimage/admin/add')));
		}
	}

	/**
	 * 编辑及提交处理
	 */
	public function edit() {
		$this->admin_priv('flash_update', ecjia::MSGTYPE_JSON);

		$allow_suffix = array('gif', 'jpg', 'png', 'jpeg', 'bmp');

		$id       = !empty($_GET['id']) ? intval($_GET['id']) : 0; //取得id
		$flashdb  = $this->cycle->player_data();
		
		if (isset($flashdb[$id])) {
			$rt = $flashdb[$id];
		} else {
			$links[] = array('text' => RC_Lang::get('cycleimage::flashplay.cycle_image_list'), 'href' => RC_Uri::url('cycleimage/admin/init'));
			return $this->showmessage(RC_Lang::get('cycleimage::flashplay.id_error'), ecjia::MSGTYPE_HTML | ecjia::MSGSTAT_ERROR, array('links' => $links));
		}
		
		if (empty($_POST['step'])) {
			ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('cycleimage::flashplay.edit_cycle_image')));
			ecjia_screen::get_current_screen()->add_help_tab(array(
				'id'		=> 'overview',
				'title'		=> RC_Lang::get('cycleimage::flashplay.overview'),
				'content'	=> '<p>' . RC_Lang::get('cycleimage::flashplay.edit_cycleimage_help') . '</p>'
			));

			ecjia_screen::get_current_screen()->set_help_sidebar(
				'<p><strong>' . RC_Lang::get('cycleimage::flashplay.more_info') . '</strong></p>' .
				'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:轮播图管理" target="_blank">'.RC_Lang::get('cycleimage::flashplay.about_edit_cycleimage').'</a>') . '</p>'
			);
			
			$this->assign('ur_here', RC_Lang::get('cycleimage::flashplay.edit_cycle_image'));
			$this->assign('action_link', array('text' => RC_Lang::get('cycleimage::flashplay.cycle_image_list'), 'href' => RC_Uri::url('cycleimage/admin/init')));
			
			$rt['img_url']  = $rt['url'];
			$rt['img_src']  = $rt['src'];
			$rt['img_txt']  = $rt['text'];
			$rt['img_sort'] = empty($rt['sort']) ? 0 : $rt['sort'];
			$rt['id']       = $id;
			$this->assign('rt', $rt);
			
			$this->assign('form_action', RC_Uri::url('cycleimage/admin/edit', array('id' => $id)));
			
			$this->display('cycleimage_edit.dwt');
		}

		// 提交处理
		elseif ($_POST['step'] == 2) {
			if (empty($_POST['img_url'])) {
				return $this->showmessage(RC_Lang::get('cycleimage::flashplay.link_empty'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}

			// 有上传图片
			if (!empty($_FILES['img_file_src']['name'])) {
				$upload = RC_Upload::uploader('image', array('save_path' => 'data/afficheimg', 'auto_sub_dirs' => false));
				$info   = $upload->upload($_FILES['img_file_src']);
				if (!empty($info)) {
					$src = $upload->get_position($info);
				} else {
					return $this->showmessage($upload->error(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
				}
			}
			// 直接填写图片地址
			else if (!empty($_POST['img_src'])) {
				$src = $_POST['img_src'];
				if (strstr($src, 'http') && !strstr($src, $_SERVER['SERVER_NAME'])) {
					$src = get_url_image($src);
				}
			}
			// 图片地址不能为空
			else {
				return $this->showmessage(RC_Lang::get('cycleimage::flashplay.src_empty'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}

			if (strpos($rt['src'], 'http') === false && $rt['src'] != $src) {
				$upload->remove($rt['src']);
			}
       		$flashdb[$id] = array(
       			'src'	=> $src,
       			'url'	=> $_POST['img_url'],
       			'text'	=> $_POST['img_text'],
       			'sort'	=> $_POST['img_sort']
       		);

		    // 实现排序
		    $flashdb_sort   = array();
			$_flashdb       = array();
			foreach ($flashdb as $key => $value) {
				$flashdb_sort[$key] = $value['sort'];
			}
			asort($flashdb_sort, SORT_NUMERIC);

			foreach ($flashdb_sort as $key => $value) {
		        $_flashdb[] = $flashdb[$key];
			}
			unset($flashdb, $flashdb_sort);

			$_flashdb = serialize($_flashdb);
			ecjia_config::instance()->write_config(cycleimage_method::STORAGEKEY_cycleimage_data, $_flashdb);

			ecjia_admin::admin_log($_POST['img_text'], 'edit', 'cycleimage');
		    return $this->showmessage(RC_Lang::get('cycleimage::flashplay.edit_success'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('cycleimage/admin/init')));
		}
	}

	/**
	 * 编辑分类的排序
	 */
	public function edit_sort() {
		$this->admin_priv('flash_update', ecjia::MSGTYPE_JSON);

		$id    = !empty($_POST['pk']) 		? intval($_POST['pk']) 		: 0;
		$order = !empty($_POST['value']) 	? intval($_POST['value']) 	: 0;

		if (!is_numeric($order)) {
			return $this->showmessage(RC_Lang::get('cycleimage::flashplay.format_error'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		} else {
			$flashdb              = $this->cycle->player_data();
			$flashdb[$id]['sort'] = $order;

			$flashdb_sort   = array();
			$_flashdb       = array();
			foreach ($flashdb as $key => $value) {
				$flashdb_sort[$key] = $value['sort'];
			}
			asort($flashdb_sort, SORT_NUMERIC);

			foreach ($flashdb_sort as $key => $value) {
				$_flashdb[] = $flashdb[$key];
			}
			unset($flashdb, $flashdb_sort);

			$_flashdb = serialize($_flashdb);
			ecjia_config::instance()->write_config(cycleimage_method::STORAGEKEY_cycleimage_data, $_flashdb);

			return $this->showmessage(RC_Lang::get('cycleimage::flashplay.sort_edit_ok'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_uri::url('cycleimage/admin/init')));
		}
	}

	/**
	 * 删除
	 */
	public function del() {
		$this->admin_priv('flash_delete', ecjia::MSGTYPE_JSON);

		$id 		= !empty($_GET['id']) ? intval($_GET['id']) : 0;
		$flashdb	= $this->cycle->player_data();
		
		if (isset($flashdb[$id])) {
			$rt = $flashdb[$id];
		} else {
			$links[] = array('text' => RC_Lang::get('cycleimage::flashplay.cycle_image_list'), 'href' => RC_Uri::url('cycleimage/admin/init'));
			return $this->showmessage(RC_Lang::get('cycleimage::flashplay.id_error'), ecjia::MSGTYPE_HTML | ecjia::MSGSTAT_ERROR, array('links' => $links));
		}
		
		if (strpos($rt['src'], 'http') === false) {
			$disk = RC_Filesystem::disk();
			$disk->delete(RC_Upload::upload_path() . $rt['src']);
		}
		
		$_flashdb = array();
		foreach ($flashdb as $key => $val) {
			if ($key != $id) {
				$_flashdb[] = $val;
			}
		}

		$_flashdb = serialize($_flashdb);
		ecjia_config::instance()->write_config(cycleimage_method::STORAGEKEY_cycleimage_data, $_flashdb);

		ecjia_admin::admin_log($rt['text'], 'remove', 'cycleimage');
		return $this->showmessage(RC_Lang::get('cycleimage::flashplay.remove_success'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
	}

	/**
	 * 切换轮播图展示样式
	 */
	public function apply() {
		$this->admin_priv('flash_manage', ecjia::MSGTYPE_JSON);
		
		$flash_theme = !empty($_GET['code']) ? trim($_GET['code']) : '';
		if (ecjia::config(cycleimage_method::STORAGEKEY_cycleimage_style) != $flash_theme) {
			$result = ecjia_config::instance()->write_config(cycleimage_method::STORAGEKEY_cycleimage_style, $flash_theme);
			if ($result) {
				return $this->showmessage(RC_Lang::get('cycleimage::flashplay.install_success'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
			} else {
				return $this->showmessage(RC_Lang::get('cycleimage::flashplay.edit_no'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
		}
	}

	/**
	 * 轮播图预览
	 */
	public function preview() {
		$this->admin_priv('flash_manage', ecjia::MSGTYPE_JSON);

	    $code      = !empty($_GET['code']) ? trim($_GET['code']) : '';
	    $script    = $this->cycle->get_cycleimage_script($code);
	    $flashtpls = $this->cycle->cycle_list();

		return $this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => $script, 'flashtpl' => $flashtpls[$code]));
	}
}

// end