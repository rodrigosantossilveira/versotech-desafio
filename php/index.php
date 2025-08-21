<?php
declare(strict_types=1);
require 'connection.php';

// ------------------------------------------------------------
// Util
// ------------------------------------------------------------
function h(?string $v): string { return htmlspecialchars((string)$v ?? '', ENT_QUOTES, 'UTF-8'); }

$connection = new Connection();
$pdo = $connection->getConnection();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Garante tabelas (idempotente) — caso a base venha vazia
$pdo->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY, name VARCHAR(100) NOT NULL, email VARCHAR(100) NOT NULL)");
$pdo->exec("CREATE TABLE IF NOT EXISTS colors (id INTEGER PRIMARY KEY, name VARCHAR(50) NOT NULL)");
$pdo->exec("CREATE TABLE IF NOT EXISTS user_colors (user_id INTEGER NOT NULL, color_id INTEGER NOT NULL)");

$action = $_GET['action'] ?? 'list';

// ------------------------------------------------------------
// Ações POST
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create') {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if ($name === '' || $email === '') { $error = "Preencha nome e e-mail."; }
        else {
            $stmt = $pdo->prepare("INSERT INTO users (name, email) VALUES (:n, :e)");
            $stmt->execute([':n'=>$name, ':e'=>$email]);
            header("Location: ?action=list&ok=1"); exit;
        }
    } elseif ($action === 'edit') {
        $id    = (int)($_GET['id'] ?? 0);
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if ($id <= 0) { $error = "ID inválido."; }
        elseif ($name === '' || $email === '') { $error = "Preencha nome e e-mail."; }
        else {
            $stmt = $pdo->prepare("UPDATE users SET name=:n, email=:e WHERE id=:id");
            $stmt->execute([':n'=>$name, ':e'=>$email, ':id'=>$id]);
            header("Location: ?action=list&ok=1"); exit;
        }
    } elseif ($action === 'delete') {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM user_colors WHERE user_id=:id")->execute([':id'=>$id]);
            $pdo->prepare("DELETE FROM users WHERE id=:id")->execute([':id'=>$id]);
        }
        header("Location: ?action=list&ok=1"); exit;
    } elseif ($action === 'colors') {
        $id = (int)($_GET['id'] ?? 0);
        $selected = array_map('intval', $_POST['colors'] ?? []);

        // sincroniza: remove todos e insere os selecionados
        $pdo->prepare("DELETE FROM user_colors WHERE user_id=:id")->execute([':id'=>$id]);
        $ins = $pdo->prepare("INSERT INTO user_colors (user_id, color_id) VALUES (:u, :c)");
        foreach ($selected as $cid) {
            $ins->execute([':u'=>$id, ':c'=>$cid]);
        }
        header("Location: ?action=list&ok=1"); exit;
    }
}

// ------------------------------------------------------------
// Helpers de consulta
// ------------------------------------------------------------
function allUsers(PDO $pdo) {
    $rs = $pdo->query("SELECT * FROM users ORDER BY id DESC");
    return $rs->fetchAll(PDO::FETCH_OBJ);
}
function getUser(PDO $pdo, int $id) {
    $st = $pdo->prepare("SELECT * FROM users WHERE id=:id");
    $st->execute([':id'=>$id]);
    return $st->fetchObject();
}
function allColors(PDO $pdo) {
    $rs = $pdo->query("SELECT * FROM colors ORDER BY name");
    return $rs->fetchAll(PDO::FETCH_OBJ);
}
function userColorIds(PDO $pdo, int $userId): array {
    $st = $pdo->prepare("SELECT color_id FROM user_colors WHERE user_id=:id");
    $st->execute([':id'=>$userId]);
    return array_map('intval', array_column($st->fetchAll(PDO::FETCH_ASSOC), 'color_id'));
}

// ------------------------------------------------------------
// UI
// ------------------------------------------------------------
?><!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>CRUD PHP + SQLite (Users & Colors)</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 24px; background:#0b0b0c; color:#e7e7ea; }
    a { color:#7aa2ff; text-decoration:none; }
    a:hover { text-decoration:underline; }
    .btn { padding:8px 12px; border-radius:8px; border:1px solid #2e2f36; background:#16171a; color:#e7e7ea; display:inline-block; }
    .btn:hover { background:#1d1f24; }
    .btn-danger { border-color:#4a1f22; background:#281417; }
    .btn-primary { border-color:#20355a; background:#132033; }
    .card { background:#121316; border:1px solid #2e2f36; border-radius:14px; padding:16px; margin-bottom:16px; }
    table { width:100%; border-collapse:collapse; }
    th, td { border-bottom:1px solid #2e2f36; padding:10px; text-align:left; }
    th { background:#0f1013; }
    .right { text-align:right; }
    input[type=text], input[type=email] { width:100%; padding:8px; border-radius:8px; border:1px solid #2e2f36; background:#0f1013; color:#e7e7ea; }
    .grid { display:grid; grid-template-columns: 1fr 1fr; gap:12px; }
    .chips { display:flex; gap:6px; flex-wrap:wrap; }
    .chip { padding:4px 8px; border:1px solid #2e2f36; border-radius:999px; font-size:12px; background:#0f1013; }
    .muted { color:#a1a1aa; font-size:12px; }
    .ok { color:#7ee787; }
    .err { color:#ff8e8e; }
</style>
</head>
<body>
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2 style="margin:0;">Usuários</h2>
        <div>
            <a class="btn btn-primary" href="?action=create">Novo usuário</a>
            <a class="btn" href="?action=list">Atualizar</a>
        </div>
    </div>
    <?php if (!empty($_GET['ok'])): ?>
        <p class="ok">Operação realizada com sucesso.</p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <p class="err"><?= h($error) ?></p>
    <?php endif; ?>

    <?php if ($action === 'list'): ?>
        <?php $items = allUsers($pdo); ?>
        <table>
            <thead>
                <tr>
                    <th style="width:70px;">ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th style="width:260px;" class="right">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $u): ?>
                <tr>
                    <td><?= (int)$u->id ?></td>
                    <td><?= h($u->name) ?></td>
                    <td><?= h($u->email) ?></td>
                    <td class="right">
                        <a class="btn" href="?action=colors&id=<?= (int)$u->id ?>">Cores</a>
                        <a class="btn" href="?action=edit&id=<?= (int)$u->id ?>">Editar</a>
                        <form method="post" action="?action=delete&id=<?= (int)$u->id ?>" style="display:inline" onsubmit="return confirm('Excluir este usuário?');">
                            <button class="btn btn-danger" type="submit">Excluir</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$items): ?>
                <tr><td colspan="4" class="muted">Sem usuários cadastrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if ($action === 'create' || $action === 'edit'):
        $editing = $action === 'edit';
        $user = $editing ? getUser($pdo, (int)($_GET['id'] ?? 0)) : (object)['name'=>'','email'=>''];
    ?>
        <form method="post" class="card">
            <div class="grid">
                <div>
                    <label>Nome</label>
                    <input type="text" name="name" value="<?= h($user->name) ?>" required>
                </div>
                <div>
                    <label>E-mail</label>
                    <input type="email" name="email" value="<?= h($user->email) ?>" required>
                </div>
            </div>
            <div style="margin-top:12px; display:flex; gap:8px;">
                <button class="btn btn-primary" type="submit"><?= $editing ? 'Salvar' : 'Criar' ?></button>
                <a class="btn" href="?action=list">Cancelar</a>
            </div>
        </form>
    <?php endif; ?>

    <?php if ($action === 'colors'):
        $id = (int)($_GET['id'] ?? 0);
        $user = getUser($pdo, $id);
        $colors = allColors($pdo);
        $selected = userColorIds($pdo, $id);
    ?>
        <div class="card">
            <h3 style="margin-top:0;">Vincular cores — <?= h($user->name) ?> <span class="muted">(ID <?= (int)$user->id ?>)</span></h3>
            <form method="post">
                <div class="chips">
                    <?php foreach ($colors as $c): ?>
                        <label class="chip">
                            <input type="checkbox" name="colors[]" value="<?= (int)$c->id ?>" <?= in_array((int)$c->id, $selected, true) ? 'checked' : '' ?>>
                            <?= h($c->name) ?>
                        </label>
                    <?php endforeach; ?>
                    <?php if (!$colors): ?>
                        <span class="muted">Sem cores cadastradas. Insira na tabela <code>colors</code>.</span>
                    <?php endif; ?>
                </div>
                <div style="margin-top:12px; display:flex; gap:8px;">
                    <button class="btn btn-primary" type="submit">Salvar vínculos</button>
                    <a class="btn" href="?action=list">Voltar</a>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
