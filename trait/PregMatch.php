<?php

namespace ComTrait;

trait  PregMatch
{

    public function checkName($str, $max = 0, $min = 0)
    {
        if (empty($str)) return false;
        if ($max > 0 && mb_strlen($str, 'utf-8') > $max) return false;
        if ($min > 0 && mb_strlen($str, 'utf-8') < $min) return false;
        return preg_match('/^[0-9a-zA-Z_\x{4e00}-\x{9fa5}]+$/u', $str);
    }

    public function checkUserName($str, $max = 0, $min = 0)
    {
        if (empty($str)) return false;
        if ($max > 0 && mb_strlen($str, 'utf-8') > $max) return false;
        if ($min > 0 && mb_strlen($str, 'utf-8') < $min) return false;
        return preg_match('/^[a-zA-Z0-9_]+$/u', $str);
    }

    public function checkMobile($str)
    {
        $regex="/^1[3-8]{1}[0-9]{9}$/";
        return preg_match($regex, $str);
    }

    public function checkEmail($str)
    {
        $regex = '/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/';
        return preg_match($regex, $str);
    }

    public function checkPrice($str)
    {
        if (empty($str)) return false;
        return preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $str);
    }

    public function checkIdCard($card_id)
    {
        if (empty($card_id)) return false;

        $City = [
            11 => "北京", 12 => "天津", 13 => "河北", 14 => "山西", 15 => "内蒙古",
            21 => "辽宁", 22 => "吉林", 23 => "黑龙江",
            31 => "上海", 32 => "江苏", 33 => "浙江", 34 => "安徽", 35 => "福建", 36 => "江西", 37 => "山东",
            41 => "河南", 42 => "湖北", 43 => "湖南", 44 => "广东", 45 => "广西", 46 => "海南",
            50 => "重庆", 51 => "四川", 52 => "贵州", 53 => "云南", 54 => "西藏",
            61 => "陕西", 62 => "甘肃", 63 => "青海", 64 => "宁夏", 65 => "新疆",
            71 => "台湾",
            81 => "香港", 82 => "澳门",
            91 => "国外",
        ];

        $idCardLength = strlen($card_id);
        //长度验证  
        if (!preg_match('/^\d{17}(\d|x)$/i', $card_id) and !preg_match('/^\d{15}$/i', $card_id)) return false;

        //地区验证  
        if (!array_key_exists(intval(substr($card_id, 0, 2)), $City)) return false;

        // 15位身份证验证生日，转换为18位  
        if ($idCardLength == 15) {
            $sBirthday = '19' . substr($card_id, 6, 2) . '-' . substr($card_id, 8, 2) . '-' . substr($card_id, 10, 2);
            try {
                $d = new \DateTime($sBirthday);
                $dd = $d->format('Y-m-d');
                if ($sBirthday != $dd) return false;
            } catch (\Exception $e) {
                if (strtotime($sBirthday) === false) return false;
            }

            $card_id = substr($card_id, 0, 6) . '19' . substr($card_id, 6, 9);//15to18
            $Bit18 = $this->getVerifyBit($card_id);//算出第18位校验码
            $card_id = $card_id . $Bit18;
        }
        // 判断是否大于2078年，小于1900年  
        $year = substr($card_id, 6, 4);
        if ($year < 1900 || $year > 2078) return false;


        //18位身份证处理  
        $sBirthday = substr($card_id, 6, 4) . '-' . substr($card_id, 10, 2) . '-' . substr($card_id, 12, 2);
        try {
            $d = new \DateTime($sBirthday);
            $dd = $d->format('Y-m-d');
            if ($sBirthday != $dd) return false;
        } catch (\Exception $e) {
            if (strtotime($sBirthday) === false) return false;
        }

        //身份证编码规范验证  
        $card_id_base = substr($card_id, 0, 17);
        if (strtoupper(substr($card_id, 17, 1)) != $this->getVerifyBit($card_id_base)) return false;

        return true;
    }

    // 计算身份证校验码，根据国家标准GB 11643-1999  
    private function getVerifyBit($card_id_base)
    {
        if (strlen($card_id_base) != 17) return false;

        //加权因子  
        $factor = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
        //校验码对应值  
        $verify_number_list = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
        $checksum = 0;
        for ($i = 0; $i < strlen($card_id_base); $i++) {
            $checksum += substr($card_id_base, $i, 1) * $factor[$i];
        }
        $mod = $checksum % 11;
        $verify_number = $verify_number_list[$mod];
        return $verify_number;
    }

}