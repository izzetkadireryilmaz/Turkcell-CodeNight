<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// --- DB ---
$host = 'localhost';
$dbname = 'b';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}

// --- KATEGORİ BELİRLEYİCİ ---
function detectCategory($msg) {
    $msg = mb_strtolower($msg);

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

    foreach($keywords as $word => $category) {
        $pos = mb_strpos($msg, $word);
        if ($pos !== false) {
            $positions[$pos] = $category;
        }
    }

    if (empty($positions)) return 'genel';

    ksort($positions);
    return array_values($positions)[0];
}

// --- FORM VERİLERİ ---
$itemName = trim($_POST['item_name'] ?? '');
$quantity = (int)($_POST['quantity'] ?? 0);
$cityName = trim($_POST['city_name'] ?? 'Bilinmiyor');

if ($itemName === '' || $quantity <= 0 || $cityName === '') {
    echo json_encode(['success' => false, 'error' => 'Eksik veya hatalı veri.']);
    exit;
}

// --- KATEGORİ ---
$category = detectCategory($itemName);

// ID üret
"RS-" . substr(md5(microtime()), 0, 20);

// EKLEME
try {
    $stmt = $pdo->prepare("INSERT INTO resources (resource_id, provider, category, quantity, city, available)
                           VALUES (?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        $resource_id,
        "Web Formu Bağışçısı",
        $category,
        $quantity,
        $cityName,
        1
    ]);

    echo json_encode([
        'success' => true,
        'resource_id' => $resource_id,
        'category' => $category,
        'message' => 'Resources tablosuna başarıyla eklendi.'
    ]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

?>
