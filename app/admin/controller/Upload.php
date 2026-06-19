<?php
declare(strict_types=1);

namespace app\admin\controller;

use RuntimeException;
use think\facade\Filesystem;
use think\file\UploadedFile;

class Upload extends BaseController
{
    private const MAX_IMAGE_SIZE = 5242880;
    private const REMOTE_CONNECT_TIMEOUT = 15;
    private const REMOTE_TIMEOUT = 45;
    private const REMOTE_USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 PiksellAdmin/1.0';
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private const ALLOWED_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    public function image(): \think\Response
    {
        $directory = $this->normalizeDirectory((string)$this->request->post('dir', 'products'));
        $file = $this->request->file('file');

        try {
            if ($file instanceof UploadedFile) {
                return $this->success($this->saveUploadedImage($file, $directory), '图片上传成功');
            }

            $imageUrl = trim((string)$this->request->post('image_url', ''));
            if ($imageUrl !== '') {
                return $this->success($this->saveRemoteImage($imageUrl, $directory), '图片保存成功');
            }
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage());
        }

        return $this->error('请上传图片文件或提供图片链接');
    }

    private function saveUploadedImage(UploadedFile $file, string $directory): array
    {
        if (!$file->isValid()) {
            throw new RuntimeException('上传文件无效');
        }

        $extension = strtolower($file->extension());
        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new RuntimeException('仅支持 jpg、jpeg、png、gif、webp 图片');
        }

        $size = (int)$file->getSize();
        if ($size <= 0 || $size > self::MAX_IMAGE_SIZE) {
            throw new RuntimeException('图片大小不能超过5MB');
        }

        $datePath = date('Ymd');
        $fileName = md5((string)$file->getOriginalName() . microtime(true) . random_int(1000, 9999)) . '.' . $extension;
        $storedPath = Filesystem::disk('public')->putFileAs($directory . '/' . $datePath, $file, $fileName);
        if ($storedPath === false) {
            throw new RuntimeException('图片保存失败');
        }

        return $this->buildPayload((string)$storedPath, $file->getOriginalName(), $size);
    }

    private function saveRemoteImage(string $imageUrl, string $directory): array
    {
        if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            throw new RuntimeException('图片链接格式不正确');
        }

        $scheme = strtolower((string)parse_url($imageUrl, PHP_URL_SCHEME));
        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new RuntimeException('图片链接仅支持 http 或 https');
        }

        $download = $this->downloadRemoteImageWithFallback($imageUrl);
        $mime = strtolower((string)($download['mime'] ?? ''));
        $content = (string)($download['content'] ?? '');
        $size = strlen($content);

        if ($content === '' || $size <= 0) {
            throw new RuntimeException('远程图片内容为空');
        }

        if ($size > self::MAX_IMAGE_SIZE) {
            throw new RuntimeException('远程图片大小不能超过5MB');
        }

        $extension = self::ALLOWED_MIMES[$mime] ?? '';
        if ($extension === '') {
            $pathExtension = strtolower(pathinfo((string)parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION));
            if (in_array($pathExtension, self::ALLOWED_EXTENSIONS, true)) {
                $extension = $pathExtension;
            }
        }

        if ($extension === '') {
            throw new RuntimeException('远程链接不是受支持的图片格式');
        }

        $datePath = date('Ymd');
        $fileName = md5($imageUrl . microtime(true)) . '.' . $extension;
        $storedPath = trim($directory . '/' . $datePath . '/' . $fileName, '/');
        $result = Filesystem::disk('public')->put($storedPath, $content);
        if ($result === false) {
            throw new RuntimeException('远程图片保存失败');
        }

        $originalName = basename((string)parse_url($imageUrl, PHP_URL_PATH)) ?: ('remote.' . $extension);

        return $this->buildPayload($storedPath, $originalName, $size);
    }

    private function downloadRemoteImage(string $imageUrl): array
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($imageUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => self::REMOTE_CONNECT_TIMEOUT,
                CURLOPT_TIMEOUT => self::REMOTE_TIMEOUT,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_ENCODING => '',
                CURLOPT_USERAGENT => self::REMOTE_USER_AGENT,
                CURLOPT_HTTPHEADER => [
                    'Accept: image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                    'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
                    'Cache-Control: no-cache',
                    'Pragma: no-cache',
                ],
            ]);

            $content = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $mime = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($content === false || $httpCode >= 400) {
                throw new RuntimeException($error !== '' ? ('远程图片下载失败：' . $error) : '远程图片下载失败');
            }

            return [
                'content' => $content,
                'mime' => trim(explode(';', $mime)[0]),
            ];
        }

        $context = stream_context_create([
            'http' => [
                'timeout' => self::REMOTE_TIMEOUT,
                'follow_location' => 1,
                'ignore_errors' => true,
                'user_agent' => self::REMOTE_USER_AGENT,
                'header' => implode("\r\n", [
                    'Accept: image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                    'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
                    'Cache-Control: no-cache',
                    'Pragma: no-cache',
                    'Connection: close',
                ]),
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $content = @file_get_contents($imageUrl, false, $context);
        if ($content === false) {
            throw new RuntimeException('远程图片下载失败');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return [
            'content' => $content,
            'mime' => (string)$finfo->buffer($content),
        ];
    }

    private function downloadRemoteImageWithFallback(string $imageUrl): array
    {
        $errors = [];

        try {
            return $this->downloadRemoteImage($imageUrl);
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }

        try {
            return $this->downloadRemoteImageByStream($imageUrl);
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }

        $message = implode('；', array_values(array_filter($errors)));
        if ($message === '') {
            $message = '远程图片下载失败，请稍后重试或改用本地上传';
        }

        throw new RuntimeException($message);
    }

    private function downloadRemoteImageByStream(string $imageUrl): array
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => self::REMOTE_TIMEOUT,
                'follow_location' => 1,
                'ignore_errors' => true,
                'user_agent' => self::REMOTE_USER_AGENT,
                'header' => implode("\r\n", [
                    'Accept: image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                    'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
                    'Cache-Control: no-cache',
                    'Pragma: no-cache',
                    'Connection: close',
                ]),
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $content = @file_get_contents($imageUrl, false, $context);
        if ($content === false) {
            $error = error_get_last();
            $message = isset($error['message']) ? trim((string)$error['message']) : '';
            if ($message !== '') {
                throw new RuntimeException('远程图片下载失败：' . $message);
            }

            throw new RuntimeException('远程图片下载失败');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return [
            'content' => $content,
            'mime' => (string)$finfo->buffer($content),
        ];
    }

    private function buildPayload(string $storedPath, string $originalName, int $size): array
    {
        $normalizedPath = str_replace('\\', '/', trim($storedPath, '/'));

        return [
            'path' => $normalizedPath,
            'url' => '/storage/' . $normalizedPath,
            'name' => $originalName,
            'size' => $size,
        ];
    }

    private function normalizeDirectory(string $directory): string
    {
        $directory = trim(str_replace('\\', '/', $directory), '/');
        if ($directory === '') {
            return 'products';
        }

        $directory = preg_replace('/[^a-zA-Z0-9_\\/-]/', '', $directory);
        if ($directory === null || $directory === '') {
            return 'products';
        }

        return trim($directory, '/');
    }
}
