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
 * 商品推荐列表
 * @author royalwang
 */
class suggestlist_module extends api_front implements api_interface {

    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {

		$location	 = $this->requestData('location', array());
		$action_type = $this->requestData('action_type', '');
    	$sort_type	 = $this->requestData('sort_by', '');
    	$size = $this->requestData('pagination.count', 15);
    	$page = $this->requestData('pagination.page', 1);
    	$type = array('new', 'best', 'hot', 'promotion');//推荐类型
    	
    	if (!in_array($action_type, $type)) {
    		return new ecjia_error('invalid_parameter', RC_Lang::get('system::system.invalid_parameter'));
    	}
    	/*经纬度为空判断*/
    	if (!is_array($location) || empty($location['longitude']) || empty($location['latitude'])) {
    		$data = array();
    		$data['list'] = array();
    		$data['pager'] = array(
    			"total" => '0',
    			"count" => '0',
    			"more"	=> '0'
    		);
    		return array('data' => $data['list'], 'pager' => $data['pager']);
    	} else {
	        $geohash = RC_Loader::load_app_class('geohash', 'store');
	        $geohash_code = $geohash->encode($location['latitude'] , $location['longitude']);
	        $geohash_code = substr($geohash_code, 0, 5);
// 	        $store_id_group = RC_Api::api('store', 'neighbors_store_id', array('geohash' => $geohash_code));
// 	        if (empty($options['store_id_group'])) {
// 	            $store_id_group = RC_Api::api('store', 'neighbors_store_id', array('geohash' => substr($geohash_code, 0, 4)));
// 	        }
    	}
    	
		switch ($sort_type) {
			case 'goods_id' :
				$order_by = array('goods_id' => 'desc');
				break;
			case 'shop_price_desc' :
				$order_by = array('shop_price' => 'desc', 'g.sort_order' => 'asc');
				break;
			case 'shop_price_asc' :
				$order_by = array('shop_price' => 'asc', 'g.sort_order' => 'asc');
	    		break;
			case 'last_update' :
				$order_by = array('last_update' => 'desc');
				break;
			default :
				$order_by = array('g.sort_order' => 'asc', 'goods_id' => 'desc');
				break;
		}

		$options = array(
			'intro'		=> $action_type,
			'sort'		=> $order_by,
			'page'		=> $page,
			'size'		=> $size,
			'geohash'	=> $geohash_code,
// 			'store_id'  => $store_id_group,
		);
		$result = RC_Api::api('goods', 'goods_list', $options);
		$data['pager'] = array(
			"total"	=> $result['page']->total_records,
			"count"	=> $result['page']->total_records,
			"more"	=> $result['page']->total_pages <= $page ? 0 : 1,
		);
		$data['list'] = array();

		if (!empty($result['list'])) {
			foreach ($result['list'] as $val) {
				/* 判断是否有促销价格*/
				$price = ($val['unformatted_shop_price'] > $val['unformatted_promote_price'] && $val['unformatted_promote_price'] > 0) ? $val['unformatted_promote_price'] : $val['unformatted_shop_price'];
				$activity_type = ($val['unformatted_shop_price'] > $val['unformatted_promote_price'] && $val['unformatted_promote_price'] > 0) ? 'PROMOTE_GOODS' : 'GENERAL_GOODS';
				/* 计算节约价格*/
				$saving_price = ($val['unformatted_shop_price'] > $val['unformatted_promote_price'] && $val['unformatted_promote_price'] > 0) ? $val['unformatted_shop_price'] - $val['unformatted_promote_price'] : (($val['unformatted_market_price'] > 0 && $val['unformatted_market_price'] > $val['unformatted_shop_price']) ? $val['unformatted_market_price'] - $val['unformatted_shop_price'] : 0);

				$mobilebuy_price = $object_id = 0;
	    			
				$data['list'][] = array(
					'goods_id'		=> $val['goods_id'],
					'id'			=> $val['goods_id'],
					'name'			=> $val['name'],
					'market_price'	=> $val['market_price'],
					'shop_price'	=> $val['shop_price'],
					'promote_price'	=> $val['promote_price'],
					'manage_mode'	=> $val['manage_mode'],
					'unformatted_promote_price' => $val['unformatted_promote_price'],
					'promote_start_date'		=> $val['promote_start_date'],
					'promote_end_date'			=> $val['promote_end_date'],
					'img' => array(
						'thumb'	=> $val['goods_img'],
						'url'	=> $val['original_img'],
						'small'	=> $val['goods_thumb']
					),
					'activity_type' => $activity_type,
					'object_id'		=> $object_id,
					'saving_price'	=>	$saving_price,
					'formatted_saving_price' => $saving_price > 0 ? '已省'.$saving_price.'元' : '',
					'seller_id'		=> $val['store_id'],
					'seller_name'	=> $val['store_name'],
				);
			}
		}
		return array('data' => $data['list'], 'pager' => $data['pager']);
    }
}

// end