<?php
declare(strict_types=1);

namespace app\admin;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\facade\Log;
use think\Response;
use Throwable;

class ExceptionHandle extends Handle
{
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];

    public function report(Throwable $exception): void
    {
        parent::report($exception);
    }

    public function render($request, Throwable $e): Response
    {
        if ($e instanceof ValidateException) {
            return json([
                'code' => 422,
                'msg' => $e->getError(),
                'data' => [],
            ], 422);
        }

        if ($e instanceof HttpException) {
            return json([
                'code' => $e->getStatusCode(),
                'msg' => $e->getMessage(),
                'data' => [],
            ], $e->getStatusCode());
        }

        if ((bool) env('APP_DEBUG', false)) {
            return parent::render($request, $e);
        }

        $errorId = $this->generateErrorId();
        $this->logException($request, $e, $errorId);

        if ($this->isDatabaseException($e)) {
            $dbError = $this->classifyDatabaseException($e);

            return json([
                'code' => $dbError['code'],
                'msg'  => $dbError['msg'],
                'data' => [
                    'error_id' => $errorId,
                    'category' => $dbError['category'],
                ],
            ], 500);
        }

        return json([
            'code' => 500,
            'msg' => '服务器开小差了，请稍后重试',
            'data' => [
                'error_id' => $errorId,
            ],
        ], 500);
    }

    private function generateErrorId(): string
    {
        try {
            return date('YmdHis') . '-' . bin2hex(random_bytes(4));
        } catch (Throwable) {
            return uniqid('err-', true);
        }
    }

    private function logException($request, Throwable $e, string $errorId): void
    {
        Log::error(sprintf(
            '[%s] %s: %s in %s:%d | %s %s | ip=%s',
            $errorId,
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            strtoupper((string) $request->method()),
            (string) $request->url(),
            (string) $request->ip()
        ));
    }

    private function isDatabaseException(Throwable $e): bool
    {
        $className = get_class($e);
        $message = $e->getMessage();

        return str_contains($className, 'think\\db\\exception')
            || str_contains($message, 'SQLSTATE[')
            || str_contains($message, 'Base table or view not found')
            || str_contains($message, 'Integrity constraint violation');
    }

    private function classifyDatabaseException(Throwable $e): array
    {
        $message = $e->getMessage();

        if (str_contains($message, 'SQLSTATE[42S02]') || str_contains($message, 'Base table or view not found')) {
            return [
                'code' => 5001,
                'category' => 'db_table_missing',
                'msg' => '数据库表缺失，请先执行数据库初始化脚本',
            ];
        }

        if (str_contains($message, 'SQLSTATE[23000]') || str_contains($message, 'Integrity constraint violation')) {
            return [
                'code' => 5002,
                'category' => 'db_constraint_violation',
                'msg' => '数据约束校验失败，请检查提交数据是否重复或关联不存在',
            ];
        }

        if (str_contains($message, 'SQLSTATE[HY000]') || str_contains($message, 'Connection refused') || str_contains($message, 'getaddrinfo')) {
            return [
                'code' => 5003,
                'category' => 'db_connection_error',
                'msg' => '数据库连接异常，请检查数据库连接配置',
            ];
        }

        return [
            'code' => 5000,
            'category' => 'db_error',
            'msg' => '数据库异常，请联系管理员并提供 error_id',
        ];
    }
}
