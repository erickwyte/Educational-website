<?php
// badge_helper.php
require 'config.php';

class BadgeHelper {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function checkAndAwardBadges($user_id) {
        $badges_awarded = [];
        
        // Check each badge type
        $badges_awarded[] = $this->checkDiscussionStarterBadge($user_id);
        $badges_awarded[] = $this->checkHelpfulContributorBadge($user_id);
        $badges_awarded[] = $this->checkEngagementBadge($user_id);
        $badges_awarded[] = $this->checkQualityPosterBadge($user_id);
        
        return array_filter($badges_awarded);
    }
    
    private function checkDiscussionStarterBadge($user_id) {
        $sql = "SELECT COUNT(*) as topic_count 
                FROM discussion_topic 
                WHERE user_id = ? AND date_posted >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        $badge_levels = [
            1 => 'discussion_starter_bronze',
            5 => 'discussion_starter_silver', 
            10 => 'discussion_starter_gold'
        ];
        
        foreach ($badge_levels as $threshold => $badge_name) {
            if ($result['topic_count'] >= $threshold) {
                if ($this->awardBadge($user_id, $badge_name)) {
                    return $badge_name;
                }
            }
        }
        
        return null;
    }
    
    private function checkHelpfulContributorBadge($user_id) {
        $sql = "SELECT AVG(cs.helpfulness_score) as avg_score, COUNT(cs.comment_id) as rated_comments
                FROM comment_scores cs
                JOIN discussion_comments dc ON cs.comment_id = dc.id
                WHERE dc.user_id = ? AND dc.date_posted >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['rated_comments'] >= 5 && $result['avg_score'] >= 80) {
            return $this->awardBadge($user_id, 'helpful_contributor') ? 'helpful_contributor' : null;
        }
        
        return null;
    }
    
    private function checkEngagementBadge($user_id) {
        $sql = "SELECT COUNT(DISTINCT topic_id) as active_topics
                FROM discussion_comments 
                WHERE user_id = ? AND date_posted >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['active_topics'] >= 5) {
            return $this->awardBadge($user_id, 'active_engager') ? 'active_engager' : null;
        }
        
        return null;
    }
    
    private function checkQualityPosterBadge($user_id) {
        $sql = "SELECT COUNT(*) as high_quality_posts
                FROM discussion_comments dc
                JOIN comment_scores cs ON dc.id = cs.comment_id
                WHERE dc.user_id = ? AND cs.helpfulness_score >= 85 
                AND dc.date_posted >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        $badge_levels = [
            3 => 'quality_poster_bronze',
            10 => 'quality_poster_silver',
            25 => 'quality_poster_gold'
        ];
        
        foreach ($badge_levels as $threshold => $badge_name) {
            if ($result['high_quality_posts'] >= $threshold) {
                if ($this->awardBadge($user_id, $badge_name)) {
                    return $badge_name;
                }
            }
        }
        
        return null;
    }
    
    private function awardBadge($user_id, $badge_name) {
        // Check if already awarded
        $sql = "SELECT id FROM user_badges WHERE user_id = ? AND badge_name = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('is', $user_id, $badge_name);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            return false; // Already has this badge
        }
        
        // Award badge
        $sql = "INSERT INTO user_badges (user_id, badge_name, awarded_at) VALUES (?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('is', $user_id, $badge_name);
        
        if ($stmt->execute()) {
            $this->logBadgeAward($user_id, $badge_name);
            return true;
        }
        
        return false;
    }
    
    private function logBadgeAward($user_id, $badge_name) {
        $sql = "INSERT INTO badge_award_logs (user_id, badge_name, awarded_at) VALUES (?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('is', $user_id, $badge_name);
        $stmt->execute();
    }
    
    // Cron job method to run for all active users
    public function runBadgeCron() {
        $sql = "SELECT id FROM users WHERE last_active >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $result = $this->conn->query($sql);
        
        $total_awarded = 0;
        while ($user = $result->fetch_assoc()) {
            $awarded = $this->checkAndAwardBadges($user['id']);
            $total_awarded += count($awarded);
        }
        
        return $total_awarded;
    }
}

// Usage example for cron job
if (php_sapi_name() === 'cli') {
    $badgeHelper = new BadgeHelper($conn);
    $awarded = $badgeHelper->runBadgeCron();
    echo "Awarded $awarded badges in this run.\n";
}
?>