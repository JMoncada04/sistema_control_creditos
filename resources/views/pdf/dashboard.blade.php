<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size:11px; color:#1e293b; background:#fff; }
  .header { background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%); color:#fff; padding:28px 36px 22px; }
  .header-top { display:flex; justify-content:space-between; align-items:flex-start; }
  .logo-area { display:flex; align-items:center; gap:12px; }
  .logo-icon { width:42px; height:42px; background:#3b82f6; border-radius:10px; display:flex; align-items:center; justify-content:center; }
  .logo-icon svg { width:24px; height:24px; fill:#fff; }
  .brand-name { font-size:22px; font-weight:700; letter-spacing:-0.5px; color:#fff; }
  .brand-sub  { font-size:11px; color:#94a3b8; margin-top:2px; }
  .report-meta { text-align:right; }
  .report-title { font-size:14px; font-weight:600; color:#e2e8f0; }
  .report-date  { font-size:10px; color:#94a3b8; margin-top:4px; }
  .header-divider { border:none; border-top:1px solid rgba(255,255,255,0.12); margin:18px 0 0; }
  .content { padding:24px 36px; }
  .section-title { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:1.2px; color:#64748b; margin-bottom:12px; padding-bottom:6px; border-bottom:1px solid #e2e8f0; }
  .stats-grid { display:flex; gap:12px; margin-bottom:24px; }
  .stat-card { flex:1; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:14px 16px; border-left:3px solid #3b82f6; }
  .stat-card.green  { border-left-color:#22c55e; }
  .stat-card.amber  { border-left-color:#f59e0b; }
  .stat-card.red    { border-left-color:#ef4444; }
  .stat-label { font-size:9px; text-transform:uppercase; letter-spacing:0.8px; color:#64748b; font-weight:600; }
  .stat-value { font-size:20px; font-weight:700; color:#0f172a; margin-top:4px; letter-spacing:-0.5px; }
  .stat-value.money { font-size:15px; }
  table { width:100%; border-collapse:collapse; margin-bottom:24px; }
  thead tr { background:#0f172a; color:#fff; }
  thead th { padding:9px 12px; font-size:9px; text-transform:uppercase; letter-spacing:0.8px; font-weight:600; text-align:left; }
  tbody tr:nth-child(even) { background:#f8fafc; }
  tbody td { padding:8px 12px; font-size:10px; color:#334155; border-bottom:1px solid #f1f5f9; }
  .badge { padding:2px 8px; border-radius:99px; font-size:9px; font-weight:600; }
  .badge-mora     { background:#fee2e2; color:#991b1b; }
  .badge-pendiente{ background:#fef9c3; color:#854d0e; }
  .badge-pagada   { background:#dcfce7; color:#166534; }
  .money { font-family: DejaVu Sans Mono, monospace; }
  .two-col { display:flex; gap:20px; margin-bottom:24px; }
  .two-col > div { flex:1; }
  .alert-row { display:flex; gap:8px; align-items:flex-start; padding:8px 0; border-bottom:1px solid #f1f5f9; }
  .alert-dot  { width:8px; height:8px; border-radius:50%; background:#ef4444; margin-top:3px; flex-shrink:0; }
  .alert-text { font-size:10px; color:#334155; line-height:1.5; }
  .footer { margin-top:16px; padding-top:12px; border-top:1px solid #e2e8f0; display:flex; justify-content:space-between; font-size:9px; color:#94a3b8; }
  .page-break { page-break-before:always; }
</style>
</head>
<body>

<!-- HEADER -->
<div class="header">
  <div class="header-top">
    <div class="logo-area">
      <div class="logo-icon">
        <svg viewBox="0 0 24 24"><path d="M20 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
      </div>
      <div>
        <div class="brand-name">CreditPro</div>
        <div class="brand-sub">Sistema de Control de Créditos</div>
      </div>
    </div>
    <div class="report-meta">
      <div class="report-title">Reporte Ejecutivo — Dashboard</div>
      <div class="report-date">Generado el {{ $fecha }}</div>
    </div>
  </div>
  <hr class="header-divider">
</div>

<!-- CONTENT -->
<div class="content">

  <!-- STATS -->
  <div class="section-title">Resumen general</div>
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-label">Créditos activos</div>
      <div class="stat-value">{{ $creditosActivos }}</div>
    </div>
    <div class="stat-card green">
      <div class="stat-label">Cobrado este mes</div>
      <div class="stat-value money">L. {{ number_format($cobradoEsteMes, 2) }}</div>
    </div>
    <div class="stat-card amber">
      <div class="stat-label">Cuotas pendientes (30d)</div>
      <div class="stat-value">{{ $cuotasPendientes }}</div>
    </div>
    <div class="stat-card red">
      <div class="stat-label">Clientes en mora</div>
      <div class="stat-value">{{ $clientesEnMora }}</div>
    </div>
  </div>

  <!-- CUOTAS PROXIMAS -->
  <div class="section-title">Cuotas próximas a vencer (próximos 7 días)</div>
  @if($proximasCuotas->count())
  <table>
    <thead>
      <tr>
        <th>Cliente</th><th>Venta #</th><th>Cuota N°</th>
        <th>Vencimiento</th><th>Monto</th><th>Estado</th>
      </tr>
    </thead>
    <tbody>
      @foreach($proximasCuotas as $c)
      <tr>
        <td>{{ $c['cliente'] }}</td>
        <td class="money">#{{ $c['venta'] }}</td>
        <td>{{ $c['cuota'] }}</td>
        <td>{{ $c['fecha'] }}</td>
        <td class="money">L. {{ number_format($c['monto'], 2) }}</td>
        <td><span class="badge badge-{{ strtolower($c['estado']) }}">{{ $c['estado'] }}</span></td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @else
  <p style="color:#94a3b8;font-size:11px;margin-bottom:24px;">Sin cuotas próximas a vencer.</p>
  @endif

  <!-- ALERTAS MORA -->
  @if($alertasMora->count())
  <div class="section-title">Alertas activas — Cuotas en mora</div>
  @foreach($alertasMora as $a)
  <div class="alert-row">
    <div class="alert-dot"></div>
    <div class="alert-text"><strong>{{ $a['cliente'] }}</strong> — Venta #{{ $a['venta'] }}, Cuota {{ $a['cuota'] }} vencida.</div>
  </div>
  @endforeach
  @endif

  <!-- FOOTER -->
  <div class="footer">
    <span>CreditPro · Sistema de Control de Créditos</span>
    <span>Generado automáticamente · {{ $fecha }}</span>
  </div>

</div>
</body>
</html>
