<?php

    namespace Seller\Model;

    use Think\Model;

    class PurchaseModel extends Model {
        protected $_validate = array(
            array('phone','require','请输入手机号'), // 验证手机号是否为空
            array('phone', '/^(13[0-9]{9})|(14[57][0-9]{8})|(15[012356789][0-9]{8})|(17[0-9]{9})|(18[0-9]{9})$/', '请输入正确的手机号', 1, 'regex', 3),
            array('email', '/^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$/', '请输入正确的邮箱', 1, 'regex', 3),
        );
    }
