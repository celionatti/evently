-- Settings table for application configuration
CREATE TABLE `settings` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `type` enum('string','integer','boolean','json','text','email','url') NOT NULL DEFAULT 'string',
  `category` varchar(50) NOT NULL DEFAULT 'general',
  `description` varchar(255) DEFAULT NULL,
  `is_editable` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_key` (`key`),
  INDEX `idx_category` (`category`),
  INDEX `idx_is_editable` (`is_editable`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default settings
INSERT INTO `settings` (`key`, `value`, `type`, `category`, `description`, `is_editable`) VALUES
-- Application Settings
('app_name', 'Eventlyy', 'string', 'application', 'Application name displayed throughout the site', 1),
('app_tagline', 'Your Premier Event Management Platform', 'string', 'application', 'Application tagline or subtitle', 1),
('app_description', 'Eventlyy is a comprehensive event management platform that helps you create, manage, and promote events effortlessly.', 'text', 'application', 'Application description for SEO and marketing', 1),
('app_url', 'https://eventlyy.com', 'url', 'application', 'Primary application URL', 1),
('app_logo', '/dist/img/logo.png', 'string', 'application', 'Path to application logo', 1),
('app_favicon', '/dist/img/favicon.ico', 'string', 'application', 'Path to application favicon', 1),

-- Contact Information
('contact_email', 'info@eventlyy.com', 'email', 'contact', 'Primary contact email address', 1),
('support_email', 'support@eventlyy.com', 'email', 'contact', 'Support email address', 1),
('contact_phone', '+234 800 123 4567', 'string', 'contact', 'Primary contact phone number', 1),
('contact_address', '123 Business District, Victoria Island, Lagos, Nigeria', 'text', 'contact', 'Physical business address', 1),

-- Social Media
('facebook_url', '', 'url', 'social', 'Facebook page URL', 1),
('twitter_url', '', 'url', 'social', 'Twitter profile URL', 1),
('instagram_url', '', 'url', 'social', 'Instagram profile URL', 1),
('linkedin_url', '', 'url', 'social', 'LinkedIn profile URL', 1),
('youtube_url', '', 'url', 'social', 'Youtube profile URL', 1),


-- Payment Settings
('paystack_public_key', '', 'string', 'payment', 'Paystack public key', 1),
('paystack_secret_key', '', 'string', 'payment', 'Paystack secret key (encrypted)', 1),
('payment_currency', 'NGN', 'string', 'payment', 'Default payment currency', 1),
('enable_payments', '1', 'boolean', 'payment', 'Enable payment processing', 1),

-- System Settings
('maintenance_mode', '0', 'boolean', 'system', 'Put application in maintenance mode', 1),
('allow_registration', '1', 'boolean', 'system', 'Allow new user registrations', 1),
('session_timeout', '120', 'integer', 'system', 'Session timeout in minutes', 1),
('enable_event_reviews', '1', 'boolean', 'system', 'Allow users to review events', 1),
('auto_approve_events', '0', 'boolean', 'system', 'Automatically approve new events', 1),

-- SEO Settings
('meta_title', 'Eventlyy - Premier Event Management Platform', 'string', 'seo', 'Default page title for SEO', 1),
('meta_description', 'Create, manage, and promote events with Eventlyy. The ultimate event management platform for organizers and attendees.', 'text', 'seo', 'Default meta description', 1),
('meta_keywords', 'events, event management, tickets, event planning, event promotion', 'text', 'seo', 'Default meta keywords', 1),

-- Terms and Privacy
('terms_of_service', '', 'text', 'legal', 'Terms of service content', 1),
('privacy_policy', '', 'text', 'legal', 'Privacy policy content', 1),
('cookie_policy', '', 'text', 'legal', 'Cookie policy content', 1),

-- Cache Settings
('cache_enabled', '1', 'boolean', 'cache', 'Enable application caching', 1),
('cache_duration', '3600', 'integer', 'cache', 'Cache duration in seconds', 1);