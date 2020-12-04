<?php

class user
{
    protected static $mdb;
    private static $instance;
    private static $uinfo = array();
    protected static $fields = array();

    private function __construct()
    {
        self::$mdb = mysqliUtil::getInstance('master_db');
        self::$fields = load_config('tables');
    }
    private function __clone()
    {
    }
    public static function getInstance()
    {
        if (!isset(self::$instance) || !(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function autoLogin()
    {
        if (self::isLogined()) {
            return true;
        }
        $_auth = sgetcookie('_auth');
        if ($_auth !== null) {
            $_auth = aesCryptUtil::getDecryptStr($_auth);
            if (strlen($_auth) && strpos($_auth, "\t") !== false) {
                $cols = explode("\t", $_auth);
                if (count($cols) == 3) {
                    $cols[0] = intval($cols['0']);
                    $sql = "SELECT uid,username,password FROM ".tname('user')." WHERE uid=?";
                    $query = self::$mdb->stmt_query($sql, "i", $cols[0]);
                    if ($user = self::$mdb->fetch_array($query)) {
                        if ($cols[1] === $user['password']) {
                            $_SESSION['UID'] = $cols[0];
                            $_SESSION['USERNAME'] = $cols[1];
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * 用户登录
     * @param $username
     * @param $password
     * @return Array
     */
    public static function login($username, $password)
    {
        $rarr = array('code'=>0, 'msg'=>'succ', 'data'=>array());

        $sql = "SELECT * FROM ".tname('user')." WHERE username=? LIMIT 1";
        $query = self::$mdb->stmt_query($sql, "s", $username);
        if (!$user = self::$mdb->fetch_array($query)) {
            return array_replace_recursive($rarr, array(
                'code' => 1,
                'msg' => '用户不存在',
            ));
        }
        $passwordEncry = md5($password.md5($user['salt']));
        if ($passwordEncry !== $user['password']) {
            return array_replace_recursive($rarr, array(
                'code' => 2,
                'msg' => '密码不正确',
            ));
        }
        // 设置session
        $uid = intval($user['uid']);
        $_SESSION['UID'] = $uid;
        $_SESSION['USERNAME'] = $user['username'];
        // 设置cookie
        $_auth = aesCryptUtil::getEncryptStr("{$uid}\t{$passwordEncry}\t".date('YmdHis'));
        ssetcookie('uid', $uid, 3*86400);
        ssetcookie('username', $user['username'], 3*86400);
        ssetcookie('_auth', $_auth, 3*86400);

        // 更新登录时间
        $ip0 = get_clientip(true);
        $logintime = time();
        $sql = "UPDATE ".tname('user')." SET loginip=?,logintime=? WHERE uid=?";
        self::$mdb->stmt_query($sql, 'sii', $ip0, $logintime, $uid);
        if (isset(self::$uinfo[$uid])) {
            self::$uinfo[$uid]['loginip'] = $ip0;
            self::$uinfo[$uid]['logintime'] = $logintime;
        }

        // 写入登录记录
        $ip1 = get_clientip(false);
        $datafile = APP_DATA . "/qqwry.dat";
        $iploc = new iploc($datafile);
        $resu = $iploc->getlocation($ip0);
        $resu['country'] = iconv('gbk', 'utf-8//ignore', $resu['country']); // 如 北京市
        $resu['area'] = iconv('gbk', 'utf-8//ignore', $resu['area']); // 如 联通
        $sql = "INSERT INTO ".tname('user_login_record')." (id, uid, ip, iploc, country, area, ctime) VALUES (NULL, ?, ?, ?, ?, ?, ?)";
        self::$mdb->stmt_query($sql, 'issssi', $uid, $ip1, '', $resu['country'], $resu['area'], $logintime);

        return array_replace_recursive($rarr, array(
            'code' => 0,
            'msg' => 'succ',
            'data' => array(
                'username' => $user['username'],
                'email' => $user['email'],
                'gender' => $user['gender'],
                'regip' => $user['regip'],
                'loginip' => $user['loginip'],
                'ctime' => $user['ctime'],
                'logintime' => $user['logintime'],
            ),
        ));
    }

    /**
     * 用户注册
     * @param $userinfo
     * @return Array
     */
    public static function register($userinfo, $need_invcode = true)
    {
        $rarr = array('code'=>0, 'msg'=>'succ', 'data'=>array());

        $username = isset($userinfo['username']) ? strtolower($userinfo['username']) : '';
        $password = isset($userinfo['password']) ? $userinfo['password'] : '';
        $gender = isset($userinfo['gender']) ? $userinfo['gender'] : 0; // 0:未知, 1:男, 2:女
        $email = isset($userinfo['email']) ? $userinfo['email'] : '';
        $invcode = isset($userinfo['invcode']) ? $userinfo['invcode'] : '';

        if (strlen($username) < 1) {
            return array_replace_recursive($rarr, array(
                'code' => 1,
                'msg' => '用户名为空',
            ));
        }
        if (!preg_match('/^[\w\-]+$/i', $username)) {
            return array_replace_recursive($rarr, array(
                'code' => 2,
                'msg' => '用户名格式不正确',
            ));
        }
        $banned = array('root', 'admin', 'sogou', 'sogouer', 'host', 'hostroot');
        if (in_array($username, $banned, true)) {
            return array_replace_recursive($rarr, array(
                'code' => 3,
                'msg' => '该用户名为系统保留请更换',
            ));
        }
        if (strlen($password) < 1) {
            return array_replace_recursive($rarr, array(
                'code' => 4,
                'msg' => '密码不能为空',
            ));
        }
        if (strlen($password) < 8) {
            return array_replace_recursive($rarr, array(
                'code' => 5,
                'msg' => '密码长度不能少于8个字符',
            ));
        }
        if (strlen($email) < 1) {
            return array_replace_recursive($rarr, array(
                'code' => 6,
                'msg' => '邮箱不能为空',
            ));
        }
        if (!isemail($email)) {
            return array_replace_recursive($rarr, array(
                'code' => 7,
                'msg' => '邮箱格式不正确',
            ));
        }
        // 检查邀请码
        $invcoder = array();
        if ($need_invcode) {
            if (strlen($invcode) < 1) {
                return array_replace_recursive($rarr, array(
                    'code' => 8,
                    'msg' => '没有填写邀请码',
                ));
            }
            // 检查邀请码
            $sql = "SELECT id,used FROM ".tname('register_invcode')." WHERE invcode=?";
            $query = self::$mdb->stmt_query($sql, "s", $invcode);
            if ($invcoder = self::$mdb->fetch_array($query)) {
                if ($invcoder['used']) {
                    return array_replace_recursive($rarr, array(
                        'code' => 9,
                        'msg' => '邀请码已失效',
                    ));
                }
            } else {
                return array_replace_recursive($rarr, array(
                    'code' => 10,
                    'msg' => '无效的邀请码',
                ));
            }
        }
        // 检查是否重复
        $sql = "SELECT uid FROM ".tname('user')." WHERE username=?";
        $query = self::$mdb->stmt_query($sql, "s", $username);
        if ($user = self::$mdb->fetch_array($query)) {
            return array_replace_recursive($rarr, array(
                'code' => 11,
                'msg' => '该用户名已存在请更换',
            ));
        }

        if ($need_invcode) {
            self::$mdb->query("START TRANSACTION");
        }

        $regip = get_clientip(true);
        $loginip = '';
        $salt = random(16, false);
        $passwordEncry = md5($password.md5($salt));
        $sql = "INSERT INTO ".tname('user')." (username, password, email, gender, regip, loginip, salt, ctime, mtime, lasttime, logintime) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if (!self::$mdb->stmt_query($sql, "sssisssiiii", $username, $passwordEncry, $email, $gender, $regip, $loginip, $salt, time(), 0, time(), 0)) {
            if ($need_invcode) {
                self::$mdb->query("ROLLBACK");
            }
            return array_replace_recursive($rarr, array(
                'code' => 12,
                'msg' => '注册失败',
            ));
        }

        // 更新邀请码状态
        if ($need_invcode) {
            $sql = "UPDATE ".tname('register_invcode')." SET used=1, mtime=? WHERE id=?";
            if (!self::$mdb->stmt_query($sql, "ii", time(), $invcoder['id'])) {
                self::$mdb->query("ROLLBACK");
                return array_replace_recursive($rarr, array(
                    'code' => 12,
                    'msg' => '注册失败(邀请码)',
                ));
            }
        }

        if ($need_invcode) {
            self::$mdb->query("COMMIT");
        }

        return array_replace_recursive($rarr, array(
            'code' => 0,
            'msg' => 'succ',
        ));
    }

    /**
     * 获取用户信息
     * @param $uid Integer 用户uid
     * @param $fields String 获取的字段
     * @return Array
     */
    public static function info($uid = 0, $fields = '*')
    {
        if ($uid < 1) {
            $uid = self::isLogined();
            if ($uid < 1) {
                return array();
            }
        }
        $fieldsKey = '';
        if ($fields != '*') {
            $tmparr = explode(',', str_replace(array(' ', '`'), '', $fields));
            sort($tmparr, SORT_STRING);
            $fieldsKey = md5(implode('|', $tmparr));
        } else {
            $fieldsKey = '*';
        }

        if (isset(self::$uinfo[$uid][$fieldsKey])) {
            return self::$uinfo[$uid][$fieldsKey];
        }
        $sql = "SELECT {$fields} FROM ".tname('user')." WHERE uid=? LIMIT 1";
        $query = self::$mdb->stmt_query($sql, "i", $uid);
        if (!$user = self::$mdb->fetch_array($query)) {
            return array();
        }
        self::$uinfo[$uid][$fieldsKey] = $user;
        return $user;
    }

    /**
     * 更新用户的账户
     * @param $ismaster Boolean 是否获取主账户
     * @param $uid Integer 用户uid
     * @return Array
     */
    public static function update($user)
    {
        $rarr = array('code'=>0, 'msg'=>'succ', 'data'=>array());
        $uid = 0;
        if (isset($user['uid'])) {
            $uid = intval($user['uid']);
            unset($user['uid']);
        }
        if ($uid < 1) {
            return array_replace_recursive($rarr, array(
                'code' => 1,
                'msg' => 'uid空',
            ));
        }
        if (empty($user)) {
            return array_replace_recursive($rarr, array(
                'code' => 2,
                'msg' => 'user数据为空',
            ));
        }

        if (!isset(self::$fields[tname('user')])) {
            if (false === generate_table_conf('user', true)) {
                return array_replace_recursive($rarr, array(
                    'code' => 99,
                    'msg' => '生成表的字段类型配置失败[user]',
                ));
            }
            self::$fields = load_config('tables', true);
            ;
        }

        $sql = "UPDATE ".tname('user')." SET ";
        $fields = $values = $types = array();
        $comma = '';
        foreach (self::$fields[tname('user')] as $field => $type) {
            if (isset($user[$field])) {
                $fields[] = $field;
                $types[] = $type;
                $values[] = $user[$field];
                $sql .= "{$comma}{$field}=?";
                $comma = ",";
            }
        }
        if (empty($fields)) {
            return array_replace_recursive($rarr, array(
                'code' => 3,
                'msg' => '没有要更新的字段',
            ));
        }
        
        $fields[] = 'uid';
        $values[] = $uid;
        $types[] = 'i';
        $sql .= " WHERE uid=? LIMIT 1";
        
        $args = array();
        $args[] = $sql;
        $args[] = implode('', $types);
        $args = array_merge($args, $values);

        if (false === call_user_func_array(array(self::$mdb, 'stmt_query'), $args)) {
            return array_replace_recursive($rarr, array(
                'code' => 4,
                'msg' => '更新失败',
            ));
        }

        //
        if (isset(self::$uinfo[$uid])) {
            foreach ($fields as $idx => $field) {
                self::$uinfo[$uid][$field] = $values[$idx];
            }
        }
        
        return array_replace_recursive($rarr, array(
            'code' => 0,
            'msg' => 'succ',
        ));
    }

    /**
     * 检查是否登录
     * @param void
     * @return Integer
     */
    public static function isLogined()
    {
        $isLoginedVar = isset($_SESSION['UID']) ? $_SESSION['UID'] : 0;
        if ($isLoginedVar < 1) {
            ssetcookie('uid', 0, -1);
            ssetcookie('username', null, -1);
        } else {
            if (sgetcookie('uid') !== null && intval(sgetcookie('uid')) > 0 && sgetcookie('username') !== null) {
            } else {
                $uid = $isLoginedVar;
                $user = self::info($uid, 'username,password');
                $passwordEncry = $user['password'];
                $_auth = aesCryptUtil::getEncryptStr("{$uid}\t{$passwordEncry}\t".date('YmdHis'));
                ssetcookie('uid', $uid, 3*86400);
                ssetcookie('username', $user['username'], 3*86400);
                ssetcookie('_auth', $_auth, 3*86400);
            }
        }
        return $isLoginedVar;
    }
}
