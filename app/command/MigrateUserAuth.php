<?php
declare(strict_types=1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Db;

class MigrateUserAuth extends Command
{
    protected function configure()
    {
        $this->setName('migrate:user-auth')
            ->setDescription('迁移用户认证相关字段');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln('开始迁移用户表字段...');
        $output->writeln('');

        try {
            // 1. 添加密码字段
            $output->writeln('[1/5] 添加密码字段');
            $this->addColumnIfNotExists('users', 'password', "varchar(255) NOT NULL DEFAULT '' COMMENT '密码hash'", 'username', $output);

            // 2. 添加邀请码相关字段
            $output->writeln('');
            $output->writeln('[2/5] 添加邀请码相关字段');
            $this->addColumnIfNotExists('users', 'invite_code', "varchar(20) NOT NULL DEFAULT '' COMMENT '邀请码'", 'email', $output);
            $this->addColumnIfNotExists('users', 'inviter_id', "bigint unsigned NOT NULL DEFAULT 0 COMMENT '邀请人ID'", 'invite_code', $output);
            $this->addColumnIfNotExists('users', 'invite_count', "int unsigned NOT NULL DEFAULT 0 COMMENT '邀请人数'", 'inviter_id', $output);

            // 3. 添加签到日期字段
            $output->writeln('');
            $output->writeln('[3/5] 添加签到日期字段');
            $this->addColumnIfNotExists('users', 'last_signin_date', "date DEFAULT NULL COMMENT '最后签到日期'", 'last_login_at', $output);

            // 4. 添加微信相关字段
            $output->writeln('');
            $output->writeln('[4/5] 添加微信相关字段');
            $this->addColumnIfNotExists('users', 'wx_openid', "varchar(100) NOT NULL DEFAULT '' COMMENT '微信openid'", 'avatar', $output);
            $this->addColumnIfNotExists('users', 'wx_unionid', "varchar(100) NOT NULL DEFAULT '' COMMENT '微信unionid'", 'wx_openid', $output);
            $this->addColumnIfNotExists('users', 'wx_nickname', "varchar(100) NOT NULL DEFAULT '' COMMENT '微信昵称'", 'wx_unionid', $output);
            $this->addColumnIfNotExists('users', 'wx_avatar', "varchar(255) NOT NULL DEFAULT '' COMMENT '微信头像'", 'wx_nickname', $output);
            $this->addColumnIfNotExists('users', 'wx_session_key', "varchar(255) NOT NULL DEFAULT '' COMMENT '微信session_key'", 'wx_avatar', $output);

            // 5. 添加索引
            $output->writeln('');
            $output->writeln('[5/5] 添加索引');
            $this->addIndexIfNotExists('users', 'idx_wx_openid', 'wx_openid', $output);
            $this->addIndexIfNotExists('users', 'idx_wx_unionid', 'wx_unionid', $output);
            $this->addIndexIfNotExists('users', 'idx_email', 'email', $output);
            $this->addIndexIfNotExists('users', 'idx_invite_code', 'invite_code', $output);
            $this->addIndexIfNotExists('users', 'idx_inviter_id', 'inviter_id', $output);

            $output->writeln('');
            $output->info('✓ 迁移完成！');
            
            // 显示用户表结构
            $output->writeln('');
            $output->writeln('当前用户表结构:');
            $output->writeln(str_repeat('-', 80));
            
            $columns = Db::query('SHOW COLUMNS FROM `users`');
            foreach ($columns as $column) {
                $output->writeln(sprintf(
                    '  %-25s %-30s %-10s',
                    $column['Field'],
                    $column['Type'],
                    $column['Null']
                ));
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $output->error('迁移失败: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * 添加字段（如果不存在）
     */
    private function addColumnIfNotExists(string $table, string $column, string $definition, string $after, Output $output): bool
    {
        // 检查字段是否存在
        $columns = Db::query("SHOW COLUMNS FROM `{$table}` LIKE ?", [$column]);
        
        if (!empty($columns)) {
            $output->writeln("  - 字段 {$column} 已存在，跳过");
            return false;
        }

        // 添加字段
        $sql = "ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition} AFTER `{$after}`";
        Db::execute($sql);
        
        $output->writeln("  ✓ 添加字段 {$column}");
        return true;
    }

    /**
     * 添加索引（如果不存在）
     */
    private function addIndexIfNotExists(string $table, string $indexName, string $column, Output $output): bool
    {
        // 检查索引是否存在
        $indexes = Db::query("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        
        if (!empty($indexes)) {
            $output->writeln("  - 索引 {$indexName} 已存在，跳过");
            return false;
        }

        // 添加索引
        Db::execute("ALTER TABLE `{$table}` ADD INDEX `{$indexName}` (`{$column}`)");
        
        $output->writeln("  ✓ 添加索引 {$indexName}");
        return true;
    }
}
