<?php
// ai_functions.php
require 'config.php';

function ai_moderate_content($content, $content_type = 'post') {
    $api_key = getenv('OPENAI_API_KEY');
    if (!$api_key) {
        error_log("OpenAI API key not configured");
        return ['error' => 'AI moderation not configured'];
    }
    
    $prompt = "Analyze this forum $content_type and return ONLY valid JSON with:
    - toxicity_score (0-10)
    - is_appropriate (true/false) 
    - flags (array of strings like 'harassment', 'spam', 'hate_speech', 'off_topic')
    - suggested_improvement (string with friendly suggestion if needed)
    - confidence (0-1)
    
    Content: \"" . substr($content, 0, 2000) . "\"
    
    JSON:";

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.openai.com/v1/chat/completions',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'model' => 'gpt-3.5-turbo',
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'temperature' => 0.1,
            'max_tokens' => 500
        ]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key
        ],
        CURLOPT_TIMEOUT => 10
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        error_log("OpenAI API error: $http_code - $response");
        return ['error' => 'AI service unavailable'];
    }
    
    $result = json_decode($response, true);
    $ai_response = $result['choices'][0]['message']['content'] ?? '{}';
    
    return json_decode($ai_response, true) ?? ['error' => 'Invalid AI response'];
}

function ai_generate_engagement($topic_title, $topic_content, $existing_comments = []) {
    $api_key = getenv('OPENAI_API_KEY');
    if (!$api_key) return "Welcome to the discussion! Feel free to share your thoughts and questions.";
    
    $context = $existing_comments ? 
        "Recent comments: " . implode('; ', array_slice($existing_comments, -2)) : 
        "This is a new discussion topic.";
    
    $prompt = "As a friendly AI discussion facilitator, write a brief, engaging response (max 2 sentences) that:
    - Welcomes participants and encourages discussion
    - Asks one relevant question to spark conversation  
    - Is positive and inclusive
    - Does NOT sound like an AI bot
    
    Topic: $topic_title
    Content: $topic_content
    Context: $context";
    
    // Similar API call as ai_moderate_content but with different prompt
    // Implementation shortened for brevity...
    
    return "Thanks for starting this discussion! What has everyone's experience been with this topic?";
}

function log_ai_moderation($user_id, $content_type, $content_id, $result, $action) {
    global $conn;
    
    $sql = "INSERT INTO ai_moderation_logs (user_id, content_type, content_id, toxicity_score, flags, action_taken, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $flags_json = json_encode($result['flags'] ?? []);
    $toxicity_score = $result['toxicity_score'] ?? 0;
    
    $stmt->bind_param('isiiis', $user_id, $content_type, $content_id, $toxicity_score, $flags_json, $action);
    $stmt->execute();
    return $stmt->insert_id;
}
?>