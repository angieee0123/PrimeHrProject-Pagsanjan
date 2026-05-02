-- Chat History Table
CREATE TABLE IF NOT EXISTS `chat_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `question` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `response` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `question_type` enum('database','system','greeting','error') COLLATE utf8mb4_unicode_ci DEFAULT 'system',
  `follow_up_questions` json DEFAULT NULL,
  `codebase_files_used` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `chat_history_user_id_foreign` (`user_id`),
  KEY `chat_history_session_id_index` (`session_id`),
  KEY `chat_history_created_at_index` (`created_at`),
  CONSTRAINT `chat_history_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create index for faster queries on conversation search
CREATE INDEX idx_user_session ON chat_history(user_id, session_id);
CREATE INDEX idx_question_type ON chat_history(question_type);
