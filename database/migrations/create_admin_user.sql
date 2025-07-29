-- SQL script to create an admin user with known password 'admin123'
INSERT INTO users (username, full_name, password, role) VALUES (
  'admin',
  'Administrator',
  -- Password hash for 'admin123'
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'admin'
);
