<?php
declare(strict_types=1);

namespace app;

use think\App;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Validate;

abstract class BaseController
{
    protected App $app;
    protected \think\Request $request;
    protected bool $batchValidate = false;
    protected array $middleware = [];

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $app->request;
        $this->initialize();
    }

    protected function initialize(): void
    {
    }

    /**
     * @throws ValidateException
     */
    protected function validate(array $data, string|array $validate, array $message = [], bool $batch = false): bool
    {
        if (is_array($validate)) {
            $validator = new Validate();
            $validator->rule($validate);
        } else {
            $scene = null;
            if (str_contains($validate, '.')) {
                [$validate, $scene] = explode('.', $validate, 2);
            }

            $class = str_contains($validate, '\\')
                ? $validate
                : $this->app->parseClass('validate', $validate);
            $validator = new $class();

            if (!empty($scene)) {
                $validator->scene($scene);
            }
        }

        $validator->message($message);

        if ($batch || $this->batchValidate) {
            $validator->batch(true);
        }

        try {
            return $validator->failException(true)->check($data);
        } catch (ValidateException $exception) {
            throw new HttpResponseException(json([
                'code' => 422,
                'msg' => $exception->getError(),
                'data' => [],
            ], 422));
        }
    }
}
