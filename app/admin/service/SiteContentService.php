<?php
declare(strict_types=1);

namespace app\admin\service;

use app\model\SiteContent;
use InvalidArgumentException;

class SiteContentService
{
    public const KEY_DISCLAIMER = 'disclaimer';

    private const DEFAULTS = [
        self::KEY_DISCLAIMER => [
            'title' => '免责声明',
            'summary' => '请在此处维护免责声明摘要内容。',
            'content' => '请在此填写完整的免责声明内容，前台展示时可直接读取这里的文案。',
        ],
    ];

    public function getContent(string $contentKey): array
    {
        $contentKey = $this->normalizeKey($contentKey);
        $this->syncDefault($contentKey);

        $content = SiteContent::where('content_key', $contentKey)->find();
        return $content?->toArray() ?? [];
    }

    public function saveContent(string $contentKey, array $data): array
    {
        $contentKey = $this->normalizeKey($contentKey);
        $this->syncDefault($contentKey);

        $content = SiteContent::where('content_key', $contentKey)->find();
        if ($content === null) {
            throw new InvalidArgumentException('内容不存在');
        }

        $content->save([
            'title' => trim((string)($data['title'] ?? '')),
            'summary' => trim((string)($data['summary'] ?? '')),
            'content' => trim((string)($data['content'] ?? '')),
            'status' => isset($data['status']) ? (int)$data['status'] : (int)$content->status,
        ]);

        return $content->refresh()->toArray();
    }

    private function syncDefault(string $contentKey): void
    {
        $exists = SiteContent::where('content_key', $contentKey)->find();
        if ($exists !== null) {
            return;
        }

        $default = self::DEFAULTS[$contentKey];
        SiteContent::create([
            'content_key' => $contentKey,
            'title' => $default['title'],
            'summary' => $default['summary'],
            'content' => $default['content'],
            'status' => 1,
        ]);
    }

    private function normalizeKey(string $contentKey): string
    {
        $contentKey = trim(strtolower($contentKey));
        if (!isset(self::DEFAULTS[$contentKey])) {
            throw new InvalidArgumentException('站点内容标识不存在');
        }

        return $contentKey;
    }
}
