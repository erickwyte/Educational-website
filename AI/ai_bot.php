<?php
// ai_bot.php
require 'config.php';
require 'ai_functions.php';

class AIDiscussionBot {
    private $conn;
    private $bot_user_id;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->bot_user_id = $this->get_bot_user_id();
    }
    
    private function get_bot_user_id() {
        // Get or create AI bot user
        $sql = "SELECT id FROM users WHERE username = 'AI_Assistant' AND email = 'ai@dasaplus.com'";
        $result = $this->conn->query($sql);
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc()['id'];
        }
        
        // Create AI bot user
        $sql = "INSERT INTO users (username, email, course, role, profile_photo, created_at) 
                VALUES ('AI_Assistant', 'ai@dasaplus.com', 'General', 'ai_bot', '/assets/ai-bot.png', NOW())";
        $this->conn->query($sql);
        return $this->conn->insert_id;
    }
    
    public function engage_with_new_topic($topic_id) {
        // Get topic details
        $sql = "SELECT dt.title, dt.content, u.course 
                FROM discussion_topic dt 
                JOIN users u ON dt.user_id = u.id 
                WHERE dt.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $topic_id);
        $stmt->execute();
        $topic = $stmt->get_result()->fetch_assoc();
        
        if (!$topic) return false;
        
        // Get recent comments for context
        $comments = $this->get_topic_comments($topic_id);
        
        // Generate AI response
        $ai_response = ai_generate_engagement($topic['title'], $topic['content'], $comments);
        
        // Insert as comment
        $sql = "INSERT INTO discussion_comments (topic_id, user_id, comment, date_posted, is_ai_generated) 
                VALUES (?, ?, ?, NOW(), 1)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iis', $topic_id, $this->bot_user_id, $ai_response);
        
        if ($stmt->execute()) {
            $this->log_engagement($topic_id, $stmt->insert_id, 'question');
            return $stmt->insert_id;
        }
        
        return false;
    }
    
    private function get_topic_comments($topic_id, $limit = 3) {
        $sql = "SELECT comment FROM discussion_comments 
                WHERE topic_id = ? AND is_ai_generated = 0 
                ORDER BY date_posted DESC LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $topic_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $comments = [];
        while ($row = $result->fetch_assoc()) {
            $comments[] = substr($row['comment'], 0, 100);
        }
        return $comments;
    }
    
    private function log_engagement($topic_id, $comment_id, $engagement_type) {
        $sql = "INSERT INTO ai_engagement (topic_id, comment_id, engagement_type, created_at) 
                VALUES (?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iis', $topic_id, $comment_id, $engagement_type);
        $stmt->execute();
    }
    
    public function moderate_content($content, $user_id, $content_type, $content_id) {
        $result = ai_moderate_content($content, $content_type);
        
        if (isset($result['error'])) {
            return ['approved' => true, 'error' => $result['error']]; // Fail open
        }
        
        $action = 'approved';
        $approved = true;
        
        if (($result['toxicity_score'] ?? 0) > 7 || !($result['is_appropriate'] ?? true)) {
            $action = 'flagged';
            $approved = false;
        }
        
        // Log moderation action
        log_ai_moderation($user_id, $content_type, $content_id, $result, $action);
        
        return [
            'approved' => $approved,
            'toxicity_score' => $result['toxicity_score'] ?? 0,
            'flags' => $result['flags'] ?? [],
            'suggestion' => $result['suggested_improvement'] ?? ''
        ];
    }
}
?>git add .
git commit -m "Latest local changes"
git push