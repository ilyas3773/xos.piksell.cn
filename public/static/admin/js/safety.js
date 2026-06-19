/**
 * 凯力在线学习系统 - 安全生产隐患排查模块
 * 包含：安全排查、整改记录、报表、企业端登录记录
 */

// ==================== 安全排查 ====================
function loadInspection(p = 1) {
    loadGenericList('安全排查管理', '/inspection', [
        { title: '序号', render: (_, i, pg) => (pg - 1) * 15 + i + 1 },
        { title: '车牌号', key: 'plate_number' },
        { title: '姓名', key: 'real_name' },
        { title: '手机号', key: 'phone' },
        { title: '是否登记', render: r => r.is_registered == 1 ? '<span class="tag tag-success">已登记</span>' : '<span class="tag tag-danger">未登记</span>' },
        { title: '是否合格', render: r => r.is_qualified == 1 ? '<span class="tag tag-success">合格</span>' : '<span class="tag tag-danger">不合格</span>' },
        { title: '检查时间', render: r => ts(r.check_time) },
    ], p);
}

// ==================== 整改记录归档 ====================
function loadRectification(p = 1) {
    loadGenericList('整改记录归档', '/rectification', [
        { title: '序号', render: (_, i, pg) => (pg - 1) * 15 + i + 1 },
        { title: '车牌号', key: 'plate_number' },
        { title: '姓名', key: 'real_name' },
        { title: '手机号', key: 'phone' },
        { title: '整改项目', key: 'rectification_items' },
        { title: '整改时间', render: r => ts(r.rectification_time) },
    ], p);
}

// ==================== 排查数据报表 ====================
function loadSafetyReport(p = 1) {
    loadGenericList('排查数据报表', '/safety-report', [
        { title: '序号', render: (_, i, pg) => (pg - 1) * 15 + i + 1 },
        { title: '车牌号', key: 'plate_number' },
        { title: '姓名', key: 'real_name' },
        { title: '手机号', key: 'phone' },
        { title: '检查时间', render: r => ts(r.check_time) },
    ], p);
}

// ==================== 企业端登录记录 ====================
function loadLoginLog(p = 1) {
    loadGenericList('企业端登录记录', '/login-log', [
        { title: '序号', render: (_, i, pg) => (pg - 1) * 15 + i + 1 },
        { title: '企业', render: r => r.organization?.name || '--' },
        { title: '登录人', key: 'login_name' },
        { title: '登录时间', render: r => ts(r.login_time || r.create_time) },
    ], p);
}
