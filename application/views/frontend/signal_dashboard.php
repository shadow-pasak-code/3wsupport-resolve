<style>
  .container-gold-fluid {
    padding-top: calc(75px + 30px);
    --bs-gutter-x: 1.5rem;
    --bs-gutter-y: 0;
    width: 100%;
    padding-right: calc(var(--bs-gutter-x) * 0.5);
    padding-left: calc(var(--bs-gutter-x) * 0.5);
    margin-right: auto;
    margin-left: auto;
  }
</style>
<div class="body-wrapper">

  <div class="body-wrapper-inner">
    <div class="container-gold-fluid">
      <!--  Header End -->

      <!--  Row 1 -->
      <div class="row">
        <div class="col-lg-8 d-flex align-items-strech">
          <div class="card w-100">
            <div class="card-body">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <div class="">
                  <h5 class="card-title fw-semibold">XAU/USD (Time Frame <span style="color:red;">1 Minute</span>)</h5>
                </div>
              </div>
              <canvas id="goldChart" height="150"></canvas>
              <div id="latest_signal" class="mt-3 fs-6"></div>
              <div id="winrate_display" style="margin-top:10px; font-weight:bold; color:#000;">
                Winrate: --
              </div>
            </div>
          </div>
        </div>
        <!-- Auto click Order -->
        <div class="col-lg-4">
          <div class="col-lg-12 col-sm-6">
            <!-- Monthly Earnings -->
            <div class="card">
              <div class="card-body">
                <div class="row alig n-items-start">
                  <div class="col-10">
                    <h5 class="card-title mb-10 fw-semibold">Auto Order [BUY Trend] <span id="countdown-timer" style="color:red;">Time: 00:00 Sec</span></h5>

                    <span>
                      <label class="fw-semibold mb-3">Profit: <span id="profit" style="color:green;">1,820</span> $</label>
                      <label class="fw-semibold mb-3">Balance:
                        <span id="balanceDisplay" style="color:orange;">420</span> $</label>
                      <label class="fw-semibold mb-3">Loss: <span style="color:red;">14</span> $</label>
                    </span>

                    <form>

                      <!-- Order Type -->
                      <div class="mb-3">
                        <label class="form-label">Order Type</label>
                        <select id="orderType" class="form-control">
                          <option value="buy">Buy</option>
                          <option value="sell">Sell</option>
                        </select>
                      </div>

                      <!-- Pending Type -->
                      <div class="mb-3">
                        <label class="form-label">Pending Order</label>
                        <select id="pendingType" class="form-control">
                          <option value="buy_limit">Buy Limit</option>
                          <option value="sell_limit">Sell Limit</option>
                          <option value="buy_stop">Buy Stop</option>
                          <option value="sell_stop">Sell Stop</option>
                        </select>
                      </div>

                      <!-- Lot Size -->
                      <div class="mb-3">
                        <label class="form-label">Lot Size</label>
                        <input type="number" step="0.01" class="form-control" id="lotSize" value="0.02">
                      </div>

                      <!-- Entry -->
                      <div class="mb-3">
                        <label class="form-label">Entry Price</label>
                        <input type="number" class="form-control" id="entryPrice">
                      </div>

                      <!-- TP -->
                      <div class="mb-3">
                        <label class="form-label">Take Profit (TP)</label>
                        <input type="number" class="form-control" id="tpPrice">
                      </div>

                      <!-- SL -->
                      <div class="mb-3">
                        <label class="form-label">Stop Loss (SL)</label>
                        <input type="number" class="form-control" id="slPrice">
                      </div>

                      <!-- Realtime Section -->
                      <div class="alert alert-info">
                        Balance: <span id="balanceRealtime" style="color:orange;font-weight:bold;">420</span> $ <br>
                        Required Margin: <span id="requiredMargin" style="color:blue;font-weight:bold;">0</span> $
                      </div>

                      <!-- Button -->
                      <div class="d-flex align-items-center pb-1">
                        <button type="button" class="btn btn-primary m-1" onclick="submitOrder()">Order</button>
                      </div>

                    </form>
                  </div>
                  <div class="col-2">
                    <div class="d-flex justify-content-end">
                      <div
                        class="text-white bg-danger rounded-circle p-6 d-flex align-items-center justify-content-center">
                        <i class="ti ti-currency-dollar fs-6"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div id="earning"></div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-12 col-sm-6">
            <!-- Yearly Breakup -->
            <div class="card overflow-hidden">
              <div class="card-body p-4">
                <h5 class="card-title mb-10 fw-semibold">Order Data Table</h5>
                <div class="row align-items-center">
                  <div class="col-7">
                    <div class="d-flex align-items-center mb-2">
                      <span
                        class="me-1 rounded-circle bg-light-success round-20 d-flex align-items-center justify-content-center">
                        <i class="ti ti-arrow-up-left text-success"></i>
                      </span>
                      <p class="text-dark me-1 fs-3 mb-0">Profit +9%</p>
                      <p class="fs-3 mb-0">5 Days 4 Hrs.</p>
                    </div>
                    <div class="d-flex align-items-center">
                      <div class="me-3">
                        <span class="round-8 bg-primary rounded-circle me-2 d-inline-block"></span>
                        <span class="fs-2">Win</span>
                      </div>
                      <div>
                        <span class="round-8 bg-danger rounded-circle me-2 d-inline-block"></span>
                        <span class="fs-2">Loss</span>
                      </div>
                    </div>
                    <div class="table-responsive">
                      <table id="t_add_row" class="table table-striped w-100 table-bordered display text-nowrap">
                        <thead>
                          <!-- start row -->
                          <tr>
                            <th>Column 1</th>
                            <th>Column 2</th>
                            <th>Column 3</th>
                            <th>Column 4</th>
                            <th>Column 5</th>
                          </tr>
                          <!-- end row -->
                        </thead>
                        <tfoot>
                          <!-- start row -->
                          <tr>
                            <th>Column 1</th>
                            <th>Column 2</th>
                            <th>Column 3</th>
                            <th>Column 4</th>
                            <th>Column 5</th>
                          </tr>
                          <!-- end row -->
                        </tfoot>
                      </table>
                    </div>
                  </div>
                  <div class="col-5">
                    <div class="d-flex justify-content-center">
                      <div id="grade"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
</div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@1.2.0/dist/chartjs-adapter-moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  let ctx = document.getElementById('goldChart').getContext('2d');

  let chartData = {
    labels: [],
    datasets: [{
      label: 'ราคาทอง',
      data: [],
      borderColor: 'gold',
      backgroundColor: 'rgba(255, 215, 0, 0.2)',
      tension: 0.2,
      pointBackgroundColor: [],
      pointRadius: []
    }]
  };

  let goldChart = new Chart(ctx, {
    type: 'line',
    data: chartData,
    options: {
      responsive: true,
      plugins: {
        legend: {
          display: true
        },
        tooltip: {
          mode: 'index',
          intersect: false
        }
      },
      interaction: {
        mode: 'nearest',
        intersect: false
      },
      scales: {
        x: {
          display: true,
          title: {
            display: true,
            text: 'เวลา'
          }
        },
        y: {
          display: true,
          title: {
            display: true,
            text: 'ราคา (USD)'
          }
        }
      }
    },
    plugins: [{
      id: 'drawWinlines',
      afterDraw(chart) {
        if (!chart.winlines) return;
        const ctx = chart.ctx;
        const xScale = chart.scales.x;
        const yScale = chart.scales.y;

        ctx.save();
        ctx.lineWidth = 1.5;
        ctx.strokeStyle = '#00ff88';

        chart.winlines.forEach(line => {
          ctx.beginPath();
          ctx.moveTo(xScale.getPixelForValue(line.x1), yScale.getPixelForValue(line.y1));
          ctx.lineTo(xScale.getPixelForValue(line.x2), yScale.getPixelForValue(line.y2));
          ctx.stroke();
        });

        ctx.restore();
      }
    }]
  });

  let priceHistory = [];

  // ✅ ฟังก์ชันสไตล์จุด
  function getPointStyle(signal) {
    if (signal === 'BUY') return {
      color: 'green',
      radius: 5
    };
    if (signal === 'SELL') return {
      color: 'red',
      radius: 5
    };
    return {
      color: 'orange',
      radius: 0.5
    };
  }

  // ✅ กลยุทธ์หลายแบบ
  function detectStrategySignal(latestPrice, prevPrice, history) {
    if (prevPrice === null) return 'WAIT';
    const threshold = 0.2;

    if (latestPrice < prevPrice - threshold) return 'BUY';
    if (latestPrice > prevPrice + threshold) return 'SELL';

    if (history.length >= 3) {
      const sma =
        (history[history.length - 1].price +
          history[history.length - 2].price +
          history[history.length - 3].price) /
        3;
      if (latestPrice > sma * 1.001) return 'BUY';
      if (latestPrice < sma * 0.999) return 'SELL';
    }

    if (history.length >= 5) {
      const last5 = history.slice(-5).map(p => p.price);
      const min5 = Math.min(...last5);
      const max5 = Math.max(...last5);
      if (latestPrice < min5) return 'BUY';
      if (latestPrice > max5) return 'SELL';
    }

    return 'WAIT';
  }

  // ✅ โหลดข้อมูลย้อนหลัง
  function loadHistoricalSignals() {
    fetch('<?= base_url("signalDetector/fetch_signals") ?>')
      .then(res => res.json())
      .then(data => {
        data.forEach(row => {
          chartData.labels.push(row.datetime);
          chartData.datasets[0].data.push(row.price);
          priceHistory.push({
            price: row.price,
            datetime: row.datetime
          });

          let prev =
            priceHistory.length > 1 ?
            priceHistory[priceHistory.length - 2].price :
            null;
          let signal = detectStrategySignal(row.price, prev, priceHistory);
          let style = getPointStyle(signal);
          chartData.datasets[0].pointBackgroundColor.push(style.color);
          chartData.datasets[0].pointRadius.push(style.radius);
        });
        goldChart.update();
        updateWinlines();
      });
  }

  // ✅ คำนวณ Winrate + เก็บข้อมูลเส้น winline
  function updateWinlines() {
    const thresholdProfit = 5;
    let wins = 0;
    let totalBuys = 0;
    let winlines = [];

    for (let i = 0; i < priceHistory.length; i++) {
      let point = priceHistory[i];
      let prev = i > 0 ? priceHistory[i - 1].price : null;
      let signal = detectStrategySignal(point.price, prev, priceHistory);

      if (signal === 'BUY') {
        totalBuys++;

        // หาจุดที่ราคาขึ้นมากกว่า threshold
        for (let j = i + 1; j < priceHistory.length; j++) {
          if (priceHistory[j].price - point.price >= thresholdProfit) {
            wins++;
            winlines.push({
              x1: i,
              y1: point.price,
              x2: j,
              y2: priceHistory[j].price
            });
            break;
          }
        }
      }
    }

    // ✅ อัปเดตเส้นให้ Chart จำไว้
    goldChart.winlines = winlines;

    // ✅ คำนวณ winrate
    const winratePercent = totalBuys > 0 ? ((wins / totalBuys) * 100).toFixed(2) : 0;
    const winDiv = document.getElementById('winrate_display');
    if (winDiv) {
      winDiv.innerHTML = `Winrate: <span style="color:#72d572;">${winratePercent}% </span> [ <span style="color:#72d572;"> ${wins} from ${totalBuys}</span>] points `;
    }

    goldChart.update();
  }

  // ✅ เริ่มต้นโหลดข้อมูล
  loadHistoricalSignals();

  // ✅ Fetch Realtime
  let prevPrice = null;

  function fetchLatestSignal() {
    fetch('<?= base_url("signalDetector/run") ?>')
      .then(res => res.json())
      .then(data => {
        if (data.error) return;

        let signal = detectStrategySignal(data.price, prevPrice, priceHistory);
        prevPrice = data.price;
        let style = getPointStyle(signal);

        document.getElementById('latest_signal').innerHTML = `
        <strong>ราคา:</strong> ${data.price} &nbsp;
        <strong>สัญญาณ:</strong> <span style="color:${style.color}; font-weight:bold">${signal}</span> &nbsp;
        <strong>Confidence:</strong> ${data.confidence}% &nbsp;
        <strong>เวลา:</strong> ${data.datetime}
      `;

        chartData.labels.push(data.datetime);
        chartData.datasets[0].data.push(data.price);
        chartData.datasets[0].pointBackgroundColor.push(style.color);
        chartData.datasets[0].pointRadius.push(style.radius);
        priceHistory.push({
          price: data.price,
          datetime: data.datetime
        });

        if (chartData.labels.length > 50) {
          chartData.labels.shift();
          chartData.datasets[0].data.shift();
          chartData.datasets[0].pointBackgroundColor.shift();
          chartData.datasets[0].pointRadius.shift();
          priceHistory.shift();
        }

        updateWinlines();
        // อัปเดต Trading Panel
        updateTradingPanel(signal, data.price);

        if (signal === 'BUY' || signal === 'SELL') {
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: signal === 'BUY' ? 'success' : 'error',
            title: `${signal} Signal!`,
            text: `ราคา ${data.price} | Confidence: ${data.confidence}%`,
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true
          });
        }
      })
      .catch(err => console.error(err));
  }

  // Countdown Timer function
  let countdown = 60; // 60 วินาที
  const timerEl = document.getElementById('countdown-timer');

  //✅ เรียกทุก 60 วิ
  fetchLatestSignal();
  setInterval(fetchLatestSignal, 60000);
  // ฟังก์ชันนับถอยหลัง
  // function startCountdown() {
  //   countdown = 60; // รีเซ็ตเวลา
  //   timerEl.textContent = `Time: 00:${countdown < 10 ? '0' + countdown : countdown} Sec`;

  //   const interval = setInterval(() => {
  //     countdown--;
  //     timerEl.textContent = `Time: 00:${countdown < 10 ? '0' + countdown : countdown} Sec`;

  //     if (countdown <= 0) {
  //       fetchLatestSignal(); // เรียกข้อมูล
  //       countdown = 60; // รีเซ็ตตัวนับถอยหลัง
  //     }
  //   }, 1000);
  // }

  // // เริ่มนับถอยหลัง
  // startCountdown();



  // ออกออเดอร์อัตโนมัติ

  let balance = 420; // Balance เริ่มต้น
  let lotSize = 0.02; // สามารถอ่านจาก input
  const contractSize = 100; // ทองคำ XAUUSD

  function updateTradingPanel(signal, price) {
    const entryInput = document.getElementById("entryPrice");
    const tpInput = document.getElementById("tpPrice");
    const slInput = document.getElementById("slPrice");
    const profit = document.getElementById("profit");
    const balanceDisplay = document.getElementById("balanceRealtime");

    if (signal === "BUY") {
      entryInput.value = price.toFixed(2);
      tpInput.value = (price + 5).toFixed(2);
      slInput.value = (price - 10).toFixed(2);

      // คำนวณ Profit / Loss realtime
      let profit = (price - parseFloat(entryInput.value)) * lotSize * contractSize;
      let newBalance = balance + profit;
      balanceDisplay.innerText = newBalance.toFixed(2);
      calculateMargin() // คำนวณ Margin อัตโนมัติ
      
    } else {
      entryInput.value = "";
      tpInput.value = "";
      slInput.value = "";
      balanceDisplay.innerText = balance.toFixed(2);
    }

  }


  function calculateMargin() {
    let lot = parseFloat(document.getElementById("lotSize").value);
    let entry = parseFloat(document.getElementById("entryPrice").value);

    if (!lot || !entry) return;

    let leverage = 100; // ปรับได้
    let contractSize = 100; // ทองคำ XAUUSD ใช้ 100
    let margin = (lot * contractSize * entry) / leverage;

    document.getElementById("requiredMargin").innerText = margin.toFixed(2);
    submitOrder() // คำนวณ เข้าออเดอร์ อัตโนมัติ
  }

  // คำนวณ margin ทุกครั้งที่เปลี่ยนค่า
  document.querySelectorAll("#lotSize, #entryPrice")
    .forEach(el => el.addEventListener("input", calculateMargin));


  function submitOrder() {
    let orderType = document.getElementById("orderType").value;
    let pendingType = document.getElementById("pendingType").value;
    let lot = document.getElementById("lotSize").value;
    let entry = document.getElementById("entryPrice").value;
    let tp = document.getElementById("tpPrice").value;
    let sl = document.getElementById("slPrice").value;
    let margin = document.getElementById("requiredMargin").innerText;

    if (!entry || !tp || !sl) {
      Swal.fire("Error", "กรุณากรอก Entry/TP/SL ให้ครบ", "error");
      return;
    }

    Swal.fire({
      title: "Order Confirmed",
      html: `
        <b>${orderType.toUpperCase()} - ${pendingType.toUpperCase()}</b><br>
        Lot: ${lot}<br>
        Entry: ${entry}<br>
        TP: ${tp}<br>
        SL: ${sl}<br><br>
        Required Margin: <b>${margin} $</b>
      `,
      icon: "success"
    });
  }
</script>