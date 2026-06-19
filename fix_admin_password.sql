# 把 admin_users 表里指定用户名的密码重置为指定明文。
# 在 Navicat 里新建查询，切换到 ceshi3 库，然后执行下面两行。
# 第一行把 PHP 算出来的 bcrypt 哈希塞进去，第二行做验证（应该返回 1）。

# 先执行这一行得到哈希：
SELECT PASSWORD('ceshi3', PASSWORD_DEFAULT);
# 拿到哈希（比如 $2y$10$abcd...xyz），复制它，替换下面 'PASTE_HASH_HERE'，再执行：
UPDATE admin_users SET password = 'PASTE_HASH_HERE', updated_at = NOW() WHERE username = 'ceshi3';
# 校验：把上一行拿到的哈希塞进 'PASTE_HASH_HERE'，如果返回 1 就说明这个哈希认 'ceshi3' 这个明文
SELECT password_verify('ceshi3', 'PASTE_HASH_HERE') AS ok;
