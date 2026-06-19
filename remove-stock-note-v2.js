// 删除库存说明和导入按钮 - 使用正则表达式
const fs = require('fs');

const file = 'public/static/admin/js/card-admin.js';
console.log('正在读取文件...');
let content = fs.readFileSync(file, 'utf8');

// 使用正则表达式匹配从 '<label>库存说明</label>' 到下一个 '</div>' 之间的所有内容
// 包括整个 form-row full 容器
const pattern = /\s+\+ '<div class="form-row full">'\s+\+ '<label>库存说明<\/label>'[\s\S]*?\+ '<\/div>'/;

const match = content.match(pattern);
if (match) {
    console.log('找到匹配内容，长度：', match[0].length);
    console.log('匹配内容预览：', match[0].substring(0, 100) + '...');
    content = content.replace(pattern, '');
    console.log('✅ 已删除库存说明和导入按钮');
} else {
    console.log('❌ 未找到匹配的内容');
}

console.log('正在保存文件...');
fs.writeFileSync(file, content, 'utf8');
console.log('✅ 文件已保存！');
console.log('\n请刷新浏览器测试（Ctrl+F5）');
