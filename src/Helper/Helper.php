<?php
/**
*助手函数类
*/
namespace Helper;
class Helper
{
    /**
    *密码加密
    */
    public static function encryptPassword($password) {
        return md5(md5(trim($password)));
    }



    /**
     * 获取随机字符串.
     * @param integer $length Length.
     * @return string
     */
    public static function genRandomString($length) {
        $charset = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $repeatTime = $length / strlen($charset);
        $charset = str_repeat($charset, $repeatTime + 1);
        $charset = str_shuffle($charset);
        return substr($charset, 0, $length);
    }





    /**
    *反转定义的数组，与传入的数组比较，返回交集，用于字段校验
    */
    public static function arrayFilterKey($arr, $keys) {
        return array_intersect_key($arr, array_flip($keys));
    }



    /**
    *去除数组中的空值，或返回白名单允许的值
    */
    public static function arrayFilterEmpty($arr, $whiteList = array()) {
        foreach ($arr as $index => $value) {
            if (empty($value) || !in_array($index, $whiteList)) {
                unset($arr[$index]);
            }
        }
        return $arr;
    }


    

    /**
     * 过滤数组中的空值（不是值为false的情况）
     * @param $arr
     * @return mixed
     */
    public static function filterEmptyValue($arr) {
        foreach ($arr as $key => $val) {
            if ((is_array($val) && empty($val)) || $val === '' || $val === null) {
                unset($arr[$key]);
            }
        }
        return $arr;
    }



    
    /**
     * 过滤字段中的特殊字符
     * @param string $fields
     * @return mixed
     */
    public static function filterSelectFields($fields) {
        $fields = str_replace('%', '\%', $fields);              // 转义%符
        //$fields = preg_replace("/[[:punct:]]/", '', $fields);   // 去除标点符号
        $fields = str_replace(' ', '', $fields);                // 过滤空格
        return $fields;
    }




    /**
     * 加密
     */
    public static function encrypt($content, $priKey) {
        ksort($content);
        $content = json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $res = openssl_get_publickey($priKey);
        //把需要加密的内容，按128位拆开加密
        $result = '';
        for ($i = 0; $i < ((strlen($content) - strlen($content) % 117) / 117 + 1); $i++) {
            $data = substr($content, $i * 117, 117);
            openssl_public_encrypt($data, $encrypted, $res);
            $result .= $encrypted;
        }
        openssl_free_key($res);
        //用base64将二进制编码
        $result = base64_encode($result);
        return $result;
    }
 



    public static function arrayReplaceExists(array $base, array $other) {
        foreach ($other as $key => $value) {
            if (array_key_exists($key, $base)) {
                $base[$key] = $value;
            }
        }
        return $base;
    }



 
    /**
     * 对数组的每一个对象增加一组固定的key -val字段
     * @param $key
     * @param $val
     */
    public static function setFixKeyValInPerItem($arr, $key, $val) {
        if (!empty($arr)) {
            foreach ($arr as &$row) {
                $row[$key] = $val;
            }
        }
        return $arr;
    }


 
    /**
     * 按key清理字段
     * @param $arr
     * @param $key
     */
    public static function unsetKeyPerItem($arr, $key) {
        if (!empty($arr)) {
            foreach ($arr as &$row) {
                unset($row[$key]);
            }
        }
 
        return $arr;
    }



 
    /**
     * 把对象的某个列作为列表hash的Key
     * @param $arr
     * @param $key
     */
    public static function pickItemColumnAsKey($arr, $key, $unique = false) {
        $newArr = array();
        if (!empty($arr)) {
            foreach ($arr as $row) {
                if (isset($row[$key])) {
                    $newRow = $row;
                    $newKey = strval($row[$key]);
                    if (!isset($newArr[$newKey])) {
                        $newArr[$newKey] = array();
                    }
 
                    if ($unique) {
                        $newArr[$newKey] = $newRow;
                    } else {
                        $newArr[$newKey][] = $newRow;
                    }
                }
            }
        }
 
        return $newArr;
    }



 
    /**
     * 挑出column对象作为新数组
     * @param $arr
     * @param $key
     */
    public static function pickUpColumnItem($arr, $colName) {
        return array_column((array)$arr, $colName);
        $newArr = array();
        if (!empty($arr)) {
            foreach ($arr as $row) {
                if (isset($row[$colName])) {
                    $newArr[] = $row[$colName];
                }
            }
        }
 
        return $newArr;
    }


 
    /**
     * 挑出column对象，并以另一个字段作为Key, 返回新数组
     * @param $arr
     * @param $key
     */
    public static function pickUpColumnItemWithKey($arr, $keyColName, $colName) {
        return array_column((array)$arr, $colName, $keyColName);
        $newArr = array();
        if (!empty($arr)) {
            foreach ($arr as $row) {
                if (isset($row[$colName]) && isset($row[$keyColName])) {
                    $newArr[$row[$keyColName]] = $row[$colName];
                }
            }
        }
 
        return $newArr;
    }


    
     /**
     * 根据Limit多的一个判断是否还有更多，顺便清理最后一个对象
     * @param $arrList
     * @param $limit
     * @return int
     */
    public static function getHasMoreAndPop(&$arrList, $limit) {
        if ($limit <= 0) {
            return 0;//error
        }
 
        $hasMore = 0;
        while (count($arrList) > $limit) {
            array_pop($arrList);
            $hasMore = 1;
        }
 
        return $hasMore;
    }
 


    /** 处理无限级分类，返回带有层级关系的树形结构
     * @param array $data 数据数组
     * @param int $root 根节点的父级id
     * @param string $id id字段名
     * @param string $pid 父级id字段名
     * @param string $child 树形结构子级字段名
     * @return array $tree 树形结构数组
     */
    public static function getMultilevelTree(array $data, $root = 0, $id = 'id', $pid = 'pid', $child = 'child') {
        $tree = [];
        $temp = [];
 
        foreach ($data as $key => $val) {
            $temp[$val[$id]] = &$data[$key];
        }
        foreach ($data as $key => $val) {
            $parentId = $val[$pid];
            if ($root == $parentId) {
                $tree[] = &$data[$key];
            } else {
                if (isset($temp[$parentId])) {
                    $parent = &$temp[$parentId];
                    $parent[$child][] = &$data[$key];
                }
            }
        }
        return $tree;
    }


    
    /**
     * 从一组数据中获取某个字段值，放入一个数组中
     * @param array $data
     * @param string $field
     * @return array
     */
    public static function getFieldInArray(array $data, $field = 'id') {
        $list = [];
        foreach ($data as $val) {
            if (isset($val[$field])) {
                $list[] = $val[$field];
            }
        }
        return $list;
    }



    
    /**
     * 插入数据到数组指定位置.
     *
     * @param integer $offset
     * @param array $insertData
     * @param array $data
     *
     * @return array
     */
    public static function insertDataToArray($offset, array $data, array $insertData) {
        $prevData = array_slice($data, 0, $offset);
        $lastData = array_slice($data, $offset);
        $prevData = array_merge($prevData, $insertData);
        return array_merge($prevData, $lastData);
    }



    
    /**
     * Http GET
     * @param string $url
     * @param array|string|null $params
     * @param array $headers
     * @return bool|mixed
     */
    public static function httpGet($url, $params = null, array $headers = []) {
        if (is_string($params) || is_array($params)) {
            is_array($params) AND $params = http_build_query($params);
            $url = rtrim($url, '?');
            if (strpos($url, '?') !== false) {
                $url .= '&' . $params;
            } else {
                $url .= '?' . $params;
            }
        }
 
        $ch = curl_init();
 
        curl_setopt_array($ch, self::$curlOptions);
 
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPGET, true);//HTTP GET
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
//        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60); // 设置超时
//        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
//        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $headers AND curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
 
        $ret = curl_exec($ch);
 
        if ($errno = curl_errno($ch)) {
            \Log::error('HttpGet failed', [$url, $headers, $errno, curl_error($ch)]);
            $ret = false;
        }
 
        curl_close($ch);
        return $ret;
    }
    





    /**
     * HTTP POST
     * @param string $url
     * @param array|string|null $params
     * @param array $headers
     * @return bool|mixed
     */
    public static function httpPost($url, $params = null, array $headers = []) {
        $ch = curl_init();
 
        curl_setopt_array($ch, self::$curlOptions);
 
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);//HTTP POST
 
        if (is_string($params) || is_array($params)) {
            is_array($params) AND $params = http_build_query($params);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
 
        $headers AND curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
 
        $ret = curl_exec($ch);
 
        if ($errno = curl_errno($ch)) {
            \Log::error('httpPost failed', [$url, $params, $headers, $errno, curl_error($ch)]);
            $ret = false;
        }
 
        curl_close($ch);
        return $ret;
    }



    

    /**
     * 分转化成2位小数的元.
     *
     * @param $money
     *
     * @return float
     */
    public static function fen2Yuan($money) {
        return number_format($money / 100, 2, '.', '');
    }


    
    /**
     * 返回唯一订单号
     */
    public static function uniqueOrderNum() {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $year = 2017;
        $orderSn = $yCode[intval(date('Y')) - $year] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
        return $orderSn;
    }


    

    /**
     * 防xss过滤
     * creator: liming
     * @param $string
     * @param bool|False $low
     * @return mixed|string
     */
    public static function cleanXss(&$string, $low = False) {
        if (!is_array($string)) {
            $string = trim($string);
            $string = strip_tags($string);
            $string = htmlspecialchars($string);
            if ($low) {
                return $string;
            }
            $string = str_replace(array(
                '"',
                "'",
                "..",
                "../",
                "./",
                '/',
                "//",
                "<",
                ">"
            ), '', $string);
            $no = '/%0[0-8bcef]/';
            $string = preg_replace($no, '', $string);
            $no = '/%1[0-9a-f]/';
            $string = preg_replace($no, '', $string);
            $no = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';
            $string = preg_replace($no, '', $string);
            return $string;
        }
        $keys = array_keys($string);
        foreach ($keys as $key) {
            self::cleanXss($string [$key]);
        }
    }
    


    public static function toArray($object) {
        return json_decode(json_encode($object), true);
    }
 
    

    public static function getParams($key = '') {
        if ($key) {
            return $_GET[$key];
        }
        return $_GET;
    }


    
    public static function getQueryParams($key = '') {
        $res = $_GET[$key];
        if (empty($res)){
            $res = $_POST[$key];
        }
 
        return $res;
    }


    
    public static function numToStr($num) {
        if (stripos($num, 'e') === false) return $num;
        $num = trim(preg_replace('/[=\'"]/', '', $num, 1), '"');//出现科学计数法，还原成字符串
        $result = "";
        while ($num > 0) {
            $v = $num - floor($num / 10) * 10;
            $num = floor($num / 10);
            $result = $v . $result;
        }
        return $result;
    }
    


    /**
     * 获取自然周时间列表
     * @return array
     */
    public static function getWeekList() {
        $time = time();
        $retData = [];
        for ($i = 1; $i <= 7; $i++) {
            $d = date('Ymd', $time - 86400 * (date('N', $time) - $i));
            $retData [] = $d;
        }
        return $retData;
    }



    
     /**
     * 生成随机字符串
     *
     * @param number $length
     */
    public static function createNoncestr($length = 32) {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i ++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }



    
    /**
     * 取得两个数组中值的比较情况
     * @param array $newIds
     * @param array $oldIds
     * @return array
     */
    public static function updateArrayDiff(array $newIds, array $oldIds) {
        $addIds = array_diff($newIds, $oldIds);      // 删除的id
        $deleteIds = array_diff($oldIds, $newIds);         // 新增的id
        $sameIds = array_intersect($newIds, $oldIds);   // 不变的id
 
        return ['delete' => $deleteIds, 'add' => $addIds, 'same' => $sameIds];
    }



    
    /**
     * 将数组中的数字值转换为整型
     * @param array $arr
     * @param array $keyArr 需要转换的字段
     * @return array
     */
    public static function arrayToInt(array $arr, $keyArr = []) {
        if (!empty($keyArr)) {
            foreach ($keyArr as $value) {
                !array_key_exists($value, $arr) ? : $arr[$value] = intval($arr[$value]);
            }
        } else {
            array_walk($arr, function (&$value) {
                $value = intval($value);
            });
        }
        return $arr;
    }
    



    /**
     * 数据分组
     * @param array $input
     * @param string $field
     * @param bool $multi
     * @return array
     */
    public static function groupBy(array $input, $field, $multi = true) {
        $ret = [];
        foreach ($input as $k => $v) {
            if(!is_array($v)){
                $v=(array)$v;
            }
            $fieldVal = $v[$field];
            unset($v[$field]);
            if ($multi) {
                $ret[$fieldVal][] = $v;
            } else {
                $ret[$fieldVal] = $v;
            }
        }
        return $ret;
    }



    
    //按指定长度截断字符串，可选择补... 和尾部
    public static function breakLongString($input,$length=0,$ellipsis=false,$tail="") {
        $len=mb_strlen($input);
        if($len>$length){
            $new = mb_substr($input,0,$length);
            $ellipsis and $new=$new."...".$tail;
        }else{
            $new=$input;
        }
 
        return $new;
    }


    
    public static function hash256($data)
    {
        return hash("sha256", $data);
    }



    //根据时间戳生成唯一码
    public static function getUniqueCode($str='') {
        $time= microtime();
        $m=substr($time,10);
        $m=$m-1516188781;
        $um= substr($time,0,10)*1000000;
        $code= $str.'_'.base_convert(str_replace('.', '', $m), 10, 36).base_convert(str_replace('.', '', $um), 10, 36);
        return $code;
 
        //return base_convert(str_replace('.', '', $_SERVER['REQUEST_TIME_FLOAT']), 10, 36);
    }
    


    /**
     * 根据生日计算年龄
     * @param int $birthday 生日时间 yyyymmdd
     * @return int
     */
    public static function getAgeByBirthday($birthday) {
        if ($birthday == null) {
            return 0;
        }
        list($year, $month, $day) = explode('-', date('Y-m-d'));
        list($bYear, $bMonth, $bDay) = explode('-', date('Y-m-d', strtotime($birthday)));
 
        $age = $year - $bYear;
 
        if ($month < $bMonth) {
            --$age;
        }
        if ($month == $bMonth && $day < $bDay) {
            --$age;
        }
 
        $age <= 0 AND $age = 0;
 
        return $age;
    }
    


    /*
     * 检测是否是url
     * @param string $url
     * @return bool
     */
    public static function checkUrl ($url) {
        $pattern = "/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/";
        if (!preg_match($pattern, $url)){
            return false;
        }
        return true;
    }



    /**
     * 检查邮箱是否正确
     *
     * @return boolean
     */
    public static function checkEmail($email) {
        $num = preg_match("/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/", $email, $match);
        
        if($num == 0){
            return false;
        }else{
            return true;
        }
    }



    /**
     * 检查手机号码是否正确
     *
     * @return boolean
     */
    public static function checkMobile($mobile) {
        $num = preg_match("/^13[0-9]{1}[0-9]{8}$|15[0-9]{1}[0-9]{8}$|18[0-9]{1}[0-9]{8}$/", $mobile, $match);
        if($num == 0){
            return false;
        }else{
            return true;
        }
    }



    /**
     * 校验身份证
     */
    public static function checkIdCard($idc) {
        if(empty($idc)){
            return false;
        }
        
        $idcard = $idc;
        $City = array(
            11 => "北京",
            12 => "天津",
            13 => "河北",
            14 => "山西",
            15 => "内蒙古",
            21 => "辽宁",
            22 => "吉林",
            23 => "黑龙江",
            31 => "上海",
            32 => "江苏",
            33 => "浙江",
            34 => "安徽",
            35 => "福建",
            36 => "江西",
            37 => "山东",
            41 => "河南",
            42 => "湖北",
            43 => "湖南",
            44 => "广东",
            45 => "广西",
            46 => "海南",
            50 => "重庆",
            51 => "四川",
            52 => "贵州",
            53 => "云南",
            54 => "西藏",
            61 => "陕西",
            62 => "甘肃",
            63 => "青海",
            64 => "宁夏",
            65 => "新疆",
            71 => "台湾",
            81 => "香港",
            82 => "澳门",
            91 => "国外"
        );
        
        $iSum = 0;
        $idCardLength = strlen($idcard);
        // 长度验证
        
        if(!preg_match('/^\d{17}(\d|x)$/i', $idcard) and !preg_match('/^\d{15}$/i', $idcard)){
            return false;
        }
        
        // 地区验证
        if(!array_key_exists(intval(substr($idcard, 0, 2)), $City)){
            return false;
        }
        
        // 15位身份证验证生日，转换为18位
        if($idCardLength == 15){
            $sBirthday = '19' . substr($idcard, 6, 2) . '-' . substr($idcard, 8, 2) . '-' . substr($idcard, 10, 2);
            if($sBirthday != $sBirthday){
                return false;
            }
            $idcard = substr($idcard, 0, 6) . "19" . substr($idcard, 6, 9); // 15to18
            $Bit18 = self::getVerifyBit($idcard); // 算出第18位校验码
            $idcard = $idcard . $Bit18;
        }
        
        // 判断是否大于2078年，小于1900年
        $year = substr($idcard, 6, 4);
        if($year < 1900 || $year > 2078){
            return false;
        }
        
        // 18位身份证处理
        $sBirthday = substr($idcard, 6, 4) . '-' . substr($idcard, 10, 2) . '-' . substr($idcard, 12, 2);
        if($sBirthday != $sBirthday){
            return false;
        }
        
        // 身份证编码规范验证
        $idcard_base = substr($idcard, 0, 17);
        if(strtoupper(substr($idcard, 17, 1)) != self::getVerifyBit($idcard_base)){
            return false;
        }
        
        return true;
    }


    
    // 计算身份证校验码，根据国家标准GB 11643-1999
    public static function getVerifyBit($idcard_base) {
        if(strlen($idcard_base) != 17){
            return false;
        }
        // 加权因子
        $factor = array(
            7,
            9,
            10,
            5,
            8,
            4,
            2,
            1,
            6,
            3,
            7,
            9,
            10,
            5,
            8,
            4,
            2
        );
        // 校验码对应值
        $verify_number_list = array(
            '1',
            '0',
            'X',
            '9',
            '8',
            '7',
            '6',
            '5',
            '4',
            '3',
            '2'
        );
        $checksum = 0;
        for($i = 0; $i < strlen($idcard_base); $i++){
            $checksum += substr($idcard_base, $i, 1) * $factor[$i];
        }
        $mod = $checksum % 11;
        $verify_number = $verify_number_list[$mod];
        return $verify_number;
    }



    /**
     *
     * @param unknown_type $srcImg 原图片
     * @param unknown_type $waterImg 水印图片
     * @param unknown_type $savepath 保存路径
     * @param unknown_type $savename 保存名字
     * @param unknown_type $positon 水印位置 1:顶部居左, 2:顶部居右, 3:居中, 4:底部局左, 5:底部居右
     * @param unknown_type $alpha 透明度 -- 0:完全透明, 100:完全不透明
     * @return number|string
     *          成功 -- 加水印后的新图片地址
     *          失败 -- -1:原文件不存在, -2:水印图片不存在, -3:原文件图像对象建立失败
     *          -4:水印文件图像对象建立失败 -5:加水印后的新图片保存失败
     */
    public static function water_img($srcImg, $waterImg, $savepath=null, $savename=null, $positon=5, $alpha=50)
    {
        $temp = pathinfo($srcImg);
        $name = $temp['basename'];
        $path = $temp['dirname'];
        $exte = $temp['extension'];
        $savename = $savename ? $savename : $name;
        $savepath = $savepath ? $savepath : $path;
        $savefile = $savepath .'/'. $savename;
        $srcinfo = @getimagesize($srcImg);
        if (!$srcinfo) {
            return -1; //原文件不存在
        }
        $waterinfo = @getimagesize($waterImg);
        if (!$waterinfo) {
            return -2; //水印图片不存在
        }
        $srcImgObj = imagecreatefromstring(file_get_contents($srcImg));
        //$srcImgObj = $this->image_create_from_ext($srcImg);
        if (!$srcImgObj) {
            return -3; //原文件图像对象建立失败
        }
        $waterImgObj = imagecreatefromstring(file_get_contents($waterImg));
        //$waterImgObj = $this->image_create_from_ext($waterImg);
        if (!$waterImgObj) {
            return -4; //水印文件图像对象建立失败
        }
        switch ($positon) {
            //1顶部居左
            case 1: $x=$y=0; break;
            //2顶部居右
            case 2: $x = $srcinfo[0]-$waterinfo[0]; $y = 0; break;
            //3居中
            case 3: $x = ($srcinfo[0]-$waterinfo[0])/2; $y = ($srcinfo[1]-$waterinfo[1])/2; break;
            //4底部居左
            case 4: $x = 0; $y = $srcinfo[1]-$waterinfo[1]; break;
            //5底部居右
            case 5: $x = $srcinfo[0]-$waterinfo[0]; $y = $srcinfo[1]-$waterinfo[1]; break;
            default: $x=$y=0;
        }
        imagecopymerge($srcImgObj, $waterImgObj, $x, $y, 0, 0, $waterinfo[0], $waterinfo[1], $alpha);
        switch ($srcinfo[2]) {//1 = GIF，2 = JPG，3 = PNG，4 = SWF，5 = PSD，6 = BMP，7 = TIFF(intel byte order)，8 = TIFF(motorola byte order)，9 = JPC，10 = JP2，11 = JPX，12 = JB2，13 = SWC，14 = IFF，15 = WBMP，16 = XBM。
            case 1: imagegif($srcImgObj, $savefile); break;
            case 2: imagejpeg($srcImgObj, $savefile); break;
            case 3: imagepng($srcImgObj, $savefile); break;
            default: return -5; //保存失败
        }
        imagedestroy($srcImgObj);
        imagedestroy($waterImgObj);
        return $savefile;
    }



    /**
     * 时间显示函数t
     * @param int or string $unixtime 时间戳或者时间字符串
     * @param int $limit 相差时间间隔
     * @param string $format 超出时间间隔的日期显示格式
     * @return string 返回需要的时间格式
     */
    public static function showtime($unixtime, $limit = 18000, $format = "Y-m-d") {

        $nowtime = time();
        $showtime = "";
        if(!is_int($unixtime)){
            $unixtime = strtotime($unixtime);
        }
        $differ = $nowtime - $unixtime;
        if($differ >= 0){
            if($differ > $limit){
                $showtime = date($format, $unixtime);
            }else{
                $showtime = $differ > 86400 ? floor($differ / 86400) . "天前" : ($differ > 3600 ? floor($differ / 3600) . "小时前" : floor($differ / 60) . "分钟前");
            }
        }else{
            if(-$differ > $limit){
                $showtime = date($format, $unixtime);
            }else{
                $showtime = -$differ > 86400 ? floor(-$differ / 86400) . "天" : (-$differ > 3600 ? floor(-$differ / 3600) . "小时" : floor(-$differ / 60) . "分钟");
            }
        }
        return $showtime;
    }



    /**
     * 获取当前时间和参数时间相差的天数
     * @param unknown $timestamp 参数时间戳
     */
    public static function getDay($timestamp) {

        //当前时间  年月日
        $nowday = date("Y-m-d");

        //系统时间  年月日
        $sysday = date("Y-m-d",$timestamp);

        //时间差
        $day = strtotime($nowday) - strtotime($sysday);

        //转换天数
        $day = $day/86400;
        return $day;
    }




    /**
     * 时间差计算
     * @param int $timestamp
     * @return string
     */
    public static function roundTime($timestamp) {

        $now = CURRENT_TIMESTAMP;
        $time = $timestamp - $now;

        if ($time > 0) {
            $suffix = '之后';
        }
        else {
            $suffix = '之前';
        }

        $time = abs($time);
        if ($time < 60) {
            $fix_time = '秒';
            $round_time = $time;
        }
        elseif ($time < 3600) {
            $fix_time = '分钟';
            $round_time = round($time / 60);
        }
        elseif ($time < 3600 * 24) {
            $fix_time = '小时';
            $round_time = round($time / 3600);
        }
        elseif ($time < 3600 * 24 * 7) {
            $fix_time = '天';
            $round_time = round($time / (3600 * 24));
        }
        elseif ($time < 3600 * 24 * 30) {
            $fix_time = '周';
            $round_time = round($time / (3600 * 24 * 7));
        }
        elseif ($time < 3600 * 24 * 365) {
            $fix_time = '个月';
            $round_time = round($time / (3600 * 24 * 30));
        }
        elseif ($time >= 3600 * 24 * 365) {
            $fix_time = '年';
            $round_time = round($time / (3600 * 24 * 365));
        }

        return $round_time . ' ' . $fix_time . $suffix;
    }




    /**
     * 返回客户端IP地址
     *
     * @return string
     */
    public static function getip(){

        if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')){
            $onlineip = getenv('HTTP_CLIENT_IP');
        }elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')){
            $onlineip = getenv('HTTP_X_FORWARDED_FOR');
        }elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')){
            $onlineip = getenv('REMOTE_ADDR');
        }elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')){
            $onlineip = $_SERVER['REMOTE_ADDR'];
        }
        preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
        return $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown';
    }



    /**
     * 将一个平面的二维数组按照指定的字段转换为树状结构
     * @param array $arr 数据源
     * @param string $key_node_id 节点ID字段名
     * @param string $key_parent_id 节点父ID字段名
     * @param string $key_childrens 保存子节点的字段名
     * @param boolean $refs 是否在返回结果中包含节点引用
     * return array 树形结构的数组
     */
    public static function toTree($arr, $key_node_id, $key_parent_id = 'parent_id', $key_childrens = 'children', $treeIndex = false, & $refs = null) {

        $refs = array();
        foreach ($arr as $offset => $row) {
            $arr[$offset][$key_childrens] = array();
            $refs[$row[$key_node_id]] = & $arr[$offset];
        }

        $tree = array();
        foreach ($arr as $offset => $row) {
            $parent_id = $row[$key_parent_id];
            if ($parent_id) {
                if (!isset($refs[$parent_id])) {
                    if ($treeIndex) {
                        $tree[$offset] = & $arr[$offset];
                    }
                    else {
                        $tree[] = & $arr[$offset];
                    }
                    continue;
                }
                $parent = & $refs[$parent_id];
                if ($treeIndex) {
                    $parent[$key_childrens][$offset] = & $arr[$offset];
                }
                else {
                    $parent[$key_childrens][] = & $arr[$offset];
                }
            }
            else {
                if ($treeIndex) {
                    $tree[$offset] = & $arr[$offset];
                }
                else {
                    $tree[] = & $arr[$offset];
                }
            }
        }

        return $tree;
    }



    /**
    * 将数组按照键值转换成数组
    */
    public static function toHashmap($arr, $key_field, $value_field = null) {

        $ret = array ();
        if (empty ( $arr )) {
            return $ret;
        }
        if ($value_field) {
            foreach ( $arr as $row ) {
                if (isset ( $row [$key_field] )) {
                    $ret [$row [$key_field]] = isset($row [$value_field])?$row [$value_field]:'NULL';
                }
            }
        } else {
            foreach ( $arr as $row ) {
                $ret [$row [$key_field]] = $row;
            }
        }
        return $ret;
    }

    /**
     * 将数组用分隔符连接并输出
     * @param $array
     * @param $separator
     * @param $find
     * @return string
     */
    public static function toString($array, $separator = ',', $find = '') {

        $str = '';
        $separator_temp = '';

        if (! empty ( $find )) {
            if (! is_array ( $find )) {
                $find = self::toArray ( $find );
            }
            foreach ( $find as $key ) {
                $str .= $separator_temp . $array [$key];
                $separator_temp = $separator;
            }
        } else {
            foreach ( $array as $value ) {
                $str .= $separator_temp . $value;
                $separator_temp = $separator;
            }
        }
        return $str;
    }



    /**
     * 从一个二维数组中返回指定键的所有值
     * @param array $arr 数据源
     * @param string $col 要查询的键
     * @return array 包含指定键所有值的数组
     */
    public static function getCols($arr, $col) {

        $ret = array ();
        foreach ( $arr as $row ) {
            if (isset ( $row [$col] )) {
                $ret [] = $row [$col];
            }
        }
        return $ret;
    }



}