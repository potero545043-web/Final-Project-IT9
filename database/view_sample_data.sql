USE `it9_project-db`;

-- Run this only if your users table is empty.
-- It creates one sample user so the report records can connect to a valid user_id.
INSERT INTO `users` (`name`, `student_id`, `email`, `phone`, `role`, `password`, `created_at`, `updated_at`)
SELECT
    'Sample Student',
    'SAMPLE-001',
    'sample.student@lostandfound.test',
    '09951234567',
    'student',
    '$2y$12$0o8kZpJXigvtYdq1X/Q9weY6rW3aMTde9fdBdIl0WfOKc5y6Y5VlS',
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `users` WHERE `email` = 'sample.student@lostandfound.test'
);

INSERT INTO `lost_reports` (`user_id`, `description`, `location`, `date_reported`, `status`, `created_at`, `updated_at`)
SELECT
    `id`,
    'Lost black wallet with school ID and library card inside.',
    'Main Library',
    NOW() - INTERVAL 3 DAY,
    'open',
    NOW(),
    NOW()
FROM `users`
ORDER BY `id`
LIMIT 1;

INSERT INTO `lost_reports` (`user_id`, `description`, `location`, `date_reported`, `status`, `created_at`, `updated_at`)
SELECT
    `id`,
    'Lost keychain with two silver keys near the student lounge.',
    'Student Lounge',
    NOW() - INTERVAL 1 DAY,
    'open',
    NOW(),
    NOW()
FROM `users`
ORDER BY `id`
LIMIT 1;

INSERT INTO `found_reports` (`user_id`, `item_id`, `description`, `location`, `date_reported`, `status`, `created_at`, `updated_at`)
SELECT
    `id`,
    NULL,
    'Found blue umbrella left beside the cafeteria entrance.',
    'Cafeteria Entrance',
    NOW() - INTERVAL 2 DAY,
    'open',
    NOW(),
    NOW()
FROM `users`
ORDER BY `id`
LIMIT 1;

INSERT INTO `found_reports` (`user_id`, `item_id`, `description`, `location`, `date_reported`, `status`, `created_at`, `updated_at`)
SELECT
    `id`,
    NULL,
    'Found phone charger in Computer Laboratory 3.',
    'Computer Laboratory 3',
    NOW(),
    'open',
    NOW(),
    NOW()
FROM `users`
ORDER BY `id`
LIMIT 1;

SELECT * FROM `vw_lost_found_reports`;
