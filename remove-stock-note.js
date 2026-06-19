// 删除库存说明和导入按钮
const fs = require('fs');

const file = 'public/static/admin/js/card-admin.js';
console.log('正在读取文件...');
let content = fs.readFileSync(file, 'utf8');

// 要删除的内容（从 '<div class="form-row full">' 开始，到对应的 '</div>' 结束）
const toRemove = `            + '<div class="form-row full">'
            + '<label>库存说明</label>'
            + '<div class="product-stock-note">库存会根据当前商品下"未使用卡密"的数量自动同步，所以这里不能手填修改。要增加库存，请导入卡密；要减少库存，请删除卡密或把卡密改成锁定/作废。</div>'
            + (isEdit
                ? '<div class="product-stock-actions"><button type="button" class="btn btn-small" id="openProductStockImportBtn">导入卡密补库存</button></div>'
                : '<div class="muted">先创建商品，保存后再导入卡密补库存。</div>')
            + '</div>'`;

if (content.includes(toRemove)) {
    content = content.replace(toRemove, '');
    console.log('✅ 已删除库存说明和导入按钮');
} else {
    console.log('❌ 未找到要删除的内容');
    console.log('尝试查找部分内容...');
    if (content.includes('库存说明')) {
        console.log('✅ 找到"库存说明"文本');
    }
    if (content.includes('导入卡密补库存')) {
        console.log('✅ 找到"导入卡密补库存"文本');
    }
}

console.log('正在保存文件...');
fs.writeFileSync(file, content, 'utf8');
console.log('✅ 文件已保存！');
console.log('\n请刷新浏览器测试（Ctrl+F5）');
