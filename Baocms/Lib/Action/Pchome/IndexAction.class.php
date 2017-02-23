<?php


class IndexAction extends CommonAction
{


    public function _initialize()
    {
        parent::_initialize();
        $this->type = D('Keyword')->fetchAll();
        $this->assign('types', $this->type);
        $this->goodscates = D('Goodscate')->fetchAll();
        $this->assign('goodscates', $this->goodscates);
        //分类信息
        $this->cates = D('Lifecate')->fetchAll();
        $this->assign('cates', $this->cates);
        $this->assign('channelmeans', D('Lifecate')->getChannelMeans());
        //商城分类
        $this->goodscate = D('Goodscate')->fetchAll();
        $this->assign('goodscate', $this->goodscate);

        //商家
        $this->shopcates = D('Shopcate')->fetchAll();
        $this->assign('shopcates', $this->shopcates);

        //新闻
        $this->articlecates = D('Articlecate')->fetchAll();
        $this->assign('articlecates', $this->articlecates);
        //家政
        $this->lifeservicecates = D('Housekeepingcate')->fetchAll();
        $this->assign('lifeservicecates', $this->lifeservicecates);

    }

    public function post($url, $post_data, $timeout = 300){
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/json;encoding=utf-8',
                'content' => urldecode(json_encode($post_data)),
                'timeout' => $timeout
            )
        );
        $context = stream_context_create($options);
        return file_get_contents($url, false, $context);
    }

    public function index()
    {
//        header("Location:" . U('mobile/index/index'));
//        die;
//        $access_token = D('Weixin')->getSiteToken();
//
//        $articles[] = array(
//            'title' => urlencode('测试图片'),
//            'url' => 'http://wap.51loveshow.com',
//            'picurl' => 'http://wap.51loveshow.com/attachs/2016/10/15/thumb_5801bed9f22ac.jpg'
//        );
//
//
//
//        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $access_token;
//        $content = array();
//        $content['touser'] = 'onj8pwk4Wn--a4baB3PrLAjHAAjo';
//        $content['msgtype'] = 'news';
//        $content['news'] = array('articles' => $articles);
//        $rs =  $this->post($url, $content);
//        var_dump($rs);
//        die;


        header("Location:" . U('mobile/index/index'));
        if (is_mobile()) {
            header("Location:" . U('mobile/index/index'));
            die;
        }
        $linkArr = array();


        $cache = cache(array('type' => 'File', 'expire' => 600));
        if (!$counts = $cache->get('index_count')) {
            $counts['shop'] = D('Shop')->count();
            $counts['coupon'] = D('Coupon')->count();
            $counts['users'] = D('Users')->count();
            $counts['life'] = D('Life')->count();
            $counts['post'] = D('Post')->count();
            $cache->set('index_count', $counts);
        }


        $this->assign('counts', $counts);
        $this->assign('host', __HOST__);
        $this->assign('linkArr', $linkArr);
        $this->display();

    }


    public function get_arr()
    {

        if (IS_AJAX) {

            $cate_id = I('val', 0, 'intval,trim');

            $today = date('Y-m-d');

            $t = D('Tuan');
            $map = array(
                'cate_id' => $cate_id,
                'city_id' => $this->city_id,
                'closed' => 0,
                'audit' => 1,
                'bg_date' => array('elt', $today),
                'end_date' => array('egt', $today)

            );
            $r = $t->where($map)->limit(8)->select();

            if ($r) {
                $this->ajaxReturn(array('status' => 'success', 'arr' => $r));
            } else {
                $this->ajaxReturn(array('status' => 'error'));
            }

        }

    }


    public function test()
    {

        //$data = D('Email')->sendMail('email_newpwd', '1442211217@qq.com', '重置密码', array('newpwd' => 123456));
        // var_dump($data);
        //var_dump(D('Email')->getEorrer());

    }

}
