/**
 * GlobeTrek Adventures - Lightweight Canvas Chart Library
 * Zero external dependencies, pure HTML5 Canvas for fast and reliable dashboards.
 */

window.GlobeCharts = {
  // 1. Line/Area Chart for Revenue
  drawLineChart: function(canvasId, labels, dataPoints, prefix = '$') {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();

    canvas.width = rect.width * dpr;
    canvas.height = rect.height * dpr;
    ctx.scale(dpr, dpr);

    const width = rect.width;
    const height = rect.height;
    const padding = { top: 30, right: 30, bottom: 40, left: 60 };
    const chartW = width - padding.left - padding.right;
    const chartH = height - padding.top - padding.bottom;

    ctx.clearRect(0, 0, width, height);

    const maxVal = Math.max(...dataPoints, 100) * 1.2;
    const minVal = 0;

    // Draw Grid Lines & Y-axis labels
    ctx.strokeStyle = '#e2e8f0';
    ctx.lineWidth = 1;
    ctx.fillStyle = '#64748b';
    ctx.font = '11px Inter, sans-serif';
    ctx.textAlign = 'right';

    const yTicks = 4;
    for (let i = 0; i <= yTicks; i++) {
      const val = (maxVal / yTicks) * i;
      const y = padding.top + chartH - (i / yTicks) * chartH;
      ctx.beginPath();
      ctx.moveTo(padding.left, y);
      ctx.lineTo(width - padding.right, y);
      ctx.stroke();
      ctx.fillText(prefix + Math.round(val), padding.left - 8, y + 4);
    }

    // Calculate Points
    const stepX = chartW / (labels.length - 1 || 1);
    const points = dataPoints.map((val, idx) => {
      const x = padding.left + idx * stepX;
      const y = padding.top + chartH - ((val - minVal) / (maxVal - minVal)) * chartH;
      return { x, y, val, label: labels[idx] };
    });

    if (points.length < 2) return;

    // Draw Area Gradient
    const gradient = ctx.createLinearGradient(0, padding.top, 0, padding.top + chartH);
    gradient.addColorStop(0, 'rgba(2, 132, 199, 0.35)');
    gradient.addColorStop(1, 'rgba(2, 132, 199, 0.02)');

    ctx.beginPath();
    ctx.moveTo(points[0].x, points[0].y);
    points.forEach(p => ctx.lineTo(p.x, p.y));
    ctx.lineTo(points[points.length - 1].x, padding.top + chartH);
    ctx.lineTo(points[0].x, padding.top + chartH);
    ctx.closePath();
    ctx.fillStyle = gradient;
    ctx.fill();

    // Draw Line
    ctx.beginPath();
    ctx.moveTo(points[0].x, points[0].y);
    points.forEach(p => ctx.lineTo(p.x, p.y));
    ctx.strokeStyle = '#0284c7';
    ctx.lineWidth = 3;
    ctx.stroke();

    // Draw Points and X Labels
    ctx.textAlign = 'center';
    points.forEach(p => {
      // Point Outer
      ctx.beginPath();
      ctx.arc(p.x, p.y, 5, 0, Math.PI * 2);
      ctx.fillStyle = '#ffffff';
      ctx.fill();
      ctx.strokeStyle = '#0284c7';
      ctx.lineWidth = 2.5;
      ctx.stroke();

      // X Label
      ctx.fillStyle = '#64748b';
      ctx.fillText(p.label, p.x, height - padding.bottom + 20);
    });
  },

  // 2. Bar Chart for Destinations/Packages
  drawBarChart: function(canvasId, labels, dataPoints) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();

    canvas.width = rect.width * dpr;
    canvas.height = rect.height * dpr;
    ctx.scale(dpr, dpr);

    const width = rect.width;
    const height = rect.height;
    const padding = { top: 30, right: 20, bottom: 40, left: 45 };
    const chartW = width - padding.left - padding.right;
    const chartH = height - padding.top - padding.bottom;

    ctx.clearRect(0, 0, width, height);

    const maxVal = Math.max(...dataPoints, 5) * 1.25;
    const barWidth = Math.min(36, (chartW / labels.length) * 0.6);
    const stepX = chartW / labels.length;

    // Y Grid
    ctx.strokeStyle = '#f1f5f9';
    ctx.lineWidth = 1;
    ctx.fillStyle = '#64748b';
    ctx.font = '11px Inter, sans-serif';
    ctx.textAlign = 'right';

    for (let i = 0; i <= 3; i++) {
      const val = Math.round((maxVal / 3) * i);
      const y = padding.top + chartH - (i / 3) * chartH;
      ctx.beginPath();
      ctx.moveTo(padding.left, y);
      ctx.lineTo(width - padding.right, y);
      ctx.stroke();
      ctx.fillText(val, padding.left - 8, y + 4);
    }

    // Bars
    labels.forEach((label, idx) => {
      const val = dataPoints[idx];
      const barH = (val / maxVal) * chartH;
      const x = padding.left + idx * stepX + (stepX - barWidth) / 2;
      const y = padding.top + chartH - barH;

      const barGrad = ctx.createLinearGradient(0, y, 0, y + barH);
      barGrad.addColorStop(0, '#f97316');
      barGrad.addColorStop(1, '#ea580c');

      ctx.fillStyle = barGrad;
      ctx.beginPath();
      ctx.roundRect ? ctx.roundRect(x, y, barWidth, barH, [4, 4, 0, 0]) : ctx.rect(x, y, barWidth, barH);
      ctx.fill();

      // Value label on top
      ctx.fillStyle = '#1e293b';
      ctx.font = 'bold 11px Inter, sans-serif';
      ctx.textAlign = 'center';
      ctx.fillText(val, x + barWidth / 2, y - 6);

      // X Label
      ctx.fillStyle = '#64748b';
      ctx.font = '11px Inter, sans-serif';
      ctx.fillText(label, x + barWidth / 2, height - padding.bottom + 20);
    });
  }
};
