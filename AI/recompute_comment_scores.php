<?php
// recompute_comment_scores.php
require 'config.php';
require 'ai_functions.php';

class ScoreRecomputation {
    private $conn;
    private $batch_size = 100;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->conn->autocommit(false);
    }
    
    public function recomputeAllScores($days_back = 30) {
        $start_time = time();
        $processed = 0;
        $last_id = 0;
        
        echo "Starting score recomputation...\n";
        
        do {
            // Get batch of recent comments without scores
            $sql = "SELECT dc.id, dc.comment, dc.user_id, dc.topic_id 
                    FROM discussion_comments dc 
                    LEFT JOIN comment_scores cs ON dc.id = cs.comment_id 
                    WHERE dc.id > ? AND dc.date_posted >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    AND cs.comment_id IS NULL AND dc.is_ai_generated = 0
                    ORDER BY dc.id ASC 
                    LIMIT ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('iii', $last_id, $days_back, $this->batch_size);
            $stmt->execute();
            $comments = $stmt->get_result();
            
            $batch_processed = 0;
            
            while ($comment = $comments->fetch_assoc()) {
                $this->processComment($comment);
                $last_id = $comment['id'];
                $batch_processed++;
                $processed++;
                
                // Rate limiting - don't overwhelm the AI API
                if ($batch_processed % 10 === 0) {
                    sleep(1);
                }
            }
            
            $this->conn->commit();
            echo "Processed batch: $batch_processed comments. Total: $processed\n";
            
        } while ($batch_processed > 0);
        
        $duration = time() - $start_time;
        echo "Completed! Processed $processed comments in $duration seconds.\n";
    }
    
    private function processComment($comment) {
        $moderation_result = ai_moderate_content($comment['comment'], 'comment');
        
        if (isset($moderation_result['error'])) {
            // If AI fails, use default scores
            $this->insertDefaultScores($comment['id']);
            return;
        }
        
        $helpfulness = $this->calculateHelpfulnessScore($moderation_result);
        $engagement = $this->calculateEngagementScore($moderation_result);
        
        $this->saveCommentScores($comment['id'], $helpfulness, $engagement, $moderation_result);
    }
    
    private function calculateHelpfulnessScore($moderation_result) {
        $base_score = 50;
        
        // Positive factors
        if (empty($moderation_result['flags'])) $base_score += 20;
        if (($moderation_result['toxicity_score'] ?? 10) < 3) $base_score += 15;
        
        // Negative factors  
        if (in_array('spam', $moderation_result['flags'] ?? [])) $base_score -= 40;
        if (in_array('harassment', $moderation_result['flags'] ?? [])) $base_score -= 30;
        
        return max(0, min(100, $base_score));
    }
    
    private function calculateEngagementScore($moderation_result) {
        $score = 60;
        $toxicity = $moderation_result['toxicity_score'] ?? 5;
        
        // High toxicity reduces engagement potential
        if ($toxicity > 7) $score -= 30;
        if ($toxicity < 3) $score += 20;
        
        return max(0, min(100, $score));
    }
    
    private function saveCommentScores($comment_id, $helpfulness, $engagement, $moderation_result) {
        $sql = "INSERT INTO comment_scores (comment_id, helpfulness_score, engagement_score, toxicity_score, flags, last_updated) 
                VALUES (?, ?, ?, ?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE 
                helpfulness_score = ?, engagement_score = ?, toxicity_score = ?, flags = ?, last_updated = NOW()";
        
        $stmt = $this->conn->prepare($sql);
        $flags_json = json_encode($moderation_result['flags'] ?? []);
        $toxicity = $moderation_result['toxicity_score'] ?? 0;
        
        $stmt->bind_param('iiidsiids', 
            $comment_id, $helpfulness, $engagement, $toxicity, $flags_json,
            $helpfulness, $engagement, $toxicity, $flags_json
        );
        
        $stmt->execute();
    }
    
    private function insertDefaultScores($comment_id) {
        $sql = "INSERT IGNORE INTO comment_scores (comment_id, helpfulness_score, engagement_score, toxicity_score, last_updated) 
                VALUES (?, 50, 50, 0, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $comment_id);
        $stmt->execute();
    }
}

// CLI Execution
if (php_sapi_name() === 'cli') {
    $recomputer = new ScoreRecomputation($conn);
    $days = $argv[1] ?? 30;
    $recomputer->recomputeAllScores($days);
} else {
    http_response_code(403);
    die('Access denied');
}
?>