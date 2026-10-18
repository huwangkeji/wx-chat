<!DOCTYPE HTML>
<html>
<head>
	<meta charset="UTF-8">
	<title>在线微信对话生成器 - 微信聊天对话制作工具</title>
	<meta name="keywords" content="免费在线微信对话生成器,微信聊天制作,对话生成器" />
	<meta name="description" content="在线微信对话生成器是一款在线微信聊天对话制作的工具，支持文字、语音、红包、转账等多种消息，一键随机生成，加载系统预设" />
	<meta name="render" content="webkit">
	<link rel="icon" href="favicon.png">
	<meta content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport">
	<meta content="telephone=no, address=no" name="format-detection">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
	<?php
	// PHP 服务端渲染：计算当前时间日期，注入到前端
	$__now = getdate();
	$__curYear = (int) date('Y');
	$__pageData = [
		'now' => [
			'year'    => (string) $__curYear,
			'month'   => (string) $__now['mon'],
			'day'     => (string) $__now['mday'],
			'hour'    => str_pad((string) $__now['hours'], 2, '0', STR_PAD_LEFT),
			'minute'  => str_pad((string) $__now['minutes'], 2, '0', STR_PAD_LEFT),
			'weekday' => ['日','一','二','三','四','五','六'][$__now['wday']],
		],
		'yearRange' => [2018, $__curYear + 1],
	];
	?>
	<script type="text/javascript">
		var _path = { images: '' }
		var page = { data: {} }
		// PHP 注入的页面数据（当前时间、年份范围）
		window.__PAGE_DATA__ = <?php echo json_encode($__pageData, JSON_UNESCAPED_UNICODE); ?>;
	</script>
	<link rel="stylesheet" href="static/app/css/zui.min.css">
	<link rel="stylesheet" href="static/app/css/app.css">
	<link rel="stylesheet" href="static/app/css/wallet.css">
	<link rel="stylesheet" href="static/app/css/extend.css">
	<script type="text/javascript" src="static/app/js/common.bundle.js"></script>
	<script type="text/javascript" src="static/app/js/chat.bundle.js"></script>
</head>
<body>
	<div id="vueApp" class="wrapper">
		<div class="container-fluid page-content">
			<div id="pageHeader">微信对话生成器 <small style="font-size:14px;color:#999;font-weight:normal;"><?php echo date('Y年n月j日'); ?> 星期<?php echo ['日','一','二','三','四','五','六'][(int)date('w')]; ?></small></div>
			<div class="edit-content">
				<div class="tab">
					<ul class="nav nav-tabs">
						<li class="active"><a data-tab href="#tabContent1">外观设置</a></li>
						<li><a data-tab href="#tabContent2">对话设置</a></li>
					</ul>
					<div class="tab-content">
						<div class="tab-pane active" id="tabContent1">
							<!-- 外观设置 -->
							<div class="view-setting">
								<div class="view-set-item">
									<span>手机信号：</span>
									<select v-model="phone.single" class="form-control">
										<option value="1">1格</option>
										<option value="2">2格</option>
										<option value="3">3格</option>
										<option value="4" selected="selected">4格</option>
									</select>
								</div>
								<div class="view-set-item">
									<span>网络信号：</span>
									<select v-model="phone.wifi" class="form-control">
										<option value="1">wifi</option>
										<option value="2">3G</option>
										<option value="3">4G</option>
										<option value="4">5G</option>
									</select>
								</div>
								<div class="view-set-item">
									<span>wifi信号：</span>
									<select v-model="phone.wifi_single" class="form-control">
										<option value="1">1格</option>
										<option value="2">2格</option>
										<option value="3" selected="selected">3格</option>
									</select>
								</div>
								<div class="view-set-item">
									<span>手机时间：</span>
									<select v-model="phone.time_hour" class="form-control edit-phone-time-hour"></select>
									<select v-model="phone.time_mini" class="form-control edit-phone-time-mini"></select>
								</div>
								<div class="view-set-item">
									<span>充电中：</span>
									<label class="my-radio"><input type="radio" name="charge"v-model="phone.battery_charge" value="1"> 是</label>
									<label class="my-radio"><input type="radio" name="charge"v-model="phone.battery_charge" value="0" checked> 否</label>
								</div>
								<div class="view-set-item">
									<span>手机电量：</span>
									<input @change="onBatteryChange($event)" type="range" v-model="phone.battery_amount"
										min="0" max="100" value="50" />
									&nbsp;&nbsp;&nbsp;{{phone.battery_amount}}%
								</div>
								<div class="view-set-item">
									<span>听筒模式：</span>
									<label class="my-radio"><input type="radio" name="phone-ear" v-model="phone.ear" value="1"> 是</label>
									<label class="my-radio"><input type="radio" name="phone-ear" v-model="phone.ear" value="0" checked> 否</label>
								</div>
								<div class="view-set-item">
									<span>消息数目：</span>
									<input type="number" name="phone-msg-count" class="form-control" v-model.number="setting.message">
								</div>
								<div class="view-set-item">
									<span>聊天标题：</span>
									<input type="text" class="form-control" v-model="setting.title">
								</div>
								<div class="view-set-item">
									<span>语音模式：</span>
									<label class="my-radio"><input type="radio" name="phone-voice" v-model="setting.voice" value="1"> 是</label>
									<label class="my-radio"><input type="radio" name="phone-voice" v-model="setting.voice" value="0" checked> 否</label>
								</div>
								<div class="view-set-item">
									<span>聊天背景：</span>
									<a class="a-phone-set-bg" href="javascript:;" title="选择背景图">
										<font>+</font>
										<img class="img-phone-set-bg" :src="setting.background" />
										<input @change="setBackground($event)" type="file" />
										<span @click="deleteBackground" v-if="setting.background != ''">x</span>
									</a>
								</div>
							</div>
						</div>
						<!-- 对话设置 -->
						<div class="tab-pane" id="tabContent2">
							<div class="dialog-user">
								<div class="dialog-user-title step-card-head"><span class="step-num">1</span><span class="step-title">用户管理</span><span class="step-hint">点击头像换图，点"选择用户"设为发送人（第一个是自己）</span></div>
								<div class="dialog-user-items">
									<div class="dialog-user-item el-remove">
										<div class="dialog-user-face">
											<a class="dialog-user-face-a" href="javascript:;">
												<input type="file" placeholder="点击更换头像">
												<i></i>
											</a>
											<input type="text" value="微信对话生成器" />
											<a class="dialog-user-select">选择用户</a>
											<a class="dialog-user-delete">x</a>
										</div>
									</div>
									<div class="dialog-user-item el-remove">
										<div class="dialog-user-face">
											<a class="dialog-user-face-a" href="javascript:;">
												<input type="file" placeholder="点击更换头像">
												<i style="background-image: url(/static/app/images/10014.jpg)"></i>
											</a>
											<input type="text" value="马先生" />
											<a class="dialog-user-select">选择用户</a>
											<a class="dialog-user-delete">x</a>
										</div>
									</div>
									<div v-for="(user, index) in users" class="dialog-user-item">
										<div class="dialog-user-face">
											<a class="dialog-user-face-a" href="javascript:;">
												<input @change="setUserImage($event, index)" type="file"
													placeholder="点击更换头像">
												<i :style="'background-image: url(' + user.image + ')'"></i>
											</a>
											<input type="text" v-model="user.name" />
											<a @click="selectUser(index)"
												:class="['dialog-user-select', {'dialog-user-selected':user.selected}]">选择用户</a>
											<a v-if="!user.is_me" @click="delUser(index)" class="dialog-user-delete"
												title="删除用户">x</a>
										</div>
									</div>
									<div class="dialog-user-item">
										<a @click="addUser" class="dialog-user-item-add" href="javascript:;"
											title="添加用户">+</a>
									</div>
								</div>

								<!-- ② 消息内容 -->
								<div class="step-card">
									<div class="step-card-head">
										<span class="step-num">2</span><span class="step-title">消息内容</span>
										<span class="step-hint">当前发送人：<b>{{getSender()}}</b>，先在上面选好用户，再在此输入文字</span>
									</div>
									<div class="step-card-body">
										<textarea v-model="setting.dialog_content" class="edit-dialog-text form-control" placeholder="在此输入文字消息内容…"></textarea>
									</div>
								</div>

								<!-- ③ 消息属性（按需填写） -->
								<div class="step-card">
									<div class="step-card-head">
										<span class="step-num">3</span><span class="step-title">消息属性</span>
										<span class="step-hint">根据要添加的消息类型按需填写，不用可留空</span>
									</div>
									<div class="step-card-body">
										<div class="prop-grid">
											<div class="prop-item">
												<label>红包/转账金额</label>
												<input v-model.number="setting.dialog_money" type="number" class="form-control" placeholder="0.00">
											</div>
											<div class="prop-item">
												<label>语音时长（秒，1-60）</label>
												<input v-model.number="setting.dialog_voice" type="number" class="form-control" placeholder="如 5">
											</div>
											<div class="prop-item">
												<label>红包备注</label>
												<input v-model="setting.dialog_repacket_remark" type="text" class="form-control" placeholder="如 恭喜发财">
											</div>
											<div class="prop-item">
												<label>转账备注</label>
												<input v-model="setting.dialog_trans_remark" type="text" class="form-control" placeholder="如 转账给你">
											</div>
										</div>
										<div class="prop-item prop-voice-read">
											<label>语音是否已读：</label>
											<label class="my-radio"><input type="radio" v-model="setting.dialog_voice_isread" value="1" checked> 已读</label>
											<label class="my-radio"><input type="radio" v-model="setting.dialog_voice_isread" value="0"> 未读</label>
										</div>
										<div class="prop-time">
											<label class="prop-time-label">聊天时间提示<span>（仅"添加时间"按钮使用）</span></label>
											<div class="time-row">
												<select v-model="setting.date_year" class="form-control edit-phone-time-year"><option value="">年</option></select>
												<select v-model="setting.date_month" class="form-control edit-phone-time-month"><option value="">月</option></select>
												<select v-model="setting.date_day" class="form-control edit-phone-time-day"><option value="">日</option></select>
												<select v-model="setting.date_xinqi" class="form-control"><option value="">星期</option><option value="星期一">星期一</option><option value="星期二">星期二</option><option value="星期三">星期三</option><option value="星期四">星期四</option><option value="星期五">星期五</option><option value="星期六">星期六</option><option value="星期日">星期日</option></select>
												<select v-model="setting.date_shiduan" class="form-control"><option value="">时段</option><option value="上午">上午</option><option value="下午">下午</option><option value="凌晨">凌晨</option><option value="晚上">晚上</option></select>
												<select v-model="setting.date_hour" class="form-control edit-phone-time-hour"><option value="">时</option></select>
												<span class="time-colon">:</span>
												<select v-model="setting.date_min" class="form-control edit-phone-time-mini"><option value="">分</option></select>
											</div>
										</div>
									</div>
								</div>

								<!-- ④ 添加到对话 -->
								<div class="step-card">
									<div class="step-card-head">
										<span class="step-num">4</span><span class="step-title">添加到对话</span>
										<span class="step-hint">点对应按钮，把上面填写的内容加入聊天预览</span>
									</div>
									<div class="step-card-body">
										<div class="btn-groups">
											<div class="btn-group">
												<button @click="addTextDialog" class="btn btn-success">💬 添加文字</button>
												<button class="btn btn-primary btn-send-image">🖼️ 添加图片<input @change="addImageDialog($event)" type="file"></button>
												<button @click="addVoiceDialog" class="btn btn-primary">🎤 添加语音</button>
											</div>
											<div class="btn-group">
												<button @click="addNoticeDialog" class="btn btn-primary">🕐 添加时间</button>
												<button @click="addRedpacketDialog" class="btn btn-primary">🧧 添加红包</button>
												<button @click="addTransferDialog" class="btn btn-primary">💸 添加转账</button>
											</div>
										</div>
										<button class="btn-clear" @click="cleanDialogs" href="javascript:;">🗑️ 清空聊天内容</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- phone -->
				<div class="phone-wrap">
					<div class="preview-badge" id="previewBadge">
						<span class="preview-badge-dot"></span>
						<span class="preview-badge-text">实时预览</span>
					</div>
					<div class="phone-content">
						<div id="phone" class="phone">
							<div class="phone-top">
								<div class="phone-bar">
									<div class="phone-time">{{phone.time_hour}}:{{phone.time_mini}}</div>
									<div
										:class="['phone-sigle', {'phone-sigle-v1':phone.single == 1}, {'phone-sigle-v2':phone.single == 2}, {'phone-sigle-v3':phone.single == 3}, {'phone-sigle-v4':phone.single == 4}]">
										信号</div>
									<div
										:class="['phone-wifi', {'phone-wifi-v2':phone.wifi > 1}, phone.wifi == 1 ? 'phone-wifi-s' + phone.wifi_single : '']">
										<template v-if="phone.wifi == 1">
											wifi
										</template>
										<template v-else-if="phone.wifi == 2">
											3G
										</template>
										<template v-else-if="phone.wifi == 3">
											4G
										</template>
										<template v-else-if="phone.wifi == 4">
											5G
										</template>
									</div>
									<div :class="['phone-battery', {'phone-battery-charge':phone.battery_charge == 1}]">
										<span>
											<font :style="[{width: phone.battery_amount + '%'}]">电量</font>
											<i></i>
										</span>
									</div>
								</div>
								<div class="phone-nav">
									<div class="phone-nav-left">
										<div class="phone-nav-back">返回</div>
										<span v-if="setting.message && setting.message > 0">{{setting.message}}</span>
									</div>
									<div class="phone-nav-center">
										<span>
											<font>{{setting.title}}</font>
											<i v-if="phone.ear == 1"></i>
										</span>
									</div>
									<div class="phone-nav-right">
										<div class="phone-nav-more">更多</div>
									</div>
								</div>
							</div>

							<div class="phone-bg">
								<img @load="onBackgroundLoad($event)" :src="setting.background" />
							</div>

							<div class="phone-water"></div>

							<div class="phone-body">
								<div class="wechat-content">
									<template v-for="(dialog, index) in dialogs">
										<div :class="['wechat-dialog', {'wechat-dialog-right':dialog.is_me}]">
											<div v-if="dialog.type != 'notice'" class="wechat-dialog-face">
												<img :src="getUserById(dialog.user_id).image" />
											</div>

											<template v-if="dialog.type == 'text'">
												<div class="wechat-dialog-text">{{dialog.content}}</div>
											</template>

											<template v-if="dialog.type == 'image'">
												<div
													:class="['wechat-dialog-text', 'wechat-dialog-image', {'wechat-dialog-image-noborder':setting.background != ''}]">
													<img :src="dialog.image">
												</div>
											</template>

											<template v-if="dialog.type == 'voice'">
												<div class="wechat-dialog-text wechat-dialog-voice">
													<div v-if="dialog.is_me"
														:style="{width: getVoiceLength(dialog.time) + 'px'}"></div>
													<span v-if="dialog.is_me">{{dialog.time}}"</span>
													<i></i>
													<span v-if="!dialog.is_me">{{dialog.time}}"</span>
													<div v-if="!dialog.is_me"
														:style="{width: getVoiceLength(dialog.time) + 'px'}"></div>
													<em v-if="!dialog.is_me && dialog.isread == '0'"></em>
												</div>
											</template>

											<template v-if="dialog.type == 'notice'">
												<div class="wechat-dialog-notice">
													<span
														:class="[{'wechat-dialog-notice-has-bg':setting.background != ''},{'wechat-dialog-notice-system':dialog.is_system}]"
														v-html="dialog.content"></span>
												</div>
											</template>

											<template v-if="dialog.type == 'transfer' || dialog.type == 'redpacket'">
												<div
													:class="['wechat-dialog-text', 'wechat-dialog-trans', {'wechat-dialog-trans-get':dialog.is_get}]">
													<div
														:class="['wechat-dialog-trans-content', {'wechat-dialog-redp-content':dialog.type == 'redpacket'}]">
														<i></i>
														<div>
															<span
																v-if="dialog.type == 'transfer'">¥{{moneyFormat(dialog.money,
																2, '', false)}}</span>
															<font v-if="dialog.type == 'transfer'">{{dialog.remark}}
															</font>
															<span
																v-if="dialog.type == 'redpacket'">{{dialog.remark}}</span>
															<font v-if="dialog.type == 'redpacket' && dialog.is_get">
																已领取</font>
														</div>
													</div>
													<div class="wechat-dialog-trans-bottom">
														<span v-if="dialog.type == 'transfer'">微信转账</span>
														<span v-if="dialog.type == 'redpacket'">微信红包</span>
													</div>
												</div>
											</template>

											<a @click="deleteDialog(index)" class="a-wechat-dialog-del"
												href="javascript:;" title="删除对话">X</a>

											<div v-if="!dialog.is_get && dialog.type == 'redpacket'"
												class="a-wechat-dialog-menu">
												<a @click="redpacketGet(index)">领取</a>
											</div>
											<div v-if="!dialog.is_get && dialog.type == 'transfer'"
												class="a-wechat-dialog-menu">
												<a @click="transferGet(index)">领取</a>
											</div>
										</div>
									</template>

									<div class="wechat-dialog el-remove">
										<div class="wechat-dialog-notice">
											<span class="wechat-dialog-notice-has-bg">2019年5月20日 下午4:20</span>
										</div>
										<a class="a-wechat-dialog-del" href="javascript:;" title="删除对话">X</a>
									</div>
									<div class="wechat-dialog wechat-dialog-right el-remove">
										<div class="wechat-dialog-face">
											<img src="static/app/images/user-face.png" />
										</div>
										<div class="wechat-dialog-text wechat-dialog-voice">
											<span>1"</span><i></i>
										</div>
										<a class="a-wechat-dialog-del" href="javascript:;" title="删除对话">X</a>
									</div>
									<div class="wechat-dialog el-remove">
										<div class="wechat-dialog-face">
											<img src="static/app/images/user-face.png" />
										</div>
										<div class="wechat-dialog-text wechat-dialog-voice">
											<i></i><span>1"</span><em></em>
										</div>
										<a class="a-wechat-dialog-del" href="javascript:;" title="删除对话">X</a>
									</div>
									<div class="wechat-dialog wechat-dialog-right el-remove">
										<div class="wechat-dialog-face">
											<img src="static/app/images/user-face.png" />
										</div>
										<div class="wechat-dialog-text wechat-dialog-image"><img
												src="static/app/images/dialog-demo1.jpeg"></div>
										<a class="a-wechat-dialog-del" href="javascript:;" title="删除对话">X</a>
									</div>
									<div class="wechat-dialog wechat-dialog-right el-remove">
										<div class="wechat-dialog-face">
											<img src="static/app/images/user-face.png" />
										</div>
										<div class="wechat-dialog-text">
											hellohelhellohellohellohellohellohellolohello</div>
										<a class="a-wechat-dialog-del" href="javascript:;" title="删除对话">X</a>
									</div>
									<div class="wechat-dialog el-remove">
										<div class="wechat-dialog-face">
											<img src="static/app/images/user-face.png" />
										</div>
										<div class="wechat-dialog-text wechat-dialog-trans">
											<div class="wechat-dialog-trans-content">
												<i></i>
												<div>
													<span>¥1.00</span>
													<font>转账给微截图</font>
												</div>
											</div>
											<div class="wechat-dialog-trans-bottom">
												<span>微信转账</span>
											</div>
										</div>
										<a class="a-wechat-dialog-del" href="javascript:;" title="删除对话">X</a>
										<div class="a-wechat-dialog-menu">
											<a>领取</a>
										</div>
									</div>
									<div class="wechat-dialog el-remove">
										<div class="wechat-dialog-face">
											<img src="static/app/images/user-face.png" />
										</div>
										<div class="wechat-dialog-text wechat-dialog-trans wechat-dialog-trans-get">
											<div class="wechat-dialog-trans-content">
												<i></i>
												<div>
													<span>¥1.00</span>
													<font>转账给微截图</font>
												</div>
											</div>
											<div class="wechat-dialog-trans-bottom">
												<span>微信转账</span>
											</div>
										</div>
										<a class="a-wechat-dialog-del" href="javascript:;" title="删除对话">X</a>
									</div>
									<div class="wechat-dialog wechat-dialog-right el-remove">
										<div class="wechat-dialog-face">
											<img src="static/app/images/user-face.png" />
										</div>
										<div class="wechat-dialog-text wechat-dialog-trans">
											<div class="wechat-dialog-trans-content">
												<i></i>
												<div>
													<span>¥1.00</span>
													<font>转账给微截图</font>
												</div>
											</div>
											<div class="wechat-dialog-trans-bottom">
												<span>微信转账</span>
											</div>
										</div>
										<a class="a-wechat-dialog-del" href="javascript:;" title="删除对话">X</a>
									</div>
									<div class="wechat-dialog wechat-dialog-right el-remove">
										<div class="wechat-dialog-face">
											<img src="static/app/images/user-face.png" />
										</div>
										<div class="wechat-dialog-text wechat-dialog-trans wechat-dialog-trans-get">
											<div class="wechat-dialog-trans-content">
												<i></i>
												<div>
													<span>¥1.00</span>
													<font>转账给微截图</font>
												</div>
											</div>
											<div class="wechat-dialog-trans-bottom">
												<span>微信转账</span>
											</div>
										</div>
										<a class="a-wechat-dialog-del" href="javascript:;" title="删除对话">X</a>
									</div>

									<div class="wechat-dialog el-remove">
										<div class="wechat-dialog-face">
											<img src="static/app/images/user-face.png" />
										</div>
										<div class="wechat-dialog-text wechat-dialog-trans">
											<div class="wechat-dialog-trans-content wechat-dialog-redp-content">
												<i></i>
												<div>
													<span>恭喜发财，大吉大利</span>
												</div>
											</div>
											<div class="wechat-dialog-trans-bottom">
												<span>微信红包</span>
											</div>
										</div>
										<a class="a-wechat-dialog-del" href="javascript:;" title="删除对话">X</a>
									</div>
									<div class="wechat-dialog wechat-dialog-right el-remove">
										<div class="wechat-dialog-face">
											<img src="static/app/images/user-face.png" />
										</div>
										<div class="wechat-dialog-text wechat-dialog-trans wechat-dialog-trans-get">
											<div class="wechat-dialog-trans-content wechat-dialog-redp-content">
												<i></i>
												<div>
													<span>恭喜发财，大吉大利</span>
												</div>
											</div>
											<div class="wechat-dialog-trans-bottom">
												<span>微信红包</span>
											</div>
										</div>
										<a class="a-wechat-dialog-del" href="javascript:;" title="删除对话">X</a>
									</div>
								</div>
							</div>

							<div class="phone-bottom">
								<div class="phone-bottom-chat">
									<div class="wechat-bottom">
										<div
											:class="['wechat-bottom-icon', 'wechat-voice-icon', {'wechat-voice-say-icon':setting.voice == 1}]">
											语音</div>
										<div :class="['wechat-input', {'wechat-input-say':setting.voice == 1}]">
											<template v-if="setting.voice == 1">按住 说话</template>
											<template v-else>输入框</template>
										</div>
										<div class="wechat-bottom-icon wechat-emoji-icon">表情</div>
										<div class="wechat-bottom-icon wechat-more-icon">更多功能</div>
									</div>
								</div>
								<div class="phone-bottom-bar">
									<i>返回桌面</i>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- 扩展脚本：一键随机 / 加载预设 / 完善时间日期（PHP 重构新增） -->
	<script type="text/javascript" src="static/app/js/extend.js"></script>
</body>

</html>