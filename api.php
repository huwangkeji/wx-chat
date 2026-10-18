<?php
/**
 * 微信对话生成器 - PHP 后端 API
 *
 * 提供：
 *   - action=time         完善的时间日期数据（年份动态扩展到当前年份）
 *   - action=random       一键随机生成对话数据
 *   - action=presets      系统预设内容列表
 *   - action=preset&id=X  加载某个系统预设内容
 *
 * 所有响应统一为 JSON：{ code: 0, msg: "ok", data: {...} }
 */

declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// 仅允许同源访问
header('Access-Control-Allow-Origin: *');

// ------------------------------------------------------------
// 工具函数
// ------------------------------------------------------------

/** 统一 JSON 输出 */
function ok($data = null, string $msg = 'ok'): void
{
    echo json_encode(['code' => 0, 'msg' => $msg, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

function fail(string $msg, int $code = 1): void
{
    echo json_encode(['code' => $code, 'msg' => $msg, 'data' => null], JSON_UNESCAPED_UNICODE);
    exit;
}

/** 0-9 a-z A-Z 简单随机串 */
function randStr(int $len = 8): string
{
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $out = '';
    for ($i = 0; $i < $len; $i++) {
        $out .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $out;
}

/** 数组随机取一个元素 */
function pick(array $arr)
{
    return $arr[array_rand($arr)];
}

// ------------------------------------------------------------
// 路由
// ------------------------------------------------------------

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'time':
        apiTime();
        break;
    case 'random':
        apiRandom();
        break;
    case 'presets':
        apiPresets();
        break;
    case 'preset':
        apiPreset();
        break;
    default:
        fail('unknown action: ' . $action);
}

// ------------------------------------------------------------
// 1. 时间日期数据
//    年份范围：2018 ~ 当前年份+1，保证不再过期
// ------------------------------------------------------------

function apiTime(): void
{
    $now = getdate();
    $curYear = (int) date('Y');

    // 年份：2018 到 当前年份+1
    $years = [];
    for ($y = 2018; $y <= $curYear + 1; $y++) {
        $years[] = ['value' => (string) $y, 'label' => $y . '年'];
    }
    // 月份
    $months = [];
    for ($m = 1; $m <= 12; $m++) {
        $months[] = ['value' => (string) $m, 'label' => $m . '月'];
    }
    // 日
    $days = [];
    for ($d = 1; $d <= 31; $d++) {
        $days[] = ['value' => (string) $d, 'label' => $d . '日'];
    }
    // 小时
    $hours = [];
    for ($h = 0; $h <= 23; $h++) {
        $v = str_pad((string) $h, 2, '0', STR_PAD_LEFT);
        $hours[] = ['value' => $v, 'label' => $v];
    }
    // 分钟
    $minutes = [];
    for ($i = 0; $i <= 59; $i++) {
        $v = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
        $minutes[] = ['value' => $v, 'label' => $v];
    }
    // 星期
    $weekdays = [
        ['value' => '星期一', 'label' => '星期一'],
        ['value' => '星期二', 'label' => '星期二'],
        ['value' => '星期三', 'label' => '星期三'],
        ['value' => '星期四', 'label' => '星期四'],
        ['value' => '星期五', 'label' => '星期五'],
        ['value' => '星期六', 'label' => '星期六'],
        ['value' => '星期日', 'label' => '星期日'],
    ];
    // 时段
    $periods = [
        ['value' => '上午', 'label' => '上午'],
        ['value' => '下午', 'label' => '下午'],
        ['value' => '凌晨', 'label' => '凌晨'],
        ['value' => '晚上', 'label' => '晚上'],
    ];

    ok([
        'years'    => $years,
        'months'   => $months,
        'days'     => $days,
        'hours'    => $hours,
        'minutes'  => $minutes,
        'weekdays' => $weekdays,
        'periods'  => $periods,
        // 当前时间，前端可直接用作手机时间默认值
        'now'      => [
            'year'    => (string) $curYear,
            'month'   => (string) $now['mon'],
            'day'     => (string) $now['mday'],
            'hour'    => str_pad((string) $now['hours'], 2, '0', STR_PAD_LEFT),
            'minute'  => str_pad((string) $now['minutes'], 2, '0', STR_PAD_LEFT),
            'weekday' => ['日', '一', '二', '三', '四', '五', '六'][$now['wday']],
            'period'  => periodOfHour((int) $now['hours']),
        ],
    ]);
}

function periodOfHour(int $h): string
{
    if ($h < 6) return '凌晨';
    if ($h < 12) return '上午';
    if ($h < 18) return '下午';
    return '晚上';
}

// ------------------------------------------------------------
// 2. 一键随机
//    生成一组完整的 phone + users + dialogs + setting
// ------------------------------------------------------------

function apiRandom(): void
{
    // ---- 用户名池 ----
    $namePool = [
        '马先生', '李老板', '张总', '王姐', '赵哥', '小美', '大刘', '阿强',
        '陈经理', '周主任', '吴老师', '郑哥', '孙姐', '朱总', '胡经理', '林妹妹',
        '何叔', '高总', '罗姐', '梁哥', '宋姐', '唐总', '许哥', '韩姐',
    ];
    // ---- 头像池（使用项目自带图片，避免外链） ----
    $avatarPool = [
        'static/app/images/user-face.png',
        'static/app/images/icon-wechat.png',
        'static/app/images/icon-alipay-chat.png',
        'static/app/images/logo.png',
    ];
    // ---- 文本对话池 ----
    $textPool = [
        '在吗？', '最近怎么样？', '有空一起吃饭吗？', '这个周末有安排吗？',
        '帮我个忙呗', '收到请回复', '哈哈哈哈哈', '你说得对',
        '我马上到', '稍等我一下', '在开会，等会回你', '好的没问题',
        '今晚加班', '记得带伞', '路上小心', '到家给我发消息',
        '转账给你了', '红包发你了', '生日快乐！', '新年快乐！',
        '恭喜恭喜', '万事如意', '大吉大利', '一路平安',
        '我想你了', '晚安', '早安', '吃了吗',
        '发个定位给我', '视频一下', '语音聊', '打字太慢了',
    ];
    // ---- 红包/转账备注 ----
    $redpacketRemark = ['恭喜发财，大吉大利', '生日快乐', '新年快乐', '一点心意', '辛苦费', '请喝茶'];
    $transferRemark  = ['转账给你', '还你的钱', '饭钱', '打车费', '辛苦了', '一点心意'];
    // ---- 聊天标题 ----
    $titlePool = [
        '微信对话', '老朋友', '工作群', '家人', '同学', '闺蜜', '兄弟', '客户',
        '相亲对象', '房东', '外卖小哥', '快递员', '代驾', '师傅',
    ];

    // ---- 生成用户（2-4 个） ----
    $userCount = random_int(2, 4);
    shuffle($namePool);
    $users = [];
    $meId = 'u-me';
    $users[] = [
        'id'       => $meId,
        'name'     => pick(['我', '本人', '自己']),
        'image'    => $avatarPool[0],
        'is_me'    => true,
        'selected' => true,
    ];
    for ($i = 1; $i < $userCount; $i++) {
        $users[] = [
            'id'       => 'u-' . $i,
            'name'     => $namePool[$i - 1],
            'image'    => $avatarPool[$i % count($avatarPool)],
            'is_me'    => false,
            'selected' => false,
        ];
    }

    // ---- 生成对话（8-20 条） ----
    $dialogCount = random_int(8, 20);
    $dialogs = [];
    $baseTime = time() - $dialogCount * 60;
    for ($i = 0; $i < $dialogCount; $i++) {
        $isMe = (bool) random_int(0, 1);
        $user = $isMe ? $users[0] : pick(array_slice($users, 1));
        $ts = $baseTime + $i * random_int(30, 120);

        // 每 4-6 条插一个时间提示
        if ($i > 0 && $i % random_int(4, 6) === 0) {
            $dialogs[] = makeNotice($ts);
        }

        $typeRoll = random_int(1, 10);
        if ($typeRoll <= 7) {
            // 文字
            $dialogs[] = [
                'id'      => 'dialog-' . $ts . '-' . randStr(4),
                'type'    => 'text',
                'content' => pick($textPool),
                'is_me'   => $isMe,
                'user_id' => $user['id'],
            ];
        } elseif ($typeRoll === 8) {
            // 语音
            $dialogs[] = [
                'id'      => 'dialog-' . $ts . '-' . randStr(4),
                'type'    => 'voice',
                'time'    => random_int(1, 60),
                'is_me'   => $isMe,
                'user_id' => $user['id'],
                'isread'  => (string) random_int(0, 1),
            ];
        } elseif ($typeRoll === 9) {
            // 红包
            $dialogs[] = [
                'id'      => 'dialog-' . $ts . '-' . randStr(4),
                'type'    => 'redpacket',
                'money'   => round(mt_rand(100, 88800) / 100, 2),
                'remark'  => pick($redpacketRemark),
                'is_me'   => $isMe,
                'user_id' => $user['id'],
                'is_get'  => (bool) random_int(0, 1),
            ];
        } else {
            // 转账
            $dialogs[] = [
                'id'      => 'dialog-' . $ts . '-' . randStr(4),
                'type'    => 'transfer',
                'money'   => round(mt_rand(100, 99900) / 100, 2),
                'remark'  => pick($transferRemark),
                'is_me'   => $isMe,
                'user_id' => $user['id'],
                'is_get'  => (bool) random_int(0, 1),
            ];
        }
    }

    // ---- 手机外观随机 ----
    $phone = [
        'single'         => (string) random_int(1, 4),
        'wifi'           => (string) random_int(1, 4),
        'wifi_single'    => (string) random_int(1, 3),
        'time_hour'      => str_pad((string) random_int(0, 23), 2, '0', STR_PAD_LEFT),
        'time_mini'      => str_pad((string) random_int(0, 59), 2, '0', STR_PAD_LEFT),
        'battery_charge' => (string) random_int(0, 1),
        'battery_amount' => random_int(5, 100),
        'ear'            => (string) random_int(0, 1),
        'mode'           => ['width' => 1125, 'height' => 2436, 'radio' => 3, 'zoom' => 0.26],
    ];

    // ---- 设置随机 ----
    $setting = [
        'title'                 => pick($titlePool),
        'message'               => random_int(0, 99),
        'voice'                 => (string) random_int(0, 1),
        'background'            => '',
        'dialog_content'        => '',
        'dialog_money'          => 0,
        'dialog_voice'          => 0,
        'dialog_voice_isread'   => '1',
        'dialog_repacket_remark' => '',
        'dialog_trans_remark'   => '',
        'date_year'             => '',
        'date_month'            => '',
        'date_day'              => '',
        'date_xinqi'            => '',
        'date_shiduan'          => '',
        'date_hour'             => '',
        'date_min'              => '',
    ];

    ok([
        'phone'   => $phone,
        'users'   => $users,
        'dialogs' => $dialogs,
        'setting' => $setting,
    ], 'random ok');
}

/** 生成一个时间提示 dialog */
function makeNotice(int $ts): array
{
    $wday = ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'][(int) date('w', $ts)];
    $h = (int) date('G', $ts);
    $period = periodOfHour($h);
    $content = date('Y年n月j日', $ts) . ' ' . $wday . ' ' . $period . ' ' . date('H:i', $ts);
    return [
        'id'        => 'dialog-' . $ts . '-n-' . randStr(4),
        'type'      => 'notice',
        'content'   => $content,
        'is_system' => true,
    ];
}

// ------------------------------------------------------------
// 3. 系统预设内容
// ------------------------------------------------------------

function apiPresets(): void
{
    $file = __DIR__ . '/presets.json';
    if (!is_file($file)) {
        fail('presets.json not found');
    }
    $json = json_decode(file_get_contents($file), true);
    if (!is_array($json)) {
        fail('presets.json invalid');
    }
    // 只返回列表摘要，不返回完整 dialogs，减少传输
    $list = [];
    foreach ($json as $item) {
        $list[] = [
            'id'          => $item['id'] ?? '',
            'name'        => $item['name'] ?? '',
            'desc'        => $item['desc'] ?? '',
            'tag'         => $item['tag'] ?? '',
            'dialogCount' => count($item['dialogs'] ?? []),
        ];
    }
    ok(['list' => $list]);
}

function apiPreset(): void
{
    $id = $_GET['id'] ?? '';
    if ($id === '') {
        fail('missing id');
    }
    $file = __DIR__ . '/presets.json';
    if (!is_file($file)) {
        fail('presets.json not found');
    }
    $json = json_decode(file_get_contents($file), true);
    if (!is_array($json)) {
        fail('presets.json invalid');
    }
    foreach ($json as $item) {
        if (($item['id'] ?? '') === $id) {
            ok($item);
        }
    }
    fail('preset not found: ' . $id);
}
