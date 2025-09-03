<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Electronic Health Records</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container py-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header">
        Electronic Health Records
      </div>
      <div class="card-body">
        <div class="row">
          <!-- Left Panel (Patient Info) -->
          <div class="col-md-4">
            <div class="card h-100">
              <div class="card-body text-start">
                <div class="text-center mb-3">
                  <i class="bi bi-person-circle fs-1"></i>
                </div>
                <p><strong>Full Name:</strong> Juan Dela Cruz</p>
                <p><strong>Mobile:</strong> 09123456789</p>
                <p><strong>Address:</strong> Manila, PH</p>
                <p><strong>Date of Birth:</strong> 01/01/1990</p>
                <p><strong>Gender:</strong> Male</p>
                <p><strong>Ailment:</strong> Fever</p>
                <hr>
                <p><strong>Date recorded:</strong> 08/26/2025</p>
              </div>
            </div>
          </div>

          <!-- Right Panel (Tabs for Records) -->
          <div class="col-md-8">
            <div class="card h-100">
              <div class="card-body">
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-3" id="recordTabs" role="tablist">
                  <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="prescription-tab" data-bs-toggle="tab" data-bs-target="#prescription" type="button" role="tab">Prescription</button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link" id="vitals-tab" data-bs-toggle="tab" data-bs-target="#vitals" type="button" role="tab">Vitals</button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link" id="lab-tab" data-bs-toggle="tab" data-bs-target="#lab" type="button" role="tab">Lab Records</button>
                  </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="recordTabsContent">
                  <div class="tab-pane fade show active" id="prescription" role="tabpanel">
                    <p>No prescriptions recorded.</p>
                  </div>
                  <div class="tab-pane fade" id="vitals" role="tabpanel">
                    <p>No vitals recorded.</p>
                  </div>
                  <div class="tab-pane fade" id="lab" role="tabpanel">
                    <p>No lab records available.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Back Button -->
        <div class="mt-4">
          <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
          </a>
        </div>
      </div>  
    </div>
  </div>

  <!-- Bootstrap 5 JS + Icons -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</body>
</html>
