#!/usr/bin/env python3
"""
Setup script to create chat_history table in the database
Run this ONCE to initialize the table
"""

import mysql.connector
import sys

DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'primehrismagdalena',
    'auth_plugin': 'mysql_native_password'
}

def create_chat_history_table():
    """Create chat_history table in the database"""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        
        # Create table
        create_table_sql = """
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
        """
        
        cursor.execute(create_table_sql)
        
        # Create indexes
        index_sqls = [
            "ALTER TABLE chat_history ADD INDEX idx_user_session (user_id, session_id);",
            "ALTER TABLE chat_history ADD INDEX idx_question_type (question_type);"
        ]
        
        for sql in index_sqls:
            try:
                cursor.execute(sql)
            except mysql.connector.Error as err:
                if err.errno == 1061:  # Index already exists
                    print(f"   ℹ️  Index already exists")
                else:
                    print(f"   ⚠️  Index creation warning: {err}")
        
        conn.commit()
        
        print("✅ Chat history table created successfully!")
        print("   - Table: chat_history")
        print("   - User tracking: YES (optional, can be anonymous)")
        print("   - Session tracking: YES")
        print("   - Fields: question, response, question_type, follow_up_questions, codebase_files_used")
        
        cursor.close()
        conn.close()
        return True
        
    except mysql.connector.Error as err:
        print(f"❌ Database error: {err}")
        return False
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

if __name__ == '__main__':
    print("Setting up chat history table...")
    print("Database: primehrismagdalena")
    print("-" * 50)
    
    if create_chat_history_table():
        sys.exit(0)
    else:
        sys.exit(1)
