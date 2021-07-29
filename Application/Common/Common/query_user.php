<?php
/**
 * Created by PhpStorm.
 * User: caipeichao
 * Date: 14-3-10
 * Time: PM7:40
 */

/**
 * 支持的字段有
 * member表中的所有字段，ucenter_member表中的所有字段
 * 头衔：title
 * 头像：avatar32 avatar64 avatar128 avatar256 avatar512
 * 个人中心地址：space_url
 *
 * @param $fields
 * @param null $uid
 * @return array|null
 */
function query_user($fields, $uid=null) {
    //默认获取自己的资料
    $uid = $uid ? $uid : is_login();
    if(!$uid) {
        return null;
    }

    //获取两张用户表格中的所有字段
    $homeModel = M('Member');
    $ucenterModel = M('UcenterMember');
    $homeFields = $homeModel->getDbFields();
    $ucenterFields = $ucenterModel->getDbFields();

    //分析每个表格分别要读取哪些字段
    $avatarFields = array('avatar32','avatar64','avatar128','avatar256','avatar512');
    $avatarFields = array_intersect($avatarFields, $fields);
    $titleFields = array('title');
    $titleFields = array_intersect($titleFields, $fields);
    $spaceUrlFields = array('space_url');
    $spaceUrlFields = array_intersect($spaceUrlFields, $fields);
    $homeFields = array_intersect($homeFields, $fields);
    $ucenterFields = array_intersect($ucenterFields, $fields);

    //查询需要的字段
    $homeResult = array();
    $ucenterResult = array();
    if($homeFields) {
        $homeResult = D('Home/Member')->where(array('uid'=>$uid))->field($homeFields)->find();
    }
    if($ucenterFields) {
        $model = D('User/UcenterMember');
        $ucenterResult = $model->where(array('id'=>$uid))->field($ucenterFields)->find();
    }

    //读取头像数据
    $avatarResult = array();
    $avatarAddon = new \Addons\Avatar\AvatarAddon();
    foreach($avatarFields as $e) {
        $avatarSize = intval(substr($e, 6));
        $avatarPath = $avatarAddon->getAvatarPath($uid);
        $avatarUrl = getImageUrlByPath('.' . $avatarPath, $avatarSize);
        $avatarResult[$e] = $avatarUrl;
    }

    //读取头衔数据
    $titleResult = array();
    if($titleFields) {
        $titleModel = D('Usercenter/Title');
        $title = $titleModel->getTitle();
        $titleResult['title'] = $title;
    }

    //获取个人中心地址
    $spaceUrlResult = array();
    if($spaceUrlFields) {
        $spaceUrlResult['space_url'] = U('UserCenter/Index/index',array('uid'=>$uid));
    }

    //返回合并的结果
    $result = array_merge($ucenterResult, $homeResult, $avatarResult, $titleResult, $spaceUrlResult);
    return $result;
}