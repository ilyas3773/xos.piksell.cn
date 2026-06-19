/**
 * 凯力在线学习系统 - 数据统计模块
 * 包含：月度报表、月安全学习、补学课程、企业安全学习、国语学习统计
 */

// ==================== 月度报表 ====================
function loadMonthlyReport(p = 1) {
    loadGenericList('月度报表', '/monthly-report', [
        { title: '序号', render: (_, i, pg) => (pg - 1) * 15 + i + 1 },
        { title: '月份', key: 'month' },
        { title: '培训单位', render: r => r.organization?.name || '--' },
        { title: '参培人数', key: 'total_users' },
        { title: '完成人数', key: 'completed_users' },
        { title: '完成率', render: r => (r.completion_rate || 0) + '%' },
    ], p);
}

// ==================== 月安全学习 ====================
function loadMonthlySafety(p = 1) {
    loadGenericList('月安全学习', '/monthly-safety', [
        { title: '序号', render: (_, i, pg) => (pg - 1) * 15 + i + 1 },
        { title: '车牌号', key: 'plate_number' },
        { title: '姓名', key: 'real_name' },
        { title: '电话', key: 'phone' },
        { title: '本月进度', render: r => (r.progress || 0) + '%' },
        { title: '考试成绩', render: r => examResult(r.exam_result) },
    ], p);
}

// ==================== 补学课程查询 ====================
function loadSupplementary(p = 1) {
    loadGenericList('补学课程查询', '/supplementary', [
        { title: '序号', render: (_, i, pg) => (pg - 1) * 15 + i + 1 },
        { title: '车牌号', key: 'plate_number' },
        { title: '姓名', key: 'real_name' },
        { title: '电话', key: 'phone' },
        { title: '本月进度', render: r => (r.progress || 0) + '%' },
        { title: '考试成绩', render: r => examResult(r.exam_result) },
    ], p);
}

// ==================== 企业安全学习统计 ====================
function loadEnterpriseSafety(p = 1) {
    loadGenericList('企业安全学习统计', '/enterprise-safety', [
        { title: '序号', render: (_, i, pg) => (pg - 1) * 15 + i + 1 },
        { title: '车牌号', key: 'plate_number' },
        { title: '姓名', key: 'real_name' },
        { title: '电话', key: 'phone' },
        { title: '本月进度', render: r => (r.progress || 0) + '%' },
        { title: '考试成绩', render: r => examResult(r.exam_result) },
    ], p);
}

// ==================== 国语学习统计 ====================
function loadMandarin(p = 1) {
    loadGenericList('国语学习统计', '/mandarin', [
        { title: '序号', render: (_, i, pg) => (pg - 1) * 15 + i + 1 },
        { title: '车牌号', key: 'plate_number' },
        { title: '姓名', key: 'real_name' },
        { title: '电话', key: 'phone' },
        { title: '本月进度', render: r => (r.progress || 0) + '%' },
        { title: '考试成绩', render: r => examResult(r.exam_result) },
    ], p);
}
