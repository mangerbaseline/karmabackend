-- Onboarding Script for Realistic Demo Database
-- Run this on your Central Database (defaultdb)

-- 1. Create the Tenant
INSERT INTO tenants (name, slug, db_name, is_active, created_at, updated_at)
VALUES ('Krema Demo Salon', 'krema-demo', 'krema', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE db_name = 'krema';

-- 2. Create the Domain
-- Replace 'karmabackend-6c6i.onrender.com' with your actual domain or local host
INSERT INTO tenant_domains (tenant_id, domain, is_active, created_at, updated_at)
SELECT id, 'krema.local', 1, NOW(), NOW() FROM tenants WHERE slug = 'krema-demo'
ON DUPLICATE KEY UPDATE is_active = 1;

-- 3. Associate Users (Optional if using per-tenant users)
-- The provided SQL has its own 'users' table. 
-- To login via the central AuthController, you might need a central user.
INSERT INTO users (name, first_name, last_name, email, password, role, is_active, created_at, updated_at)
VALUES ('Admin User', 'Admin', 'Krema', 'admin@krema.ba', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADMIN', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE role = 'ADMIN';
