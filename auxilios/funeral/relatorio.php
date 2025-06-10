<?php
// Configuração do banco
$host = 'localhost';
$db   = 'seu_banco';
$user = 'seu_usuario';
$pass = 'sua_senha';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (Exception $e) {
    die("Erro na conexão com o banco: " . $e->getMessage());
}

// Limpar avaliações (se o botão for clicado)
if (isset($_POST['limpar'])) {
    $pdo->exec("TRUNCATE TABLE avaliacoes");
    header("Location: relatorio.php");
    exit;
}

// Buscar todas avaliações
$stmt = $pdo->query("SELECT nota, data_hora FROM avaliacoes ORDER BY data_hora DESC");
$avaliacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contar por nota
$stmt = $pdo->query("SELECT nota, COUNT(*) as total FROM avaliacoes GROUP BY nota");
$contagemNotasRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

$contagem = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
foreach ($contagemNotasRaw as $linha) {
    $nota = intval($linha['nota']);
    $contagem[$nota] = intval($linha['total']);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8" />
<title>Relatório de Avaliações com MySQL</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: auto; }
h1 { margin-bottom: 20px; }
table { border-collapse: collapse; width: 100%; margin-top: 20px; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
th { background-color: #f4f4f4; }
#graficoContainer { max-width: 600px; margin: auto; }
form { margin-top: 30px; text-align: center; }
button {
  background-color: #d9534f;
  border: none;
  padding: 10px 20px;
  color: white;
  font-size: 16px;
  cursor: pointer;
  border-radius: 4px;
}
button:hover {
  background-color: #c9302c;
}
</style>
</head>
<body>

<h1>Relatório de Avaliações</h1>

<div id="graficoContainer">
  <canvas id="graficoAvaliacoes"></canvas>
</div>

<?php if (count($avaliacoes) > 0): ?>
  <table>
    <thead>
      <tr>
        <th>Nota</th>
        <th>Data/Hora</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($avaliacoes as $av): ?>
        <tr>
          <td><?= htmlspecialchars($av['nota']) ?></td>
          <td><?= htmlspecialchars($av['data_hora']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <form method="post" onsubmit="return confirm('Tem certeza que quer limpar todas as avaliações?');">
    <button type="submit" name="limpar">Limpar Avaliações</button>
  </form>
<?php else: ?>
  <p>Nenhuma avaliação registrada ainda.</p>
<?php endif; ?>

<script>
const contagem = <?= json_encode($contagem) ?>;
const labels = ['1 estrela', '2 estrelas', '3 estrelas', '4 estrelas', '5 estrelas'];
const dados = labels.map((_, i) => contagem[i + 1] || 0);

const ctx = document.getElementById('graficoAvaliacoes').getContext('2d');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: labels,
    datasets: [{
      label: 'Número de Avaliações',
      data: dados,
      backgroundColor: 'rgba(75, 192, 192, 0.7)',
      borderColor: 'rgba(75, 192, 192, 1)',
      borderWidth: 1
    }]
  },
  options: {
    scales: {
      y: { beginAtZero: true, precision: 0 }
    }
  }
});
</script>

</body>
</html>
