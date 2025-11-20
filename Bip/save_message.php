<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$host = 'localhost';
$dbname = 'b';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Veritabanı bağlantısı başarısız: ' . $e->getMessage()]);
    exit;
}

function detectCategory($msg) {
    $msg = mb_strtolower($msg, 'UTF-8');
    
    $keywords = [
        'su'        => 'su',
        'yiyecek'   => 'yiyecek',
        'gıda'      => 'yiyecek',
        'yemek'     => 'yiyecek',
        'çadır'     => 'barınma',
        'battaniye' => 'barınma',
        'barınma'   => 'barınma',
        'ilaç'      => 'sağlık',
        'doktor'    => 'sağlık',
        'sağlık'    => 'sağlık'
    ];
    
    $positions = [];
    foreach ($keywords as $word => $category) {
        $pos = mb_strpos($msg, $word, 0, 'UTF-8');
        if ($pos !== false) {
            $positions[$pos] = $category;
        }
    }
    
    if (count($positions) === 0) {
        return 'genel';
    }
    
    ksort($positions);
    return array_values($positions)[0];
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['message']) || trim($data['message']) === '') {
    echo json_encode(['success' => false, 'error' => 'Mesaj boş olamaz']);
    exit;
}

$message = trim($data['message']);
$user_id = isset($data['user_id']) ? $data['user_id'] : 'U001';
$category = detectCategory($message);

try {
    $stmt = $pdo->prepare("INSERT INTO messages (user_id, message, category) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $message, $category]);
    
    echo json_encode([
        'success' => true,
        'msg_id' => $pdo->lastInsertId(),
        'category' => $category,
        'message' => 'Mesaj başarıyla kaydedildi'
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
?>