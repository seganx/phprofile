<?php
declare(strict_types=1);
require '_configs.php';
require '_database.php';

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function sql_escape($db, $value) { return $db->conn->real_escape_string((string)$value); }

function get_profile_by_device_id($db, $device_id) {
    $d = sql_escape($db, $device_id);
    $db->query("SELECT * FROM profile WHERE device_id='$d' LIMIT 1");
    if ($db->no_result()) return null;
    return $db->result->fetch_assoc();
}

function get_profile_by_username($db, $username) {
    $u = sql_escape($db, $username);
    $db->query("SELECT * FROM profile WHERE username='$u' LIMIT 1");
    if ($db->no_result()) return null;
    return $db->result->fetch_assoc();
}

function get_profile_by_purchase_token($db, $token) {
    $t = sql_escape($db, $token);

    $db->query("SELECT profile_id FROM purchases WHERE token='$t' ORDER BY timestamp DESC LIMIT 1");
    if ($db->no_result()) return null;

    $row = $db->result->fetch_assoc();
    $pid = (int)$row["profile_id"];

    $db->query("SELECT * FROM profile WHERE id=$pid LIMIT 1");
    if ($db->no_result()) return null;

    return $db->result->fetch_assoc();
}

function get_profile_data($db, $profile_id, $device_id) {
    $pid = (int)$profile_id;
    $db->query("SELECT * FROM profile_data WHERE profile_id=$pid LIMIT 1");
    if ($db->has_result()) return $db->result->fetch_assoc();

    // fallback (اگر به هر دلیلی profile_id نبود ولی device_id موجود بود)
    if ($device_id !== "") {
        $d = sql_escape($db, $device_id);
        $db->query("SELECT * FROM profile_data WHERE device_id='$d' LIMIT 1");
        if ($db->has_result()) return $db->result->fetch_assoc();
    }

    return null;
}

function get_purchases_by_profile_id($db, $profile_id) {
    $pid = (int)$profile_id;
    $db->query("SELECT * FROM purchases WHERE profile_id=$pid ORDER BY timestamp DESC");
    $items = [];
    if ($db->has_result()) {
        while ($r = $db->result->fetch_assoc()) $items[] = $r;
    }
    return $items;
}

$device_id = trim($_GET['device_id'] ?? '');
$username  = trim($_GET['username'] ?? '');
$token     = trim($_GET['token'] ?? '');
$doReport  = isset($_GET['run']) && $_GET['run'] === '1';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>User Purchases Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .box { border: 1px solid #ddd; padding: 16px; border-radius: 8px; margin-bottom: 16px; }
        .row { display: flex; gap: 12px; flex-wrap: wrap; }
        label { display: block; font-size: 12px; color: #444; margin-bottom: 6px; }
        input { padding: 10px; border: 1px solid #ccc; border-radius: 6px; min-width: 260px; }
        button { padding: 10px 16px; border: 0; border-radius: 6px; cursor: pointer; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; font-size: 14px; vertical-align: top; }
        th { background: #f5f5f5; text-align: left; }
        .muted { color: #666; font-size: 13px; }
        .error { background: #fff3f3; border: 1px solid #ffcccc; padding: 12px; border-radius: 8px; }
        .ok { background: #f3fff6; border: 1px solid #ccffd6; padding: 12px; border-radius: 8px; }
        .pill { display:inline-block; padding: 4px 8px; border: 1px solid #ccc; border-radius: 999px; font-size: 12px; margin-right: 6px; }
        img.avatar { max-width: 96px; max-height: 96px; border-radius: 12px; border: 1px solid #ddd; }
    </style>
</head>
<body>

<div class="box">
    <h2 style="margin-top:0;">User Purchases Report</h2>
    <p class="muted" style="margin-top:6px;">
        Enter <b>one</b> of: <span class="pill">device_id</span> or <span class="pill">username</span> or <span class="pill">purchase token</span>.
    </p>

    <!-- target حذف شد تا همان صفحه رفرش شود -->
    <form method="get" action="">
        <input type="hidden" name="run" value="1">

        <div class="row">
            <div>
                <label for="device_id">Device ID</label>
                <input id="device_id" name="device_id" value="<?=h($device_id)?>" placeholder="e.g. 123abc..." autocomplete="off">
            </div>

            <div>
                <label for="username">Username</label>
                <input id="username" name="username" value="<?=h($username)?>" placeholder="e.g. mel123" autocomplete="off">
            </div>

            <div>
                <label for="token">Purchase Token</label>
                <input id="token" name="token" value="<?=h($token)?>" placeholder="e.g. token..." autocomplete="off">
            </div>
        </div>

        <div style="margin-top:14px;">
            <button type="submit">Generate Report</button>
        </div>
    </form>
</div>

<?php if ($doReport): ?>
    <?php
    $db = database::connect();
    if ($db == null || $db->is_null()) {
        echo '<div class="error"><b>Database connection failed.</b></div>';
        exit;
    }

    $profile = null;
    $search_used = "";

    // اولویت: token -> device_id -> username
    if ($token !== "") {
        $profile = get_profile_by_purchase_token($db, $token);
        $search_used = "token";
    } elseif ($device_id !== "") {
        $profile = get_profile_by_device_id($db, $device_id);
        $search_used = "device_id";
    } elseif ($username !== "") {
        $profile = get_profile_by_username($db, $username);
        $search_used = "username";
    } else {
        echo '<div class="error"><b>Please provide device_id, username, or token.</b></div>';
        $db->close();
        exit;
    }

    if ($profile == null) {
        echo '<div class="error"><b>No profile found</b> for the provided ' . h($search_used) . '.</div>';
        $db->close();
        exit;
    }

    $profileData = get_profile_data($db, (int)$profile["id"], (string)$profile["device_id"]);
    $purchases = get_purchases_by_profile_id($db, (int)$profile["id"]);

    $total_count = count($purchases);
    $completed_count = 0;
    $total_price = 0;
    foreach ($purchases as $p)
    {
        if ((int)$p["status"] == 1)
        {
            $completed_count++;
            $total_price += (int)$p["price"];
        }
    }
    ?>

    <div class="ok" style="margin-bottom:16px;">
        <b>Profile found.</b>
        <div class="muted">Search method: <b><?=h($search_used)?></b></div>
    </div>

    <div class="box">
        <h3 style="margin-top:0;">Profile</h3>
        <table>
            <tr><th>Field</th><th>Value</th></tr>
            <tr><td>id</td><td><?=h($profile["id"])?></td></tr>
            <tr><td>device_id</td><td><?=h($profile["device_id"])?></td></tr>
            <tr><td>username</td><td><?=h($profile["username"])?></td></tr>
            <tr><td>password</td><td><?=h($profile["password"])?></td></tr>
            <tr><td>nickname</td><td><?=h($profile["nickname"])?></td></tr>
            <tr><td>status</td><td><?=h($profile["status"])?></td></tr>
            <tr>
                <td>avatar</td>
                <td>
                    <?php if (!empty($profile["avatar"])): ?>
                        <div><a href="avatars/<?=h($profile["avatar"])?>.jpg" target="_blank"><?=h($profile["avatar"])?></a></div>
                        <div style="margin-top:8px;">
                            <img class="avatar" src="avatars/<?=h($profile["avatar"])?>.jpg" alt="avatar">
                        </div>
                    <?php else: ?>
                        <span class="muted">NULL</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="box">
        <h3 style="margin-top:0;">Profile Data</h3>
        <?php if ($profileData == null): ?>
            <div class="muted">No profile_data record found for this user.</div>
        <?php else: ?>
            <table>
                <tr><th>Field</th><th>Value</th></tr>
                <tr><td>gems</td><td><?=h($profileData["gems"])?></td></tr>
                <tr><td>golds</td><td><?=h($profileData["golds"])?></td></tr>
            </table>
        <?php endif; ?>
    </div>

    <div class="box">
        <h3 style="margin-top:0;">Purchases</h3>

        <div style="margin-bottom:10px;">
            <span class="pill">Rows: <?=h($total_count)?></span>
            <span class="pill">Completed: <?=h($completed_count)?></span>
            <span class="pill">Completed amount: <?=h(number_format($total_price))?></span>
        </div>

        <?php if ($total_count === 0): ?>
            <div class="muted">No purchases for this user.</div>
        <?php else: ?>
            <table>
                <tr>
                    <th>id</th>
                    <th>timestamp</th>
                    <th>profile_id</th>
                    <th>version</th>
                    <th>market</th>
                    <th>sku</th>
                    <th>price</th>
                    <th>status</th>
                    <th>token</th>
                </tr>
                <?php foreach ($purchases as $p): ?>
                    <tr>
                        <td><?=h($p["id"])?></td>
                        <td><?=h($p["timestamp"])?></td>
                        <td><?=h($p["profile_id"])?></td>
                        <td><?=h($p["version"])?></td>
                        <td><?=h($p["market"])?></td>
                        <td><?=h($p["sku"])?></td>
                        <td><?=h(number_format((int)$p["price"]))?></td>
                        <td><?=h($p["status"])?></td>
                        <td><?=h($p["token"])?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>

    <?php $db->close(); ?>
<?php endif; ?>

</body>
</html>
