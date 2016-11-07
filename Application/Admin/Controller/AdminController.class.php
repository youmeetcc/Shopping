<?php
/**
 * Created by: Jiar
 * Github: https://www.github.com/Jiar/
 */

namespace Admin\Controller;

use Think\Controller;

class AdminController extends Controller {

    /**
     * 获取管理员信息，需要通过POST请求形式获取
     *
     * @param account  邮箱或用户名
     * @param password 登录密码
     *
     * @return {"success":0, "info":"info"}
     * success为0表示获取失败，1表示获取成功；无论是否获取成功info表示内容
     */
    public function fetchEntity_action() {
        header("Access-Control-Allow-Origin: *");
        $account = I('post.account');
        $password = sha1(I("post.password"));
        if(filter_var($account, FILTER_VALIDATE_EMAIL)) {
            // 邮箱登录
            $data['email'] = $account;
            $data['password'] = $password;
            $admin = D('Admin')->where($data)->select();
            if(count($admin) == 0) {
                $backEntity['success'] = 0;
                $backEntity['info'] = '账户或密码错误';
                $this->ajaxReturn(json_encode($backEntity), 'JSON');
            }
            $admin = $admin[0];
            if($admin['is_block'] == 1) {
                $backEntity['success'] = 0;
                $backEntity['info'] = '该账户已被屏蔽';
                $this->ajaxReturn(json_encode($backEntity), 'JSON');
            }
            $id = $admin['id'];
            $data = null;
            $data['id'] = $id;
            $data['token'] = sha1('TOKEN:' .$admin['name'] .date('YmdHis'));
            $data['last_login_time'] = date('Y-m-d H:i:s');
            D('Admin')->save($data);
            $admin = D('Admin')->select($id);
            $admin = $admin[0];
            $admin['password'] = '';
            $backEntity['success'] = 1;
            $backEntity['info'] = $admin;
            $this->ajaxReturn(json_encode($backEntity), 'JSON');
        } else {
            // 账号登录
            $data['name'] = $account;
            $data['password'] = $password;
            $admin = D('Admin')->where($data)->select();
            if(count($admin) == 0) {
                $backEntity['success'] = 0;
                $backEntity['info'] = '账户或密码错误';
                $this->ajaxReturn(json_encode($backEntity), 'JSON');
            }
            $admin = $admin[0];
            if($admin['is_block'] == 1) {
                $backEntity['success'] = 0;
                $backEntity['info'] = '该账户已被屏蔽';
                $this->ajaxReturn(json_encode($backEntity), 'JSON');
            }
            $id = $admin['id'];
            $data = null;
            $data['id'] = $id;
            $data['token'] = sha1('TOKEN:' .$admin['name'] .date('YmdHis'));
            $data['last_login_time'] = date('Y-m-d H:i:s');
            D('Admin')->save($data);
            $admin = D('Admin')->select($id);
            $admin = $admin[0];
            $admin['password'] = '';
            $backEntity['success'] = 1;
            $backEntity['info'] = $admin;
            $this->ajaxReturn(json_encode($backEntity), 'JSON');
        }
    }

    /**
     * 管理员注册
     *
     * @param name       用户名
     * @param email      电子邮箱
     * @param password   登录密码
     * @param repassword 重复密码
     *
     * @return {"success":0, "info":"info"}
     * success为0表示获取失败，1表示获取成功；无论是否获取成功info表示内容
     */
    public function signup_action() {
        $name = I('post.name');
        $email = I('post.email');
        $data['name'] = $name;
        $data['email'] = $email;
        $data['password'] = I('post.password');
        $data['repassword'] = I('post.repassword');
        $data['token'] = sha1('TOKEN:' .$name .date('YmdHis'));
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $data['last_login_time'] = date('Y-m-d H:i:s');
        $id = $this->getExamineRefuseId($name, $email);
        if($id) {
            D("Admin")->delete($id);
        }
        $admin = D('Admin');
        if (!$admin->create($data)){
            $backEntity['success'] = 0;
            $backEntity['info'] = $admin->getError();
            $this->ajaxReturn(json_encode($backEntity), 'JSON');
        } else {
            $backEntity['success'] = 1;
            $backEntity['info'] = '注册成功';
            $this->ajaxReturn(json_encode($backEntity), 'JSON');
        }
    }

    /**
     * 检查注册信息中的name、email是否是审核被拒绝的管理员，如果是，返回id
     * 
     * @param  $name  名字
     * @param  $email 邮箱
     */
    private function getExamineRefuseId($name, $email) {
        $admin = D("Admin");
        $result = $admin->getByName($name);
        if(count($result) != 0) {
            if($result['is_examine'] == 2) {
                return $result['id'];
            }
        }
        $admin = D("Admin");
        $result = $admin->getByEmail($email);
        if(count($result) != 0) {
            if($result['is_examine'] == 2) {
                return $result['id'];
            }
        }
        return false;
    }

}