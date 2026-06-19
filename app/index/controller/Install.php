<?php
declare(strict_types=1);

namespace app\index\controller;

use app\BaseController;
use app\service\InstallerService;
use Throwable;

class Install extends BaseController
{
    public function index(): \think\Response
    {
        $service = new InstallerService();
        $requestHost = (string)$this->request->host(true);

        if ($service->isInstalled() && !$this->shouldUseAjax()) {
            return $this->htmlResponse($this->renderInstalledPage($service->getLockMetadata()));
        }

        $values = $service->getDefaultFormValues($requestHost);

        if ($this->shouldUseAjax()) {
            return $this->handleAjax($service, $values, $requestHost);
        }

        return $this->htmlResponse($this->renderFormPage($values));
    }

    private function shouldUseAjax(): bool
    {
        $action = (string)($this->request->post('__action', ''));
        if ($action !== '') {
            return true;
        }
        if ($this->request->isAjax()) {
            return true;
        }
        $accept = strtolower((string)$this->request->header('Accept', ''));
        if (str_contains($accept, 'application/json')) {
            return true;
        }
        $requestedWith = strtolower((string)$this->request->header('X-Requested-With', ''));
        return $requestedWith === 'xmlhttprequest';
    }

    private function handleAjax(InstallerService $service, array $values, string $requestHost): \think\Response
    {
        $post = $this->collectPostData();

        $action = (string)($post['__action'] ?? '');

        if ($action === 'test') {
            $input = array_merge($values, $post);
            $result = $service->testDatabaseConnectionDetailed($input);

            return $this->jsonResponse($result);
        }

        if ($action === 'install') {
            $input = array_merge($values, $post);
            $steps = [];
            $progress = function (string $step, string $message) use (&$steps): void {
                $steps[] = [
                    'step' => $step,
                    'message' => $message,
                    'time' => date('H:i:s'),
                ];
            };

            try {
                $result = $service->install($input, $requestHost, $progress);

                return $this->jsonResponse([
                    'ok' => true,
                    'result' => $result,
                    'steps' => $steps,
                ]);
            } catch (Throwable $exception) {
                return $this->jsonResponse([
                    'ok' => false,
                    'error' => $exception->getMessage(),
                    'steps' => $steps,
                ]);
            }
        }

        if ($action === 'fix_admin_password') {
            $result = $service->fixLegacyAdminPassword();

            return $this->jsonResponse($result);
        }

        return $this->jsonResponse(['ok' => false, 'message' => 'Invalid action']);
    }

    /**
     * 兼容 ThinkPHP 框架读取失败时的情况，从原始 php://input 兜底解析。
     */
    private function collectPostData(): array
    {
        $post = $this->request->post();
        if (!empty($post)) {
            return $post;
        }

        $raw = file_get_contents('php://input');
        if (is_string($raw) && $raw !== '') {
            $contentType = strtolower((string)$_SERVER['CONTENT_TYPE'] ?? '');
            if (strpos($contentType, 'application/json') !== false) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            } else {
                parse_str($raw, $parsed);
                if (is_array($parsed) && !empty($parsed)) {
                    return $parsed;
                }
            }
        }

        return [];
    }

    private function jsonResponse(array $data): \think\Response
    {
        return json($data, 200, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    private function htmlResponse(string $html): \think\Response
    {
        return response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    private function renderInstalledPage(array $metadata): string
    {
        $siteName = $this->escape((string)($metadata['site_name'] ?? '此站点'));
        $adminUsername = $this->escape((string)($metadata['admin_username'] ?? 'admin'));
        $dbName = $this->escape((string)($metadata['db_name'] ?? ''));
        $dbType = $this->escape(strtoupper((string)($metadata['db_type'] ?? 'mysql')));

        return $this->wrapPage(
            '已安装',
            '
            <div class="status-chip done">已安装</div>
            <h1>该站点已完成初始化</h1>
            <p class="lead">' . $siteName . ' 已就绪。安装向导已被锁定，请使用后台账号登录。</p>
            <div class="info-grid">
              <div class="info-card"><span class="label">管理员</span><strong>' . $adminUsername . '</strong></div>
              <div class="info-card"><span class="label">数据库</span><strong>' . $dbType . ' &middot; ' . $dbName . '</strong></div>
            </div>
            <div class="action-row">
              <a class="primary-button" href="/login.html">前往登录</a>
              <a class="outline-button" href="/">打开首页</a>
            </div>
            <div class="action-row" style="margin-top:12px;">
              <button type="button" class="outline-button" id="fixAdminBtn">修复管理员密码（若登录提示错误）</button>
              <span id="fixAdminResult" class="footnote" style="margin-left:12px;"></span>
            </div>
            <p class="footnote">如需重装，请在服务器上删除 <code>config/install.lock</code>，并使用新的数据库。</p>
            <script>
              (function () {
                var btn = document.getElementById("fixAdminBtn");
                var out = document.getElementById("fixAdminResult");
                if (!btn) return;
                btn.addEventListener("click", function () {
                  btn.disabled = true;
                  out.textContent = "处理中...";
                  var body = "__action=fix_admin_password";
                  var xhr = new XMLHttpRequest();
                  xhr.open("POST", location.pathname, true);
                  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                  xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                  xhr.onload = function () {
                    btn.disabled = false;
                    try {
                      var data = JSON.parse(xhr.responseText || "{}");
                      if (data && data.ok) {
                        if (data.unchanged) {
                          out.textContent = "已检查，无需修复";
                        } else {
                          out.textContent = "已修复！新密码：" + (data.new_password || "") + "（账号：" + (data.username || "") + "）";
                        }
                      } else {
                        out.textContent = "失败：" + (data && data.message ? data.message : "未知错误");
                      }
                    } catch (e) {
                      out.textContent = "返回解析失败：" + e.message;
                    }
                  };
                  xhr.onerror = function () {
                    btn.disabled = false;
                    out.textContent = "网络错误";
                  };
                  xhr.send(body);
                });
              })();
            </script>
            '
        );
    }

    private function renderFormPage(array $values, string $errorMessage = '', ?array $testResult = null): string
    {
        $body = '
            <div class="status-chip">一键部署</div>
            <h1>站点初始化</h1>
            <p class="lead">填写数据库信息和管理员账号，一键完成安装。数据库不存在时自动创建，库必须为空。</p>

            <form method="post" class="install-form" id="installForm">
              <input type="hidden" name="__action" value="install" id="actionInput" />

              <div class="form-section" id="mysqlFields">
                <h2>数据库连接信息</h2>
                <div class="field-grid">
                  ' . $this->renderPrefilledInput('db_host', '数据库地址', '127.0.0.1') . '
                  ' . $this->renderPrefilledInput('db_port', '端口', '3306') . '
                </div>
                <label class="field">
                  <span>数据库名</span>
                  <input type="text" name="db_name" value="" placeholder="如：xos_piksell_cn" />
                </label>
                <div class="field-grid">
                  <label class="field">
                    <span>账号</span>
                    <input type="text" name="db_user" value="" placeholder="如：root" />
                  </label>
                  ' . $this->renderPasswordInput('db_pass', '密码', $values, '') . '
                </div>
                <label class="field">
                  <span>字符集</span>
                  <select name="db_charset" class="field-input">
                    <option value="utf8mb4">utf8mb4</option>
                    <option value="utf8">utf8</option>
                    <option value="gbk">gbk</option>
                    <option value="latin1">latin1</option>
                  </select>
                </label>
                <button type="button" class="outline-button" id="testButton">测试连接</button>
                <div id="testResult"></div>
              </div>

              <div class="form-section">
                <h2>管理员账号</h2>
                <div class="field-grid">
                  ' . $this->renderInput('admin_username', '登录名称', $values, 'admin') . '
                </div>
                <div class="field-grid">
                  ' . $this->renderPasswordInput('admin_password', '登录密码', $values, '') . '
                  ' . $this->renderPasswordInput('admin_password_confirm', '确认密码', $values, '') . '
                </div>
                <p class="field-tip">密码至少 6 位</p>
              </div>

              <div class="action-row">
                <button type="button" class="primary-button" id="installButton">开始安装</button>
              </div>
            </form>

            <div id="progressPanel" style="display:none; margin-top:20px;">
              <div class="form-section">
                <h2>安装进度</h2>
                <div id="progressList"></div>
                <div id="progressError" class="error-box" style="display:none; margin-top:14px;"></div>
                <div id="progressSuccess" style="display:none; margin-top:14px;">
                  <div class="success-box">安装成功！将在 <span id="countdown">5</span> 秒后跳转到登录页...</div>
                  <div class="action-row" style="margin-top:12px;">
                    <a class="primary-button" href="/login.html">前往登录</a>
                  </div>
                </div>
              </div>
            </div>
        ';

        return $this->wrapPage('安装向导', $body);
    }

    private function renderInput(string $name, string $label, array $values, string $placeholder): string
    {
        $value = $this->escape((string)($values[$name] ?? ''));
        $placeholder = $this->escape($placeholder);
        $label = $this->escape($label);

        return '
            <label class="field">
              <span>' . $label . '</span>
              <input type="text" name="' . $this->escape($name) . '" value="' . $value . '" placeholder="' . $placeholder . '" />
            </label>
        ';
    }

    /**
     * 预填字段：值用 $defaultValue，placeholder 留作修改提示。
     * 适用于「不填时希望是默认值，但允许修改」的字段。
     */
    private function renderPrefilledInput(string $name, string $label, string $defaultValue, string $placeholder = ''): string
    {
        $value = $this->escape($defaultValue);
        $placeholder = $this->escape($placeholder !== '' ? $placeholder : $defaultValue);
        $label = $this->escape($label);

        return '
            <label class="field">
              <span>' . $label . '</span>
              <input type="text" name="' . $this->escape($name) . '" value="' . $value . '" placeholder="' . $placeholder . '" />
            </label>
        ';
    }

    private function renderPasswordInput(string $name, string $label, array $values, string $placeholder): string
    {
        $placeholder = $this->escape($placeholder);
        $label = $this->escape($label);

        return '
            <label class="field">
              <span>' . $label . '</span>
              <input type="password" name="' . $this->escape($name) . '" value="" placeholder="' . $placeholder . '" />
            </label>
        ';
    }

    private function wrapPage(string $title, string $body): string
    {
        $escapedTitle = $this->escape($title);

        return '<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>' . $escapedTitle . '</title>
  <link rel="stylesheet" href="/install.css">
  <script src="/install.js" defer></script>
</head>
<body>
  <div class="page">
    <div class="shell">' . $body . '</div>
  </div>
</body>
</html>';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
