/**
 * 凯力在线学习系统 - 公告管理 & 单位重要通知模块
 */

// ==================== 公告管理 ====================
let noticePage = 1;

async function loadNotice(page = 1) {
    noticePage = page;
    const main = document.getElementById('mainContent');
    main.innerHTML = '<div class="loading">加载中...</div>';
    const title = document.getElementById('noticeSearch')?.value || '';
    const data = await api(`/notice?page=${page}&limit=15&title=${encodeURIComponent(title)}`);
    const list = data?.data || [];
    const total = data?.count || 0;

    main.innerHTML = `
        <div class="card">
            <div class="card-title">公告管理</div>
            <div class="toolbar">
                <input type="text" id="noticeSearch" placeholder="搜索公告标题" value="${title}" onkeyup="if(event.key==='Enter')loadNotice()">
                <button class="btn btn-primary" onclick="loadNotice()">搜索</button>
                <button class="btn btn-success" onclick="showAddNotice()">添加公告</button>
            </div>
            <table>
                <thead><tr><th>序号</th><th>公告标题</th><th>发布时间</th><th>状态</th><th>操作</th></tr></thead>
                <tbody>${list.length ? list.map((item, idx) => `
                    <tr>
                        <td>${(page - 1) * 15 + idx + 1}</td>
                        <td>${esc(item.title)}</td>
                        <td>${ts(item.publish_time)}</td>
                        <td>${item.status == 1 ? '<span class="tag tag-success">正常</span>' : '<span class="tag tag-danger">禁用</span>'}</td>
                        <td>
                            <button class="operate-btn" onclick="showEditNotice(${item.id})">编辑</button>
                            <button class="operate-btn danger" onclick="deleteNotice(${item.id})">删除</button>
                        </td>
                    </tr>
                `).join('') : '<tr><td colspan="5" class="empty-data">暂无数据</td></tr>'}</tbody>
            </table>
            ${paginationHTML(total, page, 15, 'loadNotice')}
        </div>
    `;
}

function showAddNotice() {
    openModal('添加公告', `
        <div class="form-row"><label>公告标题</label><input type="text" id="noticeTitle"></div>
        <div class="form-row"><label>公告内容</label><textarea id="noticeContent"></textarea></div>
    `, `<button class="btn btn-primary" onclick="saveNotice()">保存</button>`);
}

async function saveNotice() {
    const title = document.getElementById('noticeTitle').value;
    const content = document.getElementById('noticeContent').value;
    if (!title) { alert('请输入标题'); return; }
    await api('/notice', { method: 'POST', body: JSON.stringify({ title, content, status: 1 }) });
    closeModal(); loadNotice();
}

async function showEditNotice(id) {
    const data = await api(`/notice/${id}`);
    const item = data?.data;
    if (!item) return;
    openModal('编辑公告', `
        <div class="form-row"><label>公告标题</label><input type="text" id="noticeTitle" value="${esc(item.title)}"></div>
        <div class="form-row"><label>公告内容</label><textarea id="noticeContent">${esc(item.content || '')}</textarea></div>
        <div class="form-row"><label>状态</label><select id="noticeStatus"><option value="1" ${item.status == 1 ? 'selected' : ''}>正常</option><option value="0" ${item.status == 0 ? 'selected' : ''}>禁用</option></select></div>
    `, `<button class="btn btn-primary" onclick="updateNotice(${id})">保存</button>`);
}

async function updateNotice(id) {
    const title = document.getElementById('noticeTitle').value;
    const content = document.getElementById('noticeContent').value;
    const status = document.getElementById('noticeStatus').value;
    await api(`/notice/${id}`, { method: 'PUT', body: JSON.stringify({ title, content, status: parseInt(status) }) });
    closeModal(); loadNotice(noticePage);
}

async function deleteNotice(id) {
    if (!confirm('确定要删除这条公告吗？')) return;
    await api(`/notice/${id}`, { method: 'DELETE' });
    loadNotice(noticePage);
}

// ==================== 单位重要通知{企业} ====================
let enterpriseNoticePage = 1;

function loadEnterpriseNotice(p = 1) {
    enterpriseNoticePage = p;
    const main = document.getElementById('mainContent');
    main.innerHTML = '<div class="loading">加载中...</div>';
    const keyword = document.getElementById('enSearchTitle')?.value || '';
    api(`/enterprise-notice?page=${p}&limit=15&title=${encodeURIComponent(keyword)}`).then(data => {
        const list = data?.data || [];
        const total = data?.count || 0;
        main.innerHTML = `
            <div class="card">
                <div class="card-title">单位重要通知{企业}</div>
                <div class="toolbar">
                    <input type="text" id="enSearchTitle" placeholder="搜索新闻名称" value="${esc(keyword)}" onkeyup="if(event.key==='Enter')loadEnterpriseNotice()">
                    <button class="btn btn-primary" onclick="loadEnterpriseNotice()">搜索</button>
                    <button class="btn btn-success" onclick="showAddEnterpriseNotice()">添加</button>
                </div>
                <table>
                    <thead><tr>
                        <th>序号</th><th>资讯标题</th><th>资讯封面</th><th>资讯内容</th>
                        <th>创建时间</th><th>创建人</th><th>更新时间</th><th>更新人</th><th>操作</th>
                    </tr></thead>
                    <tbody>${list.length ? list.map((item, idx) => `
                        <tr>
                            <td>${(p - 1) * 15 + idx + 1}</td>
                            <td>${esc(item.title)}</td>
                            <td>${item.cover ? '<img src="' + esc(item.cover) + '" style="width:60px;height:40px;object-fit:cover;border-radius:4px;">' : '--'}</td>
                            <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${esc(stripHtml(item.content || ''))}</td>
                            <td style="white-space:nowrap;">${ts(item.create_time)}</td>
                            <td>${item.create_by || '--'}</td>
                            <td style="white-space:nowrap;">${ts(item.update_time)}</td>
                            <td>${item.update_by || '--'}</td>
                            <td style="white-space:nowrap;">
                                <button class="operate-btn" onclick="showEditEnterpriseNotice(${item.id})">修改</button>
                                <button class="operate-btn danger" onclick="deleteEnterpriseNotice(${item.id})">删除</button>
                            </td>
                        </tr>
                    `).join('') : '<tr><td colspan="9" class="empty-data">暂无数据</td></tr>'}</tbody>
                </table>
                ${paginationHTML(total, p, 15, 'loadEnterpriseNotice')}
            </div>
        `;
    });
}

function showAddEnterpriseNotice() {
    openModal('新闻资讯', getEnNoticeFormHTML(), `<button class="btn btn-primary" onclick="saveEnterpriseNotice()">保存</button>`, true);
}

async function saveEnterpriseNotice() {
    const title = document.getElementById('enTitle').value;
    const cover = document.getElementById('enCover').value;
    const content = document.getElementById('enRichEditor').innerHTML;
    if (!title) { alert('请输入资讯标题'); return; }
    if (!content || content === '<br>') { alert('请输入资讯内容'); return; }
    await api('/enterprise-notice', { method: 'POST', body: JSON.stringify({ title, cover, content }) });
    closeModal(); loadEnterpriseNotice(enterpriseNoticePage);
}

async function showEditEnterpriseNotice(id) {
    const data = await api(`/enterprise-notice/${id}`);
    const item = data?.data;
    if (!item) return;
    openModal('新闻资讯', getEnNoticeFormHTML(item), `<button class="btn btn-primary" onclick="updateEnterpriseNotice(${id})">保存</button>`, true);
}

async function updateEnterpriseNotice(id) {
    const title = document.getElementById('enTitle').value;
    const cover = document.getElementById('enCover').value;
    const content = document.getElementById('enRichEditor').innerHTML;
    if (!title) { alert('请输入资讯标题'); return; }
    await api(`/enterprise-notice/${id}`, { method: 'PUT', body: JSON.stringify({ title, cover, content }) });
    closeModal(); loadEnterpriseNotice(enterpriseNoticePage);
}

async function deleteEnterpriseNotice(id) {
    if (!confirm('确定要删除这条通知吗？')) return;
    await api(`/enterprise-notice/${id}`, { method: 'DELETE' });
    loadEnterpriseNotice(enterpriseNoticePage);
}

function getEnNoticeFormHTML(item) {
    const titleVal = item ? esc(item.title) : '';
    const coverVal = item ? esc(item.cover || '') : '';
    const contentVal = item ? (item.content || '') : '';
    const coverPreview = coverVal ? `<img class="preview-img" id="enCoverPreview" src="${coverVal}">` : `<img class="preview-img" id="enCoverPreview" style="display:none">`;
    return `
        <div class="form-row">
            <label><span style="color:red">*</span> 资讯标题</label>
            <input type="text" id="enTitle" value="${titleVal}" placeholder="请输入资讯标题">
        </div>
        <div class="form-row">
            <label>资讯封面</label>
            <div class="cover-upload">
                <input type="text" id="enCover" value="${coverVal}" placeholder="请输入封面图片URL" style="flex:1" oninput="updateCoverPreview()">
                <span class="or-text">或</span>
                <label class="upload-btn">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="#fff"><path d="M9 16h6v-6h4l-7-7-7 7h4v6zm-4 2h14v2H5v-2z"/></svg>
                    上传
                    <input type="file" accept="image/*" style="display:none" onchange="uploadCoverFile(this)">
                </label>
            </div>
            ${coverPreview}
        </div>
        <div class="form-row">
            <label><span style="color:red">*</span> 资讯内容</label>
            <div class="rich-toolbar">
                <button title="加粗" onclick="richCmd('bold')"><b>B</b></button>
                <button title="斜体" onclick="richCmd('italic')"><i>I</i></button>
                <button title="下划线" onclick="richCmd('underline')"><u>U</u></button>
                <button title="删除线" onclick="richCmd('strikeThrough')" style="text-decoration:line-through">S</button>
                <div class="sep"></div>
                <button title="左对齐" onclick="richCmd('justifyLeft')" style="font-size:16px">≡</button>
                <button title="居中" onclick="richCmd('justifyCenter')" style="font-size:16px">≣</button>
                <button title="右对齐" onclick="richCmd('justifyRight')" style="font-size:16px">≢</button>
                <div class="sep"></div>
                <button title="无序列表" onclick="richCmd('insertUnorderedList')">&#8226;</button>
                <button title="有序列表" onclick="richCmd('insertOrderedList')">1.</button>
                <div class="sep"></div>
                <select onchange="richFontSize(this.value);this.selectedIndex=0" style="height:28px;border:1px solid #d9d9d9;border-radius:3px;font-size:12px;padding:0 4px;">
                    <option value="">字号</option>
                    <option value="1">12pt</option><option value="3">14pt</option><option value="4">16pt</option>
                    <option value="5">18pt</option><option value="6">24pt</option><option value="7">36pt</option>
                </select>
                <div class="sep"></div>
                <label title="文字颜色" style="cursor:pointer;display:inline-flex;align-items:center;height:28px;padding:0 4px;">
                    <span style="font-weight:bold;font-size:14px;color:#333;">A</span>
                    <input type="color" value="#000000" onchange="richFontColor(this.value)" style="width:20px;height:20px;border:none;padding:0;cursor:pointer;">
                </label>
                <label title="背景色" style="cursor:pointer;display:inline-flex;align-items:center;height:28px;padding:0 4px;">
                    <span style="font-weight:bold;font-size:14px;background:#ff0;padding:0 2px;">A</span>
                    <input type="color" value="#ffff00" onchange="richHiliteColor(this.value)" style="width:20px;height:20px;border:none;padding:0;cursor:pointer;">
                </label>
                <div class="sep"></div>
                <button title="插入链接" onclick="richInsertLink()">&#128279;</button>
                <label style="cursor:pointer;display:inline-flex;align-items:center;gap:4px;padding:0 8px;height:28px;border:1px solid #1890ff;border-radius:3px;background:#fff;font-size:12px;color:#1890ff;">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="#1890ff"><path d="M9 16h6v-6h4l-7-7-7 7h4v6zm-4 2h14v2H5v-2z"/></svg>
                    上传图片
                    <input type="file" accept="image/*" style="display:none" onchange="uploadEditorImage(this)">
                </label>
                <button title="清除格式" onclick="richCmd('removeFormat')" style="font-size:12px;">T×</button>
            </div>
            <div class="rich-editor" id="enRichEditor" contenteditable="true">${contentVal}</div>
        </div>
    `;
}
