<?php
declare(strict_types=1);

namespace app\index\controller;

use app\index\service\EnergyRechargeService;
use app\index\service\UserCenterService;
use app\index\validate\EnergyRechargeCreateValidate;
use app\index\validate\UserLoginValidate;
use app\index\validate\UserProfileValidate;
use app\index\validate\UserRegisterValidate;
use RuntimeException;
use think\facade\Filesystem;
use think\file\UploadedFile;

class User extends BaseController
{
    private const MAX_AVATAR_SIZE = 5242880;
    private const ALLOWED_AVATAR_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public function register(): \think\Response
    {
        $data = $this->request->post();
        $this->validate($data, UserRegisterValidate::class);

        try {
            $result = (new UserCenterService())->register($data, (string)$this->request->ip());
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage());
        }

        return $this->success($result, 'Registered successfully');
    }

    public function login(): \think\Response
    {
        $data = [
            'username' => trim((string)$this->request->post('username', '')),
            'password' => (string)$this->request->post('password', ''),
        ];
        $this->validate($data, UserLoginValidate::class);

        try {
            $result = (new UserCenterService())->login(
                $data['username'],
                $data['password'],
                (string)$this->request->ip()
            );
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage(), 401, 401);
        }

        return $this->success($result, 'Logged in successfully');
    }

    public function profile(): \think\Response
    {
        try {
            $data = (new UserCenterService())->getProfile((int)(($this->request->user ?? [])['id'] ?? 0));
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage(), 404, 404);
        }

        return $this->success($data);
    }

    public function updateProfile(): \think\Response
    {
        $data = $this->request->put();
        if (empty($data)) {
            $data = $this->request->post();
        }
        $this->validate($data, UserProfileValidate::class);

        try {
            $result = (new UserCenterService())->updateProfile(
                (int)(($this->request->user ?? [])['id'] ?? 0),
                $data
            );
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage(), 404, 404);
        }

        return $this->success($result, 'Profile updated successfully');
    }

    public function uploadAvatar(): \think\Response
    {
        $file = $this->request->file('file');

        try {
            if (!$file instanceof UploadedFile) {
                throw new RuntimeException('请选择头像图片');
            }

            $avatar = $this->saveAvatarFile($file);
            $profile = (new UserCenterService())->updateProfile(
                (int)(($this->request->user ?? [])['id'] ?? 0),
                ['avatar' => $avatar['url']]
            );
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage());
        }

        return $this->success([
            'avatar' => $avatar,
            'profile' => $profile,
        ], '头像上传成功');
    }

    private function saveAvatarFile(UploadedFile $file): array
    {
        if (!$file->isValid()) {
            throw new RuntimeException('上传文件无效');
        }

        $extension = strtolower($file->extension());
        if (!in_array($extension, self::ALLOWED_AVATAR_EXTENSIONS, true)) {
            $detectedExtension = $this->guessAvatarExtension($file);
            if ($detectedExtension !== '') {
                $extension = $detectedExtension;
            }
        }
        if (!in_array($extension, self::ALLOWED_AVATAR_EXTENSIONS, true)) {
            throw new RuntimeException('仅支持 jpg、jpeg、png、gif、webp 图片');
        }

        $size = (int)$file->getSize();
        if ($size <= 0 || $size > self::MAX_AVATAR_SIZE) {
            throw new RuntimeException('头像图片大小不能超过5MB');
        }

        $datePath = date('Ymd');
        $fileName = md5((string)$file->getOriginalName() . microtime(true) . random_int(1000, 9999)) . '.' . $extension;
        $storedPath = Filesystem::disk('public')->putFileAs('avatars/' . $datePath, $file, $fileName);
        if ($storedPath === false) {
            throw new RuntimeException('头像保存失败');
        }

        $normalizedPath = str_replace('\\', '/', trim((string)$storedPath, '/'));

        return [
            'path' => $normalizedPath,
            'url' => '/storage/' . $normalizedPath,
            'name' => $file->getOriginalName(),
            'size' => $size,
        ];
    }

    private function guessAvatarExtension(UploadedFile $file): string
    {
        $mime = '';
        $path = $file->getPathname();
        if (is_string($path) && $path !== '' && is_file($path)) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = (string)$finfo->file($path);
        }

        return match (strtolower($mime)) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => '',
        };
    }

    public function signIn(): \think\Response
    {
        try {
            $data = (new UserCenterService())->signIn((int)(($this->request->user ?? [])['id'] ?? 0));
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage());
        }

        return $this->success($data, 'Signed in successfully');
    }

    public function orders(): \think\Response
    {
        try {
            $data = (new UserCenterService())->getOrders(
                (int)(($this->request->user ?? [])['id'] ?? 0),
                (int)$this->request->get('page/d', 1),
                (int)$this->request->get('limit/d', 10),
                trim((string)$this->request->get('status', ''))
            );
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage(), 404, 404);
        }

        return $this->success($data);
    }

    public function energyLogs(): \think\Response
    {
        try {
            $data = (new UserCenterService())->getEnergyLogs(
                (int)(($this->request->user ?? [])['id'] ?? 0),
                (int)$this->request->get('page/d', 1),
                (int)$this->request->get('limit/d', 20)
            );
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage(), 404, 404);
        }

        return $this->success($data);
    }

    public function energySources(): \think\Response
    {
        try {
            $data = (new UserCenterService())->getEnergySources((int)(($this->request->user ?? [])['id'] ?? 0));
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage(), 404, 404);
        }

        return $this->success($data);
    }

    public function rechargePackages(): \think\Response
    {
        try {
            $data = (new EnergyRechargeService())->getPackages();
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage());
        }

        return $this->success(['list' => $data]);
    }

    public function createRechargeOrder(): \think\Response
    {
        $data = $this->request->post();
        $this->validate($data, EnergyRechargeCreateValidate::class);

        try {
            $result = (new EnergyRechargeService())->createOrder(
                (int)(($this->request->user ?? [])['id'] ?? 0),
                (int)($data['package_id'] ?? 0),
                trim((string)($data['pay_channel'] ?? '')),
                trim((string)($data['return_url'] ?? ''))
            );
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage());
        }

        return $this->success($result, 'Recharge order created');
    }

    public function rechargeOrderStatus(): \think\Response
    {
        try {
            $result = (new EnergyRechargeService())->getOrderStatus(
                (int)(($this->request->user ?? [])['id'] ?? 0),
                trim((string)$this->request->get('order_no', ''))
            );
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage());
        }

        return $this->success($result);
    }
}
