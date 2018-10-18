<?php
namespace app\controllers;

use yii;
use QL\QueryList;
use yii\web\Controller;


class DaojiaController extends Controller
{
    use Csvexport;
    public function actionIndex()
    {
        $pages = Yii::$app->request->get('page', 1);
        //管道疏通、修水路、修电路、开锁换锁、防水治漏、五金灯具、厨卫洁具、门窗家具、洗鞋、洗衣、洗家纺、奢侈品洗护
        //厨卫安装、灯具安装、家具安装、门窗五金安装、墙地面安装、窗帘五金、办公及IT安装、家电清洗、精品鲜花、礼品鲜花、花材绿植、鲜花花篮、丽人、代办跑腿、汽车保养、墙面地面、环保除虫、翻新拆旧验房、按摩
        $type = '按摩';
        //$city = 'shanghai';//shenzhen、shanghai、dongguan、hangzhou
        $cityname = '杭州';//深圳、上海、东莞、杭州
        $cityurl = 'https://hz.daojia.com/tuinaliliao1/';


        if($pages == 37){
            echo 'customer';
            exit;
        }
        $need = $this->getHtmlData($cityurl,$pages);
//        print_r($need);exit;
        if(!$need){
            echo 'success!';
            exit;
        }
        $dealdata = [
            'bidding' => '58到家',
            'city' => $cityname,
            'category' => $type,
            'list' => $need
        ];
        $this->downCsv($dealdata,$pages);
        sleep(1);
        ++$pages;
        $this->redirect('http://yii2.test/index.php?page='.$pages.'&r=daojia/index');
        return;
    }

    /**
     * @param $cityurl
     * @param int $page
     * @return mixed
     */
    public function getHtmlData($cityurl,$pages)
    {
        $url = trim($cityurl.'p'.$pages.'/?sort=sort_4017');
        //print_r($url);exit;
//        $url = 'https://sz.daojia.com/shutong2/p2/?sort=sort_4017';
        $html = $this->curl_get($url,1);
//        print_r($html);exit;
        $ql = QueryList::html($html);
        $data = $ql->rules(array(
            'text' => array('ul.w-search-list.search-list-top','html')
        ))->query()->range('li')->getData(function($item){
            /**
             * (object)[
             *  0 => (object)[
             *        0 => (object)[
             *            'title' => 'subxxxx',
             *            'price' => 12,
             *            'sell' => 4
             *        ]
             *  ]
             * ]
             */
            //一个li主要内容
            $content = QueryList::html($item['text']);
            $alist = $content->find('li')->map(function($li){
                $price = $li->find('span.price-num')->html();//30<span class="price-unit">元</span>
                $sell = $li->find('p.sale-wrapper')->html();//已售：1752
                $price = substr($price,0,stripos($price,'<'));
                $sell = substr($sell,iconv_strlen('已售：',"UTF-8")*3);//为什么*3,试出来的:)
               return (object)[
                    'company' => $li->find('p.goods-provider a')->html(),
                    'title' => $li->find('h4 a')->html(),
                    'price' =>  $price,
                    'sell' => $sell
               ];
            })->all();
            return $alist;
        });
        $ql->destruct();
        if($data->all()){
            return $data->all()[0];
        }else{
            return [];
        }

    }




    public function downCsv($csv_body,$pages)
    {
//        print_r($csv_body);exit;
        $headlist = ['竞品名称','二级品类','城市','店铺名','服务项目标题','价格','已售'];
        $data = [];
        foreach ($csv_body['list'] as  $item) {
            $data[] = [
                $csv_body['bidding'],
                $csv_body['category'],
                $csv_body['city'],
                $item->company,
                $item->title,
                $item->price,
                $item->sell
            ];
        }
//        print_r($data);exit;
        $fileName = $csv_body['bidding'].'.csv';
        $this->csv_export($data,$headlist,$fileName);
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