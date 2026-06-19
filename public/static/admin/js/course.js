/**
 * 凯力在线学习系统 - 课程管理模块
 * 包含：课程质量评价管理、问答管理
 */

// ==================== 课程质量评价 ====================
function loadEvaluation(p = 1) {
    loadGenericList('课程质量评价管理', '/evaluation', [
        { title: '序号', render: (_, i, pg) => (pg - 1) * 15 + i + 1 },
        { title: '所属课程', render: r => r.course?.title || '--' },
        { title: '评分', render: r => r.score || '--' },
        { title: '评价人', render: r => r.user?.real_name || '--' },
        { title: '创建时间', render: r => ts(r.create_time) },
    ], p);
}

// ==================== 问答管理 ====================
function loadQa(p = 1) {
    loadGenericList('问答管理', '/qa', [
        { title: '序号', render: (_, i, pg) => (pg - 1) * 15 + i + 1 },
        { title: '所属课程', render: r => r.course?.title || '--' },
        { title: '问题标题', key: 'title' },
        { title: '提问人', render: r => r.user?.real_name || '--' },
        { title: '创建时间', render: r => ts(r.create_time) },
        { title: '回复状态', render: r => r.reply_status == 1 ? '<span class="tag tag-success">已回复</span>' : '<span class="tag tag-warning">待回复</span>' },
    ], p);
}
