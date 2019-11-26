<?php

namespace ComTrait;

trait  ResultReturn
{
    /*返回数据*/
    public function data($data = [])
    {
        return $this->resultArray(true, $data);
    }

    /*成功信息*/
    public function success($msg = '处理成功')
    {
        return $this->resultArray(true, [], $msg);
    }

    /*错误信息*/
    public function error($msg = '处理失败')
    {
        return $this->resultArray(false, [], $msg);
    }

    /*结果值*/
    private function resultArray($status = true, $data = [], $msg = '')
    {
        return ['status' => $status, 'data' => $data, 'msg' => $msg];
    }
}