<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Patient Records - HMS</title>
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 20px;
    }

    .container {
      max-width: 1200px;
      margin: auto;
    }

    .card {
      background: #fff;
      border-radius: 8px;
      border: 1px solid #ddd;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      overflow: hidden;
      margin-bottom: 20px;
    }

    .card-header {
      background: #f8f9fa;
      border-bottom: 1px solid #e0e0e0;
      padding: 15px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .card-header h5 {
      margin: 0;
      font-size: 18px;
      color: #0d6efd;
    }

    .btn {
      padding: 8px 16px;
      border-radius: 5px;
      font-weight: 500;
      cursor: pointer;
      border: none;
      transition: 0.2s;
      text-decoration: none;
      display: inline-block;
      text-align: center;
    }

    .btn-outline {
      background: #fff;
      border: 1px solid #ccc;
      color: #6c757d;
    }

    .btn-outline:hover {
      background: #f1f1f1;
    }

    .btn-view {
      background: #28a745;
      color: #fff;
      padding: 5px 10px;
      border-radius: 4px;
      cursor: pointer;
      border: none;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }

    table th, table td {
      border: 1px solid #dee2e6;
      padding: 10px;
      text-align: left;
    }

    table th {
      background-color: #f8f9fa;
      font-weight: 600;
    }

    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.5);
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background: #fff;
      border-radius: 8px;
      max-width: 900px;
      width: 95%;
      padding: 20px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.3);
      animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
      from {opacity: 0; transform: scale(0.9);}
      to {opacity: 1; transform: scale(1);}
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid #ddd;
      padding-bottom: 10px;
      margin-bottom: 15px;
    }

    .close-btn {
      cursor: pointer;
      font-size: 20px;
      color: #dc3545;
      border: none;
      background: none;
    }

    .ehr-container {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
    }

    .ehr-info {
      flex: 1 1 30%;
      background: #f8f9fa;
      padding: 15px;
      border: 1px solid #e0e0e0;
      border-radius: 6px;
    }

    .ehr-info p {
      margin: 10px 0;
      font-size: 14px;
    }

    .ehr-tabs {
      flex: 1 1 65%;
    }

    .tabs {
      display: flex;
      gap: 10px;
      margin-bottom: 15px;
    }

    .tabs button {
      flex: 1;
      padding: 10px;
      border: 1px solid #ddd;
      background: #f8f9fa;
      cursor: pointer;
      border-radius: 4px;
      font-weight: 500;
    }

    .tabs button.active {
      background: #0d6efd;
      color: white;
      border-color: #0d6efd;
    }

    .tab-content {
      border: 1px solid #ddd;
      border-radius: 4px;
      padding: 20px;
      background: #fff;
      min-height: 150px;
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Patient List Section -->
    <div class="card">
      <div class="card-header">
        <h5>Patient Records</h5>
        <a href="<?= base_url('dashboard') ?>" class="btn btn-outline">Back to Dashboard</a>
      </div>
      <div class="card-body">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Contact</th>
              <th>Address</th>
              <th>Gender</th>
              <th>Category</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="patientTable">
            <tr>
              <td>1</td>
              <td>Juan Dela Cruz</td>
              <td>09123456789</td>
              <td>Manila</td>
              <td>Male</td>
              <td>General</td>
              <td><button class="btn-view" onclick="viewPatient('Juan Dela Cruz','09123456789','Manila','01/01/1990','Male','Fever')">View</button></td>
            </tr>
            <tr>
              <td>2</td>
              <td>Maria Santos</td>
              <td>09987654321</td>
              <td>Cebu</td>
              <td>Female</td>
              <td>Pediatrics</td>
              <td><button class="btn-view" onclick="viewPatient('Maria Santos','09987654321','Cebu','05/12/1995','Female','Cough')">View</button></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Popup Modal -->
  <div class="modal" id="ehrModal">
    <div class="modal-content">
      <div class="modal-header">
        <h5>Electronic Health Records</h5>
        <button class="close-btn" onclick="closeModal()">×</button>
      </div>
      <div class="ehr-container">
        <div class="ehr-info">
          <p><b>Full Name:</b> <span id="ehrName">-</span></p>
          <p><b>Mobile:</b> <span id="ehrMobile">-</span></p>
          <p><b>Address:</b> <span id="ehrAddress">-</span></p>
          <p><b>Date of Birth:</b> <span id="ehrDOB">-</span></p>
          <p><b>Gender:</b> <span id="ehrGender">-</span></p>
          <p><b>Ailment:</b> <span id="ehrAilment">-</span></p>
          <p><b>Date Recorded:</b> <span id="ehrDate">-</span></p>
        </div>
        <div class="ehr-tabs">
          <div class="tabs">
            <button class="tab-btn active" onclick="openTab(event,'prescription')">Prescription</button>
            <button class="tab-btn" onclick="openTab(event,'vitals')">Vitals</button>
            <button class="tab-btn" onclick="openTab(event,'lab')">Lab Records</button>
          </div>
          <div id="prescription" class="tab-content">
            <p>Prescription details will appear here...</p>
          </div>
          <div id="vitals" class="tab-content" style="display:none;">
            <p>Vitals records will appear here...</p>
          </div>
          <div id="lab" class="tab-content" style="display:none;">
            <p>Lab records will appear here...</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    function viewPatient(name, mobile, address, dob, gender, ailment) {
      document.getElementById("ehrName").innerText = name;
      document.getElementById("ehrMobile").innerText = mobile;
      document.getElementById("ehrAddress").innerText = address;
      document.getElementById("ehrDOB").innerText = dob;
      document.getElementById("ehrGender").innerText = gender;
      document.getElementById("ehrAilment").innerText = ailment;
      document.getElementById("ehrDate").innerText = new Date().toLocaleDateString();

      document.getElementById("ehrModal").style.display = "flex";
    }

    function closeModal() {
      document.getElementById("ehrModal").style.display = "none";
    }

    function openTab(evt, tabName) {
      var content = document.getElementsByClassName("tab-content");
      for (let i = 0; i < content.length; i++) {
        content[i].style.display = "none";
      }
      var btns = document.getElementsByClassName("tab-btn");
      for (let i = 0; i < btns.length; i++) {
        btns[i].classList.remove("active");
      }
      document.getElementById(tabName).style.display = "block";
      evt.currentTarget.classList.add("active");
    }

    // Close modal on outside click
    window.onclick = function(event) {
      let modal = document.getElementById("ehrModal");
      if (event.target === modal) {
        modal.style.display = "none";
      }
    }
  </script>
</body>
</html>
