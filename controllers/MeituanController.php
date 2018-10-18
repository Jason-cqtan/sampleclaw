<?php

namespace app\controllers;


use yii;
use QL\QueryList;
use yii\web\Controller;

class MeituanController extends Controller
{

    use Csvexport;
    public function actionIndex()
    {
        $pages = Yii::$app->request->get('page', 1);
        $type = '洗涤护理';//家电维修、数码维修（电脑、手机）、居家维修、管道疏通、开锁换锁、搬家、洗涤护理
        //$city = 'shanghai';//shenzhen、shanghai、dongguan、hangzhou
        $cityname = '杭州';//深圳、上海、东莞、杭州
        //深圳-家电维修-1页
        //$cityurl = 'https://sz.meituan.com/shenghuo/c20459/';
        //深圳-数码维修（手机）-1页
        //$cityurl = 'https://sz.meituan.com/shenghuo/c20460/';
        //深圳-数码维修（电脑）-1页
        //$cityurl = 'https://sz.meituan.com/shenghuo/c20458/';
        //深圳-居家维修-3页
        //$cityurl = 'https://sz.meituan.com/shenghuo/c20474/';
        //深圳-管道疏通-3页
        //$cityurl = 'https://sz.meituan.com/shenghuo/c20456/';
        //深圳-开锁换锁-3页
        //$cityurl = 'https://sz.meituan.com/shenghuo/c20457/';
        //深圳-搬家-3页
        //$cityurl = 'https://sz.meituan.com/shenghuo/c20454/';
        //深圳-洗涤护理-3页
        //$cityurl = 'https://sz.meituan.com/shenghuo/c20112/';


        //上海-家电维修-2页
        //$cityurl = 'https://sh.meituan.com/shenghuo/c20459/';
        //上海-数码维修（手机）-4页
        //$cityurl = 'https://sh.meituan.com/shenghuo/c20460/';
        //上海-数码维修（电脑）-1页
        //$cityurl = 'https://sh.meituan.com/shenghuo/c20458/';
        //上海-居家维修-7页
        //$cityurl = 'https://sh.meituan.com/shenghuo/c20474/';
        //上海-管道疏通-3页
        //$cityurl = 'https://sh.meituan.com/shenghuo/c20456/';
        //上海-开锁换锁-3页
        //$cityurl = 'https://sh.meituan.com/shenghuo/c20457/';
        //上海-搬家-3页
        //$cityurl = 'https://sh.meituan.com/shenghuo/c20454/';
        //上海-洗涤护理-3页
        //$cityurl = 'https://sh.meituan.com/shenghuo/c20112/';


        //东莞-家电维修-1页
        //$cityurl = 'https://dg.meituan.com/shenghuo/c20459/';
        //东莞-数码维修（手机）-4页
        //$cityurl = 'https://dg.meituan.com/shenghuo/c20460/';
        //东莞-数码维修（电脑）-无
        //东莞-居家维修-1页
        //$cityurl = 'https://dg.meituan.com/shenghuo/c20474/';
        //东莞-管道疏通-3页
        //$cityurl = 'https://dg.meituan.com/shenghuo/c20456/';
        //东莞-开锁换锁-3页
        //$cityurl = 'https://dg.meituan.com/shenghuo/c20457/';
        //东莞-搬家-无
        //东莞-洗涤护理-3页
        //$cityurl = 'https://dg.meituan.com/shenghuo/c20112/';

        //杭州-家电维修-1页
        //$cityurl = 'https://hz.meituan.com/shenghuo/c20459/';
        //杭州-数码维修（手机）-1页
        //$cityurl = 'https://hz.meituan.com/shenghuo/c20460/';
        //杭州-数码维修（电脑）-0页
        //$cityurl = 'https://hz.meituan.com/shenghuo/c20458/';
        //杭州-居家维修-7页
        //$cityurl = 'https://hz.meituan.com/shenghuo/c20474/';
        //杭州-开锁换锁-3页
        //$cityurl = 'https://hz.meituan.com/shenghuo/c20456/';
        //杭州-搬家-3页
        //$cityurl = 'https://hz.meituan.com/shenghuo/c20454/';
        //杭州-洗涤护理-3页
        $cityurl = 'https://hz.meituan.com/shenghuo/c20112/';

        $need = $this->getHtmlData($cityurl,$pages);

        if(!$need){
            echo 'success!';
            exit;
        }
        $dealdata = [
            'bidding' => '美团',
            'city' => $cityname,
            'category' => $type,
            'list' => $need
        ];
        $this->downCsv($dealdata,$pages);
        sleep(1);
        ++$pages;
        $this->redirect('http://yii2.test/index.php?page='.$pages.'&r=meituan/index');
        return;
    }

    /**
     * @param $cityurl
     * @param int $page
     * @return mixed
     */
    public function getHtmlData($cityurl,$pages)
    {
        $url = trim($cityurl.'pn'.$pages.'/');
        //print_r($url);exit;
        //$url = 'https://sz.meituan.com/shenghuo/c20459/pn1/';
        $html = $this->curl_get($url,1);
        //print_r($html);exit;
        $ql = QueryList::html($html);
        $data = $ql->rules(array(
            'text' => array('.common-list-main div.abstract-item','html')
        ))->query()->range('li')->getData(function($item){
            //一个div主要内容
            $content = QueryList::html($item['text']);
            //print_r($content);exit;
            $company = $content->find('div.list-item-desc-top a');//店铺
            $companyname = $company->html();
            //点击获取详情
            $companyurl = str_ireplace('//','https://',$company->attr('href'));
            $companyhtml = $this->curl_get($companyurl,1);
            //print_r($companyhtml);exit;
            //数据在页面js里面
            $ql2 = QueryList::html($companyhtml);
            $jsonstr = $ql2->find("div#react + script")->html();
            if(!$jsonstr){
               return [];
            }
            $jsonstr = str_ireplace('window.AppData = ','',$jsonstr);
            $jsonstr = str_ireplace(';','',$jsonstr);
            $jsonarr = json_decode($jsonstr);
            //$jsonstr = $ql2->rules(array('json'=>array('div#react + script','text')))->query()->getData(function($json){
              //  print_r($json);exit;
            //});
            /**
             * (object)[
             *  0 => (object)[
             *     'company' => 'xxx',
             *     'list' => [
             *        0 => (object)[
             *            'title' => 'subxxxx',
             *            'price' => 12,
             *            'sell' => 4
             *        ]
             *     ]
             *  ]
             * ]
             */
            $alist = array_map(function($item){
                return (object)[
                    'title' => $item->longTips,
                    'price' => $item->price,
                    'sell' => $item->sold
                ];
            },$jsonarr->groupDealList->group);
            $need['title'] = $companyname;
            $need['list'] = $alist;
            return $need;
        });
        $ql->destruct();
//        print_r($data->all());exit;
        return $data->all();

    }


    /**
     * @param $url
     * @return string
     */
    public 	function curl_get($url,$exec=0)
    {

        $str = uniqid();
        $ch = curl_init();//初始化
        curl_setopt($ch, CURLOPT_NOBODY, 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch,CURLOPT_URL,$url);//设置参数
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json; charset=utf-8;"));
        curl_setopt($ch,CURLOPT_COOKIE,'tjc='.$str.'; __mta=188184215.1539748785506.1539749013751.1539762941679.1; uuid=8dd75a08c202402a9507.'.time().'.1.0.0; _lxsdk_cuid=166809c160d8d-03f341b2bd39dd-1f396652-1fa400-166809c160ec8; ci=30; rvct=30; __mta=217838505.1539748247374.1539748973285.1539756017055.3; _lxsdk_s=166809c1610-35a-6bc-9a0%7C%7C9');
        curl_setopt ($ch, CURLOPT_TIMEOUT,0);//设置cURL允许执行的最长秒数
        curl_setopt($ch,CURLOPT_USERAGENT,$this->getRandAgent());
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);//执行之后不直接打印出来default=1
        $execres = curl_exec($ch);
        if($exec){
            return $execres;
        }
        if(curl_errno($ch))
        {
            $errormsg = curl_error($ch);
            return json_encode(array('status'=>'fail','msg'=>$errormsg));
        }
        else
        {
            $info = curl_getinfo($ch);
            return json_encode(array('status'=>'ok','content'=>$execres,'msg'=>$info));
        }
        curl_close($ch);
    }

}