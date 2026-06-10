<?php
declare(strict_types=1);
require '_configs.php';
require '_database.php';

header('Content-Type: text/html; charset=utf-8');

/** helpers **/
function h(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function bad_request(string $msg): void {
    http_response_code(400);
    echo "<!doctype html><meta charset='utf-8'><title>Error</title><p>".h($msg)."</p>";
    exit;
}
/** integer-like money formatter: no decimals, comma thousands */
function fmt_amt($n): string {
    return number_format((float)$n, 0, '.', ',');
}
/** percent formatter (two decimals) */
function fmt_pct($x): string {
    return number_format((float)$x, 2) . '%';
}

/** tiny svg builder: safe number casting */
function n($x){ return is_numeric($x) ? (0+$x) : 0; }

/** SVG: LTV line chart (avg LTV per user by day since join) */
function svg_ltv_line(array $curve, int $cohortSize): string {
    if (empty($curve) || $cohortSize <= 0) return '<p class="muted">No purchases to plot.</p>';
    $w = 720; $h = 240; $padL = 40; $padR = 10; $padT = 10; $padB = 30;
    $plotW = $w - $padL - $padR; $plotH = $h - $padT - $padB;

    // X: day index, Y: avg_ltv
    $days = array_keys($curve);
    $maxX = max($days);
    $maxY = 0.0;
    $curve_days = array_keys($curve);
    $curve_count = count($curve_days);
    for ($curve_index = 0; $curve_index < $curve_count; $curve_index++) {
        $d = $curve_days[$curve_index];
        $v = $curve[$d];
        $maxY = max($maxY, (float)$v['avg_ltv']);
    }
    if ($maxY <= 0) $maxY = 1;

    $points = [];
    for ($curve_index = 0; $curve_index < $curve_count; $curve_index++) {
        $d = $curve_days[$curve_index];
        $v = $curve[$d];
        $x = $padL + ($maxX>0 ? ($d / $maxX) * $plotW : 0);
        $y = $padT + $plotH - ($v['avg_ltv'] / $maxY) * $plotH;
        $points[] = round($x,1) . ',' . round($y,1);
    }

    $yTicks = 4;
    $grid = '';
    for ($i=0;$i<=$yTicks;$i++){
        $yy = $padT + ($plotH * $i/$yTicks);
        $grid .= '<line x1="'.$padL.'" y1="'.round($yy,1).'" x2="'.($w-$padR).'" y2="'.round($yy,1).'" stroke="#eee"/>';
    }

    $xAxis = '<line x1="'.$padL.'" y1="'.($padT+$plotH).'" x2="'.($w-$padR).'" y2="'.($padT+$plotH).'" stroke="#999"/>';
    $yAxis = '<line x1="'.$padL.'" y1="'.$padT.'" x2="'.$padL.'" y2="'.($padT+$plotH).'" stroke="#999"/>';

    $labels = '';
    // Y labels
    for ($i=0;$i<=$yTicks;$i++){
        $val = ($maxY * (1 - $i/$yTicks));
        $yy = $padT + ($plotH * $i/$yTicks);
        $labels .= '<text x="'.($padL-6).'" y="'.round($yy+4,1).'" font-size="10" text-anchor="end">'.fmt_amt($val).'</text>';
    }
    // X labels: 0, mid, max
    $labels .= '<text x="'.$padL.'" y="'.($h-8).'" font-size="10" text-anchor="middle">0</text>';
    $labels .= '<text x="'.($padL+$plotW/2).'" y="'.($h-8).'" font-size="10" text-anchor="middle">'.round($maxX/2).'</text>';
    $labels .= '<text x="'.($padL+$plotW).'" y="'.($h-8).'" font-size="10" text-anchor="end">'.$maxX.'</text>';

    $poly = '<polyline fill="none" stroke="#2a6" stroke-width="2" points="'.implode(' ', $points).'"/>';

    return <<<SVG
<svg viewBox="0 0 $w $h" width="100%" height="auto" role="img" aria-label="Avg LTV per user by day">
  <rect x="0" y="0" width="$w" height="$h" fill="white"/>
  $grid
  $xAxis
  $yAxis
  $poly
  $labels
</svg>
SVG;
}

/** SVG: Horizontal bar chart for revenue by market */
function svg_market_bars(array $markets): string {
    if (empty($markets)) return '<p class="muted">No market data to plot.</p>';
    // sort descending by revenue
    usort($markets, function($a, $b) {
        if ($b['revenue'] == $a['revenue']) return 0;
        return ($b['revenue'] > $a['revenue']) ? 1 : -1;
    });

    $labels = [];
    $values = [];
    foreach ($markets as $m) {
        $labels[] = $m['market'] === '' ? '(unknown)' : $m['market'];
        $values[] = (float)$m['revenue'];
    }
    $n = count($values);

    $w = 720; $barH = 12; $gap = 10; $padL = 50; $padR = 50; $padT = 10; $padB = 10;
    $h = $padT + $padB + $n*$barH + max(0,($n-1))*$gap;
    $plotW = $w - $padL - $padR;
    $maxV = max(1, max($values));

    $bars = '';
    $texts = '';
    for ($i=0;$i<$n;$i++){
        $y = $padT + $i*($barH+$gap);
        $bw = $plotW * ($values[$i] / $maxV);
        $bars .= '<rect x="'.$padL.'" y="'.$y.'" width="'.round($bw,1).'" height="'.$barH.'" fill="#48a"/>';
        $texts .= '<text x="'.($padL-8).'" y="'.($y+$barH*0.72).'" font-size="11" text-anchor="end">'.h($labels[$i]).'</text>';
        $texts .= '<text x="'.($padL+round($bw,1)+6).'" y="'.($y+$barH*0.72).'" font-size="10">'.fmt_amt($values[$i]).'</text>';
    }

    // X-axis (0 and max labels)
    $axis = '<line x1="'.$padL.'" y1="'.($h-$padB).'" x2="'.($w-$padR).'" y2="'.($h-$padB).'" stroke="#999"/>';
    $axis .= '<text x="'.$padL.'" y="'.($h-2).'" font-size="10" text-anchor="middle">0</text>';
    $axis .= '<text x="'.($w-$padR).'" y="'.($h-2).'" font-size="10" text-anchor="end">'.fmt_amt($maxV).'</text>';

    return <<<SVG
<svg viewBox="0 0 $w $h" width="100%" height="auto" role="img" aria-label="Revenue by market">
  <rect x="0" y="0" width="$w" height="$h" fill="white"/>
  $bars
  $texts
  $axis
</svg>
SVG;
}

/** read input **/
$start = $_GET['start_date'] ?? '';
$end   = $_GET['end_date']   ?? '';
$build = $_GET['join_build'] ?? '';

$hasQuery = ($start !== '' && $end !== '' && $build !== '');

$cohortSize = 0;
$totalRevenue = 0.0;
$payingUsers = 0;
$curve = [];
$maxDay = 0;
$purchasesWindow = array();
$purchasesWindow['min'] = null;
$purchasesWindow['max'] = null;

// per-market aggregates
$markets = []; // each: ['market','revenue','payers','conv','arpu','arppu']

if ($hasQuery) {
    $reDate = '/^\d{4}-\d{2}-\d{2}$/';
    if (!preg_match($reDate, $start)) bad_request("Invalid start_date (expected YYYY-MM-DD).");
    if (!preg_match($reDate, $end))   bad_request("Invalid end_date (expected YYYY-MM-DD).");
    if ($start > $end)                bad_request("start_date must be <= end_date.");

    $db = database::connect();
    if ($db === null || $db->is_null()) {
        http_response_code(500);
        echo "<!doctype html><meta charset='utf-8'><title>DB error</title><p>Database connection failed.</p>";
        exit;
    }

    // escape inputs
    $startEsc = $db->conn->real_escape_string($start);
    $endEsc   = $db->conn->real_escape_string($end);
    $buildEsc = $db->conn->real_escape_string($build);

    // 1) Cohort size
    $sqlCohort = "
        SELECT COUNT(*) AS n
        FROM `profile`
        WHERE `join_date` BETWEEN '{$startEsc}' AND '{$endEsc}'
          AND `join_build` = '{$buildEsc}'
    ";
    $db->query($sqlCohort);
    if ($db->has_result()) {
        $row = $db->result->fetch_assoc();
        $cohortSize = (int)($row['n'] ?? 0);
    }

    if ($cohortSize > 0) {
        // 2) Revenue per user (to count payers & total revenue)
        $sqlUserRevenue = "
            SELECT pu.profile_id, SUM(pu.price) AS revenue
            FROM `purchases` pu
            INNER JOIN `profile` p
                ON p.id = pu.profile_id
            WHERE p.`join_date` BETWEEN '{$startEsc}' AND '{$endEsc}'
              AND p.`join_build` = '{$buildEsc}'
              AND pu.`status` = 1
              AND DATE(pu.`timestamp`) >= p.`join_date`
            GROUP BY pu.profile_id
        ";
        $db->query($sqlUserRevenue);
        if ($db->has_result()) {
            while ($r = $db->result->fetch_assoc()) {
                $rev = (float)($r['revenue'] ?? 0);
                $totalRevenue += $rev;
                if ($rev > 0) $payingUsers++;
            }
        }

        // 3) Daily revenue aligned to day since join
        $sqlDaily = "
            SELECT
                DATEDIFF(DATE(pu.`timestamp`), p.`join_date`) AS day_since_join,
                SUM(pu.price) AS revenue_day,
                MIN(DATE(pu.`timestamp`)) AS first_day,
                MAX(DATE(pu.`timestamp`)) AS last_day
            FROM `purchases` pu
            INNER JOIN `profile` p
                ON p.id = pu.profile_id
            WHERE p.`join_date` BETWEEN '{$startEsc}' AND '{$endEsc}'
              AND p.`join_build` = '{$buildEsc}'
              AND pu.`status` = 1
              AND DATE(pu.`timestamp`) >= p.`join_date`
            GROUP BY day_since_join
            HAVING day_since_join >= 0
            ORDER BY day_since_join ASC
        ";
        $db->query($sqlDaily);
        $revByDay = [];
        if ($db->has_result()) {
            while ($r = $db->result->fetch_assoc()) {
                $d = (int)$r['day_since_join'];
                $revByDay[$d] = (float)($r['revenue_day'] ?? 0);
                $maxDay = max($maxDay, $d);
                $purchasesWindow['min'] = $purchasesWindow['min'] ? min($purchasesWindow['min'], $r['first_day']) : $r['first_day'];
                $purchasesWindow['max'] = $purchasesWindow['max'] ? max($purchasesWindow['max'], $r['last_day'])  : $r['last_day'];
            }
        }
        $cum = 0.0;
        for ($d = 0; $d <= $maxDay; $d++) {
            $dayRev = $revByDay[$d] ?? 0.0;
            $cum += $dayRev;
            $curve_item = array();
            $curve_item['rev_day'] = $dayRev;
            $curve_item['rev_cum'] = $cum;
            $curve_item['avg_ltv'] = $cohortSize > 0 ? ($cum / $cohortSize) : 0.0;
            $curve[$d] = $curve_item;
        }

        // 4) Per-market breakdown
        // revenue & payers per market (payers are distinct profiles with revenue > 0 in that market)
        $sqlMarket = "
            SELECT
                COALESCE(pu.market, '') AS market,
                SUM(pu.price) AS revenue,
                COUNT(DISTINCT CASE WHEN pu.price > 0 THEN pu.profile_id END) AS payers
            FROM `purchases` pu
            INNER JOIN `profile` p
                ON p.id = pu.profile_id
            WHERE p.`join_date` BETWEEN '{$startEsc}' AND '{$endEsc}'
              AND p.`join_build` = '{$buildEsc}'
              AND pu.`status` = 1
              AND DATE(pu.`timestamp`) >= p.`join_date`
            GROUP BY COALESCE(pu.market, '')
            ORDER BY revenue DESC
        ";
        $db->query($sqlMarket);
        if ($db->has_result()) {
            while ($r = $db->result->fetch_assoc()) {
                $m = array();
                $m['market'] = (string)($r['market'] ?? '');
                $m['revenue'] = (float)($r['revenue'] ?? 0);
                $m['payers'] = (int)($r['payers'] ?? 0);
                // Conversion relative to full cohort (consistent frame)
                $m['conv']  = ($cohortSize > 0) ? (100.0 * $m['payers'] / $cohortSize) : 0.0;
                // ARPU relative to full cohort for comparability; ARPPU relative to payers in that market
                $m['arpu']  = ($cohortSize > 0) ? ($m['revenue'] / $cohortSize) : 0.0;
                $m['arppu'] = ($m['payers'] > 0) ? ($m['revenue'] / $m['payers']) : 0.0;

                $markets[] = $m;
            }
        }
    }

    $db->close();
}
?>
<!doctype html>
<html lang="en">
<meta charset="utf-8">
<title>Cohort LTV</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  body { font: 16px/1.5 system-ui, -apple-system, Segoe UI, Roboto, sans-serif; margin: 2rem; }
  label { display:block; margin:.6rem 0 .2rem; }
  input[type="date"], input[type="text"] { width: 100%; padding:.5rem; }
  button { margin-top: 1rem; padding:.6rem 1rem; }
  code { background: #f6f6f6; padding:.1rem .3rem; }
  .muted { color:#555; }
  .grid { display:grid; gap:1rem; grid-template-columns: repeat(auto-fit, minmax(15rem, 1fr)); margin-top: 1rem; }
  .card { border:1px solid #eee; border-radius:.75rem; padding:1rem; }
  table { width:100%; border-collapse: collapse; margin-top: 1rem; }
  th, td { text-align:right; padding:.4rem .5rem; border-bottom: 1px solid #eee; }
  th:first-child, td:first-child { text-align:left; }
  .nowrap { white-space: nowrap; }
  .section { margin-top: 2rem; }
</style>

<h1>Cohort LTV</h1>

<?php if (!$hasQuery): ?>
  <p>Enter a date range (<code>YYYY-MM-DD</code>) and a <code>join_build</code> to compute LTV for that cohort.</p>
  <form method="get" action="">
    <label for="start_date">Start date</label>
    <input id="start_date" name="start_date" type="date" required pattern="\d{4}-\d{2}-\d{2}">

    <label for="end_date">End date</label>
    <input id="end_date" name="end_date" type="date" required pattern="\d{4}-\d{2}-\d{2}">

    <label for="join_build">Join build</label>
    <input id="join_build" name="join_build" type="text" required placeholder="e.g. 1.2.3">

    <button type="submit">Compute LTV</button>
  </form>

<?php else: ?>
  <header>
    <p class="muted">
      Cohort: <code><?=h($start)?></code> → <code><?=h($end)?></code> ·
      join_build: <code><?=h($build)?></code> ·
      profiles: <strong><?=h((string)$cohortSize)?></strong>
      <?php if ($purchasesWindow['min'] && $purchasesWindow['max']): ?>
        · purchases window: <span class="nowrap"><code><?=h($purchasesWindow['min'])?></code> → <code><?=h($purchasesWindow['max'])?></code></span>
      <?php endif; ?>
    </p>
  </header>

  <?php if ($cohortSize === 0): ?>
    <p>No profiles found for this selection.</p>
    <p><a href="<?=h($_SERVER['PHP_SELF'])?>">← New search</a></p>
  <?php else: ?>
    <section class="grid">
      <div class="card">
        <div class="muted">Total Revenue</div>
        <div><strong><?=fmt_amt($totalRevenue)?></strong></div>
      </div>
      <div class="card">
        <div class="muted">ARPU (Revenue / Users)</div>
        <div><strong><?=fmt_amt($cohortSize > 0 ? $totalRevenue / $cohortSize : 0)?></strong></div>
      </div>
      <div class="card">
        <div class="muted">Paying Users</div>
        <div><strong><?=h((string)$payingUsers)?></strong></div>
      </div>
      <div class="card">
        <div class="muted">Payer Conversion</div>
        <div><strong><?=($cohortSize>0? number_format(100*$payingUsers/$cohortSize, 2):'0.00')?>%</strong></div>
      </div>
      <div class="card">
        <div class="muted">ARPPU (Revenue / Payers)</div>
        <div><strong><?=fmt_amt($payingUsers > 0 ? $totalRevenue / $payingUsers : 0)?></strong></div>
      </div>
      <div class="card">
        <div class="muted">Max Day in Curve</div>
        <div><strong><?=h((string)$maxDay)?></strong></div>
      </div>
    </section>

    <div class="section">
      <h2>LTV Curve (Avg per User)</h2>
      <?=svg_ltv_line($curve, $cohortSize)?>
      <table>
        <thead>
          <tr>
            <th>Day</th>
            <th>Revenue (Day)</th>
            <th>Cumulative Revenue</th>
            <th>Avg LTV / User</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($curve)): ?>
            <tr><td colspan="4" style="text-align:center;">No purchases found.</td></tr>
          <?php else: ?>
            <?php $curve_days = array_keys($curve); ?>
            <?php $curve_count = count($curve_days); ?>
            <?php for ($curve_index = 0; $curve_index < $curve_count; $curve_index++): ?>
              <?php $d = $curve_days[$curve_index]; ?>
              <?php $vals = $curve[$d]; ?>
              <tr>
                <td><?=h((string)$d)?></td>
                <td><?=fmt_amt($vals['rev_day'])?></td>
                <td><?=fmt_amt($vals['rev_cum'])?></td>
                <td><?=fmt_amt($vals['avg_ltv'])?></td>
              </tr>
            <?php endfor; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="section">
      <h2>Per-Market Breakdown</h2>
      <?=svg_market_bars($markets)?>
      <table>
        <thead>
          <tr>
            <th>Market</th>
            <th>Revenue</th>
            <th>Payers</th>
            <th>Conversion</th>
            <th>ARPU</th>
            <th>ARPPU</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($markets)): ?>
            <tr><td colspan="6" style="text-align:center;">No market data.</td></tr>
          <?php else: ?>
            <?php foreach ($markets as $m): ?>
              <tr>
                <td><?=h($m['market'] === '' ? '(unknown)' : $m['market'])?></td>
                <td><?=fmt_amt($m['revenue'])?></td>
                <td><?=h((string)$m['payers'])?></td>
                <td><?=fmt_pct($cohortSize>0 ? (100.0*$m['payers']/$cohortSize) : 0)?></td>
                <td><?=fmt_amt($m['arpu'])?></td>
                <td><?=fmt_amt($m['arppu'])?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <p class="muted">Notes: LTV curve is cohort-aligned (Day 0 = join date). Market ARPU uses full cohort size for comparability; ARPPU uses market payers.</p>
    <p><a href="<?=h($_SERVER['PHP_SELF'])?>">← New search</a></p>
  <?php endif; ?>
<?php endif; ?>
</html>
