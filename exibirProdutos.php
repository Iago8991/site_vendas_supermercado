<?php
session_start();
require("bd_config.php");
//error_reporting(0);

$busca = "";
$categoria = "";
$whereClause = "";
$conditions = [];

// Se houver um termo de busca, utiliza para filtrar produtos
if (isset($_GET['busca']) && !empty(trim($_GET['busca']))) {
    // Remove espaços no início e no fim
    $busca = trim(mysqli_real_escape_string($con, $_GET['busca']));
    
    // Divide a pesquisa em palavras individuais e remove palavras vazias
    $palavras = array_filter(explode(" ", $busca), function($p) {
        return trim($p) !== "";
    });
    
    $searchConditions = [];
    foreach ($palavras as $palavra) {
        $palavra = trim($palavra);
        $searchConditions[] = "(produtos_nome LIKE '%$palavra%' OR produtos_descricao LIKE '%$palavra%')";
    }
    if (count($searchConditions) > 0) {
        // Junta as condições de busca com OR
        $conditions[] = "(" . implode(" OR ", $searchConditions) . ")";
    }
}

// Se houver filtro por categoria, adiciona essa condição
if (isset($_GET['categoria']) && !empty(trim($_GET['categoria']))) {
    $categoria = trim(mysqli_real_escape_string($con, $_GET['categoria']));
    $conditions[] = "categoria = '$categoria'";
}

// Se houver alguma condição, junta-as com AND; caso contrário, retorna todos os produtos
if (count($conditions) > 0) {
    $whereClause = " WHERE " . implode(" AND ", $conditions);
} else {
    $whereClause = "";
}

$sql = "SELECT * FROM produtos $whereClause ORDER BY produtos_nome";
$resultado = mysqli_query($con, $sql);

if (!$resultado) {
    die("Erro na consulta: " . mysqli_error($con));
}
?>

<html>
<head>
    <link rel="stylesheet" href="/css/exibirProdutos.css">
    <meta charset="UTF-8">
    <title>Exibir Produtos</title>
</head>
<body>
    <div class="exibirProdutosContainer">
        <h1>Produtos</h1>

        <!-- Formulário de busca e filtro -->
        <div class="filtroDePesquisa">
            <form action="exibirProdutos.php" method="GET" id="searchForm">
                <div class="search-input">
                    <input type="text" id="busca" name="busca" placeholder="Pesquisar por nome ou descrição" value="<?=htmlspecialchars($busca)?>">
                    <span type="text" id="busca" name="busca"
                </div>
                
                <select name="categoria">
                    <option value="">Todas as Categorias</option>
                    <option value="alimentos" <?= ($categoria == "alimentos") ? 'selected' : '' ?>>Alimentos</option>
                    <option value="carne" <?= ($categoria == "carne") ? 'selected' : '' ?>>Carne</option>
                    <option value="bebidas" <?= ($categoria == "bebidas") ? 'selected' : '' ?>>Bebidas</option>
                    <option value="padaria" <?= ($categoria == "padaria") ? 'selected' : '' ?>>Padaria</option>
                    <option value="vegetal" <?= ($categoria == "vegetal") ? 'selected' : '' ?>>Vegetal</option>
                    <option value="fruta" <?= ($categoria == "fruta") ? 'selected' : '' ?>>Fruta</option>
                    <option value="hortifruti" <?= ($categoria == "hortifruti") ? 'selected' : '' ?>>Hortifrúti (Legumes, Frutas, Relacionados)</option>
                    <option value="alimentosCongelados" <?= ($categoria == "alimentosCongelados") ? 'selected' : '' ?>>Alimentos Congelados</option>
                    <option value="frios" <?= ($categoria == "frios") ? 'selected' : '' ?>>Frios</option>
                    <option value="produtosDeLimpeza" <?= ($categoria == "produtosDeLimpeza") ? 'selected' : '' ?>>Produtos De Limpeza</option>
                    <option value="higienePessoal" <?= ($categoria == "higienePessoal") ? 'selected' : '' ?>>Higiene Pessoal</option>
                    <option value="outros" <?= ($categoria == "outros") ? 'selected' : '' ?>>Outros</option>
                </select>
                <button type="submit">Filtrar</button>
            </form>
        </div>

        <div class="produtosGrade">
            <?php while ($produto = mysqli_fetch_assoc($resultado)) { ?>
                <div class="produtoItem">
                    <img src="upload_produtos/<?= htmlspecialchars($produto['produtos_imagem']) ?>" alt="<?= htmlspecialchars($produto['produtos_nome']) ?>" width="100px" height="100px">
                    <h3><?= htmlspecialchars($produto['produtos_nome']) ?></h3>
                    <p><?= htmlspecialchars($produto['produtos_descricao']) ?></p>
                    <p class="price">R$ <?= number_format($produto['produtos_preco'], 2, ',', '.') ?></p>
                    <?php if (isset($produto['produtos_desconto']) && $produto['produtos_desconto'] > 0) { ?>
                        <p class="disconto">Desconto: <?= $produto['produtos_desconto'] ?>%</p>
                    <?php } ?>
                    <a href="carrinho.php?adicionar=<?= $produto['produtos_id'] ?>">Comprar</a>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php mysqli_close($con); ?>
</body>
</html>


<?php
