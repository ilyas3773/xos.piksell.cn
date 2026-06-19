<?php
declare(strict_types=1);

namespace app\index\controller;

use app\BaseController;
use app\index\service\ShopService;
use app\service\InstallerService;

class Index extends BaseController
{
    public function index(): \think\Response
    {
        $installer = new InstallerService();

        if (!$installer->isInstalled()) {
            return response('', 302, ['Location' => '/install']);
        }

        return $this->htmlResponse($this->renderOfficialSite());
    }

    private function htmlResponse(string $html): \think\Response
    {
        return response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    private function renderOfficialSite(): string
    {
        $siteName = 'Piksell Store';
        $tagline = '';
        $copyrightText = '';
        $contactEmail = '';
        $featuredProducts = [];
        $latestProducts = [];
        $announcement = null;

        try {
            $shop = new ShopService();
            $homeData = $shop->getHome();
            $svc = $homeData['service'] ?? [];
            $siteName = trim((string)($svc['site_name'] ?? '')) ?: 'Piksell Store';
            $tagline = trim((string)($svc['tagline'] ?? ''));
            $copyrightText = trim((string)($svc['copyright_text'] ?? ''));
            $contactEmail = trim((string)($svc['contact_email'] ?? ''));
            $featuredProducts = array_slice($homeData['featured_products'] ?? [], 0, 4);
            $latestProducts = array_slice($homeData['latest_products'] ?? [], 0, 4);
            $announcement = $homeData['announcement'] ?? null;
        } catch (\Throwable) {
            // 安装后但数据库读取失败时降级渲染
        }

        $siteNameEsc = htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8');
        $taglineEsc = htmlspecialchars($tagline, ENT_QUOTES, 'UTF-8');
        $year = date('Y');

        $productCards = '';
        $hasProducts = !empty($featuredProducts) || !empty($latestProducts);

        if ($hasProducts) {
            $showProducts = !empty($featuredProducts) ? $featuredProducts : $latestProducts;
            foreach ($showProducts as $p) {
                $name = htmlspecialchars((string)($p['name'] ?? '未知商品'), ENT_QUOTES, 'UTF-8');
                $categoryName = htmlspecialchars((string)($p['category_name'] ?? ''), ENT_QUOTES, 'UTF-8');
                $displayImage = trim((string)($p['display_image'] ?? ''));
                $energy = (int)($p['exchange_energy'] ?? 0);
                $productId = (int)($p['id'] ?? 0);

                $thumbStyle = "background: linear-gradient(135deg, rgba(79,124,255,0.22), rgba(34,211,238,0.12));";
                if ($displayImage !== '') {
                    $thumbStyle = "background:#0f172a; background-image: url('" . htmlspecialchars($displayImage, ENT_QUOTES, 'UTF-8') . "'); background-size: cover; background-position: center;";
                }
                $categoryLabel = $categoryName !== '' ? htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8') : '数字商品';
                $productCards .= '
        <a href="/product/' . $productId . '" class="product-card">
          <div class="product-thumb" style="' . $thumbStyle . '"></div>
          <div class="product-info">
            <div class="product-name">' . $name . '</div>
            <div class="product-meta">' . $categoryLabel . ' · 能量兑换</div>
            <div class="product-price">' . $energy . ' 能量</div>
          </div>
        </a>';
            }
        } else {
            $dummyProducts = [
                ['name' => '游戏点卡', 'category' => '游戏', 'icon' => '&#127918;'],
                ['name' => '软件授权', 'category' => '应用', 'icon' => '&#128421;'],
                ['name' => '会员订阅', 'category' => '服务', 'icon' => '&#127760;'],
                ['name' => '更多精品', 'category' => '持续上新', 'icon' => '&#11088;'],
            ];
            foreach ($dummyProducts as $dp) {
                $productCards .= '
        <div class="product-card">
          <div class="product-thumb" style="background:linear-gradient(135deg, rgba(79,124,255,0.20), rgba(34,211,238,0.10)); display:flex; align-items:center; justify-content:center; font-size:36px;">' . $dp['icon'] . '</div>
          <div class="product-info">
            <div class="product-name">' . htmlspecialchars($dp['name'], ENT_QUOTES, 'UTF-8') . '</div>
            <div class="product-meta">' . htmlspecialchars($dp['category'], ENT_QUOTES, 'UTF-8') . '</div>
            <div class="product-price">&#12288; 查看详情</div>
          </div>
        </div>';
            }
        }

        $announcementHtml = '';
        if ($announcement !== null) {
            $annTitle = htmlspecialchars((string)($announcement['title'] ?? ''), ENT_QUOTES, 'UTF-8');
            $annContent = htmlspecialchars(mb_substr((string)($announcement['content'] ?? ''), 0, 120, 'UTF-8'), ENT_QUOTES, 'UTF-8');
            if ($annTitle !== '') {
                $announcementHtml = '
        <div class="announcement-bar">
          <span class="ann-label">&#128227; 公告</span>
          <span class="ann-title">' . $annTitle . '</span>
          <span class="ann-desc">' . $annContent . '</span>
        </div>';
            }
        }

        $taglineSection = $taglineEsc !== ''
            ? '<p>' . $taglineEsc . '</p>'
            : '<p>聚合热门数字产品，一键下单、自动发货。注册即送积分能量，兑换心仪商品，享受流畅的数字生活体验。</p>';

        return '<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>' . $siteNameEsc . '</title>
  <style>
    :root {
      --text: #e6eaf5;
      --muted: #9aa3b2;
      --primary: #4f7cff;
      --primary-2: #a78bfa;
      --accent: #22d3ee;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    html, body {
      min-height: 100vh;
      font-family: "PingFang SC", "Microsoft YaHei", "Segoe UI", sans-serif;
      color: var(--text);
      background:
        radial-gradient(circle at 20% 8%, rgba(79, 124, 255, 0.20), transparent 30%),
        radial-gradient(circle at 80% 18%, rgba(34, 211, 238, 0.14), transparent 26%),
        linear-gradient(135deg, #020617 0%, #0b1023 55%, #0f1a3a 100%);
      background-attachment: fixed;
      -webkit-font-smoothing: antialiased;
    }

    body::before {
      content: "";
      position: fixed;
      inset: 0;
      background:
        radial-gradient(circle at 15% 15%, rgba(79, 124, 255, 0.07), transparent 32%),
        radial-gradient(circle at 85% 75%, rgba(34, 211, 238, 0.05), transparent 28%),
        linear-gradient(180deg, rgba(255, 255, 255, 0.02), transparent 40%);
      pointer-events: none;
      z-index: 0;
    }

    a { color: inherit; text-decoration: none; }

    .container {
      position: relative;
      z-index: 1;
      max-width: 1080px;
      margin: 0 auto;
      padding: 0 24px;
    }

    /* ── Header ── */
    header {
      position: sticky;
      top: 0;
      z-index: 100;
      backdrop-filter: blur(16px) saturate(140%);
      -webkit-backdrop-filter: blur(16px) saturate(140%);
      border-bottom: 1px solid rgba(255, 255, 255, 0.07);
      background: rgba(2, 6, 23, 0.60);
    }

    .header-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 62px;
    }

    .logo {
      font-size: 18px;
      font-weight: 800;
      letter-spacing: 0.06em;
      color: #f1f5f9;
    }

    .logo span { color: var(--primary); }

    nav {
      display: flex;
      align-items: center;
      gap: 6px;
    }

    nav a {
      padding: 8px 14px;
      border-radius: 10px;
      font-size: 14px;
      color: var(--muted);
      transition: color 0.2s, background 0.2s;
    }

    nav a:hover {
      color: var(--text);
      background: rgba(255, 255, 255, 0.05);
    }

    .btn-primary {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 9px 18px;
      border-radius: 10px;
      background: linear-gradient(135deg, var(--primary), var(--primary-2));
      color: #fff;
      font-size: 14px;
      font-weight: 700;
      transition: opacity 0.2s, box-shadow 0.2s;
      box-shadow: 0 8px 24px rgba(79, 124, 255, 0.30);
    }

    .btn-primary:hover {
      opacity: 0.90;
      box-shadow: 0 12px 32px rgba(79, 124, 255, 0.45);
    }

    /* ── Announcement ── */
    .announcement-bar {
      background: rgba(79, 124, 255, 0.10);
      border-bottom: 1px solid rgba(79, 124, 255, 0.18);
      padding: 10px 0;
      text-align: center;
      font-size: 13px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      flex-wrap: wrap;
    }

    .ann-label {
      font-weight: 700;
      color: var(--primary);
      letter-spacing: 0.05em;
    }

    .ann-title {
      color: #f1f5f9;
      font-weight: 600;
    }

    .ann-desc {
      color: var(--muted);
      max-width: 400px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    /* ── Hero ── */
    .hero {
      padding: 80px 0 60px;
      text-align: center;
    }

    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 6px 14px;
      border-radius: 999px;
      background: rgba(79, 124, 255, 0.12);
      border: 1px solid rgba(79, 124, 255, 0.25);
      font-size: 12px;
      font-weight: 600;
      color: var(--primary);
      letter-spacing: 0.08em;
      margin-bottom: 22px;
    }

    .hero-badge::before {
      content: "";
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: var(--primary);
      animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: 0.5; transform: scale(1.4); }
    }

    .hero h1 {
      font-size: clamp(34px, 6vw, 56px);
      font-weight: 900;
      line-height: 1.12;
      letter-spacing: -0.02em;
      color: #f1f5f9;
      margin-bottom: 16px;
    }

    .hero h1 em {
      font-style: normal;
      background: linear-gradient(135deg, var(--primary), var(--accent));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .hero > .container > p {
      font-size: 16px;
      line-height: 1.75;
      color: var(--muted);
      max-width: 560px;
      margin: 0 auto 36px;
    }

    .hero-actions {
      display: flex;
      justify-content: center;
      gap: 14px;
      flex-wrap: wrap;
    }

    .btn-lg {
      padding: 12px 28px;
      border-radius: 14px;
      font-size: 15px;
      font-weight: 700;
    }

    .btn-outline {
      display: inline-flex;
      align-items: center;
      padding: 12px 28px;
      border-radius: 14px;
      border: 1px solid rgba(255, 255, 255, 0.12);
      background: rgba(255, 255, 255, 0.04);
      color: var(--text);
      font-size: 15px;
      font-weight: 700;
      transition: background 0.2s, border-color 0.2s;
    }

    .btn-outline:hover {
      background: rgba(255, 255, 255, 0.08);
      border-color: rgba(255, 255, 255, 0.22);
    }

    /* ── Section shared ── */
    .section { padding: 60px 0; }

    .section-header {
      text-align: center;
      margin-bottom: 40px;
    }

    .section-label {
      font-size: 12px;
      font-weight: 700;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      color: var(--accent);
      margin-bottom: 10px;
    }

    .section-title {
      font-size: 28px;
      font-weight: 800;
      color: #f1f5f9;
      margin-bottom: 10px;
    }

    .section-desc {
      font-size: 15px;
      color: var(--muted);
      max-width: 480px;
      margin: 0 auto;
    }

    /* ── Features ── */
    .features-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 18px;
    }

    .feature-card {
      padding: 22px 20px;
      border-radius: 18px;
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.09);
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .feature-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 16px 40px rgba(2, 6, 23, 0.4);
    }

    .feature-icon {
      width: 42px;
      height: 42px;
      border-radius: 11px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      margin-bottom: 14px;
    }

    .feature-card h3 {
      font-size: 15px;
      font-weight: 700;
      color: #f1f5f9;
      margin-bottom: 7px;
    }

    .feature-card p {
      font-size: 13px;
      color: var(--muted);
      line-height: 1.65;
    }

    /* ── Products ── */
    .products-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
    }

    .product-card {
      border-radius: 16px;
      overflow: hidden;
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.09);
      transition: transform 0.2s, box-shadow 0.2s;
      display: block;
    }

    .product-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 20px 50px rgba(2, 6, 23, 0.5);
    }

    .product-thumb {
      width: 100%;
      aspect-ratio: 16/9;
    }

    .product-info {
      padding: 13px 14px;
    }

    .product-name {
      font-size: 14px;
      font-weight: 700;
      color: #f1f5f9;
      margin-bottom: 5px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .product-meta {
      font-size: 12px;
      color: var(--muted);
      margin-bottom: 9px;
    }

    .product-price {
      font-size: 14px;
      font-weight: 800;
      color: var(--accent);
    }

    /* ── Stats ── */
    .stats-row {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
    }

    .stat-card {
      text-align: center;
      padding: 26px 14px;
      border-radius: 18px;
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.09);
    }

    .stat-num {
      font-size: 30px;
      font-weight: 900;
      background: linear-gradient(135deg, var(--primary), var(--accent));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      line-height: 1.2;
      margin-bottom: 6px;
    }

    .stat-label {
      font-size: 13px;
      color: var(--muted);
    }

    /* ── CTA ── */
    .cta-section { padding: 70px 0; }

    .cta-box {
      border-radius: 24px;
      padding: 48px;
      text-align: center;
      background:
        radial-gradient(circle at 50% 0%, rgba(79, 124, 255, 0.22), transparent 55%),
        rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(79, 124, 255, 0.20);
      position: relative;
      overflow: hidden;
    }

    .cta-box::before {
      content: "";
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at 30% 80%, rgba(34, 211, 238, 0.10), transparent 40%);
      pointer-events: none;
    }

    .cta-box h2 {
      font-size: 26px;
      font-weight: 800;
      color: #f1f5f9;
      margin-bottom: 12px;
    }

    .cta-box p {
      font-size: 15px;
      color: var(--muted);
      margin-bottom: 28px;
    }

    /* ── Footer ── */
    footer {
      position: relative;
      z-index: 1;
      border-top: 1px solid rgba(255, 255, 255, 0.07);
      padding: 26px 0;
    }

    .footer-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 12px;
    }

    .footer-copy {
      font-size: 13px;
      color: var(--muted);
    }

    .footer-links {
      display: flex;
      gap: 20px;
    }

    .footer-links a {
      font-size: 13px;
      color: var(--muted);
      transition: color 0.2s;
    }

    .footer-links a:hover { color: var(--text); }

    /* ── Divider ── */
    .divider {
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.10), transparent);
    }

    /* ── Responsive ── */
    @media (max-width: 900px) {
      .features-grid { grid-template-columns: repeat(2, 1fr); }
      .products-grid { grid-template-columns: repeat(2, 1fr); }
      .stats-row { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 600px) {
      .features-grid { grid-template-columns: 1fr; }
      .products-grid { grid-template-columns: repeat(2, 1fr); }
      .stats-row { grid-template-columns: repeat(2, 1fr); }
      .cta-box { padding: 32px 20px; }
      nav { display: none; }
      .hero { padding: 50px 0 40px; }
      .ann-desc { display: none; }
    }
  </style>
</head>
<body>

  <!-- Header -->
  <header>
    <div class="container header-inner">
      <div class="logo">' . $siteNameEsc . '<span>.</span></div>
      <nav>
        <a href="#features">功能</a>
        <a href="#products">商品</a>
        <a href="#about">关于</a>
        <a href="/admin.html">管理后台</a>
      </nav>
      <a href="/login.html" class="btn-primary btn-lg">用户登录</a>
    </div>
  </header>

  <!-- Announcement -->
  ' . $announcementHtml . '

  <!-- Hero -->
  <section class="hero">
    <div class="container">
      <div class="hero-badge">数字产品即开即用</div>
      <h1>发现精品数字商品<br><em>轻松兑换 即时到手</em></h1>
      ' . $taglineSection . '
      <div class="hero-actions">
        <a href="/login.html" class="btn-primary btn-lg">立即开始</a>
        <a href="#products" class="btn-outline btn-lg">浏览商品</a>
      </div>
    </div>
  </section>

  <div class="divider"></div>

  <!-- Features -->
  <section class="section" id="features">
    <div class="container">
      <div class="section-header">
        <div class="section-label">Features</div>
        <div class="section-title">为什么选择我们</div>
        <div class="section-desc">安全、快捷、透明，为用户提供极致的数字商品购买体验。</div>
      </div>
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon" style="background:rgba(79,124,255,0.15);">&#128640;</div>
          <h3>自动发货</h3>
          <p>下单后系统自动发送卡密，无需等待人工处理，全年 24 小时即时到账。</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon" style="background:rgba(34,211,238,0.12);">&#128274;</div>
          <h3>安全可靠</h3>
          <p>卡密加密存储，全站 HTTPS 传输，账户安全有保障。</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon" style="background:rgba(167,139,250,0.12);">&#9889;</div>
          <h3>能量积分</h3>
          <p>注册即送积分，每日签到获取能量，邀请好友获得奖励，兑换更划算。</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon" style="background:rgba(52,211,153,0.12);">&#128241;</div>
          <h3>移动适配</h3>
          <p>完美支持手机访问，随时随地浏览商品、管理订单，流畅体验。</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon" style="background:rgba(251,146,60,0.12);">&#128172;</div>
          <h3>即时客服</h3>
          <p>遇到问题随时联系客服，获得专业解答，服务响应迅速。</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon" style="background:rgba(248,113,113,0.12);">&#128200;</div>
          <h3>实时统计</h3>
          <p>商品点击、兑换数据全链路追踪，数据驱动运营决策。</p>
        </div>
      </div>
    </div>
  </section>

  <div class="divider"></div>

  <!-- Products -->
  <section class="section" id="products">
    <div class="container">
      <div class="section-header">
        <div class="section-label">Products</div>
        <div class="section-title">热门商品</div>
        <div class="section-desc">精选热门品类，持续更新，满足不同用户需求。</div>
      </div>
      <div class="products-grid">
        ' . $productCards . '
      </div>
    </div>
  </section>

  <div class="divider"></div>

  <!-- Stats -->
  <section class="section">
    <div class="container">
      <div class="stats-row">
        <div class="stat-card">
          <div class="stat-num">24/7</div>
          <div class="stat-label">全天候运营</div>
        </div>
        <div class="stat-card">
          <div class="stat-num">99.9%</div>
          <div class="stat-label">服务可用性</div>
        </div>
        <div class="stat-card">
          <div class="stat-num">&lt;3s</div>
          <div class="stat-label">平均发货速度</div>
        </div>
        <div class="stat-card">
          <div class="stat-num">AES256</div>
          <div class="stat-label">数据加密</div>
        </div>
      </div>
    </div>
  </section>

  <div class="divider"></div>

  <!-- CTA -->
  <section class="cta-section" id="about">
    <div class="container">
      <div class="cta-box">
        <h2>准备好开始了吗？</h2>
        <p>注册账户，获取积分能量，探索海量数字商品。</p>
        <div class="hero-actions">
          <a href="/login.html" class="btn-primary btn-lg">立即注册</a>
          <a href="/admin.html" class="btn-outline btn-lg">管理后台</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <div class="container footer-inner">
      <div class="footer-copy">' . ($copyrightText !== '' ? htmlspecialchars($copyrightText, ENT_QUOTES, 'UTF-8') : '&copy; ' . $year . ' ' . $siteNameEsc . '. All rights reserved.') . '</div>
      <div class="footer-links">
        <a href="/login.html">用户登录</a>
        <a href="/admin.html">管理后台</a>
        ' . ($contactEmail !== '' ? '<a href="mailto:' . htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8') . '">联系我们</a>' : '') . '
      </div>
    </div>
  </footer>

  <script>
    document.querySelectorAll("a[href^=\"#\"]").forEach(function(el) {
      el.addEventListener("click", function(e) {
        var target = document.querySelector(this.getAttribute("href"));
        if (target) {
          e.preventDefault();
          target.scrollIntoView({ behavior: "smooth", block: "start" });
        }
      });
    });
  </script>

</body>
</html>';
    }
}
