UPDATE `admin_users`
SET `password` = '$2y$10$IQR7yvTx/Ti0Z.EAyMWyR.dWXddxl769OawObg/q8bWkLzgMAr5l6',
    `updated_at` = NOW()
WHERE `username` = 'admin';

