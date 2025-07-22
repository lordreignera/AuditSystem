<div class="main-panel">
          <div class="content-wrapper">
            <div class="row">
              <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
                <div class="card bg-white border border-dark">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-9">
                        <div class="d-flex align-items-center align-self-start">
                          <h3 class="mb-0">{{ $totalProjects ?? 0 }}</h3>
                        </div>
                      </div>
                      <div class="col-3">
                        <div class="icon icon-box-success ">
                          <span class="mdi mdi-arrow-top-right icon-item"></span>
                        </div>
                      </div>
                    </div>
                    <h6 class="text-muted font-weight-normal">Total Projects</h6>
                  </div>
                </div>
              </div>
              <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
                <div class="card bg-white border border-dark">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-9">
                        <div class="d-flex align-items-center align-self-start">
                          <h3 class="mb-0">{{ $inProgressProjects ?? 0 }}</h3>
                        </div>
                      </div>
                      <div class="col-3">
                        <div class="icon icon-box-success">
                          <span class="mdi mdi-arrow-top-right icon-item"></span>
                        </div>
                      </div>
                    </div>
                    <h6 class="text-muted font-weight-normal">Projects In Progress</h6>
                  </div>
                </div>
              </div>
              <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
                <div class="card bg-white border border-dark">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-9">
                        <div class="d-flex align-items-center align-self-start">
                          <h3 class="mb-0">{{ $totalReviewTypes ?? 0 }}</h3>
                        </div>
                      </div>
                      <div class="col-3">
                        <div class="icon icon-box-success">
                          <span class="mdi mdi-arrow-top-right icon-item"></span>
                        </div>
                      </div>
                    </div>
                    <h6 class="text-muted font-weight-normal">Review Types</h6>
                  </div>
                </div>
              </div>
              <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
                <div class="card bg-white border border-dark">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-9">
                        <div class="d-flex align-items-center align-self-start">
                          <h3 class="mb-0">{{ $progressData->count() ?? 0 }}</h3>
                        </div>
                      </div>
                      <div class="col-3">
                        <div class="icon icon-box-success ">
                          <span class="mdi mdi-arrow-top-right icon-item"></span>
                        </div>
                      </div>
                    </div>
                    <h6 class="text-muted font-weight-normal">Tracked Projects</h6>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-lg-8 grid-margin stretch-card">
                <div class="card bg-white border border-dark">
                  <div class="card-body">
                    <h4 class="card-title">Project Progress</h4>
                    <canvas id="projectProgressChart" style="height:300px"></canvas>
                  </div>
                </div>
              </div>
              <div class="col-lg-4 grid-margin stretch-card">
                <div class="card bg-white border border-dark">
                  <div class="card-body">
                    <h4 class="card-title">Progress Distribution</h4>
                    <canvas id="projectProgressPie" style="height:300px"></canvas>
                  </div>
                </div>
              </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
              document.addEventListener('DOMContentLoaded', function() {
                // Bar chart
                var ctx = document.getElementById('projectProgressChart').getContext('2d');
                var chart = new Chart(ctx, {
                  type: 'bar',
                  data: {
                    labels: {!! json_encode($progressData->pluck('name')) !!},
                    datasets: [{
                      label: 'Progress (%)',
                      data: {!! json_encode($progressData->pluck('progress')) !!},
                      backgroundColor: 'rgba(54, 162, 235, 0.7)',
                      borderColor: 'rgba(54, 162, 235, 1)',
                      borderWidth: 1
                    }]
                  },
                  options: {
                    scales: {
                      y: {
                        beginAtZero: true,
                        max: 100
                      }
                    }
                  }
                });
                // Pie chart
                var pieCtx = document.getElementById('projectProgressPie').getContext('2d');
                var pieChart = new Chart(pieCtx, {
                  type: 'pie',
                  data: {
                    labels: ['25% (Setup)', '50% (Editing Complete)', '85% (Answered)', '100% (Reported)'],
                    datasets: [{
                      data: [{{$pieStages[25]}}, {{$pieStages[50]}}, {{$pieStages[85]}}, {{$pieStages[100]}}],
                      backgroundColor: [
                        'rgba(255, 205, 86, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)'
                      ],
                      borderColor: [
                        'rgba(255, 205, 86, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)'
                      ],
                      borderWidth: 1
                    }]
                  },
                  options: {
                    responsive: true,
                    plugins: {
                      legend: {
                        position: 'bottom',
                      },
                      title: {
                        display: false
                      }
                    }
                  }
                });
              });
            </script>

        </div>
          <!-- content-wrapper ends -->
          <!-- partial:partials/_footer.html -->
          <footer class="footer">
            <div class="d-sm-flex justify-content-center justify-content-sm-between">
              <span class="text-muted d-block text-center text-sm-left d-sm-inline-block">Copyright ©prodendistributioncompany 2024</span>
            </div>
          </footer>
          <!-- partial -->
        </div>
        <!-- main-panel ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>