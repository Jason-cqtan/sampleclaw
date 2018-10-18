<?php

namespace app\controllers;


trait Csvexport
{
    /**
     * 导出excel(csv)
     * @data 导出数据
     * @headlist 第一行,列名
     * @fileName 输出Excel文件名
     */
    public function csv_export($data = array(), $headlist = array(), $fileName) {

        //header('Content-Type: application/vnd.ms-excel');
//        header("Content-type:text/csv;charset=gb2312");
        //header('Content-Disposition: attachment;filename="'.$fileName.'.csv"');
        //header('Cache-Control: max-age=0');

        //打开PHP文件句柄,php://output 表示直接输出到浏览器
        //$fp = fopen('php://output', 'a');


        if(!file_exists($fileName)){
            //输出Excel列名信息
            foreach ($headlist as $key => $value) {
                //CSV的Excel支持GBK编码，一定要转换，否则乱码
                $headlist[$key] = iconv('utf-8', 'GBK//IGNORE', $value);
            }
            $fp = fopen($fileName, 'a');
            //将数据通过fputcsv写到文件句柄
            fputcsv($fp, $headlist);
        }else{
            $fp = fopen($fileName, 'a');
        }
        //计数器
        $num = 0;

        //每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
        $limit = 100000;

        //逐行取出数据，不浪费内存
        $count = count($data);
        for ($i = 0; $i < $count; $i++) {

            $num++;

            //刷新一下输出buffer，防止由于数据过多造成问题
            if ($limit == $num) {
                ob_flush();
                flush();
                $num = 0;
            }

            $row = $data[$i];
            foreach ($row as $key => $value) {
                try{
                    $row[$key] = iconv('utf-8', 'GBK//IGNORE', $value);
                }catch (yii\base\ErrorException $e){
                    $row[$key] = $value;//iconv('utf-8', 'gbk', $value);
                }

            }

            fputcsv($fp, $row);
        }
    }

    public function downCsv($csv_body,$pages)
    {
//        print_r($csv_body);exit;
        $headlist = ['竞品名称','二级品类','城市','店铺名','团购标题','价格','已售'];
        $data = [];
        foreach ($csv_body['list'] as  $companies) {
            if(!$companies){
                continue;
            }
            foreach ($companies['list'] as $shop){
                $data[] = [
                    $csv_body['bidding'],
                    $csv_body['category'],
                    $csv_body['city'],
                    $companies['title'],
                    $shop->title,
                    $shop->price,
                    $shop->sell
                ];
            }
        }
        $fileName = $csv_body['bidding'].'.csv';
        $this->csv_export($data,$headlist,$fileName);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getRandAgent()
    {
        $user_agent = [
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:55.0) Gecko/20100101 Firefox/55.0",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:55.0) Gecko/20100101 Firefox/55.0",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:55.0) Gecko/20100101 Firefox/55.0",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:55.0) Gecko/20100101 Firefox/55.0",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:55.0) Gecko/20100101 Firefox/55.0",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:55.0) Gecko/20100101 Firefox/55.0",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:55.0) Gecko/20100101 Firefox/54.0",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:55.0) Gecko/20100101 Firefox/53.0",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:55.0) Gecko/20100101 Firefox/52.0",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:55.0) Gecko/20100101 Firefox/50.0",
            "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.104 Safari/537.36 Core/1.53.2306.400 QQBrowser/9.5.10648.400",
            "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/22.0.1207.1 Safari/537.1",
            "Mozilla/5.0 (X11; CrOS i686 2268.111.0) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11",
            "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.6 (KHTML, like Gecko) Chrome/20.0.1092.0 Safari/536.6",
            "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.6 (KHTML, like Gecko) Chrome/20.0.1090.0 Safari/536.6",
            "Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/19.77.34.5 Safari/537.1",
            "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/536.5 (KHTML, like Gecko) Chrome/19.0.1084.9 Safari/536.5",
            "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/536.5 (KHTML, like Gecko) Chrome/19.0.1084.36 Safari/536.5",
            "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1063.0 Safari/536.3",
            "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1063.0 Safari/536.3",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_0) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1063.0 Safari/536.3",
            "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1062.0 Safari/536.3",
            "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1062.0 Safari/536.3",
            "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1061.1 Safari/536.3",
            "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1061.1 Safari/536.3",
            "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1061.1 Safari/536.3",
            "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1061.0 Safari/536.3",
            "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.24 (KHTML, like Gecko) Chrome/19.0.1055.1 Safari/535.24",
            "Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/535.24 (KHTML, like Gecko) Chrome/19.0.1055.1 Safari/535.24",
        ];
        $index = random_int(0,count($user_agent)-1);
        return $user_agent[$index];
    }
}