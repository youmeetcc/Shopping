<?php
/**
 * Created by: Jiar
 * Github: https://www.github.com/Jiar/
 */

namespace Shopping\Controller;

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
     * 登录操作
     */
    public function signin_action() {
        if(session('?adminId') && session('?adminToken')) {
            $this->redirect('Admin/admin');
        } else {
            $account = I('post.account');
            $password = sha1(I("post.password"));
            if(filter_var($account, FILTER_VALIDATE_EMAIL)) {
                // 邮箱登录
                $data['email'] = $account;
                $data['password'] = $password;
                $result = D('Admin')->where($data)->select();
                $result = $result[0];
                if(count($result) == 0) {
                    $this->error('该账户不存在');
                    return;
                }
                if($result['is_examine'] == 0) {
                    $this->error('该账户还在审核中，请耐心等待');
                    return;
                } else if($result['is_examine'] == 2) {
                    $this->error('该账户审核被拒绝，您可以重新注册');
                    return;
                }
                if($result['is_block'] == 1) {
                    $this->error('该账户已被屏蔽');
                    return;
                }
                $this->saveDataBySignin($result);
            } else {
                // 用户登录
                $data['name'] = $account;
                $data['password'] = $password;
                $result = D('Admin')->where($data)->select();
                $result = $result[0];
                if(count($result) == 0) {
                    $this->error('该账户不存在');
                    return;
                }
                if($result['is_examine'] == 0) {
                    $this->error('该账户还在审核中，请耐心等待');
                    return;
                } else if($result['is_examine'] == 2) {
                    $this->error('该账户审核被拒绝，您可以重新注册');
                    return;
                }
                if($result['is_block'] == 1) {
                    $this->error('该账户已被屏蔽');
                    return;
                }
                $this->saveDataBySignin($result);
            }
        }
    }

    /**
     * 注册操作
     */
    public function signup_action() {
        $name = I('post.name');
        $data['name'] = $name;
        $data['password'] = I('post.password');
        $data['repassword'] = I('post.repassword');
        $data['email'] = I('post.email');
        $data['token'] = sha1('TOKEN:' .$name .date('YmdHis'));
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $data['last_login_time'] = date('Y-m-d H:i:s');
        $id = $this->getExamineRefuseId($data['name'], $data['email']);
        if($id) {
            $admin = D("Admin");
            $admin->delete($id);
        }
        $admin = D('Admin');
        if (!$admin->create($data)){
            $this->error(structureErrorInfo($admin->getError()));
        } else {
            $admin->add();
            $this->redirect('Admin/login');
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