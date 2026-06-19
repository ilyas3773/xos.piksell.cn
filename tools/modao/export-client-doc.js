const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const SOURCE_URL =
  'https://modao.cc/proto/vleU6gPXt6247eY5kWf5Qa/sharing?view_mode=read_only&screen=rbpV3a69jcZA5qlu2';
const PASSWORD = 'ckoqy3';
const OUTPUT_DIR = path.resolve(__dirname, '../../docs/modao-client-spec');
const SCREENSHOT_DIR = path.join(OUTPUT_DIR, 'screenshots');

const PAGE_WAIT_MS = 1200;
const VIEWPORT = { width: 2200, height: 2400 };
const DESIGN_SIZE = { width: 750, height: 1624 };

function ensureDir(dirPath) {
  fs.mkdirSync(dirPath, { recursive: true });
}

function escapeMd(text) {
  return String(text).replace(/\|/g, '\\|').replace(/\n/g, ' ');
}

function pad2(value) {
  return String(value).padStart(2, '0');
}

function dedupeStrings(items) {
  const seen = new Set();
  const result = [];
  for (const item of items) {
    const value = String(item || '').trim();
    if (!value || seen.has(value)) {
      continue;
    }
    seen.add(value);
    result.push(value);
  }
  return result;
}

function cleanPageName(text) {
  return String(text || '').replace(/^\s*\d+\s*/, '').trim();
}

async function waitShort(page, ms = PAGE_WAIT_MS) {
  await page.waitForLoadState('networkidle', { timeout: 5000 }).catch(() => {});
  await page.waitForTimeout(ms);
}

async function login(page) {
  await page.goto(SOURCE_URL, { waitUntil: 'networkidle', timeout: 60000 });
  const passwordInput = page.locator('input').first();
  if (await passwordInput.count()) {
    await passwordInput.fill(PASSWORD);
    await page.locator('button').first().click();
    await waitShort(page, 4000);
  }
}

async function switchToClientDemo(page) {
  await page.locator('.preview-switch-btn').nth(1).click();
  await waitShort(page, 2500);
  await page.locator('.screen-name').nth(0).click();
  await waitShort(page, 2500);
}

async function goToClientPage(page, index) {
  await page.locator('.canvas-sortable-list .rn-content-item').nth(index).click();
  await waitShort(page);
}

async function getPageInfo(page) {
  return page.evaluate(() => {
    const footer = document.querySelector('.preview-footer-toolbar .page');
    const activeItem = document.querySelector('.canvas-sortable-list .rn-content-item.active');
    const viewport = document.querySelector('.screen-viewport');
    const activeCanvas = document.querySelector('.pcanvas.active');

    const texts = [...document.querySelectorAll('.screen-viewport .wRichText .text')]
      .map((node) => {
        const rect = node.getBoundingClientRect();
        const viewportRect = viewport.getBoundingClientRect();
        return {
          text: (node.textContent || '').trim(),
          x: rect.x - viewportRect.x,
          y: rect.y - viewportRect.y,
          w: rect.width,
          h: rect.height,
        };
      })
      .filter((item) => item.text)
      .sort((a, b) => a.y - b.y || a.x - b.x);

    const clickables = [...document.querySelectorAll('.screen-viewport .clickable')]
      .map((node, idx) => {
        const rect = node.getBoundingClientRect();
        const viewportRect = viewport.getBoundingClientRect();
        return {
          order: idx,
          cid: node.dataset.cid || '',
          linkCid: node.dataset.link_cid || '',
          className: node.className || '',
          x: rect.x - viewportRect.x,
          y: rect.y - viewportRect.y,
          w: rect.width,
          h: rect.height,
        };
      })
      .filter((item) => item.w > 0 && item.h > 0)
      .sort((a, b) => a.y - b.y || a.x - b.x);

    const canvasCid = activeCanvas ? activeCanvas.getAttribute('data-cid') : '';
    const footerText = footer ? footer.textContent.trim() : '';
    const match = footerText.match(/(\d+)\s*\/\s*(\d+)/);
    const activeText = activeItem ? activeItem.textContent.trim() : '';
    const activeParts = activeText.split('\n').map((part) => part.trim()).filter(Boolean);
    const pageNumber = match ? Number(match[1]) : null;
    const totalPages = match ? Number(match[2]) : null;

    return {
      counterText: footerText,
      pageNumber,
      totalPages,
      activeListText: activeText,
      activeListParts: activeParts,
      pageName: cleanPageName(activeParts.slice(1).join(' ') || activeParts[0] || ''),
      canvasCid,
      url: window.location.href,
      visibleTexts: texts,
      clickables,
      bodyText: (viewport?.innerText || '').replace(/\s+/g, ' ').trim(),
    };
  });
}

function overlapArea(a, b) {
  const width = Math.max(0, Math.min(a.x + a.w, b.x + b.w) - Math.max(a.x, b.x));
  const height = Math.max(0, Math.min(a.y + a.h, b.y + b.h) - Math.max(a.y, b.y));
  return width * height;
}

function distanceScore(a, b) {
  const aCx = a.x + a.w / 2;
  const aCy = a.y + a.h / 2;
  const bCx = b.x + b.w / 2;
  const bCy = b.y + b.h / 2;
  return Math.abs(aCx - bCx) + Math.abs(aCy - bCy);
}

function buildHotspotLabel(hotspot, texts) {
  const matches = texts
    .map((text) => ({
      text: text.text,
      area: overlapArea(hotspot, text),
      dist: distanceScore(hotspot, text),
    }))
    .filter((item) => item.area > 0 || item.dist < 80)
    .sort((a, b) => (b.area - a.area) || (a.dist - b.dist))
    .slice(0, 3);

  if (!matches.length) {
    return '(unlabeled hotspot)';
  }

  return dedupeStrings(matches.map((item) => item.text)).join(' / ');
}

function summarizeKeyTexts(texts) {
  return dedupeStrings(texts.map((item) => item.text)).slice(0, 40);
}

async function captureScreenshot(page, filepath) {
  await page.locator('.screen-viewport').screenshot({ path: filepath });
}

function interactionResult(orig, after) {
  const sameCanvas = orig.canvasCid === after.canvasCid;
  const samePage = orig.pageNumber === after.pageNumber;
  if (!sameCanvas || !samePage) {
    return {
      type: 'navigate',
      targetPageNumber: after.pageNumber,
      targetPageName: after.pageName,
      targetCanvasCid: after.canvasCid,
      targetUrl: after.url,
    };
  }

  if (orig.bodyText !== after.bodyText) {
    return {
      type: 'state_change',
      targetPageNumber: after.pageNumber,
      targetPageName: after.pageName,
      targetCanvasCid: after.canvasCid,
      targetUrl: after.url,
    };
  }

  return {
    type: 'no_visible_change',
    targetPageNumber: after.pageNumber,
    targetPageName: after.pageName,
    targetCanvasCid: after.canvasCid,
    targetUrl: after.url,
  };
}

function buildMarkdown(data) {
  const lines = [];
  lines.push('# 墨刀客户端开发文档');
  lines.push('');
  lines.push('- 项目：冠春工厂系统');
  lines.push(`- 来源：${data.sourceUrl}`);
  lines.push(`- 生成时间：${data.generatedAt}`);
  lines.push(`- 页面范围：客户端 ${data.clientPageCount} 页`);
  lines.push(`- 设计尺寸：${data.designSize.width} x ${data.designSize.height}`);
  lines.push('');
  lines.push('## 使用说明');
  lines.push('');
  lines.push('- 每个页面都包含原型截图、关键文案和点击交互。');
  lines.push('- 截图用于还原 UI，关键文案可直接对照前端文案，交互表用于实现路由或页面状态跳转。');
  lines.push('- 若某个热点没有文字标签，文档会标记为 `(unlabeled hotspot)`，这种情况通常代表纯图片区域或透明点击层。');
  lines.push('');
  lines.push('## 页面索引');
  lines.push('');
  for (const page of data.pages) {
    lines.push(`- ${pad2(page.pageNumber)}. ${page.pageName}`);
  }
  lines.push('');

  for (const page of data.pages) {
    lines.push(`## ${pad2(page.pageNumber)}. ${page.pageName}`);
    lines.push('');
    lines.push(`![${escapeMd(page.pageName)}](./screenshots/${page.screenshotFile})`);
    lines.push('');
    lines.push('- 页面序号：' + page.pageNumber);
    lines.push('- 画布 ID：' + page.canvasCid);
    lines.push('- 页面链接：' + page.url);
    lines.push('');
    lines.push('### 关键文案');
    lines.push('');
    if (page.keyTexts.length) {
      for (const text of page.keyTexts) {
        lines.push(`- ${text}`);
      }
    } else {
      lines.push('- 无可提取文本');
    }
    lines.push('');
    lines.push('### 点击交互');
    lines.push('');
    lines.push('| 序号 | 热点标签 | 触发 | 结果 | 目标页面 | 热点坐标 |');
    lines.push('| --- | --- | --- | --- | --- | --- |');
    if (page.interactions.length) {
      for (const interaction of page.interactions) {
        const targetPage = interaction.result.targetPageNumber
          ? `${interaction.result.targetPageNumber}. ${interaction.result.targetPageName}`
          : '-';
        const coords = `${Math.round(interaction.bounds.x)},${Math.round(interaction.bounds.y)},${Math.round(interaction.bounds.w)},${Math.round(interaction.bounds.h)}`;
        lines.push(
          `| ${interaction.order} | ${escapeMd(interaction.label)} | tap | ${interaction.result.type} | ${escapeMd(targetPage)} | ${coords} |`
        );
      }
    } else {
      lines.push('| - | 无点击交互 | - | - | - | - |');
    }
    lines.push('');
  }

  return lines.join('\n');
}

async function main() {
  ensureDir(OUTPUT_DIR);
  ensureDir(SCREENSHOT_DIR);

  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: VIEWPORT, deviceScaleFactor: 1 });

  try {
    await login(page);
    await switchToClientDemo(page);

    const overviewInfo = await getPageInfo(page);
    const totalPages = overviewInfo.totalPages || 46;
    const pages = [];

    for (let pageIndex = 0; pageIndex < totalPages; pageIndex += 1) {
      await goToClientPage(page, pageIndex);

      const current = await getPageInfo(page);
      const screenshotFile = `page-${pad2(current.pageNumber)}.png`;
      await captureScreenshot(page, path.join(SCREENSHOT_DIR, screenshotFile));

      const pageRecord = {
        pageNumber: current.pageNumber,
        pageName: current.pageName,
        canvasCid: current.canvasCid,
        url: current.url,
        screenshotFile,
        keyTexts: summarizeKeyTexts(current.visibleTexts),
        interactions: [],
      };

      for (let hotspotIndex = 0; hotspotIndex < current.clickables.length; hotspotIndex += 1) {
        const latest = await getPageInfo(page);
        const hotspot = latest.clickables[hotspotIndex];
        if (!hotspot) {
          continue;
        }

        const label = buildHotspotLabel(hotspot, latest.visibleTexts);
        const locator = page.locator('.screen-viewport .clickable').nth(hotspotIndex);
        await locator.click().catch(() => null);
        await waitShort(page, 1600);

        const after = await getPageInfo(page);
        pageRecord.interactions.push({
          order: hotspotIndex + 1,
          label,
          cid: hotspot.cid,
          linkCid: hotspot.linkCid,
          bounds: {
            x: hotspot.x,
            y: hotspot.y,
            w: hotspot.w,
            h: hotspot.h,
          },
          result: interactionResult(latest, after),
        });

        await goToClientPage(page, pageIndex);
      }

      pages.push(pageRecord);
      console.log(`Exported page ${current.pageNumber}/${totalPages}: ${current.pageName}`);
    }

    const data = {
      projectName: '冠春工厂系统',
      sourceUrl: SOURCE_URL,
      generatedAt: new Date().toISOString(),
      clientPageCount: pages.length,
      designSize: DESIGN_SIZE,
      pages,
    };

    fs.writeFileSync(
      path.join(OUTPUT_DIR, 'client_pages.json'),
      JSON.stringify(data, null, 2),
      'utf8'
    );
    fs.writeFileSync(path.join(OUTPUT_DIR, 'README.md'), buildMarkdown(data), 'utf8');
  } finally {
    await browser.close();
  }
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});
