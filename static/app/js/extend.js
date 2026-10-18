/**
 * 微信对话生成器 - 扩展脚本
 *
 * 依赖：jQuery、Vue（由 common.bundle.js / chat.bundle.js 提供）
 * 功能：
 *   1. 完善时间日期下拉（年份动态到当前年份+1，覆盖 bundle 中过期的 2018-2021）
 *   2. 一键随机生成对话
 *   3. 加载系统预设内容
 *
 * 由 index.php 在 bundle 之后加载。
 */
(function () {
    'use strict';

    // ------------------------------------------------------------
    // 工具
    // ------------------------------------------------------------
    function api(action, params) {
        var url = 'api.php?action=' + encodeURIComponent(action);
        if (params) {
            for (var k in params) {
                if (Object.prototype.hasOwnProperty.call(params, k)) {
                    url += '&' + encodeURIComponent(k) + '=' + encodeURIComponent(params[k]);
                }
            }
        }
        return $.ajax({ url: url, type: 'GET', dataType: 'json' });
    }

    function getVue() {
        var el = document.getElementById('vueApp');
        if (!el) return null;
        // Vue 2 实例挂在 DOM 的 __vue__ 上
        return el.__vue__ || null;
    }

    function waitVue(cb, tryCount) {
        tryCount = tryCount || 0;
        var vm = getVue();
        if (vm && vm.phone && vm.users && vm.dialogs && vm.setting) {
            cb(vm);
        } else if (tryCount < 100) {
            setTimeout(function () { waitVue(cb, tryCount + 1); }, 100);
        }
    }

    // ------------------------------------------------------------
    // 1. 完善时间日期下拉
    // ------------------------------------------------------------
    function fillTimeSelects(timeData) {
        // 清空并填充年份（覆盖 bundle 中过期的 2018-2021）
        var $y = $('.edit-phone-time-year').empty();
        $y.append('<option value="">-</option>');
        timeData.years.forEach(function (it) {
            $y.append('<option value="' + it.value + '">' + it.label + '</option>');
        });

        var $m = $('.edit-phone-time-month').empty();
        $m.append('<option value="">-</option>');
        timeData.months.forEach(function (it) {
            $m.append('<option value="' + it.value + '">' + it.label + '</option>');
        });

        var $d = $('.edit-phone-time-day').empty();
        $d.append('<option value="">-</option>');
        timeData.days.forEach(function (it) {
            $d.append('<option value="' + it.value + '">' + it.label + '</option>');
        });

        // 小时/分钟（手机时间 + 对话时间共用 class）
        var $h = $('.edit-phone-time-hour').empty();
        timeData.hours.forEach(function (it) {
            $h.append('<option value="' + it.value + '">' + it.label + '</option>');
        });

        var $min = $('.edit-phone-time-mini').empty();
        timeData.minutes.forEach(function (it) {
            $min.append('<option value="' + it.value + '">' + it.label + '</option>');
        });

        // 时段补充"晚上"选项（原项目只有上午/下午/凌晨）
        var $p = $('.edit-phone-time-shiduan');
        if ($p.length && $p.find('option[value="晚上"]').length === 0) {
            $p.append('<option value="晚上">晚上</option>');
        }
    }

    // ------------------------------------------------------------
    // 2. 一键随机
    // ------------------------------------------------------------
    function applyData(vm, data) {
        // 用 Vue.set / 直接赋值保证响应式
        if (data.phone) {
            for (var k in data.phone) {
                if (k === 'mode') continue; // 保留原 mode
                vm.$set(vm.phone, k, data.phone[k]);
            }
        }
        if (data.setting) {
            for (var k2 in data.setting) {
                vm.$set(vm.setting, k2, data.setting[k2]);
            }
        }
        if (data.users) {
            // 替换用户数组
            vm.users.splice(0, vm.users.length);
            data.users.forEach(function (u) { vm.users.push(u); });
        }
        if (data.dialogs) {
            vm.dialogs.splice(0, vm.dialogs.length);
            data.dialogs.forEach(function (d) { vm.dialogs.push(d); });
        }
    }

    function onRandom() {
        var $btn = $('#btn-random');
        var old = $btn.html();
        $btn.html('生成中...').prop('disabled', true);
        api('random').then(function (res) {
            if (res.code !== 0) { alert('随机失败：' + res.msg); return; }
            var vm = getVue();
            if (!vm) { alert('页面未就绪'); return; }
            applyData(vm, res.data);
            // 滚到底部
            setTimeout(function () {
                var $body = $('#phone').find('.phone-body');
                $body.scrollTop($body[0].scrollHeight);
            }, 100);
        }).fail(function () {
            alert('请求失败，请检查 api.php');
        }).always(function () {
            $btn.html(old).prop('disabled', false);
        });
    }

    // ------------------------------------------------------------
    // 3. 加载系统预设
    // ------------------------------------------------------------
    function onPresets() {
        var $btn = $('#btn-presets');
        var old = $btn.html();
        $btn.html('加载中...').prop('disabled', true);
        api('presets').then(function (res) {
            if (res.code !== 0) { alert('获取预设失败：' + res.msg); return; }
            showPresetDialog(res.data.list);
        }).fail(function () {
            alert('请求失败，请检查 api.php');
        }).always(function () {
            $btn.html(old).prop('disabled', false);
        });
    }

    function showPresetDialog(list) {
        // 移除已有弹窗
        $('#preset-modal').remove();

        var items = list.map(function (it) {
            return '<div class="preset-item" data-id="' + it.id + '">' +
                '<div class="preset-item-name">' + escapeHtml(it.name) +
                    (it.tag ? '<span class="preset-item-tag">' + escapeHtml(it.tag) + '</span>' : '') +
                '</div>' +
                '<div class="preset-item-desc">' + escapeHtml(it.desc || '') + '</div>' +
                '<div class="preset-item-meta">共 ' + it.dialogCount + ' 条对话</div>' +
            '</div>';
        }).join('');

        var html =
            '<div class="preset-modal" id="preset-modal">' +
                '<div class="preset-modal-mask"></div>' +
                '<div class="preset-modal-box">' +
                    '<div class="preset-modal-header">' +
                        '<span>系统预设内容</span>' +
                        '<a class="preset-modal-close" href="javascript:;" title="关闭">×</a>' +
                    '</div>' +
                    '<div class="preset-modal-body">' +
                        (items || '<div class="preset-empty">暂无预设</div>') +
                    '</div>' +
                    '<div class="preset-modal-footer">点击预设即可加载到编辑区</div>' +
                '</div>' +
            '</div>';

        $('body').append(html);

        var $modal = $('#preset-modal');
        $modal.on('click', '.preset-modal-close, .preset-modal-mask', function () {
            $modal.remove();
        });
        $modal.on('click', '.preset-item', function () {
            var id = $(this).data('id');
            loadPreset(id, $modal);
        });
    }

    function loadPreset(id, $modal) {
        api('preset', { id: id }).then(function (res) {
            if (res.code !== 0) { alert('加载失败：' + res.msg); return; }
            var vm = getVue();
            if (!vm) { alert('页面未就绪'); return; }
            applyData(vm, res.data);
            $modal.remove();
            setTimeout(function () {
                var $body = $('#phone').find('.phone-body');
                $body.scrollTop($body[0].scrollHeight);
            }, 100);
        }).fail(function () {
            alert('请求失败，请检查 api.php');
        });
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    // ------------------------------------------------------------
    // 4. 注入按钮
    // ------------------------------------------------------------
    function injectButtons() {
        if ($('#extend-btn-bar').length) return;
        var html =
            '<div class="extend-btn-bar" id="extend-btn-bar">' +
                '<button type="button" id="btn-random" class="btn btn-success extend-btn">🎲 一键随机</button>' +
                '<button type="button" id="btn-presets" class="btn btn-primary extend-btn">📚 加载预设</button>' +
                '<button type="button" id="btn-now-time" class="btn btn-default extend-btn">🕐 设为当前时间</button>' +
                '<button type="button" id="btnFullscreen" class="btn btn-default extend-btn">🔍 全屏预览</button>' +
                '<button type="button" id="btnShotCurrent" class="btn btn-default extend-btn">📷 下载当前</button>' +
                '<button type="button" id="btnShotLong" class="btn btn-default extend-btn">📋 下载长图</button>' +
            '</div>';
        // 插到 pageHeader 之后
        $('#pageHeader').after(html);

        $('#btn-random').on('click', onRandom);
        $('#btn-presets').on('click', onPresets);
        $('#btn-now-time').on('click', onNowTime);
        $('#btnFullscreen').on('click', openFullscreen);
        $('#btnShotCurrent').on('click', shotCurrent);
        $('#btnShotLong').on('click', shotLong);
    }

    function onNowTime() {
        var vm = getVue();
        if (!vm) return;
        api('time').then(function (res) {
            if (res.code !== 0) return;
            var now = res.data.now;
            vm.$set(vm.phone, 'time_hour', now.hour);
            vm.$set(vm.phone, 'time_mini', now.minute);
        });
    }

    // ------------------------------------------------------------
    // 5. 预览操作：全屏预览 + 截图下载
    // ------------------------------------------------------------
    var HTML2CANVAS_CDN = 'https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js';

    function loadHtml2Canvas() {
        var d = $.Deferred();
        if (window.html2canvas) { d.resolve(window.html2canvas); return d.promise(); }
        var s = document.createElement('script');
        s.src = HTML2CANVAS_CDN;
        s.onload = function () { d.resolve(window.html2canvas); };
        s.onerror = function () { d.reject('html2canvas 加载失败，请检查网络'); };
        document.head.appendChild(s);
        return d.promise();
    }

    // 修复截图问题：清空隐藏文字 + 移除 transform 缩放（html2canvas 对 transform 支持差）
    function cleanHiddenText(clonedDoc) {
        // 1. 移除 .phone-content 的 transform: scale()，避免 html2canvas 渲染重叠
        var pc = clonedDoc.querySelector('.phone-content');
        if (pc) {
            pc.style.transform = 'none';
            pc.style.webkitTransform = 'none';
        }
        // 2. 清空 text-indent:-9999px 隐藏的文字
        var selectors = [
            '.phone-sigle', '.phone-wifi', '.phone-battery font',
            '.phone-nav-back', '.phone-nav-more',
            '.phone-bottom-bar i', '.phone-bottom-chat i'
        ];
        selectors.forEach(function (sel) {
            var els = clonedDoc.querySelectorAll(sel);
            for (var i = 0; i < els.length; i++) els[i].textContent = '';
        });
        try {
            var all = clonedDoc.querySelectorAll('*');
            for (var j = 0; j < all.length; j++) {
                var ti = clonedDoc.defaultView.getComputedStyle(all[j]).textIndent;
                if (ti && parseInt(ti, 10) <= -9999) all[j].textContent = '';
            }
        } catch (e) { /* 忽略 */ }
    }

    function triggerDownload(blob, filename) {
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url; a.download = filename;
        document.body.appendChild(a); a.click();
        document.body.removeChild(a); URL.revokeObjectURL(url);
    }

    function setShotTip(msg) {
        var $t = $('#shotTip');
        if ($t.length) $t.text(msg).show();
    }

    // 全屏预览：截图后全屏自适应展示纯图片
    function openFullscreen() {
        var $phone = $('#phone');
        if (!$phone.length) { alert('未找到预览区域'); return; }
        $('#fsModal').remove();
        // 先显示加载提示
        var $modal = $(
            '<div class="fs-modal" id="fsModal">' +
                '<div class="fs-modal-loading">正在生成截图…</div>' +
            '</div>'
        );
        $('body').append($modal);
        // 点击/ESC 关闭
        $modal.on('click', function () { $('#fsModal').remove(); });
        $(document).one('keydown.fs', function (e) {
            if (e.keyCode === 27) $('#fsModal').remove();
        });
        // 截图
        loadHtml2Canvas().then(function (h2c) {
            h2c($phone[0], {
                scale: 2, useCORS: true, backgroundColor: '#ffffff', logging: false,
                onclone: function (doc) { cleanHiddenText(doc); }
            }).then(function (canvas) {
                var dataUrl = canvas.toDataURL('image/png');
                $modal.empty().append('<img class="fs-modal-img" src="' + dataUrl + '" />');
            }).catch(function (e) {
                $modal.empty().append('<div class="fs-modal-loading">❌ ' + e + '</div>');
            });
        }).fail(function (e) {
            $modal.empty().append('<div class="fs-modal-loading">❌ ' + e + '</div>');
        });
    }

    // 截图当前手机框
    function shotCurrent() {
        var $phone = $('#phone');
        if (!$phone.length) { alert('未找到预览区域'); return; }
        setShotTip('正在生成截图，请稍候…');
        loadHtml2Canvas().then(function (h2c) {
            h2c($phone[0], {
                scale: 2, useCORS: true, backgroundColor: '#ffffff', logging: false,
                onclone: function (doc) { cleanHiddenText(doc); }
            }).then(function (canvas) {
                canvas.toBlob(function (blob) {
                    triggerDownload(blob, 'wechat-current-' + Date.now() + '.png');
                    setShotTip('✅ 当前截图已下载');
                    setTimeout(function () { $('#shotTip').hide(); }, 2500);
                }, 'image/png');
            }).catch(function (e) { setShotTip('❌ ' + e); });
        }).fail(function (e) { setShotTip('❌ ' + e); });
    }

    // 截图长图：截取手机框全部内容（移除高度限制，消息全展开）
    function shotLong() {
        var $phone = $('#phone');
        if (!$phone.length) { alert('未找到预览区域'); return; }
        setShotTip('正在生成长截图，请稍候…');
        loadHtml2Canvas().then(function (h2c) {
            h2c($phone[0], {
                scale: 2, useCORS: true, backgroundColor: '#ffffff', logging: false,
                onclone: function (doc) {
                    cleanHiddenText(doc);
                    // 移除手机框固定高度，让所有消息展开
                    var phone = doc.getElementById('phone');
                    if (phone) {
                        phone.style.height = 'auto';
                        phone.style.minHeight = '0';
                    }
                    // phone-body 从绝对定位+滚动改为静态+自适应，展开全部消息
                    var body = doc.querySelector('.phone-body');
                    if (body) {
                        body.style.position = 'static';
                        body.style.overflow = 'visible';
                        body.style.height = 'auto';
                        body.style.top = 'auto';
                        body.style.bottom = 'auto';
                    }
                    // 隐藏底部输入栏（长图不需要）
                    var bottom = doc.querySelector('.phone-bottom');
                    if (bottom) bottom.style.display = 'none';
                }
            }).then(function (canvas) {
                canvas.toBlob(function (blob) {
                    triggerDownload(blob, 'wechat-long-' + Date.now() + '.png');
                    setShotTip('✅ 长截图已下载');
                    setTimeout(function () { $('#shotTip').hide(); }, 2500);
                }, 'image/png');
            }).catch(function (e) { setShotTip('❌ ' + e); });
        }).fail(function (e) { setShotTip('❌ ' + e); });
    }

    // ------------------------------------------------------------
    // 启动
    // ------------------------------------------------------------
    $(function () {
        // 1. 拉取时间数据，填充下拉
        api('time').then(function (res) {
            if (res.code === 0) {
                fillTimeSelects(res.data);
                window.__timeData = res.data;
            }
        });

        // 2. 等待 Vue 实例就绪后注入按钮
        waitVue(function (vm) {
            injectButtons();
            // 把当前时间作为手机默认时间
            if (window.__timeData) {
                var now = window.__timeData.now;
                vm.$set(vm.phone, 'time_hour', now.hour);
                vm.$set(vm.phone, 'time_mini', now.minute);
            }
        });
    });
})();
