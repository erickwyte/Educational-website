CREATE DATABASE edu_website;
USE edu_website;

-- Users Table

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    phone_number VARCHAR(15) UNIQUE NOT NULL,
    course VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('user', 'admin') DEFAULT 'user',
    subscription_end DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);



CREATE TABLE admin_list (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,  -- Added password column
    user_type ENUM('admin') NOT NULL DEFAULT 'admin'
);


INSERT INTO admin_list (email, user_type) VALUES ('angayiaerick@gmail.com', 'admin');


-- User Uploads Table (PDF Uploads)

CREATE TABLE user_pdfs_uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    uploaded_by VARCHAR(100) DEFAULT 'guest',        -- Now supports "guest"
    approved TINYINT(1) DEFAULT 0,
    subject VARCHAR(100) NOT NULL,
    original_name VARCHAR(255),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL, -- Supports IPv4 and IPv6
    attempt_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_attempt_time (ip_address, attempt_time)
);

CREATE TABLE testimonials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  message TEXT NOT NULL,
  name VARCHAR(255) NOT NULL,
  university VARCHAR(255) NOT NULL,
  status ENUM('pending', 'approved') DEFAULT 'pending'
);


CREATE TABLE blog_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(255) UNIQUE NOT NULL
);

CREATE TABLE blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    category_id INT NULL,  -- Allows NULL if category is deleted
    views INT DEFAULT 0,
    likes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  -- Track updates
    deleted_at TIMESTAMP NULL,  -- Soft delete
    FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE SET NULL
);


CREATE TABLE blog_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blog_id INT NOT NULL,
    username VARCHAR(50) NOT NULL,
    comment TEXT NOT NULL,
    date_posted TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (blog_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (username) REFERENCES users(username) ON DELETE CASCADE
);

CREATE TABLE blog_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    UNIQUE KEY unique_like (user_id, post_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE
);

CREATE TABLE blog_media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blog_id INT,
    media_url VARCHAR(255) NOT NULL,
    media_type ENUM('image', 'video') NOT NULL,
    FOREIGN KEY (blog_id) REFERENCES blog_posts(id) ON DELETE CASCADE
);



-- If you need to modify it, here's the correct structure:
CREATE TABLE IF NOT EXISTS discussion_topic (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    date_posted DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add index for better performance
CREATE INDEX idx_user_id ON discussion_topic(user_id);
CREATE INDEX idx_date_posted ON discussion_topic(date_posted);


CREATE TABLE universities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);



CREATE TABLE questions_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE questions_pdfs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    university_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_by VARCHAR(100) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES questions_categories(id) ON DELETE CASCADE,
    FOREIGN KEY (university_id) REFERENCES universities(id) ON DELETE CASCADE
);





CREATE TABLE notes_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE notes_pdfs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    category_id INT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES notes_categories(id) ON DELETE SET NULL
);









CREATE TABLE discussion_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    topic_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    date_posted TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (topic_id) REFERENCES discussion_topic(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE user_saved_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    question_id INT NOT NULL,
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions_pdfs(id) ON DELETE CASCADE
);



CREATE TABLE user_saved_pdfs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pdf_id INT NOT NULL,
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_pdf (user_id, pdf_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (pdf_id) REFERENCES notes_pdfs(id) ON DELETE CASCADE
);

/* This table handles user messages to admin and admin replies */
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,  
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    reply TEXT DEFAULT NULL,
    status ENUM('pending', 'replied') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status) 
);

CREATE TABLE donation_mpesa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(100) NOT NULL,  -- New column for email
    amount DECIMAL(10,2) NOT NULL,
    transaction_id VARCHAR(50) NOT NULL UNIQUE,
    status ENUM('Pending', 'Completed', 'Failed') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



CREATE TABLE stripe_donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    transaction_id VARCHAR(255) UNIQUE NOT NULL,
    status VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Email Subscriptions Table
CREATE TABLE email_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(255) NOT NULL
);


INSERT INTO courses (course_name) VALUES
('Accounting'),
('Actuarial Science'),
('Aerospace Engineering'),
('Agribusiness'),
('Agriculture'),
('Animal Science'),
('Anthropology'),
('Applied Mathematics'),
('Archaeology'),
('Architecture'),
('Artificial Intelligence'),
('Astronomy'),
('Automotive Engineering'),
('Banking and Finance'),
('Biochemistry'),
('Bioinformatics'),
('Biology'),
('Biomedical Engineering'),
('Biotechnology'),
('Botany'),
('Business Administration'),
('Chemical Engineering'),
('Chemistry'),
('Civil Engineering'),
('Communication Studies'),
('Community Development'),
('Computer Engineering'),
('Computer Science'),
('Construction Management'),
('Counseling Psychology'),
('Creative Writing'),
('Criminology'),
('Culinary Arts'),
('Cybersecurity'),
('Data Analytics'),
('Data Science'),
('Dentistry'),
('Development Studies'),
('Digital Marketing'),
('Disaster Management'),
('Early Childhood Education'),
('Ecology'),
('Economics'),
('Education'),
('Electrical Engineering'),
('Electronics Engineering'),
('Emergency and Disaster Management'),
('Energy Engineering'),
('Entrepreneurship'),
('Environmental Engineering'),
('Environmental Science'),
('Epidemiology'),
('Fashion and Design'),
('Film and Media Studies'),
('Finance'),
('Fine Arts'),
('Food Science and Technology'),
('Forensic Science'),
('Forestry'),
('Game Development'),
('Genetics'),
('Geography'),
('Geology'),
('Graphic Design'),
('Health Informatics'),
('History'),
('Hospitality Management'),
('Hotel and Restaurant Management'),
('Human Anatomy'),
('Human Nutrition and Dietetics'),
('Human Resource Management'),
('Industrial Chemistry'),
('Industrial Design'),
('Industrial Engineering'),
('Information Management Systems'),
('Information Technology'),
('Interior Design'),
('International Business'),
('International Relations'),
('Journalism'),
('Labor Relations'),
('Landscape Architecture'),
('Law'),
('Library and Information Science'),
('Linguistics'),
('Logistics and Supply Chain Management'),
('Manufacturing Engineering'),
('Marine Biology'),
('Marketing'),
('Materials Science and Engineering'),
('Mathematical Sciences'),
('Mathematics'),
('Mechanical Engineering'),
('Media and Communication'),
('Medical Laboratory Science'),
('Medicine'),
('Microbiology'),
('Mining Engineering'),
('Music'),
('Nanotechnology'),
('Network Engineering'),
('Neuroscience'),
('Nursing'),
('Nutrition Science'),
('Occupational Therapy'),
('Oceanography'),
('Operations Research'),
('Optometry'),
('Petroleum Engineering'),
('Pharmaceutical Sciences'),
('Pharmacy'),
('Philosophy'),
('Photography'),
('Physical Education'),
('Physics'),
('Physiotherapy'),
('Political Science'),
('Project Management'),
('Psychology'),
('Public Administration'),
('Public Health'),
('Quantity Surveying'),
('Real Estate Management'),
('Renewable Energy'),
('Robotics Engineering'),
('Social Work'),
('Sociology'),
('Software Engineering'),
('Special Education'),
('Speech and Language Therapy'),
('Sports Science'),
('Statistics'),
('Supply Chain Management'),
('Sustainable Development'),
('Taxation'),
('Teacher Education'),
('Theatre Arts'),
('Theology'),
('Tourism and Hospitality'),
('Town and Regional Planning'),
('Translation and Interpretation'),
('Transportation Engineering'),
('Urban and Regional Planning'),
('Veterinary Medicine'),
('Visual Arts'),
('Wildlife Management'),
('Zoology');













-- Add profile_photo column to users table
ALTER TABLE users 
ADD COLUMN profile_photo VARCHAR(255) DEFAULT NULL AFTER subscription_end;

-- Add reset_token and reset_expires columns for password reset functionality
ALTER TABLE users 
ADD COLUMN reset_token VARCHAR(100) DEFAULT NULL AFTER profile_photo,
ADD COLUMN reset_expires DATETIME DEFAULT NULL AFTER reset_token;

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_reset_token ON users(reset_token);
CREATE INDEX idx_users_subscription ON users(subscription_end);

-- Add created_at and updated_at to admin_list table for better tracking
ALTER TABLE admin_list
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add status column to user_pdfs_uploads for better content management
ALTER TABLE user_pdfs_uploads
ADD COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' AFTER approved;

-- Add user_id foreign key to user_pdfs_uploads for better user tracking
ALTER TABLE user_pdfs_uploads
ADD COLUMN user_id INT NULL AFTER uploaded_by,
ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;

-- Add additional columns to testimonials for better management
ALTER TABLE testimonials
ADD COLUMN user_id INT NULL AFTER university,
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;

-- Create user_activity table for tracking user actions
CREATE TABLE user_activity (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_type ENUM('login', 'logout', 'upload', 'download', 'view', 'comment', 'like') NOT NULL,
    activity_details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create notifications table for user notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create user_preferences table for storing user settings
CREATE TABLE user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    email_notifications TINYINT(1) DEFAULT 1,
    news_letter TINYINT(1) DEFAULT 1,
    theme ENUM('light', 'dark') DEFAULT 'light',
    language VARCHAR(10) DEFAULT 'en',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default preferences for existing users
INSERT INTO user_preferences (user_id)
SELECT id FROM users
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- Create user_sessions table for tracking active sessions
CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL UNIQUE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add indexes for better performance
CREATE INDEX idx_user_activity_user ON user_activity(user_id);
CREATE INDEX idx_user_activity_type ON user_activity(activity_type);
CREATE INDEX idx_notifications_user ON notifications(user_id);
CREATE INDEX idx_notifications_read ON notifications(is_read);
CREATE INDEX idx_user_sessions_token ON user_sessions(session_token);
CREATE INDEX idx_user_sessions_expires ON user_sessions(expires_at);

-- Add trigger to create user preferences when a new user is registered
DELIMITER //
CREATE TRIGGER after_user_insert
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO user_preferences (user_id) VALUES (NEW.id);
END;
//
DELIMITER ;

-- Create audit_log table for important system events
CREATE TABLE audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(100) NOT NULL,
    record_id INT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Add comments to tables and columns for better documentation
ALTER TABLE users 
COMMENT 'Stores user account information including authentication details';

ALTER TABLE users 
MODIFY COLUMN profile_photo VARCHAR(255) 
COMMENT 'Path to user profile photo stored in uploads/profile_photos/';

ALTER TABLE users 
MODIFY COLUMN reset_token VARCHAR(100) 
COMMENT 'Token for password reset functionality';

ALTER TABLE users 
MODIFY COLUMN reset_expires DATETIME 
COMMENT 'Expiration datetime for password reset token';

-- Create view for active users
CREATE VIEW active_users AS
SELECT 
    u.id,
    u.username,
    u.email,
    u.user_type,
    u.subscription_end,
    CASE 
        WHEN u.subscription_end >= CURDATE() THEN 'active'
        ELSE 'expired'
    END as subscription_status
FROM users u
WHERE u.subscription_end IS NOT NULL;

-- Create view for user statistics
CREATE VIEW user_stats AS
SELECT 
    u.id,
    u.username,
    COUNT(DISTINCT usq.question_id) as saved_questions_count,
    COUNT(DISTINCT usp.pdf_id) as saved_notes_count,
    COUNT(DISTINCT dt.id) as discussion_topics_count,
    COUNT(DISTINCT dc.id) as discussion_comments_count,
    COALESCE(MAX(ua.created_at), u.created_at) as last_activity
FROM users u
LEFT JOIN user_saved_questions usq ON u.id = usq.user_id
LEFT JOIN user_saved_pdfs usp ON u.id = usp.user_id
LEFT JOIN discussion_topic dt ON u.id = dt.user_id
LEFT JOIN discussion_comments dc ON u.id = dc.user_id
LEFT JOIN user_activity ua ON u.id = ua.user_id
GROUP BY u.id, u.username, u.created_at;

ALTER TABLE user_activity ADD INDEX idx_user_id (user_id);
ALTER TABLE user_activity ADD INDEX idx_activity_type (activity_type);
ALTER TABLE user_activity ADD INDEX idx_created_at (created_at);


CREATE TABLE paypal_donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    transaction_id VARCHAR(255) NOT NULL,
    status VARCHAR(50) NOT NULL,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);