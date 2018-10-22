<?php
namespace app\controllers;

use yii;
use QL\QueryList;
use yii\web\Controller;

class DianpingController extends Controller
{
    use Csvexport;

    /**
     * (object)[
     *  0 => (object)[
     *     'company' => 'xxx',
     *     'list' => [
     *        0 => (object)[
     *            'title' => 'subxxxx',
     *            'price' => 12,
     *            'sellnum' => 4
     *        ]
     *     ]
     *  ]
     * ]
     */
    public function actionIndex()
    {
        $pages = Yii::$app->request->get('page', 1);
        $type = '洗涤护理';//搬家运输、家电数码维修、居家维修、洗涤护理
        $city = 'hangzhou';//shenzhen、shanghai、dongguan、hangzhou
        $cityname = '杭州';//深圳、上海、东莞、杭州
        //$url = 'http://t.dianping.com/deal/22445493';
        //深圳-搬家运输-3页
        //$cityurl = 'http://www.dianping.com/shenzhen/ch80/g33986p';
        //深圳-家电数码维修-9页
        //$cityurl = 'http://www.dianping.com/shenzhen/ch80/g33976p';
        //深圳-居家维修-10页
        //$cityurl = 'http://www.dianping.com/shenzhen/ch80/g26117p';
        //深圳-洗涤护理-10页
        //$cityurl = 'http://www.dianping.com/shenzhen/ch80/g33762p';
        //上海-搬家运输-11页
        //$cityurl = 'http://www.dianping.com/shanghai/ch80/g33986p';
        //上海-家电数码维修-22页
        //$cityurl = 'http://www.dianping.com/shanghai/ch80/g33976p';
        //上海-居家维修-19页
        //$cityurl = 'http://www.dianping.com/shanghai/ch80/g26117p';
        //上海-洗涤护理-25页
        //$cityurl = 'http://www.dianping.com/shanghai/ch80/g33762p';
        //东莞-搬家运输-无
        //东莞-家电数码维修-2页
        //$cityurl = 'http://www.dianping.com/dongguan/ch80/g33976p';
        //东莞-居家维修-1页
        //$cityurl = 'http://www.dianping.com/dongguan/ch80/g26117p';
        //东莞-洗涤护理-1页
        //$cityurl = 'http://www.dianping.com/dongguan/ch80/g33762p';
        //杭州-搬家运输-4页
        //$cityurl = 'http://www.dianping.com/hangzhou/ch80/g33986p';
        //杭州-家电数码维修-4页
        //$cityurl = 'http://www.dianping.com/hangzhou/ch80/g33976p';
        //杭州-居家维修-6页
        //$cityurl = 'http://www.dianping.com/hangzhou/ch80/g26117p';
        //杭州-洗涤护理-11页
        $cityurl = 'http://www.dianping.com/hangzhou/ch80/g33762p';
        $need = $this->getHtmlData($cityurl,$pages);
        if(!$need){
            echo 'success!';
            exit;
        }
        $dealdata = [
            'bidding' => '大众点评',
            'city' => $cityname,
            'category' => $type,
            'list' => $need
        ];
        $this->downCsv($dealdata,$pages);
        sleep(1);
        ++$pages;
        return \Yii::$app->response->redirect('/index.php?page='.$pages.'&r=dianping/index', 301)->send();
    }


    /**
     * @param $cityurl
     * @param int $page
     * @return mixed
     */
    public function getHtmlData($cityurl,$page=1)
    {
        $url = trim($cityurl.$page.'m3');
        $html = $this->curl_get($url,1);
        //print_r($html);exit;
//        $ql = QueryList::getInstance();
//        print_r( $ql->get($url));
//        $data = $ql->get($url)->rules(array(
//            'text' => array('#shop-all-list li','html')
//        ))->query()->range('li')->getData(function($item){
        $ql = QueryList::html($html);
        $data = $ql->rules(array(
            'text' => array('#shop-all-list li','html')
        ))->query()->range('li')->getData(function($item){
            //一个li主要内容
            $content = QueryList::html($item['text']);
            //print_r($content);exit;
            $company = $content->find('div.tit a h4')->html();//店铺名
            $alist = $content->find('div.svr-info a[target="_blank"][data-click-name="shop_info_groupdeal_click"]')->map(function($a){
                //获取id
                $tempurl = trim($a->attr('href'));
                $id =substr($tempurl,strripos($tempurl,'/')+1);
                $apiurl = 'http://t.dianping.com/ajax/getaids?ids='.$id;
                $res = json_decode($this->curl_get($apiurl));
                try{
                    $data = json_decode($res->content)->msg->dealGroupList->$id;
                    return (object)[
                        'title' => $a->text(),
                        'price' => $data->price,
                        'sell' => $data->join
                    ];
                }catch (yii\base\ErrorException $exception){
                    return (object)[
                        'title' => $a->text(),
                        'price' => '',
                        'sell' => ''
                    ];
                }

                /*return (object)[
                    'title' => $a->text(),//团购标题
                    'url' => $a->attr('href')
                ];*/
            })->all();
            $need['title'] = $company;
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
        curl_setopt($ch,CURLOPT_COOKIE,'_hc.v=f95c1a61-4725-0457-a75c-86b337e7b08c.1539595471; cy='.$str.';_lxsdk_cuid=166778a28c7c8-0d442aaa83deed-346c780e-1fa400-166778a28c8c8; _lxsdk=166778a28c7c8-0d442aaa83deed-346c780e-1fa400-166778a28c8c8; s_ViewType=10; switchcityflashtoast=1; cityid=7; source=m_browser_test_33; default_ab=citylist%3AA%3A1%7Cindex%3AA%3A1%7CshopList%3AA%3A1; JSESSIONID=6E1B2979C734D954352C2003ACAFC468; _lxsdk_s=1667b773448-3f7-1f6-295%7C%7C3');
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