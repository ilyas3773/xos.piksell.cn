const fs = require('fs');

const file = 'public/static/admin/js/card-admin.js';
console.log('正在读取文件...');
let content = fs.readFileSync(file, 'utf8');

// 修复常见的编码问题
const replacements = {
    '分�?': '分类',
    '配�?': '配置',
    '信�?': '信息',
    '优�?': '优化',
    '仪表�?': '仪表盘',
    '订�?': '订单',
    '加载�?': '加载中',
    '已�?': '已选',
    '当前�?': '当前共',
    '最�?': '最多',
    '排序�?': '排序值',
    '状�?': '状态',
    '描�?': '描述',
    '名�?': '名称',
    '创�?': '创建',
    '删�?': '删除',
    '素�?': '素材',
    '资源�?': '资源。',
    '公�?': '公告',
    '提示�?': '提示。',
    '内容�?': '内容。',
    '吗�?': '吗？',
    '�?': '级'
};

console.log('正在修复编码问题...');
let fixCount = 0;
for (const [search, replace] of Object.entries(replacements)) {
    const count = (content.match(new RegExp(search.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g')) || []).length;
    if (count > 0) {
        content = content.split(search).join(replace);
        console.log(`  修复 "${search}" -> "${replace}": ${count} 处`);
        fixCount += count;
    }
}

console.log(`总共修复了 ${fixCount} 处编码问题`);
console.log('正在保存文件...');
fs.writeFileSync(file, content, 'utf8');
console.log('✅ 编码问题已修复！');
console.log('\n请执行以下操作：');
console.log('1. 清除浏览器缓存（Ctrl+F5）');
console.log('2. 刷新管理后台页面');
console.log('3. 测试商品管理功能');
