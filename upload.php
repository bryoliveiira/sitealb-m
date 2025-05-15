<?php
$host = 'localhost';
$db   = 'nome do banco';
$user = 'usuario';
$pass = 'senha';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die('Erro na conexão com o banco de dados: ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES)) {
    $tipo = isset($_FILES['foto']) ? 'foto' : (isset($_FILES['video']) ? 'video' : '');
    $arquivo = $_FILES[$tipo] ?? null;

    if ($arquivo && $arquivo['error'] === UPLOAD_ERR_OK) {
        $nomeOriginal = basename($arquivo['name']);
        $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));


        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'mp4'];
        if (!in_array($extensao, $extensoesPermitidas)) {
            die("<script>alert('Extensão de arquivo não permitida.'); window.history.back();</script>");
        }

        $diretorioDestino = __DIR__ . '/../arquivos/';
        if (!file_exists($diretorioDestino)) {
            mkdir($diretorioDestino, 0777, true);
        }

        $nomeFinal = uniqid("file_", true) . '.' . $extensao;
        $caminhoFinal = $diretorioDestino . $nomeFinal;


        if (move_uploaded_file($arquivo['tmp_name'], $caminhoFinal)) {
            $stmt = $pdo->prepare("INSERT INTO uploads (filename, filetype) VALUES (:filename, :filetype)");
            $stmt->execute([
                ':filename' => $nomeFinal,
                ':filetype' => $tipo
            ]);
            echo "<script>alert('Arquivo enviado com sucesso!'); window.history.back();</script>";
        } else {
            echo "<script>alert('Erro ao mover o arquivo.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Erro no envio do arquivo.'); window.history.back();</script>";
    }
} else {
    echo "Acesso inválido.";
}
?>
