/**
 * 凯力在线学习系统 - 文件上传 & 富文本编辑器模块
 */

// ==================== 封面上传 ====================

/**
 * 封面预览更新
 */
function updateCoverPreview() {
    const url = document.getElementById('enCover').value;
    const img = document.getElementById('enCoverPreview');
    if (url) { img.src = url; img.style.display = 'block'; }
    else { img.style.display = 'none'; }
}

/**
 * 封面文件上传
 */
async function uploadCoverFile(input) {
    const file = input.files[0];
    if (!file) return;
    const fd = new FormData();
    fd.append('file', file);
    const res = await fetch(API_BASE + '/upload', {
        method: 'POST',
        headers: { 'Authorization': 'Bearer ' + token },
        body: fd
    });
    const data = await res.json();
    if (data?.code === 0 && data?.data?.url) {
        document.getElementById('enCover').value = data.data.url;
        const img = document.getElementById('enCoverPreview');
        img.src = data.data.url;
        img.style.display = 'block';
    } else {
        alert(data?.msg || '上传失败');
    }
    input.value = '';
}

// ==================== 富文本编辑器 ====================

/**
 * 上传编辑器图片
 */
async function uploadEditorImage(input) {
    const file = input.files[0];
    if (!file) return;
    const fd = new FormData();
    fd.append('file', file);
    const res = await fetch(API_BASE + '/upload', {
        method: 'POST',
        headers: { 'Authorization': 'Bearer ' + token },
        body: fd
    });
    const data = await res.json();
    if (data?.code === 0 && data?.data?.url) {
        document.getElementById('enRichEditor').focus();
        document.execCommand('insertImage', false, data.data.url);
    } else {
        alert(data?.msg || '上传失败');
    }
    input.value = '';
}

/**
 * 富文本命令执行
 */
function richCmd(cmd) {
    document.getElementById('enRichEditor').focus();
    document.execCommand(cmd, false, null);
}

/**
 * 富文本字号设置
 */
function richFontSize(size) {
    if (!size) return;
    document.getElementById('enRichEditor').focus();
    document.execCommand('fontSize', false, size);
}

/**
 * 富文本文字颜色
 */
function richFontColor(color) {
    document.getElementById('enRichEditor').focus();
    document.execCommand('foreColor', false, color);
}

/**
 * 富文本背景色（高亮）
 */
function richHiliteColor(color) {
    document.getElementById('enRichEditor').focus();
    document.execCommand('hiliteColor', false, color);
}

/**
 * 插入链接
 */
function richInsertLink() {
    const url = prompt('请输入链接地址：', 'https://');
    if (url) {
        document.getElementById('enRichEditor').focus();
        document.execCommand('createLink', false, url);
    }
}
